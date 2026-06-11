<?php

use Livewire\Component;
use App\Models\Transaction;

new class extends Component
{
    public string $invoiceId = '';
    public ?Transaction $transaction = null;
    public bool $searched = false;

    public function mount()
    {
        $queryInvoice = request()->query('invoice');
        if ($queryInvoice) {
            $this->invoiceId = $queryInvoice;
            $this->track();
        }
    }

    public function track()
    {
        $this->validate([
            'invoiceId' => 'required|string|min:5'
        ]);

        $this->transaction = Transaction::with(['product', 'item'])
            ->where('invoice_id', trim($this->invoiceId))
            ->first();
            
        $this->searched = true;
    }
};
?>

<div class="max-w-2xl mx-auto my-8 relative z-10 space-y-6 font-mono text-xs text-[#d4d4d4]">
    
    <!-- Search Query Terminal Card -->
    <div class="rounded-lg border border-ide bg-[#181818] p-6 space-y-6 shadow-lg shadow-black/40">
        <div class="flex items-center justify-between text-[#858585] border-b border-ide pb-3">
            <span class="font-bold text-slate-400">// SYS_QUERY: ORDER_TRACKING_TELEMETRY</span>
            <span class="text-[9px] bg-slate-900 border border-ide text-slate-500 px-1.5 py-0.5 rounded">CMD: track --invoice</span>
        </div>

        <form wire:submit.prevent="track" class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <input 
                    wire:model="invoiceId" 
                    type="text" 
                    placeholder="Contoh: INV-20260612-123456" 
                    class="w-full h-10 pl-10 pr-4 rounded bg-ide-editor border border-ide text-xs text-white placeholder-slate-700 focus:outline-none focus:border-[#007acc] focus:ring-1 focus:ring-[#007acc]/20 transition-all @error('invoiceId') border-red-500/50 @enderror"
                >
                <svg class="absolute left-3.5 top-3.5 w-3.5 h-3.5 text-slate-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
            </div>
            
            <button 
                type="submit" 
                class="h-10 px-5 rounded bg-[#0e639c] hover:bg-[#1177bb] border border-[#1177bb]/30 text-white font-bold text-xs tracking-wider active:scale-95 transition-all shadow-md shrink-0 uppercase"
            >
                ▶ RUN_QUERY
            </button>
        </form>
        @error('invoiceId') <p class="text-[10px] text-red-400 font-semibold mt-1">{{ $message }}</p> @enderror
    </div>

    <!-- Search Results Console -->
    @if($searched)
        <div 
            x-transition:enter="transition ease-out duration-200 transform"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            class="space-y-6"
        >
            @if($transaction)
                <!-- Invoice Details Editor Card -->
                <div class="rounded-lg border border-ide bg-[#181818] p-6 space-y-6 shadow-lg shadow-black/40">
                    
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-ide pb-5 text-[#858585]">
                        <div>
                            <span class="text-[9px] text-slate-500 uppercase tracking-widest block font-bold">// DATABASE_RECORD</span>
                            <h3 class="text-sm font-bold text-white tracking-wide text-cyan-400 font-mono">{{ $transaction->invoice_id }}</h3>
                            <span class="text-[9px] text-slate-500 block mt-0.5">query_timestamp: {{ $transaction->created_at->format('Y-m-d H:i:s') }}</span>
                        </div>

                        <div>
                            <!-- Dynamic Status Badges (Syntax styles) -->
                            @if($transaction->status === 'completed')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded border border-emerald-500/30 bg-emerald-500/10 text-[9px] text-emerald-400 font-bold uppercase tracking-widest">
                                    [● STATUS: SUCCESS]
                                </span>
                            @elseif($transaction->status === 'failed')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded border border-red-500/30 bg-red-500/10 text-[9px] text-red-400 font-bold uppercase tracking-widest">
                                    [✗ STATUS: FAILED]
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded border border-yellow-500/30 bg-yellow-500/10 text-[9px] text-yellow-400 font-bold uppercase tracking-widest animate-pulse">
                                    [● STATUS: PENDING]
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Detail Info Grid -->
                    <div class="grid grid-cols-2 gap-4 text-xs leading-relaxed border-b border-ide pb-5 text-slate-400">
                        <div>
                            <span class="text-[9px] text-slate-500 uppercase block font-bold mb-1">"product_name"</span>
                            <span class="font-bold text-white block">{{ $transaction->product->name }}</span>
                            <span class="text-[10px] text-slate-500">// dev: {{ strtolower($transaction->product->developer) }}</span>
                        </div>
                        <div>
                            <span class="text-[9px] text-slate-500 uppercase block font-bold mb-1">"item_sku"</span>
                            <span class="font-bold text-white block">{{ $transaction->item->name }}</span>
                            <span class="text-[#b5cea8] font-bold">Rp {{ number_format($transaction->price_paid, 0, ',', '.') }}</span>
                        </div>
                        <div class="mt-2">
                            <span class="text-[9px] text-slate-500 uppercase block font-bold mb-1">"target_account"</span>
                            <span class="font-bold text-white block">{{ $transaction->user_id_input }}</span>
                        </div>
                        @if($transaction->zone_id_input)
                            <div class="mt-2">
                                <span class="text-[9px] text-slate-500 uppercase block font-bold mb-1">"target_zone"</span>
                                <span class="font-bold text-white block">{{ $transaction->zone_id_input }}</span>
                            </div>
                        @endif
                    </div>

                    <!-- Debug Timeline output logs -->
                    <div class="space-y-4">
                        <h4 class="text-[10px] font-bold text-slate-500 uppercase">// DEBUG_DISPATCH_LOGS</h4>
                        
                        <div class="bg-ide-editor p-4 rounded border border-ide space-y-3 font-mono text-[11px] leading-relaxed">
                            
                            <!-- Log Line 1 -->
                            <div class="flex gap-2">
                                <span class="text-slate-500 select-none">[01]</span>
                                <span class="text-cyan-400">[INFO]</span>
                                <span class="text-slate-400">Invoice created. Transaction records initialized in datastore.</span>
                            </div>

                            <!-- Log Line 2 -->
                            <div class="flex gap-2">
                                <span class="text-slate-500 select-none">[02]</span>
                                @if($transaction->status === 'completed')
                                    <span class="text-green-500">[SUCCESS]</span>
                                    <span class="text-slate-400">Payment received and validated via {{ $transaction->payment_method }}.</span>
                                @elseif($transaction->status === 'failed')
                                    <span class="text-red-500">[ERROR]</span>
                                    <span class="text-[#f43f5e]">Payment validation failed or timed out. Transaction cancelled.</span>
                                @else
                                    <span class="text-yellow-500">[WARNING]</span>
                                    <span class="text-slate-400">Awaiting payment settlement of Rp {{ number_format($transaction->price_paid, 0, ',', '.') }} via {{ $transaction->payment_method }}.</span>
                                @endif
                            </div>

                            <!-- Log Line 3 -->
                            <div class="flex gap-2">
                                <span class="text-slate-500 select-none">[03]</span>
                                @if($transaction->status === 'completed')
                                    <span class="text-green-500">[SUCCESS]</span>
                                    <span class="text-slate-400">Digital diamonds successfully injected into user target account.</span>
                                @elseif($transaction->status === 'failed')
                                    <span class="text-slate-600">[ABORTED]</span>
                                    <span class="text-slate-600">Payload injection skipped due to previous transaction fault.</span>
                                @else
                                    <span class="text-slate-500">[PENDING]</span>
                                    <span class="text-slate-500">Dispatch script queue is holding. Awaiting payment signal.</span>
                                @endif
                            </div>

                        </div>
                    </div>
                </div>
            @else
                <!-- Not Found Alert -->
                <div class="rounded-lg border border-red-500/20 bg-[#181818] p-8 text-center space-y-3">
                    <span class="text-3xl block">🔍</span>
                    <h3 class="text-xs font-bold text-white uppercase tracking-wider">// ERR: TRANSACTION_DESCRIPTOR_NOT_FOUND</h3>
                    <p class="text-[11px] text-slate-500 max-w-sm mx-auto">Query failed. No records matching invoice <span class="text-red-400 font-semibold">"{{ $invoiceId }}"</span> found inside transaction indexes.</p>
                </div>
            @endif
        </div>
    @endif

</div>