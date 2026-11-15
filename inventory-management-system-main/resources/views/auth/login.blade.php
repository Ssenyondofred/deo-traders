<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — AD Accounting System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body, html {
            height: 100%;
            margin: 0;
            font-family: system-ui, -apple-system, sans-serif;
        }
        /* Sisi Kiri: Background Gambar dengan Overlay Gelap */
        .login-sidebar {
            background-image: linear-gradient(rgba(15, 34, 43, 0.85), rgba(15, 34, 43, 0.9)), url('https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?q=80&w=1200');
            background-size: cover;
            background-position: center;
            color: #ffffff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 4rem;
        }
        /* Sisi Kanan: Form Container */
        .login-content {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            background-color: #ffffff;
        }
        .login-form-wrapper {
            width: 100%;
            max-width: 400px;
        }
        /* Custom Button agar sewarna dengan tema gelap kebiruan */
        .btn-custom {
            background-color: #1a2e40;
            border-color: #1a2e40;
            color: #ffffff;
            transition: all 0.3s ease;
        }
        .btn-custom:hover {
            background-color: #111f2c;
            border-color: #111f2c;
            color: #ffffff;
        }
        /* Logo lingkaran */
        .logo-container {
            width: 80px;
            height: 80px;
            background-color: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem auto;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .logo-container img {
            max-width: 80%;
            max-height: 80%;
            object-fit: containment;
        }
    </style>
</head>
<body>

<div class="container-fluid h-100 p-0">
    <div class="row g-0 h-100">
        
        <div class="col-lg-6 d-none d-lg-flex login-sidebar">
            <div style="max-width: 500px;">
                <p class="text-uppercase tracking-wider small opacity-75 mb-1" style="letter-spacing: 1px;">Accounting System</p>
                <h1 class="fw-bold mb-3" style="font-size: 2.5rem;">Accountingg System</h1>
                <p class="lead opacity-75 mb-5" style="font-size: 1rem; line-height: 1.6;">
                    A comprehensive accounting and dry corn inventory management platform, designed for workflow efficiency and audit accuracy.
                </p>
                <p class="small opacity-50">&copy; 2026. All rights reserved.</p>
            </div>
        </div>

        <div class="col-lg-6 col-12 login-content">
            <div class="login-form-wrapper">
                
                <div class="logo-container">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo">
                </div>

                <div class="text-center mb-4">
                    <h4 class="fw-bold text-dark mb-1">Accounting System</h4>
                    <p class="text-muted small">Login Page</p>
                </div>

                @if($errors->any())
                    <div class="alert alert-danger py-2 small">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-medium">Email / Username</label>
                        <input type="text" name="login" class="form-control form-control-lg" style="font-size: 0.95rem;" value="{{ old('login') }}" required autofocus>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-medium">Password</label>
                        <input type="password" name="password" class="form-control form-control-lg" style="font-size: 0.95rem;" required>
                    </div>
                    
                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label text-muted small" for="remember">Remember Me</label>
                    </div>
                    
                    <button type="submit" class="btn btn-custom btn-lg w-100 mb-4" style="font-size: 1rem;">→ Login</button>
                </form>

                <!-- <div class="p-3 bg-light rounded text-center small text-muted">
                    Demo: <span class="text-danger">admin</span> / <span class="text-danger">staff</span> — password: <span class="text-danger">password</span>
                </div> -->

            </div>
        </div>

    </div>
</div>

</body>
</html>