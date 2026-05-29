@extends('layouts.app')

@section('title', 'Lịch Sử Làm Bài')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold">Lịch sử kiểm tra</h2>
        <p class="text-muted mb-0">Xem lại toàn bộ kết quả các bài thi trắc nghiệm đã nộp</p>
    </div>
</div>

@if($attempts->isEmpty())
    <div class="card card-custom p-5 text-center shadow-sm">
        <div class="py-5">
            <i class="fa-solid fa-clock-rotate-left text-muted fa-4x mb-4"></i>
            <h4 class="fw-bold">Chưa có lịch sử làm bài</h4>
            <p class="text-muted mb-4">Bạn chưa thực hiện bất kỳ bài trắc nghiệm nào trong hệ thống QuizMaster.</p>
            <a href="{{ route('attempts.available') }}" class="btn btn-gradient px-4 py-2 text-white fw-bold card-hover"><i class="fa-solid fa-pen-fancy me-1"></i> Làm bài ngay</a>
        </div>
    </div>
@else
    <div class="card card-custom shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Tên bài Quiz</th>
                        <th>Thời gian bắt đầu</th>
                        <th>Thời gian nộp</th>
                        <th>Điểm đạt được</th>
                        <th>Tỉ lệ đúng (%)</th>
                        <th>Trạng thái</th>
                        <th class="pe-4 text-end">Chi tiết</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attempts as $attempt)
                        <tr>
                            <td class="ps-4"><strong>{{ $attempt->quiz->title }}</strong></td>
                            <td>{{ $attempt->started_at->format('H:i d/m/Y') }}</td>
                            <td>{{ $attempt->completed_at ? $attempt->completed_at->format('H:i d/m/Y') : 'Chưa hoàn thành' }}</td>
                            <td>
                                <span class="fw-bold text-primary">{{ number_format($attempt->score, 1) }}</span> / {{ number_format($attempt->total_points, 1) }}
                            </td>
                            <td>
                                @php
                                    $pct = $attempt->total_points > 0 ? ($attempt->score / $attempt->total_points) * 100 : 0;
                                @endphp
                                <span class="fw-semibold">{{ number_format($pct, 1) }}%</span>
                            </td>
                            <td>
                                @if($pct >= 70)
                                    <span class="badge bg-success">Đạt</span>
                                @else
                                    <span class="badge bg-danger">Không đạt</span>
                                @endif
                            </td>
                            <td class="pe-4 text-end">
                                <a href="{{ route('attempts.result', $attempt) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-none border-1">
                                    <i class="fa-solid fa-eye me-1"></i> Xem bài nộp
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection
