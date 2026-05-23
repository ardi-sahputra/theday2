<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('image/favicon.svg') }}">
    <title>@yield('title') — Theday</title>
    <meta name="description" content="@yield('meta_description')">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600&family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,400&display=swap"
        rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'DM Sans', sans-serif;
            background-color: #FFFCF7;
            color: #2C2417;
        }

        .brand-font {
            font-family: 'Cormorant Garamond', serif;
        }

        .prose-legal h2 {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2C2417;
            margin-top: 2.5rem;
            margin-bottom: 0.75rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #F0E8DC;
        }

        .prose-legal h3 {
            font-size: 1rem;
            font-weight: 600;
            color: #3D2B1A;
            margin-top: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .prose-legal p {
            font-size: 0.9375rem;
            line-height: 1.75;
            color: #4A3728;
            margin-bottom: 1rem;
        }

        .prose-legal ul {
            list-style: none;
            padding: 0;
            margin-bottom: 1rem;
        }

        .prose-legal ul li {
            font-size: 0.9375rem;
            line-height: 1.75;
            color: #4A3728;
            padding-left: 1.25rem;
            position: relative;
            margin-bottom: 0.375rem;
        }

        .prose-legal ul li::before {
            content: '—';
            position: absolute;
            left: 0;
            color: #92A89C;
            font-weight: 600;
        }

        .prose-legal a {
            color: #92A89C;
            text-decoration: underline;
        }

        .toc-link {
            display: block;
            font-size: 0.8125rem;
            color: #6B5040;
            padding: 0.3rem 0.75rem;
            border-left: 2px solid transparent;
            transition: all 0.15s;
            line-height: 1.4;
        }

        .toc-link:hover,
        .toc-link.active {
            color: #92A89C;
            border-left-color: #92A89C;
        }

        .nav-scroll {
            backdrop-filter: blur(12px);
            background-color: rgba(255, 253, 247, 0.95);
            box-shadow: 0 1px 0 rgba(146, 168, 156, 0.15);
        }

        .lang-btn {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 6px 10px;
            border-radius: 8px;
            border: 1px solid rgba(146, 168, 156, 0.4);
            background: transparent;
            color: #73877C;
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.15s;
        }

        .lang-btn:hover {
            background: rgba(146, 168, 156, 0.08);
        }
    </style>
</head>

<body>

    {{-- ── Navbar ─────────────────────────────────────────────────────────── --}}
    <nav id="navbar" class="sticky top-0 z-50 nav-scroll transition-all duration-300 py-3 px-6">
        <div class="max-w-6xl mx-auto flex items-center justify-between">
            <a href="/" class="flex items-center gap-2 flex-shrink-0">
                <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background-color: #92A89C">
                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                </div>
                <span class="font-semibold text-stone-800 brand-font text-lg">Theday</span>
            </a>

            <div class="flex items-center gap-3">
                @auth
                    <a href="/dashboard"
                        class="text-sm font-medium px-4 py-2 rounded-xl text-white transition-all hover:opacity-90"
                        style="background-color: #92A89C">Dashboard</a>
                @else
                    <a href="/login"
                        class="text-sm font-medium text-stone-500 hover:text-stone-800 transition-colors px-3 py-2">Masuk</a>
                    <a href="/register"
                        class="text-sm font-semibold px-4 py-2 rounded-xl text-white transition-all hover:opacity-90"
                        style="background-color: #92A89C">Daftar Gratis</a>
                @endauth
            </div>
        </div>
    </nav>

    {{-- ── Content ─────────────────────────────────────────────────────────── --}}
    <div class="max-w-6xl mx-auto px-6 py-14">
        <div class="lg:grid lg:grid-cols-[220px_1fr] lg:gap-14">

            {{-- TOC sidebar --}}
            <aside class="hidden lg:block">
                <div class="sticky top-20">
                    <p class="text-xs font-semibold uppercase tracking-widest text-stone-400 mb-3">Daftar Isi</p>
                    <nav id="toc" class="space-y-0.5">
                        @yield('toc')
                    </nav>

                    <div class="mt-8 pt-6 border-t border-stone-100 space-y-1">
                        <p class="text-xs font-semibold uppercase tracking-widest text-stone-400 mb-2">Halaman Legal</p>
                        <a href="{{ route('legal.privacy') }}"
                            class="toc-link {{ request()->routeIs('legal.privacy') ? 'active' : '' }}">Kebijakan
                            Privasi</a>
                        <a href="{{ route('legal.terms') }}"
                            class="toc-link {{ request()->routeIs('legal.terms') ? 'active' : '' }}">Syarat &
                            Ketentuan</a>
                        <a href="{{ route('legal.cookie') }}"
                            class="toc-link {{ request()->routeIs('legal.cookie') ? 'active' : '' }}">Kebijakan
                            Cookie</a>
                    </div>
                </div>
            </aside>

            {{-- Main content --}}
            <main class="min-w-0">
                {{-- Page header --}}
                <div class="mb-10">
                    <div class="flex items-center gap-2 mb-3">
                        <a href="/"
                            class="text-xs text-stone-400 hover:text-stone-600 transition-colors">Beranda</a>
                        <span class="text-stone-300">/</span>
                        <span class="text-xs text-stone-500">@yield('breadcrumb')</span>
                    </div>
                    <h1 class="brand-font text-3xl font-semibold text-stone-900 mb-2">@yield('page_title')</h1>
                    <p class="text-sm text-stone-400">Terakhir diperbarui: @yield('last_updated')</p>
                </div>

                {{-- Mobile TOC --}}
                <details class="lg:hidden mb-8 border border-stone-200 rounded-xl overflow-hidden">
                    <summary
                        class="px-4 py-3 text-sm font-medium text-stone-600 cursor-pointer bg-stone-50 hover:bg-stone-100 transition-colors">
                        Daftar Isi
                    </summary>
                    <nav class="px-4 py-3 space-y-1">
                        @yield('toc')
                    </nav>
                </details>

                <div class="prose-legal">
                    @yield('content')
                </div>

                {{-- Bottom legal nav --}}
                <div class="mt-16 pt-8 border-t border-stone-100 flex flex-wrap gap-4 text-sm">
                    <a href="{{ route('legal.privacy') }}"
                        class="text-stone-500 hover:text-stone-800 transition-colors {{ request()->routeIs('legal.privacy') ? 'font-semibold text-stone-800' : '' }}">Kebijakan
                        Privasi</a>
                    <a href="{{ route('legal.terms') }}"
                        class="text-stone-500 hover:text-stone-800 transition-colors {{ request()->routeIs('legal.terms') ? 'font-semibold text-stone-800' : '' }}">Syarat
                        & Ketentuan</a>
                    <a href="{{ route('legal.cookie') }}"
                        class="text-stone-500 hover:text-stone-800 transition-colors {{ request()->routeIs('legal.cookie') ? 'font-semibold text-stone-800' : '' }}">Kebijakan
                        Cookie</a>
                </div>
            </main>
        </div>
    </div>

    {{-- ── Footer ─────────────────────────────────────────────────────────── --}}
    <footer style="background-color: #111; color: #888" class="mt-16">
        <div class="max-w-6xl mx-auto px-6 py-10">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <a href="/" class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-md flex items-center justify-center" style="background-color: #92A89C">
                        <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                    </div>
                    <span class="text-white text-sm font-semibold brand-font">Theday</span>
                </a>
                <p class="text-xs">© {{ date('Y') }} Theday. Dibuat dengan ❤️ di Indonesia.</p>
                <div class="flex items-center gap-5 text-xs">
                    <a href="{{ route('legal.privacy') }}" class="hover:text-white transition-colors">Privasi</a>
                    <a href="{{ route('legal.terms') }}" class="hover:text-white transition-colors">Ketentuan</a>
                    <a href="{{ route('legal.cookie') }}" class="hover:text-white transition-colors">Cookie</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // TOC active link on scroll
        const sections = document.querySelectorAll('[data-section]');
        const tocLinks = document.querySelectorAll('.toc-link[href^="#"]');

        if (sections.length && tocLinks.length) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        tocLinks.forEach(l => l.classList.remove('active'));
                        const active = document.querySelector(`.toc-link[href="#${entry.target.id}"]`);
                        if (active) active.classList.add('active');
                    }
                });
            }, {
                rootMargin: '-20% 0px -70% 0px'
            });
            sections.forEach(s => observer.observe(s));
        }
    </script>
</body>

</html>
