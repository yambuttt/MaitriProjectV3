<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? 'Maitri Store | Administrative Terminal' }}</title>

        <!-- Google Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- CSS Styles for Admin Console Workspace -->
        <style>
            body {
                font-family: 'Fira Code', 'Plus Jakarta Sans', monospace;
            }
            .font-space {
                font-family: 'Space Grotesk', sans-serif;
            }
            .font-mono {
                font-family: 'Fira Code', monospace;
            }
            
            /* VS Code Theme Colors */
            .bg-ide-editor {
                background-color: #1e1e1e;
            }
            .bg-ide-sidebar {
                background-color: #181818;
            }
            .border-ide {
                border-color: #2b2b2b;
            }
            
            /* Grid layout lines */
            .editor-grid {
                background-size: 40px 40px;
                background-image: 
                    linear-gradient(to right, rgba(255, 255, 255, 0.012) 1px, transparent 1px),
                    linear-gradient(to bottom, rgba(255, 255, 255, 0.012) 1px, transparent 1px);
            }
            
            /* Window Controls */
            .window-dot {
                width: 10px;
                height: 10px;
                border-radius: 50%;
                display: inline-block;
            }
            .dot-close { background-color: #ff5f56; }
            .dot-min { background-color: #ffbd2e; }
            .dot-max { background-color: #27c93f; }

            /* Syntax highlights */
            .syntax-keyword { color: #569cd6; }
            .syntax-string { color: #ce9178; }
            .syntax-number { color: #b5cea8; }
            .syntax-comment { color: #6a9955; }
            .syntax-function { color: #dcdcaa; }
            .syntax-variable { color: #9cdcfe; }

            /* Tech Custom Scrollbar */
            ::-webkit-scrollbar {
                width: 6px;
                height: 6px;
            }
            ::-webkit-scrollbar-track {
                background: #121212;
            }
            ::-webkit-scrollbar-thumb {
                background: #333333;
                border-radius: 3px;
            }
            ::-webkit-scrollbar-thumb:hover {
                background: #444444;
            }
        </style>
    </head>
    <body class="bg-[#121212] text-[#d4d4d4] min-h-screen relative overflow-x-hidden antialiased select-none font-mono">
        
        <!-- Editor background grids -->
        <div class="absolute inset-0 editor-grid pointer-events-none z-0"></div>

        <!-- Custom Dedicated Admin Header (VS Code Editor Vibe, but restricted workspace) -->
        <header class="relative z-50 w-full border-b border-ide bg-[#181818] h-12 px-4 sm:px-6 flex items-center justify-between text-xs text-[#858585]">
            <div class="flex items-center gap-3 truncate">
                <!-- MacOS style dots -->
                <div class="flex gap-1.5 shrink-0">
                    <span class="window-dot dot-close"></span>
                    <span class="window-dot dot-min"></span>
                    <span class="window-dot dot-max"></span>
                </div>
                <div class="border-l border-ide pl-3 ml-1.5 text-slate-400 font-bold shrink-0">
                    MAITRI_CONSOLE
                </div>
                <span class="text-slate-600 hidden sm:inline truncate">// root@dispatch_server:~/dashboard</span>
            </div>

            <div class="flex items-center gap-4 text-[10px] font-bold shrink-0">
                <span class="hidden md:inline text-slate-600">BRANCH: main*</span>
                <span class="text-[#4ec9b0]">MODE: ADMIN_WORKSPACE</span>
            </div>
        </header>

        <!-- Main Workspace dedicated container -->
        <main class="relative z-10 w-full px-4 sm:px-6 lg:px-8 py-6 min-h-[calc(100vh-80px)]">
            {{ $slot }}
        </main>

        <!-- Dedicated Admin Status Bar Footer -->
        <footer class="relative z-50 border-t border-ide bg-[#181818] text-[#858585] py-1.5 px-4 text-[10px] flex justify-between items-center select-none font-mono">
            <div class="flex items-center gap-4">
                <span class="bg-[#007acc] text-white px-2 py-0.5 font-bold flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-400 animate-ping"></span>
                    SSH: connected
                </span>
                <span class="hidden sm:inline">DB: SQLite</span>
                <span class="hidden sm:inline">Vite: v8.0.16</span>
            </div>
            
            <div class="flex items-center gap-4">
                <span>UTF-8</span>
                <span>PHP/Livewire v4</span>
                <span class="text-cyan-400 font-bold">// SECURE_TUNNEL_ESTABLISHED</span>
            </div>
        </footer>

    </body>
</html>
