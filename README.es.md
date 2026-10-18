# 🌙 NightGuardian demo

[English](README.md)

Una pequeña aplicación Laravel 13 con **una sola página llena de botones**. Cada botón hace que la app haga algo que
[NightGuardian](https://github.com/pablozagni/nightguardian) debe reportar — o que deliberadamente **no** debe reportar.
Sirve para probar un colector, ver cómo se comporta el SDK o comprobar que tus avisos funcionan.

Instala el SDK real desde Packagist (`composer require nightguardian/laravel`), igual que lo haría tu propia app.

## Qué hace cada botón

| Botón | Qué hace | Qué llega al colector |
|---|---|---|
| **Throw an exception** | Una excepción que nadie atrapa → HTTP 500 | Una página con código de referencia (`NG-XXXX-XXXX`) y cuadro de comentario; un issue de excepción; el comentario que envíes, asociado a él |
| **Report a handled error** | `NightGuardian::report($e, [...])` con contexto | Una excepción marcada como *manejada*, con `order_id`, `gateway`, `attempt` |
| **Write the logs** | `Log::error()` y `Log::warning()` | El error como issue de log (el warning solo con `NIGHTGUARDIAN_LOG_LEVEL=warning`) |
| **Call the JSON endpoint** | Una petición que falla pidiendo JSON | Un issue de excepción; el cuerpo sigue siendo el JSON de Laravel y solo se agrega la cabecera `X-NightGuardian-Reference` |
| **Make a slow request** | Petición de ~2,5 s (umbral 2 s) | Un evento de petición lenta |
| **Run a slow query** | `SELECT SLEEP(1.5)` (umbral 1 s) — requiere MySQL/MariaDB | Un evento de consulta lenta con el SQL |
| **Queue the job** / **Process the queue now** | Un job que siempre falla, en la cola `database` | Un evento de job fallido |
| **Arm the failing task** / **Run the scheduler now** | Un comando programado (`demo:fail`) que falla una vez | Un evento de tarea programada fallida |
| **Send the burst** | N errores idénticos (1–200) | Un issue cuyo contador sube N — usá 50 o más para probar el umbral de avisos |
| **Mark a deploy** | `php artisan nightguardian:deploy` | Una marca de deploy |
| 404 / validación / 419 | Comportamiento normal de una aplicación | **Nada.** NightGuardian los ignora a propósito |

Al final de la página se muestra la salida de `php artisan nightguardian:status`.

## Inicio rápido

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
```

Levantala y abrí <http://localhost:8000>:

```bash
php artisan serve
```

Dejá `APP_DEBUG=false` (viene así en `.env.example`): con `true`, Laravel muestra su propia página de depuración y no
aparece la página amable de NightGuardian con el código de referencia.

### Jobs y tareas programadas que fallan

La página tiene botones para ejecutarlos sin consola, pero así funciona en serio. En dos consolas más:

```bash
php artisan queue:work          # procesa el job que falla, encolado con "Queue the job"
php artisan schedule:work       # ejecuta la tarea que falla, armada con "Arm the failing task"
```

### El botón de consulta lenta

`SELECT SLEEP()` necesita MySQL o MariaDB. En `.env` definí `DB_CONNECTION=mysql`, `DB_DATABASE`, `DB_USERNAME` y
`DB_PASSWORD`, creá la base y corré `php artisan migrate`. Con SQLite el botón explica que se omite.

## Publicarla en un servidor

Si publicás la demo, **definí una clave** para que nadie más llene tu colector de errores:

```dotenv
DEMO_KEY=una-cadena-larga-y-aleatoria
```

Con `DEMO_KEY`, todas las páginas piden la clave primero (5 intentos por minuto). Vacía, no pide nada: para uso local.
Usá un proyecto descartable en tu colector: la demo crea issues y avisos reales y, con una ráfaga, muchísimos eventos.

## Notas

- Los eventos se envían **después** de la respuesta, así que aparecen en el colector un instante después de apretar el
  botón. Si el colector está caído, el SDK los guarda en `storage/nightguardian/spool` y los envía luego;
  `nightguardian:status` lo muestra.
- El SDK registra sus comandos Artisan solo en consola. La página ejecuta `nightguardian:status` y `nightguardian:deploy`
  desde una petición web, por eso `AppServiceProvider` los registra también para la web.
- Los tests (`vendor/bin/pest`) corren con el SDK apagado: nunca contactan a un colector.

## Licencia

MIT, ver [LICENSE](LICENSE).
