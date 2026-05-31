@extends('layouts.app')

@section('title', 'Đăng ký')

@section('content')
<div class="row justify-content-center align-items-center" style="min-height: calc(100vh - 200px);">
    <div class="col-md-6">
        <div class="card card-custom p-4 shadow-sm">
            <div class="text-center mb-4">
                <h3 class="fw-bold text-primary">Tạo tài khoản mới</h3>
                <p class="text-muted">Bắt đầu học tập và kiểm tra với sự trợ giúp của AI</p>
            </div>
            
            <form action="{{ route('register') }}" method="POST">
                @csrf
                
                <div class="mb-3">
                    <label for="name" class="form-label font-medium">Họ và tên</label>
                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required autofocus>
                    @error('name')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label font-medium">Địa chỉ Email</label>
                    <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                    @error('email')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="role" class="form-label font-medium">Bạn là</label>
                    <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required>
                        <option value="" disabled {{ old('role') ? '' : 'selected' }}>-- Chọn vai trò --</option>
                        <option value="student" {{ old('role') === 'student' ? 'selected' : '' }}>Học Sinh</option>
                        <option value="teacher" {{ old('role') === 'teacher' ? 'selected' : '' }}>Giáo Viên</option>
                    </select>
                    @error('role')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label font-medium">Mật khẩu</label>
                        <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required>
                        @error('password')
                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="password-confirm" class="form-label font-medium">Xác nhận mật khẩu</label>
                        <input type="password" name="password_confirmation" id="password-confirm" class="form-control" required>
                    </div>
                </div>

                <div class="d-grid mb-3 mt-2">
                    <button type="submit" class="btn btn-gradient py-2 fw-semibold">Đăng ký tài khoản</button>
                </div>
                
                <div class="text-center text-muted">
                    Đã có tài khoản? <a href="{{ route('login') }}" class="text-primary text-decoration-none fw-medium">Đăng nhập</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
