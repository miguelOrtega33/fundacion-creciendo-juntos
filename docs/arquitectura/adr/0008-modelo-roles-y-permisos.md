# ADR-0008: Modelo de roles y permisos

- Estado: Propuesto
- Fecha: 2026-11-10
- Issue: #8

## Contexto

La aplicación necesita autorización del lado servidor antes de incorporar gestión de usuarios o dominio editorial. Los nombres de rol definidos en `SPEC.md` no bastan para decidir qué acciones puede ejecutar cada persona, sobre qué recursos ni con qué alcance.

Este ADR establece el vocabulario, la matriz de Fase 1, su representación técnica y las reglas que impiden escalamiento o pérdida de control administrativo.

## Decisión

La Fase 1 usará roles fijos y versionados en código, permisos explícitos por acción y alcance, y autorización obligatoria del lado servidor. Laravel representará los roles mediante un `enum` respaldado por texto, Gates y Policies aplicarán la matriz, y MySQL reforzará los valores permitidos y las operaciones críticas.

Los privilegios no serán configurables desde la interfaz durante esta fase. Cualquier cambio en roles, acciones o alcances deberá pasar por migración, pruebas, revisión y Pull Request.

## Principios

- **Denegación por defecto:** toda acción queda prohibida mientras no exista una autorización explícita.
- **Control en servidor:** ocultar un enlace o botón no reemplaza una Policy o Gate.
- **Mínimo privilegio:** cada rol recibe solo las capacidades necesarias para su responsabilidad.
- **Acciones explícitas:** no se autoriza mediante frases generales como «gestionar usuarios».
- **Alcance explícito:** cada autorización indica sobre qué instancias puede actuar.
- **Sin jerarquía implícita:** un rol superior no hereda automáticamente todas las acciones; la matriz debe declararlas.
- **Separación de responsabilidades:** crear contenido, revisarlo y administrar privilegios son capacidades distintas.

## Vocabulario

### Actor y objetivo

- **Actor:** persona autenticada que intenta ejecutar una acción.
- **Objetivo:** recurso o persona sobre el que recae la acción.
- **Visitante:** solicitud no autenticada. No es un rol persistido y solo accede a recursos públicos.

### Recursos

| Recurso | Significado | Fase |
|---|---|---|
| `panel` | Entrada al área privada de la aplicación. | Fase 1 |
| `usuario` | Cuenta, datos básicos y estado de acceso de una persona. | Fase 1 |
| `rol` | Asignación de responsabilidades a una cuenta. | Fase 1 |
| `noticia` | Contenido y estados del flujo editorial. | Fase posterior |
| `auditoria` | Registro inmutable de acciones relevantes. | Fase posterior |

Agregar un recurso nuevo exige extender la matriz y sus pruebas antes de exponer una ruta o acción.

### Acciones

Las capacidades se nombran como `recurso.acción` y expresan una operación observable:

| Acción | Significado |
|---|---|
| `panel.acceder` | Entrar al área privada. |
| `usuario.listar` | Consultar una colección de cuentas autorizada. |
| `usuario.ver` | Consultar una cuenta concreta. |
| `usuario.crear` | Crear una cuenta sin asignar privilegios fuera del alcance permitido. |
| `usuario.actualizar` | Modificar datos básicos de una cuenta. |
| `usuario.desactivar` | Impedir el acceso de una cuenta sin borrar su trazabilidad. |
| `rol.asignar` | Cambiar el rol persistido de una cuenta. |

Las acciones de noticias y auditoría se definirán en sus ADR respectivos. Nombrarlas como recursos futuros no las autoriza ni las implementa en este hito.

### Alcances

| Alcance | Significado |
|---|---|
| `propio` | El actor y el objetivo son la misma cuenta, o el actor es propietario del recurso. |
| `gestionado` | El objetivo pertenece a un conjunto de roles que el actor puede administrar explícitamente. |
| `global` | La acción puede alcanzar cualquier instancia, salvo prohibiciones de seguridad expresas. |
| `ninguno` | La acción está denegada. |

Un alcance amplio no anula reglas críticas como la prohibición de autoescalamiento o la protección del último superadministrador.

## Matriz de permisos de Fase 1

La matriz usa los alcances definidos anteriormente. Una restricción escrita en la celda forma parte del permiso y debe verificarse en servidor.

| Acción | Visitante | Editor | Administrador | Superadministrador |
|---|---|---|---|---|
| `panel.acceder` | `ninguno` | `propio` | `propio` | `propio` |
| `usuario.listar` | `ninguno` | `ninguno` | `gestionado`: solo editores | `global` |
| `usuario.ver` | `ninguno` | `propio` | `propio` y `gestionado`: solo editores | `global` |
| `usuario.crear` | `ninguno` | `ninguno` | `gestionado`: nueva cuenta editora | `global`: editor, administrador o superadministrador |
| `usuario.actualizar` | `ninguno` | `propio`: datos básicos | `propio` y `gestionado`: datos básicos de editores | `global`: datos básicos |
| `usuario.desactivar` | `ninguno` | `ninguno` | `gestionado`: solo editores | `global`: nunca a sí mismo ni al último superadministrador activo |
| `rol.asignar` | `ninguno` | `ninguno` | `ninguno` | `global`: solo sobre otra cuenta y sin dejar al sistema sin superadministrador activo |

### Conjuntos gestionados

- Un **editor** no administra cuentas ajenas.
- Un **administrador** administra únicamente cuentas cuyo rol actual y rol resultante sean `editor`.
- Un **superadministrador** administra cuentas de cualquier rol, sujeto a las prohibiciones críticas.
- El visitante no pertenece a un conjunto gestionado porque no es una cuenta autenticada.

### Datos básicos

`usuario.actualizar` cubre nombre y otros atributos de perfil que se aprueben posteriormente. No permite cambiar rol, contraseña, identificadores de sesión ni campos de seguridad. Cada operación sensible requiere una acción propia y una autorización independiente.

La eliminación física de cuentas no forma parte de la Fase 1. No existirá una acción `usuario.eliminar`, y cualquier intento de exponer esa operación deberá denegarse hasta que un ADR defina su necesidad, autorización y efecto sobre la trazabilidad.

### Prohibiciones críticas

Estas reglas prevalecen sobre cualquier celda con alcance `global`:

1. Ningún actor puede cambiar su propio rol.
2. Ningún actor puede desactivar su propia cuenta mediante la administración de usuarios.
3. Un administrador no puede ver, crear, modificar, desactivar ni cambiar el rol de otro administrador o superadministrador.
4. No se puede desactivar ni degradar al último superadministrador activo.
5. Crear una cuenta no autoriza a asignarle un rol fuera del conjunto gestionado por el actor.
6. Una cuenta desactivada no puede autenticarse ni conservar sesiones activas.

La implementación debe probar cada permiso concedido y, al menos, una denegación por rol y acción. Las prohibiciones críticas requieren pruebas de regresión específicas.

## Representación técnica

### Roles persistidos

- `users.role` se almacenará como texto y Laravel lo convertirá a un `enum` respaldado de PHP.
- Los valores iniciales serán `editor`, `administrador` y `superadministrador`.
- La base de datos aplicará una restricción `CHECK` equivalente para impedir valores desconocidos fuera de Laravel.
- No se usará `ENUM` nativo de MySQL: agregar o retirar un rol debe realizarse mediante una migración explícita sin acoplar el dominio a un tipo específico del motor.
- No se creará todavía una tabla configurable de roles y permisos. La matriz es pequeña, versionada y requiere revisión de código para cambiar privilegios.

El visitante no se persiste en `users.role`. La ausencia de una cuenta autenticada representa ese estado.

### Estado de acceso

- `users.disabled_at` será una marca de tiempo anulable: `null` significa cuenta activa y una fecha significa cuenta desactivada.
- Se conservará la fila para mantener referencias y trazabilidad; la desactivación no equivale a borrar la cuenta.
- La comprobación de cuenta activa se realizará al autenticar y en cada solicitud privada mediante middleware. Ocultar controles en la interfaz no sustituye esta validación.
- Desactivar una cuenta eliminará sus sesiones persistidas, invalidará su `remember_token` y revocará sus tokens de recuperación pendientes dentro de la misma operación.

Antes de implementar administración de cuentas, la aplicación deberá usar `SESSION_DRIVER=database` y una tabla de sesiones con `user_id` indexado. El almacenamiento en archivos no permite localizar y revocar de forma fiable las sesiones de una cuenta específica, por lo que no satisface esta decisión.

Una futura reactivación requerirá una acción, autorización y registro de auditoría propios; no se considera implícita en `usuario.actualizar`.

### Gates y Policies

- Un Gate controlará `panel.acceder`, porque el panel no es una instancia de modelo administrada.
- Una `UserPolicy` resolverá las acciones sobre cuentas y recibirá tanto al actor como al objetivo cuando corresponda.
- Las consultas de listado deberán filtrar desde la base de datos según el conjunto gestionado; no se cargarán cuentas prohibidas para ocultarlas después.
- Los controladores y Form Requests consumirán estas decisiones, pero no duplicarán la matriz.
- La interfaz podrá consultar permisos para presentar acciones disponibles, aunque la decisión definitiva siempre permanecerá en el servidor.

### Operaciones críticas

Los cambios de rol y las desactivaciones se ejecutarán dentro de una transacción. Cuando puedan afectar a un superadministrador, la operación bloqueará las filas relevantes antes de contar superadministradores activos. Así se evita que dos solicitudes concurrentes desactiven o degraden simultáneamente a quienes parecen no ser el último superadministrador.

Después de cambiar el rol de una cuenta se eliminarán sus sesiones persistidas y se invalidará su `remember_token`. La persona deberá autenticarse nuevamente para que ninguna sesión conserve capacidades asociadas al rol anterior.

### Registro mínimo de seguridad

Crear cuentas, cambiar roles y desactivar cuentas generará un evento append-only dentro de la misma transacción que aplica el cambio. Cada evento conservará como mínimo actor, objetivo, acción, valores de seguridad anterior y nuevo, resultado y fecha. No almacenará contraseñas, tokens, identificadores de sesión ni otros secretos.

La aplicación no expondrá operaciones para actualizar o eliminar estos eventos. Los intentos denegados se registrarán en el log de seguridad con un identificador de correlación, porque no pertenecen a una transacción de cambio exitosa. Este registro mínimo permite reconstruir mutaciones de privilegios; el alcance, retención y consulta de la auditoría editorial completa continúan pendientes de su ADR específico.

### Primer superadministrador

El primer superadministrador se creará mediante un comando Artisan ejecutado conscientemente por una persona operadora:

- exigirá nombre y correo explícitos;
- solicitará la contraseña mediante entrada oculta y confirmación;
- rechazará la operación si ya existe un superadministrador activo;
- no incluirá credenciales predeterminadas ni datos privilegiados en seeders, variables versionadas o imágenes Docker;
- registrará solo el resultado necesario, nunca la contraseña.

Los seeders de demostración podrán crear usuarios no privilegiados con datos ficticios, pero no resolverán el arranque administrativo de producción.

### Estrategia de pruebas

- Pruebas unitarias tabuladas cubrirán cada celda de la matriz y sus alcances.
- Pruebas funcionales comprobarán que rutas y solicitudes rechazan acciones aunque la interfaz intente enviarlas directamente.
- Pruebas de integración con MySQL cubrirán la restricción de roles, la transacción del último superadministrador y la revocación de sesiones y tokens.
- Una prueba de integración comprobará que las sesiones de otras cuentas permanecen intactas al revocar todas las sesiones del objetivo.
- Las mutaciones privilegiadas comprobarán el evento de seguridad generado y que ningún secreto quede registrado.
- Cada prohibición crítica tendrá una regresión explícita, incluidos autoescalamiento, autodesactivación y administración de roles superiores.
- Una prueba comprobará que no exista una ruta ni una autorización para eliminar cuentas en Fase 1.
- Se comprobará que una prueba falle al romper temporalmente la regla que protege, antes de aceptar su evidencia.

## Alternativas descartadas

### Roles y permisos configurables en tablas

Se descarta por ahora un modelo dinámico con tablas de roles, permisos y relaciones. Aunque permitiría cambios sin desplegar código, introduce una interfaz administrativa sensible, invalidación de caché, auditoría adicional y combinaciones difíciles de revisar. La matriz inicial es pequeña y debe cambiar solo mediante revisión técnica.

### Paquete genérico de control de acceso

No se incorporará una dependencia para resolver esta primera matriz. Laravel ya ofrece Gates, Policies y conversión a enums; agregar un paquete aumentaría superficie de actualización y conceptos sin aportar una necesidad demostrada. La decisión se podrá reevaluar si aparecen permisos configurables, múltiples roles simultáneos o equipos con alcances variables.

### `ENUM` nativo de MySQL

Se descarta porque acopla los valores del dominio al tipo específico del motor y vuelve más rígida su evolución. Texto, enum de PHP y restricción `CHECK` mantienen validación en ambas capas con migraciones explícitas.

### Condiciones de rol repartidas en controladores y vistas

Se descartan comprobaciones como `if ($user->role === ...)` distribuidas por la aplicación. Duplican reglas, facilitan contradicciones y pueden convertir una condición visual en la única protección. Las decisiones deben concentrarse en Gates, Policies y servicios de operaciones críticas.

### Indicador booleano de cuenta activa

Se descarta un campo `is_active` porque solo registra el estado actual. `disabled_at` conserva además el momento de la desactivación y permite razonar sobre sesiones o auditoría sin agregar otro campo inmediatamente.

## Consecuencias

### Positivas

- La política puede revisarse como una matriz única antes de exponer rutas o pantallas.
- La denegación por defecto y la validación en servidor reducen escalamiento accidental de privilegios.
- Los filtros en base de datos evitan revelar cuentas fuera del alcance del actor.
- Las transacciones protegen invariantes incluso ante solicitudes concurrentes.
- La revocación de sesiones hace efectivos los cambios de acceso inmediatamente.
- El registro append-only permite atribuir y reconstruir cambios de privilegios.

### Costos aceptados

- Cambiar un rol o permiso requiere modificar código, migración cuando corresponda y pruebas.
- La matriz y las Policies deben mantenerse sincronizadas mediante pruebas tabuladas.
- Las operaciones del último superadministrador necesitan pruebas reales con MySQL, no solo SQLite.
- La administración de cuentas exige migrar las sesiones desde archivos hacia la base de datos.
- El registro mínimo de seguridad agrega almacenamiento y obliga a definir su política de retención antes de producción.
- El comando inicial exige una acción operativa documentada durante cada instalación productiva.

## Evolución de la decisión

- Agregar una acción o recurso exige actualizar este ADR o el ADR de su dominio antes de implementar rutas.
- Agregar un rol exige actualizar el enum, la restricción de base de datos, toda la matriz y sus pruebas.
- Retirar o renombrar un rol requiere una migración de datos previa; nunca se elimina un valor mientras existan cuentas que lo utilicen.
- Adoptar permisos configurables o múltiples roles por cuenta requiere un nuevo ADR que reemplace explícitamente esta decisión.
- Las capacidades editoriales y de auditoría se incorporarán en matrices propias y no se inferirán de los nombres actuales de rol.

## Fuera de este ADR

Este ADR no implementa migraciones, enum, Gates, Policies, middleware ni interfaz. Las acciones detalladas del dominio editorial y la auditoría se decidirán en sus ADR respectivos antes de construir esos módulos.
