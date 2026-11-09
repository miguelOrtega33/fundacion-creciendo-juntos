# ADR-0007: Blade, PHP-FPM y Nginx

- Estado: Aprobado
- Fecha: 2026-11-09

## Decisión

La presentación inicial usará Blade servido por Laravel mediante PHP-FPM detrás de Nginx. JavaScript se agregará progresivamente solo donde mejore la interacción.

## Motivo

Este enfoque mantiene una superficie inicial pequeña, favorece accesibilidad y permite entregar HTML funcional antes de introducir complejidad de frontend.

## Alcance

No se adopta todavía un framework SPA ni se decide el editor visual. Esas decisiones quedan para fases posteriores.
