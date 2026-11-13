# Especificación del producto

## Objetivo

Plataforma institucional responsiva para publicar y gestionar noticias con un flujo editorial trazable y seguro.

## Tecnología acordada

Laravel 13.x sobre PHP 8.3 o superior, MySQL 8.4, Nginx con PHP-FPM, Blade con mejoras progresivas mediante JavaScript, Docker Compose, Mailpit y PHPUnit. La licencia del repositorio es MIT.

## Roles

- Visitante: consulta noticias públicas.
- Editor: accede al panel y administra sus datos básicos; sus capacidades editoriales se definirán con el flujo de noticias.
- Administrador: gestiona cuentas editoras; sus capacidades de revisión se definirán con el flujo editorial.
- Superadministrador: gestiona cuentas de cualquier rol y asigna roles, sujeto a las prohibiciones críticas.

La matriz de administración de cuentas, alcances y restricciones de Fase 1 está aprobada en [ADR-0008](docs/arquitectura/adr/0008-modelo-roles-y-permisos.md). Nombrar capacidades editoriales futuras no las autoriza hasta que su ADR defina acciones y transiciones.

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

El proveedor de correo productivo, la política de retención, el diseño visual, los límites y procesamiento de archivos, los permisos editoriales, la política de publicación, el flujo editorial y la auditoría requieren decisiones en fases posteriores. La implementación del modelo de administración de cuentas aprobado en ADR-0008 permanece pendiente.

