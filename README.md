# 📊 Sistema de Login con Monitoreo Prometheus + Grafana

> Laboratorio práctico para la electiva de **Ciberseguridad**  
> Monitoreo de intentos de login, detección de ataques de fuerza bruta y visualización en tiempo real.


## 🎯 Objetivos de aprendizaje

Al finalizar este laboratorio, los estudiantes serán capaces de:

- Instrumentar una aplicación PHP para exponer métricas personalizadas
- Configurar Prometheus para scrapear endpoints HTTP
- Visualizar métricas en Grafana y crear dashboards
- Detectar ataques de fuerza bruta mediante consultas PromQL
- Entender la importancia del monitoreo en ciberseguridad
- Implementar Redis como sistema de persistencia para métricas

## 📂 Estructura del Proyecto

```
monitoreo_de_login/
├── composer.json              # Dependencias PHP
├── databases.sql              # Esquema de base de datos
├── bruteforce.py              # Script de ataque (opcional)
├── config/
│   └── database.php           # Conexión a MySQL
├── monitor/
│   └── PrometheusMonitor.php  # Métricas personalizadas
├── public/
│   ├── index.php              # Redirige a login
│   ├── login.php              # Procesa autenticación
│   ├── register.php           # Registro de usuarios
│   ├── dashboard.php          # Área protegida
│   ├── logout.php             # Cierre de sesión
│   ├── metrics.php            # Endpoint para Prometheus
│   └── style.css              # Estilos visuales
├── vendor/                    # Dependencias (Composer)
└── storage/                   # Almacenamiento de métricas (Redis)
```

---

## 📖 Descripción del Proyecto

Este sistema de autenticación desarrollado en PHP permite:

- ✅ Registro de usuarios con contraseñas cifradas (`password_hash`)
- ✅ Inicio de sesión con verificación segura (`password_verify`)
- ✅ Monitoreo en tiempo real de intentos de login (fallidos y exitosos)
- ✅ Visualización de métricas en Prometheus y Grafana
- ✅ Detección de ataques de fuerza bruta mediante consultas PromQL
- ✅ Persistencia de métricas con Redis
- ✅ Registro de auditoría en base de datos MySQL

---

## 📋 Requisitos Previos (Windows)

| Herramienta | Versión | Enlace de descarga |
|-------------|---------|-------------------|
| **XAMPP** | 8.x o superior | [Descargar](https://www.apachefriends.org/) |
| **Composer** | 2.x | [Descargar](https://getcomposer.org/) |
| **Prometheus** | 3.x (windows-amd64) | [Descargar](https://prometheus.io/download/) |
| **Windows Exporter** | 0.26+ | [Descargar](https://github.com/prometheus-community/windows_exporter/releases) |
| **Grafana** | 10.x (Windows .msi) | [Descargar](https://grafana.com/grafana/download) |
| **Redis** | 3.x (Windows) | [Descargar](https://github.com/microsoftarchive/redis/releases) |
| **Redis PHP Extension** | 6.3.0 para PHP 8.2 | [Descargar](https://windows.php.net/downloads/pecl/releases/redis/6.3.0/) |

---

## 🚀 Instalación Paso a Paso

### 1️⃣ Clonar el repositorio

```bash
git clone https://github.com/RodrigoSu1209/monitoreo_de_login.git
```

### 2️⃣ Ubicar el proyecto en XAMPP

Copia la carpeta clonada dentro de `C:\xampp\htdocs\`

```
C:\xampp\htdocs\monitoreo_de_login\
```

### 3️⃣ Instalar dependencias PHP con Composer

```bash
cd C:\xampp\htdocs\monitoreo_de_login
composer install
```

**Si no tienes Composer:** Descárgalo desde [getcomposer.org](https://getcomposer.org/)

### 4️⃣ Configurar la base de datos MySQL

1. Abre **XAMPP Control Panel** e inicia **Apache** y **MySQL**
2. Abre phpMyAdmin: `http://localhost/phpmyadmin`
3. Crea una base de datos: `login_system`
4. Importa el archivo `databases.sql` que está en la raíz del proyecto
5. La conexión a la base de datos está configurada en `config/database.php` con:
   - Servidor: `localhost`
   - Usuario: `root`
   - Contraseña: `''` (vacía)

### 5️⃣ Instalar Redis en Windows

1. Descarga el instalador desde: https://github.com/microsoftarchive/redis/releases
2. Ejecuta `Redis-x64-3.0.504.msi`
3. Durante la instalación, marca **"Add Redis to the PATH"**
4. Verifica que Redis está corriendo:
   ```cmd
   redis-cli ping
   ```
   Deberías ver: `PONG`

### 6️⃣ Instalar la extensión Redis para PHP

1. Ve a: https://windows.php.net/downloads/pecl/releases/redis/6.3.0/
2. Descarga el archivo que coincida con tu PHP:
   - Para XAMPP (PHP 8.2 Thread Safe): `php_redis-6.3.0-8.2-ts-vs16-x64.zip`
3. Extrae el archivo `php_redis.dll`
4. Copia el archivo a: `C:\xampp\php\ext\`
5. Abre `C:\xampp\php\php.ini` y agrega al final:
   ```ini
   extension=redis
   ```
6. Reinicia Apache desde XAMPP

### 7️⃣ Verificar la instalación de Redis en PHP

Crea un archivo `info.php` dentro de `public/`:

```php
<?php
phpinfo();
```

Visita `http://localhost/monitoreo_de_login/public/info.php` y busca la sección **redis**. Si aparece, la instalación fue exitosa.

**Elimina este archivo después de verificar.**

---

## 🔧 Configuración de Prometheus

### 1️⃣ Descargar Prometheus

Descarga la versión para Windows desde [prometheus.io/download](https://prometheus.io/download/).

### 2️⃣ Ubicar Prometheus

Descomprime el archivo en `C:\prometheus\`

### 3️⃣ Configurar `prometheus.yml`

Edita el archivo `C:\prometheus\prometheus.yml` y agrega este job:

```yaml
global:
  scrape_interval: 15s
  evaluation_interval: 15s

scrape_configs:
  # Monitoreo de Prometheus mismo
  - job_name: "prometheus"
    static_configs:
      - targets: ["localhost:9090"]

  # Monitoreo del sistema Windows
  - job_name: "windows_exporter"
    static_configs:
      - targets: ["localhost:9182"]

  # Monitoreo de la aplicación de login
  - job_name: "login_system"
    scrape_interval: 5s
    metrics_path: /monitoreo_de_login/public/metrics.php
    static_configs:
      - targets: ["localhost:80"]
        labels:
          app: "login-system"
          environment: "development"
```

### 4️⃣ Iniciar Prometheus

```cmd
cd C:\prometheus
prometheus.exe
```

### 5️⃣ Verificar el target

Abre `http://localhost:9090/targets` y confirma que `login_system` está en estado **UP**.

---

## 📊 Configuración de Grafana

### 1️⃣ Descargar e instalar Grafana

Descarga el instalador `.msi` desde [grafana.com](https://grafana.com/grafana/download).

### 2️⃣ Iniciar Grafana

Grafana se instala como servicio de Windows. Accede a:

```
http://localhost:3000
```

Credenciales por defecto:
- Usuario: `admin`
- Contraseña: `admin` (te pedirá cambiarla)

### 3️⃣ Conectar Grafana con Prometheus

1. Ve a **Configuration** (⚙️) > **Data Sources** > **Add data source**
2. Selecciona **Prometheus**
3. En URL escribe: `http://localhost:9090`
4. Haz clic en **Save & Test**

### 4️⃣ Importar un Dashboard

1. Ve a **Dashboards** > **Import**
2. En "Import via grafana.com", escribe: `10467` (Dashboard para Windows Exporter)
3. Selecciona tu fuente de datos Prometheus
4. Haz clic en **Import**

---
## ❓ Solución de problemas comunes

| Problema | Causa | Solución |
|----------|-------|----------|
| `metrics.php` muestra solo `php_info` | Redis no está corriendo | Inicia Redis: `redis-server` |
| Prometheus target DOWN | XAMPP no está corriendo | Inicia Apache en XAMPP |
| Error de conexión a MySQL | Credenciales incorrectas | Revisa `config/database.php` |
| Extensión Redis no cargada | `php.ini` sin `extension=redis` | Agrega la línea y reinicia Apache |
| Contadores no aumentan | No se han generado eventos | Haz intentos de login fallidos |
| Redis no persiste datos | Redis no está corriendo | `redis-cli ping` debe devolver `PONG` |

---

## 🧪 Probar el Sistema

### 1️⃣ Verificar la aplicación web

Abre `http://localhost/monitoreo_de_login/public/login.php`

### 2️⃣ Registrar un usuario

1. Haz clic en **"Registrate"**
2. Completa el formulario
3. Verifica el mensaje de éxito

### 3️⃣ Probar login exitoso

1. Ingresa con las credenciales registradas
2. Verifica que te redirige al dashboard

### 4️⃣ Probar login fallido

1. Ingresa con credenciales incorrectas
2. Verifica el mensaje de error

### 5️⃣ Verificar métricas en Prometheus

1. Ve a `http://localhost:9090/graph`
2. Escribe: `login_failures_total`
3. Haz clic en **Execute**

### 6️⃣ Verificar métricas en Grafana

1. Ve a `http://localhost:3000`
2. Explora los dashboards importados
3. Crea un panel con la consulta: `login_failures_total`

---

## 🔍 Consultas PromQL útiles

| Propósito | Consulta |
|-----------|----------|
| Ver todos los intentos fallidos | `login_failures_total` |
| Intentos fallidos por usuario | `sum(login_failures_total) by (username)` |
| Intentos fallidos por IP | `sum(login_failures_total) by (ip)` |
| Tasa de fallos por minuto | `rate(login_failures_total[1m])` |
| Logins exitosos | `login_success_total` |
| Sesiones activas | `login_active_sessions` |
| Detectar ataque (más de 10 fallos/min) | `rate(login_failures_total[1m]) > 10` |

---

## 📚 Recursos adicionales

- [Documentación de Prometheus](https://prometheus.io/docs/introduction/overview/)
- [Documentación de Grafana](https://grafana.com/docs/grafana/latest/)
- [Librería PHP para Prometheus](https://github.com/promphp/prometheus_client_php)
- [Redis para Windows](https://github.com/microsoftarchive/redis)


## 📄 Licencia

<a href="https://ejemplo.com"><font dir="auto" style="vertical-align: inherit;"><font dir="auto" style="vertical-align: inherit;">Electiva Ciberseguridad</font></font></a><font dir="auto" style="vertical-align: inherit;"><font dir="auto" style="vertical-align: inherit;"> © 2026 de </font></font><a href="https://ejemplo.com"><font dir="auto" style="vertical-align: inherit;"><font dir="auto" style="vertical-align: inherit;">Educación</font></font></a><font dir="auto" style="vertical-align: inherit;"><font dir="auto" style="vertical-align: inherit;"> tiene licencia </font></font><a href="https://creativecommons.org/licenses/by-nc-sa/4.0/"><font dir="auto" style="vertical-align: inherit;"><font dir="auto" style="vertical-align: inherit;">CC BY-NC-SA 4.0</font></font>
<br>
<br>
</a><img src="https://mirrors.creativecommons.org/presskit/icons/cc.svg" alt="" style="max-width: 1em;max-height:1em;margin-left: .2em;"><img src="https://mirrors.creativecommons.org/presskit/icons/by.svg" alt="" style="max-width: 1em;max-height:1em;margin-left: .2em;"><img src="https://mirrors.creativecommons.org/presskit/icons/nc.svg" alt="" style="max-width: 1em;max-height:1em;margin-left: .2em;"><img src="https://mirrors.creativecommons.org/presskit/icons/sa.svg" alt="" style="max-width: 1em;max-height:1em;margin-left: .2em;">
