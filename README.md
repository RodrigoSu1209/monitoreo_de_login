# Login System Monitoreo

Sistema de autenticacion en PHP con MySQL y metricas para Prometheus.

## Requisitos

- XAMPP con Apache, PHP y MySQL.
- Composer.
- Redis activo y la extension Redis habilitada en PHP.
- Git, si se instala desde el repositorio.

## Instalacion

1. Clona el repositorio dentro de `C:\xampp\htdocs`:

	```bash
	git clone https://github.com/RodrigoSu1209/monitoreo_de_login.git
	cd monitoreo_de_login
	```

2. Instala las dependencias de Composer:

	```bash
	composer install
	```

	Esto crea la carpeta `vendor/`, que no se almacena en Git.

3. Crea la base de datos. En phpMyAdmin, importa [databases.sql](databases.sql), o ejecuta:

	```bash
	mysql -u root -p < databases.sql
	```

4. Crea el archivo `config/database.php`. Este archivo no se publica porque contiene la configuracion local de la base de datos:

	```php
	<?php
	$pdo = new PDO(
		 'mysql:host=localhost;dbname=login_system;charset=utf8mb4',
		 'root',
		 ''
	);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	```

	Cambia el usuario, la contrasena o el host si tu instalacion de MySQL es diferente.

5. Inicia Apache, MySQL y Redis.

## Uso

Abre en el navegador:

```text
http://localhost/monitoreo_de_login/public/
```

Desde ahi se puede registrar un usuario, iniciar sesion y acceder al dashboard.

El endpoint de metricas esta disponible en:

```text
http://localhost/monitoreo_de_login/public/metrics.php
```

## Estructura

```text
monitoreo_de_login/
├── composer.json
├── databases.sql
├── config/
│   └── database.php       # Se crea localmente
├── monitor/
│   └── PrometheusMonitor.php
├── public/
│   ├── index.php
│   ├── login.php
│   ├── register.php
│   ├── dashboard.php
│   ├── logout.php
│   ├── metrics.php
│   └── style.css
└── vendor/                # Se genera con composer install
```

## Notas

- `config/database.php`, `vendor/` y `.gitignore` no forman parte del repositorio remoto.
- Redis es necesario porque el monitor usa almacenamiento persistente para las metricas.
- No publiques contrasenas, tokens ni archivos de configuracion con datos reales.