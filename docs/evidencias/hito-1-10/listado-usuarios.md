# Evidencia Hito 1.10 — listado de usuarios

- **Commit evaluado:** `b82c636`
- **Requisito:** listar solo las cuentas autorizadas por ADR-0008.
- **Entorno:** aplicación local Docker, Chromium y MySQL local.
- **Esperado:** tabla accesible, paginación, estados visibles y adaptación móvil sin revelar cuentas fuera del alcance.
- **Obtenido:** superadministrador visualizó los tres roles; administrador visualizó exclusivamente editores; paginación de 10 registros operativa.
- **Consola:** 0 errores y 0 advertencias en escritorio y móvil.
- **Datos:** cuatro cuentas ficticias eliminadas junto con sus sesiones al finalizar.

El recuadro rojo identifica el componente evaluado:

- [Escritorio 1440 × 1000](listado-usuarios-escritorio.png)
- [Móvil 390 × 844](listado-usuarios-movil.png)
