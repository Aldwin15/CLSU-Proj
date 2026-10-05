<!DOCTYPE html>
<html lang="en" class="h-full font-sans antialiased" :class="{ 'dark': isDarkMode, 'bg-[#0b0f17] text-slate-100': isDarkMode, 'bg-[#eceff3] text-slate-800': !isDarkMode }" x-data="prototypeApp()" x-init="initApp()" @keydown.escape.window="closeAllModals()">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CLSU BuildWeather | PPSDS Weather-Responsive Scheduling & Progress Platform</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        surface: {
                            base: '#eceff3',
                            card: '#ffffff',
                            cardMuted: '#f4f6f9',
                            border: '#e1e6ed',
                            textMuted: '#64748b',
                            textDark: '#1e293b'
                        },
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                        },
                        amber: {
                            400: '#fbbf24',
                            500: '#f59e0b',
                            600: '#d97706',
                        },
                        sky: {
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                        }
                    },
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'Inter', 'system-ui', 'sans-serif'],
                    },
                    boxShadow: {
                        'soft-card': '0 1px 3px rgba(0, 0, 0, 0.04), 0 4px 12px rgba(15, 23, 42, 0.03)',
                        'soft-card-hover': '0 4px 6px rgba(0, 0, 0, 0.03), 0 10px 20px rgba(15, 23, 42, 0.05)',
                        'sidebar': '1px 0 0 rgba(225, 230, 237, 0.9)',
                    }
                }
            }
        }
    </script>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Lucide Icons CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Alpine.js CDN -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        
        /* Custom Scrollbars */
        ::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }
        .dark ::-webkit-scrollbar-track {
            background: #0f172a;
        }
        .dark ::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 9999px;
        }
        html:not(.dark) ::-webkit-scrollbar-track {
            background: #eceff3;
        }
        html:not(.dark) ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }

        /* Dark Mode Containers */
        .dark .dribbble-sidebar {
            background: #0f1523;
            border-right: 1px solid rgba(255, 255, 255, 0.07);
        }
        .dark .dribbble-navbar {
            background: rgba(15, 21, 35, 0.85);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.07);
        }
        .dark .dribbble-card {
            background: #131a2c;
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 1.25rem;
        }

        /* Light Mode Soft Contrast Containers */
        html:not(.dark) .dribbble-sidebar {
            background: #ffffff;
            border-right: 1px solid #e2e8f0;
        }
        html:not(.dark) .dribbble-navbar {
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(14px);
            border-bottom: 1px solid #e2e8f0;
        }
        html:not(.dark) .dribbble-card {
            background: #ffffff;
            border: 1px solid #e6ebf1;
            border-radius: 1.25rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02), 0 4px 12px rgba(15, 23, 42, 0.03);
        }
        
        /* Gantt Grid Line Guides */
        .gantt-col-highlight {
            background-color: rgba(99, 102, 241, 0.08) !important;
            border-left: 1px dashed rgba(99, 102, 241, 0.4) !important;
            border-right: 1px dashed rgba(99, 102, 241, 0.4) !important;
        }
        .dark .gantt-col-highlight {
            background-color: rgba(99, 102, 241, 0.15) !important;
            border-left: 1px dashed rgba(129, 140, 248, 0.5) !important;
            border-right: 1px dashed rgba(129, 140, 248, 0.5) !important;
        }

        /* 3-Shade Progress Styles */
        .progress-shade-planned {
            background-color: #cbd5e1;
        }
        .dark .progress-shade-planned {
            background-color: #334155;
        }

        .progress-shade-actual {
            background-color: #4f46e5;
        }
        .dark .progress-shade-actual {
            background-color: #6366f1;
        }

        .progress-shade-recovered {
            background-color: #10b981;
        }
        .dark .progress-shade-recovered {
            background-color: #34d399;
        }
    </style>
</head>
<body class="h-full overflow-hidden flex flex-col selection:bg-indigo-500 selection:text-white transition-colors duration-200">

    <!-- Toast Notification Container -->
    <div class="fixed top-4 right-4 left-4 sm:left-auto sm:right-5 sm:top-5 z-[9999] flex flex-col gap-2.5 pointer-events-none">
        <template x-for="toast in toasts" :key="toast.id">
            <div x-show="toast.visible"
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="opacity-0 translate-y-[-20px] scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-90"
                 class="pointer-events-auto flex items-start gap-3 p-3.5 sm:p-4 rounded-2xl shadow-xl border w-full sm:max-w-md sm:w-96 backdrop-blur-xl"
                 :class="{
                    'border-emerald-500/30 bg-[#131a2c]/95 text-emerald-300': toast.type === 'success' && isDarkMode,
                    'border-emerald-200 bg-white/95 text-emerald-800 shadow-emerald-500/5': toast.type === 'success' && !isDarkMode,
                    'border-amber-500/30 bg-[#131a2c]/95 text-amber-300': toast.type === 'warning' && isDarkMode,
                    'border-amber-200 bg-white/95 text-amber-800 shadow-amber-500/5': toast.type === 'warning' && !isDarkMode,
                    'border-rose-500/30 bg-[#131a2c]/95 text-rose-300': toast.type === 'error' && isDarkMode,
                    'border-rose-200 bg-white/95 text-rose-800 shadow-rose-500/5': toast.type === 'error' && !isDarkMode,
                    'border-indigo-500/30 bg-[#131a2c]/95 text-indigo-300': toast.type === 'info' && isDarkMode,
                    'border-indigo-200 bg-white/95 text-indigo-800 shadow-indigo-500/5': toast.type === 'info' && !isDarkMode
                 }">
                <div class="p-1.5 rounded-xl shrink-0" :class="{
                    'bg-emerald-500/10 text-emerald-500': toast.type === 'success',
                    'bg-amber-500/10 text-amber-500': toast.type === 'warning',
                    'bg-rose-500/10 text-rose-500': toast.type === 'error',
                    'bg-indigo-500/10 text-indigo-500': toast.type === 'info'
                }">
                    <i :data-lucide="toast.icon || 'bell'" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider opacity-75" x-text="toast.title"></p>
                    <p class="text-xs sm:text-sm font-medium mt-0.5 leading-snug" :class="isDarkMode ? 'text-slate-200' : 'text-slate-700'" x-text="toast.message"></p>
                </div>
                <button @click="removeToast(toast.id)" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors p-1">
                    <i data-lucide="x" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                </button>
            </div>
        </template>
    </div>

    <!-- MAIN APP CONTAINER -->
    <div class="flex h-full w-full overflow-hidden relative">
        
        <!-- MOBILE BACKDROP -->
        <div x-show="sidebarOpen" 
             @click="sidebarOpen = false"
             x-cloak
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm z-40 lg:hidden">
        </div>

        <!-- SIDEBAR NAVIGATION -->
        <aside class="fixed inset-y-0 left-0 z-50 w-72 dribbble-sidebar flex flex-col justify-between shrink-0 transition-transform duration-300 ease-in-out lg:static lg:translate-x-0"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
            <div>
                <!-- Brand Header -->
                <div class="h-16 sm:h-20 px-6 flex items-center justify-between border-b border-slate-100 dark:border-slate-800/80">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-sky-500 p-0.5 shadow-md shadow-indigo-500/20 flex items-center justify-center">
                            <i data-lucide="building-2" class="w-5 h-5 text-white"></i>
                        </div>
                        <div>
                            <h1 class="text-sm sm:text-base font-bold tracking-tight text-slate-900 dark:text-white flex items-center gap-1.5">
                                CLSU BuildWeather <span class="px-1.5 py-0.5 text-[9px] sm:text-[10px] font-semibold tracking-wider rounded-md bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-200/60 dark:border-indigo-500/20">PPSDS</span>
                            </h1>
                            <p class="text-[11px] text-slate-400 font-medium">Vertical Construction DSS</p>
                        </div>
                    </div>
                    <!-- Mobile Close -->
                    <button @click="sidebarOpen = false" class="lg:hidden p-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-slate-800">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <!-- Project Selector Dropdown -->
                <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800/60">
                    <div class="flex items-center justify-between mb-1.5 px-1">
                        <label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Active Project</label>
                        <button @click="openNewProjectModal()" class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
                            <span>+ New Project</span>
                        </button>
                    </div>
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" 
                                class="w-full flex items-center justify-between gap-2 px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/70 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200/80 dark:border-slate-700/60 transition text-left text-xs sm:text-sm text-slate-800 dark:text-slate-200">
                            <div class="flex items-center gap-2 truncate">
                                <div class="w-2 h-2 rounded-full bg-emerald-500 dark:bg-emerald-400 animate-pulse shrink-0"></div>
                                <span class="font-medium truncate" x-text="currentProject.name"></span>
                            </div>
                            <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400 shrink-0 transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                        </button>
                        
                        <div x-show="open" @click.away="open = false" x-cloak
                             class="absolute top-full left-0 right-0 mt-1.5 py-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/80 rounded-2xl shadow-xl z-50 overflow-hidden">
                            <div class="px-3.5 py-1 text-[10px] font-bold uppercase text-slate-400">Switch Workspace</div>
                            <template x-for="p in projects" :key="p.id">
                                <button @click="switchProject(p); open = false; if(window.innerWidth < 1024) sidebarOpen = false;"
                                        class="w-full px-3.5 py-2 text-left text-xs hover:bg-indigo-50 dark:hover:bg-indigo-500/10 hover:text-indigo-600 dark:hover:text-indigo-400 flex items-center justify-between transition-colors"
                                        :class="{'text-indigo-600 dark:text-indigo-400 font-bold bg-indigo-50/50 dark:bg-indigo-500/5': currentProject.id === p.id, 'text-slate-700 dark:text-slate-300': currentProject.id !== p.id}">
                                    <span x-text="p.name" class="truncate"></span>
                                    <span class="text-[10px] text-slate-400 px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 shrink-0" x-text="p.code"></span>
                                </button>
                            </template>
                            <div class="border-t border-slate-100 dark:border-slate-800 mt-1 pt-1 px-2">
                                <button @click="openProjectManagementModal(); open = false;" class="w-full px-2 py-1.5 text-left text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 rounded-lg flex items-center justify-between">
                                    <span>Manage All Projects</span>
                                    <i data-lucide="arrow-right" class="w-3 h-3"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Links -->
                <nav class="p-4 space-y-1.5">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 px-3 py-1">Overview</p>
                    
                    <!-- Overview Dashboard -->
                    <button @click="switchTab('dashboard')"
                            class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-medium transition-all group"
                            :class="activeTab === 'dashboard' ? 'bg-indigo-600 text-white shadow-sm font-semibold' : 'text-slate-700 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60'">
                        <svg class="w-4 h-4 shrink-0 transition-transform group-hover:scale-110" :class="activeTab === 'dashboard' ? 'text-white' : 'text-slate-500 dark:text-slate-400 group-hover:text-indigo-600 dark:group-hover:text-white'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="7" height="9" x="3" y="3" rx="1"/>
                            <rect width="7" height="5" x="14" y="3" rx="1"/>
                            <rect width="7" height="9" x="14" y="12" rx="1"/>
                            <rect width="7" height="5" x="3" y="16" rx="1"/>
                        </svg>
                        <span>Dashboard</span>
                    </button>

                    <!-- Scheduling & Gantt (Moved directly below Dashboard) -->
                    <button @click="switchTab('scheduling')"
                            class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-medium transition-all group"
                            :class="activeTab === 'scheduling' ? 'bg-indigo-600 text-white shadow-sm font-semibold' : 'text-slate-700 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60'">
                        <svg class="w-4 h-4 shrink-0 transition-transform group-hover:scale-110" :class="activeTab === 'scheduling' ? 'text-white' : 'text-slate-500 dark:text-slate-400 group-hover:text-indigo-600 dark:group-hover:text-white'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="18" height="18" x="3" y="4" rx="2"/>
                            <path d="M16 2v4"/>
                            <path d="M8 2v4"/>
                            <path d="M3 10h18"/>
                            <path d="M8 14h.01"/>
                            <path d="M12 14h.01"/>
                            <path d="M16 14h.01"/>
                            <path d="M8 18h.01"/>
                            <path d="M12 18h.01"/>
                            <path d="M16 18h.01"/>
                        </svg>
                        <span class="flex-1 text-left">Weather Gantt</span>
                        <span class="px-1.5 py-0.5 text-[9px] sm:text-[10px] rounded-md bg-amber-500/10 text-amber-700 dark:text-amber-300 font-bold border border-amber-500/20"
                              :class="activeTab === 'scheduling' ? 'bg-white/20 text-white border-transparent' : ''"
                              x-text="countActiveAlerts() + ' Alert'"></span>
                    </button>

                    <!-- Projects Management Tab -->
                    <button @click="switchTab('projects')"
                            class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-medium transition-all group"
                            :class="activeTab === 'projects' ? 'bg-indigo-600 text-white shadow-sm font-semibold' : 'text-slate-700 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60'">
                        <svg class="w-4 h-4 shrink-0 transition-transform group-hover:scale-110" :class="activeTab === 'projects' ? 'text-white' : 'text-slate-500 dark:text-slate-400 group-hover:text-indigo-600 dark:group-hover:text-white'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 20a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.69-.9L9.6 3.9A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2Z"/>
                        </svg>
                        <span class="flex-1 text-left">Projects</span>
                        <span class="px-1.5 py-0.5 text-[9px] sm:text-[10px] rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-bold"
                              :class="activeTab === 'projects' ? 'bg-white/20 text-white' : ''"
                              x-text="projects.length"></span>
                    </button>

                    <!-- Contractor Companies Tab -->
                    <button x-show="canAccessTab('companies')" @click="switchTab('companies')"
                            class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-medium transition-all group"
                            :class="activeTab === 'companies' ? 'bg-indigo-600 text-white shadow-sm font-semibold' : 'text-slate-700 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60'">
                        <svg class="w-4 h-4 shrink-0 transition-transform group-hover:scale-110" :class="activeTab === 'companies' ? 'text-white' : 'text-slate-500 dark:text-slate-400 group-hover:text-indigo-600 dark:group-hover:text-white'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 21h18"/>
                            <path d="M5 21V7l8-4v18"/>
                            <path d="M19 21V11l-6-4"/>
                            <path d="M9 9h1"/>
                            <path d="M9 13h1"/>
                            <path d="M9 17h1"/>
                        </svg>
                        <span class="flex-1 text-left">Contractor Companies</span>
                        <span class="px-1.5 py-0.5 text-[9px] sm:text-[10px] rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-bold"
                              :class="activeTab === 'companies' ? 'bg-white/20 text-white' : ''"
                              x-text="companies.length"></span>
                    </button>

                    <!-- Construction Activities Database -->
                    <button x-show="canAccessTab('activity-library')" @click="switchTab('activity-library')"
                            class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-medium transition-all group"
                            :class="activeTab === 'activity-library' ? 'bg-indigo-600 text-white shadow-sm font-semibold' : 'text-slate-700 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60'">
                        <svg class="w-4 h-4 shrink-0 transition-transform group-hover:scale-110" :class="activeTab === 'activity-library' ? 'text-white' : 'text-slate-500 dark:text-slate-400 group-hover:text-indigo-600 dark:group-hover:text-white'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                        </svg>
                        <span class="flex-1 text-left">Construction Activities</span>
                        <span class="px-1.5 py-0.5 text-[9px] sm:text-[10px] rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-bold"
                              :class="activeTab === 'activity-library' ? 'bg-white/20 text-white' : ''"
                              x-text="masterActivities.length"></span>
                    </button>

                    <!-- Progress Monitoring -->
                    <button @click="switchTab('progress')"
                            class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-medium transition-all group"
                            :class="activeTab === 'progress' ? 'bg-indigo-600 text-white shadow-sm font-semibold' : 'text-slate-700 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60'">
                        <svg class="w-4 h-4 shrink-0 transition-transform group-hover:scale-110" :class="activeTab === 'progress' ? 'text-white' : 'text-slate-500 dark:text-slate-400 group-hover:text-indigo-600 dark:group-hover:text-white'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/>
                            <polyline points="16 7 22 7 22 13"/>
                        </svg>
                        <span class="flex-1 text-left">Progress Monitor</span>
                    </button>

                    <p x-show="canAccessTab('companies') || canAccessTab('weather-config') || canAccessTab('audit') || canAccessTab('users')" class="text-[10px] font-bold uppercase tracking-wider text-slate-400 px-3 pt-3 pb-1">System & Administration</p>

                    <!-- Weather Configuration -->
                    <button x-show="canAccessTab('weather-config')" @click="switchTab('weather-config')"
                            class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-medium transition-all group"
                            :class="activeTab === 'weather-config' ? 'bg-indigo-600 text-white shadow-sm font-semibold' : 'text-slate-700 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60'">
                        <svg class="w-4 h-4 shrink-0 transition-transform group-hover:scale-110" :class="activeTab === 'weather-config' ? 'text-white' : 'text-slate-500 dark:text-slate-400 group-hover:text-indigo-600 dark:group-hover:text-white'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2v2"/>
                            <path d="m4.93 4.93 1.41 1.41"/>
                            <path d="M20 12h2"/>
                            <path d="m19.07 4.93-1.41 1.41"/>
                            <path d="M15.947 12.65a4 4 0 0 0-5.925-4.128"/>
                            <path d="M13 22H7a5 5 0 1 1 4.9-6H13a3 3 0 0 1 0 6Z"/>
                        </svg>
                        <span>Weather & Rules</span>
                    </button>

                    <!-- Activity / Audit Logs -->
                    <button x-show="canAccessTab('audit')" @click="switchTab('audit')"
                            class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-medium transition-all group"
                            :class="activeTab === 'audit' ? 'bg-indigo-600 text-white shadow-sm font-semibold' : 'text-slate-700 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60'">
                        <svg class="w-4 h-4 shrink-0 transition-transform group-hover:scale-110" :class="activeTab === 'audit' ? 'text-white' : 'text-slate-500 dark:text-slate-400 group-hover:text-indigo-600 dark:group-hover:text-white'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                            <path d="M3 3v5h5"/>
                            <path d="M12 7v5l4 2"/>
                        </svg>
                        <span class="flex-1 text-left">Activity / Audit Logs</span>
                        <span class="w-2 h-2 rounded-full" :class="activeTab === 'audit' ? 'bg-white' : 'bg-indigo-500'"></span>
                    </button>

                    <!-- Users Management Tab -->
                    <button x-show="canAccessTab('users')" @click="switchTab('users')"
                            class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-medium transition-all group"
                            :class="activeTab === 'users' ? 'bg-indigo-600 text-white shadow-sm font-semibold' : 'text-slate-700 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60'">
                        <svg class="w-4 h-4 shrink-0 transition-transform group-hover:scale-110" :class="activeTab === 'users' ? 'text-white' : 'text-slate-500 dark:text-slate-400 group-hover:text-indigo-600 dark:group-hover:text-white'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                        <span class="flex-1 text-left">Users & Roles</span>
                        <span class="px-1.5 py-0.5 text-[9px] sm:text-[10px] rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-bold"
                              :class="activeTab === 'users' ? 'bg-white/20 text-white' : ''"
                              x-text="users.length"></span>
                    </button>
                </nav>
            </div>

            <!-- Sidebar Footer -->
            <div class="p-4 m-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/70 dark:border-slate-700/60 text-xs">
                <div class="flex items-center gap-2 mb-1 text-indigo-600 dark:text-indigo-400 font-semibold text-xs">
                    <i data-lucide="graduation-cap" class="w-3.5 h-3.5"></i>
                    <span>CLSU Thesis Project</span>
                </div>
                <p class="text-slate-500 dark:text-slate-400 text-[10px] leading-relaxed">
                    PPSDS weather-responsive scheduling & progress monitoring decision support.
                </p>
            </div>
        </aside>

        <!-- RIGHT CONTENT AREA -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            
            <!-- TOP NAVBAR -->
            <header class="h-16 sm:h-20 dribbble-navbar px-3 sm:px-8 flex items-center justify-between shrink-0 z-20 gap-2">
                
                <!-- Left: Sidebar toggle + Desktop Weather Indicator -->
                <div class="flex items-center gap-2 sm:gap-4 min-w-0">
                    <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 shrink-0">
                        <i data-lucide="menu" class="w-5 h-5"></i>
                    </button>

                    <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-100/80 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-700/60 text-xs truncate">
                        <i data-lucide="map-pin" class="w-3.5 h-3.5 text-rose-500 shrink-0"></i>
                        <span class="font-semibold text-slate-700 dark:text-slate-200 truncate" x-text="currentProject.location"></span>
                        <span class="text-slate-300 dark:text-slate-600">|</span>
                        <div class="flex items-center gap-1.5 font-semibold shrink-0" :class="selectedWeatherSource === 'pagasa' ? 'text-rose-500 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400'">
                            <span class="w-1.5 h-1.5 rounded-full" :class="selectedWeatherSource === 'pagasa' ? 'bg-rose-500' : 'bg-emerald-500 animate-pulse'"></span>
                            <span class="text-[11px] sm:text-xs" x-text="weatherProvider"></span>
                        </div>
                    </div>

                    <!-- Presentation & Scenario Simulation Menu (Desktop Only) -->
                    <div class="relative hidden md:block" x-data="{ demoMenuOpen: false }">
                        <button @click="demoMenuOpen = !demoMenuOpen" 
                                class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-gradient-to-r from-amber-500/10 via-indigo-500/10 to-sky-500/10 hover:from-amber-500/20 hover:to-indigo-500/20 text-slate-800 dark:text-slate-200 border border-indigo-200/80 dark:border-indigo-500/30 text-xs font-bold transition active:scale-95 shrink-0 shadow-sm">
                            <i data-lucide="sparkles" class="w-3.5 h-3.5 text-amber-500"></i>
                            <span>Thesis Demo Scenarios</span>
                            <i data-lucide="chevron-down" class="w-3 h-3 text-slate-400 shrink-0 transition-transform" :class="{ 'rotate-180': demoMenuOpen }"></i>
                        </button>

                        <div x-show="demoMenuOpen" @click.away="demoMenuOpen = false" x-cloak
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute left-0 mt-2 w-80 py-2.5 bg-white dark:bg-[#131a2c] border border-slate-200 dark:border-slate-700/80 rounded-2xl shadow-2xl z-50">
                            
                            <div class="px-4 pb-2 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                                <div>
                                    <p class="text-[11px] font-extrabold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">Live Thesis Presentation Tools</p>
                                    <p class="text-[10px] text-slate-400">Instant test cases for defense evaluation</p>
                                </div>
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-amber-50 dark:bg-amber-500/10 text-amber-600 border border-amber-500/20">Preset</span>
                            </div>

                            <div class="p-2 space-y-1">
                                <!-- Scenario 1: Heavy Rain -->
                                <button @click="triggerSimulatedWeatherAlert(); demoMenuOpen = false;" 
                                        class="w-full p-2.5 text-left rounded-xl hover:bg-amber-50 dark:hover:bg-amber-500/10 transition group flex items-start gap-2.5">
                                    <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                        <i data-lucide="cloud-rain" class="w-4 h-4"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-bold text-slate-900 dark:text-white">1. Heavy Rain on Concrete Pouring</p>
                                        <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">Triggers 75% precipitation threshold on Sept 30 & highlights Gantt</p>
                                    </div>
                                </button>

                                <!-- Scenario 2: High Wind on Crane -->
                                <button @click="triggerCraneWindAlert(); demoMenuOpen = false;" 
                                        class="w-full p-2.5 text-left rounded-xl hover:bg-rose-50 dark:hover:bg-rose-500/10 transition group flex items-start gap-2.5">
                                    <div class="w-8 h-8 rounded-lg bg-rose-100 dark:bg-rose-500/20 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                        <i data-lucide="wind" class="w-4 h-4"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-bold text-slate-900 dark:text-white">2. High Wind on Tower Crane Lifting</p>
                                        <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">Triggers 48 km/h wind gust alert on Oct 02 (Safety Infeasible)</p>
                                    </div>
                                </button>

                                <!-- Scenario 3: Progress Lag Recovery -->
                                <button @click="triggerProgressVarianceDemo(); demoMenuOpen = false;" 
                                        class="w-full p-2.5 text-left rounded-xl hover:bg-emerald-50 dark:hover:bg-emerald-500/10 transition group flex items-start gap-2.5">
                                    <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                        <i data-lucide="trending-up" class="w-4 h-4"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-bold text-slate-900 dark:text-white">3. Progress Acceleration (+15% Recovered)</p>
                                        <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">Demonstrates 3-shade variance bar recovery</p>
                                    </div>
                                </button>

                                <!-- Reset Option -->
                                <div class="pt-1 border-t border-slate-100 dark:border-slate-800">
                                    <button @click="resetToInitialState(); demoMenuOpen = false;" 
                                            class="w-full px-3 py-2 text-left rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 text-[11px] font-semibold text-slate-600 dark:text-slate-400 flex items-center justify-between transition">
                                        <span class="flex items-center gap-2">
                                            <i data-lucide="rotate-ccw" class="w-3.5 h-3.5 text-slate-400"></i>
                                            <span>Reset All Scenario Data</span>
                                        </span>
                                        <span class="text-[9px] text-slate-400">Default Baseline</span>
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Right: Company + Notification + Role + Profile Menu -->
                <div class="flex items-center gap-1.5 sm:gap-3 shrink-0">
                    
                    <!-- Company Switcher -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" 
                                class="flex items-center gap-1 sm:gap-2 px-2.5 sm:px-3 py-1.5 sm:py-2 rounded-xl bg-slate-100 dark:bg-slate-800/80 hover:bg-slate-200/80 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700/70 text-xs font-medium text-slate-700 dark:text-slate-200 transition">
                            <i data-lucide="building" class="w-3.5 h-3.5 text-indigo-500 shrink-0"></i>
                            <span class="hidden md:inline font-semibold" x-text="currentCompany.name"></span>
                            <span class="md:hidden font-semibold text-[11px]" x-text="currentCompany.code"></span>
                            <i data-lucide="chevron-down" class="w-3 h-3 text-slate-400 shrink-0"></i>
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak
                             class="absolute right-0 mt-2 w-56 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/80 rounded-2xl shadow-xl z-50">
                            <p class="px-3.5 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">Select Company</p>
                            <template x-for="c in companies" :key="c.id">
                                <button @click="switchCompany(c); open = false;" 
                                        class="w-full px-3.5 py-2 text-left text-xs hover:bg-indigo-50 dark:hover:bg-indigo-500/10 hover:text-indigo-600 dark:hover:text-indigo-400 flex items-center justify-between"
                                        :class="{'text-indigo-600 dark:text-indigo-400 font-bold bg-indigo-50/60 dark:bg-indigo-500/5': currentCompany.id === c.id, 'text-slate-700 dark:text-slate-300': currentCompany.id !== c.id}">
                                    <span x-text="c.name"></span>
                                    <span class="text-[10px] text-slate-400" x-text="c.projectsCount + ' Projects'"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Notification Center Bell & Dropdown -->
                    <div class="relative" x-data="{ notifOpen: false }">
                        <button @click="notifOpen = !notifOpen; $nextTick(() => lucide.createIcons());" 
                                class="relative p-1.5 sm:p-2 rounded-xl bg-slate-100 dark:bg-slate-800/80 hover:bg-slate-200/80 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700/70 text-slate-600 dark:text-slate-300 transition shrink-0"
                                aria-label="View Weather Alerts and Notifications">
                            <i data-lucide="bell" class="w-4 h-4"></i>
                            
                            <!-- Unread Indicator Badge & Pulse -->
                            <template x-if="countUnreadNotifications() > 0">
                                <span class="absolute -top-1 -right-1 flex h-4 min-w-[16px] px-1 items-center justify-center rounded-full bg-rose-500 text-[9px] font-extrabold text-white ring-2 ring-white dark:ring-[#0f1523] animate-pulse"
                                      x-text="countUnreadNotifications()">
                                </span>
                            </template>
                        </button>

                        <!-- Notification Dropdown Panel -->
                        <div x-show="notifOpen" @click.away="notifOpen = false" x-cloak
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-72 sm:w-96 py-2.5 bg-white dark:bg-[#131a2c] border border-slate-200 dark:border-slate-700/80 rounded-2xl shadow-2xl z-50 overflow-hidden">
                            
                            <!-- Header -->
                            <div class="px-4 pb-2.5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <h4 class="text-xs font-bold text-slate-900 dark:text-white">Weather Alerts & Notices</h4>
                                    <span class="px-1.5 py-0.5 rounded-full text-[9px] font-extrabold bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400" 
                                          x-text="countUnreadNotifications() + ' New'"></span>
                                </div>
                                <button @click="markAllNotificationsAsRead()" 
                                        class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                                    Mark all as read
                                </button>
                            </div>

                            <!-- Notifications List -->
                            <div class="max-h-80 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800/60 text-xs">
                                
                                <template x-for="notif in notifications" :key="notif.id">
                                    <div class="p-3.5 hover:bg-slate-50 dark:hover:bg-slate-800/40 transition flex items-start gap-3 cursor-pointer group"
                                         :class="{ 'bg-indigo-50/30 dark:bg-indigo-500/5': !notif.read }"
                                         @click="handleNotificationClick(notif); notifOpen = false;">
                                        
                                        <!-- Category Icon -->
                                        <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 mt-0.5"
                                             :class="{
                                                'bg-amber-100 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400': notif.type === 'warning',
                                                'bg-rose-100 dark:bg-rose-500/20 text-rose-600 dark:text-rose-400': notif.type === 'error',
                                                'bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400': notif.type === 'success',
                                                'bg-sky-100 dark:bg-sky-500/20 text-sky-600 dark:text-sky-400': notif.type === 'info'
                                             }">
                                            <i :data-lucide="notif.icon || 'bell'" class="w-4 h-4"></i>
                                        </div>

                                        <!-- Content -->
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between gap-1">
                                                <p class="font-bold text-slate-900 dark:text-white truncate text-[11px]" x-text="notif.title"></p>
                                                <span class="text-[9px] text-slate-400 shrink-0" x-text="notif.time"></span>
                                            </div>
                                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 line-clamp-2 leading-relaxed" x-text="notif.message"></p>
                                            
                                            <!-- Action Trigger Hint if Alert -->
                                            <template x-if="notif.category === 'weather_alert'">
                                                <span class="inline-flex items-center gap-1 text-[10px] font-bold text-indigo-600 dark:text-indigo-400 mt-1 group-hover:translate-x-0.5 transition-transform">
                                                    <span>Open Weather Assessment</span>
                                                    <i data-lucide="arrow-right" class="w-3 h-3"></i>
                                                </span>
                                            </template>
                                        </div>

                                        <!-- Unread Dot -->
                                        <div class="shrink-0 self-center" x-show="!notif.read">
                                            <span class="w-2 h-2 rounded-full bg-indigo-600 dark:bg-indigo-400 block"></span>
                                        </div>
                                    </div>
                                </template>

                                <!-- Empty State -->
                                <template x-if="notifications.length === 0">
                                    <div class="py-8 text-center px-4">
                                        <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center mx-auto mb-2">
                                            <i data-lucide="bell-off" class="w-5 h-5"></i>
                                        </div>
                                        <p class="text-xs font-bold text-slate-700 dark:text-slate-300">No active alerts</p>
                                        <p class="text-[10px] text-slate-400 mt-0.5">All weather and progress advisories are cleared.</p>
                                    </div>
                                </template>

                            </div>

                            <!-- Footer -->
                            <div class="p-2 border-t border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-[#0f1523]/80 text-center">
                                <button @click="switchTab('audit'); notifOpen = false;" 
                                        class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 transition">
                                    View Full Activity & Audit Log →
                                </button>
                            </div>

                        </div>
                    </div>

                    <!-- Role Switcher -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" 
                                class="flex items-center gap-1 sm:gap-2 px-2.5 sm:px-3 py-1.5 sm:py-2 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 hover:bg-indigo-100/80 dark:hover:bg-indigo-500/20 border border-indigo-200/80 dark:border-indigo-500/30 text-xs font-semibold text-indigo-700 dark:text-indigo-300 transition">
                            <i data-lucide="shield" class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400 shrink-0"></i>
                            <span class="hidden sm:inline" x-text="'Role: ' + currentRole.title"></span>
                            <span class="sm:hidden text-[11px]" x-text="currentRole.short"></span>
                            <i data-lucide="chevron-down" class="w-3 h-3 text-indigo-500 shrink-0"></i>
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak
                             class="absolute right-0 mt-2 w-64 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/80 rounded-2xl shadow-xl z-50">
                            <div class="px-3.5 py-1.5 border-b border-slate-100 dark:border-slate-800">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Simulate Role Perspective</p>
                                <p class="text-[11px] text-slate-400">Changes permissions & actions</p>
                            </div>
                            <template x-for="r in roles" :key="r.id">
                                <button @click="switchRole(r); open = false;" 
                                        class="w-full px-3.5 py-2.5 text-left text-xs hover:bg-indigo-50 dark:hover:bg-indigo-500/10 hover:text-indigo-600 dark:hover:text-indigo-300 flex flex-col gap-0.5 border-b border-slate-100 dark:border-slate-800/40 last:border-0"
                                        :class="{'text-indigo-600 dark:text-indigo-400 font-bold bg-indigo-50/60 dark:bg-indigo-500/5': currentRole.id === r.id, 'text-slate-700 dark:text-slate-300': currentRole.id !== r.id}">
                                    <div class="flex items-center justify-between">
                                        <span class="font-semibold" x-text="r.title"></span>
                                        <span x-show="currentRole.id === r.id" class="w-1.5 h-1.5 rounded-full bg-indigo-600 dark:bg-indigo-400"></span>
                                    </div>
                                    <span class="text-[10px] text-slate-400 font-normal" x-text="r.desc"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Profile & Theme Toggle Menu -->
                    <div class="relative pl-1 sm:pl-2 border-l border-slate-200 dark:border-slate-800" x-data="{ profileOpen: false }">
                        <button @click="profileOpen = !profileOpen" class="flex items-center gap-1.5 p-1 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                            <div class="w-7 h-7 sm:w-9 sm:h-9 rounded-xl bg-indigo-600 flex items-center justify-center font-bold text-[11px] sm:text-xs text-white shadow-sm shrink-0">
                                <span x-text="currentUser.initials"></span>
                            </div>
                            <div class="hidden xl:block text-left">
                                <p class="text-xs font-bold text-slate-800 dark:text-slate-200" x-text="currentUser.name"></p>
                                <p class="text-[10px] text-slate-400 font-medium" x-text="currentRole.title"></p>
                            </div>
                            <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400 hidden sm:block"></i>
                        </button>

                        <div x-show="profileOpen" @click.away="profileOpen = false" x-cloak
                             class="absolute right-0 mt-2 w-64 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/80 rounded-2xl shadow-xl z-50">
                            <div class="px-4 py-2.5 border-b border-slate-100 dark:border-slate-800">
                                <p class="text-xs font-bold text-slate-900 dark:text-white" x-text="currentUser.name"></p>
                                <p class="text-[11px] text-slate-400" x-text="currentUser.email"></p>
                            </div>

                            <!-- Appearance Section -->
                            <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-800">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-2">
                                        <i :data-lucide="isDarkMode ? 'moon' : 'sun'" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                                        <span>Appearance</span>
                                    </span>
                                    <button @click="toggleTheme()" 
                                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                                            :class="isDarkMode ? 'bg-indigo-600' : 'bg-slate-300'">
                                        <span class="sr-only">Toggle theme</span>
                                        <span class="pointer-events-none relative inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                              :class="isDarkMode ? 'translate-x-5' : 'translate-x-0'">
                                            <span class="absolute inset-0 flex h-full w-full items-center justify-center transition-opacity"
                                                  :class="isDarkMode ? 'opacity-0 duration-100 ease-out' : 'opacity-100 duration-200 ease-in'">
                                                <i data-lucide="sun" class="w-3 h-3 text-amber-500"></i>
                                            </span>
                                            <span class="absolute inset-0 flex h-full w-full items-center justify-center transition-opacity"
                                                  :class="isDarkMode ? 'opacity-100 duration-200 ease-in' : 'opacity-0 duration-100 ease-out'">
                                                <i data-lucide="moon" class="w-3 h-3 text-indigo-600"></i>
                                            </span>
                                        </span>
                                    </button>
                                </div>
                                <p class="text-[10px] text-slate-400 mt-1" x-text="isDarkMode ? 'Active: Dark Mode' : 'Active: Soft Light Palette'"></p>
                            </div>

                            <div class="px-2 pt-1.5">
                                <button @click="profileOpen = false; addToast('Profile Settings', 'User preference panel (Active Laravel Session)', 'info', 'settings')" 
                                        class="w-full flex items-center gap-2.5 px-3 py-2 text-xs font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-xl transition">
                                    <i data-lucide="user-cog" class="w-4 h-4 text-slate-400"></i>
                                    <span>Account Settings</span>
                                </button>
                                
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="w-full m-0 p-0">
                                    @csrf
                                    <button type="submit" 
                                            class="w-full flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-500/10 rounded-xl transition cursor-pointer">
                                        <i data-lucide="log-out" class="w-4 h-4"></i>
                                        <span>Sign Out (Logout)</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>

            </header>

            <!-- MAIN DYNAMIC CONTENT VIEWPORT -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 relative">
                
                <!-- CONTEXT-AWARE SKELETON LOADER STATE -->
                <div x-show="isLoading" x-cloak class="space-y-6 animate-pulse">
                    
                    <!-- Header Skeleton -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-2">
                        <div class="space-y-2">
                            <div class="h-7 w-56 sm:w-80 bg-slate-200 dark:bg-slate-800 rounded-xl"></div>
                            <div class="h-4 w-72 sm:w-96 bg-slate-200/70 dark:bg-slate-800/60 rounded-lg"></div>
                        </div>
                        <div class="h-9 w-32 bg-slate-200 dark:bg-slate-800 rounded-xl"></div>
                    </div>

                    <!-- 1. DASHBOARD SKELETON -->
                    <template x-if="activeTab === 'dashboard'">
                        <div class="space-y-6">
                            <!-- 4 KPI Cards -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
                                <div class="dribbble-card p-5 space-y-3">
                                    <div class="flex justify-between items-center"><div class="h-3 w-24 bg-slate-200 dark:bg-slate-800 rounded"></div><div class="w-7 h-7 bg-slate-200 dark:bg-slate-800 rounded-lg"></div></div>
                                    <div class="h-8 w-20 bg-slate-200 dark:bg-slate-800 rounded-lg"></div>
                                    <div class="h-3 w-32 bg-slate-200 dark:bg-slate-800 rounded"></div>
                                </div>
                                <div class="dribbble-card p-5 space-y-3">
                                    <div class="flex justify-between items-center"><div class="h-3 w-24 bg-slate-200 dark:bg-slate-800 rounded"></div><div class="w-7 h-7 bg-slate-200 dark:bg-slate-800 rounded-lg"></div></div>
                                    <div class="h-8 w-20 bg-slate-200 dark:bg-slate-800 rounded-lg"></div>
                                    <div class="h-3 w-32 bg-slate-200 dark:bg-slate-800 rounded"></div>
                                </div>
                                <div class="dribbble-card p-5 space-y-3">
                                    <div class="flex justify-between items-center"><div class="h-3 w-24 bg-slate-200 dark:bg-slate-800 rounded"></div><div class="w-7 h-7 bg-slate-200 dark:bg-slate-800 rounded-lg"></div></div>
                                    <div class="h-8 w-28 bg-slate-200 dark:bg-slate-800 rounded-lg"></div>
                                    <div class="h-3 w-36 bg-slate-200 dark:bg-slate-800 rounded"></div>
                                </div>
                                <div class="dribbble-card p-5 space-y-3">
                                    <div class="flex justify-between items-center"><div class="h-3 w-24 bg-slate-200 dark:bg-slate-800 rounded"></div><div class="w-7 h-7 bg-slate-200 dark:bg-slate-800 rounded-lg"></div></div>
                                    <div class="h-8 w-28 bg-slate-200 dark:bg-slate-800 rounded-lg"></div>
                                    <div class="h-3 w-36 bg-slate-200 dark:bg-slate-800 rounded"></div>
                                </div>
                            </div>

                            <!-- 7-Day Weather Outlook Skeleton -->
                            <div class="dribbble-card p-6 space-y-4">
                                <div class="flex justify-between"><div class="h-4 w-48 bg-slate-200 dark:bg-slate-800 rounded"></div><div class="h-3 w-24 bg-slate-200 dark:bg-slate-800 rounded"></div></div>
                                <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3">
                                    <div class="h-28 bg-slate-100 dark:bg-slate-800/50 rounded-2xl"></div>
                                    <div class="h-28 bg-slate-100 dark:bg-slate-800/50 rounded-2xl"></div>
                                    <div class="h-28 bg-slate-100 dark:bg-slate-800/50 rounded-2xl"></div>
                                    <div class="h-28 bg-slate-100 dark:bg-slate-800/50 rounded-2xl"></div>
                                    <div class="h-28 bg-slate-100 dark:bg-slate-800/50 rounded-2xl"></div>
                                    <div class="h-28 bg-slate-100 dark:bg-slate-800/50 rounded-2xl"></div>
                                    <div class="h-28 bg-slate-100 dark:bg-slate-800/50 rounded-2xl"></div>
                                </div>
                            </div>

                            <!-- S-Curve Chart Skeleton -->
                            <div class="dribbble-card p-6 space-y-4">
                                <div class="flex justify-between"><div class="h-4 w-52 bg-slate-200 dark:bg-slate-800 rounded"></div><div class="h-3 w-32 bg-slate-200 dark:bg-slate-800 rounded"></div></div>
                                <div class="h-64 sm:h-80 bg-slate-100 dark:bg-slate-800/40 rounded-2xl"></div>
                            </div>
                        </div>
                    </template>

                    <!-- 2. PROJECTS / ACTIVITIES GRID SKELETON -->
                    <template x-if="activeTab === 'projects' || activeTab === 'activity-library'">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                            <div class="dribbble-card p-5 space-y-4">
                                <div class="flex justify-between"><div class="w-10 h-10 bg-slate-200 dark:bg-slate-800 rounded-xl"></div><div class="h-5 w-16 bg-slate-200 dark:bg-slate-800 rounded"></div></div>
                                <div class="h-5 w-3/4 bg-slate-200 dark:bg-slate-800 rounded"></div>
                                <div class="h-3 w-1/2 bg-slate-200 dark:bg-slate-800 rounded"></div>
                                <div class="grid grid-cols-2 gap-2 pt-2"><div class="h-12 bg-slate-100 dark:bg-slate-800/60 rounded-xl"></div><div class="h-12 bg-slate-100 dark:bg-slate-800/60 rounded-xl"></div></div>
                                <div class="h-9 bg-slate-200 dark:bg-slate-800 rounded-xl pt-2"></div>
                            </div>
                            <div class="dribbble-card p-5 space-y-4">
                                <div class="flex justify-between"><div class="w-10 h-10 bg-slate-200 dark:bg-slate-800 rounded-xl"></div><div class="h-5 w-16 bg-slate-200 dark:bg-slate-800 rounded"></div></div>
                                <div class="h-5 w-3/4 bg-slate-200 dark:bg-slate-800 rounded"></div>
                                <div class="h-3 w-1/2 bg-slate-200 dark:bg-slate-800 rounded"></div>
                                <div class="grid grid-cols-2 gap-2 pt-2"><div class="h-12 bg-slate-100 dark:bg-slate-800/60 rounded-xl"></div><div class="h-12 bg-slate-100 dark:bg-slate-800/60 rounded-xl"></div></div>
                                <div class="h-9 bg-slate-200 dark:bg-slate-800 rounded-xl pt-2"></div>
                            </div>
                            <div class="dribbble-card p-5 space-y-4">
                                <div class="flex justify-between"><div class="w-10 h-10 bg-slate-200 dark:bg-slate-800 rounded-xl"></div><div class="h-5 w-16 bg-slate-200 dark:bg-slate-800 rounded"></div></div>
                                <div class="h-5 w-3/4 bg-slate-200 dark:bg-slate-800 rounded"></div>
                                <div class="h-3 w-1/2 bg-slate-200 dark:bg-slate-800 rounded"></div>
                                <div class="grid grid-cols-2 gap-2 pt-2"><div class="h-12 bg-slate-100 dark:bg-slate-800/60 rounded-xl"></div><div class="h-12 bg-slate-100 dark:bg-slate-800/60 rounded-xl"></div></div>
                                <div class="h-9 bg-slate-200 dark:bg-slate-800 rounded-xl pt-2"></div>
                            </div>
                        </div>
                    </template>

                    <!-- 3. GANTT / PROGRESS VIEW SKELETON -->
                    <template x-if="activeTab === 'scheduling' || activeTab === 'progress'">
                        <div class="dribbble-card p-6 space-y-4">
                            <div class="flex justify-between"><div class="h-4 w-60 bg-slate-200 dark:bg-slate-800 rounded"></div><div class="h-4 w-32 bg-slate-200 dark:bg-slate-800 rounded"></div></div>
                            <div class="h-12 bg-slate-200/80 dark:bg-slate-800/80 rounded-xl"></div>
                            <div class="space-y-3">
                                <div class="h-14 bg-slate-100 dark:bg-slate-800/50 rounded-xl"></div>
                                <div class="h-14 bg-slate-100 dark:bg-slate-800/50 rounded-xl"></div>
                                <div class="h-14 bg-slate-100 dark:bg-slate-800/50 rounded-xl"></div>
                                <div class="h-14 bg-slate-100 dark:bg-slate-800/50 rounded-xl"></div>
                            </div>
                        </div>
                    </template>

                    <!-- 4. TABLE / AUDIT / USERS SKELETON -->
                    <template x-if="activeTab === 'audit' || activeTab === 'users' || activeTab === 'weather-config'">
                        <div class="dribbble-card p-6 space-y-4">
                            <div class="h-10 bg-slate-100 dark:bg-slate-800/60 rounded-xl"></div>
                            <div class="space-y-2">
                                <div class="h-12 bg-slate-200/60 dark:bg-slate-800/70 rounded-lg"></div>
                                <div class="h-12 bg-slate-100 dark:bg-slate-800/40 rounded-lg"></div>
                                <div class="h-12 bg-slate-100 dark:bg-slate-800/40 rounded-lg"></div>
                                <div class="h-12 bg-slate-100 dark:bg-slate-800/40 rounded-lg"></div>
                            </div>
                        </div>
                    </template>

                </div>

                <!-- ACTUAL CONTENT VIEWS -->
                <div x-show="!isLoading" x-cloak class="space-y-6 sm:space-y-8">
                    
                    <!-- TAB 0: PROJECTS DIRECTORY & WORKSPACE MANAGEMENT (1-CLICK DEDICATED PAGE) -->
                    <section x-show="activeTab === 'projects'" x-cloak class="space-y-6">
                        
                        <!-- Header & Actions -->
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div>
                                <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
                                    <span>CLSU Construction Projects Directory</span>
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-200/60 dark:border-indigo-500/20" x-text="getFilteredProjects().length + ' Vertical Project(s)'"></span>
                                </h2>
                                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1 font-medium">
                                    CLSU campus vertical building workspaces monitored by PPSDS and implemented by assigned private contractors.
                                </p>
                            </div>

                            <div class="flex items-center gap-3">
                                <button @click="openNewProjectModal()" 
                                        class="flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition shadow-md shadow-indigo-500/20 active:scale-95">
                                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                                    <span>Create New Project</span>
                                </button>
                            </div>
                        </div>

                        <!-- Contractor / Organization Scope Filter Pills -->
                        <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs">
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider shrink-0 mr-1">Scope View:</span>
                            <button @click="projectCompanyFilter = 'ALL'"
                                    class="px-3.5 py-1.5 rounded-xl font-bold transition shrink-0 flex items-center gap-1.5"
                                    :class="projectCompanyFilter === 'ALL' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800/80 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'">
                                <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                                <span>All Campus Projects (PPSDS)</span>
                            </button>
                            <template x-for="c in companies" :key="'filter-comp-' + c.id">
                                <button @click="projectCompanyFilter = c.id.toString()"
                                        class="px-3 py-1.5 rounded-xl font-semibold transition shrink-0 flex items-center gap-1.5 border"
                                        :class="projectCompanyFilter === c.id.toString() ? 'bg-indigo-600 text-white border-transparent shadow-sm' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:border-indigo-400'">
                                    <i data-lucide="building" class="w-3.5 h-3.5" :class="projectCompanyFilter === c.id.toString() ? 'text-white' : 'text-indigo-500'"></i>
                                    <span x-text="c.code + ' — ' + c.name.split(' ')[0]"></span>
                                </button>
                            </template>
                        </div>

                        <!-- Projects Grid Cards -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                            <template x-for="p in getFilteredProjects()" :key="'grid-p-' + p.id">
                                <div class="dribbble-card p-5 relative flex flex-col justify-between group hover:shadow-md transition-all border"
                                     :class="currentProject.id === p.id ? 'border-indigo-500/40 ring-2 ring-indigo-500/10' : 'border-slate-200/80 dark:border-slate-800'">
                                    
                                    <div>
                                        <!-- Card Top Header -->
                                        <div class="flex items-start justify-between gap-3 mb-3">
                                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500/10 to-sky-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-sm shrink-0 border border-indigo-200/60 dark:border-indigo-500/20">
                                                <i data-lucide="building-2" class="w-5 h-5"></i>
                                            </div>
                                            <div class="flex items-center gap-1.5 flex-wrap justify-end">
                                                <span x-show="currentProject.id === p.id" 
                                                      class="px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-500/20 flex items-center gap-1">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                                    <span>ACTIVE SITE</span>
                                                </span>
                                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400" x-text="p.code"></span>
                                            </div>
                                        </div>

                                        <!-- Assigned Contractor Company Badge -->
                                        <div class="mb-2">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 border border-indigo-200/70 dark:border-indigo-500/20">
                                                <i data-lucide="hard-hat" class="w-3 h-3 text-indigo-500"></i>
                                                <span x-text="p.company_name || getUserCompanyName(p.company_id || p.companyId)"></span>
                                            </span>
                                        </div>

                                        <!-- Project Title & Location -->
                                        <h3 class="font-bold text-slate-900 dark:text-white text-base leading-snug group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition" x-text="p.name"></h3>
                                        
                                        <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 mt-2">
                                            <i data-lucide="map-pin" class="w-3.5 h-3.5 text-rose-500 shrink-0"></i>
                                            <span class="truncate" x-text="p.location"></span>
                                        </div>

                                        <!-- Site Parameters -->
                                        <div class="grid grid-cols-2 gap-2 mt-4 pt-4 border-t border-slate-100 dark:border-slate-800/80 text-xs">
                                            <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200/70 dark:border-slate-800">
                                                <p class="text-[10px] font-bold uppercase text-slate-400">Structure Type</p>
                                                <p class="font-extrabold text-slate-800 dark:text-slate-200 mt-0.5" x-text="(p.storeys || 4) + '-Storey Vertical'"></p>
                                            </div>
                                            <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200/70 dark:border-slate-800">
                                                <p class="text-[10px] font-bold uppercase text-slate-400">Accomplishment</p>
                                                <p class="font-extrabold text-emerald-600 dark:text-emerald-400 mt-0.5" x-text="(p.avgActual || 50) + '% Completed'"></p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Card Actions -->
                                    <div class="flex items-center justify-between gap-2 mt-5 pt-3 border-t border-slate-100 dark:border-slate-800/80">
                                        <button @click="switchProject(p, 'dashboard')" 
                                                class="flex-1 py-2 px-3 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5"
                                                :class="currentProject.id === p.id ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800 hover:bg-indigo-50 hover:text-indigo-600 dark:hover:bg-indigo-500/10 text-slate-700 dark:text-slate-300'">
                                            <i :data-lucide="currentProject.id === p.id ? 'check' : 'arrow-right'" class="w-3.5 h-3.5"></i>
                                            <span x-text="currentProject.id === p.id ? 'Active Workspace' : 'Open Project'"></span>
                                        </button>

                                        <button @click="editProject(p)" class="p-2 rounded-xl text-slate-400 hover:text-indigo-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition" title="Edit Site Details">
                                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                                        </button>

                                        <button @click="deleteProject(p.id)" class="p-2 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition" title="Delete Project">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>

                                </div>
                            </template>
                        </div>

                    </section>

                    <!-- TAB: CONTRACTOR COMPANIES & ORGANIZATIONS DIRECTORY -->
                    <section x-show="activeTab === 'companies'" x-cloak class="space-y-6">
                        
                        <!-- Header & Actions -->
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div>
                                <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
                                    <span>Contractor Organizations Directory</span>
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-200/60 dark:border-indigo-500/20" x-text="filteredCompanies().length + ' Organizations'"></span>
                                </h2>
                                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1 font-medium">
                                    Directory of private contractor companies awarded vertical construction projects across the CLSU campus.
                                </p>
                            </div>

                            <div class="flex items-center gap-3" x-show="hasPermission('manage_companies')">
                                <button @click="openNewCompanyModal()" 
                                        class="flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition shadow-md shadow-indigo-500/20 active:scale-95">
                                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                                    <span>Register Contractor Company</span>
                                </button>
                            </div>
                        </div>

                        <!-- Statistics KPI Cards -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-5">
                            
                            <div class="dribbble-card p-5 relative group hover:shadow-md transition-all">
                                <div class="flex items-center justify-between text-slate-400 text-xs font-bold mb-2 uppercase tracking-wider">
                                    <span>Registered Organizations</span>
                                    <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                                        <i data-lucide="building" class="w-4 h-4"></i>
                                    </div>
                                </div>
                                <div class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight" x-text="companies.length + ' Companies'"></div>
                                <p class="text-[11px] text-slate-400 mt-1">PPSDS Monitoring Agency + Awarded Private Contractors</p>
                            </div>

                            <div class="dribbble-card p-5 relative group hover:shadow-md transition-all">
                                <div class="flex items-center justify-between text-slate-400 text-xs font-bold mb-2 uppercase tracking-wider">
                                    <span>Active Contractors</span>
                                    <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                                    </div>
                                </div>
                                <div class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 tracking-tight" x-text="companies.filter(c => (c.status || 'Active') === 'Active').length + ' Active'"></div>
                                <p class="text-[11px] text-slate-400 mt-1">Authorized for campus site implementation</p>
                            </div>

                            <div class="dribbble-card p-5 relative group hover:shadow-md transition-all">
                                <div class="flex items-center justify-between text-slate-400 text-xs font-bold mb-2 uppercase tracking-wider">
                                    <span>Total Campus Workspaces</span>
                                    <div class="w-8 h-8 rounded-lg bg-sky-50 dark:bg-sky-500/10 flex items-center justify-center text-sky-600 dark:text-sky-400">
                                        <i data-lucide="folders" class="w-4 h-4"></i>
                                    </div>
                                </div>
                                <div class="text-2xl font-extrabold text-sky-600 dark:text-sky-400 tracking-tight" x-text="allProjects.length + ' Projects'"></div>
                                <p class="text-[11px] text-slate-400 mt-1">Vertical building sites undergoing scheduling</p>
                            </div>

                        </div>

                        <!-- Filter Toolbar -->
                        <div class="dribbble-card p-4 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
                            <div class="flex items-center gap-2 flex-1 max-w-md">
                                <div class="relative w-full">
                                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                                    <input type="text" x-model="companySearch" placeholder="Search contractor name, code, or contact person..."
                                           class="w-full pl-9 pr-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs font-semibold text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <button x-show="companySearch" @click="companySearch = ''" class="p-2 rounded-lg text-slate-400 hover:text-slate-600">
                                    <i data-lucide="x" class="w-4 h-4"></i>
                                </button>
                            </div>

                            <div class="flex items-center gap-2.5 flex-wrap">
                                <span class="text-[11px] font-bold text-slate-400 uppercase hidden sm:inline">Status:</span>
                                <template x-for="st in ['ALL', 'Active', 'Inactive']" :key="'st-' + st">
                                    <button @click="companyStatusFilter = st"
                                            class="px-3 py-1.5 rounded-xl text-xs font-bold transition"
                                            :class="companyStatusFilter === st ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800/80 text-slate-600 dark:text-slate-300 hover:bg-slate-200'">
                                        <span x-text="st === 'ALL' ? 'All Statuses' : st"></span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <!-- Contractor Companies Cards Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                            <template x-for="c in filteredCompanies()" :key="'comp-card-' + c.id">
                                <div class="dribbble-card p-5 relative flex flex-col justify-between group hover:shadow-md transition-all border"
                                     :class="currentCompany.id === c.id ? 'border-indigo-500/40 ring-2 ring-indigo-500/10' : 'border-slate-200/80 dark:border-slate-800'">
                                    
                                    <div>
                                        <!-- Top Header: Code badge & Status -->
                                        <div class="flex items-start justify-between gap-3 mb-3">
                                            <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-sky-500 text-white font-extrabold text-sm flex items-center justify-center shrink-0 shadow-sm">
                                                <span x-text="c.code ? c.code.substring(0, 3) : 'ORG'"></span>
                                            </div>

                                            <div class="flex items-center gap-1.5 flex-wrap justify-end">
                                                <span x-show="c.code === 'PPSDS'" 
                                                      class="px-2 py-0.5 rounded-md text-[9px] font-extrabold bg-indigo-50 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300 border border-indigo-200/60 dark:border-indigo-500/30">
                                                    OWNER / PPSDS
                                                </span>
                                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold"
                                                      :class="(c.status || 'Active') === 'Active' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-500/20' : 'bg-slate-100 dark:bg-slate-800 text-slate-500'"
                                                      x-text="c.status || 'Active'"></span>
                                            </div>
                                        </div>

                                        <!-- Company Name & Code -->
                                        <h3 class="font-bold text-slate-900 dark:text-white text-base leading-snug group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition" x-text="c.name"></h3>
                                        <p class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 mt-0.5" x-text="'Org Code: ' + c.code"></p>

                                        <!-- Contact Information Details -->
                                        <div class="mt-3.5 space-y-2 text-xs">
                                            <div class="flex items-center gap-2 text-slate-600 dark:text-slate-300">
                                                <i data-lucide="user" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                                                <span class="truncate font-medium" x-text="c.contact_person || 'Engineering Lead / Director'"></span>
                                            </div>

                                            <div class="flex items-center gap-2 text-slate-500 dark:text-slate-400 text-[11px]">
                                                <i data-lucide="mail" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                                                <span class="truncate" x-text="c.contact_email || 'No email provided'"></span>
                                            </div>

                                            <div class="flex items-center gap-2 text-slate-500 dark:text-slate-400 text-[11px]">
                                                <i data-lucide="phone" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                                                <span class="truncate" x-text="c.contact_phone || 'No phone provided'"></span>
                                            </div>

                                            <div class="flex items-start gap-2 text-slate-500 dark:text-slate-400 text-[11px]">
                                                <i data-lucide="map-pin" class="w-3.5 h-3.5 text-rose-500 shrink-0 mt-0.5"></i>
                                                <span class="line-clamp-2" x-text="c.address || 'Science City of Muñoz, Nueva Ecija'"></span>
                                            </div>
                                        </div>

                                        <!-- Projects & Personnel Stats -->
                                        <div class="grid grid-cols-2 gap-2 mt-4 pt-4 border-t border-slate-100 dark:border-slate-800/80 text-xs">
                                            <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200/70 dark:border-slate-800">
                                                <p class="text-[10px] font-bold uppercase text-slate-400">Awarded Sites</p>
                                                <p class="font-extrabold text-slate-800 dark:text-slate-200 mt-0.5" x-text="(c.projects_count || c.projectsCount || 0) + ' Projects'"></p>
                                            </div>
                                            <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200/70 dark:border-slate-800">
                                                <p class="text-[10px] font-bold uppercase text-slate-400">Personnel</p>
                                                <p class="font-extrabold text-indigo-600 dark:text-indigo-400 mt-0.5" x-text="(c.users_count || c.usersCount || 0) + ' Users'"></p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex items-center justify-between gap-2 mt-5 pt-3 border-t border-slate-100 dark:border-slate-800/80">
                                        <button @click="switchCompany(c); switchTab('projects');" 
                                                class="flex-1 py-2 px-3 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5"
                                                :class="currentCompany.id === c.id ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800 hover:bg-indigo-50 hover:text-indigo-600 dark:hover:bg-indigo-500/10 text-slate-700 dark:text-slate-300'">
                                            <i :data-lucide="currentCompany.id === c.id ? 'check' : 'arrow-right'" class="w-3.5 h-3.5"></i>
                                            <span x-text="currentCompany.id === c.id ? 'Active Scope' : 'View Projects'"></span>
                                        </button>

                                        <button x-show="hasPermission('manage_companies')" @click="editCompany(c)" class="p-2 rounded-xl text-slate-400 hover:text-indigo-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition" title="Edit Contractor Info">
                                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                                        </button>

                                        <button x-show="hasPermission('manage_companies') && c.code !== 'PPSDS'" @click="deleteCompany(c.id)" class="p-2 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition" title="Delete Contractor">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>

                                </div>
                            </template>
                        </div>

                        <!-- Empty State -->
                        <template x-if="filteredCompanies().length === 0">
                            <div class="dribbble-card p-12 text-center">
                                <div class="max-w-sm mx-auto flex flex-col items-center justify-center text-center space-y-3">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center">
                                        <i data-lucide="search-x" class="w-6 h-6"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-slate-800 dark:text-slate-200">No matching contractor companies found</p>
                                        <p class="text-xs text-slate-400 mt-1">Try clearing your search query or status filter.</p>
                                    </div>
                                    <button @click="companySearch = ''; companyStatusFilter = 'ALL';"
                                            class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm transition">
                                        Reset Filters
                                    </button>
                                </div>
                            </div>
                        </template>

                    </section>

                    <!-- TAB 1: PROJECT OVERVIEW DASHBOARD -->
                    <section x-show="activeTab === 'dashboard'" x-cloak class="space-y-6">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 sm:gap-4">
                            <div>
                                <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
                                    <span>Project Health & Weather Dashboard</span>
                                </h2>
                                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1 font-medium">
                                    Real-time monitoring of construction schedules, active weather advisories, and progress variance.
                                </p>
                            </div>
                            <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
                                <span class="px-3.5 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 text-[11px] sm:text-xs text-slate-600 dark:text-slate-300 flex items-center gap-2 shadow-sm font-medium">
                                    <i data-lucide="clock" class="w-3.5 h-3.5 text-indigo-500"></i>
                                    <span>Sync: <strong>Today, 8:00 AM</strong></span>
                                </span>
                            </div>
                        </div>

                        <!-- 4 Summary KPI Cards -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
                            
                            <!-- Card 1: Overall Planned Progress -->
                            <div class="dribbble-card p-5 relative overflow-hidden group hover:shadow-md transition-all">
                                <div class="flex items-center justify-between text-slate-400 text-xs font-bold mb-3 uppercase tracking-wider">
                                    <span>Planned Cumulative</span>
                                    <div class="w-7 h-7 rounded-lg bg-sky-50 dark:bg-sky-500/10 flex items-center justify-center">
                                        <i data-lucide="target" class="w-4 h-4 text-sky-500"></i>
                                    </div>
                                </div>
                                <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight" x-text="calculateOverallPlanned() + '%'"></div>
                                <div class="mt-3 flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                                    <span class="font-medium text-slate-700 dark:text-slate-300">Target this week:</span>
                                    <span x-text="calculateOverallPlanned() + '%'"></span>
                                </div>
                            </div>

                            <!-- Card 2: Overall Actual Progress -->
                            <div class="dribbble-card p-5 relative overflow-hidden group hover:shadow-md transition-all">
                                <div class="flex items-center justify-between text-slate-400 text-xs font-bold mb-3 uppercase tracking-wider">
                                    <span>Actual Cumulative</span>
                                    <div class="w-7 h-7 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center">
                                        <i data-lucide="activity" class="w-4 h-4 text-emerald-500"></i>
                                    </div>
                                </div>
                                <div class="text-2xl sm:text-3xl font-extrabold text-emerald-600 dark:text-emerald-400 tracking-tight" x-text="calculateOverallActual() + '%'"></div>
                                <div class="mt-3 flex items-center gap-1.5 text-xs font-semibold"
                                     :class="calculateOverallVariance() >= 0 ? 'text-emerald-600' : 'text-amber-600'">
                                    <i :data-lucide="calculateOverallVariance() >= 0 ? 'trending-up' : 'trending-down'" class="w-3.5 h-3.5"></i>
                                    <span x-text="(calculateOverallVariance() >= 0 ? '+' : '') + calculateOverallVariance() + '% Variance (' + getProjectStatus() + ')'"></span>
                                </div>
                            </div>

                            <!-- Card 3: Weather Feasibility Status -->
                            <div class="dribbble-card p-5 relative overflow-hidden group hover:shadow-md transition-all"
                                 :class="countActiveAlerts() > 0 ? (isDarkMode ? 'bg-amber-500/5 border-amber-500/20' : 'bg-amber-50/50 border-amber-200/80') : (isDarkMode ? 'bg-emerald-500/5 border-emerald-500/20' : 'bg-emerald-50/50 border-emerald-200/80')">
                                <div class="flex items-center justify-between text-xs font-bold mb-3 uppercase tracking-wider"
                                     :class="countActiveAlerts() > 0 ? 'text-amber-600 dark:text-amber-300' : 'text-emerald-600 dark:text-emerald-400'">
                                    <span>Weather Feasibility</span>
                                    <div class="w-7 h-7 rounded-lg flex items-center justify-center"
                                         :class="countActiveAlerts() > 0 ? 'bg-amber-100 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400' : 'bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400'">
                                        <i :data-lucide="countActiveAlerts() > 0 ? 'cloud-rain' : 'check-circle'" class="w-4 h-4"></i>
                                    </div>
                                </div>
                                <div class="text-lg sm:text-xl font-bold tracking-tight"
                                     :class="countActiveAlerts() > 0 ? 'text-amber-700 dark:text-amber-300' : 'text-emerald-700 dark:text-emerald-300'"
                                     x-text="countActiveAlerts() > 0 ? 'Conditionally Feasible' : 'Feasible (Clear)'"></div>
                                <div class="mt-3 text-xs text-slate-500 dark:text-slate-400 font-medium">
                                    <span x-text="countActiveAlerts() > 0 ? (countActiveAlerts() + ' activity requires weather evaluation') : 'All activities clear of weather threshold alerts'"></span>
                                </div>
                            </div>

                            <!-- Card 4: Worker Safety Risk -->
                            <div class="dribbble-card p-5 relative overflow-hidden group hover:shadow-md transition-all">
                                <div class="flex items-center justify-between text-slate-400 text-xs font-bold mb-3 uppercase tracking-wider">
                                    <span>Worker Safety Risk</span>
                                    <div class="w-7 h-7 rounded-lg flex items-center justify-center"
                                         :class="countActiveAlerts() > 0 ? 'bg-rose-50 dark:bg-rose-500/10 text-rose-500' : 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-500'">
                                        <i data-lucide="hard-hat" class="w-4 h-4"></i>
                                    </div>
                                </div>
                                <div class="text-lg sm:text-xl font-bold tracking-tight"
                                     :class="countActiveAlerts() > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400'"
                                     x-text="countActiveAlerts() > 0 ? 'Moderate (Weather Advisory)' : 'Low Risk (Normal Operations)'"></div>
                                <div class="mt-3 text-xs text-slate-500 dark:text-slate-400 font-medium">
                                    <span x-text="countActiveAlerts() > 0 ? 'Safety advisory issued for outdoor activities' : 'No active weather safety hazards on-site'"></span>
                                </div>
                            </div>

                        </div>

                        <!-- Weather Outlook Preview: 7-Day vs Monthly Toggleable Card -->
                        <div class="dribbble-card p-5 sm:p-6">
                            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 mb-4 sm:mb-5">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-lg bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center shrink-0">
                                        <i data-lucide="calendar-days" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                            <span x-text="weatherViewMode === '7days' ? '7-Day Site Weather & Risk Outlook' : 'Monthly Meteorological Outlook & Risk Calendar'"></span>
                                        </h3>
                                    </div>
                                </div>
                                
                                <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
                                    
                                    <!-- 7-Day vs Month View Toggle Switch -->
                                    <div class="flex items-center p-1 rounded-xl bg-slate-100 dark:bg-slate-800/90 border border-slate-200/80 dark:border-slate-700 text-xs">
                                        <button @click="weatherViewMode = '7days'; $nextTick(() => lucide.createIcons());"
                                                class="px-2.5 sm:px-3 py-1 rounded-lg font-semibold transition flex items-center gap-1.5 text-xs"
                                                :class="weatherViewMode === '7days' ? 'bg-white dark:bg-indigo-600 text-indigo-600 dark:text-white shadow-sm font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200'">
                                            <i data-lucide="calendar-days" class="w-3.5 h-3.5"></i>
                                            <span>7 Days</span>
                                        </button>
                                        <button @click="weatherViewMode = 'monthly'; $nextTick(() => lucide.createIcons());"
                                                class="px-2.5 sm:px-3 py-1 rounded-lg font-semibold transition flex items-center gap-1.5 text-xs"
                                                :class="weatherViewMode === 'monthly' ? 'bg-white dark:bg-indigo-600 text-indigo-600 dark:text-white shadow-sm font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200'">
                                            <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                                            <span>Month View</span>
                                        </button>
                                    </div>

                                    <!-- Month Selector Dropdown (Active in Month View) -->
                                    <template x-if="weatherViewMode === 'monthly'">
                                        <div class="relative" x-data="{ monthDropdownOpen: false }">
                                            <button @click="monthDropdownOpen = !monthDropdownOpen; $nextTick(() => lucide.createIcons());" 
                                                    class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800/90 hover:bg-slate-200/80 dark:hover:bg-slate-800 border border-slate-200/80 dark:border-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-200 transition shadow-sm">
                                                <i data-lucide="calendar" class="w-3.5 h-3.5 text-indigo-500 shrink-0"></i>
                                                <span class="font-bold" x-text="selectedWeatherMonth + ' 2026'"></span>
                                                <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': monthDropdownOpen }"></i>
                                            </button>

                                            <div x-show="monthDropdownOpen" @click.away="monthDropdownOpen = false" x-cloak
                                                 x-transition:enter="transition ease-out duration-150"
                                                 x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                                                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                                 x-transition:leave="transition ease-in duration-100"
                                                 x-transition:leave-start="opacity-100 scale-100"
                                                 x-transition:leave-end="opacity-0 scale-95"
                                                 class="absolute right-0 sm:left-0 sm:right-auto mt-1.5 w-48 py-1.5 bg-white dark:bg-[#131a2c] border border-slate-200 dark:border-slate-700/80 rounded-2xl shadow-2xl z-40 overflow-hidden">
                                                
                                                <div class="px-3.5 py-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 dark:border-slate-800">
                                                    Select Timeline Month
                                                </div>

                                                <template x-for="m in availableMonths" :key="m.name">
                                                    <button @click="selectedWeatherMonth = m.name; monthDropdownOpen = false; $nextTick(() => lucide.createIcons());" 
                                                            class="w-full px-3.5 py-2 text-left text-xs hover:bg-indigo-50 dark:hover:bg-indigo-500/10 transition flex items-center justify-between"
                                                            :class="selectedWeatherMonth === m.name ? 'bg-indigo-50/70 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 font-bold' : 'text-slate-700 dark:text-slate-300'">
                                                        <span x-text="m.name + ' 2026'"></span>
                                                        <template x-if="selectedWeatherMonth === m.name">
                                                            <i data-lucide="check" class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400 shrink-0"></i>
                                                        </template>
                                                    </button>
                                                </template>
                                            </div>
                                        </div>
                                    </template>

                                    <!-- Data Source API Dropdown -->
                                    <div class="relative" x-data="{ sourceDropdownOpen: false }">
                                        <button @click="sourceDropdownOpen = !sourceDropdownOpen; $nextTick(() => lucide.createIcons());" 
                                                class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800/90 hover:bg-slate-200/80 dark:hover:bg-slate-800 border border-slate-200/80 dark:border-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-200 transition shadow-sm">
                                            <div class="w-2 h-2 rounded-full animate-pulse" :class="selectedWeatherSource === 'pagasa' ? 'bg-sky-500' : 'bg-emerald-500'"></div>
                                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider hidden sm:inline">Source:</span>
                                            <span class="font-bold" x-text="selectedWeatherSource === 'pagasa' ? 'DOST-PAGASA' : 'Open-Meteo'"></span>
                                            <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': sourceDropdownOpen }"></i>
                                        </button>

                                        <div x-show="sourceDropdownOpen" @click.away="sourceDropdownOpen = false" x-cloak
                                             x-transition:enter="transition ease-out duration-150"
                                             x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                                             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                             x-transition:leave="transition ease-in duration-100"
                                             x-transition:leave-start="opacity-100 scale-100"
                                             x-transition:leave-end="opacity-0 scale-95"
                                             class="absolute right-0 mt-1.5 w-64 py-1.5 bg-white dark:bg-[#131a2c] border border-slate-200 dark:border-slate-700/80 rounded-2xl shadow-2xl z-40 overflow-hidden">
                                            
                                            <div class="px-3.5 py-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                                                <span>Weather Data Source</span>
                                                <span class="text-[9px] text-emerald-500 font-mono">Live API</span>
                                            </div>

                                            <!-- Open-Meteo Option -->
                                            <button @click="switchWeatherSource('open-meteo'); sourceDropdownOpen = false;" 
                                                    class="w-full px-3.5 py-2.5 text-left text-xs hover:bg-indigo-50 dark:hover:bg-indigo-500/10 transition flex items-center justify-between"
                                                    :class="selectedWeatherSource === 'open-meteo' ? 'bg-indigo-50/70 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 font-bold' : 'text-slate-700 dark:text-slate-300'">
                                                <div class="flex items-center gap-2.5 min-w-0">
                                                    <div class="w-6 h-6 rounded-lg bg-emerald-500/10 text-emerald-600 flex items-center justify-center shrink-0">
                                                        <i data-lucide="cloud" class="w-3.5 h-3.5"></i>
                                                    </div>
                                                    <div>
                                                        <p class="font-bold leading-tight">Open-Meteo REST API</p>
                                                        <p class="text-[10px] text-slate-400 font-normal">Global ECMWF / GFS Satellite</p>
                                                    </div>
                                                </div>
                                                <template x-if="selectedWeatherSource === 'open-meteo'">
                                                    <i data-lucide="check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                                                </template>
                                            </button>

                                            <!-- PAGASA Option -->
                                            <button @click="switchWeatherSource('pagasa'); sourceDropdownOpen = false;" 
                                                    class="w-full px-3.5 py-2.5 text-left text-xs hover:bg-indigo-50 dark:hover:bg-indigo-500/10 transition flex items-center justify-between border-t border-slate-100 dark:border-slate-800/60"
                                                    :class="selectedWeatherSource === 'pagasa' ? 'bg-indigo-50/70 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 font-bold' : 'text-slate-700 dark:text-slate-300'">
                                                <div class="flex items-center gap-2.5 min-w-0">
                                                    <div class="w-6 h-6 rounded-lg bg-sky-500/10 text-sky-600 flex items-center justify-center shrink-0">
                                                        <i data-lucide="radio" class="w-3.5 h-3.5"></i>
                                                    </div>
                                                    <div>
                                                        <p class="font-bold leading-tight">DOST-PAGASA API</p>
                                                        <p class="text-[10px] text-slate-400 font-normal">Muñoz Regional Synoptic Station</p>
                                                    </div>
                                                </div>
                                                <template x-if="selectedWeatherSource === 'pagasa'">
                                                    <i data-lucide="check" class="w-4 h-4 text-sky-500 shrink-0"></i>
                                                </template>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- DOST-PAGASA Missing API Key Error State -->
                            <template x-if="selectedWeatherSource === 'pagasa'">
                                <div class="p-6 rounded-2xl bg-rose-500/5 dark:bg-rose-500/10 border border-rose-500/20 text-center flex flex-col items-center justify-center py-8 my-1">
                                    <div class="w-12 h-12 rounded-2xl bg-rose-500/10 text-rose-500 flex items-center justify-center mb-3">
                                        <i data-lucide="cloud-off" class="w-6 h-6"></i>
                                    </div>
                                    <h4 class="text-sm font-bold text-slate-900 dark:text-white">DOST-PAGASA API Connection Error</h4>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 max-w-lg mt-1.5 leading-relaxed">
                                        Unable to fetch live meteorological data from DOST-PAGASA regional synoptic endpoint. No active API key or institutional clearance credentials are configured on the system.
                                    </p>
                                    <div class="mt-4 flex items-center gap-3 flex-wrap justify-center">
                                        <button @click="switchWeatherSource('open-meteo')" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs transition flex items-center gap-1.5 shadow-sm">
                                            <i data-lucide="cloud" class="w-3.5 h-3.5"></i>
                                            <span>Switch to Open-Meteo (Live Feed)</span>
                                        </button>
                                        <button @click="switchTab('weather-config')" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-semibold text-xs transition border border-slate-200 dark:border-slate-700">
                                            <span>Manage API Credentials</span>
                                        </button>
                                    </div>
                                </div>
                            </template>

                            <!-- VIEW 1: LIVE 7-DAY FORECAST GRID -->
                            <template x-if="selectedWeatherSource !== 'pagasa' && weatherViewMode === '7days'">
                                <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-2.5 sm:gap-3">
                                    <template x-for="day in weatherOutlook" :key="day.date">
                                        <div class="p-3.5 rounded-2xl border text-center transition-all cursor-pointer hover:scale-[1.02]"
                                             @click="highlightGanttDate(day.date); switchTab('scheduling');"
                                             :class="{
                                                'bg-slate-50 dark:bg-slate-900/60 border-slate-200/70 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700': day.risk === 'low',
                                                'bg-amber-50 dark:bg-amber-500/10 border-amber-200 dark:border-amber-500/30 hover:border-amber-300': day.risk === 'medium',
                                                'bg-rose-50 dark:bg-rose-500/10 border-rose-200 dark:border-rose-500/30 hover:border-rose-300': day.risk === 'high'
                                             }">
                                            <p class="text-[10px] sm:text-xs font-semibold text-slate-400" x-text="day.dayName"></p>
                                            <p class="text-[11px] font-bold text-slate-800 dark:text-slate-200 mt-0.5" x-text="day.date"></p>
                                            <div class="my-2.5 flex justify-center">
                                                <i :data-lucide="day.icon" class="w-6 h-6" :class="day.iconColor"></i>
                                            </div>
                                            <p class="text-xs font-bold text-slate-900 dark:text-white" x-text="day.temp"></p>
                                            <div class="mt-2 pt-2 border-t border-slate-200/70 dark:border-slate-800/80 flex items-center justify-center gap-1 text-[10px]"
                                                 :class="day.rainProb > 50 ? 'text-amber-600 dark:text-amber-300 font-bold' : 'text-slate-400 font-medium'">
                                                <i data-lucide="droplets" class="w-3 h-3"></i>
                                                <span x-text="day.rainProb + '%'"></span>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <!-- VIEW 2: 30-DAY / MONTHLY METEOROLOGICAL CALENDAR GRID -->
                            <template x-if="selectedWeatherSource !== 'pagasa' && weatherViewMode === 'monthly'">
                                <div class="space-y-4">
                                    <!-- Monthly Risk & Workability Summary Stats -->
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200/70 dark:border-slate-800">
                                        <div class="flex items-center gap-2.5 px-2">
                                            <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center shrink-0">
                                                <i data-lucide="check-circle" class="w-4 h-4"></i>
                                            </div>
                                            <div>
                                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Workable Weather Days</p>
                                                <p class="text-xs sm:text-sm font-extrabold text-emerald-600 dark:text-emerald-400" x-text="getMonthlyWorkableCount() + ' Days (Feasible)'"></p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2.5 px-2 border-t sm:border-t-0 sm:border-l border-slate-200/70 dark:border-slate-800 pt-2 sm:pt-0">
                                            <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center shrink-0">
                                                <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                                            </div>
                                            <div>
                                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Rain Risk Window</p>
                                                <p class="text-xs sm:text-sm font-extrabold text-amber-600 dark:text-amber-400" x-text="getMonthlyAlertCount() + ' Days (Precautionary)'"></p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2.5 px-2 border-t sm:border-t-0 sm:border-l border-slate-200/70 dark:border-slate-800 pt-2 sm:pt-0">
                                            <div class="w-8 h-8 rounded-xl bg-indigo-500/10 text-indigo-600 flex items-center justify-center shrink-0">
                                                <i data-lucide="map-pin" class="w-4 h-4"></i>
                                            </div>
                                            <div>
                                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Target Region</p>
                                                <p class="text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-300 truncate">Science City of Muñoz</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 30-Day Calendar Matrix -->
                                    <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-7 gap-2">
                                        <template x-for="day in getMonthlyWeatherDays()" :key="day.date">
                                            <div class="p-2.5 rounded-xl border transition-all cursor-pointer hover:scale-[1.03] hover:shadow-md flex flex-col justify-between"
                                                 @click="highlightGanttDate(day.date); switchTab('scheduling');"
                                                 :class="{
                                                    'bg-white dark:bg-[#131a2c] border-slate-200/80 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700': day.risk === 'low',
                                                    'bg-amber-500/5 border-amber-500/30 hover:border-amber-500/60': day.risk === 'medium',
                                                    'bg-rose-500/5 border-rose-500/30 hover:border-rose-500/60': day.risk === 'high'
                                                 }">
                                                <div class="flex items-center justify-between">
                                                    <span class="text-[10px] font-extrabold text-slate-700 dark:text-slate-300" x-text="day.dayNumber"></span>
                                                    <span class="text-[9px] font-bold text-slate-400" x-text="day.dayName"></span>
                                                </div>
                                                <div class="my-2 flex items-center justify-center gap-1.5">
                                                    <i :data-lucide="day.icon" class="w-4 h-4" :class="day.iconColor"></i>
                                                    <span class="text-[11px] font-bold text-slate-900 dark:text-white" x-text="day.temp"></span>
                                                </div>
                                                <div class="pt-1.5 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between text-[9px]">
                                                    <span class="font-bold flex items-center gap-0.5" :class="day.rainProb > 40 ? 'text-amber-500 font-extrabold' : 'text-slate-400'">
                                                        <i data-lucide="droplets" class="w-2.5 h-2.5"></i>
                                                        <span x-text="day.rainProb + '%'"></span>
                                                    </span>
                                                    <span class="px-1 py-0.2 rounded font-bold uppercase tracking-wider text-[8px]"
                                                          :class="{
                                                            'text-emerald-500': day.risk === 'low',
                                                            'text-amber-500': day.risk === 'medium',
                                                            'text-rose-500': day.risk === 'high'
                                                          }"
                                                          x-text="day.risk === 'low' ? 'Feasible' : (day.risk === 'medium' ? 'Watch' : 'Alert')"></span>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- S-Curve Visualization Area -->
                        <div class="dribbble-card p-5 sm:p-6">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4 sm:mb-6">
                                <div>
                                    <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-lg bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center">
                                            <i data-lucide="line-chart" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                                        </div>
                                        <span>Planned vs. Actual S-Curve Progress</span>
                                    </h3>
                                    <p class="text-xs text-slate-400 mt-0.5 font-medium">Cumulative progress variance across the 12-week construction milestone baseline.</p>
                                </div>
                                <div class="flex items-center gap-3 text-xs" x-show="progressData && progressData.length > 0">
                                    <span class="flex items-center gap-1.5 text-indigo-600 dark:text-sky-400 font-semibold"><span class="w-3 h-0.5 bg-indigo-600 dark:bg-sky-400 inline-block"></span> Planned</span>
                                    <span class="flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400 font-semibold"><span class="w-3 h-0.5 bg-emerald-500 dark:bg-emerald-400 inline-block"></span> Actual</span>
                                </div>
                            </div>
                            
                            <template x-if="!progressData || progressData.length === 0">
                                <div class="h-64 sm:h-72 w-full flex flex-col items-center justify-center p-6 text-center border-2 border-dashed border-slate-200/80 dark:border-slate-800 rounded-2xl bg-slate-50/50 dark:bg-slate-900/30">
                                    <div class="w-11 h-11 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mb-2.5">
                                        <i data-lucide="line-chart" class="w-5 h-5"></i>
                                    </div>
                                    <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200">No Construction Progress Yet</h4>
                                    <p class="text-xs text-slate-400 max-w-sm mt-1 leading-relaxed">
                                        This project does not have active construction activities or recorded accomplishments yet.
                                    </p>
                                    <button @click="switchTab('scheduling')" class="mt-3.5 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-sm active:scale-95">
                                        <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
                                        <span>Add Construction Activity</span>
                                    </button>
                                </div>
                            </template>

                            <div x-show="progressData && progressData.length > 0" class="h-64 sm:h-80 w-full relative">
                                <canvas id="sCurveChart"></canvas>
                            </div>
                        </div>

                    </section>

                    <!-- TAB: PREDEFINED CONSTRUCTION ACTIVITY DATABASE (THESIS SECTION 1.1) -->
                    <section x-show="activeTab === 'activity-library'" x-cloak class="space-y-6">
                        
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div>
                                <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
                                    <span>Construction Activities</span>
                                </h2>
                                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1 font-medium">
                                    Predefined construction activity catalog with automated weather threshold limits & OSHA safety rules.
                                </p>
                            </div>
                            
                            <div class="flex items-center gap-3" x-show="hasPermission('manage_master_activities')">
                                <button @click="openNewMasterActivityModal()" 
                                        class="flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition shadow-md shadow-indigo-500/20 active:scale-95">
                                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                                    <span>Register New Activity</span>
                                </button>
                            </div>
                        </div>

                        <!-- Category Filter Pills & Search Bar -->
                        <div class="dribbble-card p-4 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
                            <div class="flex items-center gap-2 flex-1 max-w-md">
                                <div class="relative w-full">
                                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                                    <input type="text" x-model="activityLibrarySearch" placeholder="Search activities, codes, or rules..."
                                           class="w-full pl-9 pr-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs font-semibold text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <button x-show="activityLibrarySearch" @click="activityLibrarySearch = ''" class="p-2 rounded-lg text-slate-400 hover:text-slate-600">
                                    <i data-lucide="x" class="w-4 h-4"></i>
                                </button>
                            </div>

                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-[11px] font-bold text-slate-400 uppercase mr-1 hidden sm:inline">Category:</span>
                                <template x-for="cat in ['ALL', 'Structural', 'Heavy Equipment', 'Enclosure', 'Substructure', 'Finishes']" :key="'cat-' + cat">
                                    <button @click="activityCategoryFilter = cat"
                                            class="px-3 py-1.5 rounded-xl text-xs font-bold transition"
                                            :class="activityCategoryFilter === cat ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800/80 text-slate-600 dark:text-slate-300 hover:bg-slate-200'">
                                        <span x-text="cat === 'ALL' ? 'All Activities' : cat"></span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <!-- Activity Cards Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                            <template x-for="item in filteredMasterActivities()" :key="'lib-' + item.id">
                                <div class="dribbble-card p-5 relative flex flex-col justify-between group hover:shadow-md transition-all border border-slate-200/80 dark:border-slate-800">
                                    
                                    <div>
                                        <!-- Card Header -->
                                        <div class="flex items-start justify-between gap-3 mb-3">
                                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500/10 to-sky-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-sm shrink-0 border border-indigo-200/60 dark:border-indigo-500/20">
                                                <i data-lucide="hammer" class="w-5 h-5"></i>
                                            </div>
                                            <div class="flex items-center gap-1.5">
                                                <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300 border border-indigo-200/50" x-text="item.category"></span>
                                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400" x-text="item.code"></span>
                                            </div>
                                        </div>

                                        <!-- Title & Description -->
                                        <h3 class="font-bold text-slate-900 dark:text-white text-base leading-snug group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition" x-text="item.name"></h3>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5 leading-relaxed line-clamp-2" x-text="item.description"></p>

                                        <!-- Predecessor Hint -->
                                        <div class="flex items-center gap-2 mt-3 text-[11px] text-slate-500 dark:text-slate-400">
                                            <i data-lucide="git-branch" class="w-3.5 h-3.5 text-indigo-500 shrink-0"></i>
                                            <span>Default Predecessor: <strong class="text-slate-700 dark:text-slate-200" x-text="item.predecessorHint"></strong></span>
                                        </div>

                                        <!-- Weather Threshold Matrix Box -->
                                        <div class="mt-4 p-3 rounded-2xl bg-slate-50 dark:bg-slate-900/70 border border-slate-200/70 dark:border-slate-800 space-y-2">
                                            <div class="flex items-center justify-between text-[10px] font-bold uppercase text-slate-400">
                                                <span>Weather Assessment Rules</span>
                                                <span class="text-indigo-600 dark:text-indigo-400" x-text="item.defaultDuration + ' Days Default'"></span>
                                            </div>

                                            <div class="grid grid-cols-3 gap-2 text-center pt-1">
                                                <div class="p-2 rounded-xl bg-white dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700">
                                                    <p class="text-[9px] font-bold text-slate-400 uppercase">Max Rain %</p>
                                                    <p class="text-xs font-extrabold text-amber-600 dark:text-amber-400" x-text="'< ' + item.maxRainProb + '%'"></p>
                                                </div>
                                                <div class="p-2 rounded-xl bg-white dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700">
                                                    <p class="text-[9px] font-bold text-slate-400 uppercase">Max Volume</p>
                                                    <p class="text-xs font-extrabold text-slate-800 dark:text-slate-200" x-text="'< ' + item.maxRainVol + 'mm'"></p>
                                                </div>
                                                <div class="p-2 rounded-xl bg-white dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700">
                                                    <p class="text-[9px] font-bold text-slate-400 uppercase">Max Wind</p>
                                                    <p class="text-xs font-extrabold text-slate-800 dark:text-slate-200" x-text="'< ' + item.maxWind + 'km/h'"></p>
                                                </div>
                                            </div>

                                            <!-- Safety Trigger -->
                                            <div class="flex items-center gap-1.5 pt-1 text-[10px] text-rose-600 dark:text-rose-400 font-semibold">
                                                <i data-lucide="shield-alert" class="w-3.5 h-3.5 shrink-0"></i>
                                                <span class="truncate" x-text="'Safety: ' + item.safetyTrigger"></span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Card Actions -->
                                    <div class="flex items-center justify-between gap-2 mt-5 pt-3 border-t border-slate-100 dark:border-slate-800/80">
                                        <button x-show="hasPermission('add_gantt_activity')" @click="scheduleLibraryActivityDirectly(item)" 
                                                class="flex-1 py-2 px-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-sm active:scale-95">
                                            <i data-lucide="calendar-plus" class="w-3.5 h-3.5"></i>
                                            <span>Apply to Gantt</span>
                                        </button>

                                        <button x-show="hasPermission('manage_master_activities')" @click="editMasterActivity(item)" class="p-2 rounded-xl text-slate-400 hover:text-indigo-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition" title="Edit Threshold Rules">
                                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                                        </button>

                                        <button x-show="hasPermission('manage_master_activities')" @click="deleteMasterActivity(item.id)" class="p-2 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition" title="Delete Template">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>

                                </div>
                            </template>
                        </div>

                        <!-- Empty State -->
                        <template x-if="filteredMasterActivities().length === 0">
                            <div class="dribbble-card p-12 text-center">
                                <div class="max-w-sm mx-auto flex flex-col items-center justify-center text-center space-y-3">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center">
                                        <i data-lucide="search-x" class="w-6 h-6"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-slate-800 dark:text-slate-200">No matching activities found</p>
                                        <p class="text-xs text-slate-400 mt-1">Try resetting the category filter or changing your search term.</p>
                                    </div>
                                    <button @click="activityCategoryFilter = 'ALL'; activityLibrarySearch = '';"
                                            class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm transition">
                                        Reset Activity Filters
                                    </button>
                                </div>
                            </div>
                        </template>

                    </section>

                    <!-- TAB 2: INTERACTIVE SCHEDULING & WEATHER-RESPONSIVE GANTT -->
                    <section x-show="activeTab === 'scheduling'" x-cloak class="space-y-6">
                        
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div>
                                <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
                                    <span>Weather-Responsive Construction Gantt</span>
                                </h2>
                                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1 font-medium">
                                    Baseline vs. Approved schedules evaluated against active weather forecast thresholds.
                                </p>
                            </div>
                            
                            <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
                                <div class="flex items-center gap-3 px-3 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs shadow-sm">
                                    <span class="flex items-center gap-1.5 text-slate-600 dark:text-slate-300 font-medium">
                                        <span class="w-3 h-2 rounded bg-slate-300 dark:bg-slate-600 inline-block border border-dashed border-slate-400"></span> Baseline
                                    </span>
                                    <span class="flex items-center gap-1.5 text-indigo-600 dark:text-indigo-400 font-semibold">
                                        <span class="w-3 h-2.5 rounded bg-indigo-600 inline-block shadow-sm"></span> Current / Approved
                                    </span>
                                    <span class="flex items-center gap-1.5 text-amber-600 dark:text-amber-400 font-semibold">
                                        <span class="w-3 h-2.5 rounded bg-amber-500 inline-block animate-pulse"></span> Weather Alert
                                    </span>
                                </div>

                                <button @click="highlightGanttDate(todayDate)" x-show="highlightedDate !== todayDate" 
                                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 hover:bg-indigo-100 dark:hover:bg-indigo-500/20 text-xs font-semibold text-indigo-700 dark:text-indigo-300 transition border border-indigo-200 dark:border-indigo-500/20">
                                    <i data-lucide="crosshair" class="w-3.5 h-3.5"></i>
                                    <span>Today</span>
                                </button>

                                <button @click="resetHighlightedDate()" x-show="highlightedDate" 
                                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 text-xs font-semibold text-slate-700 dark:text-slate-200 transition">
                                    <i data-lucide="x-circle" class="w-3.5 h-3.5"></i>
                                    <span>Clear Highlight</span>
                                </button>

                                <button x-show="hasPermission('add_gantt_activity')" @click="openNewActivityModal()" 
                                        class="flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition shadow-sm active:scale-95">
                                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                    <span>Add Activity</span>
                                </button>
                            </div>
                        </div>

                        <!-- GANTT CONTAINER -->
                        <div class="dribbble-card overflow-hidden">
                            
                            <div class="p-4 px-6 border-b border-slate-200/80 dark:border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-3 bg-slate-50/50 dark:bg-slate-900/40">
                                <div class="flex items-center gap-3 flex-wrap">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                                            <i data-lucide="calendar" class="w-4 h-4"></i>
                                        </div>
                                        <div>
                                            <span class="text-xs font-bold text-slate-800 dark:text-white block" x-text="selectedGanttMonth + ' 2026 Milestone View'"></span>
                                            <span class="text-[10px] text-slate-400 font-medium" x-text="ganttViewMode === 'month' ? (selectedGanttWeek === 'all' ? 'Full Month (30-Day Gantt View)' : 'Showing ' + selectedGanttWeek) : (selectedGanttWeek === 'all' ? '7-Day Focused Outlook' : 'Showing ' + selectedGanttWeek)"></span>
                                        </div>
                                    </div>
                                    
                                    <span class="hidden sm:inline text-slate-300 dark:text-slate-700">|</span>

                                    <!-- View Mode Toggle: 7-Day vs Full Month (30 Days) -->
                                    <div class="flex items-center bg-slate-200/70 dark:bg-slate-800 p-1 rounded-xl gap-1 text-xs">
                                        <button @click="setGanttViewMode('7days')" 
                                                class="px-2.5 py-1 rounded-lg font-bold transition text-[11px] flex items-center gap-1.5"
                                                :class="ganttViewMode === '7days' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'">
                                            <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                                            <span>7-Day Focused</span>
                                        </button>
                                        <button @click="setGanttViewMode('month')" 
                                                class="px-2.5 py-1 rounded-lg font-bold transition text-[11px] flex items-center gap-1.5"
                                                :class="ganttViewMode === 'month' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'">
                                            <i data-lucide="calendar-days" class="w-3.5 h-3.5"></i>
                                            <span>Full Month (30 Days)</span>
                                        </button>
                                    </div>

                                    <span class="hidden sm:inline text-slate-300 dark:text-slate-700">|</span>

                                    <!-- Month Toggle Pills -->
                                    <div class="flex items-center bg-slate-200/70 dark:bg-slate-800 p-1 rounded-xl gap-1 text-xs">
                                        <template x-for="m in availableMonths" :key="m.name">
                                            <button @click="switchGanttMonth(m.name)" 
                                                    class="px-3 py-1 rounded-lg font-bold transition text-[11px]"
                                                    :class="selectedGanttMonth === m.name ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'">
                                                <span x-text="m.label"></span>
                                            </button>
                                        </template>
                                    </div>

                                    <!-- Week Quick Filter -->
                                    <div class="flex items-center bg-slate-200/70 dark:bg-slate-800 p-1 rounded-xl gap-1 text-xs">
                                        <button @click="filterGanttWeek('all')" 
                                                class="px-2.5 py-1 rounded-lg font-bold transition text-[11px]"
                                                :class="selectedGanttWeek === 'all' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'">
                                            <span x-text="ganttViewMode === 'month' ? 'All Days' : 'Week 1 (Days 1–7)'"></span>
                                        </button>
                                        <button @click="filterGanttWeek('Week 1')" 
                                                class="px-2 py-1 rounded-lg font-bold transition text-[11px]"
                                                :class="selectedGanttWeek === 'Week 1' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'">
                                            Wk 1
                                        </button>
                                        <button @click="filterGanttWeek('Week 2')" 
                                                class="px-2 py-1 rounded-lg font-bold transition text-[11px]"
                                                :class="selectedGanttWeek === 'Week 2' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'">
                                            Wk 2
                                        </button>
                                        <button @click="filterGanttWeek('Week 3')" 
                                                class="px-2 py-1 rounded-lg font-bold transition text-[11px]"
                                                :class="selectedGanttWeek === 'Week 3' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'">
                                            Wk 3
                                        </button>
                                        <button @click="filterGanttWeek('Week 4')" 
                                                class="px-2 py-1 rounded-lg font-bold transition text-[11px]"
                                                :class="selectedGanttWeek === 'Week 4' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'">
                                            Wk 4
                                        </button>
                                        <button @click="filterGanttWeek('Week 5')" 
                                                class="px-2 py-1 rounded-lg font-bold transition text-[11px]"
                                                :class="selectedGanttWeek === 'Week 5' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'">
                                            Wk 5
                                        </button>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <button @click="navigateGanttPrev()" class="p-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-indigo-600 transition" :title="ganttViewMode === 'month' ? 'Previous Month' : 'Previous Week'">
                                        <i data-lucide="chevron-left" class="w-4 h-4"></i>
                                    </button>
                                    <div class="text-center px-1">
                                        <span class="text-[11px] font-bold text-slate-700 dark:text-slate-200 block whitespace-nowrap" x-text="getGanttNavLabel()"></span>
                                        <span class="text-[9px] text-slate-400 font-semibold block" x-text="ganttDates.length > 0 ? (ganttDates[0].date + ' – ' + ganttDates[ganttDates.length - 1].date) : ''"></span>
                                    </div>
                                    <button @click="navigateGanttNext()" class="p-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-indigo-600 transition" :title="ganttViewMode === 'month' ? 'Next Month' : 'Next Week'">
                                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Forecast Horizon & Confidence Legend Banner -->
                            <div class="px-6 py-2 bg-indigo-50/70 dark:bg-indigo-950/30 border-b border-indigo-100 dark:border-indigo-900/40 flex flex-wrap items-center justify-between text-[11px] text-slate-600 dark:text-slate-300">
                                <div class="flex items-center gap-4 flex-wrap">
                                    <span class="flex items-center gap-1.5 font-bold text-indigo-700 dark:text-indigo-300">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block animate-pulse"></span>
                                        <span>Active Forecast Zone (Days 1–14): Live Risk & Weather Triggers</span>
                                    </span>
                                    <span class="flex items-center gap-1.5 font-medium text-slate-500 dark:text-slate-400">
                                        <span class="w-2 h-2 rounded-full bg-indigo-400 inline-block"></span>
                                        <span>Extended Horizon (Days 15–31): Baseline Trajectory</span>
                                    </span>
                                    <span class="flex items-center gap-1.5 font-semibold text-sky-700 dark:text-sky-400">
                                        <span class="w-2 h-2 rounded-full bg-sky-500 inline-block"></span>
                                        <span>Continued Same Day (On-Site Mitigation Applied)</span>
                                    </span>
                                </div>
                                <div class="flex items-center gap-2 font-medium text-slate-500 dark:text-slate-400 text-[10px]">
                                    <i data-lucide="info" class="w-3.5 h-3.5 text-indigo-500"></i>
                                    <span>Click activity row for mitigation rules</span>
                                </div>
                            </div>

                            <div class="overflow-x-auto">
                                <div :class="ganttDates.length > 14 ? 'min-w-[1450px]' : 'min-w-[1050px]'">
                                    
                                    <div class="grid grid-cols-12 bg-slate-100/90 dark:bg-slate-900/90 border-b border-slate-200 dark:border-slate-800 text-center items-stretch">
                                        <div class="col-span-4 px-6 py-3.5 text-left flex items-center justify-between">
                                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Construction Activities</span>
                                            <span class="text-[10px] text-indigo-600 dark:text-indigo-400 font-bold px-2.5 py-1 rounded-md bg-indigo-50 dark:bg-indigo-500/10">Forecast Timeline</span>
                                        </div>
                                        
                                        <div class="col-span-8 grid gap-0 items-stretch" :style="`grid-template-columns: repeat(${ganttDates.length}, minmax(0, 1fr));`">
                                            <template x-for="day in ganttDates" :key="day.date">
                                                <div @click="highlightGanttDate(day.date)"
                                                     class="px-1 py-2.5 transition-all cursor-pointer flex flex-col items-center justify-center group relative border-r border-slate-200/60 dark:border-slate-800/80"
                                                     :class="{
                                                        'gantt-col-highlight shadow-xs': highlightedDate === day.date,
                                                        'hover:bg-slate-200/70 dark:hover:bg-slate-800': highlightedDate !== day.date && !isToday(day.date),
                                                        'opacity-80 bg-slate-50/40 dark:bg-slate-900/30': day.isExtended
                                                     }">
                                                    <div class="flex items-center gap-1">
                                                        <span class="text-[9px] font-bold uppercase" :class="highlightedDate === day.date ? 'text-indigo-600 dark:text-indigo-400 font-extrabold' : (day.isExtended ? 'text-slate-400' : 'text-slate-500')" x-text="day.dayName"></span>
                                                        <span x-show="isToday(day.date)" class="px-1 py-0.2 rounded text-[7px] font-black uppercase bg-indigo-600 text-white leading-tight shadow-xs tracking-tighter">Today</span>
                                                    </div>
                                                    <span class="text-[11px] font-bold" :class="highlightedDate === day.date ? 'text-indigo-600 dark:text-indigo-300 font-extrabold' : (isToday(day.date) ? 'text-indigo-600 dark:text-indigo-400 font-extrabold' : 'text-slate-700 dark:text-slate-200')" x-text="day.date"></span>
                                                    
                                                    <div class="mt-1 p-1 rounded-lg transition-transform group-hover:scale-110 flex items-center justify-center"
                                                         :class="{
                                                            'bg-emerald-500/10 text-emerald-500': !day.isExtended && day.risk === 'low',
                                                            'bg-amber-500/15 text-amber-500 animate-pulse': !day.isExtended && day.risk === 'medium',
                                                            'bg-rose-500/20 text-rose-500 animate-bounce': !day.isExtended && day.risk === 'high',
                                                            'bg-indigo-500/10 text-indigo-400': day.isExtended
                                                         }">
                                                        <i :data-lucide="day.icon" class="w-3.5 h-3.5"></i>
                                                    </div>

                                                    <span class="text-[8px] font-bold mt-0.5"
                                                          :class="day.isExtended ? 'text-slate-400' : (day.rainProb > 50 ? 'text-amber-600 dark:text-amber-400' : 'text-slate-400')"
                                                          x-text="day.isExtended ? 'Plan' : day.rainProb + '%'"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <div class="divide-y divide-slate-100 dark:divide-slate-800/80 text-xs">
                                        <template x-for="act in activities" :key="act.id">
                                            <div class="grid grid-cols-12 items-stretch hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors group cursor-pointer"
                                                 @click="openWeatherDrawer(act)"
                                                 :class="{ 'bg-amber-50/30 dark:bg-amber-500/5': act.hasAlert }">
                                                
                                                <div class="col-span-4 px-6 py-4.5 flex items-center justify-between gap-4 border-r border-slate-100 dark:border-slate-800/60">
                                                    <div class="min-w-0 pr-2">
                                                         <div class="flex items-center gap-2 flex-wrap">
                                                             <span class="font-bold text-sm text-slate-800 dark:text-slate-100 truncate" x-text="act.name"></span>
                                                             <span x-show="act.hasAlert" class="px-1.5 py-0.5 text-[9px] font-extrabold rounded bg-amber-500 text-white animate-pulse">WEATHER ALERT</span>
                                                             <span x-show="act.continuedSameDay" class="px-2 py-0.5 text-[9px] font-extrabold rounded bg-sky-500/15 text-sky-600 dark:text-sky-400 border border-sky-300 dark:border-sky-500/30 flex items-center gap-1 shadow-2xs">
                                                                 <i data-lucide="shield-check" class="w-3 h-3 text-sky-500"></i>
                                                                 <span>CONTINUED SAME DAY</span>
                                                             </span>
                                                         </div>
                                                         <div class="flex items-center gap-2 text-xs text-slate-400 mt-1 font-medium flex-wrap">
                                                             <span x-text="'Predecessor: ' + (act.predecessor || 'None')"></span>
                                                             <span>•</span>
                                                             <span x-text="'Schedule: ' + act.currentStart + ' - ' + act.currentEnd"></span>
                                                             <template x-if="act.continuedSameDay">
                                                                 <span class="text-sky-600 dark:text-sky-400 font-semibold flex items-center gap-1">
                                                                     <span>•</span>
                                                                     <i data-lucide="clock" class="w-3 h-3"></i>
                                                                     <span x-text="'Resume: ' + (act.resumeTime || '13:00')"></span>
                                                                 </span>
                                                             </template>
                                                         </div>
                                                    </div>

                                                    <div class="flex items-center gap-2 shrink-0">
                                                        <button @click.stop="openWeatherDrawer(act)" 
                                                                class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-xs"
                                                                :class="{
                                                                    'bg-sky-50 text-sky-700 dark:bg-sky-500/10 dark:text-sky-400 border border-sky-300 dark:border-sky-500/30': act.continuedSameDay,
                                                                    'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20': !act.continuedSameDay && act.feasibility === 'Feasible',
                                                                    'bg-amber-100 text-amber-800 dark:bg-amber-500/20 dark:text-amber-300 border border-amber-300 dark:border-amber-500/40 hover:bg-amber-200': !act.continuedSameDay && act.feasibility === 'Conditionally Feasible',
                                                                    'bg-rose-100 text-rose-800 dark:bg-rose-500/20 dark:text-rose-300 border border-rose-300 dark:border-rose-500/40': !act.continuedSameDay && act.feasibility === 'Infeasible'
                                                                }">
                                                            <i :data-lucide="act.continuedSameDay ? 'shield-check' : (act.feasibility === 'Feasible' ? 'check-circle' : 'alert-circle')" class="w-3.5 h-3.5"></i>
                                                            <span x-text="act.continuedSameDay ? 'Mitigated' : act.feasibility"></span>
                                                        </button>
                                                        <button x-show="hasPermission('add_gantt_activity')" @click.stop="deleteScheduleActivity(act.id)" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition" title="Delete from Gantt schedule">
                                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                        </button>
                                                    </div>
                                                </div>

                                                <div class="col-span-8 grid gap-0 relative items-center self-stretch min-h-[76px]" :style="`grid-template-columns: repeat(${ganttDates.length}, minmax(0, 1fr));`">
                                                    <template x-for="day in ganttDates" :key="'grid-' + day.date">
                                                        <div class="h-full border-r border-slate-100 dark:border-slate-800/60 transition-colors"
                                                             :class="{
                                                                'gantt-col-highlight': highlightedDate === day.date,
                                                                'bg-indigo-500/[0.04] dark:bg-indigo-400/[0.03] border-indigo-300/40 dark:border-indigo-500/30': isToday(day.date) && highlightedDate !== day.date
                                                             }">
                                                        </div>
                                                    </template>

                                                    <div class="absolute inset-x-0 flex flex-col justify-center gap-2 px-3 pointer-events-none">
                                                        <div class="h-2.5 rounded border border-dashed border-slate-400 dark:border-slate-500 bg-slate-300/40 dark:bg-slate-700/40 relative pointer-events-auto cursor-help"
                                                             :style="getGanttBarStyle(act, true)"
                                                             :title="`Baseline Schedule: ${act.baselineStart} to ${act.baselineEnd}`">
                                                        </div>

                                                        <div class="h-6.5 rounded-lg flex items-center justify-between px-3 text-white font-semibold shadow-sm transition-all pointer-events-auto cursor-pointer hover:brightness-110 hover:shadow-md"
                                                             :style="getGanttBarStyle(act, false)"
                                                             :class="{
                                                                'bg-gradient-to-r from-sky-600 via-indigo-600 to-indigo-500 ring-1 ring-sky-400/50 shadow-sky-500/10': act.continuedSameDay,
                                                                'bg-gradient-to-r from-indigo-600 to-indigo-500': !act.hasAlert && !act.continuedSameDay,
                                                                'bg-gradient-to-r from-amber-500 to-amber-600 ring-2 ring-amber-400/80 animate-pulse': act.hasAlert
                                                             }">
                                                            <div class="flex items-center gap-1.5 truncate">
                                                                <i x-show="act.continuedSameDay" data-lucide="shield-check" class="w-3.5 h-3.5 text-sky-200 shrink-0"></i>
                                                                <span class="truncate text-xs font-semibold" x-text="act.name"></span>
                                                            </div>
                                                            <div class="flex items-center gap-1.5 ml-2 shrink-0">
                                                                <span x-show="act.continuedSameDay" class="text-[9px] font-bold bg-white/20 px-1.5 py-0.2 rounded tracking-tight" x-text="'Resumed ' + (act.resumeTime || '13:00')"></span>
                                                                <span class="text-[10px] opacity-90 font-bold" x-text="act.durationDays + 'd'"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </template>
                                    </div>

                                </div>
                            </div>

                        </div>

                    </section>

                    <!-- TAB 3: DATE-BASED PROGRESS MONITORING -->
                    <section x-show="activeTab === 'progress'" x-cloak class="space-y-6">
                        
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div>
                                <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
                                    <span>Date-Based Progress Monitoring</span>
                                </h2>
                                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1 font-medium">
                                    3-Shade cumulative progress tracking automatically linked with the Current/Approved schedule.
                                </p>
                            </div>

                            <div class="flex items-center gap-3 flex-wrap">
                                <div class="flex items-center gap-3 px-3.5 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs shadow-sm font-medium">
                                    <span class="flex items-center gap-1.5 text-slate-600 dark:text-slate-300">
                                        <span class="w-3.5 h-3 rounded progress-shade-planned inline-block"></span> Light = Planned
                                    </span>
                                    <span class="flex items-center gap-1.5 text-indigo-600 dark:text-indigo-400 font-bold">
                                        <span class="w-3.5 h-3 rounded progress-shade-actual inline-block"></span> Dark = Actual
                                    </span>
                                    <span class="flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400 font-bold">
                                        <span class="w-3.5 h-3 rounded progress-shade-recovered inline-block"></span> Medium = Recovered Variance
                                    </span>
                                </div>

                                <button x-show="hasPermission('edit_progress')" @click="openProgressEntryModal()" 
                                        class="flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition shadow-md shadow-indigo-500/20 active:scale-95">
                                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                                    <span>Update Daily Progress</span>
                                </button>
                            </div>
                        </div>

                        <!-- 3-Shade Progress Table -->
                        <div class="dribbble-card overflow-hidden">
                            <div class="p-4 px-6 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/40">
                                <div class="flex items-center gap-3 text-xs">
                                    <span class="font-bold text-slate-800 dark:text-white flex items-center gap-2">
                                        <i data-lucide="layers" class="w-4 h-4 text-emerald-500"></i>
                                        <span>Cumulative Accomplishment Tracker</span>
                                    </span>
                                    <span class="text-slate-300 dark:text-slate-700">|</span>
                                    <span class="text-slate-500 dark:text-slate-400">Hover over bars to inspect detailed Planned %, Actual %, and Variance %</span>
                                </div>

                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                                          :class="calculateOverallVariance() >= 0 ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400' : 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400'"
                                          x-text="'Overall Status: ' + getProjectStatus()"></span>
                                </div>
                            </div>

                            <div class="overflow-x-auto">
                                <div class="min-w-[1050px]">
                                    <div class="grid grid-cols-12 bg-slate-100/90 dark:bg-slate-900/90 border-b border-slate-200 dark:border-slate-800 text-center py-2.5">
                                        <div class="col-span-4 px-4 text-left">
                                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Active Task Progress</span>
                                        </div>
                                        <div class="col-span-8 grid gap-0" :style="`grid-template-columns: repeat(${ganttDates.length}, minmax(0, 1fr));`">
                                            <template x-for="day in ganttDates" :key="'prog-head-' + day.date">
                                                <div class="px-1 py-1 text-center">
                                                    <p class="text-[10px] font-bold uppercase text-slate-400" x-text="day.dayName"></p>
                                                    <p class="text-xs font-bold text-slate-700 dark:text-slate-200" x-text="day.date"></p>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <div class="divide-y divide-slate-100 dark:divide-slate-800/80 text-xs">
                                        <template x-for="item in progressData" :key="item.id">
                                            <div class="grid grid-cols-12 items-center hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors py-4 px-0">
                                                <div class="col-span-4 px-4 flex items-center justify-between gap-3">
                                                    <div class="min-w-0">
                                                        <p class="font-bold text-slate-800 dark:text-slate-200 truncate" x-text="item.name"></p>
                                                        <div class="flex items-center gap-2 text-[11px] text-slate-400 mt-0.5">
                                                            <span>Planned: <strong class="text-slate-700 dark:text-slate-300" x-text="item.plannedProgress + '%'"></strong></span>
                                                            <span>•</span>
                                                            <span>Actual: <strong class="text-indigo-600 dark:text-indigo-400" x-text="item.actualProgress + '%'"></strong></span>
                                                        </div>
                                                    </div>

                                                    <div class="shrink-0 text-right">
                                                        <span class="px-2 py-1 rounded-lg text-[10px] font-extrabold"
                                                              :class="{
                                                                'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400': item.variance >= 0,
                                                                'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400': item.variance < 0 && item.variance > -5,
                                                                'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400': item.variance <= -5
                                                              }"
                                                              x-text="(item.variance >= 0 ? '+' : '') + item.variance + '%'"></span>
                                                        <p class="text-[9px] text-slate-400 mt-0.5" x-text="item.status"></p>
                                                    </div>
                                                </div>

                                                <div class="col-span-8 grid gap-0 h-12 relative items-center overflow-hidden" :style="`grid-template-columns: repeat(${ganttDates.length}, minmax(0, 1fr));`">
                                                    <template x-for="day in ganttDates" :key="'prog-grid-' + day.date">
                                                        <div class="h-full border-r border-slate-100 dark:border-slate-800/60"></div>
                                                    </template>

                                                    <div class="absolute inset-x-0 px-2 flex items-center">
                                                        <div class="h-6 w-full rounded-xl progress-shade-planned relative overflow-hidden flex items-center shadow-inner group">
                                                            <div class="h-full progress-shade-actual rounded-l-xl transition-all duration-500 relative flex items-center justify-end pr-2 text-[10px] font-bold text-white shadow-sm"
                                                                 :style="`width: ${item.actualProgress}%;`">
                                                                <span x-text="item.actualProgress + '%'"></span>
                                                            </div>

                                                            <template x-if="item.variance > 0">
                                                                <div class="h-full progress-shade-recovered transition-all duration-500 flex items-center justify-center text-[10px] font-bold text-white shadow-sm animate-pulse"
                                                                     :style="`width: ${item.variance}%;`">
                                                                    <span>+<span x-text="item.variance"></span>%</span>
                                                                </div>
                                                            </template>

                                                            <template x-if="item.variance < 0">
                                                                <div class="h-full bg-amber-500/20 border-r-2 border-amber-500 border-dashed flex items-center justify-center text-[9px] font-bold text-amber-700 dark:text-amber-300"
                                                                     :style="`width: ${Math.abs(item.variance)}%;`">
                                                                    <span><span x-text="item.variance"></span>%</span>
                                                                </div>
                                                            </template>

                                                            <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center bg-slate-950/80 text-white text-[10px] font-bold pointer-events-none rounded-xl">
                                                                Planned: <span x-text="item.plannedProgress + '%'" class="mx-1 text-slate-300"></span> | 
                                                                Actual: <span x-text="item.actualProgress + '%'" class="mx-1 text-indigo-400"></span> | 
                                                                Variance: <span x-text="(item.variance >= 0 ? '+' : '') + item.variance + '%'" :class="item.variance >= 0 ? 'text-emerald-400 ml-1' : 'text-amber-400 ml-1'"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </section>

                    <!-- TAB 4: ACTIVITY & AUDIT TRAIL -->
                    <section x-show="activeTab === 'audit'" x-cloak class="space-y-6">
                        
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div>
                                <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
                                    <span>Activity Log & Thesis Audit Trail</span>
                                </h2>
                                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1 font-medium">
                                    Append-only telemetry recording user responses to weather alerts across participating construction companies.
                                </p>
                            </div>

                            <div class="flex items-center gap-2.5" x-show="hasPermission('export_audit')">
                                <button @click="exportAuditLogs()" 
                                        class="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-200 transition shadow-sm">
                                    <i data-lucide="download" class="w-4 h-4 text-indigo-500"></i>
                                    <span>Export Research Dataset (CSV)</span>
                                </button>
                            </div>
                        </div>

                        <!-- Thesis Research Metrics -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="dribbble-card p-4 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center text-indigo-600">
                                    <i data-lucide="building" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold uppercase text-slate-400">Total Companies Tested</p>
                                    <p class="text-lg font-bold text-slate-900 dark:text-white" x-text="companies.length + ' Organizations'"></p>
                                </div>
                            </div>

                            <div class="dribbble-card p-4 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-500/10 flex items-center justify-center text-amber-500">
                                    <i data-lucide="cloud-lightning" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold uppercase text-slate-400">Weather Alert Responses</p>
                                    <p class="text-lg font-bold text-slate-900 dark:text-white" x-text="filteredAuditLogs().filter(l => l.category === 'Weather Decision').length + ' Recorded'"></p>
                                </div>
                            </div>

                            <div class="dribbble-card p-4 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center text-emerald-500">
                                    <i data-lucide="timer" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold uppercase text-slate-400">Avg. Response Time</p>
                                    <p class="text-lg font-bold text-slate-900 dark:text-white">7.4 Minutes</p>
                                </div>
                            </div>
                        </div>

                        <!-- Filter Controls -->
                        <div class="dribbble-card p-4 px-5">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
                                <div>
                                    <label class="font-bold text-slate-400 uppercase text-[10px] block mb-1">Company Filter</label>
                                    <select x-model="auditCompanyFilter" 
                                            class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 font-semibold text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        <option value="ALL">All Companies (Comparative View)</option>
                                        <template x-for="c in companies" :key="'audit-comp-' + c.id">
                                            <option :value="c.code" x-text="c.name"></option>
                                        </template>
                                    </select>
                                </div>

                                <div>
                                    <label class="font-bold text-slate-400 uppercase text-[10px] block mb-1">Event Category</label>
                                    <select x-model="auditEventFilter" 
                                            class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 font-semibold text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        <option value="ALL">All Event Types</option>
                                        <option value="Weather Decision">Weather Alert Responses</option>
                                        <option value="Schedule Change">Schedule Adjustments</option>
                                        <option value="Progress Update">Progress Updates</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="font-bold text-slate-400 uppercase text-[10px] block mb-1">User Action Decision</label>
                                    <select x-model="auditDecisionFilter" 
                                            class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 font-semibold text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        <option value="ALL">All Actions</option>
                                        <option value="Reschedule">Rescheduled</option>
                                        <option value="Continue Same Day">Continued Same Day</option>
                                    </select>
                                </div>

                                <div class="flex items-end">
                                    <button @click="auditCompanyFilter = 'ALL'; auditEventFilter = 'ALL'; auditDecisionFilter = 'ALL';" 
                                            class="w-full px-3 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-xs font-semibold text-slate-600 dark:text-slate-300 transition">
                                        Reset Filters
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Audit Log Table -->
                        <div class="dribbble-card overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-xs">
                                    <thead class="bg-slate-50 dark:bg-slate-900/60 border-y border-slate-200 dark:border-slate-800 text-slate-400 uppercase text-[10px] font-bold">
                                        <tr>
                                            <th class="py-3 px-4">Date / Time</th>
                                            <th class="py-3 px-4">Company</th>
                                            <th class="py-3 px-4">User & Role</th>
                                            <th class="py-3 px-4">Event & Context</th>
                                            <th class="py-3 px-4">Weather Snapshot</th>
                                            <th class="py-3 px-4">Decision / Outcome</th>
                                            <th class="py-3 px-4 text-right">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                                        <template x-for="log in filteredAuditLogs()" :key="log.id">
                                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                                <td class="py-3.5 px-4 font-semibold text-slate-800 dark:text-slate-200 whitespace-nowrap">
                                                    <p x-text="log.timestamp"></p>
                                                    <span class="text-[10px] text-slate-400" x-text="log.relativeTime"></span>
                                                </td>

                                                <td class="py-3.5 px-4">
                                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 border border-indigo-200/60 dark:border-indigo-500/20"
                                                          x-text="log.companyCode"></span>
                                                </td>

                                                <td class="py-3.5 px-4">
                                                    <p class="font-bold text-slate-800 dark:text-slate-200" x-text="log.userName"></p>
                                                    <p class="text-[10px] text-slate-400" x-text="log.userRole"></p>
                                                </td>

                                                <td class="py-3.5 px-4">
                                                    <div class="flex items-center gap-1.5">
                                                        <span class="w-1.5 h-1.5 rounded-full"
                                                              :class="{
                                                                'bg-amber-500': log.category === 'Weather Decision',
                                                                'bg-indigo-500': log.category === 'Schedule Change',
                                                                'bg-emerald-500': log.category === 'Progress Update'
                                                              }"></span>
                                                        <span class="font-bold text-slate-800 dark:text-slate-200" x-text="log.actionTitle"></span>
                                                    </div>
                                                    <p class="text-[10px] text-slate-400 mt-0.5" x-text="'Target: ' + log.targetActivity"></p>
                                                </td>

                                                <td class="py-3.5 px-4">
                                                    <template x-if="log.weatherSnapshot">
                                                        <div class="p-1.5 rounded-lg bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-[10px] space-y-0.5">
                                                            <div class="flex items-center justify-between gap-2">
                                                                <span class="text-slate-400">Rain Prob:</span>
                                                                <strong class="text-amber-600 dark:text-amber-400" x-text="log.weatherSnapshot.rainProb + '%'"></strong>
                                                            </div>
                                                            <div class="flex items-center justify-between gap-2">
                                                                <span class="text-slate-400">Feasibility:</span>
                                                                <span class="font-bold text-slate-700 dark:text-slate-300" x-text="log.weatherSnapshot.feasibility"></span>
                                                            </div>
                                                        </div>
                                                    </template>
                                                    <template x-if="!log.weatherSnapshot">
                                                        <span class="text-slate-400 italic text-[11px]">N/A (Standard)</span>
                                                    </template>
                                                </td>

                                                <td class="py-3.5 px-4">
                                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold inline-block"
                                                          :class="{
                                                            'bg-amber-50 text-amber-800 dark:bg-amber-500/10 dark:text-amber-300 border border-amber-200/80 dark:border-amber-500/30': log.outcomeType === 'Reschedule',
                                                            'bg-sky-50 text-sky-800 dark:bg-sky-500/10 dark:text-sky-300 border border-sky-200/80 dark:border-sky-500/30': log.outcomeType === 'Continue Same Day',
                                                            'bg-emerald-50 text-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-300': log.outcomeType === 'Progress Saved',
                                                            'bg-indigo-50 text-indigo-800 dark:bg-indigo-500/10 dark:text-indigo-300': log.outcomeType === 'Schedule Approved'
                                                          }"
                                                          x-text="log.outcomeDetails"></span>
                                                </td>

                                                <td class="py-3.5 px-4 text-right">
                                                    <button @click="openLogModal(log)" class="p-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-indigo-50 hover:text-indigo-600 text-slate-500 dark:text-slate-400 transition" title="View Detailed Audit Record">
                                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                                    </button>
                                                </td>

                                            </tr>
                                        </template>
                                        <!-- Filter Empty State -->
                                        <template x-if="filteredAuditLogs().length === 0">
                                            <tr>
                                                <td colspan="7" class="py-12 px-4 text-center">
                                                    <div class="max-w-sm mx-auto flex flex-col items-center justify-center text-center space-y-3">
                                                        <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center">
                                                            <i data-lucide="search-x" class="w-6 h-6"></i>
                                                        </div>
                                                        <div>
                                                            <p class="text-sm font-bold text-slate-800 dark:text-slate-200">No matching audit records found</p>
                                                            <p class="text-xs text-slate-400 mt-1">Try resetting the company or event category filters above to view the dataset.</p>
                                                        </div>
                                                        <button @click="auditCompanyFilter = 'ALL'; auditEventFilter = 'ALL'; auditDecisionFilter = 'ALL';"
                                                                class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm transition">
                                                            Reset Filters
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </section>

                    <!-- TAB: USERS & ROLES MANAGEMENT -->
                    <section x-show="activeTab === 'users'" x-cloak class="space-y-6">
                        
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div>
                                <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
                                    <span>User & Role Management</span>
                                </h2>
                                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1 font-medium">
                                    Manage organization users, role-based access permissions, and multi-company account assignments.
                                </p>
                            </div>
                            
                            <div class="flex items-center gap-3">
                                <button @click="openNewUserModal()" 
                                        class="flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition shadow-md shadow-indigo-500/20 active:scale-95">
                                    <i data-lucide="user-plus" class="w-4 h-4"></i>
                                    <span>Add New User</span>
                                </button>
                            </div>
                        </div>

                        <!-- User Role Summary Cards -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
                            <template x-for="r in roles" :key="'role-stat-' + r.id">
                                <div class="dribbble-card p-4 sm:p-5 relative group hover:shadow-md transition-all cursor-pointer"
                                     @click="userRoleFilter = (userRoleFilter === r.id ? 'ALL' : r.id)"
                                     :class="userRoleFilter === r.id ? 'ring-2 ring-indigo-500 border-indigo-500/30' : ''">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400" x-text="r.short"></span>
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold"
                                              :class="getRoleBadgeClass(r.id)"
                                              x-text="users.filter(u => u.roleId === r.id).length + ' Users'"></span>
                                    </div>
                                    <h4 class="font-extrabold text-slate-900 dark:text-white text-sm sm:text-base" x-text="r.title"></h4>
                                    <p class="text-[11px] text-slate-400 mt-1 line-clamp-2" x-text="r.desc"></p>
                                </div>
                            </template>
                        </div>

                        <!-- Users Filter & Search Toolbar -->
                        <div class="dribbble-card p-4 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
                            <div class="flex items-center gap-2 flex-1 max-w-md">
                                <div class="relative w-full">
                                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                                    <input type="text" x-model="userSearch" placeholder="Search by user name or email..."
                                           class="w-full pl-9 pr-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs font-semibold text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <button x-show="userSearch" @click="userSearch = ''" class="p-2 rounded-lg text-slate-400 hover:text-slate-600">
                                    <i data-lucide="x" class="w-4 h-4"></i>
                                </button>
                            </div>

                            <div class="flex items-center gap-2.5 flex-wrap">
                                <!-- Filter by Company -->
                                <select x-model="userCompanyFilter"
                                        class="px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="ALL">All Companies</option>
                                    <template x-for="c in companies" :key="'ufc-' + c.id">
                                        <option :value="c.id" x-text="c.name"></option>
                                    </template>
                                </select>

                                <!-- Filter by Role -->
                                <select x-model="userRoleFilter"
                                        class="px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="ALL">All System Roles</option>
                                    <template x-for="r in roles" :key="'ufr-' + r.id">
                                        <option :value="r.id" x-text="r.title"></option>
                                    </template>
                                </select>

                                <button x-show="userSearch || userRoleFilter !== 'ALL' || userCompanyFilter !== 'ALL'"
                                        @click="userSearch = ''; userRoleFilter = 'ALL'; userCompanyFilter = 'ALL';"
                                        class="px-3 py-2 rounded-xl bg-slate-200/80 dark:bg-slate-800 hover:bg-slate-300 text-xs font-semibold text-slate-600 dark:text-slate-300 transition">
                                    Reset
                                </button>
                            </div>
                        </div>

                        <!-- Users Table Container -->
                        <div class="dribbble-card overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-xs">
                                    <thead class="bg-slate-50/70 dark:bg-slate-900/50 border-b border-slate-200/80 dark:border-slate-800 uppercase text-[10px] font-extrabold text-slate-400 tracking-wider">
                                        <tr>
                                            <th class="py-3 px-5">User Profile</th>
                                            <th class="py-3 px-4">Organization / Company</th>
                                            <th class="py-3 px-4">System Role</th>
                                            <th class="py-3 px-4">Status</th>
                                            <th class="py-3 px-4">Assigned Projects</th>
                                            <th class="py-3 px-4">Last Activity</th>
                                            <th class="py-3 px-5 text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                                        <template x-for="user in filteredUsers()" :key="'usr-' + user.id">
                                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors group">
                                                
                                                <!-- User Name + Email -->
                                                <td class="py-3.5 px-5">
                                                    <div class="flex items-center gap-3">
                                                        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-sky-500 text-white font-extrabold text-xs flex items-center justify-center shrink-0 shadow-sm">
                                                            <span x-text="user.initials"></span>
                                                        </div>
                                                        <div class="min-w-0">
                                                            <div class="flex items-center gap-1.5">
                                                                <p class="font-bold text-slate-900 dark:text-white truncate text-xs sm:text-sm" x-text="user.name"></p>
                                                                <span x-show="user.id === currentUser.id" class="px-1.5 py-0.2 text-[9px] font-bold rounded bg-indigo-50 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-300 border border-indigo-200/50">YOU</span>
                                                            </div>
                                                            <p class="text-[11px] text-slate-400 truncate" x-text="user.email"></p>
                                                        </div>
                                                    </div>
                                                </td>

                                                <!-- Company -->
                                                <td class="py-3.5 px-4 font-semibold text-slate-700 dark:text-slate-300">
                                                    <div class="flex items-center gap-1.5">
                                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400" x-text="getUserCompanyCode(user.companyId)"></span>
                                                        <span class="truncate max-w-[140px]" x-text="getUserCompanyName(user.companyId)"></span>
                                                    </div>
                                                </td>

                                                <!-- System Role -->
                                                <td class="py-3.5 px-4">
                                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-extrabold inline-block border"
                                                          :class="getRoleBadgeClass(user.roleId)"
                                                          x-text="getUserRoleTitle(user.roleId)"></span>
                                                </td>

                                                <!-- Status -->
                                                <td class="py-3.5 px-4">
                                                    <div class="flex items-center gap-1.5">
                                                        <span class="w-2 h-2 rounded-full" :class="user.status === 'Active' ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400'"></span>
                                                        <span class="font-semibold text-slate-700 dark:text-slate-300 text-xs" x-text="user.status || 'Active'"></span>
                                                    </div>
                                                </td>

                                                <!-- Projects -->
                                                <td class="py-3.5 px-4">
                                                    <span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 font-bold text-[11px]"
                                                          x-text="(user.assignedProjectsCount || 1) + ' Sites'"></span>
                                                </td>

                                                <!-- Last Active -->
                                                <td class="py-3.5 px-4 text-slate-400 text-[11px] whitespace-nowrap" x-text="user.lastActive || 'Recently'"></td>

                                                <!-- Actions -->
                                                <td class="py-3.5 px-5 text-right whitespace-nowrap">
                                                    <div class="flex items-center justify-end gap-1.5">
                                                        <button @click="editUser(user)" class="p-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-indigo-50 hover:text-indigo-600 dark:hover:bg-indigo-500/10 text-slate-500 dark:text-slate-400 transition" title="Edit User">
                                                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                                                        </button>
                                                        <button @click="deleteUser(user.id)" class="p-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-500/10 text-slate-500 dark:text-slate-400 transition" title="Delete User">
                                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                        </button>
                                                    </div>
                                                </td>

                                            </tr>
                                        </template>

                                        <!-- Users Empty State -->
                                        <template x-if="filteredUsers().length === 0">
                                            <tr>
                                                <td colspan="7" class="py-12 px-4 text-center">
                                                    <div class="max-w-sm mx-auto flex flex-col items-center justify-center text-center space-y-3">
                                                        <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center">
                                                            <i data-lucide="user-x" class="w-6 h-6"></i>
                                                        </div>
                                                        <div>
                                                            <p class="text-sm font-bold text-slate-800 dark:text-slate-200">No matching users found</p>
                                                            <p class="text-xs text-slate-400 mt-1">Try clearing your search query or adjusting role and organization filters.</p>
                                                        </div>
                                                        <button @click="userSearch = ''; userRoleFilter = 'ALL'; userCompanyFilter = 'ALL';"
                                                                class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm transition">
                                                            Reset User Filters
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </section>

                    <!-- TAB 5: WEATHER CONFIGURATION -->
                    <section x-show="activeTab === 'weather-config'" x-cloak class="space-y-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Weather API & Activity Threshold Rules</h2>
                                <p class="text-xs sm:text-sm text-slate-400 mt-1">Configure endpoints (Open-Meteo / PAGASA) and individual activity threshold limits.</p>
                            </div>
                        </div>
                        <div class="dribbble-card p-5 sm:p-8">
                            <div class="max-w-2xl space-y-4">
                                <div class="p-4 rounded-xl border flex flex-col sm:flex-row sm:items-center justify-between gap-3 transition cursor-pointer"
                                     @click="switchWeatherSource('open-meteo')"
                                     :class="selectedWeatherSource === 'open-meteo' ? 'bg-indigo-50/50 dark:bg-indigo-500/10 border-indigo-200 dark:border-indigo-500/30' : 'bg-slate-50 dark:bg-slate-900 border-slate-200 dark:border-slate-800'">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center shrink-0">
                                            <i data-lucide="cloud" class="w-5 h-5"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-slate-900 dark:text-white">Open-Meteo REST API</p>
                                            <p class="text-xs text-slate-400">High-Resolution Numerical Satellite Weather Model (Live Hourly Data)</p>
                                        </div>
                                    </div>
                                    <span class="self-start sm:self-auto px-2.5 py-1 rounded-lg font-bold text-xs border"
                                          :class="selectedWeatherSource === 'open-meteo' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/30' : 'bg-slate-200/50 dark:bg-slate-800 text-slate-400 border-slate-300/40 dark:border-slate-700'"
                                          x-text="selectedWeatherSource === 'open-meteo' ? 'Active (Connected)' : 'Standby (Click to Select)'"></span>
                                </div>
                                <div class="p-4 rounded-xl border flex flex-col sm:flex-row sm:items-center justify-between gap-3 transition cursor-pointer"
                                     @click="switchWeatherSource('pagasa')"
                                     :class="selectedWeatherSource === 'pagasa' ? 'bg-indigo-50/50 dark:bg-indigo-500/10 border-indigo-200 dark:border-indigo-500/30' : 'bg-slate-50 dark:bg-slate-900 border-slate-200 dark:border-slate-800'">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-sky-500/10 text-sky-600 flex items-center justify-center shrink-0">
                                            <i data-lucide="radio" class="w-5 h-5"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-slate-900 dark:text-white">DOST-PAGASA API Endpoint Adapter</p>
                                            <p class="text-xs text-slate-400">Science City of Muñoz Agrometeorological Synoptic Station #329</p>
                                        </div>
                                    </div>
                                    <span class="self-start sm:self-auto px-2.5 py-1 rounded-lg font-bold text-xs border"
                                          :class="selectedWeatherSource === 'pagasa' ? 'bg-sky-500/10 text-sky-600 dark:text-sky-400 border-sky-500/30' : 'bg-slate-200/50 dark:bg-slate-800 text-slate-400 border-slate-300/40 dark:border-slate-700'"
                                          x-text="selectedWeatherSource === 'pagasa' ? 'Active (Connected)' : 'Standby (Click to Select)'"></span>
                                </div>
                            </div>
                        </div>
                    </section>

                </div>

            </main>

        </div>

        <!-- ========================================================================= -->
        <!-- PHASE 4: RIGHT-SIDE WEATHER ASSESSMENT & DECISION SLIDE-OVER DRAWER       -->
        <!-- ========================================================================= -->
        <div x-show="drawerOpen" x-cloak class="relative z-50" aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
            <div x-show="drawerOpen"
                 x-transition:enter="ease-in-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in-out duration-300"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="drawerOpen = false"
                 class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity"></div>

            <div class="fixed inset-0 overflow-hidden pointer-events-none">
                <div class="absolute inset-0 overflow-hidden">
                    <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                        <div x-show="drawerOpen"
                             x-transition:enter="transform transition ease-in-out duration-300"
                             x-transition:enter-start="translate-x-full"
                             x-transition:enter-end="translate-x-0"
                             x-transition:leave="transform transition ease-in-out duration-300"
                             x-transition:leave-start="translate-x-0"
                             x-transition:leave-end="translate-x-full"
                             class="pointer-events-auto w-screen max-w-md bg-white dark:bg-[#0f1523] border-l border-slate-200 dark:border-slate-800 shadow-2xl flex flex-col justify-between overflow-y-auto">
                            
                            <template x-if="selectedActivity">
                                <div class="p-6 space-y-6">
                                    <div class="flex items-start justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="px-2 py-0.5 rounded-md bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 text-[10px] font-extrabold uppercase tracking-wider">
                                                    Activity Assessment
                                                </span>
                                                <span x-show="selectedActivity.hasAlert" class="px-2 py-0.5 rounded-md bg-amber-500 text-white text-[10px] font-extrabold uppercase">
                                                    Alert Active
                                                </span>
                                                <span x-show="selectedActivity.continuedSameDay" class="px-2 py-0.5 rounded-md bg-sky-500 text-white text-[10px] font-extrabold uppercase flex items-center gap-1 shadow-xs">
                                                    <i data-lucide="shield-check" class="w-3 h-3"></i>
                                                    <span>Mitigation Active</span>
                                                </span>
                                            </div>
                                            <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white mt-1.5" x-text="selectedActivity.name"></h3>
                                            <p class="text-xs text-slate-400 mt-0.5" x-text="'Scheduled: ' + selectedActivity.currentStart + ' to ' + selectedActivity.currentEnd"></p>
                                        </div>
                                        <button @click="drawerOpen = false" class="p-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                            <i data-lucide="x" class="w-5 h-5"></i>
                                        </button>
                                    </div>

                                    <!-- Continued Same Day Status Callout Banner -->
                                    <template x-if="selectedActivity.continuedSameDay">
                                        <div class="p-4 rounded-2xl bg-gradient-to-r from-sky-500/10 via-indigo-500/10 to-transparent border border-sky-300 dark:border-sky-500/30 text-xs space-y-2">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-2 font-bold text-sky-800 dark:text-sky-300">
                                                    <i data-lucide="shield-check" class="w-4 h-4 text-sky-600 dark:text-sky-400"></i>
                                                    <span>Continued Same Day (With On-Site Mitigation)</span>
                                                </div>
                                                <span class="px-2 py-0.5 rounded-full bg-sky-100 dark:bg-sky-500/20 text-sky-700 dark:text-sky-300 font-extrabold text-[10px]"
                                                      x-text="'Target Resume: ' + (selectedActivity.resumeTime || '13:00')"></span>
                                            </div>
                                            <p class="text-slate-600 dark:text-slate-300 text-[11px] leading-relaxed" 
                                               x-text="selectedActivity.mitigationNotes || 'On-site engineering mitigation applied with approved resume target window.'"></p>
                                        </div>
                                    </template>

                                    <div class="space-y-3">
                                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Risk Classifications</p>
                                        <div class="p-3.5 rounded-2xl border flex items-center justify-between"
                                             :class="{
                                                'bg-emerald-50 dark:bg-emerald-500/10 border-emerald-200 dark:border-emerald-500/20': selectedActivity.feasibility === 'Feasible',
                                                'bg-amber-50 dark:bg-amber-500/10 border-amber-200 dark:border-amber-500/30': selectedActivity.feasibility === 'Conditionally Feasible',
                                                'bg-rose-50 dark:bg-rose-500/10 border-rose-200 dark:border-rose-500/30': selectedActivity.feasibility === 'Infeasible'
                                             }">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-xl flex items-center justify-center"
                                                     :class="{
                                                        'bg-emerald-500 text-white': selectedActivity.feasibility === 'Feasible',
                                                        'bg-amber-500 text-white': selectedActivity.feasibility === 'Conditionally Feasible',
                                                        'bg-rose-500 text-white': selectedActivity.feasibility === 'Infeasible'
                                                     }">
                                                    <i :data-lucide="selectedActivity.feasibility === 'Feasible' ? 'check' : 'alert-triangle'" class="w-4 h-4"></i>
                                                </div>
                                                <div>
                                                    <p class="text-[10px] font-bold uppercase text-slate-400">Activity Feasibility</p>
                                                    <p class="text-xs font-bold text-slate-900 dark:text-white" x-text="selectedActivity.feasibility"></p>
                                                </div>
                                            </div>
                                            <span class="text-[10px] font-semibold text-slate-500 dark:text-slate-400" x-text="selectedActivity.hasAlert ? 'Rule Exceeded' : 'Passes Criteria'"></span>
                                        </div>

                                        <div class="p-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/60 flex items-center justify-between">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-xl bg-rose-500/10 text-rose-500 flex items-center justify-center">
                                                    <i data-lucide="hard-hat" class="w-4 h-4"></i>
                                                </div>
                                                <div>
                                                    <p class="text-[10px] font-bold uppercase text-slate-400">Worker Safety Risk</p>
                                                    <p class="text-xs font-bold text-rose-600 dark:text-rose-400" x-text="selectedActivity.hasAlert ? 'Moderate Safety Alert (Rain Slippage)' : 'Low Safety Risk'"></p>
                                                </div>
                                            </div>
                                            <span class="text-[10px] font-semibold text-slate-500 dark:text-slate-400">OSHA Safety Rule</span>
                                        </div>
                                    </div>

                                    <div class="space-y-3">
                                        <div class="flex items-center justify-between">
                                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Forecast Telemetry (Sep 30)</p>
                                            <span class="text-[10px] text-slate-400 font-medium">Open-Meteo REST API</span>
                                        </div>

                                        <div class="grid grid-cols-2 gap-2.5">
                                            <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800">
                                                <div class="flex items-center justify-between text-slate-400 text-[10px] font-bold uppercase mb-1">
                                                    <span>Rain Probability</span>
                                                    <i data-lucide="cloud-rain" class="w-3.5 h-3.5 text-amber-500"></i>
                                                </div>
                                                <p class="text-base font-extrabold text-amber-600 dark:text-amber-400">75%</p>
                                                <p class="text-[10px] text-slate-400 mt-0.5 font-medium">Threshold: 40%</p>
                                            </div>

                                            <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800">
                                                <div class="flex items-center justify-between text-slate-400 text-[10px] font-bold uppercase mb-1">
                                                    <span>Precipitation</span>
                                                    <i data-lucide="droplets" class="w-3.5 h-3.5 text-sky-500"></i>
                                                </div>
                                                <p class="text-base font-extrabold text-slate-900 dark:text-white">6.2 mm/hr</p>
                                                <p class="text-[10px] text-slate-400 mt-0.5 font-medium">Threshold: 2.5 mm</p>
                                            </div>

                                            <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800">
                                                <div class="flex items-center justify-between text-slate-400 text-[10px] font-bold uppercase mb-1">
                                                    <span>Wind Speed</span>
                                                    <i data-lucide="wind" class="w-3.5 h-3.5 text-indigo-500"></i>
                                                </div>
                                                <p class="text-base font-extrabold text-slate-900 dark:text-white">22 km/h</p>
                                                <p class="text-[10px] text-emerald-600 font-medium">Safe (&lt; 40 km/h)</p>
                                            </div>

                                            <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800">
                                                <div class="flex items-center justify-between text-slate-400 text-[10px] font-bold uppercase mb-1">
                                                    <span>Temperature</span>
                                                    <i data-lucide="thermometer" class="w-3.5 h-3.5 text-rose-500"></i>
                                                </div>
                                                <p class="text-base font-extrabold text-slate-900 dark:text-white">27°C</p>
                                                <p class="text-[10px] text-slate-400 font-medium">Heat Index: Normal</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/30 text-xs">
                                        <div class="flex items-center gap-2 font-bold text-amber-800 dark:text-amber-300 mb-1">
                                            <i data-lucide="info" class="w-4 h-4"></i>
                                            <span>Decision Support Advisory</span>
                                        </div>
                                        <p class="text-amber-700 dark:text-amber-200/80 leading-relaxed text-[11px]">
                                            Forecast indicates high rain probability during morning hours on Sep 30. You can choose to <strong>Continue Same Day</strong> with a scheduled resume time or <strong>Reschedule</strong> to an optimal clear weather window.
                                        </p>
                                    </div>
                                </div>
                            </template>

                            <div class="p-5 bg-slate-50 dark:bg-[#0c111c] border-t border-slate-200 dark:border-slate-800 space-y-2.5">
                                <template x-if="hasPermission('reschedule_activity')">
                                    <div>
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-2">Choose Scheduling Action:</p>
                                        <div class="grid grid-cols-2 gap-2.5">
                                            <button @click="openContinueModal()"
                                                    class="px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-800 dark:text-slate-200 transition flex items-center justify-center gap-1.5 shadow-sm">
                                                <i data-lucide="clock" class="w-3.5 h-3.5 text-indigo-500"></i>
                                                <span>Continue Same Day</span>
                                            </button>

                                            <button @click="openRescheduleModal()"
                                                    class="px-3.5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-md shadow-indigo-600/20">
                                                <i data-lucide="calendar-plus" class="w-3.5 h-3.5"></i>
                                                <span>Reschedule Activity</span>
                                            </button>
                                        </div>

                                        <button @click="deleteScheduleActivity(selectedActivity.id)"
                                                class="w-full mt-2 py-2 px-3 rounded-xl bg-rose-50 hover:bg-rose-100 dark:bg-rose-500/10 dark:hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-500/20 text-xs font-bold transition flex items-center justify-center gap-1.5">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                            <span>Remove Activity from Gantt</span>
                                        </button>
                                    </div>
                                </template>

                                <template x-if="!hasPermission('reschedule_activity')">
                                    <div class="p-3.5 rounded-2xl bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-center space-y-1">
                                        <div class="flex items-center justify-center gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-300">
                                            <i data-lucide="lock" class="w-3.5 h-3.5 text-amber-500"></i>
                                            <span x-text="'Decision Locked: ' + currentRole.title"></span>
                                        </div>
                                        <p class="text-[10px] text-slate-400">
                                            Only Project Engineers and Administrators hold authority to execute timeline rescheduling decisions.
                                        </p>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- DECISION MODAL 1: CONTINUE SAME DAY -->
        <div x-show="continueModalOpen" x-cloak class="relative z-50">
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm transition-opacity" @click="continueModalOpen = false"></div>
            
            <div class="fixed inset-0 z-10 overflow-y-auto flex items-center justify-center p-4">
                <div class="w-full max-w-md bg-white dark:bg-[#131a2c] border border-slate-200 dark:border-slate-700 rounded-3xl shadow-2xl p-6 space-y-5 text-left">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                            <i data-lucide="play-circle" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">Continue Activity Same Day</h3>
                            <p class="text-xs text-slate-400" x-text="selectedActivity ? selectedActivity.name : ''"></p>
                        </div>
                    </div>

                    <p class="text-xs text-slate-600 dark:text-slate-300">
                        Confirming to proceed with the activity on the scheduled date (<span class="font-bold" x-text="selectedActivity ? selectedActivity.currentStart : ''"></span>) despite weather advisory.
                    </p>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-400">Continuation / Resume Target Time</label>
                        <input type="time" x-model="resumeTime" value="13:00"
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-sm font-semibold text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <p class="text-[11px] text-slate-400">e.g. Resuming once rain eases around 1:00 PM</p>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-400">On-Site Mitigation Notes & Precautions</label>
                        <textarea x-model="continuationMitigationNotes" rows="2"
                                  placeholder="e.g. Protective tarpaulin installed, water-resistant additive used, scheduled pour for afternoon window"
                                  class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-medium text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-2">
                        <button @click="continueModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-500 hover:text-slate-700">Cancel</button>
                        <button @click="confirmContinueSameDay()" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-500/20">
                            Confirm & Log Decision
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- DECISION MODAL 2: RESCHEDULE -->
        <div x-show="rescheduleModalOpen" x-cloak class="relative z-50">
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm transition-opacity" @click="rescheduleModalOpen = false"></div>
            
            <div class="fixed inset-0 z-10 overflow-y-auto flex items-center justify-center p-4">
                <div class="w-full max-w-lg bg-white dark:bg-[#131a2c] border border-slate-200 dark:border-slate-700 rounded-3xl shadow-2xl p-6 space-y-5 text-left">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                            <i data-lucide="calendar-plus" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">Reschedule Construction Activity</h3>
                            <p class="text-xs text-slate-400" x-text="selectedActivity ? selectedActivity.name : ''"></p>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-400">Select New Scheduled Date</label>
                        <select x-model="newScheduledDate" 
                                class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs sm:text-sm font-semibold text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="Oct 02">Oct 02, 2026 (Recommended — Low Rain Risk 20%)</option>
                            <option value="Oct 03">Oct 03, 2026 (Clear Weather 10%)</option>
                            <option value="Oct 04">Oct 04, 2026 (Sunny 15%)</option>
                        </select>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/80 border border-slate-200 dark:border-slate-800 space-y-3">
                        <div class="flex items-center justify-between text-xs font-bold text-slate-800 dark:text-slate-200">
                            <span class="flex items-center gap-1.5">
                                <i data-lucide="git-branch" class="w-4 h-4 text-indigo-500"></i>
                                <span>Potentially Affected Successors (1 Activity)</span>
                            </span>
                            <span class="text-[10px] text-amber-600 font-bold px-2 py-0.5 rounded bg-amber-50 dark:bg-amber-500/10">Dependency Alert</span>
                        </div>
                        
                        <div class="p-3 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs">
                            <p class="font-bold text-slate-800 dark:text-white">Tower Crane Heavy Lifting & Steel Assembly</p>
                            <p class="text-[11px] text-slate-400 mt-0.5">Predecessor: Concrete Pouring (Cannot start before slab curing is complete)</p>
                        </div>

                        <div class="space-y-2 pt-1">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Successor Adjustment Option:</label>
                            
                            <label class="flex items-center gap-2.5 text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
                                <input type="radio" name="successorAction" value="adjust" x-model="successorAction" class="text-indigo-600 focus:ring-indigo-500">
                                <span><strong>Auto-adjust succeeding activities</strong> (Shift dependent tasks accordingly)</span>
                            </label>

                            <label class="flex items-center gap-2.5 text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
                                <input type="radio" name="successorAction" value="retain" x-model="successorAction" class="text-indigo-600 focus:ring-indigo-500">
                                <span><strong>Retain succeeding dates</strong> (Manual review required by engineer)</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-2">
                        <button @click="rescheduleModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-500 hover:text-slate-700">Cancel</button>
                        <button @click="confirmReschedule()" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-500/20">
                            Update Schedule & Log Audit
                        </button>
                    </div>

                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- CONTRACTOR COMPANY MODAL: ADD / EDIT CONTRACTOR ORGANIZATION              -->
        <!-- ========================================================================= -->
        <div x-show="companyModalOpen" x-cloak class="relative z-50">
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm transition-opacity" @click="companyModalOpen = false"></div>
            
            <div class="fixed inset-0 z-10 overflow-y-auto flex items-center justify-center p-4">
                <div class="w-full max-w-lg bg-white dark:bg-[#131a2c] border border-slate-200 dark:border-slate-700 rounded-3xl shadow-2xl p-6 space-y-5 text-left" @click.stop>
                    
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                                <i data-lucide="building" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white" x-text="editingCompanyId ? 'Edit Contractor Organization' : 'Register Contractor Company'"></h3>
                                <p class="text-xs text-slate-400">External private contractor details & university project assignments</p>
                            </div>
                        </div>
                        <button @click="companyModalOpen = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <form @submit.prevent="saveCompany()" class="space-y-4 text-xs">
                        <div class="grid grid-cols-3 gap-3">
                            <div class="col-span-2">
                                <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Company / Contractor Name *</label>
                                <input type="text" x-model="companyForm.name" required placeholder="e.g. Megawide Construction Corp."
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-semibold text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Org Code *</label>
                                <input type="text" x-model="companyForm.code" required placeholder="MEGAWIDE" :disabled="editingCompanyId && companyForm.code === 'PPSDS'"
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-semibold text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 uppercase">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Authorized Contact Person</label>
                                <input type="text" x-model="companyForm.contact_person" placeholder="e.g. Engr. Juan Dela Cruz"
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-semibold text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Status</label>
                                <select x-model="companyForm.status"
                                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-semibold text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="Active">Active (Eligible for Site Bidding / Work)</option>
                                    <option value="Inactive">Inactive (Suspended / Completed)</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Email *</label>
                                <input type="email" x-model="companyForm.contact_email" required placeholder="contractor@vertical.ph"
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-semibold text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <p class="text-[10px] text-slate-400 mt-1" x-show="!editingCompanyId">
                                    Login password will be defaulted to <span class="font-mono text-indigo-500 font-bold">password123</span>.
                                </p>
                            </div>
                            <div>
                                <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Contact Phone</label>
                                <input type="text" x-model="companyForm.contact_phone" placeholder="0917-000-0000"
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-semibold text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>

                        <div>
                            <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Office / Site Headquarters Address</label>
                            <textarea x-model="companyForm.address" rows="2" placeholder="e.g. KM 148 Maharlika Highway, Science City of Muñoz, Nueva Ecija"
                                      class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-semibold text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100 dark:border-slate-800">
                            <button type="button" @click="companyModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-500 hover:text-slate-700">Cancel</button>
                            <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-500/20">
                                <span x-text="editingCompanyId ? 'Update Contractor Profile' : 'Register Contractor'"></span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- PROJECT CRUD MODAL: CREATE / EDIT PROJECT                                -->
        <!-- ========================================================================= -->
        <div x-show="projectModalOpen" x-cloak class="relative z-50">
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm transition-opacity" @click="projectModalOpen = false"></div>
            
            <div class="fixed inset-0 z-10 overflow-y-auto flex items-center justify-center p-4">
                <div class="w-full max-w-lg bg-white dark:bg-[#131a2c] border border-slate-200 dark:border-slate-700 rounded-3xl shadow-2xl p-6 space-y-5 text-left" @click.stop>
                    
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                                <i data-lucide="building-2" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white" x-text="editingProjectId ? 'Edit Project Details' : 'Create New Construction Project'"></h3>
                                <p class="text-xs text-slate-400">Vertical construction site parameters & weather coordinates</p>
                            </div>
                        </div>
                        <button @click="projectModalOpen = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <form @submit.prevent="saveProject()" class="space-y-4 text-xs">
                        <div>
                            <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Project Name *</label>
                            <input type="text" x-model="projectForm.name" required placeholder="e.g. Marina Bayfront Tower (45-Storey)"
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-semibold text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Project Code</label>
                                <input type="text" x-model="projectForm.code" required placeholder="CLSU-ENG-2026"
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-semibold text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Assigned Company</label>
                                <select x-model="projectForm.companyId"
                                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-semibold text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <template x-for="c in companies" :key="'p-comp-' + c.id">
                                        <option :value="c.id" x-text="c.name"></option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Site Location & Weather Telemetry City *</label>
                            <input type="text" x-model="projectForm.location" required placeholder="CLSU Campus, Science City of Muñoz, Nueva Ecija"
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-semibold text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <p class="text-[10px] text-slate-400 mt-1">Automatically resolves coordinates for Open-Meteo & PAGASA hourly forecasts.</p>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Target Total Storeys</label>
                                <input type="number" x-model="projectForm.storeys" min="1" max="100" placeholder="5"
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-semibold text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Baseline Milestone Duration</label>
                                <select x-model="projectForm.durationWeeks"
                                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-semibold text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="12">12 Weeks (Standard Baseline)</option>
                                    <option value="24">24 Weeks (Extended Phase)</option>
                                    <option value="48">48 Weeks (Full Project)</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100 dark:border-slate-800">
                            <button type="button" @click="projectModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-500 hover:text-slate-700">Cancel</button>
                            <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-500/20">
                                <span x-text="editingProjectId ? 'Update Project' : 'Create Project Workspace'"></span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- PROJECT MANAGEMENT DIRECTORY MODAL (FULL DIRECTORY VIEW)                  -->
        <!-- ========================================================================= -->
        <div x-show="projectDirectoryOpen" x-cloak class="relative z-50">
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm transition-opacity" @click="projectDirectoryOpen = false"></div>
            
            <div class="fixed inset-0 z-10 overflow-y-auto flex items-center justify-center p-4">
                <div class="w-full max-w-3xl bg-white dark:bg-[#131a2c] border border-slate-200 dark:border-slate-700 rounded-3xl shadow-2xl p-6 space-y-5 text-left" @click.stop>
                    
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                                <i data-lucide="folders" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white">Construction Projects Directory</h3>
                                <p class="text-xs text-slate-400">All registered vertical construction projects across client organizations</p>
                            </div>
                        </div>
                        <button @click="openNewProjectModal()" class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold flex items-center gap-1.5 shadow-sm">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            <span>New Project</span>
                        </button>
                    </div>

                    <div class="divide-y divide-slate-100 dark:divide-slate-800/80 max-h-96 overflow-y-auto">
                        <template x-for="p in getFilteredProjects()" :key="'dir-' + p.id">
                            <div class="py-3.5 flex items-center justify-between gap-4">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold text-xs shrink-0">
                                        <i data-lucide="building-2" class="w-4 h-4"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <p class="font-bold text-slate-900 dark:text-white text-xs sm:text-sm truncate" x-text="p.name"></p>
                                            <span x-show="currentProject.id === p.id" class="px-2 py-0.5 rounded text-[9px] font-extrabold bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-500/20">ACTIVE</span>
                                        </div>
                                        <div class="flex items-center gap-2 text-[11px] text-slate-400 mt-0.5">
                                            <span class="font-semibold text-indigo-600 dark:text-indigo-400" x-text="p.company_name || getUserCompanyName(p.company_id || p.companyId)"></span>
                                            <span>•</span>
                                            <span x-text="p.code"></span>
                                            <span>•</span>
                                            <span class="truncate" x-text="p.location"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 shrink-0">
                                    <button @click="switchProject(p); projectDirectoryOpen = false;" 
                                            class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 dark:bg-slate-800 hover:bg-indigo-50 hover:text-indigo-600 dark:hover:bg-indigo-500/10 transition"
                                            x-text="currentProject.id === p.id ? 'Current' : 'Select'"></button>
                                    <button @click="editProject(p)" class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition" title="Edit Project">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </button>
                                    <button @click="deleteProject(p.id)" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition" title="Delete Project">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="flex justify-end pt-2 border-t border-slate-100 dark:border-slate-800">
                        <button @click="projectDirectoryOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">Close Directory</button>
                    </div>

                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- ACTIVITY BUILDER MODAL: ADD / EDIT CONSTRUCTION ACTIVITY                  -->
        <!-- ========================================================================= -->
        <div x-show="activityModalOpen" x-cloak class="relative z-50">
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm transition-opacity" @click="activityModalOpen = false"></div>
            
            <div class="fixed inset-0 z-10 overflow-y-auto flex items-center justify-center p-4">
                <div class="w-full max-w-lg bg-white dark:bg-[#131a2c] border border-slate-200 dark:border-slate-700 rounded-3xl shadow-2xl p-6 space-y-5 text-left" @click.stop>
                    
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                                <i data-lucide="calendar-plus" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white" x-text="editingActivityId ? 'Edit Construction Activity' : 'Add Construction Activity'"></h3>
                                <p class="text-xs text-slate-400">Set schedule dates, predecessor dependencies, and weather rules</p>
                            </div>
                        </div>
                        <button @click="activityModalOpen = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <form @submit.prevent="saveActivity()" class="space-y-4 text-xs">
                        <div>
                            <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Activity Name *</label>
                            <input type="text" x-model="activityForm.name" required placeholder="e.g. Shear Wall Concrete Pouring (Level 15)"
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-semibold text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Predecessor Activity</label>
                                <select x-model="activityForm.predecessor"
                                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-semibold text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="">None (Can start anytime)</option>
                                    <template x-for="a in activities" :key="'pred-' + a.id">
                                        <option :value="a.name" x-text="a.name"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Duration (Days)</label>
                                <input type="number" min="1" max="14" x-model="activityForm.durationDays" required
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-semibold text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Baseline Start Date</label>
                                <select x-model="activityForm.startCol"
                                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-semibold text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <template x-for="(d, idx) in ganttDates" :key="'dopt-' + d.date">
                                        <option :value="idx" x-text="d.date + ' (' + d.dayName + ')'"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Weather Sensitivity Preset</label>
                                <select x-model="activityForm.weatherRuleText"
                                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-semibold text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="Rain < 40%, No Heavy Rain">Concrete Pouring (Rain < 40%)</option>
                                    <option value="Wind < 30km/h, No Lightning">Tower Crane / Lifting (Wind < 30km/h)</option>
                                    <option value="Rain < 30%, Humidity < 80%">Waterproofing / Cladding (Rain < 30%)</option>
                                    <option value="Rain < 60%, Wind < 45km/h">Rebar & Formwork (Rain < 60%)</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100 dark:border-slate-800">
                            <button type="button" @click="activityModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-500 hover:text-slate-700">Cancel</button>
                            <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-500/20">
                                <span x-text="editingActivityId ? 'Update Activity' : 'Add Activity to Gantt'"></span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- PROGRESS UPDATE MODAL: ALL ACTIVITIES WITH DIRECT SLIDERS                 -->
        <!-- ========================================================================= -->
        <div x-show="progressModalOpen" x-cloak class="relative z-50">
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm transition-opacity" @click="progressModalOpen = false"></div>
            
            <div class="fixed inset-0 z-10 overflow-y-auto flex items-center justify-center p-4">
                <div class="w-full max-w-2xl bg-white dark:bg-[#131a2c] border border-slate-200 dark:border-slate-700 rounded-3xl shadow-2xl p-6 space-y-5 text-left" @click.stop>
                    
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold">
                                <i data-lucide="trending-up" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white">Update Daily Accomplishment Progress</h3>
                                <p class="text-xs text-slate-400">Site Supervisor Accomplishment Log • <span class="font-semibold text-indigo-600 dark:text-indigo-400" x-text="currentProject?.name || 'Current Project'"></span></p>
                            </div>
                        </div>
                        <button @click="progressModalOpen = false" class="p-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <!-- Activities Progress Entry List -->
                    <div class="space-y-4 max-h-[60vh] overflow-y-auto pr-1">
                        <template x-for="(act, idx) in progressEntries" :key="'entry-' + act.id">
                            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/70 border border-slate-200/80 dark:border-slate-800 space-y-3 transition hover:border-slate-300 dark:hover:border-slate-700">
                                
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <h4 class="text-xs font-bold text-slate-800 dark:text-white" x-text="act.name"></h4>
                                        <div class="flex items-center gap-2 text-[11px] text-slate-400 mt-0.5">
                                            <span>Target Planned: <strong class="text-slate-700 dark:text-slate-300" x-text="act.plannedProgress + '%'"></strong></span>
                                            <span>•</span>
                                            <span>Previous Actual: <span x-text="act.originalActual + '%'"></span></span>
                                        </div>
                                    </div>

                                    <!-- Variance Badge -->
                                    <div class="shrink-0 text-right">
                                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-extrabold"
                                              :class="{
                                                'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400 border border-emerald-500/20': (act.actualProgress - act.plannedProgress) >= 0,
                                                'bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400 border border-amber-500/20': (act.actualProgress - act.plannedProgress) < 0 && (act.actualProgress - act.plannedProgress) > -5,
                                                'bg-rose-50 text-rose-700 dark:bg-rose-500/15 dark:text-rose-400 border border-rose-500/20': (act.actualProgress - act.plannedProgress) <= -5
                                              }">
                                            <span x-text="((act.actualProgress - act.plannedProgress) >= 0 ? '+' : '') + (act.actualProgress - act.plannedProgress) + '% Variance'"></span>
                                        </span>
                                    </div>
                                </div>

                                <!-- Slider and Direct Numeric Input -->
                                <div class="flex items-center gap-4">
                                    <div class="flex-1">
                                        <input type="range" min="0" max="100" x-model.number="act.actualProgress"
                                               class="w-full h-2 bg-slate-200 dark:bg-slate-700 rounded-lg appearance-none cursor-pointer accent-emerald-600 focus:outline-none">
                                    </div>
                                    <div class="flex items-center gap-1.5 shrink-0">
                                        <input type="number" min="0" max="100" x-model.number="act.actualProgress"
                                               class="w-16 px-2 py-1.5 text-center text-xs font-bold rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                        <span class="text-xs font-bold text-slate-400">%</span>
                                    </div>
                                </div>

                            </div>
                        </template>
                    </div>

                    <!-- Daily Accomplishment Notes -->
                    <div>
                        <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1 text-xs">Accomplishment / Mitigation Notes (Optional)</label>
                        <input type="text" x-model="progressReportNotes" placeholder="e.g. Concrete curing on schedule, no rain delays encountered today"
                               class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <!-- Footer Controls -->
                    <div class="flex items-center justify-between pt-3 border-t border-slate-100 dark:border-slate-800">
                        <span class="text-[11px] text-slate-400">All changes will be updated and recorded to the audit log</span>
                        <div class="flex items-center gap-2.5">
                            <button type="button" @click="progressModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">Cancel</button>
                            <button type="button" @click="confirmAllProgressUpdates()" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md shadow-emerald-500/20 active:scale-95 transition">
                                Save All Accomplishment Logs
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- PHASE 6 MODAL: DETAILED AUDIT TRAIL RECORD VIEWER (INTERACTIVE MODAL)     -->
        <!-- ========================================================================= -->
        <div x-show="logModalOpen" x-cloak class="relative z-50">
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm transition-opacity" @click="logModalOpen = false"></div>
            
            <div class="fixed inset-0 z-10 overflow-y-auto flex items-center justify-center p-4">
                <div class="w-full max-w-lg bg-white dark:bg-[#131a2c] border border-slate-200 dark:border-slate-700 rounded-3xl shadow-2xl p-6 space-y-6 text-left"
                     @click.stop>
                    
                    <template x-if="selectedLog">
                        <div class="space-y-5">
                            
                            <!-- Modal Header -->
                            <div class="flex items-start justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-2xl flex items-center justify-center"
                                         :class="{
                                            'bg-amber-50 dark:bg-amber-500/10 text-amber-500': selectedLog.category === 'Weather Decision',
                                            'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-500': selectedLog.category === 'Schedule Change',
                                            'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-500': selectedLog.category === 'Progress Update'
                                         }">
                                        <i :data-lucide="selectedLog.category === 'Weather Decision' ? 'cloud-lightning' : (selectedLog.category === 'Progress Update' ? 'trending-up' : 'calendar')" class="w-5 h-5"></i>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold uppercase"
                                                  :class="{
                                                    'bg-amber-50 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300': selectedLog.category === 'Weather Decision',
                                                    'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300': selectedLog.category === 'Schedule Change',
                                                    'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300': selectedLog.category === 'Progress Update'
                                                  }"
                                                  x-text="selectedLog.category"></span>
                                            <span class="text-xs text-slate-400" x-text="selectedLog.relativeTime"></span>
                                        </div>
                                        <h3 class="text-base font-bold text-slate-900 dark:text-white mt-1" x-text="selectedLog.actionTitle"></h3>
                                    </div>
                                </div>
                                <button @click="logModalOpen = false" class="p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 hover:text-slate-600">
                                    <i data-lucide="x" class="w-5 h-5"></i>
                                </button>
                            </div>

                            <!-- 2-Column Metadata Grid -->
                            <div class="grid grid-cols-2 gap-3 text-xs">
                                
                                <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-900/70 border border-slate-200/80 dark:border-slate-800 space-y-1">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase block">Construction Company</span>
                                    <p class="font-extrabold text-slate-800 dark:text-slate-200" x-text="selectedLog.companyName"></p>
                                    <span class="text-[10px] text-indigo-600 dark:text-indigo-400 font-semibold" x-text="'Org Code: ' + selectedLog.companyCode"></span>
                                </div>

                                <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-900/70 border border-slate-200/80 dark:border-slate-800 space-y-1">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase block">Responsible User</span>
                                    <p class="font-extrabold text-slate-800 dark:text-slate-200" x-text="selectedLog.userName"></p>
                                    <span class="text-[10px] text-slate-500 dark:text-slate-400 font-medium" x-text="'Role: ' + selectedLog.userRole"></span>
                                </div>

                                <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-900/70 border border-slate-200/80 dark:border-slate-800 space-y-1 col-span-2">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase block">Target Construction Activity</span>
                                    <p class="font-extrabold text-slate-800 dark:text-slate-200" x-text="selectedLog.targetActivity"></p>
                                </div>

                            </div>

                            <!-- Weather Telemetry Snapshot (If applicable) -->
                            <template x-if="selectedLog.weatherSnapshot">
                                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                                            <i data-lucide="cloud-rain" class="w-3.5 h-3.5 text-amber-500"></i>
                                            <span>Weather Telemetry Snapshot At Alert Event</span>
                                        </span>
                                        <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold">Open-Meteo Verified</span>
                                    </div>

                                    <div class="grid grid-cols-3 gap-2 pt-1 text-center">
                                        <div class="p-2 rounded-xl bg-white dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700">
                                            <p class="text-[9px] font-bold text-slate-400 uppercase">Rain Probability</p>
                                            <p class="text-sm font-extrabold text-amber-600 dark:text-amber-400" x-text="selectedLog.weatherSnapshot.rainProb + '%'"></p>
                                        </div>

                                        <div class="p-2 rounded-xl bg-white dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700">
                                            <p class="text-[9px] font-bold text-slate-400 uppercase">Wind Velocity</p>
                                            <p class="text-sm font-extrabold text-slate-800 dark:text-white" x-text="selectedLog.weatherSnapshot.wind"></p>
                                        </div>

                                        <div class="p-2 rounded-xl bg-white dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700">
                                            <p class="text-[9px] font-bold text-slate-400 uppercase">Classification</p>
                                            <p class="text-[11px] font-bold text-amber-700 dark:text-amber-300 truncate" x-text="selectedLog.weatherSnapshot.feasibility"></p>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <!-- Decision / Outcome Details Card -->
                            <div class="p-4 rounded-2xl bg-indigo-50/60 dark:bg-indigo-500/10 border border-indigo-200/80 dark:border-indigo-500/30 space-y-1.5">
                                <span class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider block">Decision Execution Record</span>
                                <p class="text-xs font-extrabold text-slate-900 dark:text-white leading-relaxed" x-text="selectedLog.outcomeDetails"></p>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400" x-text="'Exact Timestamp: ' + selectedLog.timestamp"></p>
                            </div>

                            <!-- Footer Close Button -->
                            <div class="flex items-center justify-between pt-2">
                                <span class="text-[10px] text-slate-400 font-semibold flex items-center gap-1">
                                    <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-500"></i>
                                    <span>Immutable Audit Hash Preserved</span>
                                </span>
                                <button @click="logModalOpen = false" class="px-5 py-2.5 rounded-xl bg-slate-900 dark:bg-slate-700 hover:bg-slate-800 text-white text-xs font-bold transition">
                                    Close Details
                                </button>
                            </div>

                        </div>
                    </template>

                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- USER MANAGEMENT MODAL: ADD / EDIT USER PROFILE & ROLES                    -->
        <!-- ========================================================================= -->
        <div x-show="userModalOpen" x-cloak class="relative z-50">
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm transition-opacity" @click="userModalOpen = false"></div>
            
            <div class="fixed inset-0 z-10 overflow-y-auto flex items-center justify-center p-4">
                <div class="w-full max-w-2xl bg-white dark:bg-[#131a2c] border border-slate-200 dark:border-slate-700 rounded-3xl shadow-2xl p-6 space-y-5 text-left" @click.stop>
                    
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                                <i data-lucide="user-cog" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white" x-text="editingUserId ? 'Edit User Profile' : 'Add New Organization User'"></h3>
                                <p class="text-xs text-slate-400">Configure multi-company account and role privileges</p>
                            </div>
                        </div>
                        <button @click="userModalOpen = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <form @submit.prevent="saveUser()" class="space-y-4 text-xs">
                        <div>
                            <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Full Name *</label>
                            <input type="text" x-model="userForm.name" required placeholder="e.g. Engr. Maria Clara Santos"
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-semibold text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Email Address (Login Username) *</label>
                            <input type="email" x-model="userForm.email" required placeholder="e.g. maria.santos@apexbuilders.ph"
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-semibold text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Company / Organization *</label>
                                <select x-model="userForm.companyId"
                                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-semibold text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <template x-for="c in companies" :key="'modal-comp-' + c.id">
                                        <option :value="c.id" x-text="c.name"></option>
                                    </template>
                                </select>
                            </div>

                            <div>
                                <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">System Role *</label>
                                <select x-model="userForm.roleId" @change="onUserRoleChange()"
                                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-semibold text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <template x-for="r in roles" :key="'modal-role-' + r.id">
                                        <option :value="r.id" x-text="r.title"></option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Account Status</label>
                            <select x-model="userForm.status"
                                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-semibold text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="Active">Active (Full system login enabled)</option>
                                <option value="Inactive">Inactive (Suspended)</option>
                            </select>
                        </div>

                        <!-- Role Permissions Summary Info -->
                        <div class="p-3 rounded-2xl bg-indigo-50/50 dark:bg-indigo-500/10 border border-indigo-200/60 dark:border-indigo-500/20 text-[11px] text-slate-600 dark:text-slate-300">
                            <span class="font-bold text-indigo-600 dark:text-indigo-400 block mb-0.5">Role Permission Summary:</span>
                            <span x-text="roles.find(r => r.id === userForm.roleId)?.desc || 'Standard privileges'"></span>
                        </div>

                        <!-- Granular Access Control & Permissions Matrix -->
                        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 space-y-3.5">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-lg bg-indigo-100 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                                        <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-800 dark:text-slate-200 text-xs">Custom Access Control & Permissions</h4>
                                        <p class="text-[10px] text-slate-500 dark:text-slate-400">Toggle module access and authorized actions for this account</p>
                                    </div>
                                </div>
                                <button type="button" @click="onUserRoleChange()" class="px-2.5 py-1 rounded-lg text-[10px] font-semibold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-500/10 hover:bg-indigo-100 dark:hover:bg-indigo-500/20 transition-colors">
                                    Reset to Defaults
                                </button>
                            </div>

                            <!-- Module Visibility Toggles -->
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 block mb-1.5">Module Tab Access</span>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                    <label class="flex items-center gap-2 p-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-800/80 cursor-pointer hover:border-indigo-400 transition">
                                        <input type="checkbox" x-model="userForm.permissions.tab_dashboard" class="rounded text-indigo-600 focus:ring-indigo-500 w-3.5 h-3.5">
                                        <span class="text-[11px] font-medium text-slate-700 dark:text-slate-300">Dashboard</span>
                                    </label>
                                    <label class="flex items-center gap-2 p-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-800/80 cursor-pointer hover:border-indigo-400 transition">
                                        <input type="checkbox" x-model="userForm.permissions.tab_scheduling" class="rounded text-indigo-600 focus:ring-indigo-500 w-3.5 h-3.5">
                                        <span class="text-[11px] font-medium text-slate-700 dark:text-slate-300">Gantt Schedule</span>
                                    </label>
                                    <label class="flex items-center gap-2 p-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-800/80 cursor-pointer hover:border-indigo-400 transition">
                                        <input type="checkbox" x-model="userForm.permissions.tab_projects" class="rounded text-indigo-600 focus:ring-indigo-500 w-3.5 h-3.5">
                                        <span class="text-[11px] font-medium text-slate-700 dark:text-slate-300">Projects</span>
                                    </label>
                                    <label class="flex items-center gap-2 p-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-800/80 cursor-pointer hover:border-indigo-400 transition">
                                        <input type="checkbox" x-model="userForm.permissions.tab_progress" class="rounded text-indigo-600 focus:ring-indigo-500 w-3.5 h-3.5">
                                        <span class="text-[11px] font-medium text-slate-700 dark:text-slate-300">Daily Progress</span>
                                    </label>
                                    <label class="flex items-center gap-2 p-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-800/80 cursor-pointer hover:border-indigo-400 transition">
                                        <input type="checkbox" x-model="userForm.permissions.tab_activity_library" class="rounded text-indigo-600 focus:ring-indigo-500 w-3.5 h-3.5">
                                        <span class="text-[11px] font-medium text-slate-700 dark:text-slate-300">Activity Library</span>
                                    </label>
                                    <label class="flex items-center gap-2 p-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-800/80 cursor-pointer hover:border-indigo-400 transition">
                                        <input type="checkbox" x-model="userForm.permissions.tab_weather_config" class="rounded text-indigo-600 focus:ring-indigo-500 w-3.5 h-3.5">
                                        <span class="text-[11px] font-medium text-slate-700 dark:text-slate-300">Weather Config</span>
                                    </label>
                                    <label class="flex items-center gap-2 p-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-800/80 cursor-pointer hover:border-indigo-400 transition">
                                        <input type="checkbox" x-model="userForm.permissions.tab_audit" class="rounded text-indigo-600 focus:ring-indigo-500 w-3.5 h-3.5">
                                        <span class="text-[11px] font-medium text-slate-700 dark:text-slate-300">Audit Logs</span>
                                    </label>
                                    <label class="flex items-center gap-2 p-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-800/80 cursor-pointer hover:border-indigo-400 transition">
                                        <input type="checkbox" x-model="userForm.permissions.tab_companies" class="rounded text-indigo-600 focus:ring-indigo-500 w-3.5 h-3.5">
                                        <span class="text-[11px] font-medium text-slate-700 dark:text-slate-300">Contractor Companies</span>
                                    </label>
                                    <label class="flex items-center gap-2 p-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-800/80 cursor-pointer hover:border-indigo-400 transition">
                                        <input type="checkbox" x-model="userForm.permissions.tab_users" class="rounded text-indigo-600 focus:ring-indigo-500 w-3.5 h-3.5">
                                        <span class="text-[11px] font-medium text-slate-700 dark:text-slate-300">Users & Roles</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Action & Operations Privileges -->
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 block mb-1.5">Action Privileges & Authorizations</span>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    <label class="flex items-center gap-2 p-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-800/80 cursor-pointer hover:border-indigo-400 transition">
                                        <input type="checkbox" x-model="userForm.permissions.action_reschedule" class="rounded text-indigo-600 focus:ring-indigo-500 w-3.5 h-3.5">
                                        <div class="text-[11px] text-slate-700 dark:text-slate-300">
                                            <span class="font-bold block">Reschedule Activities</span>
                                            <span class="text-[10px] text-slate-400">Shift Gantt dates on weather alerts</span>
                                        </div>
                                    </label>
                                    <label class="flex items-center gap-2 p-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-800/80 cursor-pointer hover:border-indigo-400 transition">
                                        <input type="checkbox" x-model="userForm.permissions.action_continue_same_day" class="rounded text-indigo-600 focus:ring-indigo-500 w-3.5 h-3.5">
                                        <div class="text-[11px] text-slate-700 dark:text-slate-300">
                                            <span class="font-bold block">Continue Same Day</span>
                                            <span class="text-[10px] text-slate-400">Set on-site resume time mitigation</span>
                                        </div>
                                    </label>
                                    <label class="flex items-center gap-2 p-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-800/80 cursor-pointer hover:border-indigo-400 transition">
                                        <input type="checkbox" x-model="userForm.permissions.action_edit_progress" class="rounded text-indigo-600 focus:ring-indigo-500 w-3.5 h-3.5">
                                        <div class="text-[11px] text-slate-700 dark:text-slate-300">
                                            <span class="font-bold block">Log Daily Accomplishment</span>
                                            <span class="text-[10px] text-slate-400">Submit actual progress % updates</span>
                                        </div>
                                    </label>
                                    <label class="flex items-center gap-2 p-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-800/80 cursor-pointer hover:border-indigo-400 transition">
                                        <input type="checkbox" x-model="userForm.permissions.action_export_audit" class="rounded text-indigo-600 focus:ring-indigo-500 w-3.5 h-3.5">
                                        <div class="text-[11px] text-slate-700 dark:text-slate-300">
                                            <span class="font-bold block">Export Audit CSV</span>
                                            <span class="text-[10px] text-slate-400">Download decision log CSV records</span>
                                        </div>
                                    </label>
                                    <label class="flex items-center gap-2 p-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-800/80 cursor-pointer hover:border-indigo-400 transition">
                                        <input type="checkbox" x-model="userForm.permissions.action_manage_activities" class="rounded text-indigo-600 focus:ring-indigo-500 w-3.5 h-3.5">
                                        <div class="text-[11px] text-slate-700 dark:text-slate-300">
                                            <span class="font-bold block">Master Activity Library</span>
                                            <span class="text-[10px] text-slate-400">Add, edit, delete preset activities</span>
                                        </div>
                                    </label>
                                    <label class="flex items-center gap-2 p-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-800/80 cursor-pointer hover:border-indigo-400 transition">
                                        <input type="checkbox" x-model="userForm.permissions.action_manage_companies" class="rounded text-indigo-600 focus:ring-indigo-500 w-3.5 h-3.5">
                                        <div class="text-[11px] text-slate-700 dark:text-slate-300">
                                            <span class="font-bold block">Contractor Companies</span>
                                            <span class="text-[10px] text-slate-400">Manage partner organizations</span>
                                        </div>
                                    </label>
                                    <label class="flex items-center gap-2 p-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-800/80 cursor-pointer hover:border-indigo-400 transition">
                                        <input type="checkbox" x-model="userForm.permissions.action_manage_users" class="rounded text-indigo-600 focus:ring-indigo-500 w-3.5 h-3.5">
                                        <div class="text-[11px] text-slate-700 dark:text-slate-300">
                                            <span class="font-bold block">User & Access Management</span>
                                            <span class="text-[10px] text-slate-400">Administer users & permissions</span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100 dark:border-slate-800">
                            <button type="button" @click="userModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-500 hover:text-slate-700">Cancel</button>
                            <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-500/20">
                                <span x-text="editingUserId ? 'Update User Account' : 'Create User Account'"></span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- MASTER ACTIVITY MODAL: ADD / EDIT PREDEFINED CONSTRUCTION ACTIVITY        -->
        <!-- ========================================================================= -->
        <div x-show="masterActivityModalOpen" x-cloak class="relative z-50">
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm transition-opacity" @click="masterActivityModalOpen = false"></div>
            
            <div class="fixed inset-0 z-10 overflow-y-auto flex items-center justify-center p-4">
                <div class="w-full max-w-xl bg-white dark:bg-[#131a2c] border border-slate-200 dark:border-slate-700 rounded-3xl shadow-2xl p-6 space-y-5 text-left" @click.stop>
                    
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                                <i data-lucide="hammer" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white" x-text="editingMasterActivityId ? 'Edit Construction Activity Template' : 'Register Predefined Construction Activity'"></h3>
                                <p class="text-xs text-slate-400">Master database activity template & weather sensitivity rules</p>
                            </div>
                        </div>
                        <button @click="masterActivityModalOpen = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <form @submit.prevent="saveMasterActivity()" class="space-y-4 text-xs">
                        <div class="grid grid-cols-3 gap-3">
                            <div class="col-span-2">
                                <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Activity Name *</label>
                                <input type="text" x-model="masterActivityForm.name" required placeholder="e.g. Structural Concrete Pouring"
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-semibold text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Catalog Code</label>
                                <input type="text" x-model="masterActivityForm.code" required placeholder="ACT-STR-07"
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-semibold text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Activity Category *</label>
                                <select x-model="masterActivityForm.category"
                                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-semibold text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="Structural">Structural (Concrete / Steel / Rebar)</option>
                                    <option value="Heavy Equipment">Heavy Equipment (Crane / Hoist / Rig)</option>
                                    <option value="Enclosure">Enclosure (Waterproofing / Glazing)</option>
                                    <option value="Substructure">Substructure (Excavation / Piling)</option>
                                    <option value="Finishes">Finishes (Painting / Plastering)</option>
                                </select>
                            </div>

                            <div>
                                <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Default Duration (Days)</label>
                                <input type="number" min="1" max="30" x-model="masterActivityForm.defaultDuration" required
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-semibold text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>

                        <div>
                            <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Predecessor Recommendation / Default Dependency</label>
                            <input type="text" x-model="masterActivityForm.predecessorHint" placeholder="e.g. Formwork Assembly & Level Survey"
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-semibold text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <!-- Weather Threshold Limits Matrix -->
                        <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-2.5">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Automated Weather Assessment Thresholds</p>
                            
                            <div class="grid grid-cols-3 gap-2.5">
                                <div>
                                    <label class="font-semibold text-slate-600 dark:text-slate-400 block mb-1">Max Rain %</label>
                                    <div class="relative">
                                        <input type="number" min="0" max="100" x-model="masterActivityForm.maxRainProb" required
                                               class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-bold text-amber-600">
                                    </div>
                                </div>
                                <div>
                                    <label class="font-semibold text-slate-600 dark:text-slate-400 block mb-1">Max Rain (mm/hr)</label>
                                    <input type="number" step="0.1" min="0" max="50" x-model="masterActivityForm.maxRainVol" required
                                           class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-bold text-slate-800 dark:text-white">
                                </div>
                                <div>
                                    <label class="font-semibold text-slate-600 dark:text-slate-400 block mb-1">Max Wind (km/h)</label>
                                    <input type="number" min="0" max="120" x-model="masterActivityForm.maxWind" required
                                           class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-bold text-slate-800 dark:text-white">
                                </div>
                            </div>

                            <div>
                                <label class="font-semibold text-slate-600 dark:text-slate-400 block mb-1">Worker Safety Trigger (OSHA Compliance)</label>
                                <input type="text" x-model="masterActivityForm.safetyTrigger" required placeholder="e.g. Rainfall > 3mm/hr (Slippery Surfaces)"
                                       class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-rose-600 dark:text-rose-400">
                            </div>
                        </div>

                        <div>
                            <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Engineering Description & Scope</label>
                            <textarea x-model="masterActivityForm.description" rows="2" placeholder="Describe the physical work and engineering conditions..."
                                      class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-semibold text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100 dark:border-slate-800">
                            <button type="button" @click="masterActivityModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-500 hover:text-slate-700">Cancel</button>
                            <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-500/20">
                                <span x-text="editingMasterActivityId ? 'Update Activity Template' : 'Save to Activity Library'"></span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

    </div>

    <!-- APP JAVASCRIPT STATE ENGINE -->
    <script>
        function prototypeApp() {
            return {
                isDarkMode: false,
                sidebarOpen: false,
                activeTab: 'dashboard',
                isLoading: false,
                weatherProvider: 'Open-Meteo',
                chartInstance: null,
                todayDate: '{{ now()->format("M d") }}',
                highlightedDate: '{{ now()->format("M d") }}',
                
                // Drawer & Modals State
                drawerOpen: false,
                continueModalOpen: false,
                rescheduleModalOpen: false,
                progressModalOpen: false,
                logModalOpen: false,
                
                // Project, Company & Activity CRUD Modals
                companyModalOpen: false,
                projectModalOpen: false,
                projectDirectoryOpen: false,
                activityModalOpen: false,
                userModalOpen: false,
                masterActivityModalOpen: false,
                editingCompanyId: null,
                editingProjectId: null,
                editingActivityId: null,
                editingUserId: null,
                editingMasterActivityId: null,

                companyForm: {
                    name: '',
                    code: '',
                    contact_person: '',
                    contact_email: '',
                    contact_phone: '',
                    address: 'CLSU Campus, Science City of Muñoz, Nueva Ecija',
                    status: 'Active'
                },
                companySearch: '',
                companyStatusFilter: 'ALL',

                masterActivityForm: {
                    code: '',
                    name: '',
                    category: 'Structural',
                    defaultDuration: 3,
                    maxRainProb: 40,
                    maxRainVol: 2.5,
                    maxWind: 40,
                    safetyTrigger: 'Rainfall > 3mm/hr',
                    predecessorHint: 'Rebar Installation',
                    description: ''
                },
                activityLibrarySearch: '',
                activityCategoryFilter: 'ALL',

                userForm: {
                    name: '',
                    email: '',
                    roleId: 'site_supervisor',
                    companyId: 1,
                    status: 'Active',
                    permissions: {
                        tab_dashboard: true,
                        tab_scheduling: true,
                        tab_projects: true,
                        tab_companies: false,
                        tab_progress: true,
                        tab_activity_library: false,
                        tab_weather_config: false,
                        tab_audit: false,
                        tab_users: false,
                        action_reschedule: false,
                        action_continue_same_day: false,
                        action_edit_progress: true,
                        action_manage_activities: false,
                        action_manage_projects: false,
                        action_manage_companies: false,
                        action_manage_users: false,
                        action_export_audit: false
                    }
                },
                userSearch: '',
                userRoleFilter: 'ALL',
                userCompanyFilter: 'ALL',

                projectForm: {
                    name: '',
                    code: '',
                    location: '',
                    companyId: 1,
                    storeys: 32,
                    durationWeeks: 12
                },

                activityForm: {
                    name: '',
                    predecessor: '',
                    durationDays: 2,
                    startCol: 3,
                    weatherRuleText: 'Rain < 40%, No Heavy Rain'
                },
                
                selectedActivity: null,
                selectedLog: null,
                resumeTime: '13:00',
                continuationMitigationNotes: 'On-site mitigation applied (protective covers / rapid-cure additive)',
                newScheduledDate: 'Oct 02',
                successorAction: 'adjust',

                // Progress Update Modal State (Direct Multi-Activity Accomplishment)
                progressEntries: [],
                progressReportNotes: '',
                selectedProgressActivityId: 1,
                newActualValue: 90,

                // Audit Trail Filters
                auditCompanyFilter: 'ALL',
                auditEventFilter: 'ALL',
                auditDecisionFilter: 'ALL',

                // Roles List (Aligned PPSDS University Owner vs Contractor Implementer Dynamics)
                roles: [
                    { id: 'admin', title: 'CLSU - PPSDS Administrator', short: 'PPSDS Admin', desc: 'University Physical Plant admin: system governance, contractor registry, user permissions, audit trail.' },
                    { id: 'project_engineer', title: 'PPSDS Project Engineer (Owner / Reviewer)', short: 'PPSDS Engineer', desc: 'Monitors campus vertical building projects, reviews weather risks, and approves/reschedules timelines.' },
                    { id: 'site_supervisor', title: 'Contractor Site Supervisor (Implementer / Submitter)', short: 'Site Supervisor', desc: 'On-site contractor supervisor submitting daily accomplishment progress and responding to weather mitigation alerts.' },
                    { id: 'contractor', title: 'Contractor Project Manager (Contractor Lead)', short: 'Contractor PM', desc: 'Oversees contractor-awarded building projects, views approved schedules, and monitors baseline variance.' }
                ],
                currentRole: { id: 'project_engineer', title: 'PPSDS Project Engineer (Owner / Reviewer)', short: 'PPSDS Engineer', desc: 'Monitors campus vertical building projects, reviews weather risks, and approves/reschedules timelines.' },

                currentUser: {
                    id: {{ Auth::id() ?? 1 }},
                    name: '{{ Auth::user()->name ?? "Engr. Juan Dela Cruz" }}',
                    email: '{{ Auth::user()->email ?? "engineer@vertical.ph" }}',
                    roleId: '{{ Auth::user()->role ?? "project_engineer" }}',
                    companyId: {{ Auth::user()->company_id ?? 1 }},
                    initials: '{{ Auth::user()->initials ?? "JD" }}',
                    status: 'Active',
                    lastActive: 'Just now'
                },

                // Live Weather Data from Open-Meteo / PAGASA
                selectedWeatherSource: 'open-meteo',
                weatherViewMode: '7days',
                selectedWeatherMonth: 'October',
                weatherProvider: '{{ $weatherData["provider"] ?? "Open-Meteo REST API" }}',
                weatherOutlook: @json($weatherData['daily'] ?? []),
                openMeteoOutlook: @json($weatherData['daily'] ?? []),
                pagasaConnectionError: 'Unable to fetch data from DOST-PAGASA API: Missing API Key / Institutional Credentials.',

                // Available Timeline Months & View Mode
                ganttViewMode: '7days',
                selectedGanttMonth: 'October',
                selectedGanttWeek: 'all',
                availableMonths: [
                    { name: 'September', label: 'Sep 2026' },
                    { name: 'October', label: 'Oct 2026' },
                    { name: 'November', label: 'Nov 2026' }
                ],

                // S-Curve Analytics Backend Dataset
                sCurveLabels: @json($sCurveData['labels']),
                sCurvePlanned: @json($sCurveData['planned']),
                sCurveActual: @json($sCurveData['actual']),
                sCurveForecast: @json($sCurveData['forecast']),

                // Companies List (Eloquent)
                companies: @json($companies),
                currentCompany: @json($currentCompany),

                // Projects Master & Filtered Lists (Eloquent)
                allProjects: @json($allProjects ?? $projects),
                projects: @json($projects),
                currentProject: @json($currentProject),
                projectCompanyFilter: 'ALL',

                // Predefined Master Activities Catalog (Eloquent)
                masterActivities: @json($formattedMasterActivities),

                // Dynamic Database Users List (Eloquent)
                users: @json($formattedUsers),

                // Multi-Month Timeline Master Data Dictionary (7-Day Focused & 30-Day Full Horizon)
                timelineSchedules: {
                    'September': {
                        dates: [
                            { date: 'Sep 13', dayName: 'Sun', icon: 'sun', rainProb: 10, risk: 'low', isExtended: false },
                            { date: 'Sep 14', dayName: 'Mon', icon: 'cloud-sun', rainProb: 20, risk: 'low', isExtended: false },
                            { date: 'Sep 15', dayName: 'Tue', icon: 'cloud-sun', rainProb: 25, risk: 'low', isExtended: false },
                            { date: 'Sep 16', dayName: 'Wed', icon: 'cloud-drizzle', rainProb: 40, risk: 'medium', isExtended: false },
                            { date: 'Sep 17', dayName: 'Thu', icon: 'cloud-rain', rainProb: 65, risk: 'high', isExtended: false },
                            { date: 'Sep 18', dayName: 'Fri', icon: 'sun', rainProb: 15, risk: 'low', isExtended: false },
                            { date: 'Sep 19', dayName: 'Sat', icon: 'sun', rainProb: 10, risk: 'low', isExtended: false },
                            { date: 'Sep 20', dayName: 'Sun', icon: 'sun', rainProb: 10, risk: 'low', isExtended: false }
                        ],
                        monthDates: [
                            { date: 'Sep 01', dayName: 'Tue', icon: 'sun', rainProb: 10, risk: 'low', isExtended: false },
                            { date: 'Sep 02', dayName: 'Wed', icon: 'sun', rainProb: 15, risk: 'low', isExtended: false },
                            { date: 'Sep 03', dayName: 'Thu', icon: 'cloud-sun', rainProb: 20, risk: 'low', isExtended: false },
                            { date: 'Sep 04', dayName: 'Fri', icon: 'cloud-sun', rainProb: 25, risk: 'low', isExtended: false },
                            { date: 'Sep 05', dayName: 'Sat', icon: 'sun', rainProb: 10, risk: 'low', isExtended: false },
                            { date: 'Sep 06', dayName: 'Sun', icon: 'sun', rainProb: 10, risk: 'low', isExtended: false },
                            { date: 'Sep 07', dayName: 'Mon', icon: 'cloud-drizzle', rainProb: 35, risk: 'medium', isExtended: false },
                            { date: 'Sep 08', dayName: 'Tue', icon: 'sun', rainProb: 15, risk: 'low', isExtended: false },
                            { date: 'Sep 09', dayName: 'Wed', icon: 'sun', rainProb: 10, risk: 'low', isExtended: false },
                            { date: 'Sep 10', dayName: 'Thu', icon: 'cloud-sun', rainProb: 20, risk: 'low', isExtended: false },
                            { date: 'Sep 11', dayName: 'Fri', icon: 'cloud-sun', rainProb: 25, risk: 'low', isExtended: false },
                            { date: 'Sep 12', dayName: 'Sat', icon: 'cloud-drizzle', rainProb: 40, risk: 'medium', isExtended: false },
                            { date: 'Sep 13', dayName: 'Sun', icon: 'sun', rainProb: 10, risk: 'low', isExtended: false },
                            { date: 'Sep 14', dayName: 'Mon', icon: 'cloud-rain', rainProb: 65, risk: 'high', isExtended: false },
                            { date: 'Sep 15', dayName: 'Tue', icon: 'calendar-check', rainProb: 15, risk: 'low', isExtended: true },
                            { date: 'Sep 16', dayName: 'Wed', icon: 'calendar-check', rainProb: 15, risk: 'low', isExtended: true },
                            { date: 'Sep 17', dayName: 'Thu', icon: 'calendar-check', rainProb: 20, risk: 'low', isExtended: true },
                            { date: 'Sep 18', dayName: 'Fri', icon: 'calendar-check', rainProb: 20, risk: 'low', isExtended: true },
                            { date: 'Sep 19', dayName: 'Sat', icon: 'calendar-check', rainProb: 10, risk: 'low', isExtended: true },
                            { date: 'Sep 20', dayName: 'Sun', icon: 'calendar-check', rainProb: 10, risk: 'low', isExtended: true },
                            { date: 'Sep 21', dayName: 'Mon', icon: 'calendar-check', rainProb: 15, risk: 'low', isExtended: true },
                            { date: 'Sep 22', dayName: 'Tue', icon: 'calendar-check', rainProb: 15, risk: 'low', isExtended: true },
                            { date: 'Sep 23', dayName: 'Wed', icon: 'calendar-check', rainProb: 25, risk: 'low', isExtended: true },
                            { date: 'Sep 24', dayName: 'Thu', icon: 'calendar-check', rainProb: 25, risk: 'low', isExtended: true },
                            { date: 'Sep 25', dayName: 'Fri', icon: 'calendar-check', rainProb: 15, risk: 'low', isExtended: true },
                            { date: 'Sep 26', dayName: 'Sat', icon: 'calendar-check', rainProb: 10, risk: 'low', isExtended: true },
                            { date: 'Sep 27', dayName: 'Sun', icon: 'calendar-check', rainProb: 15, risk: 'low', isExtended: true },
                            { date: 'Sep 28', dayName: 'Mon', icon: 'calendar-check', rainProb: 20, risk: 'low', isExtended: true },
                            { date: 'Sep 29', dayName: 'Tue', icon: 'calendar-check', rainProb: 15, risk: 'low', isExtended: true },
                            { date: 'Sep 30', dayName: 'Wed', icon: 'calendar-check', rainProb: 10, risk: 'low', isExtended: true }
                        ],
                        activities: [],
                        monthActivities: []
                    },
                    'October': {
                        dates: [
                            { date: 'Oct 01', dayName: 'Thu', icon: 'cloud-lightning', rainProb: 80, risk: 'high', isExtended: false },
                            { date: 'Oct 02', dayName: 'Fri', icon: 'cloud-sun', rainProb: 20, risk: 'low', isExtended: false },
                            { date: 'Oct 03', dayName: 'Sat', icon: 'sun', rainProb: 10, risk: 'low', isExtended: false },
                            { date: 'Oct 04', dayName: 'Sun', icon: 'sun', rainProb: 15, risk: 'low', isExtended: false },
                            { date: 'Oct 05', dayName: 'Mon', icon: 'cloud-rain', rainProb: 75, risk: 'high', isExtended: false },
                            { date: 'Oct 06', dayName: 'Tue', icon: 'cloud-drizzle', rainProb: 45, risk: 'medium', isExtended: false },
                            { date: 'Oct 07', dayName: 'Wed', icon: 'cloud-sun', rainProb: 25, risk: 'low', isExtended: false },
                            { date: 'Oct 08', dayName: 'Thu', icon: 'sun', rainProb: 10, risk: 'low', isExtended: false }
                        ],
                        monthDates: [
                            { date: 'Oct 01', dayName: 'Thu', icon: 'cloud-lightning', rainProb: 80, risk: 'high', isExtended: false },
                            { date: 'Oct 02', dayName: 'Fri', icon: 'cloud-sun', rainProb: 20, risk: 'low', isExtended: false },
                            { date: 'Oct 03', dayName: 'Sat', icon: 'sun', rainProb: 10, risk: 'low', isExtended: false },
                            { date: 'Oct 04', dayName: 'Sun', icon: 'sun', rainProb: 15, risk: 'low', isExtended: false },
                            { date: 'Oct 05', dayName: 'Mon', icon: 'cloud-rain', rainProb: 75, risk: 'high', isExtended: false },
                            { date: 'Oct 06', dayName: 'Tue', icon: 'cloud-drizzle', rainProb: 45, risk: 'medium', isExtended: false },
                            { date: 'Oct 07', dayName: 'Wed', icon: 'cloud-sun', rainProb: 25, risk: 'low', isExtended: false },
                            { date: 'Oct 08', dayName: 'Thu', icon: 'sun', rainProb: 10, risk: 'low', isExtended: false },
                            { date: 'Oct 09', dayName: 'Fri', icon: 'sun', rainProb: 15, risk: 'low', isExtended: false },
                            { date: 'Oct 10', dayName: 'Sat', icon: 'cloud-rain', rainProb: 70, risk: 'high', isExtended: false },
                            { date: 'Oct 11', dayName: 'Sun', icon: 'cloud-sun', rainProb: 20, risk: 'low', isExtended: false },
                            { date: 'Oct 12', dayName: 'Mon', icon: 'sun', rainProb: 10, risk: 'low', isExtended: false },
                            { date: 'Oct 13', dayName: 'Tue', icon: 'sun', rainProb: 15, risk: 'low', isExtended: false },
                            { date: 'Oct 14', dayName: 'Wed', icon: 'cloud-sun', rainProb: 20, risk: 'low', isExtended: false },
                            { date: 'Oct 15', dayName: 'Thu', icon: 'calendar-check', rainProb: 15, risk: 'low', isExtended: true },
                            { date: 'Oct 16', dayName: 'Fri', icon: 'calendar-check', rainProb: 15, risk: 'low', isExtended: true },
                            { date: 'Oct 17', dayName: 'Sat', icon: 'calendar-check', rainProb: 10, risk: 'low', isExtended: true },
                            { date: 'Oct 18', dayName: 'Sun', icon: 'calendar-check', rainProb: 10, risk: 'low', isExtended: true },
                            { date: 'Oct 19', dayName: 'Mon', icon: 'calendar-check', rainProb: 20, risk: 'low', isExtended: true },
                            { date: 'Oct 20', dayName: 'Tue', icon: 'calendar-check', rainProb: 20, risk: 'low', isExtended: true },
                            { date: 'Oct 21', dayName: 'Wed', icon: 'calendar-check', rainProb: 15, risk: 'low', isExtended: true },
                            { date: 'Oct 22', dayName: 'Thu', icon: 'calendar-check', rainProb: 15, risk: 'low', isExtended: true },
                            { date: 'Oct 23', dayName: 'Fri', icon: 'calendar-check', rainProb: 25, risk: 'low', isExtended: true },
                            { date: 'Oct 24', dayName: 'Sat', icon: 'calendar-check', rainProb: 15, risk: 'low', isExtended: true },
                            { date: 'Oct 25', dayName: 'Sun', icon: 'calendar-check', rainProb: 10, risk: 'low', isExtended: true },
                            { date: 'Oct 26', dayName: 'Mon', icon: 'calendar-check', rainProb: 15, risk: 'low', isExtended: true },
                            { date: 'Oct 27', dayName: 'Tue', icon: 'calendar-check', rainProb: 15, risk: 'low', isExtended: true },
                            { date: 'Oct 28', dayName: 'Wed', icon: 'calendar-check', rainProb: 20, risk: 'low', isExtended: true },
                            { date: 'Oct 29', dayName: 'Thu', icon: 'calendar-check', rainProb: 15, risk: 'low', isExtended: true },
                            { date: 'Oct 30', dayName: 'Fri', icon: 'calendar-check', rainProb: 10, risk: 'low', isExtended: true },
                            { date: 'Oct 31', dayName: 'Sat', icon: 'calendar-check', rainProb: 10, risk: 'low', isExtended: true }
                        ],
                        activities: @json($formattedActivities),
                        monthActivities: @json($formattedActivities)
                    },
                    'November': {
                        dates: [
                            { date: 'Nov 01', dayName: 'Sun', icon: 'sun', rainProb: 10, risk: 'low', isExtended: false },
                            { date: 'Nov 02', dayName: 'Mon', icon: 'sun', rainProb: 15, risk: 'low', isExtended: false },
                            { date: 'Nov 03', dayName: 'Tue', icon: 'cloud-sun', rainProb: 25, risk: 'low', isExtended: false },
                            { date: 'Nov 04', dayName: 'Wed', icon: 'wind', rainProb: 30, risk: 'medium', isExtended: false },
                            { date: 'Nov 05', dayName: 'Thu', icon: 'cloud-rain', rainProb: 65, risk: 'high', isExtended: false },
                            { date: 'Nov 06', dayName: 'Fri', icon: 'cloud-sun', rainProb: 20, risk: 'low', isExtended: false },
                            { date: 'Nov 07', dayName: 'Sat', icon: 'sun', rainProb: 15, risk: 'low', isExtended: false },
                            { date: 'Nov 08', dayName: 'Sun', icon: 'sun', rainProb: 10, risk: 'low', isExtended: false }
                        ],
                        monthDates: [
                            { date: 'Nov 01', dayName: 'Sun', icon: 'sun', rainProb: 10, risk: 'low', isExtended: false },
                            { date: 'Nov 02', dayName: 'Mon', icon: 'sun', rainProb: 15, risk: 'low', isExtended: false },
                            { date: 'Nov 03', dayName: 'Tue', icon: 'cloud-sun', rainProb: 25, risk: 'low', isExtended: false },
                            { date: 'Nov 04', dayName: 'Wed', icon: 'wind', rainProb: 30, risk: 'medium', isExtended: false },
                            { date: 'Nov 05', dayName: 'Thu', icon: 'cloud-rain', rainProb: 65, risk: 'high', isExtended: false },
                            { date: 'Nov 06', dayName: 'Fri', icon: 'cloud-sun', rainProb: 20, risk: 'low', isExtended: false },
                            { date: 'Nov 07', dayName: 'Sat', icon: 'sun', rainProb: 15, risk: 'low', isExtended: false },
                            { date: 'Nov 08', dayName: 'Sun', icon: 'sun', rainProb: 10, risk: 'low', isExtended: false },
                            { date: 'Nov 09', dayName: 'Mon', icon: 'wind', rainProb: 45, risk: 'high', isExtended: false },
                            { date: 'Nov 10', dayName: 'Tue', icon: 'sun', rainProb: 15, risk: 'low', isExtended: false },
                            { date: 'Nov 11', dayName: 'Wed', icon: 'cloud-sun', rainProb: 20, risk: 'low', isExtended: false },
                            { date: 'Nov 12', dayName: 'Thu', icon: 'cloud-drizzle', rainProb: 35, risk: 'medium', isExtended: false },
                            { date: 'Nov 13', dayName: 'Fri', icon: 'sun', rainProb: 10, risk: 'low', isExtended: false },
                            { date: 'Nov 14', dayName: 'Sat', icon: 'sun', rainProb: 10, risk: 'low', isExtended: false },
                            { date: 'Nov 15', dayName: 'Sun', icon: 'calendar-check', rainProb: 15, risk: 'low', isExtended: true },
                            { date: 'Nov 16', dayName: 'Mon', icon: 'calendar-check', rainProb: 15, risk: 'low', isExtended: true },
                            { date: 'Nov 17', dayName: 'Tue', icon: 'calendar-check', rainProb: 20, risk: 'low', isExtended: true },
                            { date: 'Nov 18', dayName: 'Wed', icon: 'calendar-check', rainProb: 20, risk: 'low', isExtended: true },
                            { date: 'Nov 19', dayName: 'Thu', icon: 'calendar-check', rainProb: 10, risk: 'low', isExtended: true },
                            { date: 'Nov 20', dayName: 'Fri', icon: 'calendar-check', rainProb: 10, risk: 'low', isExtended: true },
                            { date: 'Nov 21', dayName: 'Sat', icon: 'calendar-check', rainProb: 15, risk: 'low', isExtended: true },
                            { date: 'Nov 22', dayName: 'Sun', icon: 'calendar-check', rainProb: 15, risk: 'low', isExtended: true },
                            { date: 'Nov 23', dayName: 'Mon', icon: 'calendar-check', rainProb: 25, risk: 'low', isExtended: true },
                            { date: 'Nov 24', dayName: 'Tue', icon: 'calendar-check', rainProb: 25, risk: 'low', isExtended: true },
                            { date: 'Nov 25', dayName: 'Wed', icon: 'calendar-check', rainProb: 15, risk: 'low', isExtended: true },
                            { date: 'Nov 26', dayName: 'Thu', icon: 'calendar-check', rainProb: 10, risk: 'low', isExtended: true },
                            { date: 'Nov 27', dayName: 'Fri', icon: 'calendar-check', rainProb: 15, risk: 'low', isExtended: true },
                            { date: 'Nov 28', dayName: 'Sat', icon: 'calendar-check', rainProb: 20, risk: 'low', isExtended: true },
                            { date: 'Nov 29', dayName: 'Sun', icon: 'calendar-check', rainProb: 15, risk: 'low', isExtended: true },
                            { date: 'Nov 30', dayName: 'Mon', icon: 'calendar-check', rainProb: 10, risk: 'low', isExtended: true }
                        ],
                        activities: [],
                        monthActivities: []
                    }
                },

                // Gantt Chart Date Columns (Active Month)
                ganttDates: [
                    { date: 'Oct 01', dayName: 'Thu', icon: 'cloud-lightning', rainProb: 80, risk: 'high', isExtended: false },
                    { date: 'Oct 02', dayName: 'Fri', icon: 'cloud-sun', rainProb: 20, risk: 'low', isExtended: false },
                    { date: 'Oct 03', dayName: 'Sat', icon: 'sun', rainProb: 10, risk: 'low', isExtended: false },
                    { date: 'Oct 04', dayName: 'Sun', icon: 'sun', rainProb: 15, risk: 'low', isExtended: false },
                    { date: 'Oct 05', dayName: 'Mon', icon: 'cloud-rain', rainProb: 75, risk: 'high', isExtended: false },
                    { date: 'Oct 06', dayName: 'Tue', icon: 'cloud-drizzle', rainProb: 45, risk: 'medium', isExtended: false },
                    { date: 'Oct 07', dayName: 'Wed', icon: 'cloud-sun', rainProb: 25, risk: 'low', isExtended: false },
                    { date: 'Oct 08', dayName: 'Thu', icon: 'sun', rainProb: 10, risk: 'low', isExtended: false },
                ],

                // Scheduled Construction Activities (Active Month - Eloquent Dynamic)
                activities: @json($formattedActivities),

                // 3-Shade Progress Data Model (Direct Eloquent Sync)
                progressData: @json($formattedProgress),

                // Active Notifications & Weather Alerts Center (Dynamic)
                notifications: [],

                // Append-Only Activity Log & Thesis Field-Testing Audit Trail (Direct Eloquent Sync)
                auditLogs: @json($formattedAuditLogs),

                // Predefined Construction Activity Database threshold matrix
                thresholdMatrix: [
                    { activity: 'Concrete Pouring', maxRainProb: 40, maxRainVol: 2.5, maxWind: 40, safetyTrigger: 'Rainfall > 3mm/hr' },
                    { activity: 'Tower Crane Operations', maxRainProb: 65, maxRainVol: 10.0, maxWind: 28, safetyTrigger: 'Wind Gusts > 30 km/h' },
                    { activity: 'Exterior Painting & Waterproofing', maxRainProb: 30, maxRainVol: 0.5, maxWind: 35, safetyTrigger: 'Humidity > 85%' },
                    { activity: 'Structural Steel Erection', maxRainProb: 50, maxRainVol: 5.0, maxWind: 32, safetyTrigger: 'Lightning / Thunderstorm' },
                    { activity: 'Rebar & Formworks', maxRainProb: 75, maxRainVol: 15.0, maxWind: 50, safetyTrigger: 'Slippery Surfaces Warning' }
                ],

                toasts: [],

                initApp() {
                    const savedTheme = localStorage.getItem('theme');
                    if (savedTheme) {
                        this.isDarkMode = savedTheme === 'dark';
                    }

                    if (!this.highlightedDate) {
                        this.highlightedDate = this.todayDate;
                    }
                    
                    this.$nextTick(() => {
                        lucide.createIcons();
                        setTimeout(() => {
                            this.renderSCurveChart();
                        }, 100);
                    });
                    
                    setTimeout(() => {
                        this.addToast('System Connected', `Loaded ${this.currentProject?.name || 'CLSU Campus'} weather telemetry.`, 'info', 'check-circle');
                    }, 800);
                },

                closeAllModals() {
                    this.drawerOpen = false;
                    this.continueModalOpen = false;
                    this.rescheduleModalOpen = false;
                    this.progressModalOpen = false;
                    this.logModalOpen = false;
                    this.projectModalOpen = false;
                    this.projectDirectoryOpen = false;
                    this.activityModalOpen = false;
                    this.userModalOpen = false;
                    this.masterActivityModalOpen = false;
                },

                countActiveAlerts() {
                    return this.activities.filter(a => a.hasAlert).length;
                },

                switchWeatherSource(source) {
                    this.selectedWeatherSource = source;
                    if (source === 'pagasa') {
                        this.weatherProvider = 'DOST-PAGASA (Disconnected)';
                        this.weatherOutlook = [];
                        this.addToast('PAGASA API Connection Failed', 'Unable to fetch data: Missing API Key / Institutional Credentials.', 'error', 'cloud-off');
                    } else {
                        this.weatherProvider = 'Open-Meteo Synced';
                        this.weatherOutlook = JSON.parse(JSON.stringify(this.openMeteoOutlook));
                        this.addToast('Open-Meteo Connected', 'Switched to Open-Meteo live numerical satellite forecast telemetry.', 'success', 'cloud');
                    }
                    this.$nextTick(() => {
                        lucide.createIcons();
                    });
                },

                getMonthlyWeatherDays() {
                    const month = this.selectedWeatherMonth || 'October';
                    if (month === 'September') {
                        return Array.from({ length: 30 }, (_, i) => {
                            const d = i + 1;
                            const dStr = d < 10 ? '0' + d : '' + d;
                            const days = ['Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun', 'Mon'];
                            const dayName = days[(d - 1) % 7];
                            let rainProb = 15;
                            let risk = 'low';
                            let icon = 'sun';
                            let iconColor = 'text-amber-500';
                            let tempMax = 32;

                            if (d === 16 || d === 17) {
                                rainProb = 65;
                                risk = 'high';
                                icon = 'cloud-rain';
                                iconColor = 'text-rose-500';
                                tempMax = 27;
                            } else if (d >= 28 && d <= 30) {
                                rainProb = 45;
                                risk = 'medium';
                                icon = 'cloud-drizzle';
                                iconColor = 'text-indigo-500 dark:text-sky-300';
                                tempMax = 29;
                            } else if (d % 4 === 0) {
                                rainProb = 30;
                                risk = 'low';
                                icon = 'cloud-sun';
                                iconColor = 'text-indigo-500 dark:text-sky-400';
                                tempMax = 31;
                            }

                            return {
                                dayNumber: d,
                                date: `Sep ${dStr}`,
                                dayName: dayName,
                                icon: icon,
                                iconColor: iconColor,
                                temp: `${tempMax}°C`,
                                rainProb: rainProb,
                                risk: risk
                            };
                        });
                    } else if (month === 'November') {
                        return Array.from({ length: 30 }, (_, i) => {
                            const d = i + 1;
                            const dStr = d < 10 ? '0' + d : '' + d;
                            const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                            const dayName = days[(d - 1) % 7];
                            let rainProb = 10;
                            let risk = 'low';
                            let icon = 'sun';
                            let iconColor = 'text-amber-500';
                            let tempMax = 31;

                            if (d === 8 || d === 9 || d === 22) {
                                rainProb = 55;
                                risk = 'medium';
                                icon = 'cloud-rain';
                                iconColor = 'text-amber-500';
                                tempMax = 28;
                            } else if (d === 15) {
                                rainProb = 75;
                                risk = 'high';
                                icon = 'cloud-lightning';
                                iconColor = 'text-rose-500';
                                tempMax = 26;
                            } else if (d % 3 === 0) {
                                rainProb = 20;
                                risk = 'low';
                                icon = 'cloud-sun';
                                iconColor = 'text-indigo-500 dark:text-sky-400';
                                tempMax = 30;
                            }

                            return {
                                dayNumber: d,
                                date: `Nov ${dStr}`,
                                dayName: dayName,
                                icon: icon,
                                iconColor: iconColor,
                                temp: `${tempMax}°C`,
                                rainProb: rainProb,
                                risk: risk
                            };
                        });
                    } else {
                        // October (31 days) - Default active thesis month
                        return Array.from({ length: 31 }, (_, i) => {
                            const d = i + 1;
                            const dStr = d < 10 ? '0' + d : '' + d;
                            const days = ['Thu', 'Fri', 'Sat', 'Sun', 'Mon', 'Tue', 'Wed'];
                            const dayName = days[(d - 1) % 7];
                            let rainProb = 15;
                            let risk = 'low';
                            let icon = 'sun';
                            let iconColor = 'text-amber-500';
                            let tempMax = 32;

                            if (d === 1) {
                                rainProb = 80;
                                risk = 'high';
                                icon = 'cloud-lightning';
                                iconColor = 'text-rose-500';
                                tempMax = 26;
                            } else if (d === 2) {
                                rainProb = 20;
                                risk = 'low';
                                icon = 'cloud-sun';
                                iconColor = 'text-indigo-500 dark:text-sky-400';
                                tempMax = 30;
                            } else if (d === 3) {
                                rainProb = 10;
                                risk = 'low';
                                icon = 'sun';
                                iconColor = 'text-amber-500';
                                tempMax = 33;
                            } else if (d === 12 || d === 13) {
                                rainProb = 70;
                                risk = 'high';
                                icon = 'cloud-rain';
                                iconColor = 'text-rose-500';
                                tempMax = 27;
                            } else if (d === 18 || d === 25) {
                                rainProb = 45;
                                risk = 'medium';
                                icon = 'cloud-drizzle';
                                iconColor = 'text-indigo-600 dark:text-sky-300';
                                tempMax = 29;
                            } else if (d % 3 === 0) {
                                rainProb = 25;
                                risk = 'low';
                                icon = 'cloud-sun';
                                iconColor = 'text-indigo-500 dark:text-sky-400';
                                tempMax = 31;
                            }

                            return {
                                dayNumber: d,
                                date: `Oct ${dStr}`,
                                dayName: dayName,
                                icon: icon,
                                iconColor: iconColor,
                                temp: `${tempMax}°C`,
                                rainProb: rainProb,
                                risk: risk
                            };
                        });
                    }
                },

                getMonthlyWorkableCount() {
                    const days = this.getMonthlyWeatherDays();
                    return days.filter(d => d.risk === 'low').length;
                },

                getMonthlyAlertCount() {
                    const days = this.getMonthlyWeatherDays();
                    return days.filter(d => d.risk === 'medium' || d.risk === 'high').length;
                },

                calculateOverallPlanned() {
                    if (!this.progressData || this.progressData.length === 0) return (this.currentProject?.avgPlanned || 0).toFixed(1);
                    const total = this.progressData.reduce((acc, curr) => acc + (curr.plannedProgress || 0), 0);
                    return (total / this.progressData.length).toFixed(1);
                },

                calculateOverallActual() {
                    if (!this.progressData || this.progressData.length === 0) return (this.currentProject?.avgActual || 0).toFixed(1);
                    const total = this.progressData.reduce((acc, curr) => acc + (curr.actualProgress || 0), 0);
                    return (total / this.progressData.length).toFixed(1);
                },

                calculateOverallVariance() {
                    if (!this.progressData || this.progressData.length === 0) return (this.currentProject?.variance || 0).toFixed(1);
                    const plannedTotal = this.progressData.reduce((acc, curr) => acc + (curr.plannedProgress || 0), 0) / this.progressData.length;
                    const actualTotal = parseFloat(this.calculateOverallActual());
                    return (actualTotal - plannedTotal).toFixed(1);
                },

                getProjectStatus() {
                    const v = parseFloat(this.calculateOverallVariance());
                    if (v > 1) return 'Ahead of Schedule';
                    if (v < -2) return 'Behind Schedule';
                    return 'On Track';
                },

                getSelectedProgressTarget() {
                    const act = this.progressData.find(p => p.id === parseInt(this.selectedProgressActivityId));
                    return act ? act.plannedProgress : 50;
                },

                openProgressEntryModal() {
                    // Populate progressEntries with cloned items from active progressData
                    this.progressEntries = this.progressData.map(p => ({
                        id: p.id,
                        name: p.name,
                        plannedProgress: p.plannedProgress,
                        actualProgress: p.actualProgress,
                        originalActual: p.actualProgress
                    }));
                    this.progressReportNotes = '';
                    this.progressModalOpen = true;
                    this.$nextTick(() => {
                        lucide.createIcons();
                    });
                },

                async confirmAllProgressUpdates() {
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    const updatedNames = [];
                    let changesCount = 0;

                    for (const entry of this.progressEntries) {
                        const target = this.progressData.find(p => p.id === entry.id);
                        if (target) {
                            const oldVal = target.actualProgress;
                            const newVal = Math.max(0, Math.min(100, parseInt(entry.actualProgress) || 0));
                            
                            target.actualProgress = newVal;
                            target.variance = target.actualProgress - target.plannedProgress;
                            target.status = target.variance > 0 ? 'Ahead of Schedule' : (target.variance < 0 ? 'Behind Schedule' : 'On Track');
                            
                            if (oldVal !== newVal) {
                                changesCount++;
                                updatedNames.push(target.name);
                            }

                            // Sync with currentProject and allProjects cache
                            if (this.currentProject && this.currentProject.progress) {
                                const curP = this.currentProject.progress.find(p => p.id === target.id);
                                if (curP) {
                                    curP.actualProgress = newVal;
                                    curP.variance = target.variance;
                                    curP.status = target.status;
                                }
                            }
                            if (this.allProjects) {
                                const allP = this.allProjects.find(p => p.id === this.currentProject?.id);
                                if (allP && allP.progress) {
                                    const actP = allP.progress.find(p => p.id === target.id);
                                    if (actP) {
                                        actP.actualProgress = newVal;
                                        actP.variance = target.variance;
                                        actP.status = target.status;
                                    }
                                }
                            }

                            // Async backend persist
                            if (target.id) {
                                try {
                                    fetch('/progress', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': token || '',
                                            'Accept': 'application/json'
                                        },
                                        body: JSON.stringify({
                                            schedule_id: target.id,
                                            actual_progress: newVal,
                                            notes: this.progressReportNotes || `Daily progress updated to ${newVal}%`
                                        })
                                    }).catch(err => console.log('Async progress sync note:', err));
                                } catch (e) {
                                    console.error(e);
                                }
                            }
                        }
                    }

                    this.progressModalOpen = false;

                    // Audit Log Entry
                    this.auditLogs.unshift({
                        id: Date.now(),
                        timestamp: 'Sep 27, 2026 — Just now',
                        relativeTime: 'Just now',
                        companyCode: this.currentCompany?.code || 'MEGA',
                        companyName: this.currentCompany?.name || 'Megawide',
                        userName: this.currentUser?.name || 'Site Engineer',
                        userRole: this.currentRole?.title || 'Project Engineer',
                        category: 'Progress Update',
                        actionTitle: 'Daily Accomplishment Progress Submitted',
                        targetActivity: `${changesCount} Construction Activities`,
                        weatherSnapshot: null,
                        outcomeType: 'Progress Saved',
                        outcomeDetails: this.progressReportNotes ? `Progress saved with note: "${this.progressReportNotes}"` : `Updated accomplishments across active project milestone activities.`
                    });

                    this.renderSCurveChart();

                    this.addToast(
                        'Daily Progress Submitted',
                        `Successfully recorded accomplishments for all active construction activities. S-Curve refreshed.`,
                        'success',
                        'trending-up'
                    );
                },

                filteredAuditLogs() {
                    return this.auditLogs.filter(log => {
                        const matchesCompany = this.auditCompanyFilter === 'ALL' || log.companyCode === this.auditCompanyFilter;
                        const matchesCategory = this.auditEventFilter === 'ALL' || log.category === this.auditEventFilter;
                        const matchesDecision = this.auditDecisionFilter === 'ALL' || log.outcomeType === this.auditDecisionFilter;
                        return matchesCompany && matchesCategory && matchesDecision;
                    });
                },

                openLogModal(log) {
                    this.selectedLog = log;
                    this.logModalOpen = true;
                    this.$nextTick(() => {
                        lucide.createIcons();
                    });
                },

                exportAuditLogs() {
                    const companyFilter = this.auditCompanyFilter || 'ALL';
                    window.location.href = `/audit-logs/export-csv?company_id=${encodeURIComponent(companyFilter)}`;
                    
                    this.addToast(
                        'Research Dataset Exported',
                        `Exporting thesis audit dataset CSV for ${companyFilter === 'ALL' ? 'all participating companies' : companyFilter}.`,
                        'success',
                        'download'
                    );
                },

                toggleTheme() {
                    this.isDarkMode = !this.isDarkMode;
                    localStorage.setItem('theme', this.isDarkMode ? 'dark' : 'light');
                    this.$nextTick(() => {
                        lucide.createIcons();
                        this.renderSCurveChart();
                    });
                    this.addToast('Appearance Updated', `Switched to ${this.isDarkMode ? 'Dark' : 'Soft Dribbble Light'} theme mode.`, 'info', this.isDarkMode ? 'moon' : 'sun');
                },

                getGanttBarStyle(param1, param2) {
                    if (!this.ganttDates || this.ganttDates.length === 0) {
                        return 'display: none;';
                    }

                    const totalCols = this.ganttDates.length;

                    // If called with activity object: getGanttBarStyle(act, isBaseline)
                    if (typeof param1 === 'object' && param1 !== null) {
                        const act = param1;
                        const isBaseline = Boolean(param2);
                        const startStr = isBaseline ? (act.baselineStart || act.currentStart) : act.currentStart;
                        const endStr = isBaseline ? (act.baselineEnd || act.currentEnd) : act.currentEnd;

                        const getDayNum = (dStr) => {
                            if (!dStr) return null;
                            if (typeof dStr === 'string' && dStr.includes('-')) {
                                const parts = dStr.split('-');
                                return parseInt(parts[2], 10);
                            }
                            const parts = String(dStr).trim().split(' ');
                            if (parts.length >= 2) {
                                return parseInt(parts[1], 10);
                            }
                            return parseInt(dStr, 10);
                        };

                        const firstVisibleDay = getDayNum(this.ganttDates[0]?.date);
                        const lastVisibleDay = getDayNum(this.ganttDates[totalCols - 1]?.date);
                        const actStartDay = getDayNum(startStr);
                        const actEndDay = getDayNum(endStr) || (actStartDay !== null ? (actStartDay + (Number(act.durationDays) || 1) - 1) : null);

                        if (actStartDay === null || isNaN(actStartDay) || firstVisibleDay === null || lastVisibleDay === null) {
                            return 'display: none;';
                        }

                        // If activity finishes before the first visible day, or starts after the last visible day
                        if (actEndDay < firstVisibleDay || actStartDay > lastVisibleDay) {
                            return 'display: none;';
                        }

                        // Find visible start and end col indices
                        let startCol = 0;
                        let foundStart = false;
                        for (let i = 0; i < totalCols; i++) {
                            const dNum = getDayNum(this.ganttDates[i]?.date);
                            if (dNum !== null && dNum >= actStartDay) {
                                startCol = i;
                                foundStart = true;
                                break;
                            }
                        }
                        if (!foundStart || actStartDay < firstVisibleDay) {
                            startCol = 0;
                        }

                        let endCol = totalCols - 1;
                        let foundEnd = false;
                        for (let i = totalCols - 1; i >= 0; i--) {
                            const dNum = getDayNum(this.ganttDates[i]?.date);
                            if (dNum !== null && dNum <= actEndDay) {
                                endCol = i;
                                foundEnd = true;
                                break;
                            }
                        }
                        if (!foundEnd || actEndDay > lastVisibleDay) {
                            endCol = totalCols - 1;
                        }

                        const span = Math.max(1, endCol - startCol + 1);
                        const leftPercent = (startCol / totalCols) * 100;
                        const widthPercent = (span / totalCols) * 100;

                        return `margin-left: ${leftPercent}%; width: ${Math.min(widthPercent, 100 - leftPercent)}%; max-width: calc(100% - ${leftPercent}%);`;
                    }

                    // Fallback for (startCol, span) numeric arguments
                    const start = Math.max(0, Math.min(Number(param1) || 0, totalCols));
                    const rawSpan = Math.max(1, Number(param2) || 1);
                    if (start >= totalCols) {
                        return 'display: none;';
                    }
                    const availableSpan = Math.max(0, totalCols - start);
                    const clampedSpan = Math.min(rawSpan, availableSpan);
                    const leftPercent = (start / totalCols) * 100;
                    const widthPercent = (clampedSpan / totalCols) * 100;
                    return `margin-left: ${leftPercent}%; width: ${Math.min(widthPercent, 100 - leftPercent)}%; max-width: calc(100% - ${leftPercent}%);`;
                },

                switchTab(tab) {
                    if (window.innerWidth < 1024) this.sidebarOpen = false;
                    this.isLoading = true;
                    this.activeTab = tab;
                    
                    setTimeout(() => {
                        this.isLoading = false;
                        this.$nextTick(() => {
                            lucide.createIcons();
                            if (this.activeTab === 'dashboard') {
                                setTimeout(() => this.renderSCurveChart(), 60);
                            }
                        });
                    }, 250);
                },

                getFilteredProjects() {
                    if (this.projectCompanyFilter === 'ALL') {
                        if (this.currentCompany?.code === 'PPSDS') {
                            return this.allProjects;
                        } else {
                            return this.allProjects.filter(p => (p.company_id === this.currentCompany?.id) || (p.companyId === this.currentCompany?.id));
                        }
                    }
                    return this.allProjects.filter(p => (p.company_id === parseInt(this.projectCompanyFilter)) || (p.companyId === parseInt(this.projectCompanyFilter)));
                },

                switchProject(project, targetTab = null) {
                    // Find authoritative project from allProjects
                    const freshProject = this.allProjects.find(p => p.id === project.id) || project;
                    this.currentProject = freshProject;
                    
                    // Rehydrate activities specific to this project
                    if (freshProject.activities && freshProject.activities.length > 0) {
                        this.activities = JSON.parse(JSON.stringify(freshProject.activities));
                        if (this.timelineSchedules && this.timelineSchedules['October']) {
                            this.timelineSchedules['October'].activities = JSON.parse(JSON.stringify(freshProject.activities));
                        }
                    } else if (freshProject.schedules && freshProject.schedules.length > 0) {
                        this.activities = JSON.parse(JSON.stringify(freshProject.schedules));
                    } else {
                        this.activities = [];
                    }

                    // Rehydrate progress data specific to this project
                    if (freshProject.progress && freshProject.progress.length > 0) {
                        this.progressData = JSON.parse(JSON.stringify(freshProject.progress));
                    } else if (this.activities.length > 0) {
                        this.progressData = this.activities.map(a => ({
                            id: a.id,
                            name: a.name,
                            plannedProgress: a.planned_progress || a.plannedProgress || 50,
                            actualProgress: a.actual_progress || a.actualProgress || a.progress || 50,
                            variance: (a.actual_progress || a.actualProgress || a.progress || 50) - (a.planned_progress || a.plannedProgress || 50),
                            status: 'On Track'
                        }));
                        freshProject.progress = JSON.parse(JSON.stringify(this.progressData));
                    } else {
                        this.progressData = [];
                    }

                    // Sync company if needed
                    if (project.company_id || project.companyId) {
                        const cId = project.company_id || project.companyId;
                        const comp = this.companies.find(c => c.id === cId);
                        if (comp && this.currentCompany?.code !== 'PPSDS' && this.currentCompany?.id !== comp.id) {
                            this.currentCompany = comp;
                        }
                    }

                    if (targetTab) {
                        this.activeTab = targetTab;
                    }

                    this.updateSCurveDataForProject();

                    this.isLoading = true;
                    setTimeout(() => {
                        this.isLoading = false;
                        this.$nextTick(() => {
                            lucide.createIcons();
                            if (this.activeTab === 'dashboard') {
                                setTimeout(() => this.renderSCurveChart(), 60);
                            }
                        });
                    }, 200);

                    this.addToast('Workspace Loaded', `Switched active site telemetry to ${project.name}`, 'info', 'building-2');
                },

                switchCompany(company) {
                    this.currentCompany = company;
                    if (company.code === 'PPSDS') {
                        this.projects = [...this.allProjects];
                        this.projectCompanyFilter = 'ALL';
                    } else {
                        this.projects = this.allProjects.filter(p => (p.company_id === company.id) || (p.companyId === company.id));
                        if (this.projects.length === 0) {
                            this.projects = [...this.allProjects];
                        }
                        this.projectCompanyFilter = company.id.toString();
                    }

                    // Automatically switch active project to the first project of this company
                    if (!this.projects.some(p => p.id === this.currentProject?.id)) {
                        if (this.projects.length > 0) {
                            this.switchProject(this.projects[0]);
                        }
                    } else {
                        const updated = this.projects.find(p => p.id === this.currentProject.id);
                        if (updated) this.switchProject(updated);
                    }

                    this.auditCompanyFilter = company.code;
                    this.addToast('Company Scope Switched', `Viewing telemetry & vertical projects for ${company.name}`, 'info', 'building');
                },

                // =========================================================================
                // CONTRACTOR COMPANY CRUD CONTROLLERS (THESIS PHASE 2)
                // =========================================================================
                filteredCompanies() {
                    return this.companies.filter(c => {
                        const matchesStatus = this.companyStatusFilter === 'ALL' || (c.status || 'Active') === this.companyStatusFilter;
                        const q = this.companySearch.toLowerCase().trim();
                        const matchesSearch = !q || 
                            (c.name && c.name.toLowerCase().includes(q)) || 
                            (c.code && c.code.toLowerCase().includes(q)) || 
                            (c.contact_person && c.contact_person.toLowerCase().includes(q));
                        return matchesStatus && matchesSearch;
                    });
                },

                openNewCompanyModal() {
                    this.editingCompanyId = null;
                    this.companyForm = {
                        name: '',
                        code: '',
                        contact_person: '',
                        contact_email: '',
                        contact_phone: '',
                        address: 'CLSU Campus, Science City of Muñoz, Nueva Ecija',
                        status: 'Active'
                    };
                    this.companyModalOpen = true;
                    this.$nextTick(() => lucide.createIcons());
                },

                editCompany(company) {
                    this.editingCompanyId = company.id;
                    this.companyForm = {
                        name: company.name,
                        code: company.code,
                        contact_person: company.contact_person || '',
                        contact_email: company.contact_email || '',
                        contact_phone: company.contact_phone || '',
                        address: company.address || '',
                        status: company.status || 'Active'
                    };
                    this.companyModalOpen = true;
                    this.$nextTick(() => lucide.createIcons());
                },

                async deleteCompany(companyId) {
                    const comp = this.companies.find(c => c.id === companyId);
                    if (!comp) return;

                    if (comp.code === 'PPSDS') {
                        this.addToast('Action Restricted', 'The primary university physical plant department (PPSDS) cannot be removed.', 'warning', 'shield-alert');
                        return;
                    }

                    if (this.companies.length <= 1) {
                        this.addToast('Action Restricted', 'At least one organization record must remain active in the system.', 'warning', 'alert-triangle');
                        return;
                    }

                    const compName = comp.name;
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                    try {
                        const response = await fetch(`/companies/${companyId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': token || '',
                                'Accept': 'application/json'
                            }
                        });
                        const data = await response.json();
                        if (data.success === false) {
                            this.addToast('Error', data.message || 'Could not delete contractor company.', 'error', 'alert-triangle');
                            return;
                        }
                    } catch (e) {
                        console.error('Async delete company note:', e);
                    }

                    this.companies = this.companies.filter(c => c.id !== companyId);
                    if (this.currentCompany && this.currentCompany.id === companyId) {
                        const ppsds = this.companies.find(c => c.code === 'PPSDS') || this.companies[0];
                        if (ppsds) this.switchCompany(ppsds);
                    }

                    this.auditLogs.unshift({
                        id: Date.now(),
                        timestamp: 'Sep 27, 2026 — Just now',
                        relativeTime: 'Just now',
                        companyCode: 'PPSDS',
                        companyName: 'CLSU PPSDS',
                        userName: this.currentUser?.name || 'Administrator',
                        userRole: this.currentRole?.title || 'PPSDS Administrator',
                        category: 'System Admin',
                        actionTitle: 'Contractor Organization Removed',
                        targetActivity: `Contractor: ${compName}`,
                        weatherSnapshot: null,
                        outcomeType: 'Company Deleted',
                        outcomeDetails: `Removed contractor ${compName} from university directory`
                    });

                    this.addToast('Contractor Removed', `Company "${compName}" removed from university directory.`, 'info', 'trash-2');
                },

                async saveCompany() {
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    const isEditing = !!this.editingCompanyId;
                    const endpoint = isEditing ? `/companies/${this.editingCompanyId}` : '/companies';
                    const method = isEditing ? 'PUT' : 'POST';

                    const payload = {
                        name: this.companyForm.name,
                        code: this.companyForm.code.toUpperCase(),
                        contact_person: this.companyForm.contact_person,
                        contact_email: this.companyForm.contact_email,
                        contact_phone: this.companyForm.contact_phone,
                        address: this.companyForm.address,
                        status: this.companyForm.status || 'Active'
                    };

                    try {
                        const response = await fetch(endpoint, {
                            method: method,
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token || '',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(payload)
                        });
                        const data = await response.json();
                        if (data.company) {
                            const formatted = {
                                id: data.company.id,
                                name: data.company.name,
                                code: data.company.code,
                                contact_person: data.company.contact_person,
                                contact_email: data.company.contact_email,
                                contact_phone: data.company.contact_phone,
                                address: data.company.address,
                                status: data.company.status || 'Active',
                                projects_count: data.company.projects_count ?? 0,
                                users_count: data.company.users_count ?? 0,
                                projectsCount: data.company.projects_count ?? 0,
                                usersCount: data.company.users_count ?? 0
                            };

                            if (isEditing) {
                                const idx = this.companies.findIndex(c => c.id === this.editingCompanyId);
                                if (idx !== -1) this.companies[idx] = { ...this.companies[idx], ...formatted };
                                if (this.currentCompany && this.currentCompany.id === this.editingCompanyId) {
                                    this.currentCompany = { ...this.currentCompany, ...formatted };
                                }
                            } else {
                                this.companies.push(formatted);
                            }
                        }
                    } catch (e) {
                        console.error('Async save company note:', e);
                        if (isEditing) {
                            const comp = this.companies.find(c => c.id === this.editingCompanyId);
                            if (comp) {
                                comp.name = this.companyForm.name;
                                comp.code = this.companyForm.code.toUpperCase();
                                comp.contact_person = this.companyForm.contact_person;
                                comp.contact_email = this.companyForm.contact_email;
                                comp.contact_phone = this.companyForm.contact_phone;
                                comp.address = this.companyForm.address;
                                comp.status = this.companyForm.status;
                            }
                        } else {
                            this.companies.push({
                                id: Date.now(),
                                name: this.companyForm.name,
                                code: this.companyForm.code.toUpperCase(),
                                contact_person: this.companyForm.contact_person,
                                contact_email: this.companyForm.contact_email,
                                contact_phone: this.companyForm.contact_phone,
                                address: this.companyForm.address,
                                status: this.companyForm.status,
                                projects_count: 0,
                                users_count: 0,
                                projectsCount: 0,
                                usersCount: 0
                            });
                        }
                    }

                    this.addToast(
                        isEditing ? 'Contractor Updated' : 'Contractor Registered',
                        isEditing ? `Updated profile for "${this.companyForm.name}".` : `Registered "${this.companyForm.name}" as active contractor.`,
                        'success',
                        'building'
                    );

                    this.companyModalOpen = false;
                    this.$nextTick(() => lucide.createIcons());
                },

                // =========================================================================
                // PROJECT & ACTIVITY CRUD CONTROLLERS (THESIS REQUIREMENTS)
                // =========================================================================
                openNewProjectModal() {
                    this.editingProjectId = null;
                    this.projectForm = {
                        name: '',
                        code: `CLSU-BLDG-2026-0${this.allProjects.length + 1}`,
                        location: 'CLSU Campus, Science City of Muñoz, Nueva Ecija',
                        companyId: this.currentCompany.id,
                        storeys: 5,
                        durationWeeks: 12
                    };
                    this.projectModalOpen = true;
                    this.$nextTick(() => lucide.createIcons());
                },

                openProjectManagementModal() {
                    this.projectDirectoryOpen = true;
                    this.$nextTick(() => lucide.createIcons());
                },

                editProject(project) {
                    this.editingProjectId = project.id;
                    this.projectForm = {
                        name: project.name,
                        code: project.code,
                        location: project.location,
                        companyId: project.company_id || project.companyId || this.currentCompany.id,
                        storeys: project.storeys || 5,
                        durationWeeks: project.duration_weeks || 12
                    };
                    this.projectDirectoryOpen = false;
                    this.projectModalOpen = true;
                    this.$nextTick(() => lucide.createIcons());
                },

                async deleteProject(projectId) {
                    if (this.allProjects.length <= 1) {
                        this.addToast('Cannot Delete', 'At least one project workspace must remain active in the system.', 'warning', 'alert-triangle');
                        return;
                    }
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    try {
                        await fetch(`/projects/${projectId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': token || '',
                                'Accept': 'application/json'
                            }
                        });
                    } catch (e) {
                        console.error('Async delete project note:', e);
                    }

                    this.allProjects = this.allProjects.filter(p => p.id !== projectId);
                    this.projects = this.projects.filter(p => p.id !== projectId);
                    if (this.currentProject.id === projectId) {
                        this.switchProject(this.allProjects[0]);
                    }
                    this.addToast('Project Removed', 'Construction project deleted from directory.', 'info', 'trash-2');
                },

                async saveProject() {
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    const isEditing = !!this.editingProjectId;
                    const endpoint = isEditing ? `/projects/${this.editingProjectId}` : '/projects';
                    const method = isEditing ? 'PUT' : 'POST';

                    const targetCompId = parseInt(this.projectForm.companyId) || this.currentCompany?.id || 1;
                    const comp = this.companies.find(c => c.id === targetCompId);

                    const payload = {
                        company_id: targetCompId,
                        name: this.projectForm.name,
                        code: this.projectForm.code,
                        location: this.projectForm.location,
                        total_storeys: parseInt(this.projectForm.storeys) || 5,
                        baseline_duration_weeks: parseInt(this.projectForm.durationWeeks) || 12
                    };

                    try {
                        const response = await fetch(endpoint, {
                            method: method,
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token || '',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(payload)
                        });
                        const data = await response.json();
                        if (data.project) {
                            const formatted = {
                                id: data.project.id,
                                company_id: data.project.company_id,
                                companyId: data.project.company_id,
                                company_name: comp?.name || 'CLSU Project',
                                company_code: comp?.code || 'PPSDS',
                                name: data.project.name,
                                code: data.project.code,
                                location: data.project.location,
                                storeys: data.project.storeys || 5,
                                duration_weeks: data.project.duration_weeks || 12,
                                status: data.project.status || 'In Progress',
                                activities: isEditing ? (this.activities || []) : [],
                                progress: isEditing ? (this.progressData || []) : [],
                                avgPlanned: 0,
                                avgActual: 0,
                                variance: 0,
                                activeAlerts: 0
                            };

                            if (isEditing) {
                                const idxAll = this.allProjects.findIndex(p => p.id === this.editingProjectId);
                                if (idxAll !== -1) this.allProjects[idxAll] = { ...this.allProjects[idxAll], ...formatted };
                                const idx = this.projects.findIndex(p => p.id === this.editingProjectId);
                                if (idx !== -1) this.projects[idx] = { ...this.projects[idx], ...formatted };
                            } else {
                                this.allProjects.push(formatted);
                                this.projects.push(formatted);
                                this.switchProject(formatted);
                            }
                        }
                    } catch (e) {
                        console.error('Async save project note:', e);
                        if (isEditing) {
                            const proj = this.allProjects.find(p => p.id === this.editingProjectId);
                            if (proj) {
                                proj.name = this.projectForm.name;
                                proj.code = this.projectForm.code;
                                proj.location = this.projectForm.location;
                                proj.company_id = targetCompId;
                                proj.company_name = comp?.name || 'CLSU Project';
                            }
                        } else {
                            const newProj = {
                                id: Date.now(),
                                company_id: targetCompId,
                                companyId: targetCompId,
                                company_name: comp?.name || 'CLSU Project',
                                company_code: comp?.code || 'PPSDS',
                                name: this.projectForm.name,
                                code: this.projectForm.code,
                                location: this.projectForm.location,
                                storeys: parseInt(this.projectForm.storeys) || 5,
                                duration_weeks: parseInt(this.projectForm.durationWeeks) || 12,
                                status: 'In Progress',
                                activities: this.activities || [],
                                progress: this.progressData || [],
                                avgActual: 50,
                                activeAlerts: 0
                            };
                            this.allProjects.push(newProj);
                            this.projects.push(newProj);
                            this.switchProject(newProj);
                        }
                    }

                    this.addToast(
                        isEditing ? 'Project Updated' : 'Project Workspace Created',
                        isEditing ? `Updated "${this.projectForm.name}" details.` : `Created and switched to "${this.projectForm.name}". Ready for schedule generation.`,
                        'success',
                        'building-2'
                    );

                    this.projectModalOpen = false;
                    this.$nextTick(() => lucide.createIcons());
                },

                // =========================================================================
                // USER MANAGEMENT CONTROLLERS & HELPERS (MULTI-COMPANY & ROLES)
                // =========================================================================
                filteredUsers() {
                    return this.users.filter(u => {
                        const matchesRole = this.userRoleFilter === 'ALL' || u.roleId === this.userRoleFilter;
                        const matchesCompany = this.userCompanyFilter === 'ALL' || u.companyId === parseInt(this.userCompanyFilter);
                        const q = this.userSearch.toLowerCase().trim();
                        const matchesSearch = !q || u.name.toLowerCase().includes(q) || u.email.toLowerCase().includes(q);
                        return matchesRole && matchesCompany && matchesSearch;
                    });
                },

                getUserRoleTitle(roleId) {
                    const r = this.roles.find(role => role.id === roleId);
                    return r ? r.title : 'User';
                },

                getUserCompanyName(companyId) {
                    const c = this.companies.find(comp => comp.id === companyId);
                    return c ? c.name : 'Unknown Company';
                },

                getUserCompanyCode(companyId) {
                    const c = this.companies.find(comp => comp.id === companyId);
                    return c ? c.code : 'ORG';
                },

                getRoleBadgeClass(roleId) {
                    switch (roleId) {
                        case 'admin':
                            return 'bg-purple-50 text-purple-700 dark:bg-purple-500/10 dark:text-purple-400 border-purple-200 dark:border-purple-500/20';
                        case 'project_engineer':
                            return 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400 border-indigo-200 dark:border-indigo-500/20';
                        case 'site_supervisor':
                            return 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border-emerald-200 dark:border-emerald-500/20';
                        case 'contractor':
                            return 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400 border-amber-200 dark:border-amber-500/20';
                        default:
                            return 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300';
                    }
                },

                getDefaultRolePermissions(roleId) {
                    const isMasterAdmin = roleId === 'admin';
                    const isPE = roleId === 'project_engineer';
                    const isSiteSup = roleId === 'site_supervisor';
                    const isContractor = roleId === 'contractor';

                    return {
                        tab_dashboard: true,
                        tab_scheduling: true,
                        tab_projects: true,
                        tab_companies: isMasterAdmin || isPE,
                        tab_progress: true,
                        tab_activity_library: isMasterAdmin || isPE,
                        tab_weather_config: isMasterAdmin,
                        tab_audit: isMasterAdmin || isPE,
                        tab_users: isMasterAdmin,

                        action_reschedule: isMasterAdmin || isPE,
                        action_continue_same_day: isMasterAdmin || isPE,
                        action_edit_progress: isMasterAdmin || isPE || isSiteSup,
                        action_manage_activities: isMasterAdmin,
                        action_manage_projects: isMasterAdmin,
                        action_manage_companies: isMasterAdmin,
                        action_manage_users: isMasterAdmin,
                        action_export_audit: isMasterAdmin || isPE
                    };
                },

                onUserRoleChange() {
                    this.userForm.permissions = this.getDefaultRolePermissions(this.userForm.roleId);
                },

                openNewUserModal() {
                    this.editingUserId = null;
                    this.userForm = {
                        name: '',
                        email: '',
                        roleId: 'site_supervisor',
                        companyId: this.currentCompany ? this.currentCompany.id : 1,
                        status: 'Active',
                        permissions: this.getDefaultRolePermissions('site_supervisor')
                    };
                    this.userModalOpen = true;
                    this.$nextTick(() => lucide.createIcons());
                },

                editUser(user) {
                    this.editingUserId = user.id;
                    const roleId = user.roleId || user.role || 'site_supervisor';
                    let permissions = this.getDefaultRolePermissions(roleId);
                    if (user.custom_permissions && typeof user.custom_permissions === 'object') {
                        permissions = { ...permissions, ...user.custom_permissions };
                    }
                    this.userForm = {
                        name: user.name,
                        email: user.email,
                        roleId: roleId,
                        companyId: user.companyId || (user.company ? user.company.id : 1),
                        status: user.status || 'Active',
                        permissions: permissions
                    };
                    this.userModalOpen = true;
                    this.$nextTick(() => lucide.createIcons());
                },

                async deleteUser(userId) {
                    if (this.users.length <= 1) {
                        this.addToast('Action Restricted', 'At least one system user account must remain active.', 'warning', 'alert-triangle');
                        return;
                    }
                    if (this.currentUser?.id === userId) {
                        this.addToast('Action Restricted', 'You cannot delete the currently authenticated active session user.', 'warning', 'alert-triangle');
                        return;
                    }
                    const user = this.users.find(u => u.id === userId);
                    const userName = user ? user.name : 'User';
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                    try {
                        await fetch(`/users/${userId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': token || '',
                                'Accept': 'application/json'
                            }
                        });
                    } catch (e) {
                        console.error('Async delete user note:', e);
                    }

                    this.users = this.users.filter(u => u.id !== userId);

                    this.auditLogs.unshift({
                        id: Date.now(),
                        timestamp: 'Sep 27, 2026 — Just now',
                        relativeTime: 'Just now',
                        companyCode: this.currentCompany?.code || 'MEGA',
                        companyName: this.currentCompany?.name || 'Megawide',
                        userName: this.currentUser?.name || 'Admin',
                        userRole: this.currentRole?.title || 'System Admin',
                        category: 'System Admin',
                        actionTitle: 'User Account Revoked',
                        targetActivity: `User Account: ${userName}`,
                        weatherSnapshot: null,
                        outcomeType: 'User Deleted',
                        outcomeDetails: `Removed access privileges for ${userName}`
                    });

                    this.addToast('User Removed', `User "${userName}" has been deleted.`, 'info', 'user-x');
                },

                async saveUser() {
                    const initials = this.userForm.name.split(' ').map(n => n[0]).filter(Boolean).slice(0, 2).join('').toUpperCase() || 'U';
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    const isEditing = !!this.editingUserId;
                    const endpoint = isEditing ? `/users/${this.editingUserId}` : '/users';
                    const method = isEditing ? 'PUT' : 'POST';

                    const payload = {
                        company_id: parseInt(this.userForm.companyId) || 1,
                        name: this.userForm.name,
                        email: this.userForm.email,
                        role: this.userForm.roleId,
                        status: this.userForm.status || 'Active',
                        custom_permissions: this.userForm.permissions
                    };

                    try {
                        const response = await fetch(endpoint, {
                            method: method,
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token || '',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(payload)
                        });
                        const data = await response.json();
                        if (data.user) {
                            const formatted = {
                                id: data.user.id,
                                name: data.user.name,
                                email: data.user.email,
                                roleId: data.user.role,
                                companyId: data.user.company_id,
                                initials: initials,
                                status: data.user.status || 'Active',
                                custom_permissions: data.user.custom_permissions || this.userForm.permissions,
                                lastActive: 'Active Now',
                                assignedProjectsCount: 1
                            };

                            if (isEditing) {
                                const idx = this.users.findIndex(u => u.id === this.editingUserId);
                                if (idx !== -1) this.users[idx] = formatted;
                                if (this.currentUser && this.currentUser.id === this.editingUserId) {
                                    this.currentUser.custom_permissions = formatted.custom_permissions;
                                }
                            } else {
                                this.users.push(formatted);
                            }
                        }
                    } catch (e) {
                        console.error('Async save user note:', e);
                        if (isEditing) {
                            const user = this.users.find(u => u.id === this.editingUserId);
                            if (user) {
                                user.name = this.userForm.name;
                                user.email = this.userForm.email;
                                user.roleId = this.userForm.roleId;
                                user.companyId = parseInt(this.userForm.companyId);
                                user.status = this.userForm.status;
                                user.custom_permissions = { ...this.userForm.permissions };
                                user.initials = initials;
                                if (this.currentUser && this.currentUser.id === this.editingUserId) {
                                    this.currentUser.custom_permissions = user.custom_permissions;
                                }
                            }
                        } else {
                            this.users.push({
                                id: Date.now(),
                                name: this.userForm.name,
                                email: this.userForm.email,
                                roleId: this.userForm.roleId,
                                companyId: parseInt(this.userForm.companyId),
                                status: this.userForm.status,
                                custom_permissions: { ...this.userForm.permissions },
                                initials: initials,
                                lastActive: 'Never (Pending Login)',
                                assignedProjectsCount: 1
                            });
                        }
                    }

                    this.addToast(
                        isEditing ? 'User Account Updated' : 'User Account Created',
                        isEditing ? `Updated profile & access permissions for "${this.userForm.name}".` : `Created ${this.getUserRoleTitle(this.userForm.roleId)} profile for "${this.userForm.name}".`,
                        'success',
                        isEditing ? 'user-check' : 'user-plus'
                    );

                    this.userModalOpen = false;
                    this.$nextTick(() => lucide.createIcons());
                },

                // =========================================================================
                // MASTER CONSTRUCTION ACTIVITY LIBRARY CONTROLLERS (THESIS DATABASE)
                // =========================================================================
                filteredMasterActivities() {
                    return this.masterActivities.filter(a => {
                        const matchesCategory = this.activityCategoryFilter === 'ALL' || a.category === this.activityCategoryFilter;
                        const q = this.activityLibrarySearch.toLowerCase().trim();
                        const matchesSearch = !q || a.name.toLowerCase().includes(q) || a.code.toLowerCase().includes(q) || a.description.toLowerCase().includes(q);
                        return matchesCategory && matchesSearch;
                    });
                },

                openNewMasterActivityModal() {
                    this.editingMasterActivityId = null;
                    this.masterActivityForm = {
                        code: `ACT-${Date.now().toString().slice(-4)}`,
                        name: '',
                        category: 'Structural',
                        defaultDuration: 3,
                        maxRainProb: 40,
                        maxRainVol: 2.5,
                        maxWind: 40,
                        safetyTrigger: 'Rainfall > 3mm/hr (Safety Warning)',
                        predecessorHint: 'Preceding Milestone Activity',
                        description: ''
                    };
                    this.masterActivityModalOpen = true;
                    this.$nextTick(() => lucide.createIcons());
                },

                editMasterActivity(item) {
                    this.editingMasterActivityId = item.id;
                    this.masterActivityForm = {
                        code: item.code,
                        name: item.name,
                        category: item.category,
                        defaultDuration: item.defaultDuration,
                        maxRainProb: item.maxRainProb,
                        maxRainVol: item.maxRainVol,
                        maxWind: item.maxWind,
                        safetyTrigger: item.safetyTrigger,
                        predecessorHint: item.predecessorHint,
                        description: item.description
                    };
                    this.masterActivityModalOpen = true;
                    this.$nextTick(() => lucide.createIcons());
                },

                deleteMasterActivity(id) {
                    if (this.masterActivities.length <= 1) {
                        this.addToast('Action Restricted', 'At least one predefined construction activity must remain in the master catalog.', 'warning', 'alert-triangle');
                        return;
                    }
                    const act = this.masterActivities.find(a => a.id === id);
                    const actName = act ? act.name : 'Activity';
                    this.masterActivities = this.masterActivities.filter(a => a.id !== id);

                    this.auditLogs.unshift({
                        id: Date.now(),
                        timestamp: 'Sep 27, 2026 — Just now',
                        relativeTime: 'Just now',
                        companyCode: this.currentCompany.code,
                        companyName: this.currentCompany.name,
                        userName: this.currentUser.name,
                        userRole: this.currentRole.title,
                        category: 'System Admin',
                        actionTitle: 'Master Activity Removed',
                        targetActivity: `Master Activity: ${actName}`,
                        weatherSnapshot: null,
                        outcomeType: 'Activity Deleted',
                        outcomeDetails: `Removed activity preset "${actName}" from master database catalog`
                    });

                    this.addToast('Activity Preset Removed', `Removed "${actName}" from master library.`, 'info', 'trash-2');
                },

                async deleteMasterActivity(id) {
                    const act = this.masterActivities.find(a => a.id === id);
                    const actName = act ? act.name : 'Activity';
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                    try {
                        await fetch(`/master-activities/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': token || '',
                                'Accept': 'application/json'
                            }
                        });
                    } catch (e) {
                        console.error('Async delete note:', e);
                    }

                    this.masterActivities = this.masterActivities.filter(a => a.id !== id);

                    // Also remove matching scheduled activities from Gantt if present
                    if (act) {
                        this.activities = this.activities.filter(a => a.name !== act.name);
                        Object.keys(this.timelineSchedules).forEach(month => {
                            if (this.timelineSchedules[month].activities) {
                                this.timelineSchedules[month].activities = this.timelineSchedules[month].activities.filter(a => a.name !== act.name);
                            }
                            if (this.timelineSchedules[month].monthActivities) {
                                this.timelineSchedules[month].monthActivities = this.timelineSchedules[month].monthActivities.filter(a => a.name !== act.name);
                            }
                        });
                        this.progressData = this.progressData.filter(p => p.name !== act.name);
                    }

                    this.auditLogs.unshift({
                        id: Date.now(),
                        timestamp: `${this.selectedGanttMonth} 2026 — Just now`,
                        relativeTime: 'Just now',
                        companyCode: this.currentCompany?.code || 'PPSDS',
                        companyName: this.currentCompany?.name || 'CLSU - PPSDS',
                        userName: this.currentUser?.name || 'Site Engineer',
                        userRole: this.currentRole?.title || 'Project Engineer',
                        category: 'System Admin',
                        actionTitle: 'Master Activity Removed',
                        targetActivity: `Master Activity: ${actName}`,
                        weatherSnapshot: null,
                        outcomeType: 'Activity Deleted',
                        outcomeDetails: `Removed activity preset "${actName}" from master database catalog`
                    });

                    this.addToast('Activity Preset Removed', `Removed "${actName}" from master library.`, 'info', 'trash-2');
                },

                async saveMasterActivity() {
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    const isEditing = !!this.editingMasterActivityId;
                    const endpoint = isEditing ? `/master-activities/${this.editingMasterActivityId}` : '/master-activities';
                    const method = isEditing ? 'PUT' : 'POST';

                    const payload = {
                        code: this.masterActivityForm.code,
                        name: this.masterActivityForm.name,
                        category: this.masterActivityForm.category,
                        default_duration_days: parseInt(this.masterActivityForm.defaultDuration) || 3,
                        max_rain_probability: parseInt(this.masterActivityForm.maxRainProb) || 40,
                        max_rain_volume_mm: parseFloat(this.masterActivityForm.maxRainVol) || 2.5,
                        max_wind_speed_kmh: parseInt(this.masterActivityForm.maxWind) || 40,
                        safety_trigger: this.masterActivityForm.safetyTrigger || 'Rainfall > 3mm/hr',
                        predecessor_hint: this.masterActivityForm.predecessorHint || 'Preceding Milestone Activity',
                        description: this.masterActivityForm.description || ''
                    };

                    try {
                        const response = await fetch(endpoint, {
                            method: method,
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token || '',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(payload)
                        });
                        const data = await response.json();
                        
                        if (data.activity) {
                            const formatted = {
                                id: data.activity.id,
                                code: data.activity.code,
                                name: data.activity.name,
                                category: data.activity.category,
                                defaultDuration: parseInt(data.activity.default_duration_days),
                                maxRainProb: parseInt(data.activity.max_rain_probability),
                                maxRainVol: parseFloat(data.activity.max_rain_volume_mm),
                                maxWind: parseInt(data.activity.max_wind_speed_kmh),
                                safetyTrigger: data.activity.safety_trigger,
                                predecessorHint: data.activity.predecessor_hint,
                                description: data.activity.description
                            };

                            if (isEditing) {
                                const idx = this.masterActivities.findIndex(a => a.id === this.editingMasterActivityId);
                                if (idx !== -1) this.masterActivities[idx] = formatted;
                            } else {
                                this.masterActivities.push(formatted);
                            }
                        }
                    } catch (e) {
                        console.error('Async save note:', e);
                        // Fallback local update
                        if (isEditing) {
                            const act = this.masterActivities.find(a => a.id === this.editingMasterActivityId);
                            if (act) {
                                act.name = this.masterActivityForm.name;
                                act.code = this.masterActivityForm.code;
                                act.category = this.masterActivityForm.category;
                                act.defaultDuration = parseInt(this.masterActivityForm.defaultDuration);
                                act.maxRainProb = parseInt(this.masterActivityForm.maxRainProb);
                                act.maxRainVol = parseFloat(this.masterActivityForm.maxRainVol);
                                act.maxWind = parseInt(this.masterActivityForm.maxWind);
                                act.safetyTrigger = this.masterActivityForm.safetyTrigger;
                                act.predecessorHint = this.masterActivityForm.predecessorHint;
                                act.description = this.masterActivityForm.description;
                            }
                        } else {
                            this.masterActivities.push({
                                id: Date.now(),
                                code: this.masterActivityForm.code,
                                name: this.masterActivityForm.name,
                                category: this.masterActivityForm.category,
                                defaultDuration: parseInt(this.masterActivityForm.defaultDuration),
                                maxRainProb: parseInt(this.masterActivityForm.maxRainProb),
                                maxRainVol: parseFloat(this.masterActivityForm.maxRainVol),
                                maxWind: parseInt(this.masterActivityForm.maxWind),
                                safetyTrigger: this.masterActivityForm.safetyTrigger,
                                predecessorHint: this.masterActivityForm.predecessorHint,
                                description: this.masterActivityForm.description
                            });
                        }
                    }

                    this.auditLogs.unshift({
                        id: Date.now(),
                        timestamp: 'Sep 27, 2026 — Just now',
                        relativeTime: 'Just now',
                        companyCode: this.currentCompany?.code || 'MEGA',
                        companyName: this.currentCompany?.name || 'Megawide',
                        userName: this.currentUser?.name || 'Site Engineer',
                        userRole: this.currentRole?.title || 'Project Engineer',
                        category: 'System Admin',
                        actionTitle: isEditing ? 'Master Activity Updated' : 'New Master Activity Registered',
                        targetActivity: `Catalog: ${this.masterActivityForm.name}`,
                        weatherSnapshot: null,
                        outcomeType: isEditing ? 'Activity Updated' : 'Activity Created',
                        outcomeDetails: `${isEditing ? 'Updated' : 'Added'} ${this.masterActivityForm.name} (${this.masterActivityForm.category}) with Rain < ${this.masterActivityForm.maxRainProb}%, Wind < ${this.masterActivityForm.maxWind}km/h`
                    });

                    this.addToast(
                        isEditing ? 'Activity Template Updated' : 'Activity Template Registered',
                        `${isEditing ? 'Updated' : 'Added'} "${this.masterActivityForm.name}" in predefined database.`,
                        'success',
                        isEditing ? 'check-circle' : 'plus-circle'
                    );

                    this.masterActivityModalOpen = false;
                    this.$nextTick(() => lucide.createIcons());
                },

                scheduleLibraryActivityDirectly(item) {
                    this.editingActivityId = null;
                    this.activityForm = {
                        name: item.name,
                        predecessor: item.predecessorHint || (this.activities.length > 0 ? this.activities[this.activities.length - 1].name : ''),
                        durationDays: item.defaultDuration || 3,
                        startCol: 0,
                        weatherRuleText: `Rain < ${item.maxRainProb}%, Wind < ${item.maxWind}km/h`
                    };
                    this.switchTab('scheduling');
                    setTimeout(() => {
                        this.activityModalOpen = true;
                        this.$nextTick(() => lucide.createIcons());
                    }, 300);
                },

                openNewActivityModal() {
                    this.editingActivityId = null;
                    this.activityForm = {
                        name: '',
                        predecessor: this.activities.length > 0 ? this.activities[this.activities.length - 1].name : '',
                        durationDays: 2,
                        startCol: 0,
                        weatherRuleText: 'Rain < 40%, No Heavy Rain'
                    };
                    this.activityModalOpen = true;
                    this.$nextTick(() => lucide.createIcons());
                },

                async saveActivity() {
                    const startIdx = parseInt(this.activityForm.startCol) || 0;
                    const dur = parseInt(this.activityForm.durationDays) || 1;
                    const startDate = this.ganttDates[startIdx] ? this.ganttDates[startIdx].date : 'Oct 01';
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                    // Find predecessor ID if any
                    const predObj = this.activities.find(a => a.name === this.activityForm.predecessor);
                    const currentYear = new Date().getFullYear();

                    // Parse month and day from selected date (e.g. "Oct 08" -> "2026-10-08")
                    const monthMap = { 'Jan': 0, 'Feb': 1, 'Mar': 2, 'Apr': 3, 'May': 4, 'Jun': 5, 'Jul': 6, 'Aug': 7, 'Sep': 8, 'Oct': 9, 'Nov': 10, 'Dec': 11 };
                    const monthShortNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                    
                    let startDateObj = new Date(currentYear, 9, 1);
                    if (startDate) {
                        const parts = startDate.split(' ');
                        if (parts.length === 2 && monthMap[parts[0]] !== undefined) {
                            startDateObj = new Date(currentYear, monthMap[parts[0]], parseInt(parts[1]));
                        }
                    }

                    const endDateObj = new Date(startDateObj.getTime());
                    endDateObj.setDate(endDateObj.getDate() + (dur - 1));

                    const endDate = `${monthShortNames[endDateObj.getMonth()]} ${String(endDateObj.getDate()).padStart(2, '0')}`;
                    const isoStartDate = `${startDateObj.getFullYear()}-${String(startDateObj.getMonth() + 1).padStart(2, '0')}-${String(startDateObj.getDate()).padStart(2, '0')}`;

                    const payload = {
                        project_id: this.currentProject?.id || 1,
                        name: this.activityForm.name,
                        predecessor_id: predObj ? predObj.id : null,
                        duration_days: dur,
                        start_date: isoStartDate,
                        weather_rule_text: this.activityForm.weatherRuleText
                    };

                    let newActId = Date.now();

                    try {
                        const response = await fetch('/schedules', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token || '',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(payload)
                        });
                        const data = await response.json();
                        if (data.schedule && data.schedule.id) {
                            newActId = data.schedule.id;
                        }
                    } catch (e) {
                        console.error('Async schedule save note:', e);
                    }

                    const newAct = {
                        id: newActId,
                        name: this.activityForm.name,
                        predecessor: this.activityForm.predecessor || null,
                        weatherRuleText: this.activityForm.weatherRuleText,
                        feasibility: 'Feasible',
                        hasAlert: false,
                        durationDays: dur,
                        baselineStart: startDate,
                        baselineEnd: endDate,
                        baselineStartCol: startIdx,
                        baselineSpan: dur,
                        currentStart: startDate,
                        currentEnd: endDate,
                        currentStartCol: startIdx,
                        currentSpan: dur
                    };

                    this.activities.push(newAct);

                    // Also sync into active month timelineSchedule so switching month/mode retains it
                    if (this.timelineSchedules[this.selectedGanttMonth]) {
                        if (!this.timelineSchedules[this.selectedGanttMonth].activities) {
                            this.timelineSchedules[this.selectedGanttMonth].activities = [];
                        }
                        if (!this.timelineSchedules[this.selectedGanttMonth].monthActivities) {
                            this.timelineSchedules[this.selectedGanttMonth].monthActivities = [];
                        }
                        this.timelineSchedules[this.selectedGanttMonth].activities.push(newAct);
                        this.timelineSchedules[this.selectedGanttMonth].monthActivities.push(newAct);
                    }

                    this.progressData.push({
                        id: newAct.id,
                        name: newAct.name,
                        plannedProgress: 20,
                        actualProgress: 20,
                        variance: 0,
                        status: 'On Track'
                    });

                    this.auditLogs.unshift({
                        id: Date.now(),
                        timestamp: `${startDate}, ${currentYear} — Just now`,
                        relativeTime: 'Just now',
                        companyCode: this.currentCompany?.code || 'PPSDS',
                        companyName: this.currentCompany?.name || 'CLSU - PPSDS',
                        userName: this.currentUser?.name || 'Site Engineer',
                        userRole: this.currentRole?.title || 'Project Engineer',
                        category: 'Schedule Change',
                        actionTitle: 'Activity Created & Linked',
                        targetActivity: newAct.name,
                        weatherSnapshot: null,
                        outcomeType: 'Schedule Approved',
                        outcomeDetails: `New Task added: ${newAct.name} (${startDate} - ${endDate})`
                    });

                    this.activityModalOpen = false;
                    this.addToast('Activity Added to Gantt', `"${newAct.name}" saved to database and weather thresholds generated.`, 'success', 'calendar-plus');
                },

                async deleteScheduleActivity(id) {
                    if (!confirm('Are you sure you want to remove this scheduled activity from the Gantt timeline?')) {
                        return;
                    }
                    const act = this.activities.find(a => a.id === id) || (this.selectedActivity && this.selectedActivity.id === id ? this.selectedActivity : null);
                    const actName = act ? act.name : 'Activity';
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                    // Remove from active activities array
                    this.activities = this.activities.filter(a => a.id !== id);

                    // Remove from timelineSchedules
                    Object.keys(this.timelineSchedules).forEach(month => {
                        if (this.timelineSchedules[month].activities) {
                            this.timelineSchedules[month].activities = this.timelineSchedules[month].activities.filter(a => a.id !== id);
                        }
                        if (this.timelineSchedules[month].monthActivities) {
                            this.timelineSchedules[month].monthActivities = this.timelineSchedules[month].monthActivities.filter(a => a.id !== id);
                        }
                    });

                    // Remove from progress monitoring
                    this.progressData = this.progressData.filter(p => p.id !== id);

                    this.drawerOpen = false;

                    // Send async delete to backend
                    try {
                        await fetch(`/schedules/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': token || '',
                                'Accept': 'application/json'
                            }
                        });
                    } catch (e) {
                        console.error('Async delete schedule note:', e);
                    }

                    this.auditLogs.unshift({
                        id: Date.now(),
                        timestamp: `${this.selectedGanttMonth} 2026 — Just now`,
                        relativeTime: 'Just now',
                        companyCode: this.currentCompany?.code || 'PPSDS',
                        companyName: this.currentCompany?.name || 'CLSU - PPSDS',
                        userName: this.currentUser?.name || 'Site Engineer',
                        userRole: this.currentRole?.title || 'Project Engineer',
                        category: 'Schedule Change',
                        actionTitle: 'Activity Removed from Gantt',
                        targetActivity: actName,
                        weatherSnapshot: null,
                        outcomeType: 'Activity Deleted',
                        outcomeDetails: `Removed ${actName} from project Gantt schedule.`
                    });

                    this.addToast('Activity Removed', `"${actName}" removed from Gantt timeline.`, 'info', 'trash-2');
                    this.$nextTick(() => lucide.createIcons());
                },

                switchRole(role) {
                    this.currentRole = role;
                    if (!this.canAccessTab(this.activeTab)) {
                        this.switchTab('dashboard');
                    }
                    this.addToast('Role Switched (Simulation)', `Permissions updated to: ${role.title} perspective.`, 'success', 'shield-check');
                    this.$nextTick(() => lucide.createIcons());
                },

                canAccessTab(tabName) {
                    const permKey = 'tab_' + tabName.replace(/-/g, '_');
                    if (this.currentUser && (!this.currentRole || this.currentRole.id === (this.currentUser.roleId || this.currentUser.role))) {
                        if (this.currentUser.custom_permissions && typeof this.currentUser.custom_permissions[permKey] === 'boolean') {
                            return this.currentUser.custom_permissions[permKey];
                        }
                    }
                    const role = this.currentRole?.id || (this.currentUser ? (this.currentUser.roleId || this.currentUser.role) : 'admin');
                    if (role === 'admin') return true;

                    switch(tabName) {
                        case 'dashboard':
                        case 'scheduling':
                        case 'projects':
                            return true;
                        case 'companies':
                            return ['admin', 'project_engineer'].includes(role);
                        case 'progress':
                            return true; // All can view; Site Supervisor/PE/Admin can edit
                        case 'activity-library':
                            return ['admin', 'project_engineer'].includes(role);
                        case 'audit':
                            return ['admin', 'project_engineer'].includes(role);
                        case 'weather-config':
                        case 'users':
                            return role === 'admin';
                        default:
                            return true;
                    }
                },

                hasPermission(action) {
                    let permKey = 'action_' + action.replace(/-/g, '_');
                    if (action === 'add_gantt_activity' || action === 'reschedule_activity') permKey = 'action_reschedule';
                    if (action === 'manage_master_activities') permKey = 'action_manage_activities';

                    if (this.currentUser && (!this.currentRole || this.currentRole.id === (this.currentUser.roleId || this.currentUser.role))) {
                        if (this.currentUser.custom_permissions && typeof this.currentUser.custom_permissions[permKey] === 'boolean') {
                            return this.currentUser.custom_permissions[permKey];
                        }
                    }
                    const role = this.currentRole?.id || (this.currentUser ? (this.currentUser.roleId || this.currentUser.role) : 'admin');
                    if (role === 'admin') return true;

                    switch (action) {
                        case 'reschedule_activity':
                        case 'continue_same_day':
                        case 'add_gantt_activity':
                            return ['admin', 'project_engineer'].includes(role);
                        case 'edit_progress':
                            return ['admin', 'project_engineer', 'site_supervisor'].includes(role);
                        case 'manage_master_activities':
                        case 'manage_projects':
                        case 'manage_companies':
                            return ['admin', 'project_engineer'].includes(role);
                        case 'manage_users':
                            return role === 'admin';
                        case 'export_audit':
                            return ['admin', 'project_engineer'].includes(role);
                        default:
                            return false;
                    }
                },

                setGanttViewMode(mode) {
                    this.ganttViewMode = mode;
                    this.selectedGanttWeek = 'all';
                    this.highlightedDate = null;
                    this.applyGanttSchedule();
                    this.addToast('Gantt View Changed', `Switched to ${mode === 'month' ? 'Full Month (30-Day)' : '7-Day Focused'} timeline`, 'info', 'calendar');
                },

                applyGanttSchedule() {
                    const monthSchedule = this.timelineSchedules[this.selectedGanttMonth];
                    if (!monthSchedule) return;

                    const allMonthDates = monthSchedule.monthDates || monthSchedule.dates;
                    this.activities = [...(monthSchedule.monthActivities || monthSchedule.activities || this.activities)];

                    if (this.ganttViewMode === 'month') {
                        if (this.selectedGanttWeek === 'all') {
                            this.ganttDates = [...allMonthDates];
                        } else if (this.selectedGanttWeek === 'Week 1') {
                            this.ganttDates = allMonthDates.slice(0, 7);
                        } else if (this.selectedGanttWeek === 'Week 2') {
                            this.ganttDates = allMonthDates.slice(7, 14);
                        } else if (this.selectedGanttWeek === 'Week 3') {
                            this.ganttDates = allMonthDates.slice(14, 21);
                        } else if (this.selectedGanttWeek === 'Week 4') {
                            this.ganttDates = allMonthDates.slice(21, 28);
                        } else if (this.selectedGanttWeek === 'Week 5') {
                            this.ganttDates = allMonthDates.slice(28);
                        }
                    } else {
                        // 7-Day Focused View (7-Day Windows)
                        if (this.selectedGanttWeek === 'all' || this.selectedGanttWeek === 'Week 1') {
                            this.ganttDates = allMonthDates.slice(0, 7);
                        } else if (this.selectedGanttWeek === 'Week 2') {
                            this.ganttDates = allMonthDates.slice(7, 14);
                        } else if (this.selectedGanttWeek === 'Week 3') {
                            this.ganttDates = allMonthDates.slice(14, 21);
                        } else if (this.selectedGanttWeek === 'Week 4') {
                            this.ganttDates = allMonthDates.slice(21, 28);
                        } else if (this.selectedGanttWeek === 'Week 5') {
                            this.ganttDates = allMonthDates.slice(28);
                        }
                    }

                    this.$nextTick(() => lucide.createIcons());
                },

                getGanttNavLabel() {
                    if (this.ganttViewMode === 'month') {
                        if (this.selectedGanttWeek === 'all') {
                            return `${this.selectedGanttMonth} 2026`;
                        }
                        return `${this.selectedGanttMonth} 2026 — ${this.selectedGanttWeek}`;
                    } else {
                        const wk = (this.selectedGanttWeek === 'all') ? 'Week 1' : this.selectedGanttWeek;
                        return `${this.selectedGanttMonth} 2026 — ${wk}`;
                    }
                },

                switchGanttMonth(monthName) {
                    this.selectedGanttMonth = monthName;
                    this.selectedGanttWeek = (this.ganttViewMode === 'month') ? 'all' : 'Week 1';
                    this.highlightedDate = null;
                    this.applyGanttSchedule();
                },

                navigateGanttNext() {
                    const monthOrder = ['September', 'October', 'November'];
                    const currentMonthIdx = monthOrder.indexOf(this.selectedGanttMonth);
                    const weekOrder = ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5'];

                    if (this.ganttViewMode === 'month' && this.selectedGanttWeek === 'all') {
                        if (currentMonthIdx < monthOrder.length - 1) {
                            this.switchGanttMonth(monthOrder[currentMonthIdx + 1]);
                        }
                    } else {
                        let currentWeek = (this.selectedGanttWeek === 'all') ? 'Week 1' : this.selectedGanttWeek;
                        const currentWeekIdx = weekOrder.indexOf(currentWeek);

                        if (currentWeekIdx >= 0 && currentWeekIdx < weekOrder.length - 1) {
                            this.filterGanttWeek(weekOrder[currentWeekIdx + 1]);
                        } else {
                            if (currentMonthIdx < monthOrder.length - 1) {
                                this.selectedGanttMonth = monthOrder[currentMonthIdx + 1];
                                this.selectedGanttWeek = 'Week 1';
                                this.applyGanttSchedule();
                            }
                        }
                    }
                },

                navigateGanttPrev() {
                    const monthOrder = ['September', 'October', 'November'];
                    const currentMonthIdx = monthOrder.indexOf(this.selectedGanttMonth);
                    const weekOrder = ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5'];

                    if (this.ganttViewMode === 'month' && this.selectedGanttWeek === 'all') {
                        if (currentMonthIdx > 0) {
                            this.switchGanttMonth(monthOrder[currentMonthIdx - 1]);
                        }
                    } else {
                        let currentWeek = (this.selectedGanttWeek === 'all') ? 'Week 1' : this.selectedGanttWeek;
                        const currentWeekIdx = weekOrder.indexOf(currentWeek);

                        if (currentWeekIdx > 0) {
                            this.filterGanttWeek(weekOrder[currentWeekIdx - 1]);
                        } else {
                            if (currentMonthIdx > 0) {
                                this.selectedGanttMonth = monthOrder[currentMonthIdx - 1];
                                this.selectedGanttWeek = 'Week 5';
                                this.applyGanttSchedule();
                            }
                        }
                    }
                },

                prevGanttMonth() {
                    this.navigateGanttPrev();
                },

                nextGanttMonth() {
                    this.navigateGanttNext();
                },

                filterGanttWeek(weekLabel) {
                    this.selectedGanttWeek = weekLabel;
                    this.applyGanttSchedule();
                },

                isToday(date) {
                    return date === this.todayDate;
                },

                highlightGanttDate(date) {
                    this.highlightedDate = date;
                    this.$nextTick(() => {
                        lucide.createIcons();
                    });
                },

                resetHighlightedDate() {
                    this.highlightedDate = null;
                },

                openWeatherDrawer(activity) {
                    this.selectedActivity = activity;
                    this.drawerOpen = true;
                    this.$nextTick(() => {
                        lucide.createIcons();
                    });
                },

                openContinueModal() {
                    this.continueModalOpen = true;
                    this.$nextTick(() => {
                        lucide.createIcons();
                    });
                },

                async confirmContinueSameDay() {
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    const act = this.activities.find(a => a.id === this.selectedActivity.id);
                    const mitigationText = this.continuationMitigationNotes || `Activity on-site mitigation applied with resume target at ${this.resumeTime}`;
                    
                    try {
                        if (act && act.id) {
                            fetch(`/schedules/${act.id}/continue-same-day`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': token || '',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({
                                    resume_time: this.resumeTime,
                                    mitigation_notes: mitigationText
                                })
                            }).catch(err => console.log('Async sync note:', err));
                        }
                    } catch (e) {
                        console.error(e);
                    }

                    this.continueModalOpen = false;
                    this.drawerOpen = false;
                    
                    if (act) {
                        act.hasAlert = false;
                        act.feasibility = 'Feasible';
                        act.continuedSameDay = true;
                        act.resumeTime = this.resumeTime;
                        act.mitigationNotes = mitigationText;

                        this.auditLogs.unshift({
                            id: Date.now(),
                            timestamp: 'Oct 01, 2026 — Just now',
                            relativeTime: 'Just now',
                            companyCode: this.currentCompany?.code || 'CLSU',
                            companyName: this.currentCompany?.name || 'PPSDS Office',
                            userName: this.currentUser?.name || 'Project Engineer',
                            userRole: this.currentRole?.title || 'Project Engineer',
                            category: 'Weather Decision',
                            actionTitle: 'Continued Same Day',
                            targetActivity: act.name,
                            weatherSnapshot: {
                                rainProb: 75,
                                feasibility: 'Conditionally Feasible',
                                wind: '22 km/h'
                            },
                            outcomeType: 'Continue Same Day',
                            outcomeDetails: `Mitigation Applied. Work Resuming at ${this.resumeTime} (${mitigationText})`
                        });
                    }

                    this.addToast(
                        'Decision Logged: Continue Same Day',
                        `Activity confirmed for ${this.selectedActivity.currentStart} with resume time: ${this.resumeTime}. Appended to Activity Log.`,
                        'success',
                        'shield-check'
                    );
                    this.$nextTick(() => { lucide.createIcons(); });
                },

                openRescheduleModal() {
                    this.rescheduleModalOpen = true;
                    this.$nextTick(() => {
                        lucide.createIcons();
                    });
                },

                async confirmReschedule() {
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    const act = this.activities.find(a => a.id === this.selectedActivity.id);
                    
                    try {
                        if (act && act.id) {
                            fetch(`/schedules/${act.id}/reschedule`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': token || '',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({
                                    new_start_date: this.newScheduledDate,
                                    successor_action: this.successorAction,
                                    weather_snapshot: {
                                        rainProb: 75,
                                        feasibility: 'Conditionally Feasible',
                                        wind: '22 km/h'
                                    }
                                })
                            }).catch(err => console.log('Async sync note:', err));
                        }
                    } catch (e) {
                        console.error(e);
                    }

                    this.rescheduleModalOpen = false;
                    this.drawerOpen = false;
                    
                    if (act) {
                        const oldDate = act.currentStart;
                        act.currentStart = this.newScheduledDate;
                        act.currentEnd = 'Oct 03';
                        act.currentStartCol = 5;
                        act.currentSpan = 2;
                        act.hasAlert = false;
                        act.feasibility = 'Feasible';
                        act.continuedSameDay = false;
                        act.resumeTime = null;
                        act.mitigationNotes = null;

                        if (this.successorAction === 'adjust') {
                            const successor = this.activities.find(a => a.id === 3);
                            if (successor) {
                                successor.currentStart = 'Oct 04';
                                successor.currentEnd = 'Oct 06';
                                successor.currentStartCol = 7;
                            }
                        }

                        this.auditLogs.unshift({
                            id: Date.now(),
                            timestamp: 'Sep 27, 2026 — Just now',
                            relativeTime: 'Just now',
                            companyCode: this.currentCompany?.code || 'MEGA',
                            companyName: this.currentCompany?.name || 'Megawide',
                            userName: this.currentUser?.name || 'Site Engineer',
                            userRole: this.currentRole?.title || 'Project Engineer',
                            category: 'Weather Decision',
                            actionTitle: 'Rescheduled After Alert',
                            targetActivity: act.name,
                            weatherSnapshot: {
                                rainProb: 75,
                                feasibility: 'Conditionally Feasible',
                                wind: '22 km/h'
                            },
                            outcomeType: 'Reschedule',
                            outcomeDetails: `Shifted ${oldDate} → ${this.newScheduledDate} (${this.successorAction === 'adjust' ? 'Successors Adjusted' : 'Successors Retained'})`
                        });

                        this.highlightGanttDate(this.newScheduledDate);

                        this.addToast(
                            'Schedule Updated & Logged',
                            `Rescheduled "${act.name}" from ${oldDate} → ${this.newScheduledDate}. Appended to research audit trail.`,
                            'success',
                            'calendar-check'
                        );
                    }
                },

                addToast(title, message, type = 'info', icon = 'bell') {
                    const id = Date.now() + Math.random();
                    this.toasts.push({ id, title, message, type, icon, visible: true });
                    this.$nextTick(() => {
                        lucide.createIcons();
                    });

                    setTimeout(() => {
                        this.removeToast(id);
                    }, 4500);
                },

                removeToast(id) {
                    const index = this.toasts.findIndex(t => t.id === id);
                    if (index !== -1) {
                        this.toasts[index].visible = false;
                        setTimeout(() => {
                            this.toasts = this.toasts.filter(t => t.id !== id);
                        }, 300);
                    }
                },

                countUnreadNotifications() {
                    return this.notifications.filter(n => !n.read).length;
                },

                markAllNotificationsAsRead() {
                    this.notifications.forEach(n => n.read = true);
                    this.addToast('Notifications Cleared', 'All alerts marked as read.', 'info', 'check-check');
                },

                handleNotificationClick(notif) {
                    notif.read = true;
                    if (notif.category === 'weather_alert' && notif.activityId) {
                        const act = this.activities.find(a => a.id === notif.activityId);
                        if (act) {
                            this.switchTab('scheduling');
                            this.highlightGanttDate(notif.date || act.currentStart);
                            setTimeout(() => {
                                this.openWeatherDrawer(act);
                            }, 300);
                        }
                    } else if (notif.category === 'progress') {
                        this.switchTab('progress');
                    } else {
                        this.switchTab('dashboard');
                    }
                },

                triggerSimulatedWeatherAlert() {
                    const concrete = this.activities.find(a => a.id === 2);
                    if (concrete) {
                        concrete.hasAlert = true;
                        concrete.feasibility = 'Conditionally Feasible';
                        concrete.currentStart = 'Sep 30';
                        concrete.currentEnd = 'Oct 01';
                        concrete.currentStartCol = 3;
                    }
                    this.highlightGanttDate('Sep 30');
                    
                    // Add to Notification Center
                    this.notifications.unshift({
                        id: Date.now(),
                        title: 'Heavy Rain Warning (Sept 30)',
                        message: '75% precipitation threshold exceeded for Concrete Pouring. Decision needed.',
                        category: 'weather_alert',
                        activityId: 2,
                        date: 'Sep 30',
                        time: 'Just now',
                        read: false,
                        type: 'warning',
                        icon: 'cloud-rain'
                    });

                    this.switchTab('scheduling');
                    this.addToast(
                        'Weather Advisory Alert Triggered',
                        'Heavy Rain (75% probability) on Sept 30 affecting "Concrete Pouring". Added to Notifications.',
                        'warning',
                        'alert-triangle'
                    );
                },

                triggerCraneWindAlert() {
                    const crane = this.activities.find(a => a.id === 3);
                    if (crane) {
                        crane.hasAlert = true;
                        crane.feasibility = 'Infeasible';
                    }
                    this.highlightGanttDate('Oct 02');

                    // Add to Notification Center
                    this.notifications.unshift({
                        id: Date.now(),
                        title: 'OSHA High Wind Alert (Oct 02)',
                        message: 'Wind gusts 48 km/h exceed 30 km/h limit on Tower Crane Lifting. Suspension required.',
                        category: 'weather_alert',
                        activityId: 3,
                        date: 'Oct 02',
                        time: 'Just now',
                        read: false,
                        type: 'error',
                        icon: 'wind'
                    });

                    this.switchTab('scheduling');
                    this.addToast(
                        'OSHA Safety Breach: High Wind Gusts',
                        'Wind gust 48 km/h on Oct 02 exceeds Tower Crane safety limit (30 km/h). Immediate lifting suspension required.',
                        'error',
                        'wind'
                    );
                },

                triggerProgressVarianceDemo() {
                    const concrete = this.progressData.find(p => p.id === 2);
                    if (concrete) {
                        concrete.actualProgress = 75;
                        concrete.variance = 15;
                        concrete.status = 'Ahead of Schedule';
                    }
                    this.switchTab('progress');
                    this.renderSCurveChart();
                    this.addToast(
                        'Progress Acceleration Applied',
                        'Concrete Pouring updated to 75% (+15% Recovered variance shown in Emerald 3-Shade bar).',
                        'success',
                        'trending-up'
                    );
                },

                resetToInitialState() {
                    if (this.currentProject && this.currentProject.activities) {
                        this.activities = JSON.parse(JSON.stringify(this.currentProject.activities));
                    } else {
                        this.activities = [];
                    }
                    if (this.currentProject && this.currentProject.progress) {
                        this.progressData = JSON.parse(JSON.stringify(this.currentProject.progress));
                    } else {
                        this.progressData = [];
                    }
                    this.auditCompanyFilter = 'ALL';
                    this.auditEventFilter = 'ALL';
                    this.auditDecisionFilter = 'ALL';
                    this.highlightedDate = null;
                    this.updateSCurveDataForProject();
                    this.switchTab('dashboard');
                    this.addToast('Dataset Refreshed', 'Workspace state reloaded from live database.', 'info', 'rotate-ccw');
                },

                updateSCurveDataForProject() {
                    this.sCurveLabels = ['Wk 1', 'Wk 2', 'Wk 3', 'Wk 4', 'Wk 5', 'Wk 6', 'Wk 7', 'Wk 8', 'Wk 9', 'Wk 10', 'Wk 11', 'Wk 12'];
                    this.sCurvePlanned = [4, 10, 18, 28, 40, 52, 65, 78, 88, 94, 98, 100];

                    const hasProgress = this.progressData && this.progressData.length > 0;

                    if (!hasProgress) {
                        this.sCurveActual = [null, null, null, null, null, null, null, null, null, null, null, null];
                        this.sCurveForecast = [null, null, null, null, null, null, null, null, null, null, null, null];
                        return;
                    }

                    const actualAvg = parseFloat(this.calculateOverallActual()) || 0;
                    
                    const w1 = Math.max(1, Math.round(actualAvg * 0.08));
                    const w2 = Math.max(w1 + 2, Math.round(actualAvg * 0.18));
                    const w3 = Math.max(w2 + 3, Math.round(actualAvg * 0.35));
                    const w4 = Math.max(w3 + 4, Math.round(actualAvg * 0.55));
                    const w5 = Math.max(w4 + 5, Math.round(actualAvg * 0.78));
                    const w6 = actualAvg;

                    this.sCurveActual = [w1, w2, w3, w4, w5, w6, null, null, null, null, null, null];
                    
                    const remaining = 100 - actualAvg;
                    const f7 = Math.round(actualAvg + remaining * 0.22);
                    const f8 = Math.round(actualAvg + remaining * 0.45);
                    const f9 = Math.round(actualAvg + remaining * 0.68);
                    const f10 = Math.round(actualAvg + remaining * 0.84);
                    const f11 = Math.round(actualAvg + remaining * 0.94);
                    const f12 = 100;

                    this.sCurveForecast = [null, null, null, null, null, actualAvg, f7, f8, f9, f10, f11, f12];
                },

                renderSCurveChart() {
                    if (this.activeTab !== 'dashboard') return;

                    const canvas = document.getElementById('sCurveChart');
                    if (!canvas) return;

                    if (typeof Chart === 'undefined') {
                        setTimeout(() => this.renderSCurveChart(), 200);
                        return;
                    }

                    // Always ensure S-Curve data is freshly calculated for current project
                    this.updateSCurveDataForProject();

                    // Destroy Chart.js instance by ID or stored reference
                    const existingChart = Chart.getChart(canvas);
                    if (existingChart) {
                        existingChart.destroy();
                    }
                    if (this.chartInstance) {
                        try { this.chartInstance.destroy(); } catch(e) {}
                        this.chartInstance = null;
                    }

                    const ctx = canvas.getContext('2d');
                    if (!ctx) return;

                    const isDark = this.isDarkMode;
                    const gridColor = isDark ? 'rgba(51, 65, 85, 0.25)' : '#edf2f7';
                    const textColor = isDark ? '#94a3b8' : '#94a3b8';

                    const labels = (this.sCurveLabels && this.sCurveLabels.length) ? this.sCurveLabels : ['Wk 1', 'Wk 2', 'Wk 3', 'Wk 4', 'Wk 5', 'Wk 6', 'Wk 7', 'Wk 8', 'Wk 9', 'Wk 10', 'Wk 11', 'Wk 12'];
                    const plannedData = (this.sCurvePlanned && this.sCurvePlanned.length) ? this.sCurvePlanned : [4, 10, 18, 28, 40, 52, 65, 78, 88, 94, 98, 100];
                    const actualData = (this.sCurveActual && this.sCurveActual.length) ? this.sCurveActual : [null, null, null, null, null, null, null, null, null, null, null, null];
                    const forecastData = (this.sCurveForecast && this.sCurveForecast.length) ? this.sCurveForecast : [null, null, null, null, null, null, null, null, null, null, null, null];

                    this.chartInstance = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [
                                {
                                    label: 'Planned Cumulative (%)',
                                    data: plannedData,
                                    borderColor: isDark ? '#38bdf8' : '#6366f1',
                                    backgroundColor: isDark ? 'rgba(56, 189, 248, 0.08)' : 'rgba(99, 102, 241, 0.06)',
                                    borderWidth: 2.5,
                                    borderDash: [5, 5],
                                    tension: 0.35,
                                    pointBackgroundColor: isDark ? '#0284c7' : '#4f46e5',
                                    pointBorderColor: isDark ? '#38bdf8' : '#818cf8',
                                    pointRadius: 4,
                                    pointHoverRadius: 6,
                                    fill: true,
                                    spanGaps: true
                                },
                                {
                                    label: 'Actual Accomplished (%)',
                                    data: actualData,
                                    borderColor: '#10b981',
                                    backgroundColor: isDark ? 'rgba(16, 185, 129, 0.15)' : 'rgba(16, 185, 129, 0.08)',
                                    borderWidth: 3,
                                    tension: 0.35,
                                    pointBackgroundColor: '#059669',
                                    pointBorderColor: '#34d399',
                                    pointRadius: 5,
                                    pointHoverRadius: 7,
                                    fill: true,
                                    spanGaps: false
                                },
                                {
                                    label: 'Weather Adjusted Forecast (%)',
                                    data: forecastData,
                                    borderColor: '#f59e0b',
                                    backgroundColor: 'transparent',
                                    borderWidth: 2,
                                    borderDash: [3, 3],
                                    tension: 0.35,
                                    pointBackgroundColor: '#d97706',
                                    pointBorderColor: '#fbbf24',
                                    pointRadius: 3,
                                    pointHoverRadius: 5,
                                    fill: false,
                                    spanGaps: true
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            animation: {
                                duration: 300
                            },
                            interaction: {
                                mode: 'index',
                                intersect: false
                            },
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    backgroundColor: isDark ? '#0f172a' : '#1e293b',
                                    titleColor: '#f8fafc',
                                    bodyColor: '#cbd5e1',
                                    borderColor: isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(255, 255, 255, 0.1)',
                                    borderWidth: 1,
                                    padding: 10,
                                    boxPadding: 4,
                                    usePointStyle: true,
                                    callbacks: {
                                        label: function(context) {
                                            return `${context.dataset.label}: ${context.parsed.y !== null ? context.parsed.y + '%' : 'N/A'}`;
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    grid: {
                                        color: gridColor
                                    },
                                    border: {
                                        display: false
                                    },
                                    ticks: {
                                        color: textColor,
                                        font: {
                                            size: window.innerWidth < 640 ? 9 : 11,
                                            family: 'Plus Jakarta Sans'
                                        }
                                    }
                                },
                                y: {
                                    min: 0,
                                    max: 100,
                                    grid: {
                                        color: gridColor
                                    },
                                    border: {
                                        display: false
                                    },
                                    ticks: {
                                        color: textColor,
                                        callback: function(value) {
                                            return value + '%';
                                        },
                                        font: {
                                            size: window.innerWidth < 640 ? 9 : 11,
                                            family: 'Plus Jakarta Sans'
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            }
        }
    </script>
</body>
</html>
