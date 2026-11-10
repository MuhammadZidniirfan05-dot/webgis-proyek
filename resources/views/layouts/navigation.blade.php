<nav x-data="{ open: false, scrolled: false }"
     x-init="window.addEventListener('scroll', () => scrolled = window.scrollY > 8)"
     :class="scrolled ? 'shadow-lg shadow-slate-900/10' : 'shadow-sm'"
     class="sticky top-0 z-50 bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 border-b border-white/5 transition-shadow duration-300">

    {{-- Garis aksen tipis di atas --}}
    <div class="h-0.5 bg-gradient-to-r from-teal-400 via-emerald-400 to-cyan-400"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">

            {{-- Kiri: Logo + Menu --}}
            <div class="flex items-center">
                {{-- Logo --}}
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="group flex items-center gap-2.5">
                        <div class="relative">
                            <div class="absolute inset-0 bg-teal-400/40 blur-lg rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            <div class="relative p-1.5 rounded-lg bg-gradient-to-br from-teal-400 to-emerald-500 shadow-lg shadow-teal-500/30">
                                <x-application-logo class="block h-5 w-5 fill-current text-white" />
                            </div>
                        </div>
                        <span class="hidden sm:block font-bold text-white tracking-tight text-[15px]">
                            WebGIS <span class="text-teal-400">Monitoring</span>
                        </span>
                    </a>
                </div>

                {{-- Menu Desktop --}}
                <div class="hidden sm:flex sm:items-center sm:ms-10 sm:space-x-1">
                    <a href="{{ route('dashboard') }}"
                       class="relative px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200
                              {{ request()->routeIs('dashboard')
                                  ? 'text-white bg-white/10'
                                  : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                            Dashboard
                        </span>
                        @if (request()->routeIs('dashboard'))
                            <span class="absolute left-1/2 -bottom-0.5 -translate-x-1/2 w-8 h-0.5 bg-gradient-to-r from-teal-400 to-emerald-400 rounded-full"></span>
                        @endif
                    </a>
                </div>
            </div>

            {{-- Kanan: Profil --}}
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="56">
                    <x-slot name="trigger">
                        <button class="group inline-flex items-center gap-2.5 pl-2 pr-3 py-1.5 rounded-full border border-white/10 bg-white/5 hover:bg-white/10 hover:border-teal-400/40 focus:outline-none focus:ring-2 focus:ring-teal-400/40 transition-all duration-200">
                            {{-- Avatar Inisial --}}
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-teal-400 to-emerald-500 flex items-center justify-center text-white text-xs font-bold shadow-md shadow-teal-500/30">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                            <div class="hidden md:block text-left leading-tight">
                                <div class="text-[13px] font-semibold text-white truncate max-w-[120px]">
                                    {{ Auth::user()->name }}
                                </div>
                                <div class="text-[10px] text-teal-300/70 uppercase tracking-wider">Administrator</div>
                            </div>
                            <svg class="fill-current h-3.5 w-3.5 text-slate-400 group-hover:text-teal-300 transition-colors"
                                 xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                      d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                      clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        {{-- Header dropdown --}}
                        <div class="px-4 py-3 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white">
                            <div class="text-sm font-bold text-slate-800 truncate">{{ Auth::user()->name }}</div>
                            <div class="text-xs text-slate-500 truncate mt-0.5">{{ Auth::user()->email }}</div>
                        </div>

                        <div class="py-1">
                            <x-dropdown-link :href="route('profile.edit')"
                                             class="flex items-center gap-3 !text-slate-700 hover:!bg-teal-50 hover:!text-teal-700 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                {{ __('Profile') }}
                            </x-dropdown-link>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                                 onclick="event.preventDefault(); this.closest('form').submit();"
                                                 class="flex items-center gap-3 !text-red-600 hover:!bg-red-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </div>
                    </x-slot>
                </x-dropdown>
            </div>

            {{-- Hamburger Mobile --}}
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                        class="inline-flex items-center justify-center p-2 rounded-lg text-slate-300 hover:text-white hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-teal-400/40 transition-colors duration-150">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex"
                              stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16"/>
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden"
                              stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Menu Mobile --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="sm:hidden border-t border-white/5 bg-slate-900/95 backdrop-blur-md">
        <div class="pt-2 pb-3 space-y-1 px-3">
            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                      {{ request()->routeIs('dashboard')
                          ? 'text-white bg-white/10 border-l-2 border-teal-400'
                          : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                {{ __('Dashboard') }}
            </a>
        </div>

        {{-- Profil Mobile --}}
        <div class="pt-4 pb-3 border-t border-white/5">
            <div class="px-5 flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-teal-400 to-emerald-500 flex items-center justify-center text-white text-sm font-bold shadow-md shadow-teal-500/30">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <div class="font-semibold text-sm text-white truncate">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-xs text-teal-300/70 truncate">{{ Auth::user()->email }}</div>
                </div>
            </div>

            <div class="mt-3 space-y-1 px-3">
                <a href="{{ route('profile.edit') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-300 hover:text-white hover:bg-white/5 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    {{ __('Profile') }}
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <a href="{{ route('logout') }}"
                       onclick="event.preventDefault(); this.closest('form').submit();"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-red-400 hover:text-red-300 hover:bg-red-500/10 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        {{ __('Log Out') }}
                    </a>
                </form>
            </div>
        </div>
    </div>
</nav>