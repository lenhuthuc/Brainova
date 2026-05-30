<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Brainova') - Hệ thống trắc nghiệm thông minh</title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6 CDN -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #4F46E5;
            --secondary-color: #7C3AED;
            --success-color: #10B981;
            --warning-color: #F59E0B;
            --danger-color: #EF4444;
            --background-color: #F9FAFB;
            --text-color: #1F2937;
            --sidebar-width: 260px;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        /* Navbar */
        .navbar-custom {
            background-color: white;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            z-index: 1030;
        }
        
        .navbar-brand-custom {
            font-weight: 700;
            color: var(--primary-color) !important;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        /* Layout wrapper */
        .app-container {
            display: flex;
            flex: 1;
            padding-top: 56px;
        }
        
        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background-color: white;
            border-right: 1px solid #E5E7EB;
            position: fixed;
            top: 56px;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 1.5rem 1rem;
            overflow-y: auto;
            transition: all 0.3s ease;
        }
        
        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0.75rem 1rem;
            color: #4B5563;
            text-decoration: none;
            border-radius: 0.375rem;
            font-weight: 500;
            margin-bottom: 0.25rem;
            transition: all 0.2s ease;
        }
        
        .sidebar-link:hover {
            background-color: #F3F4F6;
            color: var(--primary-color);
        }
        
        .sidebar-link.active {
            background-color: #EEF2F6;
            color: var(--primary-color);
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 2rem;
            transition: all 0.3s ease;
        }
        
        /* Cards styling */
        .card-custom {
            background: white;
            border: 1px solid #E5E7EB;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        .btn-gradient {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border: none;
        }
        .btn-gradient:hover {
            opacity: 0.9;
            color: white;
        }
        
        /* Footer */
        footer {
            background-color: white;
            border-top: 1px solid #E5E7EB;
            padding: 1rem;
            text-align: center;
            font-size: 0.875rem;
            color: #6B7280;
            margin-top: auto;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg bg-white border-bottom shadow-sm fixed-top py-2">
    <div class="container-fluid px-md-4">
        
        <a class="navbar-brand d-flex align-items-center gap-2 fw-bold text-primary fs-4" href="{{ route('dashboard') }}">
            <div class="bg-primary-subtle text-primary p-2 rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
            <span>Brainova</span>
        </a>

        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" onclick="document.querySelector('.sidebar').classList.toggle('show')">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center gap-3">
                @auth
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2 text-dark border rounded-pill py-1 px-2 shadow-sm bg-white" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 14px;">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                            
                            <div class="d-none d-sm-block text-start me-1">
                                <div class="fw-medium lh-1" style="font-size: 14px;">{{ Auth::user()->name }}</div>
                                <small class="text-muted" style="font-size: 11px;">
                                    {{ Auth::user()->role === 'teacher' ? 'Giáo Viên' : 'Học Sinh' }}
                                </small>
                            </div>
                        </a>
                        
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow rounded-4 mt-3 py-2">
                            <li class="px-3 py-2 d-sm-none border-bottom mb-2">
                                <span class="fw-bold d-block text-dark">{{ Auth::user()->name }}</span>
                                <span class="badge bg-primary-subtle text-primary">{{ Auth::user()->role === 'teacher' ? 'Giáo Viên' : 'Học Sinh' }}</span>
                            </li>
                            
                            <li>
                                <a class="dropdown-item py-2 d-flex align-items-center gap-2 text-secondary" href="{{ route('profile.show') }}">
                                    <i class="fa-regular fa-user"></i> Hồ sơ cá nhân
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST" id="logout-form">
                                    @csrf
                                    <button type="submit" class="dropdown-item py-2 text-danger d-flex align-items-center gap-2 fw-medium">
                                        <i class="fa-solid fa-arrow-right-from-bracket"></i> Đăng xuất
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link fw-medium text-secondary" href="{{ route('login') }}">
                            Đăng nhập
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary rounded-pill px-4 py-2 fw-medium shadow-sm d-flex align-items-center gap-2" href="{{ route('register') }}">
                            <i class="fa-solid fa-user-plus"></i> Đăng ký ngay
                        </a>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>

    <!-- App Container -->
    <div class="app-container">
        @auth
            <!-- Sidebar -->
            <aside class="sidebar">
                <div class="d-flex flex-column h-100">
                    <div class="sidebar-menu">
                        <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="fa-solid fa-house"></i> Dashboard
                        </a>
                        
                        @if(Auth::user()->role === 'teacher')
                            <a href="{{ route('quizzes.index') }}" class="sidebar-link {{ request()->routeIs('quizzes.*') ? 'active' : '' }}">
                                <i class="fa-solid fa-book-open"></i> Quản lý Quiz
                            </a>
                            <a href="{{ route('documents.index') }}" class="sidebar-link {{ request()->routeIs('documents.*') ? 'active' : '' }}">
                                <i class="fa-solid fa-file-lines"></i> Tài liệu
                            </a>
                            <a href="{{ route('ai.generate.form') }}" class="sidebar-link {{ request()->routeIs('ai.*') ? 'active' : '' }}">
                                <i class="fa-solid fa-wand-magic-sparkles"></i> AI Question Generator
                            </a>
                        @else
                            <a href="{{ route('attempts.available') }}" class="sidebar-link {{ request()->routeIs('attempts.available') ? 'active' : '' }}">
                                <i class="fa-solid fa-pen-fancy"></i> Làm bài trắc nghiệm
                            </a>
                            <a href="{{ route('attempts.history') }}" class="sidebar-link {{ request()->routeIs('attempts.history') ? 'active' : '' }}">
                                <i class="fa-solid fa-clock-rotate-left"></i> Lịch sử làm bài
                            </a>
                        @endif
                    </div>
                </div>
            </aside>
        @endauth

        <!-- Main Content -->
        <main class="main-content" style="{{ !Auth::check() ? 'margin-left: 0; padding-top: 2rem;' : '' }}">
            <div class="container-fluid">
                <!-- Flash Messages -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                        <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                        <i class="fa-solid fa-circle-xmark me-2"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p class="mb-0">&copy; {{ date('Y') }} Brainova App.</p>
        </div>
    </footer>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>
