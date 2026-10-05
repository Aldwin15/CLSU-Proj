<!DOCTYPE html>
<html lang="en" class="h-full font-sans antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | CLSU BuildWeather — PPSDS Decision-Support Platform</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'Inter', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        clsu: {
                            green: '#006837',
                            gold: '#f9a01b',
                            darkGreen: '#004724',
                        }
                    }
                }
            }
        }
    </script>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Lucide Icons CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }
        .glass-panel {
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .glow-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(100px);
            pointer-events: none;
        }
    </style>
</head>
<body class="min-h-full flex flex-col justify-between bg-[#070b14] text-slate-100 relative overflow-x-hidden selection:bg-indigo-500 selection:text-white">
    
    <!-- Background Ambient Glow Orbs -->
    <div class="glow-orb w-96 h-96 bg-emerald-600/15 top-0 left-10"></div>
    <div class="glow-orb w-[30rem] h-[30rem] bg-indigo-600/20 -top-20 right-10"></div>
    <div class="glow-orb w-96 h-96 bg-amber-500/10 bottom-0 left-1/3"></div>

    <!-- Top Navigation Brand Bar -->
    <header class="w-full px-6 py-4 flex items-center justify-between border-b border-slate-800/60 relative z-20">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-emerald-600 via-indigo-600 to-sky-500 p-0.5 shadow-lg shadow-emerald-500/20 flex items-center justify-center">
                <i data-lucide="building-2" class="w-5 h-5 text-white"></i>
            </div>
            <div>
                <span class="text-sm font-extrabold tracking-tight text-white flex items-center gap-2">
                    CLSU BuildWeather
                    <span class="px-1.5 py-0.5 text-[9px] font-extrabold rounded bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">PPSDS</span>
                </span>
                <p class="text-[10px] text-slate-400 font-medium">Vertical Construction Decision-Support System</p>
            </div>
        </div>

        <div class="hidden sm:flex items-center gap-3">
            <div class="flex items-center gap-2 px-3 py-1 rounded-full bg-slate-900/80 border border-slate-800 text-xs text-slate-300">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="text-[11px] font-semibold">Science City of Muñoz, Nueva Ecija</span>
            </div>
            <span class="text-xs text-slate-500">|</span>
            <span class="text-xs text-slate-400 font-medium">Undergraduate Thesis 2026</span>
        </div>
    </header>

    <!-- Main Content Grid -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-12 flex items-center relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 w-full items-center">
            
            <!-- LEFT COLUMN: System Value Proposition & Live Telemetry Preview (Desktop) -->
            <div class="lg:col-span-7 space-y-8 text-left hidden lg:block">
                
                <div class="space-y-4">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-gradient-to-r from-emerald-500/10 to-indigo-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-bold">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-400"></i>
                        <span>Central Luzon State University • Physical Plant & Site Development Services</span>
                    </div>

                    <h1 class="text-3xl xl:text-4xl font-extrabold text-white tracking-tight leading-tight">
                        Weather-Responsive Construction Scheduling & Progress Monitoring
                    </h1>

                    <p class="text-sm text-slate-300 font-medium leading-relaxed max-w-xl">
                        A specialized decision-support system designed for CLSU's multi-storey vertical building projects — integrating real-time Muñoz weather telemetry with 3-shade Gantt progress tracking and OSHA safety compliance.
                    </p>
                </div>

                <!-- 3 Feature Highlight Cards Grid -->
                <div class="grid grid-cols-3 gap-4">
                    
                    <!-- Card 1 -->
                    <div class="glass-panel p-4 rounded-2xl space-y-2 border border-slate-800 hover:border-emerald-500/30 transition group">
                        <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                            <i data-lucide="cloud-lightning" class="w-4 h-4"></i>
                        </div>
                        <h4 class="text-xs font-bold text-white">Live Open-Meteo API</h4>
                        <p class="text-[11px] text-slate-400 leading-normal">
                            Automated hourly rain & wind speed telemetry for Muñoz site coordinates.
                        </p>
                    </div>

                    <!-- Card 2 -->
                    <div class="glass-panel p-4 rounded-2xl space-y-2 border border-slate-800 hover:border-indigo-500/30 transition group">
                        <div class="w-8 h-8 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center">
                            <i data-lucide="git-merge" class="w-4 h-4"></i>
                        </div>
                        <h4 class="text-xs font-bold text-white">Predecessor Impact</h4>
                        <p class="text-[11px] text-slate-400 leading-normal">
                            Automated cascade recalculation when heavy weather forces schedule shifts.
                        </p>
                    </div>

                    <!-- Card 3 -->
                    <div class="glass-panel p-4 rounded-2xl space-y-2 border border-slate-800 hover:border-amber-500/30 transition group">
                        <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center">
                            <i data-lucide="trending-up" class="w-4 h-4"></i>
                        </div>
                        <h4 class="text-xs font-bold text-white">3-Shade S-Curve</h4>
                        <p class="text-[11px] text-slate-400 leading-normal">
                            Planned vs actual vs recovered accomplishment visualization.
                        </p>
                    </div>

                </div>

                <!-- Live Campus Project Telemetry Badge -->
                <div class="glass-panel p-4 rounded-2xl border border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-600/20 text-indigo-400 flex items-center justify-center border border-indigo-500/30 shrink-0">
                            <i data-lucide="map-pin" class="w-5 h-5 text-indigo-400"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-white">Active Monitoring: CLSU College of Engineering Complex</p>
                            <p class="text-[11px] text-slate-400">5-Storey Research Facility • Supervised by CLSU-PPSDS & Megawide</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-1 rounded-lg bg-emerald-500/10 text-emerald-400 text-xs font-bold border border-emerald-500/20 flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span>
                            Live Telemetry Active
                        </span>
                    </div>
                </div>

            </div>

            <!-- RIGHT COLUMN: Authentication Card -->
            <div class="lg:col-span-5 w-full max-w-md mx-auto">
                <div class="glass-panel p-6 sm:p-8 rounded-3xl shadow-2xl border border-slate-800/90 relative overflow-hidden">
                    
                    <div class="border-b border-slate-800 pb-5 mb-6">
                        <div class="flex items-center justify-between mb-1">
                            <h2 class="text-lg font-bold text-white tracking-tight">Portal Authentication</h2>
                            <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-indigo-500/15 text-indigo-300 border border-indigo-500/30">
                                Single Sign-On
                            </span>
                        </div>
                        <p class="text-xs text-slate-400">Sign in with your assigned PPSDS or Contractor credentials</p>
                    </div>

                    @if ($errors->any())
                        <div class="mb-5 p-3.5 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-xs flex items-start gap-2.5 animate-shake">
                            <i data-lucide="alert-circle" class="w-4 h-4 shrink-0 mt-0.5 text-rose-400"></i>
                            <div>
                                @foreach ($errors->all() as $error)
                                    <p class="font-semibold">{{ $error }}</p>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <form action="{{ route('login.post') }}" method="POST" class="space-y-4 text-left">
                        @csrf
                        
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Account Email</label>
                            <div class="relative">
                                <i data-lucide="mail" class="w-4 h-4 text-slate-500 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                                <input type="email" name="email" id="emailInput" value="{{ old('email', 'engineer@vertical.ph') }}" required autofocus
                                       placeholder="engineer@vertical.ph"
                                       class="w-full pl-10 pr-4 py-3 rounded-xl bg-slate-950/80 border border-slate-700/80 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 text-white text-xs font-semibold outline-none transition placeholder:text-slate-600">
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400">Password</label>
                                <span class="text-[11px] text-slate-500 font-medium">Default: password123</span>
                            </div>
                            <div class="relative">
                                <i data-lucide="lock" class="w-4 h-4 text-slate-500 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                                <input type="password" name="password" id="passwordInput" value="password123" required
                                       placeholder="••••••••"
                                       class="w-full pl-10 pr-10 py-3 rounded-xl bg-slate-950/80 border border-slate-700/80 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 text-white text-xs font-semibold outline-none transition placeholder:text-slate-600">
                                <button type="button" onclick="togglePasswordVisibility()" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300">
                                    <i data-lucide="eye" id="eyeIcon" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-1">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="remember" checked class="w-4 h-4 rounded bg-slate-950 border-slate-700 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-xs text-slate-400 font-medium">Keep session authenticated</span>
                            </label>
                            <span class="text-[11px] text-indigo-400 font-semibold cursor-help" title="Contact PPSDS Administrator for account provisioning">Need Access?</span>
                        </div>

                        <button type="submit" 
                                class="w-full py-3.5 px-4 rounded-xl bg-gradient-to-r from-emerald-600 via-indigo-600 to-indigo-500 hover:from-emerald-500 hover:to-indigo-500 text-white text-xs font-bold shadow-lg shadow-indigo-500/25 transition active:scale-[0.98] flex items-center justify-center gap-2 mt-3">
                            <span>Authenticate & Launch Decision Workspace</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </button>
                    </form>

                    <!-- Fast Thesis Role Persona Switcher -->
                    <div class="pt-5 mt-5 border-t border-slate-800">
                        <div class="flex items-center justify-between mb-2.5">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">One-Click Thesis Evaluator Login</p>
                            <span class="text-[9px] text-emerald-400 font-bold">Auto-Fill</span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-[11px]">
                            
                            <!-- Persona 1: PPSDS Project Engineer -->
                            <button type="button" onclick="fillCreds('engineer@vertical.ph', 'Engr. Juan Dela Cruz (PPSDS)')" 
                                    class="p-2.5 rounded-xl bg-slate-950/60 hover:bg-indigo-600/15 border border-slate-800 hover:border-indigo-500/40 text-left transition group">
                                <div class="flex items-center justify-between">
                                    <p class="font-bold text-indigo-400 group-hover:text-indigo-300">PPSDS Engineer</p>
                                    <i data-lucide="check" class="w-3 h-3 text-slate-600 group-hover:text-indigo-400"></i>
                                </div>
                                <p class="text-[9px] text-slate-400 truncate mt-0.5">Approval & Monitoring</p>
                            </button>

                            <!-- Persona 2: Contractor Site Supervisor -->
                            <button type="button" onclick="fillCreds('supervisor@vertical.ph', 'Carlos Mendoza (Megawide)')" 
                                    class="p-2.5 rounded-xl bg-slate-950/60 hover:bg-emerald-600/15 border border-slate-800 hover:border-emerald-500/40 text-left transition group">
                                <div class="flex items-center justify-between">
                                    <p class="font-bold text-emerald-400 group-hover:text-emerald-300">Site Supervisor</p>
                                    <i data-lucide="check" class="w-3 h-3 text-slate-600 group-hover:text-emerald-400"></i>
                                </div>
                                <p class="text-[9px] text-slate-400 truncate mt-0.5">Daily Accomplishment</p>
                            </button>

                            <!-- Persona 3: PPSDS Admin -->
                            <button type="button" onclick="fillCreds('admin@vertical.ph', 'Atty. Sofia Reyes (PPSDS Director)')" 
                                    class="p-2.5 rounded-xl bg-slate-950/60 hover:bg-purple-600/15 border border-slate-800 hover:border-purple-500/40 text-left transition group">
                                <div class="flex items-center justify-between">
                                    <p class="font-bold text-purple-400 group-hover:text-purple-300">PPSDS Director</p>
                                    <i data-lucide="check" class="w-3 h-3 text-slate-600 group-hover:text-purple-400"></i>
                                </div>
                                <p class="text-[9px] text-slate-400 truncate mt-0.5">Full System Admin</p>
                            </button>

                            <!-- Persona 4: Contractor PM -->
                            <button type="button" onclick="fillCreds('contractor@vertical.ph', 'Engr. Ramon Santos (EEI Corp.)')" 
                                    class="p-2.5 rounded-xl bg-slate-950/60 hover:bg-amber-600/15 border border-slate-800 hover:border-amber-500/40 text-left transition group">
                                <div class="flex items-center justify-between">
                                    <p class="font-bold text-amber-400 group-hover:text-amber-300">Contractor PM</p>
                                    <i data-lucide="check" class="w-3 h-3 text-slate-600 group-hover:text-amber-400"></i>
                                </div>
                                <p class="text-[9px] text-slate-400 truncate mt-0.5">View S-Curve Progress</p>
                            </button>

                        </div>
                    </div>

                </div>
            </div>

        </div>
    </main>

    <!-- Footer -->
    <footer class="w-full px-6 py-4 border-t border-slate-800/60 text-center relative z-20 text-[11px] text-slate-500">
        <p>
            &copy; 2026 Central Luzon State University • Physical Plant and Site Development Services (PPSDS) — Web-Based Weather-Responsive Construction Decision-Support Tool
        </p>
    </footer>

    <script>
        lucide.createIcons();

        function fillCreds(email, roleTitle) {
            const emailInput = document.getElementById('emailInput');
            const passwordInput = document.getElementById('passwordInput');
            
            emailInput.value = email;
            passwordInput.value = 'password123';
            
            // Visual pulse feedback
            emailInput.classList.add('ring-2', 'ring-indigo-500');
            setTimeout(() => {
                emailInput.classList.remove('ring-2', 'ring-indigo-500');
            }, 600);
        }

        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('passwordInput');
            const eyeIcon = document.getElementById('eyeIcon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.setAttribute('data-lucide', 'eye-off');
            } else {
                passwordInput.type = 'password';
                eyeIcon.setAttribute('data-lucide', 'eye');
            }
            lucide.createIcons();
        }
    </script>
</body>
</html>
