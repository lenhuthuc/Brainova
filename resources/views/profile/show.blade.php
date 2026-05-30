@extends('layouts.app')

@section('title', 'Hồ sơ cá nhân')

@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card card-custom mb-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-4 mb-4">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold"
                         style="width: 80px; height: 80px; font-size: 32px;">
                        {{ substr($user->name, 0, 1) }}
                    </div>
                    <div>
                        <h2 class="mb-1">{{ $user->name }}</h2>
                        <p class="text-muted mb-2">{{ $user->email }}</p>
                        <span class="badge bg-primary-subtle text-primary">
                            {{ $user->role === 'teacher' ? 'Giáo Viên' : 'Học Sinh' }}
                        </span>
                    </div>
                </div>

                <div class="row mt-4 pt-3 border-top">
                    <div class="col-md-6">
                        <small class="text-muted d-block mb-1">Tên đăng nhập</small>
                        <p class="fw-500">{{ $user->name }}</p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block mb-1">Email</small>
                        <p class="fw-500">{{ $user->email }}</p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block mb-1">Vai trò</small>
                        <p class="fw-500">{{ $user->role === 'teacher' ? 'Giáo Viên' : 'Học Sinh' }}</p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block mb-1">Ngày tham gia</small>
                        <p class="fw-500">{{ $user->created_at->format('d/m/Y') }}</p>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top d-flex gap-2">
                    <a href="{{ route('profile.edit') }}" class="btn btn-primary">
                        <i class="fa-solid fa-pen me-2"></i> Chỉnh sửa hồ sơ
                    </a>
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                        <i class="fa-solid fa-arrow-left me-2"></i> Quay lại
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
