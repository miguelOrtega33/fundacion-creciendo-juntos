# Matriz de trazabilidad

| Requisito | Issue | PR | Prueba | Evidencia |
|---|---|---|---|---|
| Fase 0: esqueleto Laravel ejecutable | Pendiente | Pendiente | `HealthTest` | [Verificación local de Fase 0](../evidencias/fase-0.md) |
| Fase 0: entorno Docker Compose | Pendiente | Pendiente | `docker compose config --quiet` | [Verificación local de Fase 0](../evidencias/fase-0.md) |
| Fase 0: migración mínima | Pendiente | Pendiente | `php artisan migrate:status` | [Verificación local de Fase 0](../evidencias/fase-0.md) |
| Fase 0: CI y validaciones | Pendiente | Pendiente | Workflow de GitHub Actions | Validación local registrada; ejecución remota pendiente |
| Portal público | Pendiente | Pendiente | Pendiente | Pendiente |
| Flujo editorial | Pendiente | Pendiente | Pendiente | Pendiente |
| Base persistente de roles y estado de acceso | [#11](https://github.com/miguelOrtega33/fundacion-creciendo-juntos/issues/11) | [#15](https://github.com/miguelOrtega33/fundacion-creciendo-juntos/pull/15) | `UserTest`, `UserRoleConstraintTest` y `RoleMigrationTest` | [ADR-0008](../arquitectura/adr/0008-modelo-roles-y-permisos.md) |
| Sesiones persistentes y revocación selectiva | [#12](https://github.com/miguelOrtega33/fundacion-creciendo-juntos/issues/12) | [#16](https://github.com/miguelOrtega33/fundacion-creciendo-juntos/pull/16) | `SessionMigrationTest`, `SessionConfigurationTest` y `UserSessionRevokerTest` | [ADR-0008](../arquitectura/adr/0008-modelo-roles-y-permisos.md) |
| Eventos mínimos de seguridad | [#13](https://github.com/miguelOrtega33/fundacion-creciendo-juntos/issues/13) | [#17](https://github.com/miguelOrtega33/fundacion-creciendo-juntos/pull/17) | `SecurityEventMigrationTest`, `SecurityEventRecorderTest` y `SecurityEventTransactionTest` | [ADR-0008](../arquitectura/adr/0008-modelo-roles-y-permisos.md) |
| Aplicación de permisos y desactivación | [#10](https://github.com/miguelOrtega33/fundacion-creciendo-juntos/issues/10) | Pendiente | Pendiente | [ADR-0008](../arquitectura/adr/0008-modelo-roles-y-permisos.md) |
| Auditoría | Fase posterior | Fase posterior | Pendiente | Pendiente |

