# Evidencia Hito 1.3 — flujo completo de recuperación

- **Identificador:** EVI-AUTH-REC-002.
- **Requisito:** recibir un enlace funcional y establecer una nueva contraseña mediante un token temporal.
- **Commit funcional del recorrido visual:** `ccd03c2`.
- **Commit de las capturas:** `107397a`.
- **Vigencia automatizada hasta:** `e478638`.
- **Entorno:** Docker, Nginx, PHP-FPM, worker de cola, MySQL, Mailpit y Chromium mediante Playwright.
- **Esperado:** la solicitud entrega un correo en español mediante la cola y el formulario comunica una política de 15 a 64 caracteres antes de guardar la contraseña.
- **Obtenido:** Mailpit recibió 1 mensaje para una cuenta ficticia con el asunto «Restablece tu contraseña»; el formulario respondió HTTP 200, presentó los límites esperados y mantuvo visibles sus controles.
- **Vigencia comprobada:** el correo informa 60 minutos; las pruebas rechazan tokens expirados a los 61 minutos.
- **Seguridad comprobada:** la respuesta de solicitud es genérica, el trabajo de correo se almacena cifrado, el token se consume de forma atómica, las sesiones anteriores se invalidan y las contraseñas nuevas usan Argon2id.
- **Política comprobada:** se aceptan 15 y 64 caracteres; se rechazan 14 y 65. La vista permite espacios y frases largas sin imponer reglas de composición.
- **Vista móvil:** 390 × 1000 píxeles, sin desborde horizontal y con el texto de ayuda, ambos campos, el botón y el enlace visibles.
- **Vista de correo:** 1280 × 800 píxeles; asunto, destinatario y resumen en español visibles sin abrir el enlace.
- **Señalización:** los recuadros rojos identifican los elementos evaluados.
- **Datos:** las direcciones usan el dominio reservado `example.test`; ninguna captura expone tokens ni contraseñas y los datos ficticios se eliminaron después del recorrido.

## Vigencia posterior de la evidencia

Las capturas demuestran únicamente el recorrido visual indicado. Las correcciones posteriores se comprobaron con evidencia automatizada reproducible:

- `ForgotPasswordRequestTest`: respuesta genérica, cifrado del trabajo inicial y límites por correo e IP.
- `QueuedPasswordResetDeliveryTest`: cifrado de ambos trabajos, destinatario, worker real y recuperación ante una falla SMTP transitoria.
- `AtomicPasswordResetConcurrencyTest`: un único consumo exitoso del token bajo concurrencia MySQL.
- `ResetPasswordTest`: expiración, uso único, límites Unicode y Argon2id después del restablecimiento.
- `AuthenticationTest`: transición de bcrypt a Argon2id e invalidación de sesiones, incluso antes del primer acceso al panel.
- `ScheduleTest`: eliminación programada de trabajos fallidos con más de 168 horas.

El canal temporal indirecto de una cola compartida y sus controles compensatorios están registrados en `docs/riesgos-y-pendientes.md`.

## Formulario de nueva contraseña

![Formulario móvil de nueva contraseña delimitado en rojo](restablecimiento-movil.png)

## Correo recibido en Mailpit

![Correo de recuperación recibido y delimitado en rojo en Mailpit](correo-mailpit.png)
