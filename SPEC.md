# Especificación del producto

## Objetivo

Plataforma institucional responsiva para publicar y gestionar noticias con un flujo editorial trazable y seguro.

## Tecnología acordada

Laravel 13.x sobre PHP 8.3 o superior, MySQL 8.4, Nginx con PHP-FPM, Blade con mejoras progresivas mediante JavaScript, Docker Compose, Mailpit y PHPUnit. La licencia del repositorio es MIT.

## Roles

- Visitante: consulta noticias públicas.
- Editor: crea, modifica, guarda borradores y envía noticias a revisión.
- Administrador: revisa, aprueba, rechaza, solicita correcciones y gestiona editores.
- Superadministrador: gestiona administradores y permisos superiores.

## Funcionalidades

Portal institucional; noticias destacadas, categorías, búsqueda y paginación; editor visual; imágenes JPG/PNG/WebP; vista previa; flujo editorial tipo Pull Request; comentarios y solicitudes de corrección; historial y auditoría; paneles por rol; notificaciones internas; autenticación y recuperación por correo; usuarios y permisos; URLs amigables y accesibilidad.

## Estados editoriales

`borrador → en_revision → aprobado → publicado`; desde `en_revision` se puede pasar a `correcciones_solicitadas` y volver a `borrador`; una publicación puede pasar a `archivado`. Solo roles autorizados ejecutan cada transición y toda transición queda auditada.

## Reglas de negocio

No se publica sin aprobación; solo contenido sanitizado y archivos validados; un visitante solo ve contenido publicado; los permisos se verifican en servidor; una noticia publicada conserva historial; las URLs son únicas; la paginación y búsqueda deben ser deterministas.

## Requisitos no funcionales

Seguridad OWASP, mínimo privilegio, accesibilidad WCAG 2.2 AA como objetivo, diseño responsive, separación de ambientes, logs sin secretos, copias y recuperación documentadas, despliegue reproducible en Windows/Docker Desktop, Linux y macOS.

## Criterios de aceptación iniciales

Cada rol solo ve y ejecuta acciones autorizadas; el flujo no permite saltarse aprobación; entradas inválidas y cargas peligrosas se rechazan; una auditoría permite reconstruir cambios; una persona nueva puede levantar el proyecto siguiendo README; las pruebas automatizadas y evidencias reproducen las afirmaciones.

## Pendientes

El proveedor de correo productivo, la política de retención, el diseño visual, los límites y procesamiento de archivos, la definición detallada de permisos, la política de publicación, el flujo editorial y la auditoría requieren decisiones en fases posteriores.

