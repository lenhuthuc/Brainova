@extends('layouts.app')

@section('title', 'Làm Bài: ' . $quiz->title)

@section('content')
<style>
    /* Custom exam screen layouts */
    .exam-header {
        position: fixed;
        top: 56px;
        left: 0;
        right: 0;
        background-color: white;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        z-index: 1020;
        padding: 0.75rem 2rem;
    }
    
    .question-nav-pill {
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background-color: #F3F4F6;
        color: #4B5563;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.2s ease;
        border: 2px solid transparent;
    }
    
    .question-nav-pill:hover {
        background-color: #E5E7EB;
    }
    
    .question-nav-pill.answered {
        background-color: #EEF2F6;
        color: var(--primary-color);
        border-color: var(--primary-color);
    }
    
    .question-nav-pill.active {
        background-color: var(--primary-color);
        color: white;
    }
</style>

<!-- Custom Header for Countdown -->
<div class="exam-header d-flex justify-content-between align-items-center">
    <div>
        <h5 class="fw-bold mb-0 text-dark">{{ $quiz->title }}</h5>
        <small class="text-muted"><i class="fa-solid fa-user-pen"></i> Học sinh đang thực hiện bài làm</small>
    </div>
    
    <div class="d-flex align-items-center gap-4">
        @if($quiz->time_limit_minutes)
            <div class="d-flex align-items-center gap-2 px-3 py-2 bg-danger bg-opacity-10 text-danger rounded-pill fw-bold">
                <i class="fa-regular fa-clock fa-spin"></i> <span id="timerDisplay">--:--</span>
            </div>
        @endif
        <button type="button" class="btn btn-gradient px-4 fw-bold text-white rounded-pill" onclick="triggerSubmit()">
            <i class="fa-solid fa-paper-plane me-1"></i> Nộp Bài Làm
        </button>
    </div>
</div>

<div class="row g-4" style="margin-top: 50px;">
    <!-- Question Cards List Column -->
    <div class="col-lg-8">
        <form action="{{ route('attempts.submit', $attempt) }}" method="POST" id="attemptSubmitForm">
            @csrf
            
            <input type="hidden" name="time_taken_seconds" id="time_taken_seconds" value="0">
            
            @foreach($quiz->questions->sortBy('sort_order') as $index => $question)
                <div class="card card-custom p-4 shadow-sm mb-4 question-card" id="question-sec-{{ $index + 1 }}">
                    <div class="d-flex justify-content-between align-items-start mb-3 pb-2 border-bottom">
                        <h6 class="fw-bold text-dark mb-0">
                            <span class="text-primary fs-5 me-1">Câu {{ $index + 1 }}.</span> {{ $question->content }}
                        </h6>
                        <span class="badge bg-light text-primary border px-2 py-1 small">{{ $question->points }} điểm</span>
                    </div>

                    <input type="hidden" name="responses[{{ $index }}][question_id]" value="{{ $question->id }}">

                    @if($question->type === 'multiple_choice')
                        <div class="d-flex flex-column gap-3 mt-3">
                            @foreach($question->answers->sortBy('sort_order') as $aIndex => $answer)
                                <label class="d-flex align-items-center gap-3 p-3 border rounded-3 bg-light bg-opacity-50 cursor-pointer card-hover">
                                    <input type="radio" name="responses[{{ $index }}][answer_id]" value="{{ $answer->id }}" class="form-check-input question-option-input" data-question-index="{{ $index + 1 }}">
                                    <span class="text-dark">{{ $answer->content }}</span>
                                </label>
                            @endforeach
                        </div>
                    @elseif($question->type === 'true_false')
                        <div class="d-flex flex-column gap-3 mt-3">
                            @foreach($question->answers->sortBy('sort_order') as $aIndex => $answer)
                                <label class="d-flex align-items-center gap-3 p-3 border rounded-3 bg-light bg-opacity-50 cursor-pointer card-hover">
                                    <input type="radio" name="responses[{{ $index }}][answer_id]" value="{{ $answer->id }}" class="form-check-input question-option-input" data-question-index="{{ $index + 1 }}">
                                    <span class="text-dark">{{ $answer->content }}</span>
                                </label>
                            @endforeach
                        </div>
                    @elseif($question->type === 'short_answer')
                        <div class="mt-3">
                            <textarea name="responses[{{ $index }}][text_answer]" rows="4" class="form-control question-option-input" data-question-index="{{ $index + 1 }}" placeholder="Nhập câu trả lời chi tiết của bạn tại đây..."></textarea>
                        </div>
                    @endif
                </div>
            @endforeach
        </form>
    </div>
    
    <!-- Navigation Pill Grid Column -->
    <div class="col-lg-4">
        <div class="card card-custom p-4 shadow-sm position-sticky" style="top: 140px;">
            <h6 class="fw-bold mb-3"><i class="fa-solid fa-list me-2"></i>Bản đồ câu hỏi</h6>
            
            <div class="d-flex flex-wrap gap-2 mb-4">
                @foreach($quiz->questions as $index => $q)
                    <a href="#question-sec-{{ $index + 1 }}" class="question-nav-pill" id="nav-pill-{{ $index + 1 }}">{{ $index + 1 }}</a>
                @endforeach
            </div>
            
            <div class="border-top pt-3 small text-muted">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="d-inline-block rounded-circle" style="width: 12px; height: 12px; background-color: var(--primary-color);"></span>
                    <span>Đang xem</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="d-inline-block rounded-circle border border-primary" style="width: 12px; height: 12px; background-color: #EEF2F6;"></span>
                    <span>Đã điền câu trả lời</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const timerDisplay = document.getElementById('timerDisplay');
    const timeTakenInput = document.getElementById('time_taken_seconds');
    const form = document.getElementById('attemptSubmitForm');

    // Timer logic
    const timeLimitMinutes = {{ $quiz->time_limit_minutes ?? 0 }};
    let timeRemainingSeconds = timeLimitMinutes * 60;
    let timeTakenSeconds = 0;

    if (timeLimitMinutes > 0) {
        function updateTimer() {
            const minutes = Math.floor(timeRemainingSeconds / 60);
            const seconds = timeRemainingSeconds % 60;
            timerDisplay.innerText = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            
            if (timeRemainingSeconds <= 0) {
                clearInterval(timerInterval);
                alert('Hết giờ làm bài! Hệ thống sẽ tự động nộp bài làm của bạn.');
                submitExamDirectly();
            }
            timeRemainingSeconds--;
            timeTakenSeconds++;
            timeTakenInput.value = timeTakenSeconds;
        }

        updateTimer();
        const timerInterval = setInterval(updateTimer, 1000);
    } else {
        setInterval(() => {
            timeTakenSeconds++;
            timeTakenInput.value = timeTakenSeconds;
        }, 1000);
    }

    // Active state highlighting on navigation pills
    document.querySelectorAll('.question-option-input').forEach(input => {
        input.addEventListener('change', function () {
            const qIndex = this.getAttribute('data-question-index');
            const pill = document.getElementById(`nav-pill-${qIndex}`);
            if (pill) {
                pill.classList.add('answered');
            }
        });
        
        // Handle textarea typing
        input.addEventListener('input', function () {
            const qIndex = this.getAttribute('data-question-index');
            const pill = document.getElementById(`nav-pill-${qIndex}`);
            if (pill) {
                if (this.value.trim() !== '') {
                    pill.classList.add('answered');
                } else {
                    pill.classList.remove('answered');
                }
            }
        });
    });

    window.triggerSubmit = function () {
        if (confirm('Bạn có chắc chắn muốn nộp bài làm này không?')) {
            submitExamDirectly();
        }
    };

    function submitExamDirectly() {
        window.onbeforeunload = null;
        form.submit();
    }

    // Confirmation warning before navigation
    window.onbeforeunload = function () {
        return "Bài thi của bạn đang tiến hành. Thay đổi có thể không được lưu trữ!";
    };
});
</script>
@endsection
