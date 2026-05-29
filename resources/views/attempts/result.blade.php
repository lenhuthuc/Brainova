@extends('layouts.app')

@section('title', 'Kết Quả Bài Làm')

@section('content')
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('attempts.history') }}" class="text-decoration-none">Lịch sử làm bài</a></li>
            <li class="breadcrumb-item active" aria-current="page">Báo cáo kết quả</li>
        </ol>
    </nav>
    <h2 class="fw-bold">Báo cáo kết quả bài kiểm tra</h2>
    <p class="text-muted mb-0">Hệ thống chấm điểm tự động dựa trên đáp án chính xác</p>
</div>

<!-- Score Card Row -->
<div class="row g-4 mb-5">
    <div class="col-lg-4">
        @php
            $percentage = $attempt->total_points > 0 ? ($attempt->score / $attempt->total_points) * 100 : 0;
            $passed = $percentage >= 70;
        @endphp
        <div class="card card-custom p-4 shadow-sm text-center h-100 {{ $passed ? 'border-success' : 'border-danger' }} border-top border-4">
            <h6 class="text-muted fw-semibold">ĐIỂM SỐ CỦA BẠN</h6>
            <h1 class="fw-extrabold display-3 {{ $passed ? 'text-success' : 'text-danger' }} mb-2">{{ number_format($attempt->score, 1) }}</h1>
            <p class="text-muted font-medium mb-3">Trên tổng số {{ number_format($attempt->total_points, 1) }} điểm</p>
            
            <div class="progress mb-3 rounded-pill" style="height: 12px;">
                <div class="progress-bar {{ $passed ? 'bg-success' : 'bg-danger' }} rounded-pill" role="progressbar" style="width: {{ $percentage }}%" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            
            <span class="fs-5 fw-bold {{ $passed ? 'text-success' : 'text-danger' }} d-block">
                @if($passed)
                    <i class="fa-solid fa-circle-check me-1"></i> HOÀN THÀNH (ĐẠT)
                @else
                    <i class="fa-solid fa-circle-xmark me-1"></i> CHƯA ĐẠT KỲ VỌNG
                @endif
            </span>
        </div>
    </div>
    
    <div class="col-lg-8">
        <div class="card card-custom p-4 shadow-sm h-100 d-flex flex-column justify-content-between">
            <div>
                <h5 class="fw-bold mb-4"><i class="fa-solid fa-graduation-cap text-primary me-2"></i>Trợ Lý Giáo Viên AI (RAG Agent)</h5>
                <p class="text-muted fs-6">Bạn có bất kỳ thắc mắc hoặc câu hỏi nào liên quan đến các đáp án bị sai không? RAG AI Agent của chúng tôi có thể giải thích chi tiết dựa trên giáo trình giáo án đã được tải lên trước đó của giáo viên.</p>
            </div>
            
            <div class="mt-4">
                <a href="{{ route('rag.chat', $attempt) }}" class="btn btn-gradient py-3 px-4 text-white fw-bold card-hover d-inline-block">
                    <i class="fa-solid fa-comments me-1"></i> Hỏi Trợ Lý AI RAG Trực Tuyến
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Questions Review List -->
<div class="row">
    <div class="col-lg-12">
        <h5 class="fw-bold mb-4"><i class="fa-solid fa-magnifying-glass me-2"></i>Xem lại chi tiết bài làm</h5>
        
        @foreach($attempt->details as $index => $detail)
            <div class="card card-custom p-4 shadow-sm mb-4 {{ $detail->is_correct ? 'border-start border-success border-4' : 'border-start border-danger border-4' }}">
                <div class="d-flex justify-content-between align-items-start mb-3 pb-2 border-bottom">
                    <h6 class="fw-bold mb-0">
                        <span class="text-primary me-1">Câu {{ $index + 1 }}.</span> {{ $detail->question->content }}
                    </h6>
                    <div>
                        @if($detail->is_correct)
                            <span class="badge bg-success-subtle text-success border border-success border-opacity-25 px-2 py-1"><i class="fa-solid fa-check me-1"></i>Đúng (+{{ $detail->points_earned }} điểm)</span>
                        @else
                            <span class="badge bg-danger-subtle text-danger border border-danger border-opacity-25 px-2 py-1"><i class="fa-solid fa-times me-1"></i>Sai (0 / {{ $detail->question->points }} điểm)</span>
                        @endif
                    </div>
                </div>

                @if($detail->question->type !== 'short_answer')
                    <div class="d-flex flex-column gap-2 mb-3">
                        @foreach($detail->question->answers as $ans)
                            @php
                                $selected = $detail->answer_id === $ans->id;
                                $correct = $ans->is_correct;
                                $class = 'bg-light';
                                if ($selected) {
                                    $class = $correct ? 'bg-success bg-opacity-10 text-success border-success' : 'bg-danger bg-opacity-10 text-danger border-danger';
                                } elseif ($correct) {
                                    $class = 'bg-success bg-opacity-10 text-success border-success fw-semibold';
                                }
                            @endphp
                            <div class="p-3 border rounded-3 d-flex justify-content-between align-items-center {{ $class }}">
                                <span>{{ $ans->content }}</span>
                                @if($selected)
                                    <span class="badge {{ $correct ? 'bg-success' : 'bg-danger' }} rounded-circle p-1">
                                        <i class="fa-solid {{ $correct ? 'fa-check' : 'fa-times' }} text-white"></i>
                                    </span>
                                @elseif($correct)
                                    <span class="badge bg-success rounded-circle p-1"><i class="fa-solid fa-check text-white"></i></span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="bg-light p-3 rounded-3 mb-3 border">
                        <h6 class="text-dark fw-semibold small mb-1"><i class="fa-solid fa-user-pen me-1"></i>Bài viết của bạn:</h6>
                        <p class="mb-0 text-dark small" style="white-space: pre-wrap;">{{ $detail->text_answer ?? 'Không có câu trả lời.' }}</p>
                    </div>
                @endif

                @if($detail->question->explanation)
                    <div class="bg-light p-3 rounded-3 mb-2 border-start border-4 border-info">
                        <h6 class="text-dark fw-semibold small mb-1"><i class="fa-solid fa-lightbulb text-warning me-1"></i>Giải thích đáp án:</h6>
                        <p class="mb-0 small text-muted">{{ $detail->question->explanation }}</p>
                    </div>
                @endif
                
                @if(!$detail->is_correct)
                    <div class="text-end mt-2">
                        <a href="{{ route('rag.chat', ['attempt' => $attempt->id, 'prefill_question_id' => $detail->question->id]) }}" class="btn btn-sm btn-link text-primary text-decoration-none fw-semibold">
                            <i class="fa-solid fa-comment-dots me-1"></i> Hỏi AI giải thích chi tiết hơn về câu hỏi này
                        </a>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>
@endsection
