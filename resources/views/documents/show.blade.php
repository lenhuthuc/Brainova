@extends('layouts.app')

@section('title', $document->title)

@section('content')
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('documents.index') }}" class="text-decoration-none">Tài liệu</a></li>
            <li class="breadcrumb-item active" aria-current="page">Xem nội dung</li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold mb-1">{{ $document->title }}</h2>
            <p class="text-muted mb-0">Loại file: {{ strtoupper($document->file_type) }} | Ngày upload: {{ $document->created_at->format('H:i d/m/Y') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('ai.generate.form', ['document_id' => $document->id]) }}" class="btn btn-gradient px-4 fw-semibold text-white">
                <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Sinh Quiz từ file này
            </a>
            <form action="{{ route('documents.destroy', $document) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa tài liệu này không?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger px-3"><i class="fa-solid fa-trash me-1"></i>Xóa</button>
            </form>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card card-custom p-4 shadow-sm">
            <h5 class="fw-bold mb-3 text-dark"><i class="fa-solid fa-file-waveform text-primary me-2"></i>Nội dung đã được AI trích xuất (Xem trước 2000 ký tự)</h5>
            <div class="bg-light p-4 rounded-3 border overflow-auto" style="max-height: 500px; font-family: 'Courier New', Courier, monospace; font-size: 0.9rem; white-space: pre-wrap; line-height: 1.6;">
                {{ Str::limit($document->content_text, 2000, ' ... [Nội dung còn tiếp]') }}
            </div>
            @if(strlen($document->content_text) > 2000)
                <div class="alert alert-info border-0 mt-3 small shadow-none">
                    <i class="fa-solid fa-info-circle me-2"></i> Dữ liệu văn bản này đã được nén lưu trữ thành công trong database. AI RAG Agent sẽ sử dụng toàn bộ nội dung tài liệu này để phản hồi khi học sinh hỏi thắc mắc lúc xem kết quả bài làm.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
