# Bản Tin — Vietnamese News Portal (Laravel MVC)

Website đọc báo tiếng Việt được triển khai theo đặc tả `NEWS_PORTAL_LARAVEL_MVC_SPEC.md`: Laravel MVC monolith, Blade + Vite, PHP 8.4, MySQL 8.4, JWT access/refresh cookies, RBAC, Form Request, Policy, Service và Docker Compose.

## 1. Công nghệ

- PHP 8.4 + Laravel 12.x; Blade, Vite, Tailwind CSS, Alpine.js.
- Rich Text Editor Tiptap; lưu `content_json` và HTML được sanitize phía server.
- MySQL 8.4 + Eloquent; UUID primary keys.
- `tymon/jwt-auth` nhánh `^2.3` cho JWT, `ezyang/htmlpurifier` để sanitize rich text.
- Docker Compose: PHP-FPM, Nginx và MySQL; volumes giữ database và ảnh upload.

## 2. Khởi chạy trên Linux

Yêu cầu Docker Engine và Docker Compose plugin. Lần đầu build cần kết nối Internet để tải base images và Composer/npm dependencies.

```bash
# 1. Vào thư mục dự án
cd news-portal-laravel

# 2. Tạo file môi trường
cp .env.example .env

# 3. Đổi DB_PASSWORD và DB_ROOT_PASSWORD trong .env.
#    Có thể đổi các SEED_*_PASSWORD trước khi chạy seed.
#    APP_KEY và JWT_SECRET để trống: entrypoint sẽ tự sinh một lần khi container khởi động.

# 4. Build và khởi chạy PHP-FPM, Nginx, MySQL
docker compose -f docker-compose.yaml up -d --build

# 5. Tạo schema và dữ liệu demo
docker compose -f docker-compose.yaml exec app php artisan migrate --seed

# 6. Kiểm tra key đã được tạo trong .env trên máy host
grep -E '^(APP_KEY|JWT_SECRET)=' .env
```

Mở **http://localhost:8080**.

Nếu trước đó bạn đã build image từ bản Dockerfile cũ và gặp lỗi `bootstrap/cache directory must be present and writable`, hãy build lại image sau khi cập nhật source:

```bash
docker compose -f docker-compose.yaml down
docker compose -f docker-compose.yaml build --no-cache app
docker compose -f docker-compose.yaml up -d
```

Sau khi `app` đã chạy ổn định, mới chạy migration/seed:

```bash
docker compose -f docker-compose.yaml exec app php artisan migrate --seed
```

> Không chạy `migrate:fresh` trên môi trường có dữ liệu cần giữ. Container không tự chạy migration khi khởi động. Các lệnh trên chỉ cần chạy lần đầu hoặc khi bạn chủ động thay đổi schema.

### Tài khoản demo

Mật khẩu/email lấy từ `.env`; các giá trị mặc định local hiện tại:

| Vai trò | Email | Mật khẩu |
|---|---|---|
| ADMIN | `admin@news.local` | `Admin@12345` |
| EDITOR | `editor1@news.local` | `Editor@12345` |
| EDITOR | `editor2@news.local` | `Editor@12345` |
| USER | `reader1@news.local` | `User@12345` |
| USER | `reader2@news.local` | `User@12345` |
| USER | `reader3@news.local` | `User@12345` |

Các mật khẩu trên chỉ dành cho môi trường demo local. Hãy thay đổi trước khi sử dụng trên môi trường chia sẻ hoặc production. Đăng ký công khai luôn tạo `USER`, không thể tự chọn role.

### Dữ liệu demo có sẵn

- 10 chuyên mục mặc định: Thời sự, Thế giới, Kinh doanh, Công nghệ, Thể thao, Giải trí, Sức khỏe, Giáo dục, Pháp luật, Đời sống.
- 7 bài đã xuất bản để hiển thị trên trang chủ, trang chuyên mục và kết quả tìm kiếm.
- Một bài đã xuất bản có **revision mới đang chờ duyệt**; bản public cũ vẫn được giữ.
- Một bản nháp, một bài bị từ chối và một bài mới chờ duyệt.
- Bình luận mẫu ở trạng thái `APPROVED`, `PENDING` và `REJECTED`.
- Seed dùng `updateOrCreate`/`firstOrCreate` để có thể chạy lại mà không tạo bản ghi demo trùng theo khóa nghiệp vụ.

## 3. Các màn hình chính

### Độc giả và khách

- `/` — trang chủ, bài nổi bật, tin mới và khối chuyên mục.
- `/chuyen-muc/{slug}` — danh sách bài theo chuyên mục.
- `/tin/{slug}` — trang đọc bài, bài liên quan và bình luận đã duyệt.
- `/tim-kiem?q=tu-khoa` — tìm tiêu đề, sapo và nội dung của bài đã xuất bản.
- `/dang-nhap`, `/dang-ky`, `/tai-khoan` — xác thực và tài khoản.

### Editor

- `/editor` — thống kê bài viết theo trạng thái.
- `/editor/bai-viet` — chỉ các bài của Editor đang đăng nhập.
- `/editor/bai-viet/tao-moi` — soạn thảo và lưu nháp.
- `/editor/bai-viet/{id}/sua` — sửa bản nháp/bản bị từ chối hoặc tạo revision mới.
- `/editor/media` — thư viện ảnh đã upload.

Editor không thể tự duyệt/xuất bản bài. Không thể thao tác bài của Editor khác.

### Admin

- `/admin` — dashboard.
- `/admin/nguoi-dung` — tài khoản, tạo Editor, đổi role, khóa/mở khóa.
- `/admin/bai-viet`, `/admin/cho-duyet` — danh sách và hàng đợi duyệt.
- `/admin/binh-luan` — duyệt/từ chối/ẩn bình luận.
- `/admin/chuyen-muc` — quản lý chuyên mục.
- `/admin/media` — upload và quản lý media toàn hệ thống.
- `/admin/audit-logs` — lịch sử hành động.

## 4. Luồng nghiệp vụ cần thử

1. Đăng nhập bằng `editor1@news.local`; tạo bài viết, lưu nháp rồi gửi duyệt.
2. Đăng xuất và đăng nhập bằng `admin@news.local`; mở `/admin/cho-duyet`, duyệt hoặc từ chối bài.
3. Đăng nhập bằng Editor để xem trạng thái và lý do từ chối; sửa bài và gửi duyệt lại.
4. Mở một bài đã xuất bản, chỉnh sửa bằng Editor và gửi revision mới. Khi revision đang pending, trang public tiếp tục hiển thị bản cũ. Admin duyệt revision thì bản mới mới trở thành public.
5. Đăng nhập bằng `reader1@news.local`; gửi bình luận. Bình luận mặc định `PENDING`, chỉ xuất hiện công khai sau khi Admin duyệt.
6. Thử đăng ký độc giả kèm field `role=ADMIN`; backend vẫn tạo tài khoản `USER`.
7. Dùng hai Editor khác nhau để thử sửa bài chéo; backend trả về từ chối truy cập.
8. Thử tìm từ khóa trong bài nháp/chờ duyệt; chúng không xuất hiện trong kết quả.

## 5. JWT, cookie và bảo mật

- Access token mặc định 15 phút; refresh token mặc định 7 ngày. Cả hai nằm trong cookie `HttpOnly`; không lưu JWT trong `localStorage`/`sessionStorage`.
- `APP_KEY` và `JWT_SECRET` được entrypoint tự sinh khi để trống trong `.env`; chúng được ghi vào file `.env` trên máy host. Docker Compose không inject các giá trị rỗng này vào PHP-FPM, vì làm vậy sẽ khóa cứng giá trị rỗng trong môi trường container và khiến Laravel không đọc được secret vừa tạo.
- Với cài đặt không dùng Docker, tạo key bằng `php artisan key:generate` và `php artisan jwt:secret` sau khi copy `.env.example` thành `.env`. Không commit secret thật và không chạy lại các lệnh này tùy tiện vì sẽ làm mất hiệu lực cookie/JWT hiện có.
- JWT dùng HMAC (`HS256`) theo cấu hình mặc định. `JWT_TTL`, `JWT_REFRESH_TTL`, `JWT_ISSUER`, `JWT_AUDIENCE`, cookie name, `JWT_COOKIE_SECURE`, `JWT_COOKIE_SAME_SITE`, leeway và blacklist đều được cấu hình qua `.env`/`config/jwt.php`.
- Refresh token được lưu dưới dạng SHA-256 hash trong `refresh_sessions`, rotation tạo session/token mới và phát hiện reuse sẽ thu hồi cả family.
- Access request kiểm tra token claims và session còn hoạt động trong database; logout/disable có hiệu lực ở các request tiếp theo.
- Form thay đổi dữ liệu đi qua Laravel web middleware và CSRF token (`@csrf`). Cookie secure được bật khi cấu hình `JWT_COOKIE_SECURE=true` sau khi triển khai HTTPS.
- HTML bài viết được sanitize phía server bằng HTML Purifier; ảnh inline chỉ được tham chiếu từ `/storage/`.
- Nginx chỉ phục vụ `public/`, chặn dotfiles và các đường dẫn nhạy cảm.

## 6. Xác thực, phân quyền và upload

### Đăng ký / đăng nhập

- `/dang-ky` tạo tài khoản `USER`; form yêu cầu họ tên, email, mật khẩu và xác nhận mật khẩu.
- Mật khẩu đăng ký chỉ cần được nhập và khớp trường xác nhận; không áp dụng độ dài tối thiểu hay quy tắc kết hợp chữ hoa, chữ thường, chữ số hoặc ký tự đặc biệt (giới hạn tối đa 255 ký tự). Mật khẩu vẫn được hash bởi Laravel trước khi lưu database.
- `/dang-nhap` kiểm tra email, mật khẩu và trạng thái `ACTIVE`. Khi thành công, server cấp access token và refresh token trong hai cookie `HttpOnly`.
- Access token mặc định sống 15 phút; middleware tự dùng refresh cookie để xoay token khi access token hết hạn. Refresh token cũ bị thu hồi sau khi xoay.
- Đăng xuất thu hồi refresh session hiện tại; “đăng xuất tất cả” thu hồi toàn bộ refresh session của tài khoản.

### Phân quyền

| Vai trò | Quyền chính |
|---|---|
| `USER` | Đọc báo, quản lý tài khoản, bình luận và xóa bình luận đang chờ duyệt của chính mình |
| `EDITOR` | Các quyền của USER, quản lý bài của mình, upload media cho bài của mình và gửi bài duyệt |
| `ADMIN` | Quản trị người dùng, role, chuyên mục, bài viết, bình luận, media và audit log |

JWT middleware xác thực token và refresh-session, tải lại User từ database, sau đó gắn user vào cả Request và Auth guard của Laravel để `request()->user()`, Gate và Policy cùng nhận diện một người dùng. Middleware `role` chặn theo role; Form Request/Policy/Service tiếp tục kiểm tra quyền và ownership ở cấp tài nguyên. Người bị khóa (`DISABLED`) không được tiếp tục dùng phiên hiện tại.

### Upload ảnh

Chức năng upload đã có sẵn, không cần cài thêm module:

- Admin: `/admin/media` để upload và quản lý thư viện media toàn hệ thống.
- Editor: `/editor/media` để xem media của mình; upload ảnh bìa/ảnh nội dung trong form bài viết. Hãy lưu bản nháp trước khi upload ảnh gắn với bài.
- Chỉ nhận JPEG, PNG, WebP; mặc định tối đa 5 MB và 20 triệu pixel. Server kiểm tra MIME thực, metadata và khả năng giải mã ảnh trước khi lưu.
- Ảnh được lưu ở public storage; URL được trả về từ bản ghi media. Không cho USER/khách upload và không cho Editor dùng ảnh của tài khoản khác.
- Media đang được dùng trong bài viết không thể bị xóa.

## 7. Xử lý lỗi xác thực thường gặp

1. Kiểm tra hai giá trị `APP_KEY` và `JWT_SECRET` trong `.env` không còn rỗng: `grep -E '^(APP_KEY|JWT_SECRET)=' .env`.
2. Nếu đang nâng cấp từ bản Compose cũ, cần tạo lại container app để bỏ các biến rỗng đã được inject vào container cũ: `docker compose -f docker-compose.yaml up -d --build --force-recreate app`.
3. Xóa config cache nếu đã từng chạy `config:cache`: `docker compose -f docker-compose.yaml exec app php artisan config:clear`.
4. Nếu thay `JWT_SECRET`, mọi JWT cũ sẽ mất hiệu lực; đăng nhập lại sau khi khởi động lại app.
5. Request trình duyệt không có phiên hợp lệ sẽ được chuyển về `/dang-nhap`; request JSON/API sẽ nhận HTTP `401`. Tài khoản đã đăng nhập nhưng không đủ role sẽ nhận HTTP `403`.

## 8. Lệnh vận hành Docker

```bash
# Xem trạng thái container
docker compose -f docker-compose.yaml ps

# Theo dõi log
docker compose -f docker-compose.yaml logs -f app nginx mysql

# Chạy test (cần Composer dependencies trong container)
docker compose -f docker-compose.yaml exec app php artisan test

# Dừng container, giữ nguyên dữ liệu
docker compose -f docker-compose.yaml down

# XÓA TOÀN BỘ dữ liệu MySQL và storage volumes — chỉ dùng khi muốn reset sạch
# docker compose -f docker-compose.yaml down -v
```

MySQL chỉ nằm trong Docker network nội bộ; không expose cổng database ra host theo mặc định. Có thể thêm mapping localhost-only nếu thực sự cần debug DB.

## 9. Dependency lock files

Bản source này chưa kèm `composer.lock` và `package-lock.json`. Môi trường tạo source không có Docker và không truy cập được registry để resolve dependencies. Dockerfile hiện cài các phiên bản tương thích theo constraint (`composer update` và `npm install`) để lần build đầu không bị dừng chỉ vì thiếu lock file.

Sau lần build đầu trên Linux có Internet, nên tạo và commit lock files để tái lập build:

```bash
# Composer chạy trong container và ghi composer.lock vào thư mục project bind-mounted
docker compose -f docker-compose.yaml exec app composer update --no-interaction

# Node/npm dùng container tạm để tạo package-lock.json trong thư mục project
docker run --rm -v "$PWD":/app -w /app node:22-alpine npm install --no-audit --no-fund
```

Dockerfile sẽ tự dùng `composer install` và `npm ci` khi tìm thấy lock files; nếu chưa có thì mới resolve theo constraint. Sau khi tạo lock files, build lại để dùng dependency đã khóa. Không dùng luồng resolve động khi thiếu lock files cho production.

## 10. Chạy test

```bash
docker compose -f docker-compose.yaml exec app php artisan test
```

Đã bổ sung test cho đăng ký bằng mật khẩu đơn giản, việc cấp cả hai JWT cookie, đồng bộ user giữa Request/Auth guard, Policy/Gate và chặn role không hợp lệ. Bộ test hiện có cũng kiểm tra public/search không lộ bài chưa duyệt và sanitizer loại bỏ một số payload XSS. Nên tiếp tục mở rộng kiểm thử refresh-token reuse, upload giả mạo, IDOR và các workflow admin/editor.

## 11. Cấu trúc nổi bật

```text
app/
├── Enums/
├── Http/Controllers/{Public,Auth,Editor,Admin}/
├── Http/Middleware/
├── Http/Requests/{Auth,Article,Media,Comment,Admin}/
├── Models/
├── Policies/
└── Services/
database/migrations/
database/seeders/
resources/views/{layouts,components,public,auth,editor,admin}/
routes/web.php
Dockerfile
docker-compose.yaml
docker/nginx/default.conf
```
