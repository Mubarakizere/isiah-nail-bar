<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Secure Payment')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            background-color: #f8f9fa;
            margin: 0;
            overflow: hidden;
            height: 100vh;
            width: 100vw;
        }
        .iframe-container {
            height: 100vh;
            width: 100vw;
            display: flex;
            flex-direction: column;
        }
        iframe {
            width: 100%;
            flex: 1;
            border: none;
            display: block;
        }
    </style>

    @stack('styles')
</head>
<body>

@yield('content')

@stack('scripts')
</body>
</html>
