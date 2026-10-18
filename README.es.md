<p align="center">
  <a href="https://nightguardian.com.ar"><img src="public/og.png" alt="NightGuardian — tus apps se rompen a las 3 AM, vos lo sabés primero" width="640"></a>
</p>

<h1 align="center">🌙 Sitio y demo en vivo de NightGuardian</h1>

<p align="center">
  <a href="https://github.com/pablozagni/nightguardian-demo/actions/workflows/ci.yml"><img src="https://github.com/pablozagni/nightguardian-demo/actions/workflows/ci.yml/badge.svg" alt="CI"></a>
  <a href="LICENSE"><img src="https://img.shields.io/badge/license-MIT-blue.svg" alt="Licencia: MIT"></a>
  <img src="https://img.shields.io/badge/Laravel-13-ff2d20" alt="Laravel 13">
  <a href="https://nightguardian.com.ar"><img src="https://img.shields.io/badge/sitio-nightguardian.com.ar-f5b942" alt="Sitio"></a>
</p>

<p align="center">
  <a href="https://nightguardian.com.ar">Sitio</a> ·
  <a href="https://nightguardian.com.ar/try">Demo en vivo</a> ·
  <a href="https://github.com/pablozagni/nightguardian">Colector</a> ·
  <a href="https://github.com/pablozagni/nightguardian-laravel">SDK de Laravel</a> ·
  <a href="README.md">English</a>
</p>

Este repositorio es el código de **[nightguardian.com.ar](https://nightguardian.com.ar)**, el sitio público de
[NightGuardian](https://github.com/pablozagni/nightguardian) (monitoreo de errores open source y autoalojado), y de su
**demo en vivo**: una pequeña aplicación Laravel 13 con una página de botones. Cada botón hace que la app haga algo que
NightGuardian debe reportar — o que deliberadamente **no** debe reportar — para que puedas probar un colector, ver cómo
se comporta el SDK o comprobar que tus avisos funcionan.

Instala el SDK real desde Packagist (`composer require nightguardian/laravel`), igual que lo haría tu propia app.

- **`/`** — la landing, en español e inglés (selector en el encabezado; por defecto sigue al navegador). Qué es, características,
  cómo funciona, capturas, instalación en 3 pasos, SDKs, enlaces y una sección para apoyar el proyecto.
- **`/try`** — la demo en vivo: los botones de abajo.
- SEO: título, descripción, imagen Open Graph, enlaces canónicos y `hreflang`, JSON-LD y `/sitemap.xml`.

## Qué hace cada botón

| Botón | Qué hace | Qué llega al colector |
|---|---|---|
| **Lanzar una excepción** | Una excepción que nadie atrapa → HTTP 500 | Una página con código de referencia (`NG-XXXX-XXXX`) y cuadro de comentario; un issue de excepción; el comentario que envíes, asociado a él |
| **Reportar un error manejado** | `NightGuardian::report($e, [...])` con contexto | Una excepción marcada como *manejada*, con `order_id`, `gateway`, `attempt` |
| **Escribir los logs** | `Log::error()` y `Log::warning()` | El error como issue de log (el warning solo con `NIGHTGUARDIAN_LOG_LEVEL=warning`) |
| **Llamar al endpoint JSON** | Una petición que falla pidiendo JSON | Un issue de excepción; el cuerpo sigue siendo el JSON de Laravel y solo se agrega la cabecera `X-NightGuardian-Reference` |
| **Hacer una petición lenta** | Una petición de unos segundos (umbral 2 s) | Un evento de petición lenta |
| **Ejecutar una consulta lenta** | `SELECT SLEEP(1.5)` (umbral 1 s) — requiere MySQL/MariaDB | Un evento de consulta lenta con el SQL |
| **Encolar el job** / **Procesar la cola ahora** | Un job que siempre falla, en la cola `database` | Un evento de job fallido |
| **Armar la tarea que falla** / **Ejecutar el scheduler ahora** | Un comando programado (`demo:fail`) que falla una vez | Un evento de tarea programada fallida |
| **Enviar la ráfaga** | N errores idénticos (máximo 20 en el sitio público) | Un issue cuyo contador sube N |
| **Marcar un deploy** | `php artisan nightguardian:deploy` | Una marca de deploy |
| 404 / validación / 419 | Comportamiento normal de una aplicación | **Nada.** NightGuardian los ignora a propósito |

Después de cada botón la página enlaza al panel de la demo. También muestra la salida de `php artisan nightguardian:status`.

## Usala vos, contra cualquier colector

Necesitás PHP 8.3+, Composer y un colector NightGuardian con un proyecto y un token
(en el colector: *Projects → tu proyecto → Tokens → Create token*; el panel muestra las dos líneas que hacen falta).

```bash
git clone https://github.com/pablozagni/nightguardian-demo.git
cd nightguardian-demo
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite && php artisan migrate     # SQLite por defecto
```

Editá `.env`:

```dotenv
NIGHTGUARDIAN_URL=https://nightguardian.example.com     # tu colector
NIGHTGUARDIAN_TOKEN=ng_...                              # el token del proyecto
NIGHTGUARDIAN_PANEL_URL=https://nightguardian.example.com   # adónde apuntan los enlaces "abrir el panel"
```

Levantala y abrí <http://localhost:8000> (los botones están en <http://localhost:8000/try>):

```bash
php artisan serve
```

Dejá `APP_DEBUG=false` (viene así en `.env.example`): con `true`, Laravel muestra su propia página de depuración y no aparece la
página amable de NightGuardian con el código de referencia.

Los jobs y las tareas programadas que fallan se pueden ejecutar desde la página sin consola, pero así funcionan en serio:

```bash
php artisan queue:work          # procesa el job que falla, encolado con "Encolar el job"
php artisan schedule:work       # ejecuta la tarea que falla, armada con "Armar la tarea que falla"
```

El botón de consulta lenta necesita MySQL o MariaDB (`DB_CONNECTION=mysql` y una base); con SQLite explica que se omite.

## Configuración

| Variable | Por defecto | Qué hace |
|---|---|---|
| `NIGHTGUARDIAN_URL`, `NIGHTGUARDIAN_TOKEN` | — | Adónde van los eventos (el SDK está inactivo sin ellas) |
| `NIGHTGUARDIAN_PANEL_URL` | `https://demo.nightguardian.com.ar` | Enlaces al panel en el sitio y después de cada botón |
| `NIGHTGUARDIAN_DEMO_VISITOR_EMAIL` / `_PASSWORD` | `visitor@demo.example.com` / `visitor-demo` | La cuenta de solo lectura del panel, que muestra la landing |
| `DEMO_TRY_RATE_LIMIT` | `20` | Acciones por minuto y por IP en `/try` |
| `DEMO_BURST_MAX` | `20` | Ráfaga máxima |
| `DEMO_KEY` | vacía | Si se define, `/try` pide esta clave primero (para una demo privada) |
| `NIGHTGUARDIAN_SPONSOR_GITHUB_URL` | vacía | Botón "Apoyar en GitHub Sponsors" (se oculta si está vacía) |
| `NIGHTGUARDIAN_DONATE_MP_URL` | vacía | Botón "Donar con Mercado Pago" y su QR (se oculta si está vacía) |
| `NIGHTGUARDIAN_SITE_URL` | `https://nightguardian.com.ar` | Enlaces canónicos, Open Graph y sitemap |

Usá un proyecto descartable en tu colector: la demo crea issues y avisos reales. Para un sitio público, corré el colector en
[modo demo](https://github.com/pablozagni/nightguardian/blob/main/docs/demo-mode.md) (visitante de solo lectura, reinicio
nocturno, límite de ingest más bajo).

## Notas

- Los eventos se envían **después** de la respuesta, así que aparecen en el colector un instante después de apretar un botón. Si el
  colector está caído, el SDK los guarda en `storage/nightguardian/spool` y los envía luego; `nightguardian:status` lo muestra.
- Los tests (`vendor/bin/pest`) corren con el SDK apagado: nunca contactan a un colector.

## Licencia

MIT, ver [LICENSE](LICENSE). Hecho en Argentina por [z.system](https://www.z-system.com.ar).
