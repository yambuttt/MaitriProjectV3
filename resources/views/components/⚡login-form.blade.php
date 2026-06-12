<?php

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    public string $email = '';
    public string $password = '';

    protected array $rules = [
        'email' => 'required|email',
        'password' => 'required',
    ];

    public function login()
    {
        $this->validate();

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password])) {
            session()->regenerate();

            $user = Auth::user();
            if ($user->role === 'admin') {
                return $this->redirectRoute('admin.dashboard', navigate: true);
            } else {
                return $this->redirectRoute('user.dashboard', navigate: true);
            }
        }

        $this->addError('email', 'The credentials provided do not match our system records.');
    }
};
?>

<div class="flex items-center justify-center min-h-[calc(100vh-220px)] py-8 relative z-10 select-none">
    
    <!-- Main IDE Window Wrapper -->
    <div 
        x-data="{ 
            showPassword: false
        }"
        class="w-full max-w-4xl rounded-lg border border-ide bg-[#181818] overflow-hidden shadow-2xl shadow-black/60 font-mono text-xs text-[#d4d4d4]"
    >
        <!-- IDE Window Header -->
        <div class="h-9 bg-[#1e1e1e] border-b border-ide px-4 flex items-center justify-between text-[11px] text-[#858585]">
            <div class="flex items-center gap-2">
                <!-- MacOS style window dots -->
                <div class="flex gap-1.5 mr-2">
                    <span class="w-3 h-3 rounded-full bg-[#ff5f56]/80 border border-[#e0443e]"></span>
                    <span class="w-3 h-3 rounded-full bg-[#ffbd2e]/80 border border-[#dea123]"></span>
                    <span class="w-3 h-3 rounded-full bg-[#27c93f]/80 border border-[#1aab2f]"></span>
                </div>
                
                <!-- File Editor Tab -->
                <div class="flex items-center gap-1.5 px-3 py-1.5 bg-[#181818] border-t-2 border-[#007acc] border-x border-ide rounded-t text-[#cccccc] font-bold">
                    <span class="text-[#cb8e35]">{ }</span>
                    <span>auth_portal.json</span>
                    <span class="text-[9px] text-slate-500 hover:text-white cursor-pointer ml-1">×</span>
                </div>
            </div>
            
            <div class="flex items-center gap-4">
                <span class="hidden sm:inline text-slate-600 font-bold">// SECURE_SHELL_TUNNEL</span>
                <span class="text-[9px] text-[#4ec9b0] font-bold">MODE: LOCAL_SERVER</span>
            </div>
        </div>

        <!-- Main Workspace Editor Area -->
        <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-ide bg-[#1e1e1e] min-h-[380px]">
            
            <!-- Left Column: Authentication Form -->
            <div class="p-6 sm:p-8 flex flex-col justify-between space-y-6">
                <div class="space-y-5">
                    <div class="flex justify-between items-center border-b border-ide/50 pb-3">
                        <span class="font-bold text-slate-400 text-[10px] uppercase tracking-wider">// INITIALIZE AUTHENTICATION AGENT</span>
                        <span class="text-[9px] text-yellow-500 font-bold font-mono">AWAITING_INPUT</span>
                    </div>

                    <!-- Email Field -->
                    <div class="space-y-1.5">
                        <label class="text-[10px] text-slate-500 uppercase block font-bold tracking-wide">// INPUT: IDENTITY_NODE (EMAIL)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2.5 text-slate-600 text-xs">@</span>
                            <input 
                                wire:model.live="email"
                                type="email" 
                                placeholder="e.g. admin@maitri.com" 
                                class="w-full h-9 pl-8 pr-3 rounded bg-ide-editor border border-ide text-xs placeholder-slate-700 text-[#cccccc] focus:outline-none focus:border-[#007acc] focus:ring-1 focus:ring-[#007acc]/20 transition-all font-mono"
                            >
                        </div>
                        @error('email') <span class="text-[10px] text-red-400 font-semibold font-mono">{{ $message }}</span> @enderror
                    </div>

                    <!-- Password Field -->
                    <div class="space-y-1.5">
                        <label class="text-[10px] text-slate-500 uppercase block font-bold tracking-wide">// INPUT: AUTH_SECRET (PASSWORD)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2.5 text-slate-600 text-xs">*</span>
                            <input 
                                wire:model.live="password"
                                :type="showPassword ? 'text' : 'password'" 
                                placeholder="••••••••••••" 
                                class="w-full h-9 pl-8 pr-10 rounded bg-ide-editor border border-ide text-xs placeholder-slate-700 text-[#cccccc] focus:outline-none focus:border-[#007acc] focus:ring-1 focus:ring-[#007acc]/20 transition-all font-mono"
                                wire:keydown.enter="login"
                            >
                            <!-- Toggle Password Eye -->
                            <button 
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-3 top-2.5 text-slate-500 hover:text-slate-300 active:scale-95 transition-all text-[9px] uppercase font-bold tracking-tighter"
                            >
                                <span x-text="showPassword ? 'HIDE' : 'SHOW'"></span>
                            </button>
                        </div>
                        @error('password') <span class="text-[10px] text-red-400 font-semibold font-mono">{{ $message }}</span> @enderror
                    </div>

                    <!-- Mock helper links -->
                    <div class="flex items-center justify-between text-[9px] pt-1">
                        <a href="#" class="text-slate-500 hover:text-slate-300 hover:underline">// RESET_CREDENTIALS</a>
                        <a href="#" class="text-slate-500 hover:text-slate-300 hover:underline">// CREATE_NEW_ACCOUNT</a>
                    </div>
                </div>

                <!-- Action Authentication Button -->
                <div class="space-y-3 pt-4">
                    <button 
                        type="button"
                        wire:click="login"
                        class="w-full h-10 rounded bg-[#0e639c] hover:bg-[#1177bb] border border-[#1177bb]/30 text-white font-bold text-xs tracking-wider active:scale-95 transition-all shadow-md flex items-center justify-center gap-2 uppercase font-mono"
                        wire:loading.attr="disabled"
                    >
                        <span wire:loading.remove>▶ RUN_AUTHENTICATION_AGENT</span>
                        <span wire:loading class="flex items-center gap-2">
                            <span class="inline-block w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                            <span>VALIDATING_KEYS...</span>
                        </span>
                    </button>

                    <div class="text-[9px] text-slate-600 leading-normal">
                        * System authentication relies on direct local key decryption. Ensure SSL nodes are fully established before transmitting auth parameters.
                    </div>
                </div>
            </div>

            <!-- Right Column: Live Code Telemetry Output -->
            <div class="p-6 sm:p-8 bg-[#181818] flex flex-col justify-between space-y-6">
                <div class="space-y-3">
                    <div class="flex items-center justify-between text-[#858585] border-b border-ide pb-3">
                        <span class="font-bold text-[#ce9178]">auth_payload.json</span>
                        <span class="text-[9px] text-slate-600 font-bold">LIVE STATE</span>
                    </div>

                    <!-- Live reactive JSON tree -->
                    <div class="space-y-1 text-slate-300 overflow-x-auto text-[10px] font-mono leading-tight">
                        <div><span class="text-[#d4d4d4]">{</span></div>
                        <div class="pl-4">
                            <span class="syntax-keyword">"agent_node"</span>: <span class="syntax-string">"client_authenticator"</span>,
                        </div>
                        <div class="pl-4">
                            <span class="syntax-keyword">"identity_node"</span>: <span class="syntax-string">"</span><span class="text-[#ce9178]">{{ $email ?: 'null' }}</span><span class="syntax-string">"</span>,
                        </div>
                        <div class="pl-4">
                            <span class="syntax-keyword">"secret_length"</span>: <span class="syntax-number">{{ strlen($password) }}</span>,
                        </div>
                        <div class="pl-4">
                            <span class="syntax-keyword">"session_lifetime"</span>: <span class="syntax-number">3600</span>,
                        </div>
                        <div class="pl-4">
                            <span class="syntax-keyword">"timestamp"</span>: <span class="syntax-string">"{{ now()->toIso8601String() }}"</span>
                        </div>
                        <div><span class="text-[#d4d4d4]">}</span></div>
                    </div>
                </div>

                <!-- Debug Logs Window -->
                <div class="space-y-2">
                    <div class="text-[9px] text-slate-500 font-bold uppercase tracking-wider">// AGENT_SYSTEM_LOGS: TRACE_OUTPUT</div>
                    <div class="bg-ide-editor rounded border border-ide p-3.5 text-[9px] font-mono leading-relaxed space-y-1 text-slate-500 min-h-[110px]">
                        <div>[<span class="text-cyan-400">INFO</span>] SSH tunnel established. Ready for auth parameters.</div>
                        @if(strlen($email) > 0)
                            <div class="text-slate-400">
                                [<span class="text-cyan-400">INFO</span>] identity_node buffer set: <span class="text-[#ce9178]">"{{ $email }}"</span>
                            </div>
                        @endif
                        @if(strlen($password) > 0)
                            <div class="text-slate-400">
                                [<span class="text-cyan-400">INFO</span>] auth_secret buffer parsed: <span class="text-[#b5cea8]">{{ strlen($password) }} bytes</span>
                            </div>
                        @endif
                        <div wire:loading class="text-yellow-400 animate-pulse">
                            [<span class="text-yellow-500 font-bold">WARN</span>] decrypting credentials against local_database.db...
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>
