# Configuración recomendada de GitHub

El repositorio está publicado en GitHub y tiene configurado el remoto `origin`. La protección de `main` debe configurarse manualmente porque este repositorio local no administra reglas de GitHub.

En `Settings → Branches → Branch protection rules`, crear una regla para `main` con:

- Pull Request obligatorio antes de fusionar.
- Al menos una aprobación de una persona distinta de quien escribió el cambio.
- Solicitar que la rama esté actualizada antes de fusionar.
- Exigir que pase el workflow de CI.
- Bloquear force-push y eliminación de la rama.
- Aplicar la regla a administradores.

La organización y los equipos revisores quedan pendientes de definir. Regla obligatoria: ningún cambio entra directamente a `main`; todo cambio debe llegar mediante un Pull Request después de pasar las validaciones automáticas y la revisión independiente correspondiente.

