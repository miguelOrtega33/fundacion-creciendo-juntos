# Estrategia de pruebas

Se cubrirán pruebas unitarias de dominio, integración con MySQL y correo, funcionales del flujo editorial, seguridad (autenticación, autorización, CSRF, XSS, subida de archivos), accesibilidad y responsive.

Cada caso tendrá ID, requisito, precondiciones, pasos, resultado esperado y obtenido. Las pruebas visuales guardarán captura real con recuadro rojo, commit y entorno. Ningún resultado se afirmará sin ejecución.

Casos límite mínimos: entrada vacía, HTML malicioso, fecha inválida si aplica, primer y último resultado, cero resultados, archivo sobredimensionado, extensión MIME inconsistente, sesión expirada y usuario sin permiso.

