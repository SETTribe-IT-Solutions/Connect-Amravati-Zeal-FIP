<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connect Amravati - Govt Login Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, #0a2540 0%, #1e3a5f 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background-color: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.25);
            border-top: 6px solid #c5a880;
            overflow: hidden;
            width: 100%;
            max-width: 440px;
            padding: 40px;
        }
        .btn-gov {
            background-color: #0a2540;
            color: #ffffff;
            border: none;
            font-weight: 600;
        }
        .btn-gov:hover {
            background-color: #1e3a5f;
            color: #ffffff;
        }
    </style>
</head>
<body>

    <div class="d-flex flex-column align-items-center">
        <div class="text-white text-center mb-4">
            <i class="fa-solid fa-landmark fs-1 mb-2 text-warning"></i>
            <h2 class="fw-bold mb-0">CONNECT AMRAVATI</h2>
            <p class="small text-uppercase tracking-wider text-muted mb-0">District Administration Task Portal</p>
        </div>

        <div class="login-card">
            <h4 class="fw-bold text-dark mb-2"><i class="fa-solid fa-shield-halved"></i> NIC Secure Login</h4>
            <p class="small text-muted mb-4">Provide your official credentials to access the dashboard.</p>

            @if($errors->any())
                <div class="alert alert-danger py-2">
                    <span class="small">{{ $errors->first() }}</span>
                </div>
            @endif

            <form action="/login" method="POST" autocomplete="off">
                @csrf
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Government Email Account</label>
                    <input type="email" name="email" class="form-control" placeholder="username@gov.in" required value="{{ old('email') }}">
                </div>
                
                <div class="mb-4">
                    <label class="form-label small fw-bold text-muted">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••••••" required>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <label class="form-check-label small text-muted">
                        <input type="checkbox" name="remember" class="form-check-input me-1"> Remember Session
                    </label>
                    <a href="#" class="small text-decoration-none text-primary fw-semibold">Forgot credentials?</a>
                </div>

                <button type="submit" class="btn btn-gov w-100 py-2.5">
                    <i class="fa-solid fa-fingerprint"></i> Authenticate Credentials
                </button>
            </form>
        </div>
    </div>

</body>
</html>
