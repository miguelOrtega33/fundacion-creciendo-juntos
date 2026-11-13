# Riesgos y decisiones por confirmar

- Complejidad del editor visual y sanitización permitida.
- Validación e implementación del modelo de permisos propuesto en ADR-0008.
- Límites, almacenamiento y procesamiento de imágenes.
- Retención de auditoría y datos de correo.
- Flujo editorial y política de publicación.
- Proveedor de despliegue.
- Reglas de protección de `main` en GitHub.

## Riesgos residuales de autenticación

- La recuperación responde siempre con el mismo mensaje y encola el mismo trabajo inicial, con límites de 5 solicitudes por correo y 30 por IP cada minuto. Una cola compartida aún podría introducir un canal temporal indirecto: un atacante con una cuenta propia podría intentar correlacionar la llegada de su correo con trabajo SMTP previo. Se acepta este riesgo para el alcance actual porque la respuesta HTTP es asíncrona y uniforme, el volumen está limitado y eliminar toda contención exigiría infraestructura aislada o trabajo SMTP ficticio. Debe reevaluarse al definir proveedor de correo, número de workers, monitoreo y arquitectura de despliegue.

Resuelto para Fase 0: Laravel 13.x, PHP 8.3+, MySQL 8.4, Nginx/PHP-FPM, Blade, Docker Compose, Mailpit, PHPUnit y licencia MIT.

