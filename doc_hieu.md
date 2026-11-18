0. Xem DataBase

sử dụng `DrawMeDatabase` để xem cấu trúc database trong `migrations` : `ctrl + shift + p` --> `DrawMeDatabase`


1. `Request $request->user()`: không tự quyết định lấy User từ Cookie, Session hay JWT. Nó lấy User thông qua cơ chế Authentication Guard đang được sử dụng tại thời điểm request được xử lý:

đọc nội dung của file `config/auth.php`
- nếu  gọi `$request->user()` mà không chỉ định rõ gì thêm thì mặc định `web`
- nếu ở router có chỉ định middleware dạng `->middleware('auth:api')` thì controller tương ứng sẽ dùng `api`
- cũng có thể chỉ định cứng ngay lúc gọi hàm: `$request->user('api')` (luôn đọc JWT) hoặc `$request->user('web')` (luôn đọc Cookie–Session).

**NOTE:**
- nếu có Middleware có đoạn code: `$request->setUserResolver(fn () => $user); 			Auth::setUser($user);` thì cơ chế này sẽ ghi đè cấu hình ở file `config/auth.php` và dùng jwt token
- yêu cầu: Middleware chạy trước 
2. Trong Controller 

- Nếu trong Controller có truyền vào 1 đối tượng trong Module thì đây là `Implicit Route Model Binding`. URL sẽ dạng `/SomeThing/{tên_tham_số_trong_controller}`.

- `$this->authorize()`: đang sử dụng Authorization – cơ chế phân quyền của Laravel.
    - Check User hiện tại có quyền thực hiện hành động trên một tài nguyên cụ thể hay không?
    - syntax: `$this->authorize($ability, $arguments = []);`
        - `$ability`: Tên hành động cần kiểm tra quyền, Trùng tên với method trong Policy có tên dạng `Tên_tham_số_Policy.`
        - `$arguments `: Đối tượng hoặc các tham số được truyền cho quá trình kiểm tra quyền. 
    - nếu logic trong Policy trả về False thì sẽ bị trả về Exception 

3. thư viện `tymon/jwt-auth`

- class User phải `implements JWTSubject`
- overwrite 2 method `etJWTCustomClaims` và `getJWTIdentifier`
- create token: `JWTAuth::customClaims($claims)->fromUser($user);`
    - `$user->getJWTIdentifier()` lấy ID của user đưa vào claim sub.
    - `$user->getJWTCustomClaims()` lấy các claim mặc định khai báo ở Model (nếu có) và gộp chung với mảng $claims (typ, sid).
- `getPayload()`: lấy phần payload nhưng các trường thỏa mãn `required_claims` trong config/jwt.php

- `WTAuth::authenticate()`: 
    - thực  hiện xác thực token nếu sai trả về Exception
    - thực hiện trích xuất id của users `$id = $this->getPayload()->get('sub')`
    - thực hiện truy vấn để tái tạo Module user




5. Xac thuc

Middleware và Policy đều có thể tham gia vào quá trình bảo vệ một request, nhưng chúng đảm nhận hai nhiệm vụ khác nhau:
- Middleware (Authentication): Xác định người gửi request là ai và có được phép đi tiếp vào khu vực yêu cầu đăng nhập hay không.
- Policy (Authorization): Kiểm tra người dùng đó có quyền thực hiện một hành động cụ thể trên một tài nguyên cụ thể hay không.