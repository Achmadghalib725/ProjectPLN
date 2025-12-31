<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'PLN Dashboard') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link rel="icon" type="image/x-icon" href="{{ asset('Logo_PLN.png') }}">

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        {{-- Turbo Drive: Navigasi tanpa full page refresh --}}
        <script type="module">
            import * as Turbo from 'https://cdn.skypack.dev/@hotwired/turbo';
        </script>
    </head>
    <body class="font-sans antialiased bg-gray-50">
        <div x-data="{ sidebarOpen: window.innerWidth >= 768 }" 
             x-init="window.addEventListener('resize', () => { if(window.innerWidth < 768) sidebarOpen = false; else sidebarOpen = true; })"
             class="flex h-screen overflow-hidden">
            
            <div x-show="sidebarOpen" 
                 @click="sidebarOpen = false" 
                 x-transition:enter="transition-opacity ease-linear duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-linear duration-300"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-black/50 z-20 md:hidden glass">
            </div>

            @include('layouts.navigation')

            <div class="flex-1 flex flex-col min-w-0 overflow-hidden relative">
                
                <div class="md:hidden bg-white border-b border-gray-200 p-4 flex items-center justify-between z-10">
                    <div class="flex items-center gap-3">
                        <button @click="sidebarOpen = true" class="text-gray-500 hover:text-pln-primary focus:outline-none">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        </button>
                        <span class="font-bold text-lg text-pln-primary">E-Gudang PLN</span>
                    </div>
                    <div class="h-8 w-8 rounded-full bg-pln-primary text-white flex items-center justify-center font-bold text-xs">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                </div>

                <main class="flex-1 overflow-y-auto p-4 md:p-6 scroll-smooth">
                    {{ $slot }}
                </main>
            </div>
        </div>

        {{-- Global AJAX Navigation Script --}}
        <script>
        (function() {
            // Fungsi untuk handle AJAX request
            function ajaxNavigate(url, targetSelector, onSuccess) {
                const target = document.querySelector(targetSelector);
                if (!target) return false;

                // Loading state
                target.style.opacity = '0.5';
                target.style.pointerEvents = 'none';

                fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newContent = doc.querySelector(targetSelector);

                    if (newContent) {
                        target.innerHTML = newContent.innerHTML;

                        // Re-init Alpine.js
                        if (window.Alpine) {
                            window.Alpine.initTree(target);
                        }

                        // Update URL
                        history.pushState({}, '', url);

                        if (onSuccess) onSuccess();
                    }

                    target.style.opacity = '1';
                    target.style.pointerEvents = 'auto';
                })
                .catch(() => {
                    window.location.href = url;
                });

                return true;
            }

            // Handle pagination clicks
            function initAjaxPagination() {
                document.addEventListener('click', function(e) {
                    const link = e.target.closest('[data-ajax-pagination] a');
                    if (!link || !link.href) return;

                    const container = e.target.closest('[data-ajax-container]');
                    if (!container) return;

                    e.preventDefault();
                    e.stopPropagation();

                    const selector = '#' + container.id;
                    ajaxNavigate(link.href, selector);
                });
            }

            // Handle tab clicks
            function initAjaxTabs() {
                document.addEventListener('click', function(e) {
                    const tab = e.target.closest('[data-ajax-tab]');
                    if (!tab || !tab.href) return;

                    const contentSelector = tab.dataset.ajaxTarget;
                    if (!contentSelector) return;

                    e.preventDefault();
                    e.stopPropagation();

                    // Update active tab style
                    const tabContainer = tab.closest('[data-ajax-tabs]');
                    if (tabContainer) {
                        tabContainer.querySelectorAll('[data-ajax-tab]').forEach(t => {
                            t.classList.remove('border-[#035b71]', 'text-[#035b71]');
                            t.classList.add('border-transparent', 'text-gray-500');
                        });
                        tab.classList.remove('border-transparent', 'text-gray-500');
                        tab.classList.add('border-[#035b71]', 'text-[#035b71]');
                    }

                    ajaxNavigate(tab.href, contentSelector);
                });
            }

            // Handle AJAX form submissions
            function initAjaxForms() {
                document.addEventListener('submit', function(e) {
                    const form = e.target.closest('[data-ajax-form]');
                    if (!form) return;

                    const contentSelector = form.dataset.ajaxTarget;
                    if (!contentSelector) return;

                    e.preventDefault();
                    e.stopPropagation();

                    const formData = new FormData(form);
                    const params = new URLSearchParams(formData).toString();
                    const url = form.action + (form.action.includes('?') ? '&' : '?') + params;

                    ajaxNavigate(url, contentSelector);
                });
            }

            // Handle browser back/forward
            window.addEventListener('popstate', function() {
                window.location.reload();
            });

            // Initialize
            initAjaxPagination();
            initAjaxTabs();
            initAjaxForms();

            // Re-init after Turbo navigation
            document.addEventListener('turbo:load', function() {
                // Reset untuk page baru
            });
        })();
        </script>
    </body>
</html>