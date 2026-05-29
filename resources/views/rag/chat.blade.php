@extends('layouts.app')

@section('title', 'Hỏi AI Trợ Giảng')

@section('content')
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('attempts.history') }}" class="text-decoration-none">Lịch sử làm bài</a></li>
            <li class="breadcrumb-item"><a href="{{ route('attempts.result', $attempt) }}" class="text-decoration-none">Báo cáo kết quả</a></li>
            <li class="breadcrumb-item active" aria-current="page">Hỏi AI Trợ giảng</li>
        </ol>
    </nav>
    <h2 class="fw-bold">Hỏi AI Trợ Giảng (RAG Agent)</h2>
    <p class="text-muted">Đặt câu hỏi thắc mắc về bài làm, AI sẽ giải thích chi tiết dựa trên tài liệu giảng dạy</p>
</div>

<div class="row g-4">
    <!-- Sidebar incorrect questions -->
    <div class="col-lg-4">
        <div class="card card-custom p-4 shadow-sm h-100">
            <h6 class="fw-bold mb-3"><i class="fa-solid fa-circle-question text-danger me-2"></i>Các câu hỏi trả lời sai</h6>
            <div class="list-group list-group-flush" style="max-height: 50vh; overflow-y: auto;">
                @php $hasIncorrect = false; @endphp
                @foreach($attempt->details as $index => $detail)
                    @if(!$detail->is_correct)
                        @php $hasIncorrect = true; @endphp
                        <button type="button" class="list-group-item list-group-item-action border-0 border-bottom px-0 py-3 prefill-question-btn" data-question="Giải thích giúp em tại sao câu hỏi: '{{ addslashes($detail->question->content) }}' lại có đáp án đúng là gì và tại sao em lại làm sai?">
                            <span class="text-danger fw-bold me-1">Câu {{ $index + 1 }}.</span>
                            <span class="text-dark small text-truncate-1 d-inline-block" style="max-width: 80%; vertical-align: bottom;">{{ $detail->question->content }}</span>
                        </button>
                    @endif
                @endforeach
                
                @if(!$hasIncorrect)
                    <div class="text-center py-4">
                        <i class="fa-solid fa-circle-check text-success fa-2x mb-2"></i>
                        <p class="text-muted small">Tuyệt vời! Bạn đã trả lời đúng tất cả các câu hỏi.</p>
                    </div>
                @endif
            </div>
            
            <div class="alert alert-info border-0 mt-4 small shadow-none">
                <i class="fa-solid fa-info-circle me-2"></i> AI Trợ giảng sẽ lấy dữ liệu nền tảng từ giáo trình **"{{ $conversation->document->title ?? 'Tài liệu liên kết Quiz' }}"** để trả lời chính xác nhất.
            </div>
        </div>
    </div>

    <!-- Chat interface -->
    <div class="col-lg-8">
        <div class="card card-custom shadow-sm h-100 d-flex flex-column" style="min-height: 60vh;">
            <!-- Messages Header -->
            <div class="card-header bg-transparent border-bottom p-3 d-flex align-items-center gap-3">
                <div class="p-2 bg-primary bg-opacity-10 text-primary rounded-circle">
                    <i class="fa-solid fa-robot fa-lg"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-0">AI Assistant trực tuyến</h6>
                    <small class="text-success"><i class="fa-solid fa-circle fa-xs me-1"></i>Sẵn sàng hỗ trợ</small>
                </div>
            </div>
            
            <!-- Messages Body -->
            <div class="card-body p-4 flex-grow-1 overflow-auto" id="chatMessages" style="height: 400px; background-color: #F9FAFB;">
                <div class="chat-bubble assistant mb-3 d-flex gap-3">
                    <div class="bg-primary text-white p-2 rounded-circle align-self-start" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; font-size: 0.8rem;">
                        AI
                    </div>
                    <div class="p-3 bg-white border rounded-3 text-dark shadow-sm small" style="max-width: 80%;">
                        Xin chào! Mình là AI Trợ giảng của bạn. Mình đã đọc bài làm của bạn trong Quiz **"{{ $attempt->quiz->title }}"** và tài liệu tham khảo giảng dạy. 
                        Bạn có thắc mắc gì về bất kỳ câu hỏi nào trong bài trắc nghiệm vừa rồi không? Mình sẽ giải đáp chi tiết nhất dựa trên giáo trình.
                    </div>
                </div>

                @foreach($messages as $msg)
                    @if($msg->role === 'user')
                        <div class="chat-bubble user mb-3 d-flex gap-3 justify-content-end">
                            <div class="p-3 bg-primary text-white rounded-3 shadow-sm small" style="max-width: 80%;">
                                {{ $msg->content }}
                            </div>
                            <div class="bg-secondary text-white p-2 rounded-circle align-self-start" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; font-size: 0.8rem;">
                                Ta
                            </div>
                        </div>
                    @else
                        <div class="chat-bubble assistant mb-3 d-flex gap-3">
                            <div class="bg-primary text-white p-2 rounded-circle align-self-start" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; font-size: 0.8rem;">
                                AI
                            </div>
                            <div class="p-3 bg-white border rounded-3 text-dark shadow-sm small" style="max-width: 80%; white-space: pre-wrap;">{{ $msg->content }}</div>
                        </div>
                    @endif
                @endforeach
                
                <!-- Typing indicator -->
                <div class="chat-bubble assistant mb-3 d-flex gap-3 d-none" id="typingIndicator">
                    <div class="bg-primary text-white p-2 rounded-circle align-self-start" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; font-size: 0.8rem;">
                        AI
                    </div>
                    <div class="p-3 bg-white border rounded-3 text-dark shadow-sm small">
                        <span class="spinner-grow spinner-grow-sm text-primary" role="status"></span>
                        <span class="spinner-grow spinner-grow-sm text-primary" role="status"></span>
                        <span class="spinner-grow spinner-grow-sm text-primary" role="status"></span>
                    </div>
                </div>
            </div>
            
            <!-- Messages Footer -->
            <div class="card-footer bg-transparent border-top p-3">
                <form id="chatForm" class="d-flex gap-2">
                    <input type="text" id="chatInput" class="form-control rounded-pill px-4 shadow-none border-1" placeholder="Nhập câu hỏi của bạn tại đây..." required autocomplete="off">
                    <button type="submit" class="btn btn-gradient rounded-circle p-2 text-white d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const chatMessages = document.getElementById('chatMessages');
    const chatForm = document.getElementById('chatForm');
    const chatInput = document.getElementById('chatInput');
    const typingIndicator = document.getElementById('typingIndicator');
    const attemptId = {{ $attempt->id }};
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    let conversationId = {{ $conversation->id }};

    function scrollToBottom() {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    scrollToBottom();

    // Handle prefill buttons
    document.querySelectorAll('.prefill-question-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            chatInput.value = this.getAttribute('data-question');
            chatInput.focus();
        });
    });

    // Check if there is an URL query to prefill question
    const urlParams = new URLSearchParams(window.location.search);
    const prefillQId = urlParams.get('prefill_question_id');
    if (prefillQId) {
        chatInput.value = "Giải thích chi tiết hơn cho em về kiến thức liên quan đến câu hỏi sai trong bài thi trắc nghiệm này.";
        chatInput.focus();
    }

    chatForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        const questionText = chatInput.value.trim();
        if (!questionText) return;

        // Render user bubble
        const userDiv = document.createElement('div');
        userDiv.className = 'chat-bubble user mb-3 d-flex gap-3 justify-content-end';
        userDiv.innerHTML = `
            <div class="p-3 bg-primary text-white rounded-3 shadow-sm small" style="max-width: 80%;">
                ${escapeHtml(questionText)}
            </div>
            <div class="bg-secondary text-white p-2 rounded-circle align-self-start" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; font-size: 0.8rem;">
                Ta
            </div>
        `;
        chatMessages.appendChild(userDiv);
        chatInput.value = '';
        
        // Show typing indicator
        typingIndicator.classList.remove('d-none');
        chatMessages.appendChild(typingIndicator); // move to bottom
        scrollToBottom();

        try {
            const response = await fetch(`/attempts/${attemptId}/rag/ask`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    question: questionText,
                    conversation_id: conversationId
                })
            });

            const data = await response.json();
            
            // Remove typing indicator
            typingIndicator.classList.add('d-none');

            if (data.answer) {
                // Render assistant bubble
                const aiDiv = document.createElement('div');
                aiDiv.className = 'chat-bubble assistant mb-3 d-flex gap-3';
                aiDiv.innerHTML = `
                    <div class="bg-primary text-white p-2 rounded-circle align-self-start" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; font-size: 0.8rem;">
                        AI
                    </div>
                    <div class="p-3 bg-white border rounded-3 text-dark shadow-sm small" style="max-width: 80%; white-space: pre-wrap;">${escapeHtml(data.answer)}</div>
                `;
                chatMessages.appendChild(aiDiv);
            } else {
                alert('Có lỗi kết nối hệ thống AI. Vui lòng thử lại sau.');
            }
        } catch (err) {
            typingIndicator.classList.add('d-none');
            alert('Lỗi kết nối mạng hoặc API AI quá tải.');
        }

        scrollToBottom();
    });

    function escapeHtml(text) {
        return text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
});
</script>
@endsection
