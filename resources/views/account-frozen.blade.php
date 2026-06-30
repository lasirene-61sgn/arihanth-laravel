<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Account Frozen - ERP System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 20px;
        }
        .frozen-card {
            max-width: 500px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            animation: fadeInUp 0.6s ease-out;
        }
        .frozen-header {
            background: linear-gradient(135deg, #e63946 0%, #d90429 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .frozen-header i {
            font-size: 3rem;
            margin-bottom: 15px;
            display: block;
        }
        .frozen-body {
            padding: 40px;
            text-align: center;
        }
        .frozen-body h2 {
            color: #2d3748;
            margin-bottom: 20px;
        }
        .frozen-body p {
            color: #4a5568;
            line-height: 1.6;
            margin-bottom: 25px;
        }
        .contact-info {
            background: #f7fafc;
            border-radius: 10px;
            padding: 20px;
            margin: 25px 0;
            border-left: 4px solid #e63946;
        }
        .btn-contact {
            background: #4361ee;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-contact:hover {
            background: #3a56d4;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
        }
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="frozen-card">
        <div class="frozen-header">
            <i class="bi bi-lock"></i>
            <h1>Account Frozen</h1>
        </div>
        <div class="frozen-body">
            <h2>Your Account Has Been Frozen</h2>
            <p><strong>{{ session('frozen_message') ?: 'Your account has been temporarily frozen by the administrator.' }}</strong></p>
            
            <div class="contact-info">
                <h5><i class="bi bi-envelope me-2"></i>Contact Information</h5>
                <p>Please contact the Super Admin to resolve this issue:</p>
                <p><strong>Support Email:</strong> support@ariantth.com</p>
                <p><strong>Phone:</strong> +91 98765 43210</p>
            </div>
            
            <p>If you believe this is an error, please reach out to the Super Admin for assistance.</p>
            
            <a href="{{ url('/') }}" class="btn btn-contact">
                <i class="bi bi-house-door me-2"></i>Go to Homepage
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>