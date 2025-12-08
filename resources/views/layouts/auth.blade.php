<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Auth') - Isaiah Nail Bar</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Bootstrap + FontAwesome --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    {{-- Custom --}}
    <link rel="icon" href="{{ asset('storage/favicon.png') }}" type="image/png">
    @stack('styles')
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="min-height: 100vh;">

    <main class="container">
        @yield('content')
    </main>

    {{-- Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
