@extends('layouts.app')

@section('title', 'Chỉnh Sửa Quiz')

@section('content')
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('quizzes.index') }}" class="text-decoration-none">Quản lý Quiz</a></li>
            <li class="breadcrumb-item active" aria-current="page">Chỉnh sửa</li>
        </ol>
    </nav>
    <h2 class="fw-bold">Chỉnh sửa thông tin bài Quiz</h2>
    <p class="text-muted">Thay đổi thông tin tiêu đề, mô tả hoặc thời gian làm bài</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card card-custom p-4 shadow-sm">
            <form action="{{ route('quizzes.update', $quiz) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="mb-4">
                    <label for="title" class="form-label fw-bold">Tiêu đề bài trắc nghiệm <span class="text-danger">*</span></label>
                    <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $quiz->title) }}" placeholder="Ví dụ: Kiểm tra giữa kỳ Lập trình PHP" required>
                    @error('title')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>
                
                <div class="mb-4">
                    <label for="description" class="form-label fw-bold">Mô tả bài trắc nghiệm</label>
                    <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="4" placeholder="Nhập hướng dẫn hoặc mô tả bài làm cho học sinh...">{{ old('description', $quiz->description) }}</textarea>
                    @error('description')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="time_limit_minutes" class="form-label fw-bold">Thời gian làm bài (Phút)</label>
                        <input type="number" name="time_limit_minutes" id="time_limit_minutes" class="form-control @error('time_limit_minutes') is-invalid @enderror" value="{{ old('time_limit_minutes', $quiz->time_limit_minutes) }}" min="1" placeholder="Bỏ trống nếu không giới hạn">
                        <small class="text-muted">Hệ thống sẽ tự động nộp bài khi hết giờ.</small>
                        @error('time_limit_minutes')
                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>
                    <div class="col-md-6 d-flex align-items-center mt-3 mt-md-0">
                        <div class="form-check form-switch mt-md-4">
                            <input class="form-check-input" type="checkbox" name="is_published" id="is_published" value="1" {{ old('is_published', $quiz->is_published) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold text-dark ms-2" for="is_published">Xuất bản bài trắc nghiệm</label>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-3 border-top pt-4">
                    <button type="submit" class="btn btn-gradient px-4 py-2 text-white fw-semibold"><i class="fa-solid fa-save me-1"></i>Lưu thay đổi</button>
                    <a href="{{ route('quizzes.index') }}" class="btn btn-outline-secondary px-4 py-2 border-1"><i class="fa-solid fa-times me-1"></i>Hủy</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
