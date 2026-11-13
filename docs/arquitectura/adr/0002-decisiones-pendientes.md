# ADR-0002: Decisiones técnicas pendientes

- Estado: Pendiente

Las siguientes decisiones siguen pendientes y deben registrarse antes de implementar el dominio editorial:

1. Proveedor de correo productivo y política de retención.
2. Límites, almacenamiento y procesamiento de imágenes.
3. Retención y estructura de auditoría.
4. Flujo editorial y sus transiciones autorizadas.
5. Política de publicación y revisión.

Laravel 13.x/PHP 8.3+, MySQL 8.4, Docker Compose, Nginx/PHP-FPM, Blade, Mailpit, PHPUnit y MIT quedan resueltos en ADR-0003 a ADR-0007. El modelo de roles y permisos de Fase 1 está propuesto en ADR-0008 y permanece sujeto a validación por Pull Request. No se deben introducir dependencias ni servicios para los puntos pendientes sin ADR o aprobación explícita.

