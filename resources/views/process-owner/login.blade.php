<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Process Owner Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Process Owner Login</h4>
                    </div>
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif

                        @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('process-owner.login') }}">
                            @csrf
                            <div class="mb-3">
                                <label for="email_or_user_code" class="form-label">Email or User Code *</label>
                                <input type="text" class="form-control" id="email_or_user_code" name="email_or_user_code" value="{{ old('email_or_user_code') }}" required>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password *</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>

                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary">Login</button>
                                <a href="{{ route('process-owner.register') }}" class="btn btn-link">Don't have an account? Register</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var form = document.querySelector('form');
    if(form) {
        var latInput = document.createElement('input');
        latInput.type = 'hidden';
        latInput.name = 'latitude';
        latInput.id = 'latitude';
        
        var lonInput = document.createElement('input');
        lonInput.type = 'hidden';
        lonInput.name = 'longitude';
        lonInput.id = 'longitude';
        
        form.appendChild(latInput);
        form.appendChild(lonInput);
        
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    latInput.value = position.coords.latitude;
                    lonInput.value = position.coords.longitude;
                },
                function(error) {
                    console.warn('Geolocation failed or was denied: ', error);
                },
                { enableHighAccuracy: true, timeout: 5000, maximumAge: 0 }
            );
        }
    }
});
</script>