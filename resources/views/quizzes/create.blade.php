@extends('layouts.app')

@section('title', 'Tạo Quiz Mới')

@section('content')
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('quizzes.index') }}" class="text-decoration-none">Quản lý Quiz</a></li>
            <li class="breadcrumb-item active" aria-current="page">Tạo mới</li>
        </ol>
    </nav>
    <h2 class="fw-bold">Tạo bài trắc nghiệm mới</h2>
    <p class="text-muted">Nhập thông tin cơ bản cho bài trắc nghiệm của bạn</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card card-custom p-4 shadow-sm">
            <form action="{{ route('quizzes.store') }}" method="POST">
                @csrf
                
                <div class="mb-4">
                    <label for="title" class="form-label fw-bold">Tiêu đề bài trắc nghiệm <span class="text-danger">*</span></label>
                    <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" placeholder="Ví dụ: Kiểm tra giữa kỳ Lập trình PHP" required>
                    @error('title')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>
                
                <div class="mb-4">
                    <label for="description" class="form-label fw-bold">Mô tả bài trắc nghiệm</label>
                    <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="4" placeholder="Nhập hướng dẫn hoặc mô tả bài làm cho học sinh...">{{ old('description') }}</textarea>
                    @error('description')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="time_limit_minutes" class="form-label fw-bold">Thời gian làm bài (Phút)</label>
                        <input type="number" name="time_limit_minutes" id="time_limit_minutes" class="form-control @error('time_limit_minutes') is-invalid @enderror" value="{{ old('time_limit_minutes') }}" min="1" placeholder="Bỏ trống nếu không giới hạn">
                        <small class="text-muted">Hệ thống sẽ tự động nộp bài khi hết giờ.</small>
                        @error('time_limit_minutes')
                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>
                    <div class="col-md-6 d-flex align-items-center mt-3 mt-md-0">
                        <div class="form-check form-switch mt-md-4">
                            <input class="form-check-input" type="checkbox" name="is_published" id="is_published" value="1" {{ old('is_published') ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold text-dark ms-2" for="is_published">Xuất bản ngay lập tức</label>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-3 border-top pt-4">
                    <button type="submit" class="btn btn-gradient px-4 py-2 text-white fw-semibold"><i class="fa-solid fa-save me-1"></i>Tạo Quiz</button>
                    <a href="{{ route('quizzes.index') }}" class="btn btn-outline-secondary px-4 py-2 border-1"><i class="fa-solid fa-times me-1"></i>Hủy</a>
                </div>
            </form>
        </div>
    </div>
    
    <div class="col-lg-4 mt-4 mt-lg-0">
        <div class="card card-custom p-4 shadow-sm bg-light border-0">
            <h5 class="fw-bold mb-3 text-primary"><i class="fa-solid fa-lightbulb me-2"></i>Mẹo nhỏ tạo Quiz</h5>
            <ul class="text-muted ps-3 small">
                <li class="mb-2">Bạn có thể tạo thông tin cơ bản trước, sau đó thêm câu hỏi trắc nghiệm sau ở trang chi tiết.</li>
                <li class="mb-2">Nếu muốn học sinh có thể thấy và thực hiện bài làm ngay, hãy gạt nút **Xuất bản ngay lập tức**.</li>
                <li class="mb-2">Sử dụng tính năng **RAG AI** ở menu bên trái để tự động tạo toàn bộ câu hỏi từ file bài giảng cực kỳ nhanh chóng.</li>
            </ul>
        </div>
    </div>
</div>
@endsection
