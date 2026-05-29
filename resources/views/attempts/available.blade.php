@extends('layouts.app')

@section('title', 'Quiz Có Sẵn')

@section('content')
<div class="mb-4">
    <h2 class="fw-bold">Các bài trắc nghiệm khả dụng</h2>
    <p class="text-muted">Chọn một bài trắc nghiệm dưới đây và bắt đầu thực hiện bài kiểm tra</p>
</div>

@if($quizzes->isEmpty())
    <div class="card card-custom p-5 text-center shadow-sm">
        <div class="py-5">
            <i class="fa-solid fa-graduation-cap text-muted fa-4x mb-4"></i>
            <h4 class="fw-bold">Chưa có bài Quiz nào được xuất bản</h4>
            <p class="text-muted mb-0">Hiện tại giáo viên chưa xuất bản bài trắc nghiệm công khai nào. Vui lòng quay lại sau!</p>
        </div>
    </div>
@else
    <div class="row g-4">
        @foreach($quizzes as $quiz)
            <div class="col-lg-4 col-md-6">
                <div class="card card-custom h-100 shadow-sm card-hover d-flex flex-column border-top border-primary border-3">
                    <div class="card-body p-4 flex-grow-1">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-1 font-medium" style="font-size: 0.75rem;">
                                Hạn giờ: {{ $quiz->time_limit_minutes ? $quiz->time_limit_minutes . ' phút' : 'Không giới hạn' }}
                            </span>
                        </div>
                        
                        <h5 class="fw-bold text-dark mb-2 text-truncate-2" style="height: 48px; line-height: 24px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                            {{ $quiz->title }}
                        </h5>
                        
                        <p class="text-muted mb-4 small text-truncate-3" style="height: 54px; line-height: 18px; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                            {{ $quiz->description ?? 'Không có mô tả cho bài kiểm tra này.' }}
                        </p>
                        
                        <div class="d-flex gap-4 border-top pt-3 text-muted small">
                            <div>
                                <i class="fa-solid fa-circle-question text-primary me-1"></i> <strong>{{ $quiz->questions_count }}</strong> câu hỏi
                            </div>
                            <div>
                                <i class="fa-solid fa-user-tie text-secondary me-1"></i> GV: <strong>{{ $quiz->user->name }}</strong>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer bg-transparent border-top p-3 text-center">
                        <form action="{{ route('attempts.start', $quiz) }}" method="POST" class="d-inline w-100">
                            @csrf
                            <button type="submit" class="btn btn-gradient py-2 px-4 rounded-pill fw-bold text-white shadow-sm w-100 card-hover border-0">
                                <i class="fa-solid fa-circle-play me-1"></i> Bắt đầu làm bài
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection
