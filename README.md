# Đồ án thực hành Blog CMS với Docker Compose (MacBook Air M1)

Dự án sử dụng một tệp `docker-compose.yml` để điều phối WordPress, MySQL, phpMyAdmin, Nginx chạy với người dùng không phải `root` và HTTPS tự ký, Prometheus, Grafana, Loki, Promtail, giao diện WordPress tiếng Việt tùy chỉnh và các bộ xuất số liệu giám sát (*exporter*).

> **Chỉ dùng cho đồ án/thực hành.** Promtail đã kết thúc vòng đời hỗ trợ vào ngày 02/03/2026 nhưng được giữ lại vì đề bài yêu cầu. Grafana khuyến nghị sử dụng Grafana Alloy cho việc thu thập log được hỗ trợ. Chứng chỉ tự ký và tùy chọn `insecure_skip_verify` của Blackbox Exporter chỉ dành cho môi trường thực hành cục bộ cô lập. Trong cấu hình này, Loki không có xác thực tích hợp và không công bố cổng ra máy chủ.

## 1.1. Giao diện website Blog

Dự án có giao diện WordPress tiếng Việt thích ứng với kích thước màn hình tại `wordpress/themes/blog-cms-theme/`, không chỉ có phần hạ tầng. Giao diện gồm trang chủ kiểu tạp chí, khu vực bài viết nổi bật, liên kết chủ đề, thẻ bài viết mới nhất, trang chi tiết bài viết, trang lưu trữ/tìm kiếm, menu điều hướng trên thiết bị di động và chân trang. Giao diện chỉ sử dụng CSS/JavaScript cục bộ, không phụ thuộc phông chữ hoặc CDN bên ngoài.

Tập lệnh khởi động WordPress sao chép giao diện được cung cấp vào volume lưu trữ bền vững `wordpress_data`. Một plugin *must-use* nhỏ sẽ chọn giao diện sau khi cài đặt WordPress hoàn tất và đặt tiêu đề mặc định cho trang mới là `Góc Nhìn`. Bạn có thể thay đổi các giá trị này tại **Settings → General** (Cài đặt → Tổng quan). Trong lần chạy đầu tiên, hãy hoàn tất trình hướng dẫn cài đặt WordPress tại `https://localhost:8443`, sau đó tải lại trang chủ.

Để trang chủ có nội dung đầy đủ, hãy đăng nhập tại `https://localhost:8443/wp-admin/`, chọn **Posts → Add New** (Bài viết → Viết bài mới) và xuất bản ít nhất 3 bài viết. Gán danh mục và đặt ảnh đại diện (*Featured image*) cho mỗi bài; trang chủ và các thẻ bài viết sẽ tự hiển thị những nội dung này. Thêm liên kết điều hướng tại **Appearance → Menus** hoặc **Appearance → Editor**, tùy phiên bản WordPress. Giao diện đi kèm chỉ là lớp giao diện; nó không thay thế trang quản trị/CMS của WordPress.

## 1. Lựa chọn image và kiến trúc ARM64

| Dịch vụ | Image | Lý do / lưu ý về kiến trúc |
|---|---|---|
| WordPress | `wordpress:7.1.3-php8.3-apache` | Image chính thức có Apache/PHP; các tag WordPress chính thức có biến thể ARM64. Image Apache phù hợp cho đồ án CMS cơ bản. |
| MySQL | `mysql:8.4.11` và `platform: linux/amd64` | Image MySQL chính thức. Ép chạy amd64 theo yêu cầu đề bài; Docker Desktop giả lập x86 trên máy M1 nên có thể chậm hơn ARM64 gốc. |
| phpMyAdmin | `phpmyadmin:5.2.3-apache` | Image chính thức; cổng chỉ được công bố trên địa chỉ loopback `127.0.0.1`. |
| Nginx | `nginxinc/nginx-unprivileged:1.28-alpine` | Image Nginx chạy không đặc quyền; không cần bind cổng đặc quyền và có bản dựng ARM64. |
| Prometheus | `prom/prometheus:v3.15.0` | Image Prometheus chính thức, có bản `linux/arm64`. |
| Grafana | `grafana/grafana:12.4.11` | Image Grafana chính thức, có bản `linux/arm64`; tự cấu hình nguồn dữ liệu và dashboard khi khởi động. |
| Loki | `grafana/loki:3.7.8` | Image Loki do Grafana duy trì; dùng lưu trữ hệ thống tệp cục bộ cho bản demo một máy. |
| Promtail | `grafana/promtail:3.6.11` | Có bản ARM64. Giữ phiên bản này để đáp ứng yêu cầu đồ án dù Promtail đã kết thúc vòng đời hỗ trợ. |
| Docker metrics exporter | `ghcr.io/dlepaux/docker-exporter:latest` | Đọc số liệu CPU, bộ nhớ và mạng của container qua Docker Engine API, tránh một số kiểu mount đường dẫn hệ thống Linux như cAdvisor thường dùng vốn không phù hợp trên macOS. Tag `latest` có thể thay đổi; có thể ghim digest nếu cần khả năng tái lập chặt chẽ hơn. |
| Nginx exporter | `nginx/nginx-prometheus-exporter:1.4.2` | Exporter gọn nhẹ để xuất số liệu Nginx. |
| MySQL exporter | `prom/mysqld-exporter:v0.20.0` | Exporter của Prometheus, sử dụng tài khoản riêng với quyền giới hạn. |
| Blackbox exporter | `prom/blackbox-exporter:v0.28.0` | Exporter của Prometheus dùng để kiểm tra khả năng truy cập WordPress qua HTTPS. |

Các image được ghim phiên bản thay vì dùng `latest` nhằm tăng khả năng tái lập môi trường, ngoại trừ Docker metrics exporter đang dùng tag `latest`. Trước khi nộp đồ án hoặc cập nhật trong tương lai, hãy xem ghi chú phát hành và bảo mật của nhà cung cấp, rồi chủ động thay đổi phiên bản.

## 2. Yêu cầu

- MacBook Air M1/M2 và Docker Desktop for Mac phiên bản hiện hành.
- Docker Compose plugin (`docker compose`, không phải lệnh cũ `docker-compose`).
- Có OpenSSL trong Terminal.
- Cấp đủ tài nguyên cho Docker Desktop. Ví dụ: 4 lõi CPU và 5–6 GB RAM nếu máy có đủ bộ nhớ. MySQL được chạy giả lập `linux/amd64` theo yêu cầu nên có thể chậm hơn trên Apple Silicon.

## 3. Chuẩn bị thông tin bí mật và chứng chỉ TLS

Thực hiện trong thư mục dự án:

```bash
cp .env.example .env
openssl rand -hex 24
```

Chạy `openssl rand -hex 24` nhiều lần và dán mỗi kết quả khác nhau vào từng trường `REPLACE_WITH_...` trong file `.env`. **Không commit file `.env` lên Git.**

Tạo chứng chỉ tự ký có SAN cho `localhost`:

```bash
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout nginx/certs/localhost.key \
  -out nginx/certs/localhost.crt \
  -config nginx/certs/localhost.cnf \
  -extensions v3_req
chmod 600 nginx/certs/localhost.key
```

## 4. Kiểm tra cấu hình và khởi động

```bash
docker compose config --quiet
docker compose pull
docker compose up -d
docker compose ps
```

Lần khởi động đầu tiên có thể mất vài phút vì MySQL chạy image amd64 và quá trình khởi tạo cơ sở dữ liệu phải hoàn tất trước khi WordPress hoạt động. Nếu cần, xem log:

```bash
docker compose logs -f mysql wordpress nginx
```

### Địa chỉ truy cập

- Giao diện blog WordPress (HTTPS): <https://localhost:8443>
- Trang quản trị WordPress/CMS: <https://localhost:8443/wp-admin/>
- Kiểm tra chuyển hướng HTTP: <http://localhost:8080>
- phpMyAdmin: <http://localhost:8082>
- Prometheus: <http://localhost:9090>
- Grafana: <http://localhost:3000>

Trình duyệt sẽ cảnh báo vì chứng chỉ tự ký. Trong môi trường thực hành cục bộ này, có thể chọn tùy chọn nâng cao để tiếp tục. Không sử dụng lại chứng chỉ này cho hệ thống công khai/production.

Thông tin đăng nhập Grafana nằm trong `.env`, tại các biến `GRAFANA_ADMIN_USER` và `GRAFANA_ADMIN_PASSWORD`. Grafana tự cấu hình nguồn dữ liệu Prometheus và Loki cùng dashboard demo.

Đối với phpMyAdmin, đăng nhập bằng tài khoản cơ sở dữ liệu WordPress (`WORDPRESS_DB_USER` và `WORDPRESS_DB_PASSWORD`); không dùng tài khoản `root` của MySQL cho công việc thường ngày.

## 5. Cô lập mạng

- `frontend`: Nginx, WordPress và Nginx/Blackbox exporter. Chỉ Nginx công bố các cổng blog ra máy chủ. Cổng HTTP 8080 chuyển hướng sang HTTPS; endpoint `stub_status` của Nginx lắng nghe tại cổng nội bộ 8081 và không công bố ra máy chủ.
- `backend`: WordPress, MySQL, phpMyAdmin và MySQL exporter. MySQL không công bố cổng ra máy chủ.
- `monitoring`: Prometheus, Grafana và các exporter. Exporter được kết nối thêm vào mạng của dịch vụ đích khi cần.
- `logging`: Loki và Promtail; Grafana/Prometheus cũng tham gia mạng này để truy vấn Loki và thu thập số liệu của hệ thống log.
- `access`: mạng kết nối các dịch vụ có cổng được công bố trên máy chủ.

WordPress phải tham gia cả `frontend` và `backend`: WordPress nhận yêu cầu được Nginx chuyển tiếp và kết nối đến MySQL. phpMyAdmin chỉ công bố cổng trên `127.0.0.1`, không mở cổng dịch vụ trên giao diện mạng LAN.

## 6. Những gì được giám sát

- **Container:** Docker exporter cung cấp số liệu như `container_cpu_usage_seconds_total` và `container_memory_working_set_bytes`, lấy từ Docker Engine API. Đây là số liệu của container Docker, không phải tiến trình macOS gốc.
- **Nginx:** `nginx_connections_active`, `nginx_http_requests_total`; `nginx-exporter` đọc endpoint `stub_status`.
- **MySQL:** `mysql_global_status_threads_connected`, `mysql_global_status_questions`, `mysql_up`; được cung cấp bởi `mysqld-exporter` thông qua tài khoản `exporter` riêng.
- **WordPress:** Image WordPress này không có sẵn endpoint Prometheus. Vì vậy, Blackbox Exporter kiểm tra `https://nginx:8443/wp-login.php` và cung cấp `probe_success` cùng số liệu độ trễ yêu cầu. Phép kiểm tra có thể chưa thành công cho đến khi hoàn tất cài đặt WordPress lần đầu.

## 7. Thu thập log trên Docker Desktop for Mac

Promtail đọc log từ **các named volume được chia sẻ**, thay vì phụ thuộc vào đường dẫn `/var/lib/docker/containers` chỉ có trên Linux hoặc Docker socket. Cách này phù hợp hơn với Docker Desktop trên macOS và không cấp cho Promtail quyền truy cập mặt phẳng điều khiển Docker. Docker metrics exporter có mount Docker socket riêng để đọc số liệu; quyền truy cập socket rất nhạy cảm ngay cả khi mount ở chế độ chỉ đọc, vì vậy chỉ chạy dự án trên máy cục bộ đáng tin cậy của bạn.

- Nginx ghi `access.log` và `error.log` vào volume `nginx_logs`.
- WordPress/PHP ghi `debug.log` vào volume `wordpress_logs`, nằm ngoài thư mục web công khai.
- MySQL ghi `error.log` vào volume `mysql_logs`.
- Promtail đọc các volume này ở chế độ chỉ đọc và gửi log đến Loki qua mạng `logging`.

Kiểm tra log và trạng thái sẵn sàng của Loki:

```bash
docker compose logs --tail=100 promtail loki mysql nginx wordpress
docker compose exec loki wget -qO- http://127.0.0.1:3100/ready
```

## 8. Ví dụ truy vấn LogQL

Mở Grafana → **Explore** → chọn nguồn dữ liệu **Loki**. Sau đó chạy các truy vấn:

```logql
# 1. Tất cả dòng log truy cập Nginx
{job="nginx", log_type="access"}

# 2. Phản hồi HTTP 5xx
{job="nginx", log_type="access", status=~"5.."}

# 3. Yêu cầu đến trang đăng nhập WordPress
{job="nginx", log_type="access"} |= "/wp-login.php"

# 4. Dòng log lỗi Nginx
{job="nginx", log_type="error"}

# 5. Dòng log gỡ lỗi PHP/WordPress
{job="wordpress", log_type="php"}

# 6. Dòng log lỗi MySQL
{job="mysql", log_type="error"}

# 7. Đếm phản hồi 5xx trong 5 phút gần nhất, nhóm theo mã trạng thái
sum by (status) (count_over_time({job="nginx", log_type="access", status=~"5.."}[5m]))
```

Nếu truy vấn LogQL không trả về dữ liệu, hãy tạo một vài yêu cầu web, làm mới khoảng thời gian trong Explore và kiểm tra `docker compose logs promtail` để tìm lỗi đường dẫn hoặc quyền đọc tệp.

## 9. Danh sách kiểm tra

```bash
# Trạng thái dịch vụ/container
docker compose ps -a

# HTTP dự kiến trả về chuyển hướng 301
curl -I http://localhost:8080

# Kiểm tra HTTPS; -k chấp nhận chứng chỉ tự ký của đồ án
curl -k -I https://localhost:8443

# Kiểm tra các header bảo mật
curl -k -I https://localhost:8443 | grep -Ei 'strict-transport-security|x-frame-options|x-content-type-options|referrer-policy|content-security-policy'

# Kiểm tra sức khỏe Prometheus
curl -fsS http://localhost:9090/-/healthy
# Mở http://localhost:9090/targets và xác nhận các job mong đợi có trạng thái UP.
```

Trong Grafana, mở **Dashboards → Blog CMS → Blog CMS - Overview**.

## 10. Dừng và dọn dẹp

Dừng các container nhưng giữ lại dữ liệu website, cơ sở dữ liệu, số liệu giám sát và log:

```bash
docker compose down
```

Xóa các dịch vụ **và toàn bộ dữ liệu lưu trữ bền vững** (không thể hoàn tác):

```bash
docker compose down -v
```

Không chạy lệnh thứ hai trừ khi bạn thực sự muốn đặt lại toàn bộ môi trường thực hành.
