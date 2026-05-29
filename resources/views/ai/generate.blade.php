@extends('layouts.app')

@section('title', 'AI Sinh Câu Hỏi')

@section('content')
<div class="mb-4">
    <h2 class="fw-bold"><i class="fa-solid fa-wand-magic-sparkles text-primary me-2"></i>AI Question Generator (RAG)</h2>
    <p class="text-muted">Sinh câu hỏi trắc nghiệm tự động từ nội dung tài liệu học tập của bạn bằng AI</p>
</div>

<div class="row g-4">
    <!-- Form Config Column -->
    <div class="col-lg-5">
        <div class="card card-custom p-4 shadow-sm h-100">
            <h5 class="fw-bold mb-4 border-bottom pb-2">Cấu hình sinh câu hỏi</h5>
            
            <form action="{{ route('ai.generate') }}" method="POST" id="generateForm">
                @csrf
                
                <div class="mb-3">
                    <label for="document_id" class="form-label fw-bold">Chọn tài liệu nguồn <span class="text-danger">*</span></label>
                    <select name="document_id" id="document_id" class="form-select @error('document_id') is-invalid @enderror" required>
                        <option value="">-- Chọn tài liệu tham khảo --</option>
                        @foreach($documents as $doc)
                            <option value="{{ $doc->id }}" {{ (old('document_id') == $doc->id || request('document_id') == $doc->id) ? 'selected' : '' }}>
                                {{ $doc->title }} ({{ strtoupper($doc->file_type) }})
                            </option>
                        @endforeach
                    </select>
                    @error('document_id')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="quiz_id" class="form-label fw-bold">Lưu vào bài Quiz <span class="text-danger">*</span></label>
                    <select name="quiz_id" id="quiz_id" class="form-select @error('quiz_id') is-invalid @enderror" required>
                        <option value="">-- Chọn bài Quiz đích --</option>
                        @foreach($quizzes as $quiz)
                            <option value="{{ $quiz->id }}" {{ (old('quiz_id') == $quiz->id || request('quiz_id') == $quiz->id) ? 'selected' : '' }}>
                                {{ $quiz->title }}
                            </option>
                        @endforeach
                    </select>
                    @error('quiz_id')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="number_of_questions" class="form-label fw-bold">Số lượng câu hỏi cần sinh (1-20)</label>
                    <input type="number" name="number_of_questions" id="number_of_questions" class="form-control @error('number_of_questions') is-invalid @enderror" value="{{ old('number_of_questions', 5) }}" min="1" max="20" required>
                    @error('number_of_questions')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="question_type" class="form-label fw-bold">Thể loại câu hỏi</label>
                    <select name="question_type" id="question_type" class="form-select" required>
                        <option value="multiple_choice" {{ old('question_type') === 'multiple_choice' ? 'selected' : '' }}>Trắc nghiệm nhiều lựa chọn</option>
                        <option value="true_false" {{ old('question_type') === 'true_false' ? 'selected' : '' }}>Đúng / Sai</option>
                        <option value="short_answer" {{ old('question_type') === 'short_answer' ? 'selected' : '' }}>Tự luận ngắn</option>
                        <option value="mixed" {{ old('question_type') === 'mixed' ? 'selected' : '' }}>Hỗn hợp tất cả các loại</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="difficulty" class="form-label fw-bold">Độ khó mong muốn</label>
                    <select name="difficulty" id="difficulty" class="form-select" required>
                        <option value="easy" {{ old('difficulty') === 'easy' ? 'selected' : '' }}>Dễ (Nhớ, Nhận biết)</option>
                        <option value="medium" {{ old('difficulty') === 'medium' ? 'selected' : '' }} selected>Trung Bình (Hiểu, Vận dụng)</option>
                        <option value="hard" {{ old('difficulty') === 'hard' ? 'selected' : '' }}>Khó (Phân tích, Đánh giá)</option>
                    </select>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-gradient py-3 text-white fw-bold card-hover" id="submitBtn">
                        <i class="fa-solid fa-gears me-1"></i> Bắt đầu sinh câu hỏi
                    </button>
                </div>
            </form>

            <div class="text-center mt-4 d-none" id="loadingArea">
                <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status"></div>
                <h6 class="fw-bold">RAG AI đang xử lý tài liệu...</h6>
                <p class="text-muted small">Thời gian xử lý có thể kéo dài từ 10-30 giây tùy theo kích thước tệp và API kết nối.</p>
            </div>
        </div>
    </div>
    
    <!-- Preview Generated Content Column -->
    <div class="col-lg-7">
        <div class="card card-custom p-4 shadow-sm h-100">
            <h5 class="fw-bold mb-4 border-bottom pb-2"><i class="fa-solid fa-magnifying-glass me-2"></i>Xem trước kết quả AI Sinh</h5>
            
            @if(isset($generatedQuestions) && count($generatedQuestions) > 0)
                <form action="{{ route('ai.generate.save') }}" method="POST">
                    @csrf
                    <input type="hidden" name="quiz_id" value="{{ $quizId }}">
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="selectAllCheckbox" checked>
                            <label class="form-check-label fw-semibold" for="selectAllCheckbox">Chọn tất cả câu hỏi</label>
                        </div>
                        <button type="submit" class="btn btn-success px-4 fw-semibold"><i class="fa-solid fa-save me-1"></i>Lưu câu hỏi đã chọn</button>
                    </div>

                    <div class="questions-preview-list" style="max-height: 60vh; overflow-y: auto; padding-right: 5px;">
                        @foreach($generatedQuestions as $index => $q)
                            <div class="card card-custom p-3 bg-light border-0 mb-3">
                                <div class="d-flex gap-3 align-items-start">
                                    <div class="form-check mt-1">
                                        <input class="form-check-input question-select-checkbox" type="checkbox" name="questions[{{ $index }}][selected]" value="1" checked>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="fw-bold text-dark mb-2">Câu {{ $index + 1 }}. {{ $q['content'] }}</h6>
                                        <input type="hidden" name="questions[{{ $index }}][content]" value="{{ $q['content'] }}">
                                        <input type="hidden" name="questions[{{ $index }}][type]" value="{{ $q['type'] }}">
                                        <input type="hidden" name="questions[{{ $index }}][points]" value="{{ $q['points'] ?? 1 }}">
                                        <input type="hidden" name="questions[{{ $index }}][explanation]" value="{{ $q['explanation'] ?? '' }}">
                                        
                                        @if(isset($q['answers']) && count($q['answers']) > 0)
                                            <ul class="list-group list-group-flush mb-2 border rounded shadow-none small">
                                                @foreach($q['answers'] as $aIndex => $ans)
                                                    <li class="list-group-item py-1 px-3 d-flex justify-content-between align-items-center {{ $ans['is_correct'] ? 'bg-success bg-opacity-10 text-success fw-bold' : '' }}">
                                                        <span>{{ $ans['content'] }}</span>
                                                        <input type="hidden" name="questions[{{ $index }}][answers][{{ $aIndex }}][content]" value="{{ $ans['content'] }}">
                                                        <input type="hidden" name="questions[{{ $index }}][answers][{{ $aIndex }}][is_correct]" value="{{ $ans['is_correct'] ? '1' : '0' }}">
                                                        @if($ans['is_correct'])
                                                            <span class="badge bg-success rounded-circle p-1"><i class="fa-solid fa-check text-white"></i></span>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                        
                                        @if(isset($q['explanation']) && $q['explanation'])
                                            <div class="mt-2 small text-muted p-2 bg-white rounded border border-start border-3 border-info">
                                                <strong>Giải thích:</strong> {{ $q['explanation'] }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </form>
            @else
                <div class="text-center py-5">
                    <i class="fa-solid fa-wand-magic-sparkles text-muted fa-4x mb-4"></i>
                    <h6 class="text-muted">Chưa có dữ liệu xem trước nào.</h6>
                    <p class="text-muted small px-4">Sau khi cấu hình và nhấn nút sinh câu hỏi, kết quả trích xuất cấu trúc RAG từ mô hình ngôn ngữ (Gemini/OpenAI) sẽ hiển thị chi tiết tại đây để bạn kiểm tra lại trước khi lưu.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('generateForm');
    const submitBtn = document.getElementById('submitBtn');
    const loadingArea = document.getElementById('loadingArea');

    if (form) {
        form.addEventListener('submit', function () {
            submitBtn.disabled = true;
            submitBtn.style.display = 'none';
            loadingArea.classList.remove('d-none');
        });
    }

    const selectAll = document.getElementById('selectAllCheckbox');
    if (selectAll) {
        selectAll.addEventListener('change', function () {
            document.querySelectorAll('.question-select-checkbox').forEach(cb => {
                cb.checked = selectAll.checked;
            });
        });
    }
});
</script>
@endsection
