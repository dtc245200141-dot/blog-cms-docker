# Blog CMS Docker Compose Lab (MacBook Air M1)

A single `docker-compose.yml` orchestrates WordPress, MySQL, phpMyAdmin, non-root Nginx with self-signed HTTPS, Prometheus, Grafana, Loki, Promtail, a custom Vietnamese WordPress theme, and metrics exporters.

> **Coursework/lab use only.** Promtail reached EOL on 2026-03-02; it is included because the assignment explicitly requires it. Grafana recommends Grafana Alloy for supported log collection. The self-signed certificate and blackbox `insecure_skip_verify` are for the isolated local lab only. Loki has no built-in authentication in this configuration and is deliberately not published to the host.


## 1.1. Blog website interface

The project includes a responsive Vietnamese WordPress theme at `wordpress/themes/blog-cms-theme/` (not just the infrastructure stack). The design includes a magazine-style homepage, featured-post area, topic links, latest-post cards, article detail pages, archive/search pages, a mobile navigation menu, and a footer. It uses local CSS/JS only and does not depend on a remote font/CDN.

The WordPress startup wrapper copies the supplied theme into the persistent `wordpress_data` volume. A small must-use plugin selects the theme once WordPress installation is complete and gives a fresh site the default title `Góc Nhìn`; users can change these through **Settings → General**. On first run, finish the WordPress setup wizard at `https://localhost:8443`, then refresh the homepage.

To make the homepage look complete, sign in at `https://localhost:8443/wp-admin/`, choose **Posts → Add New**, and publish at least 3 posts. Assign categories and set a Featured image in each post; the homepage and cards render these automatically. Add navigation links at **Appearance → Menus** (or **Appearance → Editor**, depending on the WordPress version). The included UI is a theme layer; it does not replace WordPress admin/CMS.

## 1. Image and ARM64 choices

| Service | Image | Reason / architecture note |
|---|---|---|
| WordPress | `wordpress:7.1.3-php8.3-apache` | Official image with Apache/PHP; official WordPress tags publish ARM64 variants. The Apache image is convenient for a beginner CMS lab. |
| MySQL | `mysql:8.4.11` + `platform: linux/amd64` | Official MySQL image. Forced to amd64 as required; Docker Desktop uses x86 emulation on M1, so expect lower performance than native ARM64. |
| phpMyAdmin | `phpmyadmin:5.2.3-apache` | Official image explicitly supports `arm64v8`. Published only to `127.0.0.1`. |
| Nginx | `nginxinc/nginx-unprivileged:1.28-alpine` | NGINX-maintained unprivileged image; runs without binding privileged ports and has ARM64 builds. |
| Prometheus | `prom/prometheus:v3.15.0` | Official Prometheus image includes `linux/arm64`. |
| Grafana | `grafana/grafana:12.4.11` | Official Grafana image includes `linux/arm64`; provisions dashboard/data sources at startup. |
| Loki | `grafana/loki:3.7.8` | Grafana-maintained Loki image; use local filesystem storage for this single-host demo. |
| Promtail | `grafana/promtail:3.6.11` | Includes ARM64. This version is retained for the assignment, although Promtail is EOL. |
| Docker metrics exporter | `ghcr.io/dlepaux/docker-exporter:latest` | Multi-arch `linux/amd64` and `linux/arm64`; reads CPU, memory and network stats from the Docker Engine API, avoiding cAdvisor-style Linux host-path mounts on macOS. The `latest` tag is mutable, so pin a digest for stricter reproducibility. |
| Nginx exporter | `nginx/nginx-prometheus-exporter:1.4.2` | Small official exporter; publishes multi-architecture images. |
| MySQL exporter | `prom/mysqld-exporter:v0.20.0` | Prometheus exporter image includes `linux/arm64`; uses a dedicated account with limited grants. |
| Blackbox exporter | `prom/blackbox-exporter:v0.28.0` | Official Prometheus exporter supports ARM64 and probes WordPress over HTTPS. |

Tags are pinned to versions rather than `latest` to make the lab more reproducible. Before a future submission, review vendor release/security notes and update version pins deliberately.

## 2. Requirements

- MacBook Air M1/M2 and current Docker Desktop for Mac.
- Docker Compose plugin (`docker compose`, not the legacy `docker-compose`).
- OpenSSL available in Terminal.
- Allocate enough resources to Docker Desktop: for example, 4 CPU cores and 5–6 GB RAM if the Mac has enough memory. MySQL is deliberately emulated as `linux/amd64`; this can be slower on Apple Silicon.

## 3. Prepare secrets and TLS certificate

From this directory:

```bash
cp .env.example .env
openssl rand -hex 24
```

Run `openssl rand -hex 24` several times and paste a different output into each `REPLACE_WITH_...` field in `.env`. Do not commit `.env`.

Generate a self-signed certificate with localhost SANs:

```bash
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout nginx/certs/localhost.key \
  -out nginx/certs/localhost.crt \
  -config nginx/certs/localhost.cnf \
  -extensions v3_req
chmod 600 nginx/certs/localhost.key
```

## 4. Validate and start

```bash
docker compose config --quiet
docker compose pull
docker compose up -d
docker compose ps
```

First startup can take a few minutes because MySQL is an amd64 image and database initialization must finish before WordPress. Check logs if needed:

```bash
docker compose logs -f mysql wordpress nginx
```

Open:

- WordPress blog interface (HTTPS): <https://localhost:8443>
- WordPress admin/CMS: <https://localhost:8443/wp-admin/>
- HTTP redirect test: <http://localhost:8080>
- phpMyAdmin: <http://localhost:8082>
- Prometheus: <http://localhost:9090>
- Grafana: <http://localhost:3000>

The browser will warn about the self-signed certificate. For this local lab, choose the browser's advanced option to proceed. Do not reuse this certificate for public production.

Grafana login: the user/password values in `.env` (`GRAFANA_ADMIN_USER`, `GRAFANA_ADMIN_PASSWORD`). Grafana automatically provisions Prometheus and Loki sources and the `Blog CMS - Overview` dashboard.

For phpMyAdmin, sign in with the WordPress database user (`WORDPRESS_DB_USER`) and `WORDPRESS_DB_PASSWORD`; do not use the MySQL root account for routine work.

## 5. Network isolation

- `frontend`: Nginx, WordPress, and the Nginx/blackbox exporters. Only Nginx has host-published blog ports. The public HTTP listener on 8080 redirects to HTTPS; the Nginx `stub_status` endpoint listens on internal port 8081 and is not published to the host.
- `backend`: WordPress, MySQL, phpMyAdmin and MySQL exporter. MySQL has no published host port.
- `monitoring`: Prometheus, Grafana, cAdvisor and exporters. Exporters are multi-homed to both `monitoring` and their target's network.
- `logging`: Loki and Promtail, plus Grafana/Prometheus to query Loki and scrape log-stack metrics. Network membership is intentionally limited.

WordPress must join both `frontend` and `backend` because it receives reverse-proxied requests and connects to MySQL. phpMyAdmin is backend-only and its port binds to `127.0.0.1`, so it is not exposed on a LAN interface.

## 6. What is monitored

- **Containers:** Docker exporter metrics such as `container_cpu_usage_seconds_total` and `container_memory_working_set_bytes`, queried from the Docker Engine API. These describe Docker containers, not native macOS processes.
- **Nginx:** `nginx_connections_active`, `nginx_http_requests_total` from `nginx-exporter` reading `stub_status`.
- **MySQL:** `mysql_global_status_threads_connected`, `mysql_global_status_questions`, `mysql_up` from `mysqld-exporter` and the dedicated `exporter` account.
- **WordPress:** WordPress has no native Prometheus endpoint in this image, so Blackbox Exporter probes `https://nginx:8443/wp-login.php` and provides `probe_success` and request latency metrics. The probe can stay down until the initial WordPress installation has completed.

## 7. Log collection on Docker Desktop for Mac

Promtail tails **shared named volumes** instead of relying on Linux-only `/var/lib/docker/containers` paths or the Docker socket. This is more portable for Docker Desktop on macOS and avoids giving Promtail control-plane access to Docker. The Docker metrics exporter separately mounts the Docker socket to read stats; socket access is highly sensitive even when the bind mount is marked read-only, so keep this project local to your own trusted machine.

- Nginx writes `access.log` and `error.log` to `nginx_logs`.
- WordPress/PHP writes `debug.log` to `wordpress_logs`, outside the public web root.
- MySQL writes `error.log` to `mysql_logs`.
- Promtail tails those volumes read-only and pushes to Loki over the `logging` network.

Inspect logs and Loki readiness:

```bash
docker compose logs --tail=100 promtail loki mysql nginx wordpress
docker compose exec loki wget -qO- http://127.0.0.1:3100/ready
```

## 8. LogQL demo

Open Grafana → **Explore** → choose the **Loki** data source. Run:

```logql
# 1. All Nginx access log lines
{job="nginx", log_type="access"}

# 2. HTTP 5xx responses (status is extracted as a low-cardinality label)
{job="nginx", log_type="access", status=~"5.."}

# 3. Requests to the WordPress login page
{job="nginx", log_type="access"} |= "/wp-login.php"

# 4. Nginx error log lines
{job="nginx", log_type="error"}

# 5. PHP/WordPress debug log lines
{job="wordpress", log_type="php"}

# 6. MySQL error log lines
{job="mysql", log_type="error"}

# 7. Count 5xx responses during the last 5 minutes, grouped by status
sum by (status) (count_over_time({job="nginx", log_type="access", status=~"5.."}[5m]))
```

If the LogQL query returns no data, generate some web requests first, refresh the Explore time range, and check `docker compose logs promtail` for file-permission or path errors.

## 9. Verification checklist

```bash
# Service/container states
docker compose ps -a

# HTTP should return a 301 redirect
curl -I http://localhost:8080

# HTTPS response; -k accepts the self-signed lab certificate
curl -k -I https://localhost:8443

# Verify security headers
curl -k -I https://localhost:8443 | grep -Ei 'strict-transport-security|x-frame-options|x-content-type-options|referrer-policy|content-security-policy'

# Prometheus health and targets
curl -fsS http://localhost:9090/-/healthy
# Open http://localhost:9090/targets and confirm the expected jobs show UP.
```

In Grafana, open **Dashboards → Blog CMS → Blog CMS - Overview**.

## 10. Stop and clean up

Stop while preserving all database/site/metrics/log data:

```bash
docker compose down
```

Delete the services and **all persistent data** (irreversible reset):

```bash
docker compose down -v
```

Do not run the second command unless you intentionally want to reset the complete lab.
