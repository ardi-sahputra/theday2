<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" type="image/svg+xml" href="{{ asset('image/favicon.svg') }}">
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('image/favicon-96x96.png') }}">
    <title inertia>{{ $seo['title'] ?? config('app.name', 'Laravel') }}</title>

    @isset($seo)
        <meta name="description" content="{{ $seo['description'] ?? '' }}">
        @isset($seo['canonical'])
            <link rel="canonical" href="{{ $seo['canonical'] }}">
        @endisset

        {{-- Open Graph --}}
        <meta property="og:type" content="{{ $seo['og_type'] ?? 'website' }}">
        <meta property="og:site_name" content="TheDay">
        <meta property="og:title" content="{{ $seo['title'] ?? '' }}">
        <meta property="og:description" content="{{ $seo['description'] ?? '' }}">
        <meta property="og:url" content="{{ $seo['canonical'] ?? url()->current() }}">
        <meta property="og:locale" content="{{ app()->getLocale() === 'id' ? 'id_ID' : 'en_US' }}">
        @isset($seo['og_image'])
            <meta property="og:image" content="{{ $seo['og_image'] }}">
            <meta property="og:image:width" content="1200">
            <meta property="og:image:height" content="630">
        @endisset

        {{-- Twitter Card --}}
        <meta name="twitter:card" content="{{ $seo['twitter_card'] ?? 'summary_large_image' }}">
        <meta name="twitter:title" content="{{ $seo['title'] ?? '' }}">
        <meta name="twitter:description" content="{{ $seo['description'] ?? '' }}">
        @isset($seo['og_image'])
            <meta name="twitter:image" content="{{ $seo['og_image'] }}">
        @endisset

        {{-- Article-specific OG --}}
        @if(($seo['og_type'] ?? '') === 'article' && !empty($seo['article']))
            @isset($seo['article']['published_time'])
                <meta property="article:published_time" content="{{ $seo['article']['published_time'] }}">
            @endisset
            @isset($seo['article']['modified_time'])
                <meta property="article:modified_time" content="{{ $seo['article']['modified_time'] }}">
            @endisset
            @isset($seo['article']['author'])
                <meta property="article:author" content="{{ $seo['article']['author'] }}">
            @endisset
            @isset($seo['article']['section'])
                <meta property="article:section" content="{{ $seo['article']['section'] }}">
            @endisset
        @endif

        {{-- Structured data --}}
        @foreach($seo['schemas'] ?? [] as $schema)
            <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
        @endforeach
    @endisset

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600|pinyon-script:400|playfair-display:400,600,700|cormorant-garamond:400,600,700&display=swap" rel="stylesheet" />
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
    {{-- Premium template fonts: Astronomy + Belle Époque + Japanese Ryokan --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;600;700&family=Cormorant+Garamond:wght@400;500;600;700&family=Cormorant+SC:wght@400;600;700&family=EB+Garamond:ital,wght@0,400;0,500;0,600;1,400&family=Italianno&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;700&family=Shippori+Mincho+B1:wght@400;700&family=Sawarabi+Mincho&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;700&family=Shippori+Mincho+B1:wght@400;700&family=Sawarabi+Mincho&display=swap">

    <script>
        (function () {
            if (!location.pathname.startsWith('/admin')) return;
            const theme = localStorage.getItem('adminTheme') ?? 'system';
            const isDark = theme === 'dark' || (theme === 'system' && matchMedia('(prefers-color-scheme: dark)').matches);
            if (isDark) document.documentElement.classList.add('dark');
        })();
    </script>

    <!-- Scripts -->
    @routes
    @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
    @inertiaHead
</head>

<body class="font-sans antialiased">
    @inertia
</body>

</html>
