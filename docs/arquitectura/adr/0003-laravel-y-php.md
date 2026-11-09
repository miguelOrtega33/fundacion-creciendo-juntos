# ADR-0003: Laravel 13.x y PHP 8.3+

- Estado: Aprobado
- Fecha: 2026-11-09

## Decisión

La aplicación usará Laravel 13.x sobre PHP 8.3 o superior. Composer será el gestor de dependencias y PHPUnit la herramienta base de pruebas.

## Motivo

Laravel entrega una base integrada para rutas, configuración, validación, pruebas y autorización, manteniendo el alcance de Fase 0 pequeño. La versión mínima de PHP se fija en 8.3 para coincidir con los requisitos de Laravel 13.x.

## Alcance

Este ADR no decide permisos de negocio, autenticación completa, frontend avanzado ni funcionalidades editoriales.
