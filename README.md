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

# 4. Build và khởi chạy PHP-FPM, Nginx, MySQL
docker compose -f docker-compose.yaml up -d --build

# 5. Tạo schema và dữ liệu demo
docker compose -f docker-compose.yaml exec app php artisan migrate --seed
```

Mở [**http://localhost:8080**](http://localhost:8080).

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

## 5. Xác thực, phân quyền và upload

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
