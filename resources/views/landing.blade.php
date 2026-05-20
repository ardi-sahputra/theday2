{{-- resources/views/landing.blade.php --}}
<!DOCTYPE html>
<html lang="id" id="html-root">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- ── SEO: Core ─────────────────────────────────────────────── --}}
    <title>TheDay & Beyond — Pernikahan dan Seterusnya | Aplikasi Pendamping Pasangan Indonesia</title>
    <meta name="description"
        content="Aplikasi pendamping pasangan Indonesia dari persiapan, hari pernikahan, sampai kehidupan setelahnya. Undangan digital, RSVP, anniversary, dan lainnya.">
    <meta name="keywords"
        content="undangan digital pernikahan, undangan pernikahan digital, buat undangan nikah online gratis, undangan nikah digital, digital wedding invitation Indonesia, undangan online cantik, undangan pernikahan premium">
    <meta name="author" content="TheDay">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <link rel="canonical" href="{{ url('/') }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('image/favicon.svg') }}">
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('image/favicon-96x96.png') }}">

    {{-- ── SEO: Open Graph (WhatsApp / Facebook / LinkedIn) ─────── --}}
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:site_name" content="TheDay">
    <meta property="og:title" content="TheDay & Beyond — Pernikahan dan Seterusnya">
    <meta property="og:description"
        content="Aplikasi pendamping pasangan Indonesia dari persiapan, hari pernikahan, sampai kehidupan setelahnya. Undangan digital, RSVP, anniversary, dan lainnya.">
    <meta property="og:image" content="{{ asset('image/logo.svg') }}">
    <meta property="og:image:width" content="300">
    <meta property="og:image:height" content="150">
    <meta property="og:image:alt" content="TheDay — Platform Undangan Digital Pernikahan Premium Online">
    <meta property="og:locale" content="id_ID">
    <meta property="og:locale:alternate" content="en_US">

    {{-- ── SEO: Twitter Card ─────────────────────────────────────── --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="TheDay & Beyond — Pernikahan dan Seterusnya">
    <meta name="twitter:description"
        content="Aplikasi pendamping pasangan Indonesia dari persiapan, hari pernikahan, sampai kehidupan setelahnya. Undangan digital, RSVP, anniversary, dan lainnya.">
    <meta name="twitter:image" content="{{ asset('image/logo.svg') }}">

    {{-- ── SEO: Hreflang (bilingual ID / EN) ────────────────────── --}}
    <link rel="alternate" hreflang="id" href="{{ url('/') }}">
    <link rel="alternate" hreflang="en" href="{{ url('/') }}">
    <link rel="alternate" hreflang="x-default" href="{{ url('/') }}">

    {{-- ── SEO: Sitemap Discovery ────────────────────────────────── --}}
    <link rel="sitemap" type="application/xml" title="Sitemap" href="{{ url('/sitemap.xml') }}">

    {{-- ── Fonts ─────────────────────────────────────────────────── --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,600;1,400&display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --color-primary: #92A89C;
            --color-primary-dark: #73877C;
            --color-secondary: #E8EFEC;
            --color-accent: #CCD5AE;
            --color-dark: #1E1E1E;
            --color-bg: #F5F8F6;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--color-bg);
            color: var(--color-dark);
        }

        .font-display {
            font-family: 'Playfair Display', serif;
        }

        /* Hero gradient */
        .hero-gradient {
            background: linear-gradient(135deg, #F5F8F6 0%, #EBF0ED 45%, #DDEAE4 100%);
        }

        /* Gold button */
        .btn-primary {
            background-color: var(--color-primary);
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
            background-color: var(--color-primary-dark);
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

        /* Card hover effect */
        .feature-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .feature-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
        }

        /* Template card */
        .template-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
        }

        .template-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 24px 48px rgba(0, 0, 0, 0.12);
        }

        .template-card:hover .template-overlay {
            opacity: 1;
        }

        .template-overlay {
            opacity: 0;
            transition: opacity 0.3s ease;
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

        /* Scroll reveal */
        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }

        .reveal.visible {
            opacity: 1;
            transform: translateY(0);
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

        /* FAQ accordion */
        .faq-a {
            transition: none;
        }

        /* Reduced motion */
        @media (prefers-reduced-motion: reduce) {
            .float-animation,
            .float-animation-delay,
            .float-animation-delay-2 {
                animation: none;
            }
            .reveal {
                opacity: 1;
                transform: none;
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
          "name": "TheDay",
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
          "name": "TheDay",
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
          "name": "TheDay",
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
          ],
          "aggregateRating": {
            "@type": "AggregateRating",
            "ratingValue": "4.9",
            "reviewCount": "2000",
            "bestRating": "5",
            "worstRating": "1"
          }
        },
        {
          "@type": "FAQPage",
          "mainEntity": [
            {
              "@type": "Question",
              "name": "Apakah TheDay gratis?",
              "acceptedAnswer": {
                "@type": "Answer",
                "text": "Ya, TheDay menyediakan paket gratis selamanya. Mencakup 1 undangan aktif, template dasar, konfirmasi RSVP, link undangan, peta lokasi, dan 5 foto galeri. Tidak perlu kartu kredit."
              }
            },
            {
              "@type": "Question",
              "name": "Bagaimana cara membuat undangan digital di TheDay?",
              "acceptedAnswer": {
                "@type": "Answer",
                "text": "Cukup 3 langkah mudah: (1) Pilih template pernikahan yang sesuai, (2) Isi detail acara seperti nama mempelai, tanggal, lokasi, dan foto, (3) Bagikan link undangan ke tamu via WhatsApp atau media sosial."
              }
            },
            {
              "@type": "Question",
              "name": "Apakah tamu bisa konfirmasi kehadiran (RSVP) secara online?",
              "acceptedAnswer": {
                "@type": "Answer",
                "text": "Ya, tamu dapat langsung konfirmasi hadir atau tidak dari halaman undangan digital. Rekap kehadiran tersedia secara real-time di dashboard pengelola undangan."
              }
            },
            {
              "@type": "Question",
              "name": "Berapa banyak template undangan yang tersedia?",
              "acceptedAnswer": {
                "@type": "Answer",
                "text": "TheDay menyediakan 50+ template premium undangan pernikahan dalam berbagai tema: romantis, modern, minimalis, vintage, hingga keraton. Semua template bisa dikustomisasi warna, font, dan kontennya."
              }
            },
            {
              "@type": "Question",
              "name": "Apakah undangan digital bisa dibagikan via WhatsApp?",
              "acceptedAnswer": {
                "@type": "Answer",
                "text": "Ya, setiap undangan TheDay menghasilkan satu link unik yang bisa langsung dibagikan ke semua tamu via WhatsApp, Instagram, email, atau media sosial lainnya."
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
                <img src="{{ asset('image/logo.svg') }}" alt="TheDay" class="h-10 w-auto">
            </a>

            {{-- Desktop Nav Links --}}
            <div class="hidden md:flex items-center gap-8">
                <a href="#fitur" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors"
                    data-id="Fitur" data-en="Features">Fitur</a>
                <a href="#harga" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors"
                    data-id="Harga" data-en="Pricing">Harga</a>
                <a href="#cara-kerja" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors"
                    data-id="Cara Kerja" data-en="How It Works">Cara Kerja</a>
                <a href="#faq" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors"
                    data-id="FAQ" data-en="FAQ">FAQ</a>
            </div>

            {{-- CTA + Lang switcher --}}
            <div class="hidden md:flex items-center gap-3">
                {{-- Language Toggle --}}
                <button id="lang-toggle-desktop" onclick="toggleLanguage()" class="lang-btn"
                    aria-label="Switch language">
                    <span id="lang-flag-desktop">🇮🇩</span>
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
                <button id="lang-toggle-mobile" onclick="toggleLanguage()" class="lang-btn"
                    aria-label="Switch language">
                    <span id="lang-flag-mobile">🇮🇩</span>
                    <span id="lang-label-mobile">ID</span>
                </button>
                <button id="mobile-menu-btn" class="p-2 rounded-lg text-gray-600 hover:bg-gray-100">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                <a href="#faq" class="text-sm font-medium text-gray-600" data-id="FAQ"
                    data-en="FAQ">FAQ</a>
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
        <section class="hero-gradient min-h-screen flex items-center relative overflow-hidden pt-20">
            {{-- Background decoration --}}
            <div class="absolute inset-0 dot-pattern opacity-40"></div>

            {{-- Floating decorative elements --}}
            <div class="absolute top-32 right-16 w-64 h-64 rounded-full opacity-20 float-animation"
                style="background: radial-gradient(circle, #92A89C, transparent)"></div>
            <div class="absolute bottom-32 left-12 w-48 h-48 rounded-full opacity-15 float-animation-delay"
                style="background: radial-gradient(circle, #CCD5AE, transparent)"></div>
            <div class="absolute top-1/2 left-1/2 w-32 h-32 rounded-full opacity-10 float-animation-delay-2"
                style="background: radial-gradient(circle, #92A89C, transparent)"></div>

            <div class="max-w-6xl mx-auto px-6 grid md:grid-cols-2 gap-12 items-center relative z-10 py-20">
                {{-- Hero Text --}}
                <div class="text-center md:text-left">
                    {{-- Eyebrow badge --}}
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full mb-6"
                        style="background-color: rgba(146,168,156,0.15)">
                        <span class="w-2 h-2 rounded-full" style="background-color: #92A89C"></span>
                        <span class="text-xs font-semibold" style="color: #73877C"
                            data-id="Hari Itu & Seterusnya" data-en="The Day And Beyond">Hari Itu &amp; Seterusnya</span>
                    </div>

                    {{-- Title --}}
                    <h1 class="font-display text-4xl md:text-5xl lg:text-6xl font-bold leading-tight mb-3"
                        style="color: #2C2417">
                        TheDay <span style="color: #92A89C">&amp; Beyond</span>
                    </h1>

                    {{-- Subtitle --}}
                    <h2 class="text-xl md:text-2xl text-gray-600 mb-4 font-medium"
                        data-id="Pernikahan dan seterusnya" data-en="The wedding and what's next">
                        Pernikahan dan seterusnya
                    </h2>

                    {{-- Description --}}
                    <p class="text-base md:text-lg text-gray-500 leading-relaxed mb-8 max-w-lg mx-auto md:mx-0"
                        data-id="Pendamping pasangan dari hari spesial sampai kehidupan bersama. Mulai dari undangan digital, lanjut ke persiapan dan perjalanan pernikahan kamu."
                        data-en="Companion app for couples — from the special day to your shared life. Start with digital invitations, continue with planning and married life.">
                        Pendamping pasangan dari hari spesial sampai kehidupan bersama. Mulai dari undangan digital, lanjut ke persiapan dan perjalanan pernikahan kamu.
                    </p>

                    {{-- CTAs --}}
                    <div class="flex flex-col sm:flex-row gap-3 mb-8 justify-center md:justify-start">
                        <a href="/register" class="btn-primary text-base py-3 px-6"
                            data-id="Mulai Perjalanan Bersama" data-en="Start Your Journey">
                            Mulai Perjalanan Bersama
                        </a>
                        <a href="#phase-journey" class="btn-outline text-base py-3 px-6"
                            data-id="Pelajari Lebih" data-en="Learn More">
                            Pelajari Lebih
                        </a>
                    </div>

                    {{-- Social proof inline --}}
                    <div class="flex items-center gap-4 text-sm text-gray-500 justify-center md:justify-start">
                        <span data-id="1.000+ pasangan Indonesia sudah memulai"
                            data-en="1,000+ Indonesian couples started here">
                            <strong style="color: #2C2417">1.000+</strong> pasangan Indonesia
                        </span>
                        <span class="text-gray-300">·</span>
                        <span>⭐ <strong style="color: #2C2417">4.9</strong>
                            <span data-id="dari 2.000 ulasan" data-en="from 2,000 reviews">dari 2.000 ulasan</span>
                        </span>
                    </div>
                </div>

                {{-- Hero Illustration (placeholder) --}}
                <div class="flex justify-center order-first md:order-last">
                    {{-- PLACEHOLDER: swap to <img src="/images/landing/hero-journey.webp" alt="Perjalanan pasangan dari persiapan sampai kehidupan bersama" class="w-full max-w-lg mx-auto" loading="eager"> when ready --}}
                    <div class="aspect-[4/3] w-full max-w-lg rounded-3xl flex items-center justify-center"
                        style="background: rgba(146,168,156,0.15); border: 2px dashed rgba(146,168,156,0.4)">
                        <span style="color: rgba(146,168,156,0.7); font-size: 0.875rem; font-weight: 500; text-align: center; padding: 1rem;">Ilustrasi: hero-journey<br>(couple journey path)</span>
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
                <div class="text-center max-w-2xl mx-auto mb-16">
                    <h2 class="font-display text-3xl md:text-4xl font-bold mb-4"
                        style="color: #2C2417"
                        data-id="Tiga fase, satu aplikasi" data-en="Three phases, one app">
                        Tiga fase, satu aplikasi
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
                    <div class="rounded-2xl border p-6 md:p-8 hover:shadow-lg hover:-translate-y-1 transition-all duration-200"
                        style="border-color: rgba(146,168,156,0.2); background-color: #FFFCF7">
                        {{-- PLACEHOLDER illustration --}}
                        <div class="aspect-square w-full rounded-xl flex items-center justify-center mb-5"
                            style="background: rgba(146,168,156,0.15); border: 2px dashed rgba(146,168,156,0.4)">
                            <span style="color: rgba(146,168,156,0.7); font-size: 0.75rem; font-weight: 500;">Ilustrasi: phase-1-sebelum</span>
                        </div>
                        <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold mb-3"
                            style="background: rgba(146,168,156,0.2); color: #73877C"
                            data-id="FASE 1" data-en="PHASE 1">FASE 1</span>
                        <h3 class="text-xl font-bold mb-2" style="color: #2C2417"
                            data-id="Sebelum — Persiapan" data-en="Before — Preparation">Sebelum — Persiapan</h3>
                        <p class="text-sm text-gray-500 mb-4"
                            data-id="Atur acara dengan tenang. Checklist, daftar tamu, anggaran — semua tertata."
                            data-en="Plan calmly. Checklist, guest list, budget — all organized.">
                            Atur acara dengan tenang. Checklist, daftar tamu, anggaran — semua tertata.
                        </p>
                        <ul class="space-y-2 text-sm text-gray-600 mb-4">
                            <li class="flex items-center gap-2">
                                <span style="color: #92A89C">&#10003;</span>
                                <span data-id="Checklist Persiapan" data-en="Preparation Checklist">Checklist Persiapan</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span style="color: #92A89C">&#10003;</span>
                                <span data-id="Daftar Tamu" data-en="Guest List">Daftar Tamu</span>
                            </li>
                            <li class="flex items-center gap-2 text-gray-400">
                                <span>&#8987;</span>
                                <span data-id="Anggaran Pernikahan" data-en="Wedding Budget">Anggaran Pernikahan</span>
                            </li>
                            <li class="flex items-center gap-2 text-gray-400">
                                <span>&#8987;</span>
                                <span data-id="Wedding Planner" data-en="Wedding Planner">Wedding Planner</span>
                            </li>
                        </ul>
                        <span class="inline-block px-3 py-1 rounded-full text-xs font-medium"
                            style="border: 1px solid rgba(146,168,156,0.4); color: #73877C"
                            data-id="Hadir" data-en="Available">Hadir</span>
                    </div>

                    {{-- Card 2: Hari H (FLAGSHIP — emphasized) --}}
                    <div class="rounded-2xl p-6 md:p-8 shadow-lg transition-all duration-200 md:scale-[1.02] hover:shadow-xl"
                        style="border: 2px solid #92A89C; background: white">
                        <div class="aspect-square w-full rounded-xl flex items-center justify-center mb-5"
                            style="background: rgba(146,168,156,0.15); border: 2px dashed rgba(146,168,156,0.4)">
                            <span style="color: rgba(146,168,156,0.7); font-size: 0.75rem; font-weight: 500;">Ilustrasi: phase-2-hari-h</span>
                        </div>
                        <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold text-white mb-3"
                            style="background-color: #C8A26B"
                            data-id="FASE 2 · UNGGULAN" data-en="PHASE 2 · FLAGSHIP">FASE 2 · UNGGULAN</span>
                        <h3 class="text-xl font-bold mb-2" style="color: #2C2417"
                            data-id="Hari H — Perayaan" data-en="The Day — Celebration">Hari H — Perayaan</h3>
                        <p class="text-sm text-gray-500 mb-4"
                            data-id="Wujudkan hari spesial. Undangan elegan, RSVP rapi, tamu terkelola."
                            data-en="Bring your special day to life. Elegant invitations, neat RSVP, managed guests.">
                            Wujudkan hari spesial. Undangan elegan, RSVP rapi, tamu terkelola.
                        </p>
                        <ul class="space-y-2 text-sm text-gray-600 mb-4">
                            <li class="flex items-center gap-2">
                                <span style="color: #92A89C">&#10003;</span>
                                <span data-id="Undangan Digital 30+ tema" data-en="Digital Invitation 30+ themes">Undangan Digital 30+ tema</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span style="color: #92A89C">&#10003;</span>
                                <span data-id="RSVP & Manajemen Tamu" data-en="RSVP & Guest Management">RSVP &amp; Manajemen Tamu</span>
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
                            style="background-color: #C8A26B"
                            data-id="★ Unggulan" data-en="★ Flagship">&#9733; Unggulan</span>
                    </div>

                    {{-- Card 3: Setelah (coming soon) --}}
                    <div class="rounded-2xl border p-6 md:p-8 hover:shadow-lg hover:-translate-y-1 transition-all duration-200 opacity-80"
                        style="border-color: rgba(146,168,156,0.2); background-color: rgba(255,252,247,0.6)">
                        <div class="aspect-square w-full rounded-xl flex items-center justify-center mb-5"
                            style="background: rgba(146,168,156,0.1); border: 2px dashed rgba(146,168,156,0.3)">
                            <span style="color: rgba(146,168,156,0.6); font-size: 0.75rem; font-weight: 500;">Ilustrasi: phase-3-setelah</span>
                        </div>
                        <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold mb-3"
                            style="background: rgba(146,168,156,0.15); color: #73877C"
                            data-id="FASE 3" data-en="PHASE 3">FASE 3</span>
                        <h3 class="text-xl font-bold mb-2" style="color: #2C2417"
                            data-id="Setelah — Jalani" data-en="After — Live It">Setelah — Jalani</h3>
                        <p class="text-sm text-gray-500 mb-4"
                            data-id="Pendamping setelah hari H. Anniversary, album kenangan, perjalanan bersama."
                            data-en="Companion after the day. Anniversary, memory album, journey together.">
                            Pendamping setelah hari H. Anniversary, album kenangan, perjalanan bersama.
                        </p>
                        <ul class="space-y-2 text-sm text-gray-400 mb-4">
                            <li class="flex items-center gap-2"><span>&#8987;</span> <span data-id="Anniversary Reminder" data-en="Anniversary Reminder">Anniversary Reminder</span></li>
                            <li class="flex items-center gap-2"><span>&#8987;</span> <span data-id="Newlywed Admin" data-en="Newlywed Admin">Newlywed Admin</span></li>
                            <li class="flex items-center gap-2"><span>&#8987;</span> <span data-id="Memory Album" data-en="Memory Album">Memory Album</span></li>
                            <li class="flex items-center gap-2"><span>&#8987;</span> <span data-id="Date Night Planner" data-en="Date Night Planner">Date Night Planner</span></li>
                        </ul>
                        <span class="inline-block px-3 py-1 rounded-full text-xs font-medium"
                            style="border: 1px solid #D1D5DB; color: #6B7280"
                            data-id="Segera Hadir" data-en="Coming Soon">Segera Hadir</span>
                    </div>

                </div>
            </div>
        </section>


        {{-- ============================================================ --}}
        {{-- WHAT MAKES DIFFERENT --}}
        {{-- ============================================================ --}}
        <section class="py-24" style="background-color: #F5F8F6">
            <div class="max-w-6xl mx-auto px-6">
                <div class="text-center max-w-2xl mx-auto mb-16">
                    <h2 class="font-display text-3xl md:text-4xl font-bold mb-4"
                        style="color: #2C2417"
                        data-id="Beda dari yang lain" data-en="Different from the rest">
                        Beda dari yang lain
                    </h2>
                    <p class="text-gray-500 text-lg"
                        data-id="Bukan cuma undangan. Bukan cuma planner. Pendamping seumur hidup pernikahan."
                        data-en="Not just invitations. Not just a planner. A lifelong marriage companion.">
                        Bukan cuma undangan. Bukan cuma planner. Pendamping seumur hidup pernikahan.
                    </p>
                </div>

                <div class="grid md:grid-cols-3 gap-8">
                    {{-- Lifecycle --}}
                    <div class="text-center">
                        <div class="w-32 h-32 mx-auto rounded-2xl flex items-center justify-center mb-5"
                            style="background: rgba(146,168,156,0.15); border: 2px dashed rgba(146,168,156,0.4)">
                            <span style="color: rgba(146,168,156,0.7); font-size: 0.65rem; font-weight: 500; padding: 0.5rem; text-align: center;">Ilustrasi: diff-lifecycle</span>
                        </div>
                        <h3 class="text-lg font-bold mb-2" style="color: #2C2417"
                            data-id="Pendamping Seumur Hidup" data-en="Lifelong Companion">Pendamping Seumur Hidup</h3>
                        <p class="text-sm text-gray-500"
                            data-id="Dari sebelum sampai setelah pernikahan, dalam satu aplikasi. Bukan one-shot event app."
                            data-en="From before to after the wedding, in one app. Not a one-shot event app.">
                            Dari sebelum sampai setelah pernikahan, dalam satu aplikasi. Bukan one-shot event app.
                        </p>
                    </div>

                    {{-- Indonesian --}}
                    <div class="text-center">
                        <div class="w-32 h-32 mx-auto rounded-2xl flex items-center justify-center mb-5"
                            style="background: rgba(146,168,156,0.15); border: 2px dashed rgba(146,168,156,0.4)">
                            <span style="color: rgba(146,168,156,0.7); font-size: 0.65rem; font-weight: 500; padding: 0.5rem; text-align: center;">Ilustrasi: diff-indonesian</span>
                        </div>
                        <h3 class="text-lg font-bold mb-2" style="color: #2C2417"
                            data-id="Lokal Banget" data-en="Truly Local">Lokal Banget</h3>
                        <p class="text-sm text-gray-500"
                            data-id="Dirancang untuk pasangan Indonesia. Adat, bahasa, dan kebiasaan lokal terintegrasi."
                            data-en="Built for Indonesian couples. Local customs, language, and habits integrated.">
                            Dirancang untuk pasangan Indonesia. Adat, bahasa, dan kebiasaan lokal terintegrasi.
                        </p>
                    </div>

                    {{-- Craft --}}
                    <div class="text-center">
                        <div class="w-32 h-32 mx-auto rounded-2xl flex items-center justify-center mb-5"
                            style="background: rgba(146,168,156,0.15); border: 2px dashed rgba(146,168,156,0.4)">
                            <span style="color: rgba(146,168,156,0.7); font-size: 0.65rem; font-weight: 500; padding: 0.5rem; text-align: center;">Ilustrasi: diff-craft</span>
                        </div>
                        <h3 class="text-lg font-bold mb-2" style="color: #2C2417"
                            data-id="Kualitas Craft Premium" data-en="Premium Craft Quality">Kualitas Craft Premium</h3>
                        <p class="text-sm text-gray-500"
                            data-id="Template undangan berkualitas, design taste yang dipikirkan dengan detail."
                            data-en="Quality invitation templates, design taste crafted with detail.">
                            Template undangan berkualitas, design taste yang dipikirkan dengan detail.
                        </p>
                    </div>
                </div>
            </div>
        </section>


        {{-- ============================================================ --}}
        {{-- STATS SECTION --}}
        {{-- ============================================================ --}}
        <section class="py-16 bg-white">
            <div class="max-w-6xl mx-auto px-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 reveal">
                    @php
                        $stats = [
                            [
                                'value' => '1.000+',
                                'id' => 'Pasangan Indonesia',
                                'en' => 'Indonesian Couples',
                                'icon' => '&#128145;',
                            ],
                            [
                                'value' => '4.9/5',
                                'id' => 'Rating dari 2.000+ Ulasan',
                                'en' => 'Rating from 2,000+ Reviews',
                                'icon' => '&#11088;',
                            ],
                            [
                                'value' => '32+',
                                'id' => 'Tema Undangan',
                                'en' => 'Invitation Themes',
                                'icon' => '&#127912;',
                            ],
                            [
                                'value' => '3',
                                'id' => 'Fase Perjalanan',
                                'en' => 'Journey Phases',
                                'icon' => '&#128154;',
                            ],
                        ];
                    @endphp
                    @foreach ($stats as $stat)
                        <div class="stat-card text-center">
                            <div class="text-2xl mb-2">{!! $stat['icon'] !!}</div>
                            <div class="text-2xl md:text-3xl font-bold mb-1" style="color: var(--color-dark)">
                                {{ $stat['value'] }}</div>
                            <div class="text-sm text-gray-500" data-id="{{ $stat['id'] }}"
                                data-en="{{ $stat['en'] }}">{{ $stat['id'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>


        {{-- ============================================================ --}}
        {{-- FEATURES PER PHASE (tabbed) --}}
        {{-- ============================================================ --}}
        <section id="fitur" class="py-24 bg-white">
            <div class="max-w-6xl mx-auto px-6">
                <div class="text-center max-w-2xl mx-auto mb-12">
                    <h2 class="font-display text-3xl md:text-4xl font-bold mb-4" style="color: #2C2417"
                        data-id="Apa yang bisa kamu lakukan" data-en="What you can do">Apa yang bisa kamu lakukan</h2>
                    <p class="text-gray-500 text-lg"
                        data-id="Fitur yang ada saat ini dan yang segera hadir."
                        data-en="Features available now and coming soon.">Fitur yang ada saat ini dan yang segera hadir.</p>
                </div>

                {{-- Tabs --}}
                <div class="flex justify-center gap-2 mb-10 flex-wrap">
                    <button class="feature-tab px-5 py-2.5 rounded-full text-sm font-semibold transition-all cursor-pointer" data-tab="sebelum"
                        data-id="Sebelum" data-en="Before">Sebelum</button>
                    <button class="feature-tab is-active px-5 py-2.5 rounded-full text-sm font-semibold transition-all cursor-pointer" data-tab="harih"
                        data-id="Hari H ★" data-en="The Day ★">Hari H &#9733;</button>
                    <button class="feature-tab px-5 py-2.5 rounded-full text-sm font-semibold transition-all cursor-pointer" data-tab="setelah"
                        data-id="Setelah" data-en="After">Setelah</button>
                </div>

                {{-- Tab panels --}}
                {{-- Sebelum --}}
                <div class="feature-panel hidden" data-panel="sebelum">
                    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                        @php $sebelum = [
                            ['Checklist Persiapan','Preparation Checklist','HADIR','Available','Daftar to-do otomatis sesuai tahap persiapan','Auto to-do list by preparation stage'],
                            ['Daftar Tamu','Guest List','HADIR','Available','Import dari Excel, manage list, integrasi RSVP','Import from Excel, manage list, RSVP integration'],
                            ['Anggaran Pernikahan','Wedding Budget','SEGERA','Coming Soon','Budget tracker per kategori (catering, dekorasi, dll)','Budget tracker per category (catering, decor, etc.)'],
                            ['Wedding Planner','Wedding Planner','SEGERA','Coming Soon','Timeline + vendor checklist integrated','Timeline + vendor checklist integrated'],
                        ]; @endphp
                        @foreach($sebelum as [$title,$title_en,$status,$status_en,$desc,$desc_en])
                            <div class="rounded-xl border p-5 {{ $status==='SEGERA' ? 'opacity-70' : '' }}"
                                style="border-color: rgba(146,168,156,0.15)">
                                <div class="flex items-center justify-between mb-2">
                                    <h3 class="font-semibold" style="color: #2C2417"
                                        data-id="{{ $title }}" data-en="{{ $title_en }}">{{ $title }}</h3>
                                    <span class="text-[10px] px-2 py-0.5 rounded-full font-semibold {{ $status==='HADIR' ? '' : '' }}"
                                        style="{{ $status==='HADIR' ? 'background: rgba(146,168,156,0.2); color: #73877C' : 'border: 1px solid #D1D5DB; color: #9CA3AF' }}"
                                        data-id="{{ $status }}" data-en="{{ $status_en }}">{{ $status }}</span>
                                </div>
                                <p class="text-sm text-gray-500"
                                    data-id="{{ $desc }}" data-en="{{ $desc_en }}">{{ $desc }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Hari H --}}
                <div class="feature-panel" data-panel="harih">
                    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                        @php $harih = [
                            ['Undangan Digital','Digital Invitation','HADIR','Available','30+ template, bebas ganti, mobile-friendly','30+ templates, swap freely, mobile-friendly'],
                            ['RSVP Form','RSVP Form','HADIR','Available','Konfirmasi tamu real-time + analytics','Real-time guest confirmation + analytics'],
                            ['Manajemen Tamu','Guest Management','HADIR','Available','Kelompokkan, broadcast, pantau RSVP','Group, broadcast, monitor RSVP'],
                            ['Amplop Digital','Digital Envelope','HADIR','Available','Tamu transfer langsung, transparent tracker','Guests transfer directly, transparent tracker'],
                            ['QR Check-in','QR Check-in','SEGERA','Coming Soon','Scan tamu masuk venue via QR personal','Scan guests into venue via personal QR'],
                            ['Live Streaming','Live Streaming','SEGERA','Coming Soon','Stream upacara ke tamu yang gak hadir','Stream ceremony to absent guests'],
                        ]; @endphp
                        @foreach($harih as [$title,$title_en,$status,$status_en,$desc,$desc_en])
                            <div class="rounded-xl border p-5 {{ $status==='SEGERA' ? 'opacity-70' : '' }}"
                                style="border-color: rgba(146,168,156,0.15)">
                                <div class="flex items-center justify-between mb-2">
                                    <h3 class="font-semibold" style="color: #2C2417"
                                        data-id="{{ $title }}" data-en="{{ $title_en }}">{{ $title }}</h3>
                                    <span class="text-[10px] px-2 py-0.5 rounded-full font-semibold"
                                        style="{{ $status==='HADIR' ? 'background: rgba(146,168,156,0.2); color: #73877C' : 'border: 1px solid #D1D5DB; color: #9CA3AF' }}"
                                        data-id="{{ $status }}" data-en="{{ $status_en }}">{{ $status }}</span>
                                </div>
                                <p class="text-sm text-gray-500"
                                    data-id="{{ $desc }}" data-en="{{ $desc_en }}">{{ $desc }}</p>
                            </div>
                        @endforeach
                    </div>
                    <div class="text-center mt-8">
                        <a href="/templates" class="inline-flex items-center gap-1 font-semibold hover:underline"
                            style="color: #92A89C"
                            data-id="Lihat 30+ Tema Undangan →" data-en="See 30+ Invitation Themes →">Lihat 30+ Tema Undangan &#8594;</a>
                    </div>
                </div>

                {{-- Setelah --}}
                <div class="feature-panel hidden" data-panel="setelah">
                    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                        @php $setelah = [
                            ['Anniversary Reminder','Anniversary Reminder','SEGERA','Coming Soon','Notifikasi ulang tahun pernikahan + ide kado','Wedding anniversary notification + gift ideas'],
                            ['Newlywed Admin','Newlywed Admin','SEGERA','Coming Soon','Checklist update KK, KTP, sertifikat nikah','Checklist for KK, KTP, marriage certificate update'],
                            ['Joint Budget','Joint Budget','SEGERA','Coming Soon','Anggaran rumah tangga bareng','Joint household budget tracker'],
                            ['Memory Album','Memory Album','SEGERA','Coming Soon','Galeri foto + cerita momen spesial','Photo gallery + stories of special moments'],
                            ['Date Night Planner','Date Night Planner','SEGERA','Coming Soon','Suggestion + scheduler kencan rutin','Suggestion + scheduler for regular dates'],
                        ]; @endphp
                        @foreach($setelah as [$title,$title_en,$status,$status_en,$desc,$desc_en])
                            <div class="rounded-xl border p-5 opacity-70"
                                style="border-color: rgba(146,168,156,0.15)">
                                <div class="flex items-center justify-between mb-2">
                                    <h3 class="font-semibold" style="color: #2C2417"
                                        data-id="{{ $title }}" data-en="{{ $title_en }}">{{ $title }}</h3>
                                    <span class="text-[10px] px-2 py-0.5 rounded-full"
                                        style="border: 1px solid #D1D5DB; color: #9CA3AF"
                                        data-id="{{ $status }}" data-en="{{ $status_en }}">{{ $status }}</span>
                                </div>
                                <p class="text-sm text-gray-500"
                                    data-id="{{ $desc }}" data-en="{{ $desc_en }}">{{ $desc }}</p>
                            </div>
                        @endforeach
                    </div>
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
                        data-id="3 langkah, mulai perjalanan" data-en="3 steps to start your journey">3 langkah, mulai perjalanan</h2>
                    <p class="text-gray-500 text-lg"
                        data-id="Bisa pakai dari fase mana aja, bahkan kalau kamu sudah menikah."
                        data-en="Start from any phase, even if you're already married.">
                        Bisa pakai dari fase mana aja, bahkan kalau kamu sudah menikah.
                    </p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    {{-- Step 1 --}}
                    <div class="text-center reveal">
                        <div class="w-28 h-28 mx-auto rounded-2xl flex items-center justify-center mb-4"
                            style="background: rgba(146,168,156,0.15); border: 2px dashed rgba(146,168,156,0.4)">
                            <span style="color: rgba(146,168,156,0.7); font-size: 0.65rem; padding: 0.5rem; text-align: center;">Ilustrasi: step-1-daftar</span>
                        </div>
                        <div class="w-8 h-8 mx-auto rounded-full text-white flex items-center justify-center font-bold mb-3 text-sm"
                            style="background-color: #92A89C">1</div>
                        <h3 class="text-lg font-bold mb-2" style="color: #2C2417"
                            data-id="Daftar Gratis" data-en="Sign Up Free">Daftar Gratis</h3>
                        <p class="text-sm text-gray-500"
                            data-id="Buat akun TheDay dalam 30 detik. Tanpa kartu kredit."
                            data-en="Create your TheDay account in 30 seconds. No credit card.">Buat akun TheDay dalam 30 detik. Tanpa kartu kredit.</p>
                    </div>
                    {{-- Step 2 --}}
                    <div class="text-center reveal">
                        <div class="w-28 h-28 mx-auto rounded-2xl flex items-center justify-center mb-4"
                            style="background: rgba(146,168,156,0.15); border: 2px dashed rgba(146,168,156,0.4)">
                            <span style="color: rgba(146,168,156,0.7); font-size: 0.65rem; padding: 0.5rem; text-align: center;">Ilustrasi: step-2-tanggal</span>
                        </div>
                        <div class="w-8 h-8 mx-auto rounded-full text-white flex items-center justify-center font-bold mb-3 text-sm"
                            style="background-color: #92A89C">2</div>
                        <h3 class="text-lg font-bold mb-2" style="color: #2C2417"
                            data-id="Atur Tanggal & Lokasi" data-en="Set Date & Location">Atur Tanggal &amp; Lokasi</h3>
                        <p class="text-sm text-gray-500"
                            data-id="Set tanggal pernikahan kamu — atau anniversary kalau sudah menikah."
                            data-en="Set your wedding date — or anniversary if already married.">Set tanggal pernikahan kamu — atau anniversary kalau sudah menikah.</p>
                    </div>
                    {{-- Step 3 --}}
                    <div class="text-center reveal">
                        <div class="w-28 h-28 mx-auto rounded-2xl flex items-center justify-center mb-4"
                            style="background: rgba(146,168,156,0.15); border: 2px dashed rgba(146,168,156,0.4)">
                            <span style="color: rgba(146,168,156,0.7); font-size: 0.65rem; padding: 0.5rem; text-align: center;">Ilustrasi: step-3-mulai</span>
                        </div>
                        <div class="w-8 h-8 mx-auto rounded-full text-white flex items-center justify-center font-bold mb-3 text-sm"
                            style="background-color: #92A89C">3</div>
                        <h3 class="text-lg font-bold mb-2" style="color: #2C2417"
                            data-id="Mulai dari Fase Mana Aja" data-en="Start from Any Phase">Mulai dari Fase Mana Aja</h3>
                        <p class="text-sm text-gray-500"
                            data-id="Pilih checklist persiapan, atau langsung bikin undangan, atau atur anniversary. Bebas."
                            data-en="Pick preparation checklist, or make an invitation, or set anniversary. Your choice.">Pilih checklist persiapan, atau langsung bikin undangan, atau atur anniversary. Bebas.</p>
                    </div>
                </div>
            </div>
        </section>


        {{-- ============================================================ --}}
        {{-- TESTIMONIALS --}}
        {{-- ============================================================ --}}
        <section class="py-24" style="background: linear-gradient(135deg, #F5F8F6, #EBF0ED)">
            <div class="max-w-6xl mx-auto px-6">
                <div class="text-center mb-16 reveal">
                    <p class="text-sm font-semibold tracking-widest uppercase mb-3"
                        style="color: var(--color-primary)" data-id="Cerita Mereka" data-en="Their Stories">Cerita
                        Mereka</p>
                    <h2 class="font-display text-3xl md:text-4xl font-semibold mb-4" style="color: var(--color-dark)"
                        data-id="Dipercaya Ribuan Pasangan" data-en="Trusted by Thousands of Couples">
                        Dipercaya Ribuan Pasangan
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @php
                        $testimonials = [
                            [
                                'name' => 'Dewi Rahayu',
                                'id_role' => 'Pengantin Perempuan',
                                'en_role' => 'Bride',
                                'avatar' => 'DR',
                                'color' => 'bg-rose-300',
                                'id_text' =>
                                    '"Undangan digitalnya cantik banget! Tamu-tamu kami heran karena tampilannya se-elegan ini. Proses buatnya juga gampang, cuma 30 menit sudah selesai 😍"',
                                'en_text' =>
                                    '"The digital invitation is so beautiful! Our guests were amazed at how elegant it looked. The creation process was easy too, just 30 minutes to finish 😍"',
                                'id_event' => 'Pernikahan — Januari 2025',
                                'en_event' => 'Wedding — January 2025',
                            ],
                            [
                                'name' => 'Rizky Pratama',
                                'id_role' => 'Pengantin Pria',
                                'en_role' => 'Groom',
                                'avatar' => 'RP',
                                'color' => 'bg-[#B8C7BF]',
                                'id_text' =>
                                    '"Fitur RSVP-nya sangat membantu. Kami bisa langsung tahu siapa saja yang hadir tanpa perlu hubungi satu per satu. Hemat waktu dan tenaga!"',
                                'en_text' =>
                                    '"The RSVP feature is so helpful. We could immediately know who would attend without having to contact everyone one by one. Saves time and energy!"',
                                'id_event' => 'Pernikahan — Februari 2025',
                                'en_event' => 'Wedding — February 2025',
                            ],
                            [
                                'name' => 'Sari Putri',
                                'id_role' => 'Event Organizer',
                                'en_role' => 'Event Organizer',
                                'avatar' => 'SP',
                                'color' => 'bg-[#92A89C]/50',
                                'id_text' =>
                                    '"Saya sudah pakai TheDay untuk 15 klien dan semuanya puas. Template-nya premium, sistemnya stabil, dan harganya sangat terjangkau. Highly recommended!"',
                                'en_text' =>
                                    '"I\'ve used TheDay for 15 clients and all of them are satisfied. The templates are premium, the system is stable, and the price is very affordable. Highly recommended!"',
                                'id_event' => 'EO Professional — Bandung',
                                'en_event' => 'Professional EO — Bandung',
                            ],
                        ];
                    @endphp

                    @foreach ($testimonials as $testi)
                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-50 reveal">
                            <div class="flex items-center gap-1 mb-4">
                                @for ($i = 0; $i < 5; $i++)
                                    <svg class="w-4 h-4 text-[#C8A26B]" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                @endfor
                            </div>
                            <p class="text-sm text-gray-600 leading-relaxed mb-5 italic"
                                data-id="{{ $testi['id_text'] }}" data-en="{{ $testi['en_text'] }}">
                                {{ $testi['id_text'] }}</p>
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-full {{ $testi['color'] }} flex items-center justify-center text-white text-sm font-bold flex-shrink-0">
                                    {{ $testi['avatar'] }}
                                </div>
                                <div>
                                    <p class="font-semibold text-sm" style="color: var(--color-dark)">
                                        {{ $testi['name'] }}</p>
                                    <p class="text-xs text-gray-400" data-id="{{ $testi['id_event'] }}"
                                        data-en="{{ $testi['en_event'] }}">{{ $testi['id_event'] }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>


        {{-- ============================================================ --}}
        {{-- PRICING SECTION --}}
        {{-- ============================================================ --}}
        <section id="harga" class="py-24 bg-white">
            <div class="max-w-6xl mx-auto px-6">
                <div class="text-center mb-16 reveal">
                    <p class="text-sm font-semibold tracking-widest uppercase mb-3"
                        style="color: var(--color-primary)" data-id="Harga" data-en="Pricing">Harga</p>
                    <h2 class="font-display text-3xl md:text-4xl font-semibold mb-4" style="color: var(--color-dark)"
                        data-id="Pilih Paket yang Tepat" data-en="Choose the Right Plan">
                        Pilih Paket yang Tepat
                    </h2>
                    <p class="text-gray-500 max-w-xl mx-auto"
                        data-id="Mulai gratis, upgrade kapan saja. Tidak ada biaya tersembunyi."
                        data-en="Start for free, upgrade anytime. No hidden fees.">
                        Mulai gratis, upgrade kapan saja. Tidak ada biaya tersembunyi.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-2xl mx-auto">
                    @php
                        use App\Support\PlanFormatter;

                        $premiumDiscount = isset($plans['premium']) ? $plans['premium']->currentDiscount() : null;

                        $pricingTiers = [
                            [
                                'id_name'          => 'Gratis',
                                'en_name'          => 'Free',
                                'price'            => PlanFormatter::price((int) ($plans['free']->price ?? 0)),
                                'original_price'   => null,
                                'has_discount'     => false,
                                'discount_percent' => null,
                                'discount_label'   => null,
                                'id_period'        => PlanFormatter::period((int) ($plans['free']->duration_days ?? 0), 'id'),
                                'en_period'        => PlanFormatter::period((int) ($plans['free']->duration_days ?? 0), 'en'),
                                'popular'          => false,
                                'id_features'      => $plans['free']->features ?? [],
                                'en_features'      => $plans['free']->features ?? [],
                                'id_disabled'      => ['Custom URL', 'Upload musik sendiri', 'Analitik lengkap'],
                                'en_disabled'      => ['Custom URL', 'Upload own music', 'Full analytics'],
                                'id_cta'           => 'Mulai Gratis',
                                'en_cta'           => 'Start Free',
                            ],
                            [
                                'id_name'          => 'Premium',
                                'en_name'          => 'Premium',
                                'price'            => PlanFormatter::price((int) (isset($plans['premium']) ? $plans['premium']->effectivePrice() : 49000)),
                                'original_price'   => PlanFormatter::price((int) ($plans['premium']->price ?? 49000)),
                                'has_discount'     => $premiumDiscount !== null,
                                'discount_percent' => $premiumDiscount?->percent,
                                'discount_label'   => $premiumDiscount?->label,
                                'id_period'        => PlanFormatter::period((int) ($plans['premium']->duration_days ?? 365), 'id'),
                                'en_period'        => PlanFormatter::period((int) ($plans['premium']->duration_days ?? 365), 'en'),
                                'popular'          => true,
                                'id_features'      => $plans['premium']->features ?? [],
                                'en_features'      => $plans['premium']->features ?? [],
                                'id_disabled'      => [],
                                'en_disabled'      => [],
                                'id_cta'           => 'Pilih Premium',
                                'en_cta'           => 'Choose Premium',
                            ],
                        ];
                    @endphp

                    @foreach ($pricingTiers as $plan)
                        <div
                            class="rounded-2xl p-6 border reveal flex flex-col {{ $plan['popular'] ? 'pricing-popular shadow-2xl scale-105 border-transparent' : 'border-gray-200 shadow-sm' }}">
                            @if ($plan['popular'])
                                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white bg-opacity-20 text-xs font-semibold mb-4"
                                    data-id="Paling Populer" data-en="Most Popular">
                                    Paling Populer
                                </div>
                            @endif

                            <h3 class="font-semibold text-lg mb-1 {{ $plan['popular'] ? 'text-white' : '' }}"
                                style="{{ !$plan['popular'] ? 'color: var(--color-dark)' : '' }}"
                                data-id="{{ $plan['id_name'] }}" data-en="{{ $plan['en_name'] }}">
                                {{ $plan['id_name'] }}
                            </h3>
                            <div class="mb-6">
                                @if (!empty($plan['has_discount']))
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-xs px-2 py-0.5 rounded-md font-semibold {{ $plan['popular'] ? 'bg-white/20 text-white' : 'bg-red-100 text-red-700' }}">
                                            {{ PlanFormatter::discountBadge((int) $plan['discount_percent']) }}
                                        </span>
                                        <span class="text-xs italic {{ $plan['popular'] ? 'text-white/80' : 'text-stone-500' }}" data-id="{{ $plan['discount_label'] }}" data-en="{{ $plan['discount_label'] }}">
                                            {{ $plan['discount_label'] }}
                                        </span>
                                    </div>
                                    <div class="flex items-baseline gap-2">
                                        <span class="text-3xl font-bold {{ $plan['popular'] ? 'text-white' : '' }}" style="{{ !$plan['popular'] ? 'color: var(--color-dark)' : '' }}">
                                            {{ $plan['price'] }}
                                        </span>
                                        <s class="text-sm {{ $plan['popular'] ? 'text-white/70' : 'text-stone-400' }}">{{ $plan['original_price'] }}</s>
                                    </div>
                                @else
                                    <span class="text-3xl font-bold {{ $plan['popular'] ? 'text-white' : '' }}"
                                        style="{{ !$plan['popular'] ? 'color: var(--color-dark)' : '' }}">
                                        {{ $plan['price'] }}
                                    </span>
                                @endif
                                <span
                                    class="text-sm {{ $plan['popular'] ? 'text-white text-opacity-80' : 'text-gray-400' }}"
                                    data-id="/ {{ $plan['id_period'] }}" data-en="/ {{ $plan['en_period'] }}">
                                    / {{ $plan['id_period'] }}
                                </span>
                            </div>

                            <ul class="space-y-3 mb-8 flex-1">
                                @foreach ($plan['id_features'] as $fi => $feature)
                                    <li
                                        class="flex items-center gap-2.5 text-sm {{ $plan['popular'] ? 'text-white' : 'text-gray-600' }}">
                                        <svg class="w-4 h-4 flex-shrink-0 {{ $plan['popular'] ? 'text-white' : '' }}"
                                            style="{{ !$plan['popular'] ? 'color: var(--color-primary)' : '' }}"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span data-id="{{ $feature }}"
                                            data-en="{{ $plan['en_features'][$fi] }}">{{ $feature }}</span>
                                    </li>
                                @endforeach
                                @foreach ($plan['id_disabled'] as $di => $feature)
                                    <li class="flex items-center gap-2.5 text-sm text-gray-300 line-through">
                                        <svg class="w-4 h-4 flex-shrink-0 text-gray-300" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        <span data-id="{{ $feature }}"
                                            data-en="{{ $plan['en_disabled'][$di] }}">{{ $feature }}</span>
                                    </li>
                                @endforeach
                            </ul>

                            <a href="/templates"
                                class="block text-center py-3 rounded-xl font-semibold text-sm transition-all
                   {{ $plan['popular'] ? 'bg-white hover:bg-gray-50' : 'border-2 hover:text-white hover:bg-opacity-100' }}"
                                style="{{ $plan['popular']
                                    ? 'color: var(--color-primary-dark)'
                                    : 'border-color: var(--color-primary); color: var(--color-primary); hover:background-color: var(--color-primary)' }}"
                                data-id="{{ $plan['id_cta'] }}" data-en="{{ $plan['en_cta'] }}">
                                {{ $plan['id_cta'] }}
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>


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
                <div class="text-4xl mb-6">💌</div>
                <h2 class="font-display text-3xl md:text-5xl font-semibold text-white mb-4">
                    <span data-id="Siap Membuat" data-en="Ready to Create">Siap Membuat</span><br>
                    <span style="color: var(--color-primary)" data-id="Hari Istimewamu?"
                        data-en="Your Special Day?">Hari Istimewamu?</span>
                </h2>
                <p class="text-gray-400 text-lg mb-8 max-w-xl mx-auto"
                    data-id="Bergabung dengan 10.000+ pasangan yang sudah mempercayai TheDay untuk hari paling spesial mereka."
                    data-en="Join 10,000+ couples who have trusted TheDay for their most special day.">
                    Bergabung dengan 10.000+ pasangan yang sudah mempercayai TheDay untuk hari paling spesial mereka.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="/templates" class="btn-primary text-base py-3.5 px-10">
                        <span data-id="Buat Undangan Sekarang" data-en="Create Invitation Now">Buat Undangan
                            Sekarang</span>
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </a>
                    <a href="/login" class="btn-outline text-base py-3.5 px-10"
                        style="border-color: rgba(255,255,255,0.3); color: white" data-id="Sudah punya akun? Masuk"
                        data-en="Already have an account? Login">
                        Sudah punya akun? Masuk
                    </a>
                </div>
                <p class="text-gray-500 text-sm mt-5"
                    data-id="Gratis selamanya · Tidak perlu kartu kredit · Siap dalam 5 menit"
                    data-en="Free forever · No credit card required · Ready in 5 minutes">
                    Gratis selamanya · Tidak perlu kartu kredit · Siap dalam 5 menit
                </p>
            </div>
        </section>


    </main>

    {{-- ============================================================ --}}
    {{-- FOOTER --}}
    {{-- ============================================================ --}}
    <footer style="background-color: #111; color: #888">
        <div class="max-w-6xl mx-auto px-6 py-16">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-10 mb-12">
                {{-- Brand --}}
                <div class="md:col-span-1">
                    <a href="/" class="flex items-center mb-4">
                        <img src="{{ asset('image/logo.svg') }}" alt="TheDay"
                            class="h-10 w-auto brightness-0 invert">
                    </a>
                    <p class="text-sm leading-relaxed mb-5"
                        data-id="Platform undangan pernikahan digital online premium terbaik di Indonesia."
                        data-en="Indonesia's best premium digital wedding invitation platform.">
                        Platform undangan pernikahan digital online premium terbaik di Indonesia.
                    </p>
                    <div class="flex items-center gap-3">
                        @foreach (['instagram', 'tiktok', 'whatsapp'] as $social)
                            <a href="#"
                                class="w-9 h-9 rounded-full flex items-center justify-center transition-colors"
                                style="background: rgba(255,255,255,0.08)"
                                onmouseover="this.style.background='rgba(146,168,156,0.3)'"
                                onmouseout="this.style.background='rgba(255,255,255,0.08)'">
                                <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
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
                                ['id' => 'Template', 'en' => 'Template', 'href' => '/templates'],
                                ['id' => 'Fitur', 'en' => 'Features', 'href' => '/#fitur'],
                                ['id' => 'Harga', 'en' => 'Pricing', 'href' => '/#harga'],
                                ['id' => 'Cara Kerja', 'en' => 'How It Works', 'href' => '/#cara-kerja'],
                                ['id' => 'Blog', 'en' => 'Blog', 'href' => route('blog.index')],
                            ],
                        ],
                        [
                            'id_cat' => 'Bantuan',
                            'en_cat' => 'Support',
                            'links' => [
                                ['id' => 'Pusat Bantuan', 'en' => 'Help Center', 'href' => '#'],
                                ['id' => 'Kontak', 'en' => 'Contact', 'href' => route('contact')],
                                [
                                    'id' => 'Kebijakan Privasi',
                                    'en' => 'Privacy Policy',
                                    'href' => route('legal.privacy'),
                                ],
                                [
                                    'id' => 'Syarat & Ketentuan',
                                    'en' => 'Terms & Conditions',
                                    'href' => route('legal.terms'),
                                ],
                                ['id' => 'Kebijakan Cookie', 'en' => 'Cookie Policy', 'href' => route('legal.cookie')],
                            ],
                        ],
                    ];
                @endphp
                @foreach ($footerLinks as $section)
                    <div>
                        <h4 class="text-white font-semibold text-sm mb-4" data-id="{{ $section['id_cat'] }}"
                            data-en="{{ $section['en_cat'] }}">{{ $section['id_cat'] }}</h4>
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
                <p class="text-xs" data-id="© {{ date('Y') }} TheDay. Dibuat dengan ❤️ di Indonesia."
                    data-en="© {{ date('Y') }} TheDay. Made with ❤️ in Indonesia.">© {{ date('Y') }}
                    TheDay. Dibuat dengan ❤️ di Indonesia.</p>
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
        let currentLang = localStorage.getItem(LANG_KEY) || 'id';

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
            applyLanguage(currentLang === 'id' ? 'en' : 'id');
        }

        // Apply saved language on page load
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
        const reveals = document.querySelectorAll('.reveal');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry, i) => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        entry.target.classList.add('visible');
                    }, i * 60);
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1
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
                const answer = item.querySelector('.faq-a');
                const icon = btn.querySelector('.faq-icon');
                const isOpen = !answer.classList.contains('hidden');
                // close all
                document.querySelectorAll('.faq-a').forEach(a => a.classList.add('hidden'));
                document.querySelectorAll('.faq-icon').forEach(i => i.textContent = '+');
                // open this one if it was closed
                if (!isOpen) {
                    answer.classList.remove('hidden');
                    icon.textContent = '−';
                }
            });
        });
    </script>

</body>

</html>
