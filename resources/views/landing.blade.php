{{-- resources/views/landing.blade.php --}}
@php
    $locale    = $locale ?? 'id';
    $isEn      = $locale === 'en';
    $urlId     = url('/');
    $urlEn     = url('/en');
    $canonical = $isEn ? $urlEn : $urlId;

    $seoTitle = $isEn
        ? 'Theday & Beyond — Celebrate the Day, Cherish the Story | Indonesian Couple Companion App'
        : 'Theday & Beyond — Merayakan Hari, Merawat Cerita | Aplikasi Pendamping Pasangan Indonesia';
    $seoDesc = $isEn
        ? 'An Indonesian couple companion app — from preparation, through your wedding day, to life beyond. Digital invitations, RSVP, anniversary, and more.'
        : 'Aplikasi pendamping pasangan Indonesia dari persiapan, hari pernikahan, sampai kehidupan setelahnya. Undangan digital, RSVP, anniversary, dan lainnya.';
    $seoKeywords = $isEn
        ? 'digital wedding invitation Indonesia, online wedding invitation, couple companion app, wedding RSVP, anniversary tracker, premium digital invitation'
        : 'undangan digital pernikahan, undangan pernikahan digital, buat undangan nikah online gratis, undangan nikah digital, digital wedding invitation Indonesia, undangan online cantik, undangan pernikahan premium';
    $ogTitle = $isEn
        ? 'Theday & Beyond — Celebrate the Day, Cherish the Story'
        : 'Theday & Beyond — Merayakan Hari, Merawat Cerita';
@endphp
<!DOCTYPE html>
<html lang="{{ $isEn ? 'en' : 'id' }}" id="html-root">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- ── SEO: Core ─────────────────────────────────────────────── --}}
    <title>{{ $seoTitle }}</title>
    <meta name="description" content="{{ $seoDesc }}">
    <meta name="keywords" content="{{ $seoKeywords }}">
    <meta name="author" content="Theday">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <link rel="canonical" href="{{ $canonical }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('image/favicon.svg') }}">
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('image/favicon-96x96.png') }}">

    {{-- ── SEO: Open Graph (WhatsApp / Facebook / LinkedIn) ─────── --}}
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:site_name" content="Theday">
    <meta property="og:title" content="{{ $ogTitle }}">
    <meta property="og:description" content="{{ $seoDesc }}">
    <meta property="og:image" content="{{ asset('image/logo.svg') }}">
    <meta property="og:image:width" content="300">
    <meta property="og:image:height" content="150">
    <meta property="og:image:alt" content="Theday — Platform Undangan Digital Pernikahan Premium Online">
    <meta property="og:locale" content="{{ $isEn ? 'en_US' : 'id_ID' }}">
    <meta property="og:locale:alternate" content="{{ $isEn ? 'id_ID' : 'en_US' }}">

    {{-- ── SEO: Twitter Card ─────────────────────────────────────── --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $ogTitle }}">
    <meta name="twitter:description" content="{{ $seoDesc }}">
    <meta name="twitter:image" content="{{ asset('image/logo.svg') }}">

    {{-- ── SEO: Hreflang (bilingual ID / EN — distinct URLs) ─────── --}}
    <link rel="alternate" hreflang="id" href="{{ $urlId }}">
    <link rel="alternate" hreflang="en" href="{{ $urlEn }}">
    <link rel="alternate" hreflang="x-default" href="{{ $urlId }}">

    {{-- ── SEO: Sitemap Discovery ────────────────────────────────── --}}
    <link rel="sitemap" type="application/xml" title="Sitemap" href="{{ url('/sitemap.xml') }}">

    {{-- ── Prefetch app entry pages so the first tap into the app feels instant.
         Guests head to login/register; Speculation Rules degrade gracefully where
         unsupported. Authed users go to /dashboard (heavy) so we don't prefetch it. --}}
    @guest
    <script type="speculationrules">
    {
        "prefetch": [
            { "source": "list", "urls": ["/login", "/register"] }
        ]
    }
    </script>
    @endguest

    {{-- ── Fonts (self-hosted, latin subset, variable woff2) ──────────
         Self-hosted to drop the render-blocking round-trip to Google's two
         extra origins (googleapis + gstatic) — the main LCP win on slow 4G.
         @font-face lives inline so it's discovered in the initial HTML and
         doesn't wait on app.css. Critical above-the-fold faces are preloaded. --}}
    <link rel="preload" as="font" type="font/woff2" href="{{ asset('fonts/montserrat-latin.woff2') }}" crossorigin>
    <link rel="preload" as="font" type="font/woff2" href="{{ asset('fonts/cormorant-latin.woff2') }}" crossorigin>
    <link rel="preload" as="font" type="font/woff2" href="{{ asset('fonts/cormorant-italic-latin.woff2') }}" crossorigin>
    <link rel="preload" as="image" href="{{ asset('images/landing/hero-journey.webp') }}" fetchpriority="high">

    {{-- Landing is pure Blade + vanilla JS (no Inertia/Vue mount), so it only
         needs the compiled CSS — not the ~900KB app bundle. --}}
    @vite(['resources/css/app.css'])

    <style>
        /* Self-hosted variable fonts (latin). One woff2 per family covers the
           full weight range, so all weights below resolve to a single file. */
        @font-face {
            font-family: 'Montserrat';
            font-style: normal;
            font-weight: 300 700;
            font-display: swap;
            src: url('{{ asset('fonts/montserrat-latin.woff2') }}') format('woff2');
        }
        @font-face {
            font-family: 'Cormorant';
            font-style: normal;
            font-weight: 500 700;
            font-display: swap;
            src: url('{{ asset('fonts/cormorant-latin.woff2') }}') format('woff2');
        }
        @font-face {
            font-family: 'Cormorant';
            font-style: italic;
            font-weight: 500 600;
            font-display: swap;
            src: url('{{ asset('fonts/cormorant-italic-latin.woff2') }}') format('woff2');
        }
        @font-face {
            font-family: 'Playfair Display';
            font-style: normal;
            font-weight: 600 700;
            font-display: swap;
            src: url('{{ asset('fonts/playfair-display-latin.woff2') }}') format('woff2');
        }

        :root {
            --color-primary: #92A89C;
            --color-primary-dark: #73877C;
            --color-secondary: #E8EFEC;
            --color-accent: #CCD5AE;
            --color-dark: #1E1E1E;
            --color-bg: #F5F8F6;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background-color: var(--color-bg);
            color: var(--color-dark);
        }

        .font-display {
            font-family: 'Playfair Display', serif;
        }

        /* Hero serif — matches Landing Page.html (Cormorant) */
        .font-serif-hero {
            font-family: 'Cormorant', 'Times New Roman', serif;
            font-weight: 500;
        }

        .italic-serif-hero {
            font-family: 'Cormorant', serif;
            font-style: italic;
            font-weight: 500;
        }

        /* Hero title — Cormorant editorial serif */
        .font-hero-display {
            font-family: 'Cormorant', 'Times New Roman', serif;
            font-weight: 700;
        }

        .hero-amp {
            font-family: 'Cormorant', serif;
            font-style: italic;
            font-weight: 600;
        }

        /* Hero gradient */
        .hero-gradient {
            background: #fcf8f3;
        }


        /* Gold button */
        .btn-primary {
            /* Deeper sage than --color-primary (#92A89C) so white text clears
               WCAG 4.5:1 (5.4:1). Kept literal so the lighter sage accent used
               elsewhere via --color-primary is unaffected. */
            background-color: #5C6F64;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 0.5rem;
            font-weight: 600;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn-primary:hover {
            background-color: #4F5F55;
            transform: translateY(-1px);
            box-shadow: 0 8px 25px rgba(146, 168, 156, 0.35);
        }

        .btn-outline {
            border: 2px solid var(--color-primary);
            color: var(--color-primary);
            padding: 0.75rem 2rem;
            border-radius: 0.5rem;
            font-weight: 600;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            background: transparent;
        }

        .btn-outline:hover {
            background-color: var(--color-primary);
            color: white;
            transform: translateY(-1px);
        }

        .btn-primary:active,
        .btn-outline:active {
            transform: translateY(0) scale(0.97);
        }

        /* Card hover effect */
        .feature-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .feature-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
        }


        /* Floating animation */
        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-12px);
            }
        }

        .float-animation {
            animation: float 4s ease-in-out infinite;
        }

        .float-animation-delay {
            animation: float 4s ease-in-out infinite 1s;
        }

        .float-animation-delay-2 {
            animation: float 4s ease-in-out infinite 2s;
        }

        /* ── Scroll reveal (animation-based so card hover transforms stay intact) ── */
        .reveal,
        .reveal-fade {
            opacity: 0;
        }

        .reveal {
            transform: translateY(30px);
        }

        .reveal.reveal-scale {
            transform: scale(0.96);
        }

        .reveal.is-in {
            animation: revealRise 0.7s cubic-bezier(0.22, 1, 0.36, 1) forwards;
        }

        .reveal.reveal-scale.is-in {
            animation: revealScale 0.7s cubic-bezier(0.22, 1, 0.36, 1) forwards;
        }

        .reveal-fade.is-in {
            animation: revealFade 0.7s ease forwards;
        }

        /* Resting state after entrance — no transform lock, so :hover lift still works */
        .reveal.done,
        .reveal-fade.done {
            opacity: 1;
            transform: none;
        }

        @keyframes revealRise {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @keyframes revealScale {
            from { opacity: 0; transform: scale(0.96); }
            to   { opacity: 1; transform: scale(1); }
        }

        @keyframes revealFade {
            from { opacity: 0; }
            to   { opacity: 1; }
        }

        /* ── Hero entrance (on page load) ── */
        @keyframes heroRise {
            from { opacity: 0; transform: translateY(26px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @keyframes heroScale {
            from { opacity: 0; transform: scale(0.96) translateY(18px); }
            to   { opacity: 1; transform: scale(1) translateY(0); }
        }

        .hero-anim {
            opacity: 0;
            animation: heroRise 0.75s cubic-bezier(0.22, 1, 0.36, 1) forwards;
        }

        /* The hero H1 is the LCP element. A fade-in (opacity:0) delays LCP
           because the text isn't "painted" until the animation runs. Keep the
           slide-up motion but paint it visible immediately. */
        @keyframes heroSlide {
            from { transform: translateY(26px); }
            to   { transform: translateY(0); }
        }
        h1.hero-anim.font-hero-display {
            opacity: 1;
            animation-name: heroSlide;
        }

        .hero-illustration {
            opacity: 0;
            animation: heroScale 0.9s cubic-bezier(0.22, 1, 0.36, 1) 0.3s forwards;
        }

        /* Gentle float for hero floating mockup cards */
        @keyframes mockupFloat {
            0%, 100% { transform: translateY(0); }
            50%      { transform: translateY(-8px); }
        }

        .hero-mockup-float {
            animation: mockupFloat 5s ease-in-out infinite;
        }

        .hero-mockup-float-delay {
            animation: mockupFloat 5s ease-in-out infinite 1.4s;
        }

        /* ── Progress bar fill (scaleX = GPU-friendly), triggers when its card reveals ── */
        .bar-fill {
            transform: scaleX(0);
            transform-origin: left center;
            transition: transform 1.1s cubic-bezier(0.22, 1, 0.36, 1) 0.2s;
        }

        .is-in .bar-fill,
        .done .bar-fill {
            transform: scaleX(1);
        }

        /* ── Nav link animated underline ── */
        .nav-link {
            position: relative;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -3px;
            height: 1.5px;
            width: 100%;
            background: var(--color-primary);
            transform: scaleX(0);
            transform-origin: right;
            transition: transform 0.3s cubic-bezier(0.22, 1, 0.36, 1);
        }

        .nav-link:hover::after {
            transform: scaleX(1);
            transform-origin: left;
        }

        /* Decorative dots */
        .dot-pattern {
            background-image: radial-gradient(circle, #92A89C30 1px, transparent 1px);
            background-size: 24px 24px;
        }

        /* Nav */
        .nav-scroll {
            backdrop-filter: blur(12px);
            background-color: rgba(255, 253, 247, 0.85);
            border-bottom: 1px solid rgba(146, 168, 156, 0.15);
        }

        /* Pricing highlight */
        .pricing-popular {
            background: linear-gradient(135deg, #92A89C, #73877C);
            color: white;
        }

        /* Stats counter */
        .stat-card {
            background: white;
            border: 1px solid rgba(146, 168, 156, 0.2);
            border-radius: 1rem;
            padding: 1.5rem 2rem;
        }

        /* Divider ornament */
        .ornament-divider::before,
        .ornament-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: linear-gradient(90deg, transparent, #92A89C40, transparent);
        }

        /* Language toggle */
        .lang-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.35rem 0.75rem;
            border-radius: 0.5rem;
            border: 1.5px solid rgba(146, 168, 156, 0.4);
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            background: transparent;
            color: var(--color-primary-dark);
            letter-spacing: 0.03em;
        }

        .lang-btn:hover {
            border-color: var(--color-primary);
            background: rgba(146, 168, 156, 0.08);
        }

        /* Feature tabs */
        .feature-tab {
            background: #F5F8F6;
            color: #73877C;
        }

        .feature-tab.is-active {
            background: #92A89C;
            color: white;
        }

        .feature-tab:hover:not(.is-active) {
            background: rgba(146, 168, 156, 0.2);
        }

        /* FAQ accordion (smooth open/close) */
        .faq-a {
            max-height: 0;
            opacity: 0;
            overflow: hidden;
            padding-top: 0;
            padding-bottom: 0;
            transition: max-height 0.4s cubic-bezier(0.22, 1, 0.36, 1), opacity 0.3s ease, padding 0.4s ease;
        }

        .faq-item.is-open .faq-a {
            max-height: 500px;
            opacity: 1;
            padding-bottom: 1rem;
        }

        .faq-icon {
            transition: transform 0.3s cubic-bezier(0.22, 1, 0.36, 1);
        }

        .faq-item.is-open .faq-icon {
            transform: rotate(45deg);
        }

        /* Reduced motion */
        @media (prefers-reduced-motion: reduce) {

            .float-animation,
            .float-animation-delay,
            .float-animation-delay-2,
            .hero-mockup-float,
            .hero-mockup-float-delay {
                animation: none !important;
            }

            .hero-anim,
            .hero-illustration {
                opacity: 1 !important;
                animation: none !important;
            }

            .reveal,
            .reveal-fade {
                opacity: 1 !important;
                transform: none !important;
            }

            .reveal.is-in,
            .reveal.reveal-scale.is-in,
            .reveal-fade.is-in {
                animation: none !important;
            }

            .bar-fill {
                transform: scaleX(1) !important;
                transition: none !important;
            }

            .faq-a {
                transition: none !important;
            }
        }
    </style>

    {{-- ── JSON-LD Structured Data ───────────────────────────────── --}}
    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@@graph": [
        {
          "@type": "WebSite",
          "@id": "{{ url('/') }}/#website",
          "url": "{{ url('/') }}",
          "name": "Theday",
          "description": "Aplikasi pendamping pasangan Indonesia dari persiapan, hari pernikahan, sampai kehidupan setelahnya. Undangan digital, RSVP, anniversary, dan lainnya.",
          "inLanguage": ["id-ID", "en-US"],
          "potentialAction": {
            "@type": "SearchAction",
            "target": {
              "@type": "EntryPoint",
              "urlTemplate": "{{ url('/templates') }}?q={search_term_string}"
            },
            "query-input": "required name=search_term_string"
          }
        },
        {
          "@type": "Organization",
          "@id": "{{ url('/') }}/#organization",
          "name": "Theday",
          "url": "{{ url('/') }}",
          "logo": {
            "@type": "ImageObject",
            "url": "{{ asset('image/logo.svg') }}",
            "width": 300,
            "height": 150
          },
          "sameAs": [
            "https://www.instagram.com/thedayid",
            "https://www.tiktok.com/@thedayid"
          ]
        },
        {
          "@type": "SoftwareApplication",
          "@id": "{{ url('/') }}/#app",
          "name": "Theday",
          "applicationCategory": "LifestyleApplication",
          "operatingSystem": "Web",
          "url": "{{ url('/') }}",
          "description": "Aplikasi pendamping pasangan Indonesia dari persiapan, hari pernikahan, sampai kehidupan setelahnya. Undangan digital, RSVP, anniversary, dan lainnya.",
          "offers": [
            {
              "@type": "Offer",
              "name": "Gratis",
              "price": "0",
              "priceCurrency": "IDR",
              "description": "1 undangan aktif, template dasar, RSVP online, peta lokasi, 5 foto galeri"
            },
            {
              "@type": "Offer",
              "name": "Premium",
              "price": "{{ (int) (isset($plans['premium']) ? $plans['premium']->effectivePrice() : 49000) }}",
              "priceCurrency": "IDR",
              "description": "{{ implode(', ', $plans['premium']->features ?? []) }}"
            },
            {
              "@type": "Offer",
              "name": "Bisnis",
              "price": "299000",
              "priceCurrency": "IDR",
              "description": "Undangan tidak terbatas, white label, custom domain, laporan Excel"
            }
          ]
        },
        {
          "@type": "FAQPage",
          "mainEntity": [
            {
              "@type": "Question",
              "name": "Saya sudah menikah, masih bisa pakai Theday?",
              "acceptedAnswer": {
                "@type": "Answer",
                "text": "Bisa. Fitur Fase 3 (Setelah Nikah) seperti anniversary reminder, memory album, dan joint budget dirancang untuk pasangan yang sudah menikah. Fitur ini sedang dikembangkan dan akan tersedia bertahap. Daftar sekarang gratis untuk dapat akses awal saat rilis."
              }
            },
            {
              "@type": "Question",
              "name": "Apakah saya wajib pakai fitur undangan?",
              "acceptedAnswer": {
                "@type": "Answer",
                "text": "Tidak. Undangan digital adalah salah satu fitur unggulan, tapi kamu bisa pakai Theday hanya untuk checklist persiapan, daftar tamu, RSVP, atau fitur setelah nikah. Bebas pilih sesuai kebutuhan."
              }
            },
            {
              "@type": "Question",
              "name": "Apa bedanya Theday & Beyond dengan platform undangan lain?",
              "acceptedAnswer": {
                "@type": "Answer",
                "text": "Theday fokus ke perjalanan pernikahan jangka panjang, bukan cuma event sehari. Kami menggabungkan kualitas craft template undangan premium dengan fitur pendamping seumur hidup pasangan: dari persiapan, hari H, sampai kehidupan setelahnya. Dirancang khusus untuk pasangan Indonesia."
              }
            },
            {
              "@type": "Question",
              "name": "Fitur Setelah Nikah kapan tersedia?",
              "acceptedAnswer": {
                "@type": "Answer",
                "text": "Fitur Fase 3 (anniversary reminder, memory album, newlywed admin, joint budget) sedang dikembangkan dan akan dirilis bertahap. Kamu yang sudah daftar akan dapat notifikasi saat setiap fitur rilis."
              }
            },
            {
              "@type": "Question",
              "name": "Apa bedanya paket Free dan Premium?",
              "acceptedAnswer": {
                "@type": "Answer",
                "text": "Free: undangan digital dengan template terbatas, watermark Theday, fitur dasar checklist dan RSVP. Premium: akses ke semua template premium, tanpa watermark, custom domain, amplop digital, dan priority support."
              }
            },
            {
              "@type": "Question",
              "name": "Apakah tamu bisa konfirmasi kehadiran (RSVP) secara online?",
              "acceptedAnswer": {
                "@type": "Answer",
                "text": "Ya, tamu dapat langsung konfirmasi hadir atau tidak dari halaman undangan digital. Rekap kehadiran tersedia secara real-time di dashboard pengelola undangan."
              }
            }
          ]
        }
      ]
    }
    </script>
</head>

<body>

    {{-- ============================================================ --}}
    {{-- NAVBAR --}}
    {{-- ============================================================ --}}
    <nav id="navbar" aria-label="Navigasi utama"
        class="fixed top-0 left-0 right-0 z-50 transition-all duration-300 py-4 px-6">
        <div class="max-w-6xl mx-auto flex items-center justify-between">
            {{-- Logo --}}
            <a href="/" class="flex items-center">
                <img src="{{ asset('image/logo.svg') }}" alt="Theday" class="h-10 w-auto">
            </a>

            {{-- Desktop Nav Links --}}
            <div class="hidden md:flex items-center gap-8">
                <a href="#fitur" class="nav-link text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors"
                    data-id="Fitur" data-en="Features">Fitur</a>
                <a href="#harga" class="nav-link text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors"
                    data-id="Harga" data-en="Pricing">Harga</a>
                <a href="#cara-kerja" class="nav-link text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors"
                    data-id="Cara Kerja" data-en="How It Works">Cara Kerja</a>
                <a href="#faq" class="nav-link text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors"
                    data-id="FAQ" data-en="FAQ">FAQ</a>
            </div>

            {{-- CTA + Lang switcher --}}
            <div class="hidden md:flex items-center gap-3">
                {{-- Language Toggle --}}
                <button id="lang-toggle-desktop" onclick="toggleLanguage()" class="lang-btn">
                    <span id="lang-flag-desktop" aria-hidden="true">🇮🇩</span>
                    <span id="lang-label-desktop">ID</span>
                </button>

                @auth
                    <a href="/dashboard"
                        class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-white transition-all hover:opacity-90"
                        style="background-color: var(--color-primary)" data-id="Dashboard" data-en="Dashboard">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Dashboard
                    </a>
                @else
                    <a href="/login"
                        class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors px-4 py-2"
                        data-id="Masuk" data-en="Login">Masuk</a>
                    <a href="/register" class="btn-primary text-sm py-2 px-5" data-id="Mulai Gratis"
                        data-en="Start Free">Mulai Gratis</a>
                @endauth
            </div>

            {{-- Mobile: lang toggle + hamburger --}}
            <div class="flex md:hidden items-center gap-2">
                <button id="lang-toggle-mobile" onclick="toggleLanguage()" class="lang-btn">
                    <span id="lang-flag-mobile" aria-hidden="true">🇮🇩</span>
                    <span id="lang-label-mobile">ID</span>
                </button>
                <button id="mobile-menu-btn" class="p-2 rounded-lg text-gray-600 hover:bg-gray-100"
                    aria-label="Menu">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Mobile menu --}}
        <div id="mobile-menu" class="hidden md:hidden mt-4 pb-4 border-t border-gray-100 pt-4">
            <div class="flex flex-col gap-4 px-2">
                <a href="#fitur" class="text-sm font-medium text-gray-600" data-id="Fitur"
                    data-en="Features">Fitur</a>
                <a href="#harga" class="text-sm font-medium text-gray-600" data-id="Harga"
                    data-en="Pricing">Harga</a>
                <a href="#cara-kerja" class="text-sm font-medium text-gray-600" data-id="Cara Kerja"
                    data-en="How It Works">Cara Kerja</a>
                <a href="#faq" class="text-sm font-medium text-gray-600" data-id="FAQ" data-en="FAQ">FAQ</a>
                <div class="flex gap-3 pt-2">
                    @auth
                        <a href="/dashboard" class="btn-primary text-sm py-2 px-4 flex-1 justify-center"
                            data-id="Dashboard" data-en="Dashboard">Dashboard</a>
                    @else
                        <a href="/login" class="btn-outline text-sm py-2 px-4 flex-1 justify-center" data-id="Masuk"
                            data-en="Login">Masuk</a>
                        <a href="/register" class="btn-primary text-sm py-2 px-4 flex-1 justify-center"
                            data-id="Mulai Gratis" data-en="Start Free">Mulai Gratis</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>


    {{-- ============================================================ --}}
    {{-- HERO SECTION --}}
    {{-- ============================================================ --}}
    <main id="main-content">
        <section class="hero-gradient relative overflow-hidden flex items-center min-h-dvh pt-28 pb-20">
            {{-- Background decoration --}}
            <div class="absolute inset-0 dot-pattern opacity-40"></div>

            {{-- Floating decorative elements --}}
            <div class="absolute top-32 right-16 w-64 h-64 rounded-full opacity-20 float-animation"
                style="background: radial-gradient(circle, #92A89C, transparent)"></div>
            <div class="absolute bottom-32 left-12 w-48 h-48 rounded-full opacity-15 float-animation-delay"
                style="background: radial-gradient(circle, #CCD5AE, transparent)"></div>
            <div class="absolute top-1/2 left-1/2 w-32 h-32 rounded-full opacity-10 float-animation-delay-2"
                style="background: radial-gradient(circle, #92A89C, transparent)"></div>

            <div
                class="max-w-7xl mx-auto px-8 grid md:grid-cols-[1fr_1.1fr] gap-x-[72px] gap-y-12 items-center relative z-10">
                {{-- Hero Text --}}
                <div class="text-center md:text-left">
                    {{-- Eyebrow badge --}}
                    <div class="hero-anim inline-flex items-center gap-2 px-3 py-1.5 rounded-full mb-6"
                        style="background-color: rgba(146,168,156,0.15); animation-delay: 0.05s">
                        <span class="w-2 h-2 rounded-full" style="background-color: #92A89C"></span>
                        <span class="text-xs font-semibold" style="color: #73877C"
                            data-id="Persiapkan · Rayakan · Jalani" data-en="Prepare · Celebrate · Live">Persiapkan ·
                            Rayakan · Jalani</span>
                    </div>

                    {{-- Title --}}
                    <h1 class="hero-anim font-hero-display mb-3"
                        style="color: #2C2417; font-size: clamp(34px, 5vw, 58px); line-height: 1.05; letter-spacing: -0.015em; animation-delay: 0.12s">
                        Theday <span class="hero-amp" style="color: #92A89C; font-size: 0.9em">&amp;</span>
                        <span style="color: #92A89C">Beyond</span>
                    </h1>

                    {{-- Subtitle --}}
                    <h2 class="hero-anim italic-serif-hero text-gray-600 mt-2.5 mb-7"
                        style="font-size: clamp(22px, 2.2vw, 30px); font-weight: 400; animation-delay: 0.19s"
                        data-id="Merayakan hari, merawat cerita" data-en="Celebrate the day, cherish the story">
                        Merayakan hari, merawat cerita
                    </h2>

                    {{-- Description --}}
                    <p class="hero-anim text-gray-500 mb-8 mx-auto md:mx-0"
                        style="font-size: 16.5px; line-height: 1.65; max-width: 520px; animation-delay: 0.26s"
                        data-id="Pendamping pasangan dari hari spesial sampai kehidupan bersama. Mulai dari undangan digital, lanjut ke persiapan dan perjalanan pernikahan kamu."
                        data-en="Companion app for couples — from the special day to your shared life. Start with digital invitations, continue with planning and married life.">
                        Pendamping pasangan dari hari spesial sampai kehidupan bersama. Mulai dari undangan digital,
                        lanjut ke persiapan dan perjalanan pernikahan kamu.
                    </p>

                    {{-- CTAs --}}
                    <div class="hero-anim flex flex-col sm:flex-row gap-3 mb-8 justify-center md:justify-start"
                        style="animation-delay: 0.33s">
                        <a href="/register" class="btn-primary text-base py-3 px-6"
                            data-id="Mulai Perjalanan Bersama" data-en="Start Your Journey">
                            Mulai Perjalanan Bersama
                        </a>
                        <a href="#phase-journey" class="btn-outline text-base py-3 px-6"
                            data-id="Lihat Perjalanannya" data-en="See the Journey">
                            Lihat Perjalanannya
                        </a>
                    </div>

                    {{-- Trust signals (honest, non-fabricated) --}}
                    <div class="hero-anim space-y-2" style="animation-delay: 0.40s">
                        <div class="flex items-center gap-3 text-sm text-gray-500 justify-center md:justify-start">
                            {{-- Color palette swatch (brand tone hint) --}}
                            <div class="flex -space-x-1.5" aria-hidden="true">
                                <span class="w-5 h-5 rounded-full ring-2 ring-white"
                                    style="background-color:#B8C7BF"></span>
                                <span class="w-5 h-5 rounded-full ring-2 ring-white"
                                    style="background-color:#E8C5C0"></span>
                                <span class="w-5 h-5 rounded-full ring-2 ring-white"
                                    style="background-color:#F0E6D2"></span>
                                <span class="w-5 h-5 rounded-full ring-2 ring-white"
                                    style="background-color:#92A89C"></span>
                            </div>
                            <span data-id="Mulai gratis — tanpa kartu kredit" data-en="Start free — no credit card">
                                Mulai gratis — tanpa kartu kredit
                            </span>
                        </div>
                        <div class="flex items-center gap-1.5 text-sm text-gray-500 justify-center md:justify-start">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="#92A89C"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 6 9 17l-5-5" />
                            </svg>
                            <span data-id="32+ tema undangan premium siap pakai"
                                data-en="32+ premium invitation themes ready to use">
                                32+ tema undangan premium siap pakai
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Hero Illustration — line-art journey card (matches Landing Page.html) --}}
                <div class="hero-illustration order-first md:order-last">
                    <div class="relative mx-auto" style="max-width: 600px">
                        {{-- Illustration card --}}
                        <div class="relative w-full overflow-hidden"
                            style="aspect-ratio: 720 / 480; border-radius: 28px;
                                    background: linear-gradient(135deg, #F6F1E2 0%, #F4EDDC 60%, #EDE3C6 100%);
                                    box-shadow: 0 30px 60px -30px rgba(74,90,76,0.35), 0 2px 0 rgba(255,255,255,0.6) inset;
                                    border: 1px solid rgba(201,164,91,0.18);">
                            {{-- corner blob accents --}}
                            <div
                                style="position:absolute; top:-40px; left:-30px; width:220px; height:220px; border-radius:50%; filter:blur(2px); opacity:.7; background:#DCE4D3;">
                            </div>
                            <div
                                style="position:absolute; bottom:-50px; right:-40px; width:180px; height:180px; border-radius:50%; filter:blur(2px); opacity:.7; background:#E9DFC4;">
                            </div>
                            <div
                                style="position:absolute; top:40px; right:60px; width:90px; height:90px; border-radius:50%; filter:blur(2px); opacity:.7; background:rgba(217,181,176,0.35);">
                            </div>

                            <img src="{{ asset('images/landing/hero-journey.webp') }}"
                                alt="Perjalanan pasangan dari persiapan, hari pernikahan, sampai kehidupan bersama"
                                width="1100" height="619" loading="eager" fetchpriority="high" decoding="async"
                                style="position:relative; width:100%; height:100%; object-fit:cover; display:block;">

                            {{-- dashed border accent --}}
                            <div
                                style="position:absolute; inset:12px; border:1px dashed rgba(146,168,156,0.35); border-radius:20px; pointer-events:none;">
                            </div>
                        </div>

                        {{-- Floating: D-Day countdown (top-left) --}}
                        <div class="hero-mockup-float absolute -top-4 -left-3 sm:-top-7 sm:-left-7"
                            style="background:rgba(251,252,249,0.95); backdrop-filter:blur(8px); border-radius:16px; padding:14px 18px; box-shadow:0 20px 40px -20px rgba(74,90,76,0.35); border:1px solid rgba(216,223,210,0.8);">
                            <p
                                style="font-size:11px; color:#6C7A75; font-weight:500; letter-spacing:0.04em; text-transform:uppercase; margin:0;">
                                D-Day</p>
                            <p style="margin:4px 0 0; line-height:1;">
                                <span
                                    style="font-family:'Cormorant',serif; font-size:34px; font-weight:500; color:#1F2A2E;">120</span>
                                <span style="font-size:14px; font-weight:600; color:#73877C;" data-id="hari"
                                    data-en="days">hari</span>
                            </p>
                            <p class="tabular-nums" style="font-size:11px; color:#6C7A75; margin:6px 0 0;">22 · 11 ·
                                2026</p>
                        </div>

                        {{-- Floating: RSVP progress (bottom-right) --}}
                        <div class="hero-mockup-float-delay absolute -bottom-4 -right-2 sm:-bottom-6 sm:-right-3"
                            style="background:rgba(251,252,249,0.95); backdrop-filter:blur(8px); border-radius:16px; padding:14px 16px; box-shadow:0 20px 40px -20px rgba(74,90,76,0.35); border:1px solid rgba(216,223,210,0.8); min-width:220px;">
                            <div style="display:flex; align-items:center; gap:10px;">
                                <span
                                    style="width:34px; height:34px; border-radius:10px; background:#DCE4D3; display:grid; place-items:center; flex-shrink:0;">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                                        stroke="#4A5A4C" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 6 9 17l-5-5" />
                                    </svg>
                                </span>
                                <div>
                                    <p style="font-size:12.5px; font-weight:600; color:#1F2A2E; margin:0;"
                                        data-id="RSVP terkirim" data-en="RSVP confirmed">RSVP terkirim</p>
                                    <p style="font-size:11px; color:#6C7A75; margin:0;">
                                        <span class="tabular-nums">184</span>
                                        <span data-id="dari" data-en="of">dari</span>
                                        <span class="tabular-nums">220</span>
                                        <span data-id="tamu" data-en="guests">tamu</span>
                                    </p>
                                </div>
                            </div>
                            <div
                                style="margin-top:10px; height:5px; background:#DCE4D3; border-radius:999px; overflow:hidden;">
                                <div style="width:83%; height:100%; background:#92A89C; border-radius:999px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Wave divider --}}
            <div class="absolute bottom-0 left-0 right-0">
                <svg viewBox="0 0 1440 60" fill="none" xmlns="http://www.w3.org/2000/svg"
                    preserveAspectRatio="none">
                    <path d="M0 60L1440 60L1440 20C1200 60 960 0 720 20C480 40 240 10 0 30L0 60Z" fill="white" />
                </svg>
            </div>
        </section>


        {{-- ============================================================ --}}
        {{-- 3-PHASE JOURNEY --}}
        {{-- ============================================================ --}}
        <section id="phase-journey" class="py-24 bg-white">
            <div class="max-w-6xl mx-auto px-6">
                {{-- Section header --}}
                <div class="reveal text-center max-w-2xl mx-auto mb-16">
                    <h2 class="font-display text-3xl md:text-4xl font-bold mb-4" style="color: #2C2417"
                        data-id="Satu pendamping, tiga fase perjalanan" data-en="One companion, three phases">
                        Satu pendamping, tiga fase perjalanan
                    </h2>
                    <p class="text-gray-500 text-lg"
                        data-id="Pendamping kamu dari persiapan, perayaan, sampai kehidupan bersama setelahnya."
                        data-en="Your companion from preparation, celebration, to shared life after.">
                        Pendamping kamu dari persiapan, perayaan, sampai kehidupan bersama setelahnya.
                    </p>
                </div>

                {{-- 3 phase cards --}}
                <div class="grid md:grid-cols-3 gap-6">

                    {{-- Card 1: Sebelum --}}
                    <div class="reveal-fade rounded-2xl border p-6 md:p-8 hover:shadow-lg hover:-translate-y-1 transition-all duration-200"
                        style="border-color: rgba(146,168,156,0.2); background-color: #FFFCF7">
                        <img src="{{ asset('images/landing/phase-1.webp') }}" loading="lazy" width="760" height="760"
                            class="aspect-square w-full rounded-xl object-cover mb-5"
                            alt="Pasangan menyiapkan pernikahan — checklist, anggaran, daftar tamu">
                        <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold mb-3"
                            style="background: rgba(146,168,156,0.2); color: #73877C" data-id="FASE 1"
                            data-en="PHASE 1">FASE 1</span>
                        <h3 class="text-xl font-bold mb-2" style="color: #2C2417" data-id="Sebelum — Persiapan"
                            data-en="Before — Preparation">Sebelum — Persiapan</h3>
                        <p class="text-sm text-gray-500 mb-4"
                            data-id="Atur acara dengan tenang. Checklist, daftar tamu, anggaran — semua tertata."
                            data-en="Plan calmly. Checklist, guest list, budget — all organized.">
                            Atur acara dengan tenang. Checklist, daftar tamu, anggaran — semua tertata.
                        </p>
                        <ul class="space-y-2 text-sm text-gray-600 mb-4">
                            <li class="flex items-center gap-2">
                                <span style="color: #92A89C">&#10003;</span>
                                <span data-id="Checklist Persiapan" data-en="Preparation Checklist">Checklist
                                    Persiapan</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span style="color: #92A89C">&#10003;</span>
                                <span data-id="Daftar Tamu" data-en="Guest List">Daftar Tamu</span>
                            </li>
                            <li class="flex items-center gap-2 text-gray-400">
                                <span>&#8987;</span>
                                <span data-id="Anggaran Pernikahan" data-en="Wedding Budget">Anggaran
                                    Pernikahan</span>
                            </li>
                            <li class="flex items-center gap-2 text-gray-400">
                                <span>&#8987;</span>
                                <span data-id="Wedding Planner" data-en="Wedding Planner">Wedding Planner</span>
                            </li>
                        </ul>
                        <span class="inline-block px-3 py-1 rounded-full text-xs font-medium"
                            style="border: 1px solid rgba(146,168,156,0.4); color: #73877C" data-id="Hadir"
                            data-en="Available">Hadir</span>
                    </div>

                    {{-- Card 2: Hari H (FLAGSHIP — emphasized) --}}
                    <div class="reveal-fade rounded-2xl p-6 md:p-8 shadow-lg transition-all duration-200 md:scale-[1.02] hover:shadow-xl"
                        style="border: 2px solid #92A89C; background: white; animation-delay: 0.12s">
                        <img src="{{ asset('images/landing/phase-2.webp') }}" loading="lazy" width="760" height="760"
                            class="aspect-square w-full rounded-xl object-cover mb-5"
                            alt="Hari pernikahan — undangan digital, RSVP, manajemen tamu">
                        <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold text-white mb-3"
                            style="background-color: #C8A26B" data-id="FASE 2 · UNGGULAN"
                            data-en="PHASE 2 · FLAGSHIP">FASE 2 · UNGGULAN</span>
                        <h3 class="text-xl font-bold mb-2" style="color: #2C2417" data-id="Hari H — Perayaan"
                            data-en="The Day — Celebration">Hari H — Perayaan</h3>
                        <p class="text-sm text-gray-500 mb-4"
                            data-id="Wujudkan hari spesial. Undangan elegan, RSVP rapi, tamu terkelola."
                            data-en="Bring your special day to life. Elegant invitations, neat RSVP, managed guests.">
                            Wujudkan hari spesial. Undangan elegan, RSVP rapi, tamu terkelola.
                        </p>
                        <ul class="space-y-2 text-sm text-gray-600 mb-4">
                            <li class="flex items-center gap-2">
                                <span style="color: #92A89C">&#10003;</span>
                                <span data-id="Undangan Digital 30+ tema"
                                    data-en="Digital Invitation 30+ themes">Undangan Digital 30+ tema</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span style="color: #92A89C">&#10003;</span>
                                <span data-id="RSVP & Manajemen Tamu" data-en="RSVP & Guest Management">RSVP &amp;
                                    Manajemen Tamu</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span style="color: #92A89C">&#10003;</span>
                                <span data-id="Amplop Digital" data-en="Digital Envelope">Amplop Digital</span>
                            </li>
                            <li class="flex items-center gap-2 text-gray-400">
                                <span>&#8987;</span>
                                <span data-id="QR Check-in" data-en="QR Check-in">QR Check-in</span>
                            </li>
                        </ul>
                        <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold text-white"
                            style="background-color: #C8A26B" data-id="★ Unggulan" data-en="★ Flagship">&#9733;
                            Unggulan</span>
                    </div>

                    {{-- Card 3: Setelah (coming soon) --}}
                    <div class="reveal-fade rounded-2xl border p-6 md:p-8 hover:shadow-lg hover:-translate-y-1 transition-all duration-200"
                        style="border-color: rgba(146,168,156,0.2); background-color: rgba(255,252,247,0.6); animation-delay: 0.24s">
                        <img src="{{ asset('images/landing/phase-3.webp') }}" loading="lazy" width="760" height="760"
                            class="aspect-square w-full rounded-xl object-cover mb-5"
                            alt="Kehidupan setelah menikah — anniversary, album kenangan, perjalanan bersama">
                        <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold mb-3"
                            style="background: rgba(146,168,156,0.15); color: #73877C" data-id="FASE 3"
                            data-en="PHASE 3">FASE 3</span>
                        <h3 class="text-xl font-bold mb-2" style="color: #2C2417" data-id="Setelah — Jalani"
                            data-en="After — Live It">Setelah — Jalani</h3>
                        <p class="text-sm text-gray-500 mb-4"
                            data-id="Pendamping setelah hari H. Anniversary, album kenangan, perjalanan bersama."
                            data-en="Companion after the day. Anniversary, memory album, journey together.">
                            Pendamping setelah hari H. Anniversary, album kenangan, perjalanan bersama.
                        </p>
                        <ul class="space-y-2 text-sm text-gray-400 mb-4">
                            <li class="flex items-center gap-2"><span>&#8987;</span> <span
                                    data-id="Anniversary Reminder" data-en="Anniversary Reminder">Anniversary
                                    Reminder</span></li>
                            <li class="flex items-center gap-2"><span>&#8987;</span> <span data-id="Newlywed Admin"
                                    data-en="Newlywed Admin">Newlywed Admin</span></li>
                            <li class="flex items-center gap-2"><span>&#8987;</span> <span data-id="Memory Album"
                                    data-en="Memory Album">Memory Album</span></li>
                            <li class="flex items-center gap-2"><span>&#8987;</span> <span
                                    data-id="Date Night Planner" data-en="Date Night Planner">Date Night
                                    Planner</span></li>
                        </ul>
                        <span class="inline-block px-3 py-1 rounded-full text-xs font-medium"
                            style="border: 1px solid #D1D5DB; color: #6B7280" data-id="Segera Hadir"
                            data-en="Coming Soon">Segera Hadir</span>
                    </div>

                </div>
            </div>
        </section>


        {{-- ============================================================ --}}
        {{-- FITUR — "Satu aplikasi, seluruh perjalanan" (ported from theday(5)/sections.jsx) --}}
        {{-- ============================================================ --}}
        <section id="fitur" class="py-24" style="background-color: #F5F8F6">
            <div class="max-w-6xl mx-auto px-6">
                {{-- Section head --}}
                <div class="reveal max-w-2xl mb-12">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full mb-5"
                        style="background-color: rgba(146,168,156,0.15)">
                        <span class="w-2 h-2 rounded-full" style="background-color: #92A89C"></span>
                        <span class="text-xs font-semibold" style="color: #73877C" data-id="Fitur Lengkap"
                            data-en="Full Toolkit">Fitur Lengkap</span>
                    </div>
                    <h2 class="font-serif-hero"
                        style="color: #2C2417; font-size: clamp(34px, 5vw, 52px); line-height: 1.05; letter-spacing: -0.01em">
                        <span data-id="Satu aplikasi," data-en="One app,">Satu aplikasi,</span>
                        <span class="italic-serif-hero" style="color: #73877C" data-id="seluruh"
                            data-en="your whole">seluruh</span>
                        <span data-id="perjalanan" data-en="journey">perjalanan</span>
                    </h2>
                    <p class="text-gray-500 mt-4" style="font-size: 16.5px; line-height: 1.6"
                        data-id="Dari menyebar undangan, mengelola tamu, sampai membangun rumah tangga. Setiap fase punya alatnya sendiri — dirancang dengan rasa hangat ala Indonesia."
                        data-en="From sharing invitations and managing guests to building a home together. Every phase has its own tools — designed with Indonesian warmth.">
                        Dari menyebar undangan, mengelola tamu, sampai membangun rumah tangga. Setiap fase punya alatnya
                        sendiri — dirancang dengan rasa hangat ala Indonesia.
                    </p>
                </div>

                {{-- Feature cards --}}
                @php
                    $features = [
                        [
                            'invite',
                            'Undangan',
                            'Invitation',
                            'Undangan digital yang berkelas',
                            'Digital invitations with class',
                            'Pilih dari 30+ template elegan, kustomisasi warna, font, dan musik. Sebar ke ratusan tamu cukup satu link.',
                            'Pick from 30+ elegant templates, customize color, font, and music. Share to hundreds of guests with one link.',
                            true,
                        ],
                        [
                            'rsvp',
                            'Manajemen Tamu',
                            'Guest Management',
                            'RSVP & daftar tamu otomatis',
                            'Automatic RSVP & guest list',
                            'Tamu konfirmasi langsung di link undangan. Filter per keluarga, kerjaan, atau lokasi. Ekspor buat vendor catering.',
                            'Guests confirm right on the invitation link. Filter by family, work, or location. Export for catering vendors.',
                            true,
                        ],
                        [
                            'timeline',
                            'Perencanaan',
                            'Planning',
                            'Checklist & timeline yang ramah',
                            'A friendly checklist & timeline',
                            'Template 12 bulan sebelum hari H. Bagi tugas sama pasangan, atur reminder, simpan kontak vendor di satu tempat.',
                            'A 12-month-out template. Split tasks with your partner, set reminders, keep vendor contacts in one place.',
                            true,
                        ],
                        [
                            'budget',
                            'Keuangan',
                            'Finance',
                            'Budget tracker tanpa drama',
                            'Budget tracking, no drama',
                            'Set anggaran per kategori, catat pengeluaran, lihat sisa. Diskusi keuangan sama pasangan jadi jelas dan transparan.',
                            'Set budgets per category, log spending, see what is left. Money talks with your partner get clear.',
                            false,
                        ],
                        [
                            'gift',
                            'Hadiah',
                            'Gifts',
                            'Amplop & kado digital',
                            'Digital envelope & gifts',
                            'Terima ucapan dan hadiah langsung lewat QRIS atau transfer. Riwayat tamu tercatat otomatis buat balasan.',
                            'Receive wishes and gifts via QRIS or transfer. Guest history logged automatically for thank-yous.',
                            true,
                        ],
                        [
                            'beyond',
                            'Beyond',
                            'Beyond',
                            'Pendamping setelah hari H',
                            'A companion after the day',
                            'Album bersama, anniversary tracker, jurnal pasangan, dan goal keuangan rumah tangga — perjalanan tidak berhenti.',
                            'Shared album, anniversary tracker, couple journal, and household money goals — the journey doesn\'t stop.',
                            false,
                        ],
                    ];
                @endphp
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($features as [$visual, $tag, $tag_en, $title, $title_en, $desc, $desc_en, $live])
                        <article
                            class="reveal-fade rounded-[22px] overflow-hidden border bg-white transition duration-200 hover:-translate-y-1 hover:shadow-xl"
                            style="background-color: #FFFCF7; border-color: rgba(146,168,156,0.2); animation-delay: {{ ($loop->index % 3) * 90 }}ms">
                            {{-- Visual mockup --}}
                            <div class="relative h-[200px] border-b"
                                style="background: linear-gradient(135deg, #E8EDE3 0%, #DCE4D3 100%); border-color: rgba(146,168,156,0.2)">
                                @unless ($live)
                                    <span
                                        class="absolute top-3 right-3 z-10 text-[10px] font-semibold px-2.5 py-1 rounded-full"
                                        style="background: rgba(255,255,255,0.9); border: 1px solid #D1D5DB; color: #6B7280"
                                        data-id="Segera" data-en="Coming Soon">Segera</span>
                                @endunless

                                @if ($visual === 'invite')
                                    <div class="absolute"
                                        style="top: 20px; left: 30px; width: 140px; height: 110px; background: #FBFCF9; border: 1px solid rgba(146,168,156,0.4); border-radius: 10px; padding: 14px; box-shadow: 0 12px 30px -15px rgba(74,90,76,0.4); transform: rotate(-6deg)">
                                        <div class="italic-serif-hero" style="color: #73877C; font-size: 12px">The
                                            wedding of</div>
                                        <div
                                            style="font-family: 'Cormorant', serif; font-size: 22px; line-height: 1; font-weight: 500; color: #2C2417; margin-top: 6px">
                                            Ayu &amp; Rizki</div>
                                        <div style="height: 1px; background: rgba(146,168,156,0.4); margin: 10px 0">
                                        </div>
                                        <div style="font-size: 10px; color: #9CA3AF; letter-spacing: 0.18em">22 · 11 ·
                                            2026</div>
                                    </div>
                                    <div class="absolute"
                                        style="top: 50px; left: 110px; width: 140px; height: 110px; background: #F4EDDC; border: 1px solid rgba(146,168,156,0.4); border-radius: 10px; padding: 14px; box-shadow: 0 12px 30px -15px rgba(74,90,76,0.4); transform: rotate(5deg)">
                                        <div class="italic-serif-hero" style="color: #C8895E; font-size: 12px">Save
                                            the date</div>
                                        <div
                                            style="font-family: 'Cormorant', serif; font-size: 20px; line-height: 1; font-weight: 500; color: #2C2417; margin-top: 6px">
                                            Dito &amp; Mira</div>
                                        <div style="height: 1px; background: #E8C5C0; margin: 10px 0"></div>
                                        <div style="font-size: 10px; color: #9CA3AF; letter-spacing: 0.18em">14 · 06 ·
                                            2026</div>
                                    </div>
                                @elseif($visual === 'rsvp')
                                    <div class="absolute"
                                        style="inset: 16px 24px; background: #fff; border-radius: 12px; padding: 14px; box-shadow: 0 8px 20px -10px rgba(74,90,76,0.25); border: 1px solid rgba(146,168,156,0.2)">
                                        <div class="flex justify-between items-center mb-3">
                                            <span style="font-size: 11px; font-weight: 600; color: #2C2417">Daftar
                                                Tamu</span>
                                            <span class="font-mono"
                                                style="font-size: 10px; color: #73877C; background: #DCE4D3; border-radius: 4px; padding: 2px 6px; font-weight: 600">184
                                                / 220</span>
                                        </div>
                                        @foreach ([['BS', 'Bp. Surya & Ibu', true], ['DN', 'Dewi Nugraha', true], ['RP', 'Rian Pratama', false], ['LH', 'Lia Hartono', true]] as $r => [$init, $name, $hadir])
                                            <div class="flex items-center gap-2"
                                                style="padding: 5px 0; {{ $r ? 'border-top: 1px solid rgba(146,168,156,0.2)' : '' }}">
                                                <div
                                                    style="width: 18px; height: 18px; border-radius: 50%; background: #DCE4D3; display: grid; place-items: center; font-size: 8px; font-weight: 700; color: #4A5A4C">
                                                    {{ $init }}</div>
                                                <div style="font-size: 10px; color: #2C2417; flex: 1">
                                                    {{ $name }}</div>
                                                <div
                                                    style="font-size: 9px; font-weight: 600; color: {{ $hadir ? '#73877C' : '#9CA3AF' }}">
                                                    {{ $hadir ? '✓ Hadir' : 'menunggu' }}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                @elseif($visual === 'timeline')
                                    <div class="absolute flex flex-col" style="inset: 14px 22px; gap: 6px">
                                        @foreach ([['12 bln', 'Tentukan tanggal', true], ['9 bln', 'Booking venue', true], ['6 bln', 'Pilih vendor catering', true], ['3 bln', 'Fitting baju', false], ['1 bln', 'Kirim undangan', false]] as [$when, $what, $done])
                                            <div class="flex items-center gap-2.5"
                                                style="background: #fff; border-radius: 10px; padding: 7px 10px; border: 1px solid rgba(146,168,156,0.2)">
                                                <div
                                                    style="width: 16px; height: 16px; border-radius: 50%; border: 2px solid {{ $done ? '#92A89C' : '#D1D5DB' }}; background: {{ $done ? '#92A89C' : 'transparent' }}; display: grid; place-items: center">
                                                    @if ($done)
                                                        <span
                                                            style="color: #fff; font-size: 9px; font-weight: 700">✓</span>
                                                    @endif
                                                </div>
                                                <div class="font-mono"
                                                    style="font-size: 9px; color: #9CA3AF; min-width: 32px">
                                                    {{ $when }}</div>
                                                <div
                                                    style="font-size: 10.5px; color: #2C2417; flex: 1; {{ $done ? 'text-decoration: line-through; opacity: 0.6' : '' }}">
                                                    {{ $what }}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                @elseif($visual === 'budget')
                                    <div class="absolute"
                                        style="inset: 16px 22px; background: #fff; border-radius: 12px; padding: 14px; border: 1px solid rgba(146,168,156,0.2)">
                                        <div class="flex justify-between mb-3">
                                            <div>
                                                <div
                                                    style="font-size: 9px; color: #9CA3AF; letter-spacing: 0.08em; text-transform: uppercase">
                                                    Total budget</div>
                                                <div
                                                    style="font-family: 'Cormorant', serif; font-size: 20px; font-weight: 500; color: #2C2417; line-height: 1">
                                                    Rp 285jt</div>
                                            </div>
                                            <div class="text-right">
                                                <div style="font-size: 9px; color: #9CA3AF">terpakai</div>
                                                <div style="font-size: 13px; color: #73877C; font-weight: 600">62%
                                                </div>
                                            </div>
                                        </div>
                                        @foreach ([['Venue & Catering', 65, '#92A89C'], ['Dekorasi', 80, '#C8895E'], ['Foto & Video', 45, '#C8A26B'], ['Souvenir', 30, '#B8C7BF']] as [$n, $p, $c])
                                            <div style="margin-bottom: 8px">
                                                <div class="flex justify-between"
                                                    style="font-size: 9.5px; color: #5C6B63; margin-bottom: 3px">
                                                    <span>{{ $n }}</span><span
                                                        class="font-mono">{{ $p }}%</span></div>
                                                <div
                                                    style="height: 4px; background: #DCE4D3; border-radius: 999px; overflow: hidden">
                                                    <div class="bar-fill"
                                                        style="width: {{ $p }}%; height: 100%; background: {{ $c }}; border-radius: 999px">
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @elseif($visual === 'gift')
                                    <div class="absolute text-center"
                                        style="inset: 18px 28px; background: #fff; border-radius: 12px; border: 1px solid rgba(146,168,156,0.2); padding: 14px">
                                        <div class="italic-serif-hero" style="font-size: 13px; color: #73877C">amplop
                                            digital</div>
                                        <div
                                            style="margin: 10px auto; width: 70px; height: 70px; background: #fff; border-radius: 12px; border: 1px solid rgba(146,168,156,0.2); display: grid; place-items: center">
                                            <div
                                                style="display: grid; grid-template-columns: repeat(8,1fr); gap: 2px; width: 54px; height: 54px">
                                                @for ($i = 0; $i < 64; $i++)
                                                    <div
                                                        style="background: {{ ($i * 7) % 3 === 0 || $i % 5 === 0 || $i === 0 || $i === 7 || $i === 56 ? '#2C2417' : 'transparent' }}; border-radius: 1px">
                                                    </div>
                                                @endfor
                                            </div>
                                        </div>
                                        <div class="flex justify-center gap-1.5" style="margin-top: 8px">
                                            <div
                                                style="font-size: 9px; padding: 3px 8px; background: #DCE4D3; border-radius: 999px; color: #4A5A4C; font-weight: 600">
                                                QRIS</div>
                                            <div
                                                style="font-size: 9px; padding: 3px 8px; background: #F4EDDC; border-radius: 999px; color: #C8895E; font-weight: 600">
                                                Transfer</div>
                                        </div>
                                    </div>
                                @elseif($visual === 'beyond')
                                    <div class="absolute flex flex-col" style="inset: 16px 22px; gap: 8px">
                                        <div class="flex items-center gap-2.5"
                                            style="background: #fff; border: 1px solid rgba(146,168,156,0.2); border-radius: 10px; padding: 10px 12px">
                                            <div
                                                style="width: 32px; height: 32px; border-radius: 8px; background: #E8C5C0; display: grid; place-items: center">
                                                <svg width="16" height="16" viewBox="0 0 24 24"
                                                    fill="#fff">
                                                    <path
                                                        d="M12 21s-7-4.5-7-10a4 4 0 0 1 7-2.5A4 4 0 0 1 19 11c0 5.5-7 10-7 10z" />
                                                </svg>
                                            </div>
                                            <div style="flex: 1">
                                                <div style="font-size: 10px; color: #9CA3AF">1st Anniversary</div>
                                                <div style="font-size: 11.5px; color: #2C2417; font-weight: 600">
                                                    tinggal 248 hari</div>
                                            </div>
                                        </div>
                                        <div
                                            style="background: #fff; border: 1px solid rgba(146,168,156,0.2); border-radius: 10px; padding: 10px 12px">
                                            <div
                                                style="font-size: 9px; color: #9CA3AF; letter-spacing: 0.08em; text-transform: uppercase">
                                                Goal · DP Rumah</div>
                                            <div class="flex justify-between" style="margin-top: 4px">
                                                <span style="font-size: 11px; color: #2C2417; font-weight: 600">Rp 38jt
                                                    / 150jt</span>
                                                <span class="font-mono"
                                                    style="font-size: 10px; color: #73877C; font-weight: 600">25%</span>
                                            </div>
                                            <div
                                                style="height: 4px; background: #DCE4D3; border-radius: 999px; margin-top: 6px">
                                                <div class="bar-fill"
                                                    style="width: 25%; height: 100%; background: #92A89C; border-radius: 999px">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2"
                                            style="background: #fff; border: 1px solid rgba(146,168,156,0.2); border-radius: 10px; padding: 8px 12px">
                                            <div class="italic-serif-hero" style="font-size: 15px; color: #73877C">
                                                "hari pertama jadi suami"</div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            {{-- Card body --}}
                            <div style="padding: 22px 24px 26px">
                                <div class="uppercase font-semibold"
                                    style="font-size: 11px; letter-spacing: 0.12em; color: #73877C; margin-bottom: 10px"
                                    data-id="{{ $tag }}" data-en="{{ $tag_en }}">{{ $tag }}
                                </div>
                                <h3 style="font-family: 'Cormorant', serif; font-size: 25px; line-height: 1.1; color: #2C2417; margin-bottom: 10px; font-weight: 500; letter-spacing: -0.01em"
                                    data-id="{{ $title }}" data-en="{{ $title_en }}">{{ $title }}
                                </h3>
                                <p style="font-size: 14.5px; color: #5C6B63; line-height: 1.6"
                                    data-id="{{ $desc }}" data-en="{{ $desc_en }}">{{ $desc }}
                                </p>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>


        {{-- ============================================================ --}}
        {{-- HOW IT WORKS --}}
        {{-- ============================================================ --}}
        <section id="cara-kerja" class="py-24" style="background-color: #FFFCF7">
            <div class="max-w-6xl mx-auto px-6">
                <div class="text-center max-w-2xl mx-auto mb-16 reveal">
                    <h2 class="font-display text-3xl md:text-4xl font-bold mb-4" style="color: #2C2417"
                        data-id="3 langkah, mulai perjalanan" data-en="3 steps to start your journey">3 langkah, mulai
                        perjalanan</h2>
                    <p class="text-gray-500 text-lg"
                        data-id="Bisa pakai dari fase mana aja, bahkan kalau kamu sudah menikah."
                        data-en="Start from any phase, even if you're already married.">
                        Bisa pakai dari fase mana aja, bahkan kalau kamu sudah menikah.
                    </p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    {{-- Step 1 --}}
                    <div class="text-center reveal">
                        <img src="{{ asset('images/landing/step-1.webp') }}" alt="Daftar Gratis" loading="lazy" width="600" height="600"
                            class="w-28 h-28 mx-auto rounded-2xl object-contain mb-4">
                        <div class="w-8 h-8 mx-auto rounded-full text-white flex items-center justify-center font-bold mb-3 text-sm"
                            style="background-color: #92A89C">1</div>
                        <h3 class="text-lg font-bold mb-2" style="color: #2C2417" data-id="Daftar Gratis"
                            data-en="Sign Up Free">Daftar Gratis</h3>
                        <p class="text-sm text-gray-500"
                            data-id="Buat akun Theday dalam 30 detik. Tanpa kartu kredit."
                            data-en="Create your Theday account in 30 seconds. No credit card.">Buat akun Theday dalam
                            30 detik. Tanpa kartu kredit.</p>
                    </div>
                    {{-- Step 2 --}}
                    <div class="text-center reveal">
                        <img src="{{ asset('images/landing/step-2.webp') }}" alt="Atur Tanggal & Lokasi" loading="lazy" width="600" height="600"
                            class="w-28 h-28 mx-auto rounded-2xl object-contain mb-4">
                        <div class="w-8 h-8 mx-auto rounded-full text-white flex items-center justify-center font-bold mb-3 text-sm"
                            style="background-color: #92A89C">2</div>
                        <h3 class="text-lg font-bold mb-2" style="color: #2C2417" data-id="Atur Tanggal & Lokasi"
                            data-en="Set Date & Location">Atur Tanggal &amp; Lokasi</h3>
                        <p class="text-sm text-gray-500"
                            data-id="Set tanggal pernikahan kamu — atau anniversary kalau sudah menikah."
                            data-en="Set your wedding date — or anniversary if already married.">Set tanggal pernikahan
                            kamu — atau anniversary kalau sudah menikah.</p>
                    </div>
                    {{-- Step 3 --}}
                    <div class="text-center reveal">
                        <img src="{{ asset('images/landing/step-3.webp') }}" alt="Mulai dari Fase Mana Aja" loading="lazy" width="600" height="600"
                            class="w-28 h-28 mx-auto rounded-2xl object-contain mb-4">
                        <div class="w-8 h-8 mx-auto rounded-full text-white flex items-center justify-center font-bold mb-3 text-sm"
                            style="background-color: #92A89C">3</div>
                        <h3 class="text-lg font-bold mb-2" style="color: #2C2417" data-id="Mulai dari Fase Mana Aja"
                            data-en="Start from Any Phase">Mulai dari Fase Mana Aja</h3>
                        <p class="text-sm text-gray-500"
                            data-id="Pilih checklist persiapan, atau langsung bikin undangan, atau atur anniversary. Bebas."
                            data-en="Pick preparation checklist, or make an invitation, or set anniversary. Your choice.">
                            Pilih checklist persiapan, atau langsung bikin undangan, atau atur anniversary. Bebas.</p>
                    </div>
                </div>
            </div>
        </section>


        {{-- ============================================================ --}}
        {{-- PRICING SECTION --}}
        {{-- ============================================================ --}}
        <section id="harga" class="py-24 bg-white">
            <div class="max-w-4xl mx-auto px-6">
                <div class="reveal text-center max-w-2xl mx-auto mb-14">
                    <h2 class="font-display text-3xl md:text-4xl font-bold mb-4" style="color: #2C2417"
                        data-id="Mulai gratis, upgrade kapan kamu butuh" data-en="Start free, upgrade when you need">
                        Mulai gratis, upgrade kapan kamu butuh</h2>
                    <p class="text-gray-500 text-lg" data-id="Tanpa kartu kredit. Cancel kapan saja."
                        data-en="No credit card. Cancel anytime.">Tanpa kartu kredit. Cancel kapan saja.</p>
                </div>
                <div class="grid md:grid-cols-2 gap-6">
                    {{-- Free --}}
                    <div class="reveal rounded-2xl border p-8" style="border-color: rgba(146,168,156,0.2)">
                        <h3 class="text-xl font-bold mb-1" style="color: #2C2417" data-id="Free" data-en="Free">Free
                        </h3>
                        <p class="text-3xl font-bold mb-1" style="color: #2C2417">Rp 0</p>
                        <p class="text-sm text-gray-400 mb-6" data-id="Selamanya" data-en="Forever">Selamanya</p>
                        <ul class="space-y-3 text-sm text-gray-600 mb-8">
                            <li class="flex gap-2">
                                <span style="color: #92A89C">&#10003;</span>
                                <span data-id="Undangan digital (template terbatas)"
                                    data-en="Digital invitation (limited templates)">Undangan digital (template
                                    terbatas)</span>
                            </li>
                            <li class="flex gap-2">
                                <span style="color: #92A89C">&#10003;</span>
                                <span data-id="Checklist & Daftar Tamu" data-en="Checklist & Guest List">Checklist
                                    &amp; Daftar Tamu</span>
                            </li>
                            <li class="flex gap-2">
                                <span style="color: #92A89C">&#10003;</span>
                                <span data-id="RSVP & Wishes" data-en="RSVP & Wishes">RSVP &amp; Wishes</span>
                            </li>
                            <li class="flex gap-2 text-gray-400">
                                <span>&#8226;</span>
                                <span data-id="Watermark Theday" data-en="Theday watermark">Watermark Theday</span>
                            </li>
                        </ul>
                        <a href="/register" class="btn-outline w-full text-center block py-2.5"
                            data-id="Mulai Gratis" data-en="Start Free">Mulai Gratis</a>
                    </div>
                    {{-- Premium --}}
                    <div class="reveal rounded-2xl p-8 relative" style="border: 2px solid #C8A26B; animation-delay: 0.12s">
                        <span
                            class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 rounded-full text-xs font-semibold text-white"
                            style="background-color: #C8A26B" data-id="Paling Dipilih" data-en="Most Popular">Paling
                            Dipilih</span>
                        <h3 class="text-xl font-bold mb-1" style="color: #2C2417" data-id="Premium"
                            data-en="Premium">Premium</h3>
                        @php
                            $premiumPlan = $plans['premium'] ?? null;
                            $premiumPrice = $premiumPlan ? $premiumPlan->effectivePrice() : 49000;
                            $premiumDuration = $premiumPlan->duration_days ?? 365;
                        @endphp
                        <div class="mb-6">
                            @if ($premiumPlan && $premiumPlan->hasActiveDiscount())
                                <span class="text-base text-gray-400 line-through">Rp
                                    {{ number_format((int) $premiumPlan->price, 0, ',', '.') }}</span>
                            @endif
                            <p class="text-3xl font-bold" style="color: #C8A26B">Rp
                                {{ number_format($premiumPrice, 0, ',', '.') }}</p>
                            <p class="text-sm text-gray-400" data-id="untuk {{ $premiumDuration }} hari aktif"
                                data-en="for {{ $premiumDuration }} active days">untuk {{ $premiumDuration }} hari
                                aktif</p>
                        </div>
                        <ul class="space-y-3 text-sm text-gray-600 mb-8">
                            <li class="flex gap-2"><span style="color: #C8A26B">&#10003;</span> <span
                                    data-id="Semua tema premium (Onyx, Astronomy, dll)"
                                    data-en="All premium themes (Onyx, Astronomy, etc.)">Semua tema premium (Onyx,
                                    Astronomy, dll)</span></li>
                            <li class="flex gap-2"><span style="color: #C8A26B">&#10003;</span> <span
                                    data-id="Tanpa watermark" data-en="No watermark">Tanpa watermark</span></li>
                            <li class="flex gap-2"><span style="color: #C8A26B">&#10003;</span> <span
                                    data-id="Custom domain" data-en="Custom domain">Custom domain</span></li>
                            <li class="flex gap-2"><span style="color: #C8A26B">&#10003;</span> <span
                                    data-id="Amplop digital + analytics" data-en="Digital envelope + analytics">Amplop
                                    digital + analytics</span></li>
                            <li class="flex gap-2"><span style="color: #C8A26B">&#10003;</span> <span
                                    data-id="Priority support" data-en="Priority support">Priority support</span></li>
                        </ul>
                        <a href="{{ auth()->check() ? '/paket' : '/register' }}"
                            class="w-full text-center block py-2.5 rounded-xl font-semibold text-white transition-all hover:opacity-90"
                            style="background-color: #C8A26B" data-id="Pilih Premium" data-en="Choose Premium">Pilih
                            Premium</a>
                    </div>
                </div>
            </div>
        </section>


        {{-- ============================================================ --}}
        {{-- FAQ --}}
        {{-- ============================================================ --}}
        <section id="faq" class="py-24" style="background-color: #F5F8F6">
            <div class="max-w-3xl mx-auto px-6">
                <div class="reveal text-center mb-14">
                    <h2 class="font-display text-3xl md:text-4xl font-bold mb-4" style="color: #2C2417"
                        data-id="Pertanyaan Umum" data-en="Frequently Asked Questions">Pertanyaan Umum</h2>
                    <p class="text-gray-500 text-lg"
                        data-id="Hal yang sering ditanyakan calon dan pasangan suami-istri."
                        data-en="Common questions from couples and newlyweds.">Hal yang sering ditanyakan calon dan
                        pasangan suami-istri.</p>
                </div>
                <div class="space-y-3">
                    @php $faqs = [
                            [
                                'Saya udah nikah, masih bisa pakai Theday?',
                                'Bisa! Fitur Fase 3 (Setelah Nikah) seperti anniversary reminder, memory album, dan joint budget dirancang untuk pasangan yang sudah menikah. Fitur ini sedang dikembangkan dan akan tersedia bertahap. Daftar sekarang gratis biar dapat akses awal saat rilis.',
                            ],
                            [
                                'Apakah saya wajib pakai fitur undangan?',
                                'Tidak. Undangan digital adalah salah satu fitur unggulan, tapi kamu bisa pakai Theday cuma untuk checklist persiapan, daftar tamu, RSVP, atau (saat hadir) fitur setelah nikah. Bebas pilih sesuai kebutuhan.',
                            ],
                            [
                                'Apa bedanya Theday & Beyond dengan platform undangan lain?',
                                'Theday fokus ke perjalanan pernikahan jangka panjang — bukan cuma event sehari. Kami menggabungkan kualitas craft template undangan premium dengan fitur pendamping seumur hidup pasangan: dari persiapan, hari H, sampai kehidupan setelahnya. Dirancang khusus untuk pasangan Indonesia.',
                            ],
                            [
                                'Fitur Setelah Nikah kapan tersedia?',
                                'Fitur Fase 3 (anniversary reminder, memory album, newlywed admin, joint budget) sedang dikembangkan dan akan dirilis bertahap. Kamu yang sudah daftar akan dapat notifikasi saat setiap fitur rilis.',
                            ],
                            [
                                'Apa bedanya paket Free dan Premium?',
                                'Free: undangan digital dengan template terbatas, watermark Theday, fitur dasar checklist + RSVP. Premium: akses ke semua template premium (Netflix, Onyx, Astronomy, Spotify Wrapped, dan lain-lain), tanpa watermark, custom domain, amplop digital advance, dan priority support.',
                            ],
                            [
                                'Bagaimana cara membatalkan langganan?',
                                'Premium subscription bisa dibatalkan kapan saja dari Dashboard → Settings → Subscription → Cancel. Tidak ada biaya pembatalan. Akses Premium tetap aktif sampai akhir periode yang sudah dibayar.',
                            ],
                            [
                                'Data saya aman?',
                                'Data kamu dienkripsi dan disimpan di server Indonesia (sesuai regulasi PP No. 71/2019). Kami tidak menjual data ke pihak ketiga. Detail lengkap di Kebijakan Privasi.',
                            ],
                    ]; @endphp
                    @foreach ($faqs as $i => [$q, $a])
                        <div class="faq-item reveal-fade rounded-xl bg-white overflow-hidden"
                            style="border: 1px solid rgba(146,168,156,0.15); animation-delay: {{ $i * 60 }}ms">
                            <button
                                class="faq-q w-full flex items-center justify-between text-left px-5 py-4 font-semibold cursor-pointer"
                                style="color: #2C2417" data-faq="{{ $i }}" aria-expanded="false">
                                <span>{{ $q }}</span>
                                <span class="faq-icon flex-shrink-0 ml-3 text-xl font-light"
                                    style="color: #92A89C">+</span>
                            </button>
                            <div class="faq-a px-5 text-sm text-gray-500 leading-relaxed">
                                {{ $a }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
        {{-- Note: FAQ copy is Indonesian-first for v1 (long answer text). English bilingual for FAQ is a follow-up task. --}}


        {{-- ============================================================ --}}
        {{-- BLOG TEASER SECTION --}}
        {{-- ============================================================ --}}
        @if (isset($featuredArticles) && $featuredArticles->count() > 0)
            <section id="blog" class="py-24 bg-white reveal">
                <div class="max-w-6xl mx-auto px-6">
                    {{-- Section header --}}
                    <div class="text-center mb-12">
                        <p class="text-sm font-semibold uppercase tracking-widest mb-2"
                            style="color: var(--color-primary)">Inspirasi & Panduan</p>
                        <h2 class="font-display text-3xl md:text-4xl font-semibold mb-3"
                            style="color: var(--color-dark)">Artikel Terbaru</h2>
                        <p class="text-gray-500 max-w-md mx-auto text-base">Tips pernikahan, inspirasi undangan, dan
                            panduan merencanakan hari istimewamu.</p>
                    </div>

                    {{-- Article cards --}}
                    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
                        @foreach ($featuredArticles as $article)
                            <a href="/blog/{{ $article['slug'] }}"
                                class="group block bg-white rounded-2xl overflow-hidden feature-card cursor-pointer"
                                style="box-shadow: 0 2px 12px rgba(0,0,0,0.06)">
                                {{-- Cover --}}
                                <div class="aspect-video overflow-hidden bg-stone-100 relative">
                                    @if ($article['cover_image_url'])
                                        <img src="{{ $article['cover_image_url'] }}" alt="{{ $article['title'] }}"
                                            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" />
                                    @else
                                        <div class="w-full h-full flex items-center justify-center"
                                            style="background: linear-gradient(135deg, #EBF0ED, #DDEAE4)">
                                            <svg class="w-10 h-10 text-stone-300" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="1.5"
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                {{-- Body --}}
                                <div class="p-5">
                                    @if ($article['category'])
                                        <span class="text-xs font-semibold uppercase tracking-wider"
                                            style="color: var(--color-primary)">
                                            {{ $article['category']['name'] }}
                                        </span>
                                    @endif
                                    <h3 class="font-display text-lg font-semibold mt-1.5 mb-2 leading-snug group-hover:opacity-70 transition"
                                        style="color: var(--color-dark)">
                                        {{ $article['title'] }}
                                    </h3>
                                    <p class="text-gray-500 text-sm leading-relaxed line-clamp-2 mb-4">
                                        {{ $article['excerpt'] }}</p>
                                    <div class="flex items-center justify-between text-xs text-gray-400">
                                        <span>
                                            @if ($article['published_at'])
                                                {{ \Carbon\Carbon::parse($article['published_at'])->translatedFormat('d M Y') }}
                                            @endif
                                        </span>
                                        <span>{{ $article['reading_time'] }} menit baca</span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>

                    {{-- CTA link --}}
                    <div class="text-center">
                        <a href="/blog" class="btn-outline inline-flex items-center gap-2">
                            <span>Baca Artikel Lainnya</span>
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                            </svg>
                        </a>
                    </div>
                </div>
            </section>
        @endif


        {{-- ============================================================ --}}
        {{-- CTA SECTION --}}
        {{-- ============================================================ --}}
        <section class="py-24 relative overflow-hidden" style="background: linear-gradient(135deg, #1E1E1E, #2D2520)">
            <div class="absolute inset-0 dot-pattern opacity-10"></div>
            <div class="absolute top-0 left-0 w-96 h-96 rounded-full opacity-10"
                style="background: radial-gradient(circle, #92A89C, transparent); transform: translate(-50%, -50%)">
            </div>
            <div class="absolute bottom-0 right-0 w-96 h-96 rounded-full opacity-10"
                style="background: radial-gradient(circle, #CCD5AE, transparent); transform: translate(50%, 50%)">
            </div>

            <div class="max-w-3xl mx-auto px-6 text-center relative z-10 reveal">
                <h2 class="font-display text-3xl md:text-5xl font-semibold text-white mb-4">
                    <span data-id="Siap memulai perjalanan?" data-en="Ready to start your journey?">Siap memulai
                        perjalanan?</span>
                </h2>
                <p class="text-gray-400 text-lg mb-8 max-w-xl mx-auto"
                    data-id="Daftar gratis hari ini, mulai dari fase mana aja."
                    data-en="Sign up free today, start from any phase.">
                    Daftar gratis hari ini, mulai dari fase mana aja.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center mb-5">
                    <a href="/register" class="btn-primary text-base py-3.5 px-10">
                        <span data-id="Mulai Perjalanan Bersama →" data-en="Start Your Journey →">Mulai Perjalanan
                            Bersama &#8594;</span>
                    </a>
                </div>
                <p class="text-gray-500 text-sm" data-id="Gratis · Tanpa kartu kredit · Cancel kapan saja"
                    data-en="Free · No credit card · Cancel anytime">
                    Gratis · Tanpa kartu kredit · Cancel kapan saja
                </p>
            </div>
        </section>


    </main>

    {{-- ============================================================ --}}
    {{-- FOOTER --}}
    {{-- ============================================================ --}}
    <footer style="background-color: #111; color: #888">
        <div class="max-w-6xl mx-auto px-6 py-16">
            <div class="grid grid-cols-2 md:grid-cols-5 gap-8 mb-12">
                {{-- Brand --}}
                <div class="col-span-2 md:col-span-1">
                    <a href="/" class="flex items-center mb-4">
                        <img src="{{ asset('image/logo.svg') }}" alt="Theday"
                            class="h-10 w-auto brightness-0 invert">
                    </a>
                    <p class="text-sm leading-relaxed mb-1 font-semibold" style="color: #92A89C">
                        Theday &amp; Beyond
                    </p>
                    <p class="text-sm leading-relaxed mb-5"
                        data-id="Merayakan hari, merawat cerita — pendamping pasangan Indonesia."
                        data-en="Celebrate the day, cherish the story — companion for Indonesian couples.">
                        Merayakan hari, merawat cerita — pendamping pasangan Indonesia.
                    </p>
                    <div class="flex items-center gap-3">
                        @foreach (['instagram', 'tiktok', 'whatsapp'] as $social)
                            <a href="#" aria-label="{{ ucfirst($social) }}"
                                class="w-9 h-9 rounded-full flex items-center justify-center transition-colors"
                                style="background: rgba(255,255,255,0.08)"
                                onmouseover="this.style.background='rgba(146,168,156,0.3)'"
                                onmouseout="this.style.background='rgba(255,255,255,0.08)'">
                                <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    @if ($social === 'instagram')
                                        <path
                                            d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z" />
                                    @elseif($social === 'tiktok')
                                        <path
                                            d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z" />
                                    @else
                                        <path
                                            d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                                    @endif
                                </svg>
                            </a>
                        @endforeach
                    </div>
                </div>

                {{-- Links --}}
                @php
                    $footerLinks = [
                        [
                            'id_cat' => 'Produk',
                            'en_cat' => 'Product',
                            'links' => [
                                ['id' => 'Fitur', 'en' => 'Features', 'href' => '/#fitur'],
                                ['id' => 'Tema', 'en' => 'Themes', 'href' => '/templates'],
                                ['id' => 'Harga', 'en' => 'Pricing', 'href' => '/#harga'],
                                ['id' => 'FAQ', 'en' => 'FAQ', 'href' => '/#faq'],
                            ],
                        ],
                        [
                            'id_cat' => 'Perusahaan',
                            'en_cat' => 'Company',
                            'links' => [
                                ['id' => 'Tentang', 'en' => 'About', 'href' => '#'],
                                ['id' => 'Blog', 'en' => 'Blog', 'href' => route('blog.index')],
                                ['id' => 'Kontak', 'en' => 'Contact', 'href' => route('contact')],
                            ],
                        ],
                        [
                            'id_cat' => 'Bantuan',
                            'en_cat' => 'Help',
                            'links' => [
                                ['id' => 'FAQ', 'en' => 'FAQ', 'href' => '/#faq'],
                                ['id' => 'Panduan', 'en' => 'Guide', 'href' => '#'],
                                ['id' => 'Kontak Support', 'en' => 'Contact Support', 'href' => route('contact')],
                            ],
                        ],
                        [
                            'id_cat' => 'Legal',
                            'en_cat' => 'Legal',
                            'links' => [
                                ['id' => 'Privasi', 'en' => 'Privacy', 'href' => route('legal.privacy')],
                                ['id' => 'Syarat', 'en' => 'Terms', 'href' => route('legal.terms')],
                                ['id' => 'Cookie', 'en' => 'Cookie', 'href' => route('legal.cookie')],
                            ],
                        ],
                    ];
                @endphp
                @foreach ($footerLinks as $section)
                    <div>
                        <h3 class="text-white font-semibold text-sm mb-4" data-id="{{ $section['id_cat'] }}"
                            data-en="{{ $section['en_cat'] }}">{{ $section['id_cat'] }}</h3>
                        <ul class="space-y-3">
                            @foreach ($section['links'] as $link)
                                <li>
                                    <a href="{{ $link['href'] ?? '#' }}"
                                        class="text-sm transition-colors hover:text-white" style="color: #888"
                                        data-id="{{ $link['id'] }}"
                                        data-en="{{ $link['en'] }}">{{ $link['id'] }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>

            {{-- Bottom bar --}}
            <div class="border-t pt-8 flex flex-col md:flex-row items-center justify-between gap-4"
                style="border-color: rgba(255,255,255,0.08)">
                <p class="text-xs" data-id="© {{ date('Y') }} Theday. Dibuat dengan ❤️ di Indonesia."
                    data-en="© {{ date('Y') }} Theday. Made with ❤️ in Indonesia.">© {{ date('Y') }}
                    Theday. Dibuat dengan ❤️ di Indonesia.</p>
                <div class="flex items-center gap-6">
                    <a href="{{ route('legal.privacy') }}" class="text-xs hover:text-white transition-colors"
                        data-id="Privasi" data-en="Privacy">Privasi</a>
                    <a href="{{ route('legal.terms') }}" class="text-xs hover:text-white transition-colors"
                        data-id="Ketentuan" data-en="Terms">Ketentuan</a>
                    <a href="{{ route('legal.cookie') }}" class="text-xs hover:text-white transition-colors"
                        data-id="Cookies" data-en="Cookies">Cookies</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // ============================================================
        // LANGUAGE SWITCHER
        // ============================================================
        const LANG_KEY = 'theday_lang';
        // Locale is driven by the URL (/ = id, /en = en) for SEO. Server passes it in.
        let currentLang = @json($locale);

        function applyLanguage(lang) {
            currentLang = lang;
            localStorage.setItem(LANG_KEY, lang);

            // Update all translatable elements
            document.querySelectorAll('[data-id][data-en]').forEach(el => {
                el.textContent = lang === 'en' ? el.getAttribute('data-en') : el.getAttribute('data-id');
            });

            // Update lang toggle buttons
            const isEn = lang === 'en';
            document.querySelectorAll('#lang-flag-desktop, #lang-flag-mobile').forEach(el => {
                el.textContent = isEn ? '🇬🇧' : '🇮🇩';
            });
            document.querySelectorAll('#lang-label-desktop, #lang-label-mobile').forEach(el => {
                el.textContent = isEn ? 'EN' : 'ID';
            });

            // Update html lang attribute
            document.getElementById('html-root').setAttribute('lang', lang === 'en' ? 'en' : 'id');
        }

        function toggleLanguage() {
            // Navigate to the other locale's URL so the language has its own
            // crawlable URL (and persist for in-app pages via localStorage).
            const next = currentLang === 'id' ? 'en' : 'id';
            localStorage.setItem(LANG_KEY, next);
            window.location.assign(next === 'en' ? '/en' : '/');
        }

        // Apply the server-driven language on page load
        applyLanguage(currentLang);

        // ============================================================
        // NAVBAR SCROLL
        // ============================================================
        const navbar = document.getElementById('navbar');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 20) {
                navbar.classList.add('nav-scroll');
            } else {
                navbar.classList.remove('nav-scroll');
            }
        });

        // Mobile menu toggle
        const mobileBtn = document.getElementById('mobile-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        mobileBtn.addEventListener('click', () => {
            const isNowHidden = mobileMenu.classList.toggle('hidden');
            if (!isNowHidden) {
                navbar.classList.add('nav-scroll');
            } else if (window.scrollY <= 10) {
                navbar.classList.remove('nav-scroll');
            }
        });

        // Close mobile menu on link click
        mobileMenu.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                mobileMenu.classList.add('hidden');
                if (window.scrollY <= 10) {
                    navbar.classList.remove('nav-scroll');
                }
            });
        });

        // ============================================================
        // SCROLL REVEAL
        // ============================================================
        const reveals = document.querySelectorAll('.reveal, .reveal-fade');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    const el = entry.target;
                    el.classList.add('is-in');
                    // Drop the animation lock once it finishes so :hover transforms stay free.
                    el.addEventListener('animationend', () => {
                        el.classList.remove('is-in');
                        el.classList.add('done');
                    }, { once: true });
                    observer.unobserve(el);
                }
            });
        }, {
            threshold: 0.12,
            rootMargin: '0px 0px -40px 0px'
        });

        reveals.forEach(el => observer.observe(el));

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // ============================================================
        // FEATURE TABS
        // ============================================================
        document.querySelectorAll('.feature-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                const target = tab.dataset.tab;
                document.querySelectorAll('.feature-tab').forEach(t => {
                    t.classList.toggle('is-active', t === tab);
                });
                document.querySelectorAll('.feature-panel').forEach(p => {
                    p.classList.toggle('hidden', p.dataset.panel !== target);
                });
            });
        });

        // ============================================================
        // FAQ ACCORDION (single-open)
        // ============================================================
        document.querySelectorAll('.faq-q').forEach(btn => {
            btn.addEventListener('click', () => {
                const item = btn.closest('.faq-item');
                const isOpen = item.classList.contains('is-open');
                // close all (single-open)
                document.querySelectorAll('.faq-item').forEach(i => {
                    i.classList.remove('is-open');
                    const q = i.querySelector('.faq-q');
                    if (q) q.setAttribute('aria-expanded', 'false');
                });
                // open this one if it was closed
                if (!isOpen) {
                    item.classList.add('is-open');
                    btn.setAttribute('aria-expanded', 'true');
                }
            });
        });
    </script>

</body>

</html>
