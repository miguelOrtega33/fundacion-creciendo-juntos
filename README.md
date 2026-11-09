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
docker compose up -d --build
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --force
docker compose exec app composer test
```

En PowerShell, el primer comando equivalente es:

```powershell
Copy-Item .env.example .env
```

La aplicación queda disponible en <http://localhost:8080>, Mailpit en <http://localhost:8025> y la ruta de salud en <http://localhost:8080/health>.

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

## 📄 Licencia

MIT. Ver [LICENSE](LICENSE).

