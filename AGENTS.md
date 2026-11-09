# Instrucciones para agentes

## Alcance y flujo

- Está prohibido hacer push directo a `main` o fusionar cambios sin validación. Todo cambio debe desarrollarse en una rama `feat/`, `fix/`, `docs/` o `chore/`, pasar por un Pull Request, ejecutar las validaciones correspondientes y ser revisado antes de fusionarse.
- Un Pull Request debe tratar una sola funcionalidad o decisión acotada.
- Usar commits descriptivos, trazables y en español de Chile: `tipo(ámbito): descripción`.
- Una persona distinta de quien implementó debe revisar el cambio antes de integrarlo.
- No modificar funcionalidades fuera del issue.
- No ejecutar comandos destructivos sin autorización explícita.
- Tras dos intentos fallidos con el mismo enfoque, detenerse, releer el código y replantear.

## Seguridad y calidad

Aplicar OWASP y mínimo privilegio. Usar consultas parametrizadas, CSRF, hashing seguro, sesiones protegidas, autorización del lado servidor, validación de archivos, sanitización HTML y controles de subida seguros. Nunca guardar credenciales ni datos sensibles en Git.

## Commits y Pull Requests

Cada funcionalidad debe quedar separada en uno o más commits coherentes; no se deben mezclar funcionalidades distintas en un mismo commit. Los commits deben usar el formato `tipo(ámbito): descripción breve`, estar escritos en español de Chile y documentar en el cuerpo: qué se hizo, cómo se implementó, por qué esa solución es la práctica adecuada y cómo se verificó. Tipos permitidos: `feat`, `fix`, `docs`, `test`, `refactor`, `chore`, `ci` y `security`.

Cuando una funcionalidad cambie la interfaz, el flujo visual o la experiencia de usuario, se debe adjuntar una captura real si ayuda a demostrar el resultado. La captura debe indicar qué se evalúa, el resultado esperado y el resultado obtenido; nunca se deben fabricar evidencias.

Todo Pull Request debe referenciar su Issue, explicar el problema y la solución, indicar los archivos principales, incluir los comandos y resultados reales de validación, documentar cambios de seguridad, accesibilidad, migraciones o variables de entorno, e incluir evidencias reales cuando corresponda. No se puede fusionar sin validaciones automáticas y revisión independiente.

Ejemplos de commits:

- `feat(editorial): agrega transición de borrador a revisión`
- `fix(auth): rechaza tokens de recuperación expirados`
- `docs(fase-0): documenta ejecución local con Docker`

## Definición de terminado

Una tarea está terminada cuando el alcance está documentado, existe issue y PR, las pruebas unitarias/integración/funcionales aplicables pasan, se cubren casos límite y regresión, la seguridad y accesibilidad fueron revisadas, la evidencia es reproducible, Docker sigue funcionando y la documentación e interfaz están en español de Chile. Las cifras y afirmaciones deben copiarse de salidas reales.

## Evidencia y arquitectura

Cada afirmación técnica debe tener prueba, recorrido o referencia verificable. Registrar decisiones en `docs/arquitectura/adr/`. Nunca fabricar capturas ni declarar pruebas exitosas sin ejecutarlas. Las pruebas visuales deben incluir identificador, requisito, esperado, obtenido, captura real con recuadro rojo y commit.

