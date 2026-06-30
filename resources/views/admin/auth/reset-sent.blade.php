<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Link Sent - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
        .login-container { max-width: 400px; margin: 100px auto; }
        .login-card { border-radius: 10px; box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15); }
        .login-header { background-color: #198754; color: white; border-top-left-radius: 10px; border-top-right-radius: 10px; }
        .success-icon { font-size: 4rem; color: #198754; }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="card login-card text-center">
                <div class="card-header login-header">
                    <h3><i class="bi bi-check-circle me-2"></i>Link Sent</h3>
                </div>
                <div class="card-body">
                    <div class="mb-4 mt-3">
                        <i class="bi bi-check-circle-fill success-icon"></i>
                    </div>
                    <h4>Success!</h4>
                    <p class="text-muted">A password recovery link has been sent to your <strong>{{ $method }}</strong> ({{ $target }}).</p>
                    <div class="alert alert-info py-2">
                        <small><i class="bi bi-info-circle me-1"></i> (This is a dummy message. No actual communication was sent.)</small>
                    </div>
                    <hr>
                    <div class="d-grid">
                        <a href="{{ route('admin.login') }}" class="btn btn-primary">
                            <i class="bi bi-house-door me-2"></i>Back to Login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
