@extends('layouts.app')

@section('title', 'Tạo Câu Hỏi Mới')

@section('content')
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('quizzes.index') }}" class="text-decoration-none">Quản lý Quiz</a></li>
            <li class="breadcrumb-item"><a href="{{ route('quizzes.show', $quiz) }}" class="text-decoration-none">{{ $quiz->title }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">Tạo câu hỏi</li>
        </ol>
    </nav>
    <h2 class="fw-bold">Thêm câu hỏi mới</h2>
    <p class="text-muted">Tạo câu hỏi trắc nghiệm, đúng/sai hoặc tự luận thủ công</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card card-custom p-4 shadow-sm">
            <form action="{{ route('quizzes.questions.store', $quiz) }}" method="POST" id="questionForm">
                @csrf
                
                <div class="mb-4">
                    <label for="content" class="form-label fw-bold">Nội dung câu hỏi <span class="text-danger">*</span></label>
                    <textarea name="content" id="content" class="form-control @error('content') is-invalid @enderror" rows="3" placeholder="Nhập câu hỏi tại đây..." required>{{ old('content') }}</textarea>
                    @error('content')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="type" class="form-label fw-bold">Loại câu hỏi <span class="text-danger">*</span></label>
                        <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                            <option value="multiple_choice" {{ old('type') === 'multiple_choice' ? 'selected' : '' }}>Trắc nghiệm nhiều lựa chọn</option>
                            <option value="true_false" {{ old('type') === 'true_false' ? 'selected' : '' }}>Lựa chọn Đúng / Sai</option>
                            <option value="short_answer" {{ old('type') === 'short_answer' ? 'selected' : '' }}>Tự luận (Học sinh nhập chữ)</option>
                        </select>
                        @error('type')
                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="points" class="form-label fw-bold">Số điểm câu hỏi <span class="text-danger">*</span></label>
                        <input type="number" name="points" id="points" class="form-control @error('points') is-invalid @enderror" value="{{ old('points', 1) }}" min="1" max="100" required>
                        @error('points')
                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>
                </div>

                <!-- Multiple Choice & True/False Answers Container -->
                <div id="answersContainer" class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <label class="form-label fw-bold mb-0">Thiết lập các đáp án lựa chọn</label>
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-none border-1" id="addAnswerBtn">
                            <i class="fa-solid fa-plus-circle me-1"></i> Thêm phương án
                        </button>
                    </div>

                    <div class="alert alert-warning border-0 small mb-3 shadow-none" id="tfWarning" style="display:none;">
                        <i class="fa-solid fa-exclamation-triangle me-2"></i> Đối với câu hỏi Đúng/Sai, hệ thống sẽ tự động khởi tạo 2 đáp án: "Đúng" và "Sai".
                    </div>

                    <div id="answersList">
                        <!-- Dynamic Answers will be appended here -->
                    </div>
                    @error('answers')
                        <div class="text-danger small mt-2"><strong>{{ $message }}</strong></div>
                    @enderror
                </div>

                <!-- Short Answer Reference Container -->
                <div id="shortAnswerContainer" class="mb-4" style="display:none;">
                    <label class="form-label fw-bold">Đáp án mẫu <span class="text-muted fw-normal">(tùy chọn)</span></label>
                    <div class="alert alert-info border-0 small mb-3 shadow-none">
                        <i class="fa-solid fa-circle-info me-2"></i> Nhập đáp án mẫu để học sinh tham khảo sau khi nộp bài. Câu tự luận sẽ được chấm thủ công.
                    </div>
                    <textarea name="answers[0][content]" id="shortAnswerText" class="form-control" rows="3" placeholder="Nhập đáp án mẫu tham khảo...">{{ old('answers.0.content') }}</textarea>
                    <input type="hidden" name="answers[0][is_correct]" value="1">
                </div>

                <div class="mb-4">
                    <label for="explanation" class="form-label fw-bold">Giải thích đáp án</label>
                    <textarea name="explanation" id="explanation" class="form-control @error('explanation') is-invalid @enderror" rows="2" placeholder="Nhập lý do hoặc căn cứ khoa học cho đáp án đúng...">{{ old('explanation') }}</textarea>
                    <small class="text-muted">Học sinh sẽ nhìn thấy lời giải thích này sau khi hoàn thành bài nộp.</small>
                    @error('explanation')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="d-flex gap-3 border-top pt-4">
                    <button type="submit" class="btn btn-gradient px-4 py-2 text-white fw-semibold"><i class="fa-solid fa-save me-1"></i>Lưu câu hỏi</button>
                    <a href="{{ route('quizzes.show', $quiz) }}" class="btn btn-outline-secondary px-4 py-2 border-1"><i class="fa-solid fa-times me-1"></i>Hủy</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const typeSelect = document.getElementById('type');
    const answersContainer = document.getElementById('answersContainer');
    const shortAnswerContainer = document.getElementById('shortAnswerContainer');
    const answersList = document.getElementById('answersList');
    const addAnswerBtn = document.getElementById('addAnswerBtn');
    const tfWarning = document.getElementById('tfWarning');

    let answerIndex = 0;

    function addAnswerRow(content = '', isCorrect = false, readonly = false) {
        const div = document.createElement('div');
        div.className = 'answer-row card bg-light border-0 p-3 mb-2';
        div.innerHTML = `
            <div class="row align-items-center g-3">
                <div class="col">
                    <input type="text" name="answers[${answerIndex}][content]" class="form-control" value="${content}" placeholder="Nhập phương án trả lời..." required ${readonly ? 'readonly' : ''}>
                </div>
                <div class="col-auto d-flex align-items-center gap-3">
                    <div class="form-check mb-0">
                        <input class="form-check-input correct-radio" type="radio" name="correct_answer_index" value="${answerIndex}" ${isCorrect ? 'checked' : ''} required>
                        <label class="form-check-label text-dark fw-semibold small">Đúng</label>
                    </div>
                    <input type="hidden" name="answers[${answerIndex}][is_correct]" class="is-correct-hidden" value="${isCorrect ? '1' : '0'}">
                    ${!readonly ? `<button type="button" class="btn btn-sm btn-link text-danger p-0 border-0 remove-answer-btn"><i class="fa-solid fa-trash-can fa-lg"></i></button>` : ''}
                </div>
            </div>
        `;
        answersList.appendChild(div);
        
        // Handle radio selection syncing with hidden inputs
        const radio = div.querySelector('.correct-radio');
        radio.addEventListener('change', function () {
            document.querySelectorAll('.is-correct-hidden').forEach(hidden => hidden.value = '0');
            div.querySelector('.is-correct-hidden').value = '1';
        });

        if (!readonly) {
            div.querySelector('.remove-answer-btn').addEventListener('click', function () {
                div.remove();
                validateAnswerCount();
            });
        }
        
        answerIndex++;
    }

    function validateAnswerCount() {
        const rows = answersList.querySelectorAll('.answer-row');
        if (rows.length <= 2) {
            answersList.querySelectorAll('.remove-answer-btn').forEach(btn => btn.style.display = 'none');
        } else {
            answersList.querySelectorAll('.remove-answer-btn').forEach(btn => btn.style.display = 'inline-block');
        }
    }

    function renderOptions() {
        answersList.innerHTML = '';
        answerIndex = 0;
        const type = typeSelect.value;

        shortAnswerContainer.style.display = 'none';

        if (type === 'multiple_choice') {
            answersContainer.style.display = 'block';
            addAnswerBtn.style.display = 'inline-block';
            tfWarning.style.display = 'none';
            // Default 4 choices
            addAnswerRow('', true);
            addAnswerRow('', false);
            addAnswerRow('', false);
            addAnswerRow('', false);
            validateAnswerCount();
        } else if (type === 'true_false') {
            answersContainer.style.display = 'block';
            addAnswerBtn.style.display = 'none';
            tfWarning.style.display = 'block';
            // 2 permanent choices
            addAnswerRow('Đúng', true, true);
            addAnswerRow('Sai', false, true);
        } else {
            answersContainer.style.display = 'none';
            shortAnswerContainer.style.display = 'block';
        }
    }

    typeSelect.addEventListener('change', renderOptions);
    addAnswerBtn.addEventListener('click', () => {
        addAnswerRow('', false);
        validateAnswerCount();
    });

    // Form submit validation: force selection of correct answer
    document.getElementById('questionForm').addEventListener('submit', function (e) {
        const type = typeSelect.value;
        if (type !== 'short_answer') {
            const correctChecked = answersList.querySelector('.correct-radio:checked');
            if (!correctChecked) {
                e.preventDefault();
                alert('Vui lòng chọn ít nhất một phương án là đáp án Đúng!');
            }
        }
    });

    // Init state
    renderOptions();
});
</script>
@endsection
