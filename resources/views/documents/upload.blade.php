@extends('layouts.app')

@section('title', 'Tải lên Tài Liệu')

@section('content')
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('documents.index') }}" class="text-decoration-none">Tài liệu</a></li>
            <li class="breadcrumb-item active" aria-current="page">Upload</li>
        </ol>
    </nav>
    <h2 class="fw-bold">Tải lên tài liệu tham khảo</h2>
    <p class="text-muted">Đăng tải bài giảng, sách hoặc văn bản để RAG AI xử lý</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card card-custom p-4 shadow-sm">
            <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="mb-4">
                    <label for="title" class="form-label fw-bold">Tên hiển thị tài liệu <span class="text-danger">*</span></label>
                    <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" placeholder="Ví dụ: Chương 1 - Biến và Hàm trong PHP" required>
                    @error('title')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="file" class="form-label fw-bold">Chọn tệp tài liệu <span class="text-danger">*</span></label>
                    <div class="dropzone-area p-5 text-center border border-2 border-dashed rounded-3 bg-light" id="dropzone">
                        <i class="fa-solid fa-cloud-arrow-up text-primary fa-3x mb-3"></i>
                        <h6 class="fw-bold">Kéo thả tệp vào đây hoặc nhấn để chọn</h6>
                        <span class="text-muted small">Hỗ trợ các định dạng: **TXT, PDF, DOCX** (Tối đa 10MB)</span>
                        <input type="file" name="file" id="file" class="form-control mt-3 d-none" accept=".txt,.pdf,.docx" required>
                        <div class="mt-3 fw-semibold text-primary d-none" id="fileNameDisplay"></div>
                    </div>
                    @error('file')
                        <div class="text-danger small mt-2"><strong>{{ $message }}</strong></div>
                    @enderror
                </div>

                <div class="d-flex gap-3 border-top pt-4">
                    <button type="submit" class="btn btn-gradient px-4 py-2 text-white fw-semibold"><i class="fa-solid fa-upload me-1"></i>Bắt đầu Upload</button>
                    <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary px-4 py-2 border-1"><i class="fa-solid fa-times me-1"></i>Hủy</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const dropzone = document.getElementById('dropzone');
    const fileInput = document.getElementById('file');
    const display = document.getElementById('fileNameDisplay');

    dropzone.addEventListener('click', () => fileInput.click());

    fileInput.addEventListener('change', function () {
        if (fileInput.files.length > 0) {
            display.innerText = "Tệp đã chọn: " + fileInput.files[0].name;
            display.classList.remove('d-none');
        }
    });

    dropzone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropzone.classList.add('bg-secondary', 'bg-opacity-10');
    });

    dropzone.addEventListener('dragleave', () => {
        dropzone.classList.remove('bg-secondary', 'bg-opacity-10');
    });

    dropzone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropzone.classList.remove('bg-secondary', 'bg-opacity-10');
        if (e.dataTransfer.files.length > 0) {
            fileInput.files = e.dataTransfer.files;
            display.innerText = "Tệp đã chọn: " + fileInput.files[0].name;
            display.classList.remove('d-none');
        }
    });
});
</script>
@endsection
