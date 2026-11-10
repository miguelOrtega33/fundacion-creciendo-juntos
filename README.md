# 🌱 Fundación Creciendo Juntos

Plataforma ficticia de publicación y gestión editorial de noticias, creada como proyecto de portafolio profesional.

> 📚 Proyecto documental en preparación para una plataforma editorial con foco en orden, trazabilidad y accesibilidad.

## 🧭 Estado

Fase 0 técnica aprobada. El repositorio contiene un esqueleto Laravel ejecutable y la infraestructura base; todavía no contiene funcionalidades de noticias.

## 🛠️ Stack previsto

Laravel 13.x, PHP 8.3+, MySQL 8.4, Nginx, PHP-FPM, Blade, JavaScript progresivo, Docker Compose, Mailpit y PHPUnit.

## 🚀 Inicio local

Requisitos: Docker Desktop en Windows, Docker Engine y Compose en Linux, o Docker Desktop en macOS.

En cualquiera de los tres sistemas:

```bash
cp .env.example .env
docker compose build app worker
docker compose run --rm --no-deps --user root app php artisan key:generate
docker compose up -d mysql mailpit
docker compose run --rm app php artisan migrate --force
docker compose up -d app nginx worker
docker compose exec app composer test
docker compose exec app composer test:mysql
```

En PowerShell, el primer comando equivalente es:

```powershell
Copy-Item .env.example .env
```

La aplicación queda disponible en <http://localhost:8080>, Mailpit en <http://localhost:8025> y la ruta de salud en <http://localhost:8080/health>.

La clave se genera antes de iniciar los servicios persistentes. El worker comprueba además que `APP_KEY`, `jobs` y `failed_jobs` estén disponibles antes de consumir trabajos cifrados.

`composer test` ejecuta la suite rápida con SQLite en memoria. `composer test:mysql` usa la base MySQL migrada y valida los casos que dependen de su comportamiento real: consumo atómico concurrente, trabajos cifrados, reintentos de cola y recuperación ante una falla SMTP transitoria. Ambas suites son obligatorias antes de abrir un Pull Request.

Para detener el entorno:

```bash
docker compose down
```

Para conservar los datos de MySQL entre reinicios no se debe añadir `-v` al comando anterior.

Las dependencias exactas instaladas quedan fijadas en `composer.lock`.

## 🗂️ Estructura

- 📘 `docs/`: arquitectura, ADR, pruebas, evidencias y trazabilidad.
- 🗄️ `database/`: migraciones y datos ficticios de demostración.
- 🧪 `tests/`: pruebas organizadas por nivel.
- ⚙️ `.github/`: plantillas y automatización futura.

## 🎯 Alcance actual

- Fase 0 aprobada y documentada.
- Ruta `/health`, migración mínima, Docker Compose y CI base disponibles.
- Funcionalidades de noticias todavía fuera de implementación.
- Permisos detallados, imágenes, auditoría y flujo editorial reservados para fases posteriores.

## 🔐 Seguridad

No usar credenciales reales, datos personales ni contenido protegido. Las reglas obligatorias están en `AGENTS.md`.

Las contraseñas nuevas usan Argon2id. Durante la transición desde bcrypt, `HASH_VERIFY=false` permite autenticar hashes históricos y Laravel los reemplaza automáticamente por Argon2id después de un inicio de sesión correcto. Cuando se confirme que no quedan hashes bcrypt, se debe cambiar `HASH_VERIFY` a `true` para volver a exigir un único algoritmo.

## 📄 Licencia

MIT. Ver [LICENSE](LICENSE).

