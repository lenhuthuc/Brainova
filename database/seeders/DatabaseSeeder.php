<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Answer;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Teacher account
        $teacher = User::create([
            'name' => 'Giáo Viên Demo',
            'email' => 'teacher@example.com',
            'password' => Hash::make('password'),
            'role' => 'teacher',
        ]);

        // Create Student accounts
        $student1 = User::create([
            'name' => 'Học Sinh A',
            'email' => 'student@example.com',
            'password' => Hash::make('password'),
            'role' => 'student',
        ]);

        $student2 = User::create([
            'name' => 'Học Sinh B',
            'email' => 'student2@example.com',
            'password' => Hash::make('password'),
            'role' => 'student',
        ]);

        // Create Sample Quizzes
        $quiz1 = Quiz::create([
            'user_id' => $teacher->id,
            'title' => 'Kiến Thức Lập Trình PHP Cơ Bản',
            'description' => 'Bài kiểm tra kiến thức cơ bản về ngôn ngữ lập trình PHP, bao gồm cú pháp, biến, hàm và các cấu trúc điều khiển.',
            'time_limit_minutes' => 30,
            'is_published' => true,
        ]);

        $quiz2 = Quiz::create([
            'user_id' => $teacher->id,
            'title' => 'Laravel Framework Nâng Cao',
            'description' => 'Kiểm tra hiểu biết về Laravel framework bao gồm Eloquent ORM, Middleware, Service Container và các design patterns.',
            'time_limit_minutes' => 45,
            'is_published' => true,
        ]);

        $quiz3 = Quiz::create([
            'user_id' => $teacher->id,
            'title' => 'Cơ Sở Dữ Liệu MySQL',
            'description' => 'Bài kiểm tra về thiết kế database, SQL queries, indexing và normalization.',
            'time_limit_minutes' => null,
            'is_published' => false,
        ]);

        // Questions for Quiz 1: PHP Basics
        $this->createQuestion($quiz1, 'PHP là viết tắt của cụm từ nào?', 'multiple_choice', 1, 'PHP ban đầu là viết tắt của Personal Home Page, sau đó được đổi thành PHP: Hypertext Preprocessor.', [
            ['content' => 'Personal Home Page', 'is_correct' => false],
            ['content' => 'PHP: Hypertext Preprocessor', 'is_correct' => true],
            ['content' => 'Private Host Program', 'is_correct' => false],
            ['content' => 'Program Hypertext Processor', 'is_correct' => false],
        ]);

        $this->createQuestion($quiz1, 'Biến trong PHP bắt đầu bằng ký tự nào?', 'multiple_choice', 1, 'Trong PHP, tất cả biến đều bắt đầu bằng ký tự $ (dollar sign).', [
            ['content' => '#', 'is_correct' => false],
            ['content' => '$', 'is_correct' => true],
            ['content' => '@', 'is_correct' => false],
            ['content' => '&', 'is_correct' => false],
        ]);

        $this->createQuestion($quiz1, 'PHP là ngôn ngữ lập trình phía server (server-side).', 'true_false', 1, 'PHP là ngôn ngữ scripting phía server, code được thực thi trên server trước khi gửi kết quả HTML về client.', [
            ['content' => 'Đúng', 'is_correct' => true],
            ['content' => 'Sai', 'is_correct' => false],
        ]);

        $this->createQuestion($quiz1, 'Hàm nào dùng để in ra màn hình trong PHP?', 'multiple_choice', 2, 'Cả echo và print đều có thể in ra màn hình, nhưng echo phổ biến hơn.', [
            ['content' => 'console.log()', 'is_correct' => false],
            ['content' => 'echo', 'is_correct' => true],
            ['content' => 'System.out.println()', 'is_correct' => false],
            ['content' => 'printf()', 'is_correct' => false],
        ]);

        $this->createQuestion($quiz1, 'Giải thích sự khác nhau giữa == và === trong PHP.', 'short_answer', 3, '== so sánh giá trị (loose comparison), === so sánh cả giá trị và kiểu dữ liệu (strict comparison).', []);

        // Questions for Quiz 2: Laravel Advanced
        $this->createQuestion($quiz2, 'Eloquent ORM trong Laravel sử dụng design pattern nào?', 'multiple_choice', 2, 'Eloquent sử dụng Active Record pattern, trong đó mỗi model tương ứng với một bảng trong database.', [
            ['content' => 'Repository Pattern', 'is_correct' => false],
            ['content' => 'Active Record Pattern', 'is_correct' => true],
            ['content' => 'Data Mapper Pattern', 'is_correct' => false],
            ['content' => 'Table Gateway Pattern', 'is_correct' => false],
        ]);

        $this->createQuestion($quiz2, 'Middleware trong Laravel chạy trước khi request đến controller.', 'true_false', 1, 'Middleware có thể chạy trước (before) hoặc sau (after) khi request được xử lý bởi controller.', [
            ['content' => 'Đúng', 'is_correct' => true],
            ['content' => 'Sai', 'is_correct' => false],
        ]);

        $this->createQuestion($quiz2, 'Service Container trong Laravel hoạt động dựa trên nguyên tắc nào?', 'multiple_choice', 2, 'Service Container sử dụng Dependency Injection và Inversion of Control để quản lý dependencies.', [
            ['content' => 'Singleton Pattern', 'is_correct' => false],
            ['content' => 'Dependency Injection', 'is_correct' => true],
            ['content' => 'Factory Pattern', 'is_correct' => false],
            ['content' => 'Observer Pattern', 'is_correct' => false],
        ]);

        $this->createQuestion($quiz2, 'Lệnh artisan nào dùng để tạo migration trong Laravel?', 'multiple_choice', 1, 'php artisan make:migration là lệnh tạo file migration mới.', [
            ['content' => 'php artisan create:migration', 'is_correct' => false],
            ['content' => 'php artisan make:migration', 'is_correct' => true],
            ['content' => 'php artisan generate:migration', 'is_correct' => false],
            ['content' => 'php artisan new:migration', 'is_correct' => false],
        ]);

        $this->createQuestion($quiz2, 'Giải thích cách hoạt động của Route Model Binding trong Laravel.', 'short_answer', 3, 'Route Model Binding tự động inject model instance vào route dựa trên ID trong URL. Laravel tự động query database và trả về 404 nếu không tìm thấy.', []);
    }

    /**
     * Helper method to create a question with answers.
     */
    private function createQuestion(Quiz $quiz, string $content, string $type, int $points, string $explanation, array $answers): void
    {
        static $order = [];

        if (!isset($order[$quiz->id])) {
            $order[$quiz->id] = 0;
        }

        $question = Question::create([
            'quiz_id' => $quiz->id,
            'content' => $content,
            'type' => $type,
            'points' => $points,
            'explanation' => $explanation,
            'sort_order' => $order[$quiz->id]++,
        ]);

        foreach ($answers as $index => $answer) {
            Answer::create([
                'question_id' => $question->id,
                'content' => $answer['content'],
                'is_correct' => $answer['is_correct'],
                'sort_order' => $index,
            ]);
        }
    }
}
