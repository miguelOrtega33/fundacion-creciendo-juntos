# ADR-0008: Modelo de roles y permisos

- Estado: En elaboración
- Fecha: 2026-11-10
- Issue: #8

## Contexto

La aplicación necesita autorización del lado servidor antes de incorporar gestión de usuarios o dominio editorial. Los nombres de rol definidos en `SPEC.md` no bastan para decidir qué acciones puede ejecutar cada persona, sobre qué recursos ni con qué alcance.

Este ADR define primero un vocabulario común. La matriz de permisos, la representación técnica y las reglas de escalamiento se completarán en las secciones siguientes antes de aprobar la decisión.

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

### Prohibiciones críticas

Estas reglas prevalecen sobre cualquier celda con alcance `global`:

1. Ningún actor puede cambiar su propio rol.
2. Ningún actor puede desactivar su propia cuenta mediante la administración de usuarios.
3. Un administrador no puede ver, crear, modificar, desactivar ni cambiar el rol de otro administrador o superadministrador.
4. No se puede desactivar, degradar ni eliminar al último superadministrador activo.
5. Crear una cuenta no autoriza a asignarle un rol fuera del conjunto gestionado por el actor.
6. Una cuenta desactivada no puede autenticarse ni conservar sesiones activas.

La implementación debe probar cada permiso concedido y, al menos, una denegación por rol y acción. Las prohibiciones críticas requieren pruebas de regresión específicas.

## Fuera de este ADR

Este ADR no implementa migraciones, enum, Gates, Policies, middleware ni interfaz. Las acciones detalladas del dominio editorial y la auditoría se decidirán en sus ADR respectivos antes de construir esos módulos.
