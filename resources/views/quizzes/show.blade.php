@extends('layouts.app')

@section('title', $quiz->title)

@section('content')
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('quizzes.index') }}" class="text-decoration-none">Quản lý Quiz</a></li>
            <li class="breadcrumb-item active" aria-current="page">Chi tiết Quiz</li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-md-center flex-column flex-md-row gap-3">
        <div>
            <h2 class="fw-bold mb-1">{{ $quiz->title }}</h2>
            <p class="text-muted mb-0">Người tạo: {{ $quiz->user->name }} | Ngày tạo: {{ $quiz->created_at->format('d/m/Y') }}</p>
        </div>
        
        @if(Auth::user()->role === 'teacher' && Auth::id() === $quiz->user_id)
            <div class="d-flex gap-2">
                <form action="{{ route('quizzes.toggle-publish', $quiz) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn {{ $quiz->is_published ? 'btn-outline-secondary' : 'btn-outline-success' }} px-3 fw-medium">
                        @if($quiz->is_published)
                            <i class="fa-solid fa-eye-slash me-1"></i>Hạ xuất bản
                        @else
                            <i class="fa-solid fa-eye me-1"></i>Xuất bản công khai
                        @endif
                    </button>
                </form>
                
                <a href="{{ route('quizzes.edit', $quiz) }}" class="btn btn-outline-primary px-3 fw-medium">
                    <i class="fa-solid fa-pen me-1"></i>Sửa thông tin
                </a>
                
                <form action="{{ route('quizzes.destroy', $quiz) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bài Quiz này không?')" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger px-3 fw-medium">
                        <i class="fa-solid fa-trash me-1"></i>Xóa
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>

<div class="row g-4">
    <!-- Quiz Meta Info Column -->
    <div class="col-lg-4">
        <div class="card card-custom p-4 shadow-sm h-100">
            <h5 class="fw-bold mb-3"><i class="fa-solid fa-circle-info text-primary me-2"></i>Thông tin bài trắc nghiệm</h5>
            <p class="text-muted mb-4 fs-6">{{ $quiz->description ?? 'Không có mô tả cho bài trắc nghiệm này.' }}</p>
            
            <div class="d-grid gap-3 pt-3 border-top">
                <div class="d-flex justify-content-between">
                    <span class="text-muted"><i class="fa-solid fa-clock me-2"></i>Thời gian:</span>
                    <span class="fw-bold">{{ $quiz->time_limit_minutes ? $quiz->time_limit_minutes . ' phút' : 'Không giới hạn' }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted"><i class="fa-solid fa-circle-question me-2"></i>Tổng số câu:</span>
                    <span class="fw-bold">{{ $quiz->questions->count() }} câu</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted"><i class="fa-solid fa-toggle-on me-2"></i>Trạng thái:</span>
                    @if($quiz->is_published)
                        <span class="badge bg-success-subtle text-success border border-success border-opacity-25 px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i>Đã xuất bản</span>
                    @else
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary border-opacity-25 px-2 py-1"><i class="fa-solid fa-file-pen me-1"></i>Bản nháp</span>
                    @endif
                </div>
            </div>
            
            @if(Auth::user()->role === 'teacher')
                <div class="mt-4 pt-3 border-top">
                    <a href="{{ route('ai.generate.form', ['quiz_id' => $quiz->id]) }}" class="btn btn-gradient w-100 py-3 text-white fw-semibold card-hover">
                        <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Sinh câu hỏi từ tài liệu bằng AI
                    </a>
                </div>
            @endif
        </div>
    </div>
    
    <!-- Questions List Column -->
    <div class="col-lg-8">
        <div class="card card-custom p-4 shadow-sm h-100">
            <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                <h5 class="fw-bold mb-0"><i class="fa-solid fa-list-check text-success me-2"></i>Danh sách câu hỏi ({{ $quiz->questions->count() }})</h5>
                @if(Auth::user()->role === 'teacher' && Auth::id() === $quiz->user_id)
                    <a href="{{ route('quizzes.questions.create', $quiz) }}" class="btn btn-sm btn-primary rounded-pill px-3 shadow-none border-0 fw-semibold">
                        <i class="fa-solid fa-plus me-1"></i> Thêm câu hỏi
                    </a>
                @endif
            </div>
            
            @if($quiz->questions->isEmpty())
                <div class="text-center py-5">
                    <i class="fa-solid fa-folder-open text-muted fa-3x mb-3"></i>
                    <p class="text-muted fs-6 mb-4">Bài trắc nghiệm này hiện chưa có câu hỏi nào.</p>
                    @if(Auth::user()->role === 'teacher' && Auth::id() === $quiz->user_id)
                        <div class="d-flex justify-content-center gap-3">
                            <a href="{{ route('quizzes.questions.create', $quiz) }}" class="btn btn-sm btn-primary py-2 px-3 fw-medium"><i class="fa-solid fa-plus me-1"></i> Tạo thủ công</a>
                            <a href="{{ route('ai.generate.form', ['quiz_id' => $quiz->id]) }}" class="btn btn-sm btn-gradient py-2 px-3 text-white fw-medium"><i class="fa-solid fa-wand-magic-sparkles me-1"></i> AI sinh tự động</a>
                        </div>
                    @endif
                </div>
            @else
                <div class="accordion accordion-flush" id="questionsAccordion">
                    @foreach($quiz->questions->sortBy('sort_order') as $index => $question)
                        <div class="accordion-item border-bottom py-2">
                            <h2 class="accordion-header" id="headingQuestion{{ $question->id }}">
                                <button class="accordion-button collapsed px-0 bg-transparent text-dark fw-bold border-0 shadow-none d-flex justify-content-between align-items-center" type="button" data-bs-toggle="collapse" data-bs-target="#collapseQuestion{{ $question->id }}" aria-expanded="false" aria-controls="collapseQuestion{{ $question->id }}">
                                    <div class="flex-grow-1 text-truncate-2" style="max-width: 80%;">
                                        <span class="text-primary me-2">Câu {{ $index + 1 }}.</span> {{ $question->content }}
                                    </div>
                                    <div class="me-3">
                                        <span class="badge bg-light text-secondary border px-2 py-1 font-medium" style="font-size: 0.75rem;">
                                            {{ $question->type === 'multiple_choice' ? 'Trắc nghiệm' : ($question->type === 'true_false' ? 'Đúng/Sai' : 'Tự luận') }}
                                        </span>
                                        <span class="badge bg-light text-primary border px-2 py-1 font-medium ms-1" style="font-size: 0.75rem;">
                                            {{ $question->points }} điểm
                                        </span>
                                    </div>
                                </button>
                            </h2>
                            
                            <div id="collapseQuestion{{ $question->id }}" class="accordion-collapse collapse" aria-labelledby="headingQuestion{{ $question->id }}" data-bs-parent="#questionsAccordion">
                                <div class="accordion-body px-0 pt-3 pb-2 text-muted">
                                    
                                    @if($question->type !== 'short_answer')
                                        <h6 class="text-dark fw-semibold small mb-2"><i class="fa-solid fa-circle-info me-1"></i>Đáp án lựa chọn:</h6>
                                        <ul class="list-group list-group-flush mb-3 border rounded-3 overflow-hidden shadow-none">
                                            @foreach($question->answers as $answer)
                                                <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3 {{ $answer->is_correct ? 'bg-success bg-opacity-10 text-success fw-bold' : '' }}">
                                                    <span>{{ $answer->content }}</span>
                                                    @if($answer->is_correct)
                                                        <span class="badge bg-success rounded-circle p-1"><i class="fa-solid fa-check text-white"></i></span>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <div class="bg-light p-3 rounded-3 mb-3 border">
                                            <h6 class="text-dark fw-semibold small mb-1"><i class="fa-solid fa-pen-nib me-1"></i>Tính chất tự luận:</h6>
                                            <p class="mb-0 small text-muted">Học sinh trả lời bằng văn bản tự chọn. AI sẽ tự động chấm điểm và hỗ trợ trả lời thắc mắc dựa trên tài liệu.</p>
                                        </div>
                                    @endif

                                    @if($question->explanation)
                                        <div class="bg-light p-3 rounded-3 mb-3 border border-start border-4 border-info">
                                            <h6 class="text-dark fw-semibold small mb-1"><i class="fa-solid fa-lightbulb text-warning me-1"></i>Giải thích từ giáo viên:</h6>
                                            <p class="mb-0 small text-muted">{{ $question->explanation }}</p>
                                        </div>
                                    @endif

                                    @if(Auth::user()->role === 'teacher' && Auth::id() === $quiz->user_id)
                                        <div class="d-flex justify-content-end gap-2 pt-2 border-top">
                                            <a href="{{ route('quizzes.questions.edit', [$quiz, $question]) }}" class="btn btn-sm btn-outline-secondary px-3 py-1 rounded-pill"><i class="fa-solid fa-edit me-1"></i>Sửa câu hỏi</a>
                                            <form action="{{ route('quizzes.questions.destroy', [$quiz, $question]) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa câu hỏi này không?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger px-3 py-1 rounded-pill"><i class="fa-solid fa-trash me-1"></i>Xóa</button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
