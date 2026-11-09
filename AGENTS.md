# Instrucciones para agentes

## Alcance y flujo

- No hacer push directo a `main`; todo cambio va en una rama `feat/`, `fix/`, `docs/` o `chore/` y un Pull Request.
- Un Pull Request debe tratar una sola funcionalidad o decisión acotada.
- Usar commits descriptivos, trazables y en español de Chile: `tipo(ámbito): descripción`.
- Una persona distinta de quien implementó debe revisar el cambio antes de integrarlo.
- No modificar funcionalidades fuera del issue.
- No ejecutar comandos destructivos sin autorización explícita.
- Tras dos intentos fallidos con el mismo enfoque, detenerse, releer el código y replantear.

## Seguridad y calidad

Aplicar OWASP y mínimo privilegio. Usar consultas parametrizadas, CSRF, hashing seguro, sesiones protegidas, autorización del lado servidor, validación de archivos, sanitización HTML y controles de subida seguros. Nunca guardar credenciales ni datos sensibles en Git.

## Definición de terminado

Una tarea está terminada cuando el alcance está documentado, existe issue y PR, las pruebas unitarias/integración/funcionales aplicables pasan, se cubren casos límite y regresión, la seguridad y accesibilidad fueron revisadas, la evidencia es reproducible, Docker sigue funcionando y la documentación e interfaz están en español de Chile. Las cifras y afirmaciones deben copiarse de salidas reales.

## Evidencia y arquitectura

Cada afirmación técnica debe tener prueba, recorrido o referencia verificable. Registrar decisiones en `docs/arquitectura/adr/`. Nunca fabricar capturas ni declarar pruebas exitosas sin ejecutarlas. Las pruebas visuales deben incluir identificador, requisito, esperado, obtenido, captura real con recuadro rojo y commit.

