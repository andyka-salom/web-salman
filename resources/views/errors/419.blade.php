@php
    // Logout user
    if (Auth::check()) {
        Auth::logout();
    }

    // Invalidate session
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    // Redirect to login
    header('Location: ' . route('login'));
    exit;
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Expired - Redirecting...</title>
    <script>
        // Fallback redirect jika header redirect gagal
        window.location.href = "{{ route('login') }}";
    </script>
</head>
<body>
    <p>Session expired. Redirecting to login...</p>
</body>
</html>
