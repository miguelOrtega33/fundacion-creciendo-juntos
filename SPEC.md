# Especificación del producto

## Objetivo

Plataforma institucional responsiva para publicar y gestionar noticias con un flujo editorial trazable y seguro.

## Tecnología acordada

PHP, MySQL, HTML, CSS, JavaScript, Docker Compose y herramientas de pruebas automatizadas. Framework PHP, versión, estrategia de frontend y licencia quedan por confirmar.

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

Framework PHP, versión de PHP/MySQL, proveedor de correo productivo, política de retención, licencia, diseño visual, límites de archivos, definición de permisos detallados y política de publicación requieren decisión.

