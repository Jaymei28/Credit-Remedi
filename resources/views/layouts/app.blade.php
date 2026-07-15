<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Credit Remedi')</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('icons/icon.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('icons/icon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    
    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- PWA Meta Tags -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#667eea">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="application-name" content="Credit Remedi">
    
    <!-- iOS Meta Tags -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Credit Remedi">
    <link rel="apple-touch-icon" href="/icons/icon.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/icons/icon.png">
    
    <!-- App Icons -->
    <link rel="icon" type="image/png" sizes="192x192" href="/icons/icon.png">
    <link rel="icon" type="image/png" sizes="512x512" href="/icons/icon.png">


    <style>
        /* Navbar tour styles */
        .navbar-tour-highlight {
            background-color: #ffffff !important;
            color: #2a7ae4 !important;
            border-radius: 6px;
            padding: 4px 8px;
            position: relative;
            z-index: 10000;
        }

        /* Popup and arrow */
        #navbarTourPopup {
            position: absolute;
            background: white;
            color: #333;
            padding: 16px 20px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 9999;
            max-width: 260px;
            text-align: center;
            font-size: 14px;
            display: none;
        }

        #navbarTourArrow {
            display: none;
            position: absolute;
            width: 0;
            height: 0;
            border-left: 10px solid transparent;
            border-right: 10px solid transparent;
            border-top: 10px solid white;
            z-index: 9998;
        }

        #navbarTourBackdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 9997;
        }

        #navbarTourPopup button {
            background: #2a7ae4;
            color: white;
            border: none;
            padding: 8px 12px;
            margin-top: 12px;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
    @stack('styles')
</head>
<body style="pointer-events: auto !important; overflow: auto !important;">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2">
                <img src="{{ asset('4-removebg-preview.png') }}" alt="Credit Remedi Logo" style="height: 46px;">
            </a>

            <!-- Dark Mode Toggle - Always Visible on Mobile -->
            <div class="d-flex align-items-center gap-2 order-lg-last">
                <div class="theme-toggle-wrapper">
                    <input type="checkbox" id="theme-toggle-checkbox" class="theme-toggle-checkbox">
                    <label for="theme-toggle-checkbox" class="theme-toggle-label">
                        <span class="theme-toggle-icon sun">☀️</span>
                        <span class="theme-toggle-icon moon">🌙</span>
                        <span class="theme-toggle-slider"></span>
                    </label>
                </div>
                
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar"
                    aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>

            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav me-auto mt-2 mt-lg-0">
                    <li class="nav-item mx-lg-1 {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <a href="{{ route('dashboard') }}" class="nav-link text-white px-lg-3 py-2" style="white-space: nowrap; font-size: 0.9rem; font-weight: 500;">Dashboard</a>
                    </li>
                    <li class="nav-item mx-lg-1 {{ request()->routeIs('disputes.*') ? 'active' : '' }}">
                        <a href="{{ route('disputes.index') }}" class="nav-link text-white px-lg-3 py-2" style="white-space: nowrap; font-size: 0.9rem; font-weight: 500;">
                            Disputes
                        </a>
                    </li>
                    @if (auth()->check() && (in_array(auth()->user()->plan_type, ['starter', 'standard', 'pro', 'premium']) || auth()->user()->role === 'admin'))
                        <li class="nav-item mx-lg-1 {{ request()->routeIs('credit-repair-bot') ? 'active' : '' }}">
                            <a href="{{ route('credit-repair-bot') }}" class="nav-link text-warning px-lg-3 py-2 d-flex align-items-center gap-1" style="white-space: nowrap; font-size: 0.9rem; font-weight: 500;">
                                <img src="{{ asset('AllyAI.png') }}" alt="Ally AI" style="height: 20px; width: 20px;">
                                Ally AI
                            </a>
                        </li>
                    @endif
                    @if (auth()->check() && (in_array(auth()->user()->plan_type, ['pro', 'premium']) || auth()->user()->role === 'admin'))
                        <li class="nav-item mx-lg-1 {{ request()->routeIs('fundability.*') ? 'active' : '' }}">
                            <a href="{{ route('fundability.index') }}" class="nav-link text-info px-lg-3 py-2" style="white-space: nowrap; font-size: 0.9rem; font-weight: 500;">
                                <i class="bi bi-graph-up-arrow"></i> Funding
                            </a>
                        </li>
                    @endif
                    <li class="nav-item mx-lg-1 {{ request()->routeIs('credit-vault') ? 'active' : '' }}">
                        <a href="{{ route('credit-vault') }}" class="nav-link text-white px-lg-3 py-2" style="white-space: nowrap; font-size: 0.9rem; font-weight: 500;">Vault</a>
                    </li>
                    <li class="nav-item mx-lg-1 {{ request()->routeIs('identityiq.*') ? 'active' : '' }}">
                        <a href="{{ route('identityiq.import') }}" class="nav-link text-white px-lg-3 py-2" style="white-space: nowrap; font-size: 0.9rem; font-weight: 500;">
                            IdentityIQ
                        </a>
                    </li>
                    <li class="nav-item mx-lg-1 {{ request()->routeIs('resource-center') ? 'active' : '' }}">
                        <a href="{{ route('resource-center') }}" class="nav-link text-white px-lg-3 py-2" style="white-space: nowrap; font-size: 0.9rem; font-weight: 500;">Resources</a>
                    </li>
                </ul>

                @auth
                <ul class="navbar-nav ms-auto mt-2 mt-lg-0 align-items-lg-center">
                    @if(Auth::user()->role === 'admin')
                        <li class="nav-item mx-lg-1 {{ request()->routeIs('admin.waitlist.report') ? 'active' : '' }}">
                            <a class="nav-link text-white px-lg-3 py-2" style="white-space: nowrap; font-size: 0.9rem; font-weight: 500;" href="{{ route('admin.waitlist.report') }}">Waitlist Report</a>
                        </li>
                        <li class="nav-item mx-lg-1 {{ request()->routeIs('users.*') ? 'active' : '' }}">
                            <a class="nav-link text-white px-lg-3 py-2" style="white-space: nowrap; font-size: 0.9rem; font-weight: 500;" href="{{ route('users.index') }}">Users</a>
                        </li>
                    @endif

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white px-lg-3 py-2" style="white-space: nowrap; font-size: 0.9rem; font-weight: 500;" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            @if(Auth::user()->role === 'admin') Admin @endif {{ Auth::user()->name ?? 'User' }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item small" href="/profile">Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a href="#" class="dropdown-item small text-danger" id="unsubscribe-btn">
                                    Unsubscribe
                                </a>
                            </li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item small">Logout</button>
                                </form>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item mx-0 ms-lg-3 me-4 d-flex align-items-center">
                        <a href="mailto:help@creditremedi.com?subject=Need Support - Credit Remedi" class="btn text-white" style="background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%); white-space: nowrap; font-size: 0.75rem; padding: 0.2rem 0.5rem; border-radius: 4px;">
                            <i class="bi bi-question-circle"></i> Support
                        </a>
                    </li>
                </ul>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Guided Tour Elements -->
    <div id="navbarTourBackdrop"></div>
    <div id="navbarTourPopup">
        <div id="navbarTourText">Welcome to the navbar tour.</div>
        <button onclick="nextNavbarTourStep()">Next ➡️</button>
    </div>
    <div id="navbarTourArrow"></div>

    <!-- Page Content -->
    <main class="container mt-4">
        @yield('content')
    </main>

    @stack('scripts')

    <script>
        const navbarTourSteps = [
            { selector: 'a[href="{{ route('dashboard') }}"]', text: '📊 Dashboard — your main control center.' },
            { selector: 'a[href="{{ route('disputes.index') }}"]', text: '📁 Manage your credit disputes here.' },
            @if (auth()->check() && (auth()->user()->has_paid || auth()->user()->role === 'admin'))
                { selector: 'a[href="{{ route('credit-repair-bot') }}"]', text: '🤖 Credit Remedi AI — your smart assistant for credit repair.' },
            @endif
            { selector: 'a[href="{{ route('credit-vault') }}"]', text: '🔐 Credit Vault — your go-to place for all guide videos, tutorials, and helpful links. Everything you need to understand and use the platform is stored here securely.' },
            { selector: 'a[href="{{ route('resource-center') }}"]', text: '📚 Find helpful guides and documents in the Resource Center.' }
        ];

        let navbarTourIndex = 0;

        function startNavbarTour() {
            if (localStorage.getItem('navbarTourCompleted')) return;

            navbarTourIndex = 0;
            document.getElementById('navbarTourBackdrop').style.display = 'block';
            document.getElementById('navbarTourPopup').style.display = 'block';
            document.getElementById('navbarTourArrow').style.display = 'block';

            showNavbarTourStep(navbarTourIndex);
        }

        function showNavbarTourStep(index) {
            document.querySelectorAll('.navbar-tour-highlight').forEach(el => el.classList.remove('navbar-tour-highlight'));

            const step = navbarTourSteps[index];
            const el = document.querySelector(step.selector);
            const popup = document.getElementById('navbarTourPopup');
            const arrow = document.getElementById('navbarTourArrow');

            if (!el) return;

            el.classList.add('navbar-tour-highlight');
            document.getElementById('navbarTourText').innerText = step.text;

            const rect = el.getBoundingClientRect();
            const scrollY = window.scrollY;

            popup.style.top = `${rect.bottom + scrollY + 12}px`;
            popup.style.left = `${rect.left + rect.width / 2}px`;
            popup.style.transform = `translateX(-50%)`;

            arrow.style.top = `${rect.bottom + scrollY + 2}px`;
            arrow.style.left = `${rect.left + rect.width / 2 - 10}px`;
        }

        function nextNavbarTourStep() {
            navbarTourIndex++;
            if (navbarTourIndex >= navbarTourSteps.length) {
                document.getElementById('navbarTourPopup').style.display = 'none';
                document.getElementById('navbarTourArrow').style.display = 'none';
                document.getElementById('navbarTourBackdrop').style.display = 'none';
                document.querySelectorAll('.navbar-tour-highlight').forEach(el => el.classList.remove('navbar-tour-highlight'));
                localStorage.setItem('navbarTourCompleted', 'yes');
                return;
            }
            showNavbarTourStep(navbarTourIndex);
        }

        // window.addEventListener('load', () => {
        //     setTimeout(() => {
        //         startNavbarTour();
        //     }, 1000);
        // });
    </script>

    <script>
    document.getElementById('unsubscribe-btn').addEventListener('click', function(e) {
        e.preventDefault();

        Swal.fire({
            title: 'Are you sure?',
            text: "Your subscription will be cancelled immediately.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, unsubscribe'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch("{{ route('unsubscribe') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    Swal.fire('Unsubscribed!', data.message, 'success')
                        .then(() => window.location.reload());
                })
                .catch(err => {
                    Swal.fire('Error', 'Unable to unsubscribe. Please try again.', 'error');
                });
            }
        });
    });
    </script>

    {{-- Removed Global Loader to prevent lockouts --}}

    <script>
        // CRITICAL: Force hide all loaders immediately - runs before DOM is ready
        (function() {
            // Run immediately
            function forceHideLoaders() {
                document.body.style.overflow = 'auto';
                document.body.style.pointerEvents = 'auto';
                
                // Hide all loader overlays
                const loaders = document.querySelectorAll('.loader-overlay, #navbarTourBackdrop, #globalLoader');
                loaders.forEach(function(loader) {
                    loader.style.display = 'none !important';
                    loader.style.visibility = 'hidden';
                    loader.style.opacity = '0';
                    loader.style.pointerEvents = 'none';
                });
            }
            
            // Run immediately
            forceHideLoaders();
            
            // Run when DOM is ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', forceHideLoaders);
            } else {
                forceHideLoaders();
            }
            
            // Run after a short delay as backup
            setTimeout(forceHideLoaders, 100);
            setTimeout(forceHideLoaders, 500);
        })();
    </script>

    <!-- PWA Service Worker Registration -->
    <!-- Temporarily disabled to prevent 404 errors
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(registration => {
                        console.log('ServiceWorker registered:', registration.scope);
                    })
                    .catch(err => {
                        console.log('ServiceWorker registration failed:', err);
                    });
            });
        }
    </script>
    -->

</body>
</html>
