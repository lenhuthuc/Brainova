@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 text-center">
                    <h4 class="fw-bold text-primary mb-0">
                        {{ __('Dashboard') }}
                    </h4>
                </div>

                <div class="card-body p-4 p-md-5 text-center">
                    
                    @if (session('status'))
                        <div class="alert alert-success border-0 shadow-sm rounded-3 d-flex align-items-center justify-content-center" role="alert">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-check-circle-fill me-2" viewBox="0 0 16 16">
                                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                            </svg>
                            <div>{{ session('status') }}</div>
                        </div>
                    @endif

                    <div class="mb-4 mt-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="72" height="72" fill="currentColor" class="text-success opacity-75" viewBox="0 0 16 16">
                            <path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Zm1.679-4.493-1.335 2.226a.75.75 0 0 1-1.174.144l-.774-.773a.5.5 0 0 1 .708-.708l.547.548 1.17-1.951a.5.5 0 1 1 .858.514ZM11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM8 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z"/>
                            <path d="M8.256 14a4.474 4.474 0 0 1-.229-1.004H3c.001-.246.154-.986.832-1.664C4.484 10.68 5.711 10 8 10c.26 0 .507.009.74.025.226-.341.496-.65.804-.918C9.077 9.038 8.564 9 8 9c-5 0-6 3-6 4s1 1 1 1h5.256Z"/>
                        </svg>
                    </div>

                    <h5 class="fw-bold text-dark mb-2">{{ __('Welcome Back!') }}</h5>
                    <p class="text-muted mb-4">{{ __('You are successfully logged in to your account.') }}</p>

                    <a href="{{ url('/') }}" class="btn btn-primary rounded-pill px-4 py-2 fw-medium shadow-sm">
                        {{ __('Go to Homepage') }}
                    </a>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection