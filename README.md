# Brainova

Brainova là một hệ thống kiểm tra trắc nghiệm. Ứng dụng này tối ưu hoá quy trình tạo quiz, quản lý câu hỏi, lưu dữ liệu bài làm và đánh giá kết quả cho môi trường giáo dục. Brainova cũng hỗ trợ tính năng AI/Chatbot để tạo nội dung học tập và trợ giúp người dùng với kiến thức liên quan.

## Tổng quan

Brainova hỗ trợ:
- Tạo, quản lý và phân phối quiz cho học sinh.
- Quản lý câu hỏi trắc nghiệm với nhiều lựa chọn đáp án và điểm số.
- Lưu lịch sử làm bài và đánh giá tự động.
- Quản lý tài liệu học tập liên quan đến từng quiz.
- Tích hợp AI để tạo nội dung và hỗ trợ học tập dựa trên RAG.
- Hệ thống xác thực và phân quyền cơ bản cho giáo viên và học sinh.

## Công nghệ chính

- Laravel 10
- PHP 8.x
- MySQL
- Blade Templates
- Laravel Authentication
- MVC architecture

## Kiến trúc dự án

- `app/Models` — định nghĩa các model dữ liệu như `Quiz`, `Question`, `Answer`, `Attempt`, `Document`, `RagConversation`.
- `app/Http/Controllers` — xử lý logic ứng dụng và điều phối yêu cầu HTTP.
- `resources/views` — giao diện người dùng được xây dựng bằng Blade.
- `database/migrations` — định nghĩa cấu trúc cơ sở dữ liệu.
- `routes/web.php` — định nghĩa các route cho ứng dụng.

## Tính năng nổi bật

1. Quản lý quiz
   - Tạo, sửa, xóa quiz.
   - Đặt thời lượng, cấu hình trạng thái công khai và phân loại quiz.
2. Quản lý câu hỏi
   - Xây dựng câu hỏi trắc nghiệm với nhiều đáp án.
   - Gán điểm cho từng câu hỏi.
3. Thực hiện quiz
   - Học sinh làm bài trực tuyến và nộp kết quả.
   - Hệ thống tự động chấm và tính điểm.
4. Lưu trữ kết quả
   - Ghi nhận lịch sử bài làm.
   - Hiển thị điểm số và trạng thái hoàn thành.
5. Quản lý tài liệu
   - Tải lên và liên kết tài liệu với quiz tương ứng.
6. AI hỗ trợ và tạo nội dung
   - Tích hợp AI giúp tạo câu hỏi và hỗ trợ vấn đáp thông tin.
   - Quản lý hội thoại RAG để kết nối dữ liệu tài liệu với chức năng trợ giúp thông minh.

## Hướng dẫn cài đặt

### Chạy bằng Docker (khuyến nghị)

Yêu cầu: [Docker](https://www.docker.com/get-started) và [Docker Compose](https://docs.docker.com/compose/install/) đã được cài đặt.

1. Sao chép kho lưu trữ:
   ```bash
   git clone <repository-url> Brainova
   cd Brainova
   ```

2. Tạo file cấu hình môi trường:
   ```bash
   cp .env.example .env
   ```

3. Cập nhật `.env` để dùng MySQL trong Docker:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=mysql
   DB_PORT=3306
   DB_DATABASE=laravel
   DB_USERNAME=laravel
   DB_PASSWORD=secret
   DB_ROOT_PASSWORD=root
   ```

4. Khởi động toàn bộ hệ thống:
   ```bash
   docker compose up -d --build
   ```

5. Sinh APP_KEY và chạy migration:
   ```bash
   docker compose exec app php artisan key:generate
   docker compose exec app php artisan migrate --seed
   ```

6. Truy cập ứng dụng:
   - **Web app**: [http://localhost:9090](http://localhost:9090)
   - **phpMyAdmin**: [http://localhost:8080](http://localhost:8080)

> Để dừng: `docker compose down`  
> Để dừng và xóa toàn bộ dữ liệu: `docker compose down -v`

---

### Chạy thủ công (không dùng Docker)

1. Sao chép kho lưu trữ vào máy:
   ```powershell
   git clone <repository-url> Brainova
   cd Brainova
   ```
2. Cài đặt thư viện:
   ```powershell
   composer install
   ```
3. Tạo file cấu hình môi trường:
   ```powershell
   copy .env.example .env
   ```
4. Cập nhật cấu hình cơ sở dữ liệu trong `.env`:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=brainova
   DB_USERNAME=root
   DB_PASSWORD=
   ```
5. Thiết lập ứng dụng và cơ sở dữ liệu:
   ```powershell
   php artisan key:generate
   php artisan migrate
   php artisan db:seed
   ```
6. Khởi chạy ứng dụng:
   ```powershell
   php artisan serve
   ```

## Sử dụng cơ bản

- Đăng ký tài khoản và đăng nhập.
- Giáo viên tạo quiz, quản lý câu hỏi và tài liệu.
- Học sinh truy cập quiz public để làm bài.
- Kiểm tra kết quả ngay khi hoàn thành quiz.

## Liên hệ

- Nếu cần hỗ trợ hoặc mở rộng tính năng, vui lòng liên hệ tác giả dự án.
