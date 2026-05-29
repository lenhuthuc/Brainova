@extends('layouts.app')

@section('title', 'Chỉnh Sửa Câu Hỏi')

@section('content')
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('quizzes.index') }}" class="text-decoration-none">Quản lý Quiz</a></li>
            <li class="breadcrumb-item"><a href="{{ route('quizzes.show', $quiz) }}" class="text-decoration-none">{{ $quiz->title }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">Sửa câu hỏi</li>
        </ol>
    </nav>
    <h2 class="fw-bold">Chỉnh sửa câu hỏi</h2>
    <p class="text-muted">Thay đổi nội dung, phương án hoặc số điểm của câu hỏi</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card card-custom p-4 shadow-sm">
            <form action="{{ route('quizzes.questions.update', [$quiz, $question]) }}" method="POST" id="questionForm">
                @csrf
                @method('PUT')
                
                <div class="mb-4">
                    <label for="content" class="form-label fw-bold">Nội dung câu hỏi <span class="text-danger">*</span></label>
                    <textarea name="content" id="content" class="form-control @error('content') is-invalid @enderror" rows="3" placeholder="Nhập câu hỏi tại đây..." required>{{ old('content', $question->content) }}</textarea>
                    @error('content')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="type" class="form-label fw-bold">Loại câu hỏi <span class="text-danger">*</span></label>
                        <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                            <option value="multiple_choice" {{ old('type', $question->type) === 'multiple_choice' ? 'selected' : '' }}>Trắc nghiệm nhiều lựa chọn</option>
                            <option value="true_false" {{ old('type', $question->type) === 'true_false' ? 'selected' : '' }}>Lựa chọn Đúng / Sai</option>
                            <option value="short_answer" {{ old('type', $question->type) === 'short_answer' ? 'selected' : '' }}>Tự luận (Học sinh nhập chữ)</option>
                        </select>
                        @error('type')
                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="points" class="form-label fw-bold">Số điểm câu hỏi <span class="text-danger">*</span></label>
                        <input type="number" name="points" id="points" class="form-control @error('points') is-invalid @enderror" value="{{ old('points', $question->points) }}" min="1" max="100" required>
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
                        <!-- Prepopulated answers dynamically -->
                    </div>
                    @error('answers')
                        <div class="text-danger small mt-2"><strong>{{ $message }}</strong></div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="explanation" class="form-label fw-bold">Giải thích đáp án</label>
                    <textarea name="explanation" id="explanation" class="form-control @error('explanation') is-invalid @enderror" rows="2" placeholder="Nhập lý do hoặc căn cứ khoa học cho đáp án đúng...">{{ old('explanation', $question->explanation) }}</textarea>
                    <small class="text-muted">Học sinh sẽ nhìn thấy lời giải thích này sau khi hoàn thành bài nộp.</small>
                    @error('explanation')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="d-flex gap-3 border-top pt-4">
                    <button type="submit" class="btn btn-gradient px-4 py-2 text-white fw-semibold"><i class="fa-solid fa-save me-1"></i>Cập nhật câu hỏi</button>
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
    const answersList = document.getElementById('answersList');
    const addAnswerBtn = document.getElementById('addAnswerBtn');
    const tfWarning = document.getElementById('tfWarning');

    let answerIndex = 0;

    // Load existing answers
    const existingAnswers = @json($question->answers->sortBy('sort_order')->values());

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

    function renderOptions(isInit = false) {
        const type = typeSelect.value;
        
        if (!isInit) {
            answersList.innerHTML = '';
            answerIndex = 0;
        }

        if (type === 'multiple_choice') {
            answersContainer.style.display = 'block';
            addAnswerBtn.style.display = 'inline-block';
            tfWarning.style.display = 'none';
            
            if (isInit && existingAnswers.length > 0) {
                existingAnswers.forEach(ans => addAnswerRow(ans.content, ans.is_correct));
            } else {
                addAnswerRow('', true);
                addAnswerRow('', false);
                addAnswerRow('', false);
                addAnswerRow('', false);
            }
            validateAnswerCount();
        } else if (type === 'true_false') {
            answersContainer.style.display = 'block';
            addAnswerBtn.style.display = 'none';
            tfWarning.style.display = 'block';
            
            if (isInit && existingAnswers.length > 0) {
                existingAnswers.forEach(ans => addAnswerRow(ans.content, ans.is_correct, true));
            } else {
                addAnswerRow('Đúng', true, true);
                addAnswerRow('Sai', false, true);
            }
        } else {
            answersContainer.style.display = 'none';
        }
    }

    typeSelect.addEventListener('change', () => renderOptions(false));
    addAnswerBtn.addEventListener('click', () => {
        addAnswerRow('', false);
        validateAnswerCount();
    });

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

    // Init load
    renderOptions(true);
});
</script>
@endsection
