@extends('layouts.app')

@section('title', 'Chỉnh sửa hồ sơ cá nhân')

@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card card-custom">
            <div class="card-header border-bottom py-3 px-4">
                <h5 class="mb-0">
                    <i class="fa-solid fa-pen me-2"></i> Chỉnh sửa hồ sơ cá nhân
                </h5>
            </div>

            <div class="card-body p-4">
                <form action="{{ route('profile.update') }}" method="POST" novalidate>
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label for="name" class="form-label fw-medium">
                            <i class="fa-solid fa-user me-2 text-primary"></i> Tên đầy đủ
                        </label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name', $user->name) }}"
                               placeholder="Nhập tên của bạn" required>
                        @error('name')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="email" class="form-label fw-medium">
                            <i class="fa-solid fa-envelope me-2 text-primary"></i> Email
                        </label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                               id="email" name="email" value="{{ old('email', $user->email) }}"
                               placeholder="Nhập địa chỉ email của bạn" required>
                        @error('email')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="role" class="form-label fw-medium">
                            <i class="fa-solid fa-shield me-2 text-primary"></i> Vai trò
                        </label>
                        <input type="text" class="form-control" id="role"
                               value="{{ $user->role === 'teacher' ? 'Giáo Viên' : 'Học Sinh' }}" disabled>
                        <small class="text-muted d-block mt-2">Vai trò của bạn không thể thay đổi</small>
                    </div>

                    <div class="mb-4">
                        <label for="created_at" class="form-label fw-medium">
                            <i class="fa-solid fa-calendar me-2 text-primary"></i> Ngày tham gia
                        </label>
                        <input type="text" class="form-control" id="created_at"
                               value="{{ $user->created_at->format('d/m/Y H:i') }}" disabled>
                    </div>

                    <div class="row gap-2 pt-3 border-top mt-4">
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa-solid fa-floppy-disk me-2"></i> Lưu thay đổi
                            </button>
                        </div>
                        <div class="col-auto">
                            <a href="{{ route('profile.show') }}" class="btn btn-outline-secondary">
                                <i class="fa-solid fa-xmark me-2"></i> Hủy
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
