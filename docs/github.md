# Configuración recomendada de GitHub

La protección de `main` debe configurarse manualmente en GitHub porque este repositorio local no tiene acceso remoto ni permisos para administrar reglas.

En `Settings → Branches → Branch protection rules`, crear una regla para `main` con:

- Pull Request obligatorio antes de fusionar.
- Al menos una aprobación de una persona distinta de quien escribió el cambio.
- Solicitar que la rama esté actualizada antes de fusionar.
- Exigir que pase el workflow de CI.
- Bloquear force-push y eliminación de la rama.
- Aplicar la regla a administradores.

El remoto, la organización y los equipos revisores quedan pendientes de definir. No se ha publicado ningún cambio.

