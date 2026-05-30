@extends('layouts.app')

@section('title', 'Đăng nhập')

@section('content')
<div class="row justify-content-center align-items-center" style="min-height: calc(100vh - 200px);">
    <div class="col-md-5">
        <div class="card card-custom p-4 shadow-sm">
            <div class="text-center mb-4">
                <h3 class="fw-bold text-primary">Chào mừng quay lại</h3>
                <p class="text-muted">Đăng nhập vào tài khoản QuizMaster của bạn</p>
            </div>
            
            <form action="{{ route('login') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label font-medium">Địa chỉ Email</label>
                    <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required autofocus>
                    @error('email')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label font-medium">Mật khẩu</label>
                    <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required>
                    @error('password')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="mb-3 d-flex justify-content-between align-items-center">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label text-muted" for="remember">Ghi nhớ đăng nhập</label>
                    </div>
                    <a href="{{ route('password.request') }}" class="text-primary text-decoration-none small fw-medium">Quên mật khẩu?</a>
                </div>

                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-gradient py-2 fw-semibold">Đăng nhập</button>
                </div>
                
                <div class="text-center text-muted">
                    Chưa có tài khoản? <a href="{{ route('register') }}" class="text-primary text-decoration-none fw-medium">Đăng ký ngay</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
