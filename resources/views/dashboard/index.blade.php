@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold">Xin chào, {{ Auth::user()->name }}!</h2>
        <p class="text-muted mb-0">Hôm nay bạn muốn làm gì?</p>
    </div>
    <div>
        <span class="badge bg-primary px-3 py-2 fs-6 shadow-sm">
            <i class="fa-solid fa-user-shield me-1"></i> {{ Auth::user()->role === 'teacher' ? 'Bảng Điều Khiển Giáo Viên' : 'Bảng Học Tập Học Sinh' }}
        </span>
    </div>
</div>

@if(Auth::user()->role === 'teacher')
    <!-- Stats Row for Teacher -->
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card card-custom p-4 shadow-sm border-start border-primary border-4 card-hover">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted fw-semibold">Tổng Số Quiz</h6>
                        <h3 class="fw-bold mb-0 text-dark">{{ $quizCount }}</h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                        <i class="fa-solid fa-book-open fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-custom p-4 shadow-sm border-start border-success border-4 card-hover">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted fw-semibold">Câu Hỏi Đã Tạo</h6>
                        <h3 class="fw-bold mb-0 text-dark">{{ $questionCount }}</h3>
                    </div>
                    <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle">
                        <i class="fa-solid fa-circle-question fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-custom p-4 shadow-sm border-start border-warning border-4 card-hover">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted fw-semibold">Tài Liệu</h6>
                        <h3 class="fw-bold mb-0 text-dark">{{ $documentCount }}</h3>
                    </div>
                    <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-circle">
                        <i class="fa-solid fa-file-pdf fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-custom p-4 shadow-sm border-start border-danger border-4 card-hover">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted fw-semibold">Lượt Học Sinh Làm</h6>
                        <h3 class="fw-bold mb-0 text-dark">{{ $attemptCount }}</h3>
                    </div>
                    <div class="bg-danger bg-opacity-10 text-danger p-3 rounded-circle">
                        <i class="fa-solid fa-user-pen fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions & Recent Activity -->
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card card-custom p-4 shadow-sm h-100">
                <h5 class="fw-bold mb-4"><i class="fa-solid fa-bolt text-warning me-2"></i>Thao tác nhanh</h5>
                <div class="d-grid gap-3">
                    <a href="{{ route('quizzes.create') }}" class="btn btn-outline-primary text-start py-3 px-3 shadow-none border-1 card-hover">
                        <i class="fa-solid fa-plus-circle me-2"></i> Tạo bài Quiz mới
                    </a>
                    <a href="{{ route('documents.index') }}" class="btn btn-outline-success text-start py-3 px-3 shadow-none border-1 card-hover">
                        <i class="fa-solid fa-file-upload me-2"></i> Tải lên tài liệu giảng dạy
                    </a>
                    <a href="{{ route('ai.generate.form') }}" class="btn btn-gradient text-start py-3 px-3 card-hover text-white">
                        <i class="fa-solid fa-wand-magic-sparkles me-2"></i> Tự động tạo câu hỏi bằng AI
                    </a>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card card-custom p-4 shadow-sm h-100">
                <h5 class="fw-bold mb-4"><i class="fa-solid fa-chart-line text-primary me-2"></i>Lượt làm bài gần đây của học sinh</h5>
                @if($recentAttempts->isEmpty())
                    <div class="text-center py-5">
                        <i class="fa-solid fa-circle-info text-muted fa-3x mb-3"></i>
                        <p class="text-muted">Chưa có lượt làm bài nào từ học sinh.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Học sinh</th>
                                    <th>Bài trắc nghiệm</th>
                                    <th>Điểm số</th>
                                    <th>Kết quả</th>
                                    <th>Ngày làm bài</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentAttempts as $attempt)
                                    <tr>
                                        <td><strong>{{ $attempt->user->name }}</strong></td>
                                        <td>{{ $attempt->quiz->title }}</td>
                                        <td>
                                            <span class="fw-bold text-primary">{{ $attempt->score }}</span> / {{ $attempt->total_points }}
                                        </td>
                                        <td>
                                            @php
                                                $percentage = $attempt->total_points > 0 ? ($attempt->score / $attempt->total_points) * 100 : 0;
                                            @endphp
                                            @if($percentage >= 70)
                                                <span class="badge bg-success">Đạt</span>
                                            @else
                                                <span class="badge bg-danger">Không đạt</span>
                                            @endif
                                        </td>
                                        <td>{{ $attempt->completed_at ? $attempt->completed_at->format('H:i d/m/Y') : 'Chưa nộp' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@else
    <!-- Stats Row for Student -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card card-custom p-4 shadow-sm border-start border-primary border-4 card-hover">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted fw-semibold">Bài Quiz Sẵn Sàng</h6>
                        <h3 class="fw-bold mb-0 text-dark">{{ $availableQuizzesCount }}</h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                        <i class="fa-solid fa-pen-to-square fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-custom p-4 shadow-sm border-start border-success border-4 card-hover">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted fw-semibold">Bài Đã Hoàn Thành</h6>
                        <h3 class="fw-bold mb-0 text-dark">{{ $attemptsCount }}</h3>
                    </div>
                    <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle">
                        <i class="fa-solid fa-circle-check fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-custom p-4 shadow-sm border-start border-info border-4 card-hover">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted fw-semibold">Điểm Trung Bình (%)</h6>
                        <h3 class="fw-bold mb-0 text-dark">{{ number_format($averageScore, 1) }}%</h3>
                    </div>
                    <div class="bg-info bg-opacity-10 text-info p-3 rounded-circle">
                        <i class="fa-solid fa-chart-simple fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Available Quizzes & History -->
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card card-custom p-4 shadow-sm h-100">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0"><i class="fa-solid fa-fire text-danger me-2"></i>Quiz nổi bật nên làm</h5>
                    <a href="{{ route('attempts.available') }}" class="text-primary text-decoration-none small fw-medium">Xem tất cả</a>
                </div>
                
                @if($recentQuizzes->isEmpty())
                    <div class="text-center py-5">
                        <i class="fa-solid fa-folder-open text-muted fa-3x mb-3"></i>
                        <p class="text-muted">Không có quiz nổi bật nào khả dụng.</p>
                    </div>
                @else
                    <div class="list-group list-group-flush">
                        @foreach($recentQuizzes as $quiz)
                            <div class="list-group-item px-0 py-3 border-0 border-bottom">
                                <div class="d-flex w-100 justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1 fw-bold text-dark">{{ $quiz->title }}</h6>
                                        <small class="text-muted d-block mb-1">
                                            <i class="fa-solid fa-circle-question me-1"></i> {{ $quiz->questions_count }} câu hỏi 
                                            | <i class="fa-solid fa-clock ms-2 me-1"></i> {{ $quiz->time_limit_minutes ?? 'Không giới hạn' }} phút
                                        </small>
                                    </div>
                                    <form action="{{ route('attempts.start', $quiz) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 fw-semibold shadow-none border-0">
                                            <i class="fa-solid fa-play me-1"></i> Bắt đầu
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card card-custom p-4 shadow-sm h-100">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0"><i class="fa-solid fa-clock-rotate-left text-primary me-2"></i>Lịch sử làm bài gần đây</h5>
                    <a href="{{ route('attempts.history') }}" class="text-primary text-decoration-none small fw-medium">Tất cả lịch sử</a>
                </div>

                @if($recentAttemptsStudent->isEmpty())
                    <div class="text-center py-5">
                        <i class="fa-solid fa-circle-info text-muted fa-3x mb-3"></i>
                        <p class="text-muted">Bạn chưa hoàn thành bài quiz nào.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Bài Quiz</th>
                                    <th>Điểm</th>
                                    <th>Kết quả</th>
                                    <th>Chi tiết</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentAttemptsStudent as $attempt)
                                    <tr>
                                        <td><strong>{{ $attempt->quiz->title }}</strong></td>
                                        <td><span class="text-primary fw-bold">{{ $attempt->score }}</span> / {{ $attempt->total_points }}</td>
                                        <td>
                                            @php
                                                $percentage = $attempt->total_points > 0 ? ($attempt->score / $attempt->total_points) * 100 : 0;
                                            @endphp
                                            @if($percentage >= 70)
                                                <span class="badge bg-success">Đạt</span>
                                            @else
                                                <span class="badge bg-danger">Không đạt</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('attempts.result', $attempt) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-none border-1">
                                                <i class="fa-solid fa-eye me-1"></i> Xem bài
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endif
@endsection
