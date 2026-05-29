@extends('layouts.app')

@section('title', 'Quản lý Quiz')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold">Quản lý các bài trắc nghiệm</h2>
        <p class="text-muted mb-0">Tạo, chỉnh sửa và xuất bản các bài đánh giá năng lực</p>
    </div>
    @if(Auth::user()->role === 'teacher')
        <a href="{{ route('quizzes.create') }}" class="btn btn-gradient px-4 py-2 fw-semibold">
            <i class="fa-solid fa-plus-circle me-1"></i> Tạo Quiz Mới
        </a>
    @endif
</div>

@if($quizzes->isEmpty())
    <div class="card card-custom p-5 text-center shadow-sm">
        <div class="py-5">
            <i class="fa-solid fa-folder-open text-muted fa-4x mb-4"></i>
            <h4 class="fw-bold">Không tìm thấy bài Quiz nào</h4>
            <p class="text-muted mb-4">Bắt đầu tạo bài trắc nghiệm đầu tiên của bạn hoặc sinh câu hỏi tự động từ tài liệu bằng AI.</p>
            @if(Auth::user()->role === 'teacher')
                <div class="d-flex justify-content-center gap-3">
                    <a href="{{ route('quizzes.create') }}" class="btn btn-primary px-4 py-2"><i class="fa-solid fa-plus-circle me-1"></i> Tạo thủ công</a>
                    <a href="{{ route('ai.generate.form') }}" class="btn btn-gradient px-4 py-2"><i class="fa-solid fa-wand-magic-sparkles me-1"></i> Tạo bằng RAG AI</a>
                </div>
            @endif
        </div>
    </div>
@else
    <div class="row g-4">
        @foreach($quizzes as $quiz)
            <div class="col-lg-4 col-md-6">
                <div class="card card-custom h-100 shadow-sm card-hover d-flex flex-column">
                    <div class="card-body p-4 flex-grow-1">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge bg-opacity-10 text-primary bg-primary px-3 py-1 text-uppercase fw-semibold" style="font-size: 0.75rem;">
                                ID: #{{ $quiz->id }}
                            </span>
                            @if($quiz->is_published)
                                <span class="badge bg-success-subtle text-success border border-success border-opacity-25 px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i>Công khai</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary border-opacity-25 px-2 py-1"><i class="fa-solid fa-file-pen me-1"></i>Bản nháp</span>
                            @endif
                        </div>
                        
                        <h5 class="fw-bold text-dark mb-2 text-truncate-2" style="height: 48px; line-height: 24px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                            {{ $quiz->title }}
                        </h5>
                        
                        <p class="text-muted mb-4 small text-truncate-3" style="height: 54px; line-height: 18px; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                            {{ $quiz->description ?? 'Không có mô tả.' }}
                        </p>
                        
                        <div class="d-flex gap-4 border-top pt-3 text-muted small">
                            <div>
                                <i class="fa-solid fa-circle-question text-primary me-1"></i> <strong>{{ $quiz->questions_count }}</strong> câu hỏi
                            </div>
                            <div>
                                <i class="fa-solid fa-clock text-warning me-1"></i> <strong>{{ $quiz->time_limit_minutes ?? 'Vô hạn' }}</strong> phút
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer bg-transparent border-top p-3 d-flex justify-content-between align-items-center">
                        <a href="{{ route('quizzes.show', $quiz) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-none border-1">
                            <i class="fa-solid fa-eye me-1"></i> Xem chi tiết
                        </a>
                        
                        @if(Auth::user()->role === 'teacher')
                            <div class="d-flex gap-2">
                                <a href="{{ route('quizzes.edit', $quiz) }}" class="btn btn-sm btn-light text-secondary px-2 rounded-circle" title="Sửa">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <form action="{{ route('quizzes.destroy', $quiz) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bài Quiz này không? Toàn bộ câu hỏi liên quan sẽ bị xóa!')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-light text-danger px-2 rounded-circle" title="Xóa">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection
