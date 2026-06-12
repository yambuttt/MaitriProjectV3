<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? config('app.name', 'Maitri Store | Developer IDE Top Up Portal') }}</title>

        <!-- Google Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- CSS Styles for Code Server / IDE Layout -->
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
            
            /* VS Code Dark Grey Theme */
            .bg-ide-editor {
                background-color: #1e1e1e; /* VS Code Main Editor Grey */
            }
            .bg-ide-sidebar {
                background-color: #181818; /* VS Code Sidebar Darker Grey */
            }
            .bg-ide-panel {
                background-color: #252526; /* VS Code Panel Grey */
            }
            .border-ide {
                border-color: #2b2b2b;
            }
            
            /* Code editor line grid background */
            .editor-grid {
                background-size: 40px 40px;
                background-image: 
                    linear-gradient(to right, rgba(255, 255, 255, 0.015) 1px, transparent 1px),
                    linear-gradient(to bottom, rgba(255, 255, 255, 0.015) 1px, transparent 1px);
            }
            
            /* Editor Window Decorations */
            .window-dot {
                width: 10px;
                height: 10px;
                border-radius: 50%;
                display: inline-block;
            }
            .dot-close { background-color: #ff5f56; }
            .dot-min { background-color: #ffbd2e; }
            .dot-max { background-color: #27c93f; }

            /* Syntax highlighting colors */
            .syntax-keyword { color: #569cd6; } /* Blue */
            .syntax-string { color: #ce9178; }  /* Orange/Brown */
            .syntax-number { color: #b5cea8; }  /* Light Green */
            .syntax-comment { color: #6a9955; } /* Green comment */
            .syntax-function { color: #dcdcaa; }/* Yellow */
            .syntax-variable { color: #9cdcfe; }/* Light Blue */
            .syntax-class { color: #4ec9b0; }   /* Cyan-Green */
            .syntax-tag { color: #e06c75; }     /* Pinkish-Red */

            /* Tech Custom scrollbars for code blocks */
            ::-webkit-scrollbar {
                width: 7px;
                height: 7px;
            }
            ::-webkit-scrollbar-track {
                background: #181818;
            }
            ::-webkit-scrollbar-thumb {
                background: #3e3e3e;
                border-radius: 3px;
            }
            ::-webkit-scrollbar-thumb:hover {
                background: #4f4f4f;
            }
        </style>
    </head>
    <body class="bg-ide-editor text-[#d4d4d4] min-h-screen relative overflow-x-hidden antialiased selection:bg-cyan-500/20 selection:text-cyan-200">
        
        <!-- Editor background grids -->
        <div class="absolute inset-0 editor-grid pointer-events-none z-0"></div>

        <!-- Sticky Code Editor Tab-Bar / Navbar -->
        <livewire:navbar />

        <!-- Main Workspace container -->
        <main class="relative z-10 container mx-auto px-4 md:px-6 py-6 min-h-[calc(100vh-160px)]">
            {{ $slot }}
        </main>

        <!-- IDE Status Bar Footer -->
        <footer class="relative z-[5] border-t border-ide bg-[#007acc] text-white py-1.5 px-4 font-mono text-[10px] flex justify-between items-center select-none">
            <div class="flex items-center gap-4">
                <span class="bg-[#0066a1] px-2 py-0.5 font-bold flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-400 animate-ping"></span>
                    SSH: localhost
                </span>
                <span>main*</span>
                <span class="hidden sm:inline">// Synchronized with dispatch_server.db</span>
            </div>
            
            <div class="flex items-center gap-4">
                <span class="hidden md:inline">UTF-8</span>
                <span class="hidden md:inline">HTML/PHP/Blade</span>
                <span class="bg-[#0066a1] px-2 py-0.5 font-bold">// SYS_CONNECTED</span>
            </div>
        </footer>

        <!-- Spacer for mobile fixed checkout bar -->
        @if(request()->is('product/*'))
            <div class="h-20 lg:hidden"></div>
        @endif

    </body>
</html>
