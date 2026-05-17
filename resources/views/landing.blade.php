{{-- resources/views/landing.blade.php --}}
<!DOCTYPE html>
<html lang="id" id="html-root">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- ── SEO: Core ─────────────────────────────────────────────── --}}
    <title>TheDay - Undangan Digital Pernikahan Online Premium | Gratis</title>
    <meta name="description"
        content="Buat undangan digital pernikahan yang elegan dalam hitungan menit. 50+ template premium, RSVP online real-time, bagikan via WhatsApp. Mulai gratis, tanpa kartu kredit.">
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
    <meta property="og:title" content="TheDay — Undangan Digital Pernikahan Premium | Gratis">
    <meta property="og:description"
        content="Buat undangan pernikahan digital yang cantik dalam hitungan menit. 50+ template elegan, RSVP online, bagikan via WhatsApp. Mulai gratis!">
    <meta property="og:image" content="{{ asset('image/logo.svg') }}">
    <meta property="og:image:width" content="300">
    <meta property="og:image:height" content="150">
    <meta property="og:image:alt" content="TheDay — Platform Undangan Digital Pernikahan Premium Online">
    <meta property="og:locale" content="id_ID">
    <meta property="og:locale:alternate" content="en_US">

    {{-- ── SEO: Twitter Card ─────────────────────────────────────── --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="TheDay — Undangan Digital Pernikahan Premium Online">
    <meta name="twitter:description"
        content="Buat undangan pernikahan digital yang cantik dalam hitungan menit. 50+ template elegan, RSVP online, bagikan via WhatsApp.">
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
          "description": "Platform undangan digital pernikahan online terbaik di Indonesia.",
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
          "description": "Platform undangan digital pernikahan online. Buat undangan nikah cantik dalam hitungan menit dengan 50+ template elegan.",
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
                <a href="#template" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors"
                    data-id="Template" data-en="Template">Template</a>
                <a href="#harga" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors"
                    data-id="Harga" data-en="Pricing">Harga</a>
                <a href="#cara-kerja" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors"
                    data-id="Cara Kerja" data-en="How It Works">Cara Kerja</a>
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
                    <a href="/templates" class="btn-primary text-sm py-2 px-5" data-id="Buat Undangan — Gratis"
                        data-en="Create Invitation — Free">Buat Undangan — Gratis</a>
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
                <a href="#template" class="text-sm font-medium text-gray-600" data-id="Template"
                    data-en="Template">Template</a>
                <a href="#harga" class="text-sm font-medium text-gray-600" data-id="Harga"
                    data-en="Pricing">Harga</a>
                <a href="#cara-kerja" class="text-sm font-medium text-gray-600" data-id="Cara Kerja"
                    data-en="How It Works">Cara Kerja</a>
                <div class="flex gap-3 pt-2">
                    @auth
                        <a href="/dashboard" class="btn-primary text-sm py-2 px-4 flex-1 justify-center"
                            data-id="Dashboard" data-en="Dashboard">Dashboard</a>
                    @else
                        <a href="/login" class="btn-outline text-sm py-2 px-4 flex-1 justify-center" data-id="Masuk"
                            data-en="Login">Masuk</a>
                        <a href="/templates" class="btn-primary text-sm py-2 px-4 flex-1 justify-center"
                            data-id="Buat Undangan" data-en="Create Invitation">Buat Undangan</a>
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

            <div class="max-w-6xl mx-auto px-6 py-20 relative z-10">
                <div class="flex flex-col lg:flex-row items-center gap-16">

                    {{-- Hero Text --}}
                    <div class="flex-1 text-center lg:text-left">
                        {{-- Badge --}}
                        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium mb-6"
                            style="background-color: rgba(146,168,156,0.15); color: var(--color-primary-dark)">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            <span data-id="Dipercaya 10.000+ pasangan di Indonesia"
                                data-en="Trusted by 10,000+ couples in Indonesia">Dipercaya 10.000+ pasangan di
                                Indonesia</span>
                        </div>

                        <h1 class="font-display text-4xl md:text-5xl lg:text-6xl font-semibold leading-tight mb-6"
                            style="color: var(--color-dark)">
                            <span data-id="Buat Momen Spesialmu" data-en="Make Your Special Moment">Buat Momen
                                Spesialmu</span><br>
                            <span style="color: var(--color-primary)" data-id="Tak Terlupakan"
                                data-en="Unforgettable">Tak Terlupakan</span>
                        </h1>

                        <p class="text-lg text-gray-600 mb-8 max-w-xl leading-relaxed"
                            data-id="Undangan pernikahan digital yang elegan dan berkesan. Bagikan lewat WhatsApp, terima konfirmasi kehadiran secara real-time."
                            data-en="Elegant digital wedding invitations that leave a lasting impression. Share via WhatsApp, receive attendance confirmations in real-time.">
                            Undangan pernikahan digital yang elegan dan berkesan. Bagikan lewat WhatsApp, terima
                            konfirmasi kehadiran secara real-time.
                        </p>

                        <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                            <a href="/templates" class="btn-primary text-base py-3 px-8">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                </svg>
                                <span data-id="Buat Undangan Gratis" data-en="Create Free Invitation">Buat Undangan
                                    Gratis</span>
                            </a>
                            <a href="#template" class="btn-outline text-base py-3 px-8">
                                <span data-id="Lihat Template" data-en="View Templates">Lihat Template</span>
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                </svg>
                            </a>
                        </div>

                        {{-- Social proof --}}
                        <div class="mt-10 flex items-center gap-4 justify-center lg:justify-start">
                            <div class="flex -space-x-2">
                                @foreach (['bg-rose-300', 'bg-[#92A89C]/50', 'bg-emerald-300', 'bg-[#B8C7BF]', 'bg-purple-300'] as $color)
                                    <div
                                        class="w-9 h-9 rounded-full border-2 border-white {{ $color }} flex items-center justify-center text-xs font-bold text-white">
                                        {{ chr(65 + $loop->index) }}
                                    </div>
                                @endforeach
                            </div>
                            <div>
                                <div class="flex items-center gap-1">
                                    @for ($i = 0; $i < 5; $i++)
                                        <svg class="w-4 h-4 text-[#C8A26B]" fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                    @endfor
                                </div>
                                <p class="text-xs text-gray-500 mt-0.5" data-id="4.9/5 dari 2.000+ ulasan"
                                    data-en="4.9/5 from 2,000+ reviews">4.9/5 dari 2.000+ ulasan</p>
                            </div>
                        </div>
                    </div>

                    {{-- Hero Mock --}}
                    <div class="flex-1 flex justify-center items-center relative">
                        {{-- Phone mockup --}}
                        <div class="relative w-72 float-animation">
                            <div class="bg-white rounded-3xl shadow-2xl overflow-hidden border border-gray-100"
                                style="box-shadow: 0 40px 80px rgba(146,168,156,0.25)">
                                {{-- Phone notch --}}
                                <div class="bg-gray-900 h-7 flex items-center justify-center">
                                    <div class="w-20 h-4 bg-gray-800 rounded-full"></div>
                                </div>
                                {{-- Invitation preview --}}
                                <div class="relative"
                                    style="background: linear-gradient(160deg, #EBF0ED, #DDEAE4); height: 480px;">
                                    {{-- Decorative elements --}}
                                    <div
                                        class="absolute inset-0 flex flex-col items-center justify-center text-center px-6">
                                        <div class="w-16 h-px mb-4" style="background: var(--color-primary)"></div>
                                        <p class="text-xs text-gray-500 tracking-widest uppercase mb-2"
                                            data-id="Undangan Pernikahan" data-en="Wedding Invitation">Undangan
                                            Pernikahan</p>
                                        <h3 class="font-display text-3xl font-semibold mb-1"
                                            style="color: var(--color-dark)">Rina</h3>
                                        <div class="flex items-center gap-2 mb-1">
                                            <div class="w-8 h-px" style="background: var(--color-primary)"></div>
                                            <span class="text-xs" style="color: var(--color-primary)">&amp;</span>
                                            <div class="w-8 h-px" style="background: var(--color-primary)"></div>
                                        </div>
                                        <h3 class="font-display text-3xl font-semibold mb-6"
                                            style="color: var(--color-dark)">Budi</h3>

                                        <div class="bg-white bg-opacity-60 rounded-xl px-6 py-3 mb-4 w-full">
                                            <p class="text-xs text-gray-500 mb-0.5" data-id="Sabtu, 12 Juli 2025"
                                                data-en="Saturday, July 12, 2025">Sabtu, 12 Juli 2025</p>
                                            <p class="text-sm font-semibold" style="color: var(--color-dark)"
                                                data-id="09.00 WIB — Selesai" data-en="09:00 AM — Until Finish">09.00
                                                WIB — Selesai</p>
                                        </div>
                                        <div class="bg-white bg-opacity-60 rounded-xl px-6 py-3 w-full">
                                            <p class="text-xs text-gray-500 mb-0.5" data-id="Lokasi"
                                                data-en="Location">Lokasi</p>
                                            <p class="text-sm font-semibold" style="color: var(--color-dark)">Hotel
                                                Mulia Senayan</p>
                                            <p class="text-xs text-gray-500" data-id="Jakarta Selatan"
                                                data-en="South Jakarta">Jakarta Selatan</p>
                                        </div>

                                        <button class="mt-5 w-full py-2.5 rounded-xl text-sm font-semibold text-white"
                                            style="background: var(--color-primary)" data-id="Konfirmasi Kehadiran"
                                            data-en="Confirm Attendance">
                                            Konfirmasi Kehadiran
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- Floating badges --}}
                            <div
                                class="absolute -right-6 top-16 bg-white rounded-xl shadow-lg px-3 py-2 flex items-center gap-2 float-animation-delay">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center"
                                    style="background: rgba(146,168,156,0.15)">
                                    <svg class="w-4 h-4" style="color: var(--color-primary)" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-gray-800" data-id="128 Hadir"
                                        data-en="128 Attending">128 Hadir</p>
                                    <p class="text-xs text-gray-400" data-id="dari 200 tamu" data-en="of 200 guests">
                                        dari 200 tamu</p>
                                </div>
                            </div>

                            <div
                                class="absolute -left-8 bottom-24 bg-white rounded-xl shadow-lg px-3 py-2 flex items-center gap-2 float-animation-delay-2">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center bg-green-50">
                                    <svg class="w-4 h-4 text-green-500" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-gray-800" data-id="1.240 Dilihat"
                                        data-en="1,240 Viewed">1.240 Dilihat</p>
                                    <p class="text-xs text-gray-400" data-id="hari ini" data-en="today">hari ini</p>
                                </div>
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
        {{-- STATS SECTION --}}
        {{-- ============================================================ --}}
        <section class="py-16 bg-white">
            <div class="max-w-6xl mx-auto px-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 reveal">
                    @php
                        $stats = [
                            [
                                'value' => '10.000+',
                                'id' => 'Undangan Dibuat',
                                'en' => 'Invitations Created',
                                'icon' => '💌',
                            ],
                            ['value' => '500.000+', 'id' => 'Tamu Diundang', 'en' => 'Guests Invited', 'icon' => '👥'],
                            [
                                'value' => '50+',
                                'id' => 'Template Tersedia',
                                'en' => 'Templates Available',
                                'icon' => '🎨',
                            ],
                            [
                                'value' => '4.9/5',
                                'id' => 'Rating Kepuasan',
                                'en' => 'Satisfaction Rating',
                                'icon' => '⭐',
                            ],
                        ];
                    @endphp
                    @foreach ($stats as $stat)
                        <div class="stat-card text-center">
                            <div class="text-2xl mb-2">{{ $stat['icon'] }}</div>
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
        {{-- FEATURES SECTION --}}
        {{-- ============================================================ --}}
        <section id="fitur" class="py-24 bg-white">
            <div class="max-w-6xl mx-auto px-6">
                {{-- Section header --}}
                <div class="text-center mb-16 reveal">
                    <p class="text-sm font-semibold tracking-widest uppercase mb-3"
                        style="color: var(--color-primary)" data-id="Kenapa TheDay?" data-en="Why TheDay?">Kenapa
                        TheDay?</p>
                    <h2 class="font-display text-3xl md:text-4xl font-semibold mb-4" style="color: var(--color-dark)"
                        data-id="Semua yang Kamu Butuhkan" data-en="Everything You Need">
                        Semua yang Kamu Butuhkan
                    </h2>
                    <p class="text-gray-500 max-w-xl mx-auto"
                        data-id="Dari template elegan hingga manajemen tamu — satu platform untuk semua kebutuhan undangan digitalmu."
                        data-en="From elegant templates to guest management — one platform for all your digital invitation needs.">
                        Dari template elegan hingga manajemen tamu — satu platform untuk semua kebutuhan undangan
                        digitalmu.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @php
                        $features = [
                            [
                                'icon' =>
                                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>',
                                'id_title' => 'Template Cantik',
                                'en_title' => 'Beautiful Templates',
                                'id_desc' =>
                                    'Pilih dari 50+ template undangan pernikahan elegan. Semua responsif & mobile-friendly.',
                                'en_desc' =>
                                    'Choose from 50+ elegant wedding invitation templates. All responsive & mobile-friendly.',
                                'color' => 'rgba(146,168,156,0.12)',
                                'iconColor' => '#92A89C',
                            ],
                            [
                                'icon' =>
                                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>',
                                'id_title' => 'Konfirmasi Kehadiran',
                                'en_title' => 'RSVP Confirmation',
                                'id_desc' =>
                                    'Tamu bisa konfirmasi hadir/tidak langsung dari undangan. Rekap otomatis tersedia di dashboard.',
                                'en_desc' =>
                                    'Guests can confirm attendance directly from the invitation. Automatic summary available in the dashboard.',
                                'color' => 'rgba(204,213,174,0.2)',
                                'iconColor' => '#7C9E5A',
                            ],
                            [
                                'icon' =>
                                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>',
                                'id_title' => 'Bagikan via WhatsApp',
                                'en_title' => 'Share via WhatsApp',
                                'id_desc' =>
                                    'Satu link langsung bisa dibagikan ke semua tamu via WhatsApp, Instagram, atau email.',
                                'en_desc' => 'One link can be shared to all guests via WhatsApp, Instagram, or email.',
                                'color' => 'rgba(72,199,116,0.1)',
                                'iconColor' => '#25D366',
                            ],
                            [
                                'icon' =>
                                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>',
                                'id_title' => 'Analitik Real-time',
                                'en_title' => 'Real-time Analytics',
                                'id_desc' =>
                                    'Pantau berapa kali undanganmu dibuka, dari mana asalnya, dan statistik RSVP secara langsung.',
                                'en_desc' =>
                                    'Track how many times your invitation is opened, where it came from, and RSVP statistics in real time.',
                                'color' => 'rgba(59,130,246,0.1)',
                                'iconColor' => '#73877C',
                            ],
                            [
                                'icon' =>
                                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>',
                                'id_title' => 'Peta Lokasi',
                                'en_title' => 'Location Map',
                                'id_desc' =>
                                    'Tampilkan lokasi acara dengan Google Maps terintegrasi. Tamu tinggal klik untuk navigasi.',
                                'en_desc' =>
                                    'Display event location with integrated Google Maps. Guests just click for navigation.',
                                'color' => 'rgba(239,68,68,0.1)',
                                'iconColor' => '#EF4444',
                            ],
                            [
                                'icon' =>
                                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>',
                                'id_title' => 'Musik Latar',
                                'en_title' => 'Background Music',
                                'id_desc' =>
                                    'Tambahkan sentuhan romantis dengan musik latar pilihan. Upload lagu sendiri (Premium).',
                                'en_desc' =>
                                    'Add a romantic touch with your choice of background music. Upload your own song (Premium).',
                                'color' => 'rgba(168,85,247,0.1)',
                                'iconColor' => '#A855F7',
                            ],
                        ];
                    @endphp

                    @foreach ($features as $feature)
                        <div class="feature-card bg-white rounded-2xl p-6 border border-gray-100 shadow-sm reveal">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-4"
                                style="background-color: {{ $feature['color'] }}">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    style="color: {{ $feature['iconColor'] }}">
                                    {!! $feature['icon'] !!}
                                </svg>
                            </div>
                            <h3 class="font-semibold text-lg mb-2" style="color: var(--color-dark)"
                                data-id="{{ $feature['id_title'] }}" data-en="{{ $feature['en_title'] }}">
                                {{ $feature['id_title'] }}</h3>
                            <p class="text-sm text-gray-500 leading-relaxed" data-id="{{ $feature['id_desc'] }}"
                                data-en="{{ $feature['en_desc'] }}">{{ $feature['id_desc'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>


        {{-- ============================================================ --}}
        {{-- HOW IT WORKS --}}
        {{-- ============================================================ --}}
        <section id="cara-kerja" class="py-24" style="background-color: var(--color-secondary)">
            <div class="max-w-6xl mx-auto px-6">
                <div class="text-center mb-16 reveal">
                    <p class="text-sm font-semibold tracking-widest uppercase mb-3"
                        style="color: var(--color-primary)" data-id="Mudah & Cepat" data-en="Easy & Fast">Mudah &
                        Cepat</p>
                    <h2 class="font-display text-3xl md:text-4xl font-semibold mb-4" style="color: var(--color-dark)"
                        data-id="Buat Undangan dalam 3 Langkah" data-en="Create an Invitation in 3 Steps">
                        Buat Undangan dalam 3 Langkah
                    </h2>
                    <p class="text-gray-500 max-w-xl mx-auto"
                        data-id="Tidak perlu keahlian desain. Cukup isi detail acaramu, undanganmu siap dalam menit."
                        data-en="No design skills needed. Just fill in your event details, your invitation is ready in minutes.">
                        Tidak perlu keahlian desain. Cukup isi detail acaramu, undanganmu siap dalam menit.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative">
                    {{-- Connector line --}}
                    <div class="hidden md:block absolute top-10 left-1/4 right-1/4 h-0.5 z-0"
                        style="background: linear-gradient(90deg, var(--color-primary), var(--color-accent))"></div>

                    @php
                        $steps = [
                            [
                                'num' => '01',
                                'id_title' => 'Pilih Template',
                                'en_title' => 'Choose a Template',
                                'id_desc' =>
                                    'Jelajahi koleksi template undangan pernikahan premium. Pilih yang sesuai dengan tema acara nikahmu.',
                                'en_desc' =>
                                    'Browse our premium wedding invitation template collection. Pick one that matches your wedding theme.',
                                'emoji' => '🎨',
                            ],
                            [
                                'num' => '02',
                                'id_title' => 'Isi Detail Acara',
                                'en_title' => 'Fill in Event Details',
                                'id_desc' =>
                                    'Masukkan nama, tanggal, lokasi, dan foto. Semuanya bisa dikustomisasi sesuai selera.',
                                'en_desc' =>
                                    'Enter name, date, location, and photos. Everything can be customized to your liking.',
                                'emoji' => '✏️',
                            ],
                            [
                                'num' => '03',
                                'id_title' => 'Bagikan ke Tamu',
                                'en_title' => 'Share with Guests',
                                'id_desc' =>
                                    'Publikasikan dan bagikan link undangan via WhatsApp. Pantau RSVP dari dashboard.',
                                'en_desc' =>
                                    'Publish and share your invitation link via WhatsApp. Monitor RSVPs from the dashboard.',
                                'emoji' => '🚀',
                            ],
                        ];
                    @endphp

                    @foreach ($steps as $step)
                        <div class="relative z-10 flex flex-col items-center text-center reveal">
                            <div class="w-20 h-20 rounded-2xl flex items-center justify-center text-3xl mb-6 shadow-md"
                                style="background: white">
                                {{ $step['emoji'] }}
                            </div>
                            <div class="inline-flex items-center justify-center w-8 h-8 rounded-full text-sm font-bold text-white mb-4"
                                style="background: var(--color-primary)">
                                {{ $step['num'] }}
                            </div>
                            <h3 class="font-semibold text-xl mb-3" style="color: var(--color-dark)"
                                data-id="{{ $step['id_title'] }}" data-en="{{ $step['en_title'] }}">
                                {{ $step['id_title'] }}</h3>
                            <p class="text-gray-500 text-sm leading-relaxed max-w-xs"
                                data-id="{{ $step['id_desc'] }}" data-en="{{ $step['en_desc'] }}">
                                {{ $step['id_desc'] }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="text-center mt-12 reveal">
                    <a href="/templates" class="btn-primary text-base py-3 px-8">
                        <span data-id="Coba Sekarang — Gratis!" data-en="Try Now — Free!">Coba Sekarang —
                            Gratis!</span>
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </a>
                </div>
            </div>
        </section>


        {{-- ============================================================ --}}
        {{-- TEMPLATE SHOWCASE --}}
        {{-- ============================================================ --}}
        <section id="template" class="py-24 bg-white">
            <div class="max-w-6xl mx-auto px-6">
                <div class="text-center mb-16 reveal">
                    <p class="text-sm font-semibold tracking-widest uppercase mb-3"
                        style="color: var(--color-primary)" data-id="Template Pilihan" data-en="Featured Templates">
                        Template Pilihan</p>
                    <h2 class="font-display text-3xl md:text-4xl font-semibold mb-4" style="color: var(--color-dark)"
                        data-id="Desain untuk Setiap Momen" data-en="Design for Every Moment">
                        Desain untuk Setiap Momen
                    </h2>
                    <p class="text-gray-500 max-w-xl mx-auto"
                        data-id="50+ template premium siap pakai. Semua bisa dikustomisasi warna, font, dan isinya."
                        data-en="50+ premium ready-to-use templates. All customizable in color, font, and content.">
                        50+ template premium siap pakai. Semua bisa dikustomisasi warna, font, dan isinya.
                    </p>
                </div>

                {{-- Category tabs --}}
                @php
                    $tabs = [
                        ['id' => 'Semua', 'en' => 'All'],
                        ['id' => 'Pernikahan', 'en' => 'Wedding'],
                        ['id' => 'Nusantara', 'en' => 'Nusantara'],
                        ['id' => 'Romantis', 'en' => 'Romantic'],
                        ['id' => 'Modern', 'en' => 'Modern'],
                        ['id' => 'Minimalis', 'en' => 'Minimalist'],
                    ];
                @endphp
                <div class="flex items-center justify-center gap-3 mb-10 flex-wrap reveal">
                    @foreach ($tabs as $tab)
                        <button
                            class="px-5 py-2 rounded-full text-sm font-medium transition-all
                {{ $tab['id'] === 'Semua' ? 'text-white shadow-md' : 'text-gray-500 hover:text-gray-700 bg-gray-50 hover:bg-gray-100' }}"
                            style="{{ $tab['id'] === 'Semua' ? 'background-color: var(--color-primary)' : '' }}"
                            data-id="{{ $tab['id'] }}" data-en="{{ $tab['en'] }}">
                            {{ $tab['id'] }}
                        </button>
                    @endforeach
                </div>

                {{-- Template grid --}}
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-5">
                    @php
                        $templates = [
                            [
                                'id_name' => 'Bunga Abadi',
                                'en_name' => 'Eternal Flower',
                                'id_type' => 'Pernikahan',
                                'en_type' => 'Wedding',
                                'bg' => 'linear-gradient(160deg, #FFF5E6, #F9E4C8)',
                                'primary' => '#C8956C',
                            ],
                            [
                                'id_name' => 'Langit Senja',
                                'en_name' => 'Dusk Sky',
                                'id_type' => 'Romantis',
                                'en_type' => 'Romantic',
                                'bg' => 'linear-gradient(160deg, #FDE8E8, #FCCBCB)',
                                'primary' => '#E57070',
                            ],
                            [
                                'id_name' => 'Hijau Daun',
                                'en_name' => 'Leaf Green',
                                'id_type' => 'Pernikahan',
                                'en_type' => 'Wedding',
                                'bg' => 'linear-gradient(160deg, #E8F5E9, #C8E6C9)',
                                'primary' => '#66BB6A',
                            ],
                            [
                                'id_name' => 'Nusantara',
                                'en_name' => 'Nusantara',
                                'id_type' => 'Nusantara',
                                'en_type' => 'Nusantara',
                                'bg' => 'linear-gradient(160deg, #F5F0E8, #E8D5A3)',
                                'primary' => '#8B6914',
                            ],
                            [
                                'id_name' => 'Ungu Malam',
                                'en_name' => 'Night Purple',
                                'id_type' => 'Romantis',
                                'en_type' => 'Romantic',
                                'bg' => 'linear-gradient(160deg, #EDE7F6, #D1C4E9)',
                                'primary' => '#9575CD',
                            ],
                            [
                                'id_name' => 'Hitam Elegan',
                                'en_name' => 'Elegant Black',
                                'id_type' => 'Modern',
                                'en_type' => 'Modern',
                                'bg' => 'linear-gradient(160deg, #263238, #37474F)',
                                'primary' => '#CFD8DC',
                            ],
                            [
                                'id_name' => 'Ivory Klasik',
                                'en_name' => 'Classic Ivory',
                                'id_type' => 'Minimalis',
                                'en_type' => 'Minimalist',
                                'bg' => 'linear-gradient(160deg, #FDFBF7, #F5EFE6)',
                                'primary' => '#B8956A',
                            ],
                            [
                                'id_name' => 'Sage Minimalis',
                                'en_name' => 'Sage Minimal',
                                'id_type' => 'Minimalis',
                                'en_type' => 'Minimalist',
                                'bg' => 'linear-gradient(160deg, #F1F8E9, #DCEDC8)',
                                'primary' => '#8BC34A',
                            ],
                        ];
                    @endphp

                    @foreach ($templates as $i => $tmpl)
                        <div class="template-card rounded-2xl shadow-md cursor-pointer reveal"
                            style="{{ $loop->index >= 4 ? 'animation-delay: 0.15s' : '' }}">
                            <div class="relative"
                                style="background: {{ $tmpl['bg'] }}; height: 220px; border-radius: 1rem 1rem 0 0;">
                                {{-- Mini invitation preview --}}
                                <div
                                    class="absolute inset-0 flex flex-col items-center justify-center text-center px-4">
                                    <div class="w-8 h-px mb-2" style="background: {{ $tmpl['primary'] }}"></div>
                                    <p class="text-xs font-semibold mb-1" style="color: {{ $tmpl['primary'] }}"
                                        data-id="{{ $tmpl['id_type'] }}" data-en="{{ $tmpl['en_type'] }}">
                                        {{ $tmpl['id_type'] }}</p>
                                    <p class="font-display text-lg font-semibold"
                                        style="color: {{ str_contains($tmpl['bg'], '263238') ? '#FFF' : '#2D2D2D' }}">
                                        Rina & Budi
                                    </p>
                                    <div class="w-8 h-px mt-2" style="background: {{ $tmpl['primary'] }}"></div>
                                </div>
                                {{-- Hover overlay --}}
                                <div
                                    class="template-overlay absolute inset-0 bg-black bg-opacity-40 rounded-t-2xl flex items-center justify-center">
                                    <a href="/templates" class="bg-white text-sm font-semibold px-5 py-2 rounded-full"
                                        style="color: var(--color-dark)" data-id="Gunakan Template"
                                        data-en="Use Template">
                                        Gunakan Template
                                    </a>
                                </div>
                            </div>
                            <div class="p-3 border border-t-0 border-gray-100 rounded-b-2xl bg-white">
                                <p class="font-medium text-sm" style="color: var(--color-dark)"
                                    data-id="{{ $tmpl['id_name'] }}" data-en="{{ $tmpl['en_name'] }}">
                                    {{ $tmpl['id_name'] }}</p>
                                <p class="text-xs text-gray-400" data-id="{{ $tmpl['id_type'] }}"
                                    data-en="{{ $tmpl['en_type'] }}">{{ $tmpl['id_type'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="text-center mt-10 reveal">
                    <a href="/templates" class="btn-outline text-sm py-2.5 px-6" data-id="Lihat Semua Template (50+)"
                        data-en="View All Templates (50+)">
                        Lihat Semua Template (50+)
                    </a>
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
    </script>

</body>

</html>
