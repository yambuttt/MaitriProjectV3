<?php

use Livewire\Component;
use App\Models\Product;
use App\Models\Item;
use App\Models\Transaction;

new class extends Component
{
    public Product $product;
    
    // Form Inputs
    public string $userId = '';
    public string $zoneId = '';
    public string $whatsapp = '';
    public string $email = '';
    public string $promoCode = '';
    
    // Selectors
    public ?int $selectedItemId = null;
    public string $paymentMethod = '';
    public int $qty = 1;
    public bool $appliedPromo = false;
    
    // Invoice State
    public ?Transaction $invoice = null;
    public bool $showReceiptModal = false;

    public function mount(Product $product)
    {
        $this->product = $product;
    }

    public function selectItem(int $itemId)
    {
        $this->selectedItemId = $itemId;
    }

    public function selectPayment(string $method)
    {
        $this->paymentMethod = $method;
    }

    public function getSelectedItem()
    {
        return $this->selectedItemId ? Item::find($this->selectedItemId) : null;
    }

    public function incrementQty()
    {
        $this->qty++;
    }

    public function decrementQty()
    {
        if ($this->qty > 1) {
            $this->qty--;
        }
    }

    public function applyPromo()
    {
        $this->resetErrorBag('promoCode');
        
        if (empty($this->promoCode)) {
            $this->addError('promoCode', 'Promo code cannot be empty.');
            return;
        }

        if (strtoupper($this->promoCode) === 'MAITRIDEV') {
            $this->appliedPromo = true;
        } else {
            $this->addError('promoCode', 'Promo code is invalid.');
            $this->appliedPromo = false;
        }
    }

    public function calculateTotal()
    {
        $item = $this->getSelectedItem();
        if (!$item) return 0;
        
        $base = $item->price * $this->qty;
        if ($this->appliedPromo) {
            $base = max(0, $base - 10000); // 10k flat discount
        }
        
        return $base + $this->getFee($this->paymentMethod);
    }

    public function getFee(string $method)
    {
        if (empty($method)) return 0;
        
        if ($method === 'QRIS') {
            return 750;
        } elseif (in_array($method, ['OVO', 'DANA', 'ShopeePay'])) {
            return 1200;
        } else {
            return 2500;
        }
    }

    public function checkout()
    {
        $rules = [
            'userId' => 'required|string|min:3',
            'whatsapp' => 'required|numeric|digits_between:10,13',
            'email' => 'nullable|email',
            'selectedItemId' => 'required|integer|exists:items,id',
            'paymentMethod' => 'required|string',
            'qty' => 'required|integer|min:1',
        ];
        
        if ($this->product->placeholder_zone) {
            $rules['zoneId'] = 'required|string|min:1';
        }

        $this->validate($rules);

        $totalPrice = $this->calculateTotal();

        // Generate unique Invoice ID
        $invoiceId = 'INV-' . date('Ymd') . '-' . rand(100000, 999999);

        // Create transaction
        $this->invoice = Transaction::create([
            'invoice_id' => $invoiceId,
            'product_id' => $this->product->id,
            'item_id' => $this->selectedItemId,
            'user_id_input' => $this->userId,
            'zone_id_input' => $this->product->placeholder_zone ? $this->zoneId : null,
            'whatsapp_number' => $this->whatsapp,
            'payment_method' => $this->paymentMethod,
            'price_paid' => $totalPrice,
            'status' => 'pending'
        ]);

        $this->showReceiptModal = true;
    }

    public function closeReceipt()
    {
        $this->showReceiptModal = false;
        $this->reset(['userId', 'zoneId', 'whatsapp', 'email', 'promoCode', 'selectedItemId', 'paymentMethod', 'invoice', 'qty', 'appliedPromo']);
    }
};
?>

<div x-data="{ activeTab: 'transaksi' }" class="relative z-10 my-8 font-mono text-xs text-[#d4d4d4] pb-28 lg:pb-0">
    
    <!-- Image Viewer Tab style Game Cover Banner -->
    <div class="w-full rounded-lg border border-ide bg-[#181818] overflow-hidden mb-6 shadow-lg shadow-black/40">
        <div class="h-8 bg-[#1e1e1e] border-b border-ide px-4 flex items-center justify-between text-[11px] text-[#858585] select-none">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded bg-amber-500"></span>
                <span class="font-bold text-[#cccccc]">{{ strtolower($product->slug) }}_cover.jpg</span>
                <span class="text-slate-600 font-bold">// image_viewer</span>
            </div>
            <div class="text-slate-600 font-bold">100% ZOOM</div>
        </div>
        <div class="relative h-48 sm:h-60 md:h-64 w-full bg-cover bg-center" style="background-image: url('{{ $product->banner ?: 'https://images.unsplash.com/photo-1552820728-8b83bb6b773f?w=1000&q=80' }}')">
            <!-- Dark gradient mask -->
            <div class="absolute inset-0 bg-gradient-to-t from-[#181818] via-black/40 to-transparent"></div>
        </div>
        
        <!-- Game details header sitting on bottom edge -->
        <div class="relative p-5 bg-[#181818] border-t border-ide flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4 -mt-14 md:-mt-16 relative z-20">
                <!-- Cover logo -->
                <div class="w-20 h-20 md:w-24 md:h-24 rounded-lg bg-slate-900 border border-ide flex items-center justify-center p-1.5 shadow-2xl shrink-0">
                    <img src="{{ $product->logo ?: 'https://images.unsplash.com/photo-1552820728-8b83bb6b773f?w=120&q=80' }}" alt="{{ $product->name }}" class="w-full h-full object-cover rounded">
                </div>
                <div class="pt-6 md:pt-10">
                    <h2 class="text-lg md:text-xl font-bold text-white uppercase tracking-wider leading-tight font-space">{{ $product->name }}</h2>
                    <p class="text-[10px] text-slate-500 font-mono mt-0.5">// vendor: {{ strtolower($product->developer) }}</p>
                </div>
            </div>
            <!-- Telemetry checkmark tags -->
            <div class="flex flex-wrap items-center gap-2 text-[9px] font-mono text-slate-500">
                <span class="px-2 py-1 rounded bg-[#252526] border border-ide flex items-center gap-1.5 shadow-inner">
                    <span class="text-cyan-400">✔</span> PROCESS: INSTANT
                </span>
                <span class="px-2 py-1 rounded bg-[#252526] border border-ide flex items-center gap-1.5 shadow-inner">
                    <span class="text-cyan-400">✔</span> SUPPORT: 24/7_ONLINE
                </span>
                <span class="px-2 py-1 rounded bg-[#252526] border border-ide flex items-center gap-1.5 shadow-inner">
                    <span class="text-cyan-400">✔</span> PAY_ROUTE: SECURED
                </span>
            </div>
        </div>
    </div>

    <!-- Main Workspace Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        
        <!-- Left Column: Steps 1 to 6, Details & FAQs -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Tab Navigation Bar -->
            <div class="flex lg:hidden border border-ide bg-[#181818] rounded-lg overflow-hidden font-mono text-xs select-none">
                <button 
                    type="button"
                    @click="activeTab = 'transaksi'"
                    class="flex-1 py-3 text-center font-bold border-r border-ide transition-all flex items-center justify-center gap-1.5"
                    :class="activeTab === 'transaksi' ? 'bg-[#007acc] text-white font-bold' : 'text-slate-400 hover:text-slate-200 hover:bg-[#252526]'"
                >
                    Transaksi
                </button>
                <button 
                    type="button"
                    @click="activeTab = 'keterangan'"
                    class="flex-1 py-3 text-center font-bold transition-all flex items-center justify-center gap-1.5"
                    :class="activeTab === 'keterangan' ? 'bg-[#007acc] text-white font-bold' : 'text-slate-400 hover:text-slate-200 hover:bg-[#252526]'"
                >
                    Keterangan
                </button>
            </div>

            <!-- Transaksi Tab Content -->
            <div :class="activeTab === 'transaksi' ? 'block' : 'hidden lg:block'" class="space-y-6">
            
            <!-- STEP 1: INPUT USER ID -->
            <div class="rounded-lg border border-ide bg-[#181818] p-5 space-y-4 shadow-lg shadow-black/40">
                <div class="flex items-center gap-2 border-b border-ide pb-3 text-[#858585]">
                    <span class="text-purple-400 font-bold">01</span>
                    <span class="font-bold text-slate-300 uppercase tracking-wider">// IDENTIFICATION_PARAMS</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-[10px] text-slate-500 uppercase block font-bold">// INPUT: {{ strtoupper($product->placeholder_id) }}</label>
                        <input 
                            wire:model.live="userId" 
                            type="text" 
                            placeholder="e.g. 87654321" 
                            class="w-full h-9 px-3 rounded bg-ide-editor border border-ide text-xs placeholder-slate-700 text-[#cccccc] focus:outline-none focus:border-[#007acc] focus:ring-1 focus:ring-[#007acc]/20 transition-all @error('userId') border-red-500/50 @enderror"
                        >
                        @error('userId') <span class="text-[10px] text-red-400 font-semibold font-mono">{{ $message }}</span> @enderror
                    </div>

                    @if($product->placeholder_zone)
                        <div class="space-y-1">
                            <label class="text-[10px] text-slate-500 uppercase block font-bold">// INPUT: {{ strtoupper($product->placeholder_zone) }}</label>
                            <input 
                                wire:model.live="zoneId" 
                                type="text" 
                                placeholder="e.g. 1234" 
                                class="w-full h-9 px-3 rounded bg-ide-editor border border-ide text-xs placeholder-slate-700 text-[#cccccc] focus:outline-none focus:border-[#007acc] focus:ring-1 focus:ring-[#007acc]/20 transition-all @error('zoneId') border-red-500/50 @enderror"
                            >
                            @error('zoneId') <span class="text-[10px] text-red-400 font-semibold font-mono">{{ $message }}</span> @enderror
                        </div>
                    @endif
                </div>
            </div>

            <!-- STEP 2: SELECT ITEM -->
            <div class="rounded-lg border border-ide bg-[#181818] p-5 space-y-5 shadow-lg shadow-black/40">
                <div class="flex items-center gap-2 border-b border-ide pb-3 text-[#858585]">
                    <span class="text-purple-400 font-bold">02</span>
                    <span class="font-bold text-slate-300 uppercase tracking-wider">// PACKAGE_DENOMINATIONS</span>
                </div>

                @php
                    $passes = $product->items->filter(fn($i) => 
                        str_contains(strtolower($i->name), 'pass') || 
                        str_contains(strtolower($i->name), 'starlight') || 
                        str_contains(strtolower($i->name), 'membership') ||
                        str_contains(strtolower($i->name), 'member') ||
                        str_contains(strtolower($i->name), 'weekly') ||
                        str_contains(strtolower($i->name), 'plus')
                    );
                    $currency = $product->items->reject(fn($i) => 
                        str_contains(strtolower($i->name), 'pass') || 
                        str_contains(strtolower($i->name), 'starlight') || 
                        str_contains(strtolower($i->name), 'membership') ||
                        str_contains(strtolower($i->name), 'member') ||
                        str_contains(strtolower($i->name), 'weekly') ||
                        str_contains(strtolower($i->name), 'plus')
                    );
                @endphp

                <!-- Passes Group -->
                @if($passes->isNotEmpty())
                    <div class="space-y-2.5">
                        <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">// SECTION: PASSES_AND_BUNDLES</div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            @foreach($passes as $item)
                                <button 
                                    type="button"
                                    wire:click="selectItem({{ $item->id }})"
                                    class="group relative text-left rounded border p-3.5 flex flex-col justify-between transition-all duration-150 active:scale-95 h-[84px] bg-ide-editor {{ $selectedItemId === $item->id ? 'border-[#007acc] bg-[#007acc]/10 text-white shadow shadow-[#007acc]/10' : 'border-ide text-slate-400 hover:bg-[#252526] hover:text-white' }}"
                                >
                                    <span class="text-[10px] font-bold block mb-1 truncate leading-tight">{{ $item->name }}</span>
                                    <div>
                                        <span class="text-[11px] text-[#4fc1ff] font-bold block">Rp {{ number_format($item->price, 0, ',', '.') }}</span>
                                        @if($item->original_price && $item->original_price > $item->price)
                                            <span class="text-[8px] text-rose-500/80 font-bold line-through block mt-0.5">Rp {{ number_format($item->original_price, 0, ',', '.') }}</span>
                                        @endif
                                    </div>
                                    
                                    <div class="absolute right-2.5 bottom-2.5 w-2.5 h-2.5 rounded-sm border border-white/10 flex items-center justify-center bg-slate-900/60 {{ $selectedItemId === $item->id ? 'border-[#007acc] bg-[#007acc]' : '' }}">
                                        @if($selectedItemId === $item->id)
                                            <div class="w-1.5 h-1.5 rounded-sm bg-white"></div>
                                        @endif
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Direct Credits Group -->
                @if($currency->isNotEmpty())
                    <div class="space-y-2.5">
                        <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">// SECTION: DIRECT_DENOMINATIONS</div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            @foreach($currency as $item)
                                <button 
                                    type="button"
                                    wire:click="selectItem({{ $item->id }})"
                                    class="group relative text-left rounded border p-3.5 flex flex-col justify-between transition-all duration-150 active:scale-95 h-[84px] bg-ide-editor {{ $selectedItemId === $item->id ? 'border-[#007acc] bg-[#007acc]/10 text-white shadow shadow-[#007acc]/10' : 'border-ide text-slate-400 hover:bg-[#252526] hover:text-white' }}"
                                >
                                    <span class="text-[10px] font-bold block mb-1 truncate leading-tight">{{ $item->name }}</span>
                                    <div>
                                        <span class="text-[11px] text-[#4fc1ff] font-bold block">Rp {{ number_format($item->price, 0, ',', '.') }}</span>
                                        @if($item->original_price && $item->original_price > $item->price)
                                            <span class="text-[8px] text-rose-500/80 font-bold line-through block mt-0.5">Rp {{ number_format($item->original_price, 0, ',', '.') }}</span>
                                        @endif
                                    </div>
                                    
                                    <div class="absolute right-2.5 bottom-2.5 w-2.5 h-2.5 rounded-sm border border-white/10 flex items-center justify-center bg-slate-900/60 {{ $selectedItemId === $item->id ? 'border-[#007acc] bg-[#007acc]' : '' }}">
                                        @if($selectedItemId === $item->id)
                                            <div class="w-1.5 h-1.5 rounded-sm bg-white"></div>
                                        @endif
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
                
                @error('selectedItemId') <p class="text-[10px] text-red-400 font-semibold mt-2">{{ $message }}</p> @enderror
            </div>

            <!-- STEP 3: QUANTITY SELECTOR -->
            <div class="rounded-lg border border-ide bg-[#181818] p-5 space-y-4 shadow-lg shadow-black/40">
                <div class="flex items-center gap-2 border-b border-ide pb-3 text-[#858585]">
                    <span class="text-purple-400 font-bold">03</span>
                    <span class="font-bold text-slate-300 uppercase tracking-wider">// QUANTITY_SELECTOR</span>
                </div>

                <div class="flex items-center gap-3">
                    <button 
                        type="button"
                        wire:click="decrementQty"
                        class="w-9 h-9 rounded bg-[#252526] hover:bg-[#2d2d2e] border border-ide text-slate-400 hover:text-white flex items-center justify-center text-sm font-bold active:scale-95 transition-all select-none"
                    >
                        -
                    </button>
                    <div class="w-16 h-9 rounded bg-ide-editor border border-ide text-center flex items-center justify-center text-xs text-white font-mono select-none">
                        [ <span class="mx-1 font-bold text-cyan-400">{{ $qty }}</span> ]
                    </div>
                    <button 
                        type="button"
                        wire:click="incrementQty"
                        class="w-9 h-9 rounded bg-[#252526] hover:bg-[#2d2d2e] border border-ide text-slate-400 hover:text-white flex items-center justify-center text-sm font-bold active:scale-95 transition-all select-none"
                    >
                        +
                    </button>
                </div>
            </div>

            <!-- STEP 4: SELECT PAYMENT -->
            <div x-data="{ activeGroup: 'qris' }" class="rounded-lg border border-ide bg-[#181818] p-5 space-y-4 shadow-lg shadow-black/40">
                <div class="flex items-center gap-2 border-b border-ide pb-3 text-[#858585]">
                    <span class="text-purple-400 font-bold">04</span>
                    <span class="font-bold text-slate-300 uppercase tracking-wider">// TRANSACTION_ROUTING</span>
                </div>

                <div class="space-y-2">
                    
                    <!-- Accordion 1: QRIS -->
                    <div class="border border-ide rounded bg-ide-editor overflow-hidden">
                        <button 
                            type="button"
                            @click="activeGroup = (activeGroup === 'qris' ? '' : 'qris')" 
                            class="w-full px-4 py-2.5 bg-[#252526]/50 hover:bg-[#252526] text-left text-[10px] font-bold text-slate-400 flex items-center justify-between select-none border-b border-ide"
                        >
                            <span>// ROUTE_04A : QRIS_CHANNELS (Instant QR)</span>
                            <span :class="{'rotate-180': activeGroup === 'qris'}" class="transition-transform font-bold font-mono">▼</span>
                        </button>
                        <div x-show="activeGroup === 'qris'" class="p-3 space-y-2">
                            <div 
                                wire:click="selectPayment('QRIS')"
                                class="p-3 rounded border flex items-center justify-between cursor-pointer transition-colors text-[10px] {{ $paymentMethod === 'QRIS' ? 'bg-[#007acc]/10 border-[#007acc] text-white shadow shadow-[#007acc]/10' : 'bg-ide-editor border-ide text-slate-400 hover:bg-[#252526] hover:text-white' }}"
                            >
                                <div class="flex items-center gap-2.5">
                                    <span class="w-8 h-8 rounded bg-white flex items-center justify-center font-bold text-[8px] text-slate-900 border border-ide shrink-0 select-none shadow">QRIS</span>
                                    <div class="text-left font-mono">
                                        <span class="text-xs font-bold block">QRIS (OVO, DANA, LinkAja, ShopeePay)</span>
                                        <span class="text-[8px] text-slate-500">Processing Fee: Rp 750</span>
                                    </div>
                                </div>
                                <span class="text-xs font-bold text-[#4fc1ff]">
                                    @if($selectedItemId)
                                        Rp {{ number_format(($this->getSelectedItem()->price * $this->qty) + 750 - ($this->appliedPromo ? 10000 : 0), 0, ',', '.') }}
                                    @else
                                        WAITING_ITEM
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Accordion 2: E-Wallet -->
                    <div class="border border-ide rounded bg-ide-editor overflow-hidden">
                        <button 
                            type="button"
                            @click="activeGroup = (activeGroup === 'ewallet' ? '' : 'ewallet')" 
                            class="w-full px-4 py-2.5 bg-[#252526]/50 hover:bg-[#252526] text-left text-[10px] font-bold text-slate-400 flex items-center justify-between select-none border-b border-ide"
                        >
                            <span>// ROUTE_04B : EWALLET_CHANNELS (Direct App)</span>
                            <span :class="{'rotate-180': activeGroup === 'ewallet'}" class="transition-transform font-bold font-mono">▼</span>
                        </button>
                        <div x-show="activeGroup === 'ewallet'" class="p-3 space-y-2">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                                @foreach(['DANA', 'OVO', 'ShopeePay'] as $method)
                                    <div 
                                        wire:click="selectPayment('{{ $method }}')"
                                        class="p-3.5 rounded border flex flex-col justify-between cursor-pointer transition-colors h-[84px] {{ $paymentMethod === $method ? 'bg-[#007acc]/10 border-[#007acc] text-white shadow shadow-[#007acc]/10' : 'bg-ide-editor border-ide text-slate-400 hover:bg-[#252526] hover:text-white' }}"
                                    >
                                        <div class="flex items-center justify-between font-mono">
                                            <span class="text-xs font-bold uppercase tracking-wider">{{ $method }}</span>
                                            <span class="text-[7px] text-slate-500">Fee: Rp 1.200</span>
                                        </div>
                                        <span class="text-xs font-bold text-[#4fc1ff]">
                                            @if($selectedItemId)
                                                Rp {{ number_format(($this->getSelectedItem()->price * $this->qty) + 1200 - ($this->appliedPromo ? 10000 : 0), 0, ',', '.') }}
                                            @else
                                                WAITING
                                            @endif
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Accordion 3: Virtual Accounts -->
                    <div class="border border-ide rounded bg-ide-editor overflow-hidden">
                        <button 
                            type="button"
                            @click="activeGroup = (activeGroup === 'va' ? '' : 'va')" 
                            class="w-full px-4 py-2.5 bg-[#252526]/50 hover:bg-[#252526] text-left text-[10px] font-bold text-slate-400 flex items-center justify-between select-none border-b border-ide"
                        >
                            <span>// ROUTE_04C : VIRTUAL_ACCOUNT_CHANNELS (Bank Transfer)</span>
                            <span :class="{'rotate-180': activeGroup === 'va'}" class="transition-transform font-bold font-mono">▼</span>
                        </button>
                        <div x-show="activeGroup === 'va'" class="p-3 space-y-2">
                            @foreach(['BCA Virtual Account', 'Mandiri VA', 'BNI VA'] as $method)
                                <div 
                                    wire:click="selectPayment('{{ $method }}')"
                                    class="p-3 rounded border flex items-center justify-between cursor-pointer transition-colors text-[10px] {{ $paymentMethod === $method ? 'bg-[#007acc]/10 border-[#007acc] text-white shadow shadow-[#007acc]/10' : 'bg-ide-editor border-ide text-slate-400 hover:bg-[#252526] hover:text-white' }}"
                                >
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-8 h-8 rounded bg-blue-600 flex items-center justify-center font-bold text-[8px] text-white border border-ide shrink-0 shadow select-none font-mono">{{ substr($method, 0, 3) }}</span>
                                        <div class="text-left font-mono">
                                            <span class="text-xs font-bold block">{{ $method }}</span>
                                            <span class="text-[8px] text-slate-500">Processing Fee: Rp 2.500</span>
                                        </div>
                                    </div>
                                    <span class="text-xs font-bold text-[#4fc1ff]">
                                        @if($selectedItemId)
                                            Rp {{ number_format(($this->getSelectedItem()->price * $this->qty) + 2500 - ($this->appliedPromo ? 10000 : 0), 0, ',', '.') }}
                                        @else
                                            WAITING
                                        @endif
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Accordion 4: Convenience Store -->
                    <div class="border border-ide rounded bg-ide-editor overflow-hidden">
                        <button 
                            type="button"
                            @click="activeGroup = (activeGroup === 'store' ? '' : 'store')" 
                            class="w-full px-4 py-2.5 bg-[#252526]/50 hover:bg-[#252526] text-left text-[10px] font-bold text-slate-400 flex items-center justify-between select-none border-b border-ide"
                        >
                            <span>// ROUTE_04D : CONVENIENCE_STORE_CHANNELS (Over the Counter)</span>
                            <span :class="{'rotate-180': activeGroup === 'store'}" class="transition-transform font-bold font-mono">▼</span>
                        </button>
                        <div x-show="activeGroup === 'store'" class="p-3 space-y-2">
                            @foreach(['Indomaret', 'Alfamart'] as $method)
                                <div 
                                    wire:click="selectPayment('{{ $method }}')"
                                    class="p-3 rounded border flex items-center justify-between cursor-pointer transition-colors text-[10px] {{ $paymentMethod === $method ? 'bg-[#007acc]/10 border-[#007acc] text-white shadow shadow-[#007acc]/10' : 'bg-ide-editor border-ide text-slate-400 hover:bg-[#252526] hover:text-white' }}"
                                >
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-8 h-8 rounded bg-red-600 flex items-center justify-center font-bold text-[8px] text-white border border-ide shrink-0 shadow select-none font-mono">{{ substr($method, 0, 4) }}</span>
                                        <div class="text-left font-mono">
                                            <span class="text-xs font-bold block">{{ $method }}</span>
                                            <span class="text-[8px] text-slate-500">Processing Fee: Rp 2.500</span>
                                        </div>
                                    </div>
                                    <span class="text-xs font-bold text-[#4fc1ff]">
                                        @if($selectedItemId)
                                            Rp {{ number_format(($this->getSelectedItem()->price * $this->qty) + 2500 - ($this->appliedPromo ? 10000 : 0), 0, ',', '.') }}
                                        @else
                                            WAITING
                                        @endif
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                </div>
                @error('paymentMethod') <p class="text-[10px] text-red-400 font-semibold mt-2">{{ $message }}</p> @enderror
            </div>

            <!-- STEP 5: CONTACT INFORMATION -->
            <div class="rounded-lg border border-ide bg-[#181818] p-5 space-y-4 shadow-lg shadow-black/40">
                <div class="flex items-center gap-2 border-b border-ide pb-3 text-[#858585]">
                    <span class="text-purple-400 font-bold">05</span>
                    <span class="font-bold text-slate-300 uppercase tracking-wider">// DISPATCH_NOTIFICATIONS</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 font-mono">
                    <div class="space-y-1">
                        <label class="text-[10px] text-slate-500 uppercase block font-bold">// INPUT: EMAIL_ADDRESS (OPTIONAL)</label>
                        <input 
                            wire:model.live="email" 
                            type="email" 
                            placeholder="e.g. buyer@maitri.com" 
                            class="w-full h-9 px-3 rounded bg-ide-editor border border-ide text-xs placeholder-slate-700 text-[#cccccc] focus:outline-none focus:border-[#007acc]"
                        >
                        @error('email') <span class="text-[10px] text-red-400 font-semibold">{{ $message }}</span> @enderror
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] text-slate-500 uppercase block font-bold">// INPUT: WHATSAPP_NUMBER (REQUIRED)</label>
                        <input 
                            wire:model.live="whatsapp" 
                            type="text" 
                            placeholder="e.g. 081234567890" 
                            class="w-full h-9 px-3 rounded bg-ide-editor border border-ide text-xs placeholder-slate-700 text-[#cccccc] focus:outline-none focus:border-[#007acc] @error('whatsapp') border-red-500/50 @enderror"
                        >
                        @error('whatsapp') <span class="text-[10px] text-red-400 font-semibold">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- STEP 6: PROMO CODE VALIDATION -->
            <div class="rounded-lg border border-ide bg-[#181818] p-5 space-y-4 shadow-lg shadow-black/40">
                <div class="flex items-center gap-2 border-b border-ide pb-3 text-[#858585]">
                    <span class="text-purple-400 font-bold">06</span>
                    <span class="font-bold text-slate-300 uppercase tracking-wider">// PROMO_CODE_VALIDATION</span>
                </div>

                <div class="flex gap-2">
                    <div class="flex-1 relative">
                        <input 
                            wire:model.live="promoCode" 
                            type="text" 
                            placeholder="Gunakan kode promo (e.g. MAITRIDEV)" 
                            class="w-full h-9 px-3 rounded bg-ide-editor border border-ide text-xs placeholder-slate-700 text-white uppercase focus:outline-none focus:border-[#007acc]"
                        >
                    </div>
                    <button 
                        type="button"
                        wire:click="applyPromo"
                        class="h-9 px-4 rounded bg-[#252526] hover:bg-[#2e2e30] border border-ide text-slate-300 font-bold text-xs active:scale-95 transition-all"
                    >
                        APPLY_CODE
                    </button>
                </div>
                
                @error('promoCode') 
                    <span class="text-[10px] text-red-400 font-semibold font-mono block mt-1">{{ $message }}</span> 
                @enderror
                @if($appliedPromo)
                    <div class="text-[10px] text-green-400 font-bold font-mono mt-1">
                        // SUCCESS: Coupon 'MAITRIDEV' applied! Discounted Rp 10.000.
                    </div>
                @endif
            </div>
            </div> <!-- End of Transaksi Tab Content -->

            <!-- Description Container (Always on Desktop, Keterangan tab on Mobile) -->
            <div :class="activeTab === 'keterangan' ? 'block' : 'hidden lg:block'" class="space-y-6">
                <!-- PRODUCT DESCRIPTION -->
                <div class="rounded-lg border border-ide bg-[#181818] p-5 space-y-3 shadow-lg shadow-black/40">
                    <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">// DOCUMENTATION: DESCRIPTION</div>
                    <div class="text-xs text-slate-400 font-mono leading-relaxed bg-[#1e1e1e] p-4 rounded border border-ide whitespace-pre-line">
                        {{ $product->description }}
                        
                        1. Masukkan Data Akun (User ID & Server ID jika ada).
                        2. Pilih nominal top up yang tersedia.
                        3. Atur jumlah pembelian (quantity) yang diinginkan.
                        4. Pilih metode pembayaran yang Anda inginkan.
                        5. Isi kontak berupa nomor WhatsApp & Email untuk update status transaksi.
                        6. Masukkan kode promo (jika ada) dan selesaikan pembayaran. Produk akan masuk otomatis ke akun Anda.
                    </div>
                </div>
            </div>

            <!-- Detailed Telemetry Ratings & Reviews (Keterangan-only on Mobile, Hidden on Desktop) -->
            <div :class="activeTab === 'keterangan' ? 'block lg:hidden' : 'hidden'" class="space-y-6">
                <!-- DETAILED TELEMETRY / REVIEWS -->
                <div class="rounded-lg border border-ide bg-[#181818] p-5 space-y-4 shadow-lg shadow-black/40 font-mono">
                    <div class="flex items-center justify-between border-b border-ide pb-3 text-[#858585]">
                        <span class="font-bold text-slate-400">// ANALYTICS_TELEMETRY: CUSTOMER_REVIEWS</span>
                        <span class="text-[9px] text-[#4ec9b0] font-bold font-mono">STATUS: OK</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center bg-ide-editor p-4 rounded border border-ide">
                        <!-- Rating Summary Score -->
                        <div class="text-center md:border-r md:border-ide/60 space-y-1">
                            <div class="text-[9px] text-slate-500 font-bold uppercase tracking-wider">Overall Score</div>
                            <div class="text-4xl font-extrabold text-[#b5cea8] font-space tracking-tight">4.99<span class="text-xs text-slate-500 font-normal">/5.0</span></div>
                            <div class="flex text-amber-500 gap-0.5 text-xs justify-center select-none">
                                <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
                            </div>
                            <div class="text-[9px] text-slate-500">Dari 55.4k ulasan</div>
                        </div>

                        <!-- Rating Bars -->
                        <div class="md:col-span-2 space-y-1.5 text-[9px] text-slate-400">
                            <!-- 5 star -->
                            <div class="flex items-center gap-2">
                                <span class="w-8 shrink-0 flex items-center justify-end gap-0.5">5 <span class="text-amber-500">★</span></span>
                                <div class="flex-1 h-2 bg-[#252526] rounded-sm overflow-hidden border border-ide">
                                    <div class="h-full bg-amber-500 rounded-sm" style="width: 98%;"></div>
                                </div>
                                <span class="w-12 text-slate-500">55.26rb</span>
                            </div>
                            <!-- 4 star -->
                            <div class="flex items-center gap-2">
                                <span class="w-8 shrink-0 flex items-center justify-end gap-0.5">4 <span class="text-amber-500">★</span></span>
                                <div class="flex-1 h-2 bg-[#252526] rounded-sm overflow-hidden border border-ide">
                                    <div class="h-full bg-amber-500/80 rounded-sm" style="width: 1.5%;"></div>
                                </div>
                                <span class="w-12 text-slate-500">130</span>
                            </div>
                            <!-- 3 star -->
                            <div class="flex items-center gap-2">
                                <span class="w-8 shrink-0 flex items-center justify-end gap-0.5">3 <span class="text-amber-500">★</span></span>
                                <div class="flex-1 h-2 bg-[#252526] rounded-sm overflow-hidden border border-ide">
                                    <div class="h-full bg-amber-500/60 rounded-sm" style="width: 0.8%;"></div>
                                </div>
                                <span class="w-12 text-slate-500">46</span>
                            </div>
                            <!-- 2 star -->
                            <div class="flex items-center gap-2">
                                <span class="w-8 shrink-0 flex items-center justify-end gap-0.5">2 <span class="text-amber-500">★</span></span>
                                <div class="flex-1 h-2 bg-[#252526] rounded-sm overflow-hidden border border-ide">
                                    <div class="h-full bg-amber-500/40 rounded-sm" style="width: 0.3%;"></div>
                                </div>
                                <span class="w-12 text-slate-500">10</span>
                            </div>
                            <!-- 1 star -->
                            <div class="flex items-center gap-2">
                                <span class="w-8 shrink-0 flex items-center justify-end gap-0.5">1 <span class="text-amber-500">★</span></span>
                                <div class="flex-1 h-2 bg-[#252526] rounded-sm overflow-hidden border border-ide">
                                    <div class="h-full bg-amber-500/20 rounded-sm" style="width: 0.5%;"></div>
                                </div>
                                <span class="w-12 text-slate-500">22</span>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Reviews Feed -->
                    <div class="space-y-3 pt-2">
                        <div class="text-[10px] text-slate-500 uppercase tracking-wider font-bold">// REVIEWS_FEED: TRACE_OUTPUT</div>
                        
                        <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
                            <div class="p-2.5 rounded bg-ide-editor border border-ide text-[10px] space-y-1">
                                <div class="flex justify-between items-center text-slate-400">
                                    <span class="text-[#4fc1ff] font-bold">ran**********</span>
                                    <span class="text-slate-600">11-06-2026 20:34:34</span>
                                </div>
                                <div class="text-[#ce9178] font-bold">"Prosesnya cepat banget"</div>
                                <div class="text-slate-500 text-[8px]">// Purchased: 15 Bintang + 3 Bonus Mythic</div>
                            </div>
                            <div class="p-2.5 rounded bg-ide-editor border border-ide text-[10px] space-y-1">
                                <div class="flex justify-between items-center text-slate-400">
                                    <span class="text-[#4fc1ff] font-bold">adi**********</span>
                                    <span class="text-slate-600">11-06-2026 19:15:20</span>
                                </div>
                                <div class="text-[#ce9178] font-bold">"Top up diamond termurah, langsung masuk 10 detik"</div>
                                <div class="text-slate-500 text-[8px]">// Purchased: 257 Diamonds</div>
                            </div>
                            <div class="p-2.5 rounded bg-ide-editor border border-ide text-[10px] space-y-1">
                                <div class="flex justify-between items-center text-slate-400">
                                    <span class="text-[#4fc1ff] font-bold">mrc**********</span>
                                    <span class="text-slate-600">10-06-2026 14:02:11</span>
                                </div>
                                <div class="text-[#ce9178] font-bold">"Website gokil temanya hacker gini wkwkwk keren parah"</div>
                                <div class="text-slate-500 text-[8px]">// Purchased: 706 Diamonds</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FAQ SECTION (Always on Desktop, Hidden on Mobile) -->
            <div x-data="{ faq: null }" class="hidden lg:block rounded-lg border border-ide bg-[#181818] p-5 space-y-4 shadow-lg shadow-black/40 font-mono">
                <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">// KNOWLEDGE_BASE: FAQ</div>
                
                <div class="space-y-2">
                    <div class="border border-ide rounded overflow-hidden">
                        <button 
                            type="button"
                            @click="faq = (faq === 1 ? null : 1)"
                            class="w-full px-4 py-2 bg-ide-editor hover:bg-[#252526] text-left text-[11px] font-bold text-slate-300 flex items-center justify-between select-none"
                        >
                            <span>1. Bagaimana cara top up di Maitri Store?</span>
                            <span x-text="faq === 1 ? '[-]' : '[+]'" class="font-bold"></span>
                        </button>
                        <div x-show="faq === 1" class="p-3 bg-ide-editor border-t border-ide text-[10px] text-slate-400 leading-relaxed font-mono">
                            Cukup masukkan ID game Anda, pilih denominasi/nominal produk, tentukan kuantitas, pilih metode pembayaran yang diinginkan, isi nomor WhatsApp untuk notifikasi, dan lakukan pembayaran sesuai tagihan. Transaksi diproses dalam hitungan detik.
                        </div>
                    </div>

                    <div class="border border-ide rounded overflow-hidden">
                        <button 
                            type="button"
                            @click="faq = (faq === 2 ? null : 2)"
                            class="w-full px-4 py-2 bg-ide-editor hover:bg-[#252526] text-left text-[11px] font-bold text-slate-300 flex items-center justify-between select-none"
                        >
                            <span>2. Metode pembayaran apa saja yang tersedia?</span>
                            <span x-text="faq === 2 ? '[-]' : '[+]'" class="font-bold"></span>
                        </button>
                        <div x-show="faq === 2" class="p-3 bg-ide-editor border-t border-ide text-[10px] text-slate-400 leading-relaxed font-mono">
                            Kami menyediakan berbagai jalur pembayaran mulai dari QRIS (dukungan penuh OVO, DANA, GoPay, ShopeePay, LinkAja), E-Wallet langsung, Virtual Account Bank Transfer (BCA, Mandiri, BNI, BRI), hingga gerai ritel modern (Indomaret, Alfamart).
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Column: Sticky Sidebar -->
        <div class="hidden lg:block lg:col-span-1 space-y-6 lg:sticky lg:top-24">
            
            <!-- SYSTEM TELEMETRY / RATINGS -->
            <div class="rounded-lg border border-ide bg-[#181818] p-5 space-y-3 shadow-lg shadow-black/40">
                <div class="flex items-center justify-between border-b border-ide pb-3 text-[#858585]">
                    <span class="font-bold text-slate-400">// ANALYTICS_TELEMETRY</span>
                    <span class="text-[9px] text-[#4ec9b0] font-bold font-mono">STATUS: OK</span>
                </div>
                
                <div class="flex items-center gap-3">
                    <span class="text-3xl font-extrabold text-[#b5cea8] font-space tracking-tight">4.99</span>
                    <div class="space-y-0.5 font-mono">
                        <div class="flex text-amber-500 gap-0.5 text-xs select-none">
                            <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
                        </div>
                        <span class="text-[9px] text-slate-500 uppercase tracking-wider block">Berdasarkan total 304K rating</span>
                    </div>
                </div>
            </div>

            <!-- SUPPORT CHANNEL -->
            <div class="rounded-lg border border-ide bg-[#181818] p-5 space-y-3 shadow-lg shadow-black/40">
                <div class="flex items-center justify-between border-b border-ide pb-3 text-[#858585]">
                    <span class="font-bold text-slate-400">// COMMUNICATIONS_NODE</span>
                    <span class="text-[9px] text-emerald-500 font-bold font-mono">ONLINE</span>
                </div>
                
                <p class="text-[10px] text-slate-500 font-mono">// Butuh bantuan darurat? Hubungi staf kami langsung.</p>
                <a 
                    href="#" 
                    class="h-9 w-full rounded bg-[#252526] hover:bg-[#2d2d2e] border border-ide text-[#4fc1ff] font-bold text-xs flex items-center justify-center gap-2 active:scale-95 transition-all shadow-sm font-mono"
                >
                    CONTACT_SUPPORT_CHANNEL
                </a>
            </div>

            <!-- LIVE REACTIVE JSON CONSOLE PREVIEW -->
            <div class="rounded-lg border border-ide bg-[#181818] p-5 space-y-3 shadow-lg shadow-black/40">
                <div class="flex items-center justify-between text-[#858585] border-b border-ide pb-3">
                    <span class="font-bold text-[#ce9178]">transaction_payload.json</span>
                    <span class="text-[9px] text-slate-600 font-bold">STATE: COMPILED</span>
                </div>

                <div class="space-y-1 text-slate-300 overflow-x-auto text-[10px] font-mono leading-tight">
                    <div><span class="text-[#d4d4d4]">{</span></div>
                    <div class="pl-4">
                        <span class="syntax-keyword">"product_slug"</span>: <span class="syntax-string">"{{ $product->slug }}"</span>,
                    </div>
                    <div class="pl-4">
                        <span class="syntax-keyword">"user_id"</span>: <span class="syntax-string">"{{ $userId ?: 'null' }}"</span>,
                    </div>
                    @if($product->placeholder_zone)
                        <div class="pl-4">
                            <span class="syntax-keyword">"zone_id"</span>: <span class="syntax-string">"{{ $zoneId ?: 'null' }}"</span>,
                        </div>
                    @endif
                    <div class="pl-4">
                        <span class="syntax-keyword">"item_sku"</span>: <span class="syntax-string">"{{ $selectedItemId ? $this->getSelectedItem()->sku : 'null' }}"</span>,
                    </div>
                    <div class="pl-4">
                        <span class="syntax-keyword">"item_qty"</span>: <span class="syntax-number">{{ $qty }}</span>,
                    </div>
                    <div class="pl-4">
                        <span class="syntax-keyword">"gateway_route"</span>: <span class="syntax-string">"{{ $paymentMethod ?: 'null' }}"</span>,
                    </div>
                    <div class="pl-4">
                        <span class="syntax-keyword">"coupon_applied"</span>: <span class="syntax-number">{{ $appliedPromo ? 'true' : 'false' }}</span>,
                    </div>
                    <div class="pl-4">
                        <span class="syntax-keyword">"calculated_fee"</span>: <span class="syntax-number">{{ $this->getFee($paymentMethod) }}</span>,
                    </div>
                    <div class="pl-4">
                        <span class="syntax-keyword">"net_payable"</span>: <span class="syntax-number">{{ $this->calculateTotal() }}</span>,
                    </div>
                    <div class="pl-4">
                        <span class="syntax-keyword">"whatsapp_node"</span>: <span class="syntax-string">"{{ $whatsapp ?: 'null' }}"</span>,
                    </div>
                    <div class="pl-4">
                        <span class="syntax-keyword">"email_node"</span>: <span class="syntax-string">"{{ $email ?: 'null' }}"</span>
                    </div>
                    <div><span class="text-[#d4d4d4]">}</span></div>
                </div>
            </div>

            <!-- CHECKOUT CONSOLE ACTION PANEL -->
            <div class="hidden lg:block rounded-lg border border-ide bg-[#181818] p-5 space-y-4 shadow-lg shadow-black/40">
                <div class="flex items-center justify-between border-b border-ide pb-3 text-[#858585]">
                    <span class="font-bold text-slate-400">// DISPATCH_COMPILER</span>
                    <span class="text-[9px] text-yellow-500 font-bold">READY_TO_RUN</span>
                </div>

                @if($selectedItemId)
                    @php
                        $selectedItem = $this->getSelectedItem();
                    @endphp
                    <div class="space-y-2 text-xs font-mono">
                        <div class="flex justify-between border-b border-ide/50 pb-2">
                            <span class="text-slate-500">Item:</span>
                            <span class="text-white font-bold">{{ $selectedItem->name }} (x{{ $qty }})</span>
                        </div>
                        <div class="flex justify-between border-b border-ide/50 pb-2">
                            <span class="text-slate-500">Route:</span>
                            <span class="text-white font-bold">{{ $paymentMethod ?: 'Not Selected' }}</span>
                        </div>
                        @if($appliedPromo)
                            <div class="flex justify-between border-b border-ide/50 pb-2 text-green-400">
                                <span>Discount:</span>
                                <span>-Rp 10.000</span>
                            </div>
                        @endif
                        <div class="flex justify-between text-sm font-bold pt-1">
                            <span class="text-slate-300">Total:</span>
                            <span class="text-[#b5cea8]">Rp {{ number_format($this->calculateTotal(), 0, ',', '.') }}</span>
                        </div>
                    </div>
                @else
                    <div class="text-center py-6 text-slate-600 font-mono text-xs select-none">
                        // AWAITING_SELECTION_PARAMS
                    </div>
                @endif

                <button 
                    type="button"
                    wire:click="checkout" 
                    class="w-full h-11 rounded bg-[#0e639c] hover:bg-[#1177bb] border border-[#1177bb]/30 text-white font-bold text-xs tracking-wider active:scale-95 transition-all shadow-md flex items-center justify-center gap-2 uppercase font-mono"
                    wire:loading.attr="disabled"
                >
                    <span wire:loading.remove>▶ EXECUTE_ORDER (PESAN SEKARANG)</span>
                    <span wire:loading class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                </button>
            </div>

        </div>

    </div>

    <!-- IDE Invoice Receipt Modal -->
    <div 
        x-show="@js($showReceiptModal)" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-98"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-98"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm"
        style="display: none;"
    >
        <div class="w-full max-w-md bg-[#181818] border border-ide rounded-lg p-5 shadow-2xl relative space-y-5">
            
            <!-- Modal Header -->
            <div class="text-center relative border-b border-ide pb-3 font-mono">
                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded border border-yellow-500/30 bg-yellow-500/10 text-[9px] text-yellow-400 font-bold uppercase tracking-wider mb-2 animate-pulse animate-duration-1000">
                    // STATE: AWAITING_CONFIRMATION
                </span>
                <h4 class="text-xs font-bold text-white uppercase tracking-wider">// PAYLOAD_INVOICE_DETAILS</h4>
            </div>

            @if($invoice)
                <!-- QR Code / Payment visual box -->
                @if($invoice->payment_method === 'QRIS')
                    <div class="flex flex-col items-center justify-center bg-white p-3 rounded mx-auto w-40 h-40 border border-ide shadow-lg select-none">
                        <!-- Simulated QR Code -->
                        <div class="w-32 h-32 bg-slate-950 flex flex-wrap p-2 rounded relative">
                            <div class="w-4 h-4 border border-white absolute top-1 left-1"></div>
                            <div class="w-4 h-4 border border-white absolute top-1 right-1"></div>
                            <div class="w-4 h-4 border border-white absolute bottom-1 left-1"></div>
                            <div class="w-full h-full flex items-center justify-center text-[8px] font-mono text-[#007acc] font-bold">QRIS_GATEWAY</div>
                        </div>
                    </div>
                @else
                    <div class="bg-ide-editor border border-ide rounded p-3 text-center">
                        <span class="text-[9px] text-slate-500 uppercase tracking-widest block mb-1 font-mono">// VA_ROUTING_KEY</span>
                        <div class="flex items-center justify-center gap-2">
                            <span class="text-base font-bold text-white tracking-wider font-mono">88073{{ $invoice->whatsapp_number }}</span>
                            <button type="button" class="text-[#4fc1ff] hover:underline text-[9px] font-bold font-mono">COPY</button>
                        </div>
                        <span class="text-[9px] text-slate-500 block mt-1 font-mono">HOLDER: MAITRI_GATEWAY</span>
                    </div>
                @endif

                <!-- Receipt Details Grid -->
                <div class="space-y-2 text-xs font-mono text-slate-400">
                    <div class="flex justify-between">
                        <span>[invoice_id]</span>
                        <span class="font-bold text-white">{{ $invoice->invoice_id }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>[product_id]</span>
                        <span class="text-white">{{ $product->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>[account_node]</span>
                        <span class="text-white">{{ $invoice->user_id_input }} @if($invoice->zone_id_input) ({{ $invoice->zone_id_input }}) @endif</span>
                    </div>
                    <div class="flex justify-between">
                        <span>[item_sku]</span>
                        <span class="text-white">{{ $invoice->item->name }} (x{{ $qty }})</span>
                    </div>
                    <div class="flex justify-between">
                        <span>[payment_method]</span>
                        <span class="text-white">{{ $invoice->payment_method }}</span>
                    </div>
                    <div class="flex justify-between text-xs pt-2 border-t border-ide font-bold">
                        <span class="text-slate-300">[total_amount]</span>
                        <span class="text-[#b5cea8]">Rp {{ number_format($invoice->price_paid, 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- Simulated code response -->
                <div class="text-[9px] text-slate-500 bg-ide-editor p-2.5 rounded border border-ide font-mono">
                    <span class="text-green-500">System:</span> Transaction logged. Visit <a href="/tracker" class="text-[#4fc1ff] underline">// order_tracker.py</a> with invoice_id to check dispatch status.
                </div>
            @endif

            <!-- Modal Actions -->
            <button 
                type="button"
                wire:click="closeReceipt" 
                class="w-full h-9 rounded bg-[#252526] hover:bg-[#2e2e30] border border-ide text-slate-300 font-bold text-xs tracking-wider transition-colors font-mono"
            >
                CLOSE_PANEL
            </button>
        </div>
    </div>

    <!-- Mobile Sticky Checkout Bar (Fixed at the bottom of viewport) -->
    <div x-data="{ mobileExpanded: false }" class="fixed bottom-0 inset-x-0 z-50 bg-[#181818] border-t border-ide p-3 lg:hidden flex flex-col gap-2.5 shadow-2xl select-none font-mono">
        <!-- Toggle Header Row -->
        <div 
            @click="if (@js($selectedItemId)) mobileExpanded = !mobileExpanded"
            class="text-[10px] text-slate-400 flex items-center justify-between cursor-pointer py-1"
        >
            @if($selectedItemId)
                @php
                    $selectedItem = $this->getSelectedItem();
                @endphp
                <div class="flex items-center gap-2 min-w-0">
                    <div class="w-6 h-6 rounded bg-slate-900 border border-ide flex items-center justify-center p-0.5 shrink-0 shadow">
                        <img src="{{ $product->logo ?: 'https://images.unsplash.com/photo-1552820728-8b83bb6b773f?w=120&q=80' }}" alt="{{ $product->name }}" class="w-full h-full object-cover rounded-sm">
                    </div>
                    <div class="min-w-0 text-left">
                        <span class="text-[#cccccc] font-bold block truncate leading-tight">{{ $product->name }}</span>
                        <span class="text-slate-500 text-[8px] block truncate mt-0.5">{{ $selectedItem->name }} (x{{ $qty }})</span>
                    </div>
                </div>
                <div class="shrink-0 flex items-center gap-2">
                    <div class="text-right">
                        <span class="text-slate-500 font-bold">Total:</span>
                        <span class="text-[#b5cea8] font-bold text-xs ml-1">Rp {{ number_format($this->calculateTotal(), 0, ',', '.') }}</span>
                    </div>
                    <span class="text-slate-500 font-bold transition-transform duration-200" :class="{'rotate-180': mobileExpanded}">▼</span>
                </div>
            @else
                <div class="flex items-center gap-1.5 text-slate-500">
                    <span>// Belum ada item produk yang dipilih.</span>
                </div>
            @endif
        </div>

        <!-- Expanded Price Breakdown Panel -->
        <div 
            x-show="mobileExpanded" 
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 max-h-0"
            x-transition:enter-end="opacity-100 max-h-[380px]"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 max-h-[380px]"
            x-transition:leave-end="opacity-0 max-h-0"
            class="overflow-hidden border-t border-ide/50 pt-2.5 pb-1 space-y-1.5 text-[10px] text-slate-400"
            style="display: none;"
        >
            @if($selectedItemId)
                @php
                    $selectedItem = $this->getSelectedItem();
                @endphp
                <div class="bg-ide-editor p-3 rounded border border-ide font-mono text-[9px] leading-tight space-y-2 text-left">
                    <div class="flex items-center justify-between text-[#858585] border-b border-ide/50 pb-1.5 mb-1.5">
                        <span class="font-bold text-[#ce9178]">transaction_payload.json</span>
                        <span class="text-[8px] text-slate-600 font-bold">STATE: COMPILED</span>
                    </div>
                    <div class="space-y-1 text-slate-300 overflow-x-auto">
                        <div><span class="text-[#d4d4d4]">{</span></div>
                        <div class="pl-4">
                            <span class="syntax-keyword">"product_slug"</span>: <span class="syntax-string">"{{ $product->slug }}"</span>,
                        </div>
                        <div class="pl-4">
                            <span class="syntax-keyword">"user_id"</span>: <span class="syntax-string">"{{ $userId ?: 'null' }}"</span>,
                        </div>
                        @if($product->placeholder_zone)
                            <div class="pl-4">
                                <span class="syntax-keyword">"zone_id"</span>: <span class="syntax-string">"{{ $zoneId ?: 'null' }}"</span>,
                            </div>
                        @endif
                        <div class="pl-4">
                            <span class="syntax-keyword">"item_sku"</span>: <span class="syntax-string">"{{ $selectedItem ? $selectedItem->sku : 'null' }}"</span>,
                        </div>
                        <div class="pl-4">
                            <span class="syntax-keyword">"item_qty"</span>: <span class="syntax-number">{{ $qty }}</span>,
                        </div>
                        <div class="pl-4">
                            <span class="syntax-keyword">"gateway_route"</span>: <span class="syntax-string">"{{ $paymentMethod ?: 'null' }}"</span>,
                        </div>
                        <div class="pl-4">
                            <span class="syntax-keyword">"coupon_applied"</span>: <span class="syntax-number">{{ $appliedPromo ? 'true' : 'false' }}</span>,
                        </div>
                        <div class="pl-4">
                            <span class="syntax-keyword">"calculated_fee"</span>: <span class="syntax-number">{{ $this->getFee($paymentMethod) }}</span>,
                        </div>
                        <div class="pl-4">
                            <span class="syntax-keyword">"net_payable"</span>: <span class="syntax-number">{{ $this->calculateTotal() }}</span>,
                        </div>
                        <div class="pl-4">
                            <span class="syntax-keyword">"whatsapp_node"</span>: <span class="syntax-string">"{{ $whatsapp ?: 'null' }}"</span>,
                        </div>
                        <div class="pl-4">
                            <span class="syntax-keyword">"email_node"</span>: <span class="syntax-string">"{{ $email ?: 'null' }}"</span>
                        </div>
                        <div><span class="text-[#d4d4d4]">}</span></div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Checkout & Support Actions Row -->
        <div class="flex items-center gap-2.5">
            <!-- Support Headset Shortcut Button -->
            <a 
                href="#" 
                class="w-10 h-10 rounded bg-[#252526] hover:bg-[#2d2d2e] border border-ide text-slate-400 flex items-center justify-center shrink-0 active:scale-95 transition-all shadow-md"
                title="Hubungi Admin"
            >
                <svg class="w-4 h-4 text-[#4fc1ff]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.114 5.636a9 9 0 010 12.728M16.463 8.288a5.25 5.25 0 010 7.424M6.75 8.25l4.72-4.72a.75.75 0 011.28.53v15.88a.75.75 0 01-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A9.01 9.01 0 012.25 12c0-.83.112-1.633.322-2.396C2.806 8.756 3.63 8.25 4.51 8.25H6.75z"/>
                </svg>
            </a>

            <!-- Execute Checkout Button -->
            <button 
                type="button"
                wire:click="checkout" 
                class="flex-1 h-10 rounded bg-[#0e639c] hover:bg-[#1177bb] border border-[#1177bb]/30 text-white font-bold text-xs tracking-wider active:scale-95 transition-all shadow-md flex items-center justify-center gap-2 uppercase"
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove>▶ PESAN SEKARANG</span>
                <span wire:loading class="inline-block w-3 h-3 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
            </button>
        </div>
    </div>

</div>