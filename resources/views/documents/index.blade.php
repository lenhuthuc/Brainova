@extends('layouts.app')

@section('title', 'Quản lý Tài Liệu')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold">Quản lý Tài Liệu RAG</h2>
        <p class="text-muted mb-0">Tải lên các tài liệu để làm tư liệu tham khảo cho AI RAG Agent</p>
    </div>
    @if(Auth::user()->role === 'teacher')
        <a href="{{ route('documents.create') }}" class="btn btn-gradient px-4 py-2 fw-semibold">
            <i class="fa-solid fa-cloud-arrow-up me-1"></i> Upload Tài Liệu
        </a>
    @endif
</div>

@if($documents->isEmpty())
    <div class="card card-custom p-5 text-center shadow-sm">
        <div class="py-5">
            <i class="fa-solid fa-file-invoice text-muted fa-4x mb-4"></i>
            <h4 class="fw-bold">Không tìm thấy tài liệu nào</h4>
            <p class="text-muted mb-4">Tải lên tài liệu giáo trình (txt, pdf, docx) để làm dữ liệu nền tảng cho AI tạo câu hỏi và hỗ trợ học sinh.</p>
            @if(Auth::user()->role === 'teacher')
                <a href="{{ route('documents.create') }}" class="btn btn-primary px-4 py-2"><i class="fa-solid fa-cloud-arrow-up me-1"></i> Bắt đầu Upload</a>
            @endif
        </div>
    </div>
@else
    <div class="card card-custom shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Tên tài liệu</th>
                        <th>Định dạng</th>
                        <th>Kích thước file</th>
                        <th>Ngày tải lên</th>
                        <th class="pe-4 text-end">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($documents as $doc)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="p-2 bg-primary bg-opacity-10 text-primary rounded">
                                        @if(strtolower($doc->file_type) === 'pdf')
                                            <i class="fa-solid fa-file-pdf fa-lg text-danger"></i>
                                        @elseif(strtolower($doc->file_type) === 'docx')
                                            <i class="fa-solid fa-file-word fa-lg text-primary"></i>
                                        @else
                                            <i class="fa-solid fa-file-lines fa-lg text-secondary"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <span class="fw-bold text-dark d-block">{{ $doc->title }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border px-2 py-1 uppercase">{{ strtoupper($doc->file_type) }}</span>
                            </td>
                            <td>
                                @php
                                    $filePath = storage_path('app/private/' . $doc->file_path);
                                    $size = file_exists($filePath) ? filesize($filePath) : 0;
                                    $formattedSize = $size > 1024 * 1024 
                                        ? number_format($size / (1024 * 1024), 2) . ' MB' 
                                        : number_format($size / 1024, 2) . ' KB';
                                @endphp
                                {{ $formattedSize }}
                            </td>
                            <td>{{ $doc->created_at->format('H:i d/m/Y') }}</td>
                            <td class="pe-4 text-end">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('documents.show', $doc) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-none border-1">
                                        <i class="fa-solid fa-eye me-1"></i> Xem nội dung
                                    </a>
                                    <form action="{{ route('documents.destroy', $doc) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa tài liệu này không?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle px-2" title="Xóa">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection
