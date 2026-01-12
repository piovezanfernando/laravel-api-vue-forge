<!DOCTYPE html>
<html>
<head>
    <title>{{ config('app.name', 'Laravel') }}</title>
    <meta charset="utf-8">
    <meta name="description" content="A Quasar Project">
    <meta name="format-detection" content="telephone=no">
    <meta name="msapplication-tap-highlight" content="no">
    <meta name="viewport" content="user-scalable=no,initial-scale=1,maximum-scale=1,minimum-scale=1,width=device-width">

    @php
        $jsFile = null;
        $cssFiles = [];

        // O Quasar SPA gera os arquivos em front/dist/spa/assets
        // Precisamos encontrar os arquivos reais com hash no nome
        $frontendPath = base_path('front');
        $spaDistPath = $frontendPath . DIRECTORY_SEPARATOR . 'dist' . DIRECTORY_SEPARATOR . 'spa';
        $assetsPath = $spaDistPath . DIRECTORY_SEPARATOR . 'assets';
        
        if (file_exists($assetsPath)) {
            $jsFiles = glob($assetsPath . DIRECTORY_SEPARATOR . 'index-*.js');
            if (!empty($jsFiles)) {
                $jsFile = 'assets/' . basename($jsFiles[0]);
            }

            $cssFilesFromGlob = glob($assetsPath . DIRECTORY_SEPARATOR . 'index-*.css');
            if (!empty($cssFilesFromGlob)) {
                $cssFiles = array_map(fn($file) => 'assets/' . basename($file), $cssFilesFromGlob);
            }
        }

        // Caso exista um manifesto do Vite
        $manifestPath = $spaDistPath . DIRECTORY_SEPARATOR . '.vite' . DIRECTORY_SEPARATOR . 'manifest.json';
        if (file_exists($manifestPath)) {
            $manifest = json_decode(file_get_contents($manifestPath), true);
            $jsFile = isset($manifest['index.html']) ? 'assets/' . $manifest['index.html']['file'] : $jsFile;
            if (isset($manifest['index.html']['css'])) {
                $cssFiles = array_map(fn($file) => 'assets/' . $file, $manifest['index.html']['css']);
            }
        }
    @endphp

    @foreach($cssFiles as $cssFile)
        <link rel="stylesheet" href="{{ asset($cssFile) }}">
    @endforeach

    <link rel="icon" type="image/png" sizes="128x128" href="{{ asset('icons/favicon-128x128.png') }}">
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('icons/favicon-96x96.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('icons/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('icons/favicon-16x16.png') }}">
    <link rel="icon" type="image/ico" href="{{ asset('favicon.ico') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div id="q-app"></div>

    @if($jsFile)
        <script type="module" crossorigin src="{{ asset($jsFile) }}"></script>
    @endif
</body>
</html>
