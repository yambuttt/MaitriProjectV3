<?php

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Transaction;
use App\Models\Product;
use App\Models\Item;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    use WithPagination, WithFileUploads;

    public string $activeTab = 'overview'; // overview, transactions, products, pricing, terminal, popup
    
    // Search & filter fields
    public string $searchTransaction = '';
    public string $filterStatus = ''; // empty = all
    public string $searchProduct = '';
    public string $searchItem = '';
    
    // Price buffer
    public array $itemPrices = [];
    
    // Shell terminal attributes
    public string $commandInput = '';
    public array $consoleLogs = [];

    // Popup settings attributes
    public bool $popupActive = false;
    public string $popupType = 'both'; // text, image, both
    public string $popupTitle = '';
    public string $popupText = '';
    public string $popupImageUrl = '';
    public $popupImageFile = null;

    // Reset pagination when searching
    public function updatingSearchTransaction()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function mount()
    {
        // Gated access node
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            return $this->redirectRoute('login', navigate: true);
        }

        // Initialize price values mapping
        $this->itemPrices = Item::pluck('price', 'id')->toArray();

        // Load popup settings
        $popupPath = storage_path('app/popup_settings.json');
        if (file_exists($popupPath)) {
            $settings = json_decode(file_get_contents($popupPath), true);
            $this->popupActive = $settings['is_active'] ?? false;
            $this->popupType = $settings['type'] ?? 'both';
            $this->popupTitle = $settings['title'] ?? 'SYSTEM_NOTIFICATION';
            $this->popupText = $settings['text'] ?? '';
            $this->popupImageUrl = $settings['image_url'] ?? '';
        }

        // Seed welcome traces
        $this->addLog('INFO', 'Admin Security Node initialized successfully.');
        $this->addLog('INFO', 'Establishing tunnel session for user: ' . Auth::user()->name);
        $this->addLog('SUCCESS', 'All systems online. Ready for command logs.');
    }

    public function logout()
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        return $this->redirectRoute('login', navigate: true);
    }

    public function changeTab(string $tab)
    {
        if (in_array($tab, ['overview', 'transactions', 'products', 'pricing', 'terminal', 'popup'])) {
            $this->activeTab = $tab;
            $this->addLog('INFO', "Switched workspace tab to: {$tab}_mode");
        }
    }

    public function savePopupSettings()
    {
        $settings = [
            'is_active' => (bool)$this->popupActive,
            'type' => $this->popupType,
            'title' => $this->popupTitle,
            'text' => $this->popupText,
            'image_url' => $this->popupImageUrl
        ];
        
        file_put_contents(storage_path('app/popup_settings.json'), json_encode($settings, JSON_PRETTY_PRINT));
        
        $this->addLog('SUCCESS', 'SYSTEM POPUP: Settings persisted successfully.');
        session()->flash('popup_saved', 'Popup settings saved successfully!');
    }

    public function updatedPopupImageFile()
    {
        $this->validate([
            'popupImageFile' => 'image|max:4096', // 4MB Max
        ]);

        $fileName = 'popup_banner_' . time() . '.' . $this->popupImageFile->getClientOriginalExtension();
        
        $destinationDir = public_path('images');
        if (!file_exists($destinationDir)) {
            mkdir($destinationDir, 0755, true);
        }

        $destinationPath = $destinationDir . '/' . $fileName;
        copy($this->popupImageFile->getRealPath(), $destinationPath);
        chmod($destinationPath, 0644); // Make it world-readable for cPanel Apache
        
        $this->popupImageUrl = '/images/' . $fileName;
        $this->popupImageFile = null; // Clear state

        $this->addLog('SUCCESS', 'SYSTEM: Popup image uploaded and stored at: ' . $this->popupImageUrl);
    }

    public function updateTransactionStatus(int $id, string $status)
    {
        $transaction = Transaction::find($id);
        if ($transaction) {
            $oldStatus = $transaction->status;
            $transaction->update(['status' => $status]);
            $this->addLog('SUCCESS', "DB UPDATE: invoice '{$transaction->invoice_id}' status altered from '{$oldStatus}' to '{$status}'");
        } else {
            $this->addLog('ERROR', "DB ACTION FAIL: Transaction record ID {$id} not found.");
        }
    }

    public function toggleProductPopular(int $id)
    {
        $product = Product::find($id);
        if ($product) {
            $newStatus = !$product->is_popular;
            $product->update(['is_popular' => $newStatus]);
            $statusStr = $newStatus ? 'POPULAR_ENABLED' : 'POPULAR_DISABLED';
            $this->addLog('SUCCESS', "DB UPDATE: product '{$product->slug}' is_popular updated to: {$statusStr}");
        } else {
            $this->addLog('ERROR', "DB ACTION FAIL: Product record ID {$id} not found.");
        }
    }

    public function updateItemPrice(int $id)
    {
        $item = Item::find($id);
        if ($item) {
            $newPrice = $this->itemPrices[$id] ?? 0;
            $oldPrice = $item->price;
            $item->update(['price' => $newPrice]);
            $this->addLog('SUCCESS', "DB UPDATE: item pricing node '{$item->sku}' updated to Rp " . number_format($newPrice, 0, ',', '.'));
        } else {
            $this->addLog('ERROR', "DB ACTION FAIL: Item record ID {$id} not found.");
        }
    }

    public function executeTerminalCommand()
    {
        $cmd = trim($this->commandInput);
        if (empty($cmd)) return;

        $this->addLog('CMD', "$ " . $cmd);
        $this->commandInput = '';

        $parts = explode(' ', $cmd);
        $action = strtolower($parts[0]);

        switch ($action) {
            case 'help':
                $this->addLog('HELP', "Available terminal commands:");
                $this->addLog('HELP', "  - db:status         Get summary table row counts");
                $this->addLog('HELP', "  - seed:mock         Create a random pending transaction");
                $this->addLog('HELP', "  - clear:logs        Purge log cache console");
                $this->addLog('HELP', "  - sys:info          Display connection node telemetry");
                break;

            case 'db:status':
                $users = \App\Models\User::count();
                $products = Product::count();
                $items = Item::count();
                $transactions = Transaction::count();
                $this->addLog('INFO', "DATABASE METRICS: [users: {$users}] [products: {$products}] [items: {$items}] [transactions: {$transactions}]");
                break;

            case 'seed:mock':
                $product = Product::inRandomOrder()->first();
                if (!$product) {
                    $this->addLog('ERROR', "Seed fail: No products found.");
                    break;
                }
                $item = Item::where('product_id', $product->id)->inRandomOrder()->first();
                if (!$item) {
                    $this->addLog('ERROR', "Seed fail: No items found for product '{$product->name}'.");
                    break;
                }
                
                $invoiceId = 'INV-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
                $trans = Transaction::create([
                    'invoice_id' => $invoiceId,
                    'product_id' => $product->id,
                    'item_id' => $item->id,
                    'user_id_input' => (string)rand(10000000, 99999999),
                    'zone_id_input' => (string)rand(1000, 9999),
                    'whatsapp_number' => '0812' . rand(10000000, 99999999),
                    'payment_method' => ['QRIS', 'GOPAY', 'SHOPEEPAY', 'VA_BCA', 'VA_MANDIRI'][rand(0, 4)],
                    'price_paid' => $item->price,
                    'status' => 'pending'
                ]);

                $this->addLog('SUCCESS', "SEEDED: New mock pending transaction '{$invoiceId}' generated for '{$product->name}' (Rp " . number_format($item->price) . ")");
                break;

            case 'clear:logs':
                $this->consoleLogs = [];
                $this->addLog('SUCCESS', "Terminal log buffer flushed.");
                break;

            case 'sys:info':
                $this->addLog('INFO', "SYS INFO: IP: 127.0.0.1 | Uptime: " . now()->diffForHumans(now()->subHours(24)) . " | Environment: LOCAL_DEV");
                break;

            default:
                $this->addLog('ERROR', "Command not recognized: '{$action}'. Type 'help' for options.");
                break;
        }
    }

    private function addLog(string $level, string $message)
    {
        $this->consoleLogs[] = [
            'timestamp' => now()->format('H:i:s'),
            'level' => $level,
            'message' => $message,
        ];

        if (count($this->consoleLogs) > 40) {
            array_shift($this->consoleLogs);
        }
    }

    // Computed / helper data retrievals
    public function getTransactions()
    {
        $query = Transaction::with(['product', 'item'])
            ->orderBy('id', 'desc');

        if (!empty($this->searchTransaction)) {
            $query->where(function($q) {
                $q->where('invoice_id', 'like', '%' . $this->searchTransaction . '%')
                  ->orWhere('user_id_input', 'like', '%' . $this->searchTransaction . '%')
                  ->orWhere('whatsapp_number', 'like', '%' . $this->searchTransaction . '%');
            });
        }

        if (!empty($this->filterStatus)) {
            $query->where('status', $this->filterStatus);
        }

        return $query->paginate(8);
    }

    public function getProducts()
    {
        $query = Product::with('category')->orderBy('id', 'asc');

        if (!empty($this->searchProduct)) {
            $query->where('name', 'like', '%' . $this->searchProduct . '%')
                  ->orWhere('developer', 'like', '%' . $this->searchProduct . '%');
        }

        return $query->get();
    }

    public function getItems()
    {
        $query = Item::with('product')->orderBy('product_id', 'asc')->orderBy('price', 'asc');

        if (!empty($this->searchItem)) {
            $query->where('name', 'like', '%' . $this->searchItem . '%')
                  ->orWhere('sku', 'like', '%' . $this->searchItem . '%')
                  ->orWhereHas('product', function($q) {
                      $q->where('name', 'like', '%' . $this->searchItem . '%');
                  });
        }

        return $query->get();
    }
};
?>

<div 
    x-data="{ mobileSidebarOpen: false }"
    class="relative z-10 flex flex-col gap-6 select-none font-mono"
>
    <!-- Component CSS styling for glows and blinking cursor -->
    <style>
        @keyframes blink { 50% { opacity: 0; } }
        .terminal-cursor { animation: blink 1s step-start infinite; }
        
        .neon-glow-cyan { transition: all 0.3s ease; }
        .neon-glow-cyan:hover {
            box-shadow: 0 0 15px rgba(6, 182, 212, 0.2);
            border-color: rgba(6, 182, 212, 0.5);
            transform: translateY(-2px);
        }
        .neon-glow-emerald { transition: all 0.3s ease; }
        .neon-glow-emerald:hover {
            box-shadow: 0 0 15px rgba(16, 185, 129, 0.2);
            border-color: rgba(16, 185, 129, 0.5);
            transform: translateY(-2px);
        }
        .neon-glow-yellow { transition: all 0.3s ease; }
        .neon-glow-yellow:hover {
            box-shadow: 0 0 15px rgba(245, 158, 11, 0.2);
            border-color: rgba(245, 158, 11, 0.5);
            transform: translateY(-2px);
        }
        .neon-glow-rose { transition: all 0.3s ease; }
        .neon-glow-rose:hover {
            box-shadow: 0 0 15px rgba(244, 63, 94, 0.2);
            border-color: rgba(244, 63, 94, 0.5);
            transform: translateY(-2px);
        }
    </style>

    <!-- Mobile Sidebar Drawer Overlay (Hamburger drawer) -->
    <div 
        x-show="mobileSidebarOpen" 
        class="fixed inset-0 z-50 md:hidden flex" 
        style="display: none;"
    >
        <!-- Backdrop blur overlay -->
        <div 
            x-show="mobileSidebarOpen"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="mobileSidebarOpen = false" 
            class="fixed inset-0 bg-black/60 backdrop-blur-sm"
        ></div>

        <!-- Sidebar panel drawer content -->
        <div 
            x-show="mobileSidebarOpen"
            x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-200 transform"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
            class="relative flex flex-col w-64 max-w-xs bg-[#181818] border-r border-ide h-full p-5 text-slate-300 shadow-2xl"
        >
            <!-- Header inside drawer -->
            <div class="flex items-center justify-between pb-4 border-b border-ide mb-4 select-none">
                <span class="font-bold text-[#007acc] text-xs font-mono">&lt;MAITRI_CONSOLE/&gt;</span>
                <button 
                    @click="mobileSidebarOpen = false"
                    class="text-slate-500 hover:text-white font-bold text-sm"
                >
                    ×
                </button>
            </div>

            <!-- Explorer directory links inside drawer -->
            <div class="text-xs space-y-4 leading-relaxed font-mono select-none">
                <div>
                    <div class="flex items-center gap-1.5 text-slate-400 font-bold">
                        <span>▼</span>
                        <span>maitri-project</span>
                    </div>
                    
                    <div class="pl-4 mt-2 space-y-2 flex flex-col">
                        <button 
                            wire:click="changeTab('overview')"
                            @click="mobileSidebarOpen = false"
                            class="flex items-center gap-2 py-1.5 w-full text-left transition-all duration-150 {{ $activeTab === 'overview' ? 'text-amber-400 font-bold bg-[#1e1e1e] px-2.5 rounded-sm' : 'text-slate-400 hover:text-slate-200' }}"
                        >
                            <span class="text-amber-500 font-bold">{}</span>
                            <span>overview.json</span>
                        </button>

                        <button 
                            wire:click="changeTab('transactions')"
                            @click="mobileSidebarOpen = false"
                            class="flex items-center gap-2 py-1.5 w-full text-left transition-all duration-150 {{ $activeTab === 'transactions' ? 'text-sky-400 font-bold bg-[#1e1e1e] px-2.5 rounded-sm' : 'text-slate-400 hover:text-slate-200' }}"
                        >
                            <span class="text-sky-400 font-bold">go</span>
                            <span>transactions_db.go</span>
                        </button>

                        <button 
                            wire:click="changeTab('products')"
                            @click="mobileSidebarOpen = false"
                            class="flex items-center gap-2 py-1.5 w-full text-left transition-all duration-150 {{ $activeTab === 'products' ? 'text-[#e06c75] font-bold bg-[#1e1e1e] px-2.5 rounded-sm' : 'text-slate-400 hover:text-slate-200' }}"
                        >
                            <span class="text-[#e06c75] font-bold">rs</span>
                            <span>products_manager.rs</span>
                        </button>

                        <button 
                            wire:click="changeTab('pricing')"
                            @click="mobileSidebarOpen = false"
                            class="flex items-center gap-2 py-1.5 w-full text-left transition-all duration-150 {{ $activeTab === 'pricing' ? 'text-emerald-400 font-bold bg-[#1e1e1e] px-2.5 rounded-sm' : 'text-slate-400 hover:text-slate-200' }}"
                        >
                            <span class="text-emerald-400 font-bold">py</span>
                            <span>items_pricing.py</span>
                        </button>

                        <button 
                            wire:click="changeTab('terminal')"
                            @click="mobileSidebarOpen = false"
                            class="flex items-center gap-2 py-1.5 w-full text-left transition-all duration-150 {{ $activeTab === 'terminal' ? 'text-yellow-400 font-bold bg-[#1e1e1e] px-2.5 rounded-sm' : 'text-slate-400 hover:text-slate-200' }}"
                        >
                            <span class="text-yellow-500 font-bold">sh</span>
                            <span>sys_terminal.sh</span>
                        </button>

                        <button 
                            wire:click="changeTab('popup')"
                            @click="mobileSidebarOpen = false"
                            class="flex items-center gap-2 py-1.5 w-full text-left transition-all duration-150 {{ $activeTab === 'popup' ? 'text-cyan-400 font-bold bg-[#1e1e1e] px-2.5 rounded-sm' : 'text-slate-400 hover:text-slate-200' }}"
                        >
                            <span class="text-cyan-400 font-bold">md</span>
                            <span>popup_banner.md</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="relative z-10 flex flex-col gap-6 select-none font-mono">
    
    <!-- Top Level IDE Info Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between border border-ide bg-[#181818] p-4 rounded-lg gap-4 shadow-md">
        <div class="flex items-center gap-3">
            <span class="text-cyan-400 font-bold font-mono">&lt;</span>
            <div>
                <span class="font-space font-bold text-white text-sm">ADMIN_CONTROL_CENTER</span>
                <div class="text-[9px] text-slate-500 font-mono mt-0.5">// Secure server-side administrative terminal.</div>
            </div>
            <span class="text-cyan-400 font-bold font-mono">/&gt;</span>
        </div>
        <div class="flex items-center gap-2 text-[10px] flex-wrap">
            <span class="px-2.5 py-1 bg-[#1e1e1e] border border-ide rounded text-yellow-500 font-bold shrink-0">
                ROLE: ADMIN_ROOT
            </span>
            <span class="px-2.5 py-1 bg-cyan-950/40 border border-cyan-800/40 rounded text-cyan-400 font-bold shrink-0">
                NODE_TUNNEL: SECURE
            </span>
            <a href="/" wire:navigate class="px-2.5 py-1 bg-[#252526] hover:bg-[#2d2d2e] border border-ide rounded text-slate-300 font-bold font-mono hover:text-white transition-colors shrink-0">
                // PUBLIC_STORE
            </a>
            <button wire:click="logout" class="px-2.5 py-1 bg-rose-950/40 hover:bg-rose-900/40 border border-rose-800/40 rounded text-rose-400 font-bold font-mono transition-colors shrink-0">
                // LOGOUT
            </button>
        </div>
    </div>

    <!-- Main Workspace Layout Grid -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-6 items-start">
        
        <!-- Sidebar Explorer Drawer (Left Column: Desktop only) -->
        <div class="hidden md:block col-span-1 border border-ide bg-[#181818] rounded-lg overflow-hidden shadow-md">
            <div class="h-9 bg-[#1e1e1e] border-b border-ide px-4 flex items-center justify-between text-[10px] text-slate-500 font-bold">
                <span>EXPLORER: WORKSPACE</span>
                <span class="text-[9px] text-slate-600 font-normal">MaitriProject</span>
            </div>
            <div class="p-3 text-xs text-slate-400 space-y-3 font-mono leading-relaxed">
                <!-- Maitri Project Folder Node -->
                <div>
                    <div class="flex items-center gap-1.5 text-slate-300 font-bold">
                        <span>▼</span>
                        <span class="text-[#007acc] font-bold">&lt;MAITRI_PROJECT/&gt;</span>
                    </div>
                    
                    <div class="pl-4 mt-2 space-y-1">
                        <div class="flex items-center gap-1.5 text-slate-400">
                            <span>▼</span>
                            <span>panels</span>
                        </div>
                        
                        <!-- Panel File Links -->
                        <div class="pl-4 mt-1 space-y-1 flex flex-col">
                            <button 
                                wire:click="changeTab('overview')"
                                class="flex items-center gap-2 py-1 w-full text-left transition-colors {{ $activeTab === 'overview' ? 'text-amber-400 font-bold bg-[#1e1e1e] px-2 rounded-sm' : 'hover:text-slate-200' }}"
                            >
                                <span class="text-amber-500">{}</span>
                                <span>overview.json</span>
                            </button>

                            <button 
                                wire:click="changeTab('transactions')"
                                class="flex items-center gap-2 py-1 w-full text-left transition-colors {{ $activeTab === 'transactions' ? 'text-sky-400 font-bold bg-[#1e1e1e] px-2 rounded-sm' : 'hover:text-slate-200' }}"
                            >
                                <span class="text-sky-400">go</span>
                                <span>transactions_db.go</span>
                            </button>

                            <button 
                                wire:click="changeTab('products')"
                                class="flex items-center gap-2 py-1 w-full text-left transition-colors {{ $activeTab === 'products' ? 'text-[#e06c75] font-bold bg-[#1e1e1e] px-2 rounded-sm' : 'hover:text-slate-200' }}"
                            >
                                <span class="text-[#e06c75]">rs</span>
                                <span>products_manager.rs</span>
                            </button>

                            <button 
                                wire:click="changeTab('pricing')"
                                class="flex items-center gap-2 py-1 w-full text-left transition-colors {{ $activeTab === 'pricing' ? 'text-emerald-400 font-bold bg-[#1e1e1e] px-2 rounded-sm' : 'hover:text-slate-200' }}"
                            >
                                <span class="text-emerald-400">py</span>
                                <span>items_pricing.py</span>
                            </button>

                            <button 
                                wire:click="changeTab('terminal')"
                                class="flex items-center gap-2 py-1 w-full text-left transition-colors {{ $activeTab === 'terminal' ? 'text-yellow-400 font-bold bg-[#1e1e1e] px-2 rounded-sm' : 'hover:text-slate-200' }}"
                            >
                                <span class="text-yellow-500">sh</span>
                                <span>sys_terminal.sh</span>
                            </button>

                            <button 
                                wire:click="changeTab('popup')"
                                class="flex items-center gap-2 py-1 w-full text-left transition-colors {{ $activeTab === 'popup' ? 'text-cyan-400 font-bold bg-[#1e1e1e] px-2 rounded-sm' : 'hover:text-slate-200' }}"
                            >
                                <span class="text-cyan-400">md</span>
                                <span>popup_banner.md</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Editor Window Area (Right Columns) -->
        <div class="col-span-1 md:col-span-4 border border-ide bg-[#1e1e1e] rounded-lg overflow-hidden shadow-2xl transition-all duration-300">
            
            <!-- Window Tabs (Responsive header tabs) -->
            <div class="h-9 bg-[#1e1e1e] border-b border-ide px-2 sm:px-4 flex items-center justify-between text-[11px] text-[#858585] select-none">
                
                <!-- Scrollable tabs navigation -->
                <div class="flex items-center gap-1 overflow-x-auto scrollbar-none self-stretch pr-4">
                    <!-- Mobile Hamburger Sidebar Toggle -->
                    <button 
                        @click="mobileSidebarOpen = true"
                        class="md:hidden mr-2.5 p-1 rounded bg-[#252526] hover:bg-[#2d2d2e] border border-ide text-slate-400 hover:text-white transition-all shrink-0 active:scale-95 duration-150"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>

                    <!-- MacOS Dots (visible on desktop) -->
                    <div class="hidden sm:flex gap-1 mr-3 shrink-0">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#ff5f56]/80"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-[#ffbd2e]/80"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-[#27c93f]/80"></span>
                    </div>

                    <!-- overview.json tab -->
                    <button 
                        wire:click="changeTab('overview')"
                        class="flex items-center gap-1.5 px-3 py-2 bg-ide-editor border-r border-ide transition-all duration-150 shrink-0 h-full {{ $activeTab === 'overview' ? 'border-t-2 border-t-[#007acc] text-amber-500 font-bold bg-[#181818]' : 'text-slate-500 hover:text-slate-300' }}"
                    >
                        <span>{}</span>
                        <span class="text-[#cccccc]">overview.json</span>
                    </button>

                    <!-- transactions_db.go tab -->
                    <button 
                        wire:click="changeTab('transactions')"
                        class="flex items-center gap-1.5 px-3 py-2 bg-ide-editor border-r border-ide transition-all duration-150 shrink-0 h-full {{ $activeTab === 'transactions' ? 'border-t-2 border-t-[#007acc] text-sky-400 font-bold bg-[#181818]' : 'text-slate-500 hover:text-slate-300' }}"
                    >
                        <span>go</span>
                        <span class="text-[#cccccc]">transactions.go</span>
                    </button>

                    <!-- products_manager.rs tab -->
                    <button 
                        wire:click="changeTab('products')"
                        class="flex items-center gap-1.5 px-3 py-2 bg-ide-editor border-r border-ide transition-all duration-150 shrink-0 h-full {{ $activeTab === 'products' ? 'border-t-2 border-t-[#007acc] text-[#e06c75] font-bold bg-[#181818]' : 'text-slate-500 hover:text-slate-300' }}"
                    >
                        <span>rs</span>
                        <span class="text-[#cccccc]">products.rs</span>
                    </button>

                    <!-- items_pricing.py tab -->
                    <button 
                        wire:click="changeTab('pricing')"
                        class="flex items-center gap-1.5 px-3 py-2 bg-ide-editor border-r border-ide transition-all duration-150 shrink-0 h-full {{ $activeTab === 'pricing' ? 'border-t-2 border-t-[#007acc] text-emerald-400 font-bold bg-[#181818]' : 'text-slate-500 hover:text-slate-300' }}"
                    >
                        <span>py</span>
                        <span class="text-[#cccccc]">pricing.py</span>
                    </button>

                    <!-- sys_terminal.sh tab -->
                    <button 
                        wire:click="changeTab('terminal')"
                        class="flex items-center gap-1.5 px-3 py-2 bg-ide-editor border-r border-ide transition-all duration-150 shrink-0 h-full {{ $activeTab === 'terminal' ? 'border-t-2 border-t-[#007acc] text-yellow-400 font-bold bg-[#181818]' : 'text-slate-500 hover:text-slate-300' }}"
                    >
                        <span>sh</span>
                        <span class="text-[#cccccc]">terminal.sh</span>
                    </button>

                    <!-- popup_banner.md tab -->
                    <button 
                        wire:click="changeTab('popup')"
                        class="flex items-center gap-1.5 px-3 py-2 bg-ide-editor border-r border-ide transition-all duration-150 shrink-0 h-full {{ $activeTab === 'popup' ? 'border-t-2 border-t-[#007acc] text-cyan-400 font-bold bg-[#181818]' : 'text-slate-500 hover:text-slate-300' }}"
                    >
                        <span>md</span>
                        <span class="text-[#cccccc]">popup_banner.md</span>
                    </button>
                </div>
                
                <span class="text-[9px] text-[#4ec9b0] font-bold hidden md:inline select-none">BUFFER: ACTIVE_DUMP</span>
            </div>

            <!-- Workspace Main Content Panel -->
            <div class="p-4 sm:p-6 bg-[#1e1e1e] min-h-[440px]">
                
                <!-- View 1: Overview Panel -->
                @if($activeTab === 'overview')
                    <div class="space-y-6">
                        <!-- Server Status & Stats Row -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <!-- Revenue -->
                            <div class="bg-[#181818] border border-ide p-4 rounded flex flex-col justify-between shadow-md relative overflow-hidden group neon-glow-emerald cursor-pointer">
                                <div class="absolute right-3 top-3 opacity-5 text-emerald-400 group-hover:opacity-25 group-hover:scale-110 transition-all duration-300">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <span class="text-[9px] font-bold text-slate-500 tracking-wider">// metric_revenue</span>
                                <div class="mt-2.5">
                                    <div class="text-[9px] text-slate-400 font-mono">"total_revenue":</div>
                                    <div class="text-lg font-bold text-emerald-400">Rp {{ number_format(Transaction::where('status', 'completed')->sum('price_paid'), 0, ',', '.') }}</div>
                                </div>
                            </div>

                            <!-- Successful Transactions -->
                            <div class="bg-[#181818] border border-ide p-4 rounded flex flex-col justify-between shadow-md relative overflow-hidden group neon-glow-cyan cursor-pointer">
                                <div class="absolute right-3 top-3 opacity-5 text-cyan-400 group-hover:opacity-25 group-hover:scale-110 transition-all duration-300">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <span class="text-[9px] font-bold text-slate-500 tracking-wider">// metric_success_rate</span>
                                <div class="mt-2.5">
                                    <div class="text-[9px] text-slate-400 font-mono">"successful_checkouts":</div>
                                    <div class="text-lg font-bold text-cyan-400">{{ Transaction::where('status', 'completed')->count() }}</div>
                                </div>
                            </div>

                            <!-- Pending Orders -->
                            <div class="bg-[#181818] border border-ide p-4 rounded flex flex-col justify-between shadow-md relative overflow-hidden group neon-glow-yellow cursor-pointer">
                                <div class="absolute right-3 top-3 opacity-5 text-yellow-500 group-hover:opacity-25 group-hover:scale-110 transition-all duration-300">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <span class="text-[9px] font-bold text-slate-500 tracking-wider">// metric_pending_orders</span>
                                <div class="mt-2.5">
                                    <div class="text-[9px] text-slate-400 font-mono">"pending_tasks":</div>
                                    <div class="text-lg font-bold text-yellow-500">{{ Transaction::where('status', 'pending')->count() }}</div>
                                </div>
                            </div>

                            <!-- active_tunnel_uptime -->
                            <div class="bg-[#181818] border border-ide p-4 rounded flex flex-col justify-between shadow-md relative overflow-hidden group neon-glow-rose cursor-pointer">
                                <div class="absolute right-3 top-3 opacity-5 text-rose-500 group-hover:opacity-25 group-hover:scale-110 transition-all duration-300">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                </div>
                                <span class="text-[9px] font-bold text-slate-500 tracking-wider">// metric_uptime</span>
                                <div class="mt-2.5">
                                    <div class="text-[9px] text-slate-400 font-mono">"active_tunnel_uptime":</div>
                                    <div class="text-lg font-bold text-rose-400" x-data="{ seconds: 84300 }" x-init="setInterval(() => seconds++, 1000)">
                                        <span x-text="Math.floor(seconds/3600) + 'h ' + Math.floor((seconds%3600)/60) + 'm ' + (seconds%60) + 's'"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Core JSON telemetry and Live Audit log output -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Telemetry dump -->
                            <div class="bg-[#181818] border border-ide rounded p-5 font-mono text-xs shadow-md">
                                <div class="flex items-center justify-between border-b border-ide pb-3 mb-4 text-[10px]">
                                    <span class="text-slate-400 font-bold">// SYSTEM_METRICS_DUMP.JSON</span>
                                    <span class="text-[#4ec9b0] font-bold">STATUS: ONLINE</span>
                                </div>
                                <div class="space-y-1 text-slate-300 select-all overflow-x-auto whitespace-nowrap">
                                    <div><span class="text-[#d4d4d4]">{</span></div>
                                    <div class="pl-4"><span class="syntax-keyword">"server_name"</span>: <span class="syntax-string">"maitri_dispatch_node"</span>,</div>
                                    <div class="pl-4"><span class="syntax-keyword">"active_connections"</span>: <span class="syntax-number">12</span>,</div>
                                    <div class="pl-4"><span class="syntax-keyword">"db_driver"</span>: <span class="syntax-string">"sqlite"</span>,</div>
                                    <div class="pl-4"><span class="syntax-keyword">"environment"</span>: <span class="syntax-string">"production"</span>,</div>
                                    <div class="pl-4"><span class="syntax-keyword">"auth_role"</span>: <span class="syntax-string">"admin"</span>,</div>
                                    <div class="pl-4"><span class="syntax-keyword">"ssl_status"</span>: <span class="syntax-string">"secured_node_active"</span>,</div>
                                    <div class="pl-4"><span class="syntax-keyword">"telemetry"</span>: {</div>
                                    <div class="pl-8"><span class="syntax-keyword">"cpu_usage"</span>: <span class="syntax-string">"1.2%"</span>,</div>
                                    <div class="pl-8"><span class="syntax-keyword">"ram_usage"</span>: <span class="syntax-string">"14.8MB"</span>,</div>
                                    <div class="pl-8"><span class="syntax-keyword">"cache_hits"</span>: <span class="syntax-number">9844</span></div>
                                    <div class="pl-4">}</div>
                                    <div><span class="text-[#d4d4d4]">}</span></div>
                                </div>
                            </div>

                            <!-- Live logs panel -->
                            <div class="bg-[#181818] border border-ide rounded p-5 font-mono text-xs flex flex-col justify-between shadow-md">
                                <div>
                                    <div class="flex items-center justify-between border-b border-ide pb-3 mb-4 text-[10px]">
                                        <span class="text-slate-400 font-bold">// DIAGNOSTICS_STREAM</span>
                                        <span class="text-[#ffbd2e] font-bold animate-pulse">// STREAMING</span>
                                    </div>
                                    <div class="bg-ide-editor border border-ide rounded p-3 text-[10px] space-y-1.5 text-slate-500 overflow-y-auto max-h-[190px]">
                                        @foreach(array_reverse($consoleLogs) as $log)
                                            <div class="leading-normal">
                                                <span class="text-slate-600">[{{ $log['timestamp'] }}]</span>
                                                @if($log['level'] === 'SUCCESS')
                                                    <span class="text-emerald-500 font-bold">[SUCCESS]</span>
                                                @elseif($log['level'] === 'ERROR')
                                                    <span class="text-rose-500 font-bold">[ERROR]</span>
                                                @elseif($log['level'] === 'CMD')
                                                    <span class="text-amber-500 font-bold">[CMD]</span>
                                                @else
                                                    <span class="text-cyan-400 font-bold">[{{ $log['level'] }}]</span>
                                                @endif
                                                <span class="text-slate-300 ml-1 select-text">{{ $log['message'] }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="pt-3 text-[9px] text-slate-600 font-mono">// Stream connection active on socket port 8000.</div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- View 2: Transactions Table -->
                @if($activeTab === 'transactions')
                    <div class="space-y-4">
                        <div class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-center justify-between border-b border-ide pb-4">
                            <!-- Title -->
                            <div class="text-[10px] font-bold text-slate-400">// FILE: transactions_db.go (READ_WRITE)</div>
                            
                            <!-- Search and filter widgets -->
                            <div class="flex flex-wrap gap-2.5 items-center">
                                <input 
                                    wire:model.live="searchTransaction" 
                                    type="text" 
                                    placeholder="Search invoice / user ID..." 
                                    class="h-8 pl-3 pr-8 rounded bg-[#181818] border border-ide text-xs placeholder-slate-700 text-white focus:outline-none focus:border-[#007acc] transition-all min-w-[200px]"
                                >
                                <select 
                                    wire:model.live="filterStatus"
                                    class="h-8 px-2 rounded bg-[#181818] border border-ide text-xs text-slate-400 focus:outline-none focus:border-[#007acc] transition-all"
                                >
                                    <option value="">Status: ALL</option>
                                    <option value="pending">PENDING</option>
                                    <option value="completed">COMPLETED</option>
                                    <option value="failed">FAILED</option>
                                </select>
                            </div>
                        </div>

                        <!-- Responsive transaction table wrapper -->
                        <div class="overflow-x-auto rounded border border-ide bg-[#181818]">
                            <table class="w-full text-left border-collapse text-[10px] font-mono">
                                <thead>
                                    <tr class="bg-[#202021] text-slate-500 border-b border-ide font-bold uppercase text-[9px]">
                                        <th class="p-3">Invoice</th>
                                        <th class="p-3">Product</th>
                                        <th class="p-3">Price</th>
                                        <th class="p-3">Identifiers</th>
                                        <th class="p-3">Contact</th>
                                        <th class="p-3 text-center">Status</th>
                                        <th class="p-3 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-ide/60 text-slate-300">
                                    @forelse($this->getTransactions() as $tx)
                                        <tr class="hover:bg-ide-editor/50 transition-colors">
                                            <td class="p-3 font-bold text-slate-200">{{ $tx->invoice_id }}</td>
                                            <td class="p-3">
                                                <div class="flex items-center gap-1.5">
                                                    @if($tx->product->logo)
                                                        <img src="{{ $tx->product->logo }}" class="w-4 h-4 rounded bg-slate-900 object-cover border border-white/5">
                                                    @endif
                                                    <span>{{ $tx->product->name }}</span>
                                                </div>
                                            </td>
                                            <td class="p-3 text-emerald-400 font-semibold">Rp {{ number_format($tx->price_paid, 0, ',', '.') }}</td>
                                            <td class="p-3">
                                                <span class="text-slate-500">ID:</span> {{ $tx->user_id_input }}
                                                @if($tx->zone_id_input)
                                                    <span class="text-slate-500 ml-1">Zone:</span> {{ $tx->zone_id_input }}
                                                @endif
                                            </td>
                                            <td class="p-3 text-slate-400">{{ $tx->whatsapp_number }}</td>
                                            <td class="p-3 text-center">
                                                @if($tx->status === 'completed')
                                                    <span class="px-2 py-0.5 rounded bg-emerald-950/40 border border-emerald-800/40 text-emerald-400 font-bold uppercase text-[9px]">COMPLETED</span>
                                                @elseif($tx->status === 'failed')
                                                    <span class="px-2 py-0.5 rounded bg-rose-950/40 border border-rose-800/40 text-rose-400 font-bold uppercase text-[9px]">FAILED</span>
                                                @else
                                                    <span class="px-2 py-0.5 rounded bg-amber-950/40 border border-amber-800/40 text-amber-400 font-bold uppercase text-[9px] animate-pulse">PENDING</span>
                                                @endif
                                            </td>
                                            <td class="p-3 text-right">
                                                <div class="flex items-center justify-end gap-1">
                                                    @if($tx->status !== 'completed')
                                                        <button 
                                                            wire:click="updateTransactionStatus({{ $tx->id }}, 'completed')"
                                                            class="px-1.5 py-0.5 rounded bg-emerald-900/60 hover:bg-emerald-700 border border-emerald-600/40 text-white font-bold text-[9px] active:scale-95 transition-all"
                                                        >
                                                            ✔
                                                        </button>
                                                    @endif
                                                    @if($tx->status !== 'failed')
                                                        <button 
                                                            wire:click="updateTransactionStatus({{ $tx->id }}, 'failed')"
                                                            class="px-1.5 py-0.5 rounded bg-rose-900/60 hover:bg-rose-700 border border-rose-600/40 text-white font-bold text-[9px] active:scale-95 transition-all"
                                                        >
                                                            ✖
                                                        </button>
                                                    @endif
                                                    @if($tx->status !== 'pending')
                                                        <button 
                                                            wire:click="updateTransactionStatus({{ $tx->id }}, 'pending')"
                                                            class="px-1.5 py-0.5 rounded bg-amber-900/60 hover:bg-amber-700 border border-amber-600/40 text-white font-bold text-[9px] active:scale-95 transition-all"
                                                        >
                                                            ↺
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="p-8 text-center text-slate-500 select-none">// No records found matching current query parameters.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Custom terminal-style Pagination links -->
                        {{ $this->getTransactions()->links() }}
                        <div class="flex justify-between items-center mt-4 border-t border-ide pt-4">
                            <div class="text-[10px] text-slate-500 font-mono">
                                Showing Page {{ $this->getTransactions()->currentPage() }} of {{ $this->getTransactions()->lastPage() }} (Total: {{ $this->getTransactions()->total() }})
                            </div>
                            <div class="flex items-center gap-2">
                                @if(!$this->getTransactions()->onFirstPage())
                                    <button wire:click="previousPage" class="px-2 py-1 rounded bg-[#252526] hover:bg-[#2d2d2e] border border-ide text-[10px] text-slate-300 font-mono transition-all font-bold select-none">&lt; PREV_PAGE</button>
                                @else
                                    <span class="px-2 py-1 rounded bg-[#181818] border border-ide/50 text-[10px] text-slate-600 font-mono select-none">PREV_PAGE</span>
                                @endif

                                @if($this->getTransactions()->hasMorePages())
                                    <button wire:click="nextPage" class="px-2 py-1 rounded bg-[#252526] hover:bg-[#2d2d2e] border border-ide text-[10px] text-slate-300 font-mono transition-all font-bold select-none">NEXT_PAGE &gt;</button>
                                @else
                                    <span class="px-2 py-1 rounded bg-[#181818] border border-ide/50 text-[10px] text-slate-600 font-mono select-none">NEXT_PAGE</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <!-- View 3: Products Configuration -->
                @if($activeTab === 'products')
                    <div class="space-y-4">
                        <div class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-center justify-between border-b border-ide pb-4">
                            <div class="text-[10px] font-bold text-slate-400">// FILE: products_manager.rs (READ_WRITE)</div>
                            <input 
                                wire:model.live="searchProduct" 
                                type="text" 
                                placeholder="Search products by name..." 
                                class="h-8 pl-3 pr-8 rounded bg-[#181818] border border-ide text-xs placeholder-slate-700 text-white focus:outline-none focus:border-[#007acc] transition-all min-w-[220px]"
                            >
                        </div>

                        <!-- Product manager list -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            @forelse($this->getProducts() as $p)
                                <div class="bg-[#181818] border border-ide rounded p-4 flex flex-col justify-between shadow-md group hover:border-[#007acc]/40 transition-all">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            @if($p->logo)
                                                <img src="{{ $p->logo }}" class="w-8 h-8 rounded bg-slate-900 object-cover border border-white/5 shrink-0">
                                            @endif
                                            <div class="min-w-0">
                                                <div class="font-bold text-slate-200 text-xs truncate">{{ $p->name }}</div>
                                                <div class="text-[9px] text-slate-500 font-mono truncate">dev: {{ $p->developer }}</div>
                                            </div>
                                        </div>
                                        <span class="text-[8px] bg-cyan-950/40 border border-cyan-800/40 text-cyan-400 font-bold px-1.5 py-0.5 rounded font-mono uppercase tracking-wider">{{ $p->category->name }}</span>
                                    </div>

                                    <div class="border-t border-ide/50 pt-3 mt-4 flex items-center justify-between">
                                        <span class="text-[9px] text-slate-500 font-mono">// parameter: is_popular</span>
                                        <button 
                                            wire:click="toggleProductPopular({{ $p->id }})"
                                            class="px-2.5 py-1 rounded text-[10px] font-bold border transition-all font-mono active:scale-95 {{ $p->is_popular ? 'bg-amber-900/40 border-amber-600/40 text-amber-400' : 'bg-[#252526] border-ide text-slate-400 hover:text-slate-200' }}"
                                        >
                                            {{ $p->is_popular ? '★ POPULAR' : '☆ STABLE' }}
                                        </button>
                                    </div>
                                </div>
                            @empty
                                <div class="col-span-full py-8 text-center text-slate-500 select-none">// No products found matching current query parameters.</div>
                            @endforelse
                        </div>
                    </div>
                @endif

                <!-- View 4: Item Pricing Console -->
                @if($activeTab === 'pricing')
                    <div class="space-y-4">
                        <div class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-center justify-between border-b border-ide pb-4">
                            <div class="text-[10px] font-bold text-slate-400">// FILE: items_pricing.py (READ_WRITE)</div>
                            <input 
                                wire:model.live="searchItem" 
                                type="text" 
                                placeholder="Search by name, SKU, or game..." 
                                class="h-8 pl-3 pr-8 rounded bg-[#181818] border border-ide text-xs placeholder-slate-700 text-white focus:outline-none focus:border-[#007acc] transition-all min-w-[220px]"
                            >
                        </div>

                        <!-- Item table with inline inputs -->
                        <div class="overflow-x-auto rounded border border-ide bg-[#181818]">
                            <table class="w-full text-left border-collapse text-[10px] font-mono">
                                <thead>
                                    <tr class="bg-[#202021] text-slate-500 border-b border-ide font-bold uppercase text-[9px]">
                                        <th class="p-3">Product Name</th>
                                        <th class="p-3">Item Name</th>
                                        <th class="p-3">SKU Node</th>
                                        <th class="p-3">Edit Value (Rp)</th>
                                        <th class="p-3 text-right">Commit</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-ide/60 text-slate-300">
                                    @forelse($this->getItems() as $item)
                                        <tr class="hover:bg-ide-editor/50 transition-colors">
                                            <td class="p-3 text-slate-400 font-bold">{{ $item->product->name }}</td>
                                            <td class="p-3">{{ $item->name }}</td>
                                            <td class="p-3 text-slate-500 font-mono">{{ $item->sku }}</td>
                                            <td class="p-3">
                                                <input 
                                                    wire:model.lazy="itemPrices.{{ $item->id }}" 
                                                    type="number" 
                                                    class="h-7 w-28 px-2 rounded bg-ide-editor border border-ide text-[10px] text-white focus:outline-none focus:border-[#007acc] text-right font-mono"
                                                >
                                            </td>
                                            <td class="p-3 text-right">
                                                <button 
                                                    wire:click="updateItemPrice({{ $item->id }})"
                                                    class="px-2.5 py-1 rounded bg-[#0e639c] hover:bg-[#1177bb] text-white border border-[#1177bb]/30 font-bold text-[9px] transition-all active:scale-95 font-mono"
                                                >
                                                    COMMIT_PRICE
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="p-8 text-center text-slate-500 select-none">// No items found matching current query parameters.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <!-- View 5: System Shell Terminal -->
                @if($activeTab === 'terminal')
                    <div class="space-y-4">
                        <div class="border-b border-ide pb-3 text-[10px] font-bold text-slate-400">// FILE: sys_terminal.sh (INTERACTIVE_SHELL)</div>
                        
                        <div class="bg-[#181818] border border-ide rounded p-5 flex flex-col gap-4 shadow-md font-mono text-xs">
                            <!-- Helper Macro Triggers -->
                            <div class="space-y-2">
                                <span class="text-[9px] text-slate-500 font-bold block uppercase tracking-wider">// QUICK_MACRO_TRIGGERS</span>
                                <div class="flex flex-wrap gap-2">
                                    <button 
                                        wire:click="$set('commandInput', 'help'); executeTerminalCommand()"
                                        class="px-2.5 py-1 rounded bg-[#252526] hover:bg-[#2d2d2e] border border-ide text-[10px] text-slate-300 font-bold transition-all active:scale-95"
                                    >
                                        help
                                    </button>
                                    <button 
                                        wire:click="$set('commandInput', 'db:status'); executeTerminalCommand()"
                                        class="px-2.5 py-1 rounded bg-[#252526] hover:bg-[#2d2d2e] border border-ide text-[10px] text-slate-300 font-bold transition-all active:scale-95"
                                    >
                                        db:status
                                    </button>
                                    <button 
                                        wire:click="$set('commandInput', 'seed:mock'); executeTerminalCommand()"
                                        class="px-2.5 py-1 rounded bg-[#252526] hover:bg-[#2d2d2e] border border-ide text-[10px] text-slate-300 font-bold transition-all active:scale-95"
                                    >
                                        seed:mock
                                    </button>
                                    <button 
                                        wire:click="$set('commandInput', 'sys:info'); executeTerminalCommand()"
                                        class="px-2.5 py-1 rounded bg-[#252526] hover:bg-[#2d2d2e] border border-ide text-[10px] text-slate-300 font-bold transition-all active:scale-95"
                                    >
                                        sys:info
                                    </button>
                                    <button 
                                        wire:click="$set('commandInput', 'clear:logs'); executeTerminalCommand()"
                                        class="px-2.5 py-1 rounded bg-[#252526] hover:bg-[#2d2d2e] border border-ide text-[10px] text-slate-300 font-bold transition-all active:scale-95"
                                    >
                                        clear:logs
                                    </button>
                                </div>
                            </div>

                            <!-- Screen Terminal Output -->
                            <div class="bg-ide-editor border border-ide rounded p-4 min-h-[200px] max-h-[300px] overflow-y-auto space-y-1.5 text-[10px] text-slate-400 relative overflow-hidden">
                                <!-- CRIT monitor Scan Line effect -->
                                <div class="absolute inset-0 pointer-events-none bg-gradient-to-b from-transparent via-cyan-500/5 to-transparent h-[200%] animate-[pulse_3s_infinite]"></div>
                                @foreach($consoleLogs as $log)
                                    <div>
                                        <span class="text-slate-600">[{{ $log['timestamp'] }}]</span>
                                        @if($log['level'] === 'SUCCESS')
                                            <span class="text-emerald-500 font-bold">[SUCCESS]</span>
                                        @elseif($log['level'] === 'ERROR')
                                            <span class="text-rose-500 font-bold">[ERROR]</span>
                                        @elseif($log['level'] === 'CMD')
                                            <span class="text-amber-500 font-bold">[CMD]</span>
                                        @elseif($log['level'] === 'HELP')
                                            <span class="text-yellow-500 font-bold">[HELP]</span>
                                        @else
                                            <span class="text-cyan-400 font-bold">[{{ $log['level'] }}]</span>
                                        @endif
                                        <span class="text-slate-300 ml-1">{{ $log['message'] }}</span>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Input Line Command Entry -->
                            <div class="relative">
                                <span class="absolute left-3.5 top-2.5 text-cyan-400 font-bold select-none">$</span>
                                <input 
                                    wire:model.live="commandInput"
                                    wire:keydown.enter="executeTerminalCommand"
                                    type="text" 
                                    placeholder="Type 'help' and press Enter..." 
                                    class="w-full h-9 pl-8 pr-4 rounded bg-ide-editor border border-ide text-xs placeholder-slate-700 text-[#cccccc] focus:outline-none focus:border-[#007acc] transition-all font-mono"
                                >
                            </div>
                        </div>
                    </div>
                @endif

                <!-- View 6: Popup Settings Panel -->
                @if($activeTab === 'popup')
                    <div class="space-y-6">
                        <div class="flex items-center justify-between pb-3 border-b border-ide">
                            <div class="flex items-center gap-2">
                                <span class="text-cyan-400 font-bold">#</span>
                                <h3 class="text-sm font-bold text-white uppercase tracking-wider">// POPUP_BANNER_CONFIGURATION</h3>
                            </div>
                            <span class="text-[9px] text-slate-500 font-mono">FILE: popup_banner.md</span>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
                            
                            <!-- Left Column: Settings Form -->
                            <form wire:submit.prevent="savePopupSettings" class="bg-[#181818] border border-ide p-5 rounded-lg space-y-4 shadow-md font-mono text-xs">
                                @if (session()->has('popup_saved'))
                                    <div class="p-3 bg-emerald-950/40 border border-emerald-800/40 rounded text-emerald-400 font-bold mb-3 select-none flex items-center gap-2 animate-pulse">
                                        <span>✔</span>
                                        <span>{{ session('popup_saved') }}</span>
                                    </div>
                                @endif

                                <!-- Toggle Active -->
                                <div class="space-y-1.5">
                                    <label class="text-[#569cd6] font-bold block">// const popup_active = boolean;</label>
                                    <div class="flex items-center gap-2">
                                        <button 
                                            type="button"
                                            wire:click="$set('popupActive', true)"
                                            class="px-4 py-1.5 rounded font-mono font-bold text-xs uppercase border transition-all duration-150 {{ $popupActive ? 'bg-emerald-950/80 border-emerald-500 text-emerald-400 shadow-[0_0_15px_rgba(16,185,129,0.3)] font-black' : 'bg-[#252526] border-ide text-slate-500 hover:text-slate-300' }}"
                                        >
                                            TRUE (Aktif)
                                        </button>
                                        <button 
                                            type="button"
                                            wire:click="$set('popupActive', false)"
                                            class="px-4 py-1.5 rounded font-mono font-bold text-xs uppercase border transition-all duration-150 {{ !$popupActive ? 'bg-rose-950/80 border-rose-500 text-rose-400 shadow-[0_0_15px_rgba(244,63,94,0.3)] font-black' : 'bg-[#252526] border-ide text-slate-500 hover:text-slate-300' }}"
                                        >
                                            FALSE (Nonaktif)
                                        </button>
                                    </div>
                                </div>

                                <!-- Popup Title -->
                                <div class="space-y-1.5">
                                    <label class="text-[#569cd6] font-bold block">// const popup_title = string;</label>
                                    <input 
                                        wire:model="popupTitle" 
                                        type="text" 
                                        class="w-full h-8 px-3 rounded bg-ide-editor border border-ide text-[#cccccc] focus:outline-none focus:border-[#007acc]"
                                        placeholder="SYSTEM_NOTIFICATION"
                                    >
                                </div>

                                <!-- Content Type -->
                                <div class="space-y-1.5">
                                    <label class="text-[#569cd6] font-bold block">// const content_mode = enum;</label>
                                    <select 
                                        wire:model="popupType" 
                                        class="w-full h-8 px-2 rounded bg-ide-editor border border-ide text-[#cccccc] focus:outline-none focus:border-[#007acc]"
                                    >
                                        <option value="text">text (Hanya Teks)</option>
                                        <option value="image">image (Hanya Gambar)</option>
                                        <option value="both">both (Teks & Gambar)</option>
                                    </select>
                                </div>

                                <!-- Image URL & Upload -->
                                <div class="space-y-2 border border-ide p-3 rounded bg-[#1e1e1e]/40">
                                    <label class="text-[#569cd6] font-bold block">// const banner_image = string | File;</label>
                                    
                                    <div class="flex flex-col gap-2">
                                        <div class="flex items-center gap-3">
                                            <label class="px-3 py-1.5 bg-[#252526] hover:bg-[#2d2d30] border border-ide text-[#cccccc] cursor-pointer rounded text-[11px] font-bold transition-all flex items-center gap-1.5 select-none hover:border-cyan-500/50">
                                                <span>📂 UPLOAD BANNER</span>
                                                <input 
                                                    type="file" 
                                                    wire:model="popupImageFile" 
                                                    accept="image/*" 
                                                    class="hidden"
                                                >
                                            </label>
                                            <div wire:loading wire:target="popupImageFile" class="text-cyan-400 text-[10px] animate-pulse font-mono">
                                                [UPLOADING_PAYLOAD...]
                                            </div>
                                        </div>
                                        
                                        <input 
                                            wire:model="popupImageUrl" 
                                            type="text" 
                                            class="w-full h-8 px-3 rounded bg-ide-editor border border-ide text-[#cccccc] focus:outline-none focus:border-[#007acc]"
                                            placeholder="/images/cyber_promo.png"
                                        >
                                        <span class="text-[9px] text-slate-500 font-mono">// Upload file baru untuk mengganti gambar otomatis, atau masukkan URL path di atas.</span>
                                    </div>
                                </div>

                                <!-- Text Content -->
                                <div class="space-y-1.5">
                                    <label class="text-[#569cd6] font-bold block">// const markdown_body_text = string;</label>
                                    <textarea 
                                        wire:model="popupText" 
                                        rows="4"
                                        class="w-full p-3 rounded bg-ide-editor border border-ide text-[#cccccc] focus:outline-none focus:border-[#007acc] leading-relaxed font-mono"
                                        placeholder="Tulis pesan pengumuman di sini..."
                                    ></textarea>
                                </div>

                                <!-- Submit Button -->
                                <div class="pt-2 flex justify-end">
                                    <button 
                                        type="submit" 
                                        class="px-5 py-2 bg-[#007acc] hover:bg-[#0062a3] text-white font-bold rounded shadow-md transition-all flex items-center gap-1.5 font-mono text-xs uppercase"
                                    >
                                        <span>COMMIT_CHANGES (SIMPAN) ▶</span>
                                    </button>
                                </div>
                            </form>

                            <!-- Right Column: Live Compiler Render (Preview) -->
                            <div class="bg-[#181818] border border-ide p-5 rounded-lg flex flex-col shadow-md font-mono text-xs">
                                <span class="text-slate-500 font-bold mb-3">// LIVE_COMPILER_RENDER: preview</span>
                                
                                <div class="bg-[#1e1e1e] border border-cyan-500/20 rounded-md overflow-hidden flex flex-col shadow-inner select-none scale-95 origin-top">
                                    <!-- Preview Header -->
                                    <div class="h-8 border-b border-[#2b2b2b] bg-[#202020] px-3 flex items-center justify-between text-[#858585]">
                                        <div class="flex items-center gap-1">
                                            <span class="w-2.5 h-2.5 rounded-full bg-[#ff5f56]/80"></span>
                                            <span class="w-2.5 h-2.5 rounded-full bg-[#ffbd2e]/80"></span>
                                            <span class="w-2.5 h-2.5 rounded-full bg-[#27c93f]/80"></span>
                                            <span class="ml-1.5 text-[9px] text-slate-500">// @{{ popupTitle || 'SYSTEM_NOTIFICATION' }}</span>
                                        </div>
                                    </div>

                                    <!-- Preview Content -->
                                    <div class="p-4 space-y-3 max-h-[300px] overflow-y-auto">
                                        @if($popupType === 'image' || $popupType === 'both')
                                            @if(!empty($popupImageUrl))
                                                <div class="rounded border border-white/5 overflow-hidden bg-slate-900">
                                                    <img src="{{ $popupImageUrl }}" alt="Preview Image" class="w-full h-auto object-cover max-h-32">
                                                </div>
                                            @else
                                                <div class="h-24 rounded border border-dashed border-slate-700 bg-slate-900/50 flex items-center justify-center text-slate-500">
                                                    // No Image Set
                                                </div>
                                            @endif
                                        @endif

                                        @if($popupType === 'text' || $popupType === 'both')
                                            @if(!empty($popupText))
                                                <div class="space-y-1.5 leading-relaxed text-[#cccccc]">
                                                    <div class="text-[8px] text-[#569cd6] font-bold">// CONTENT_STREAM</div>
                                                    <p class="whitespace-pre-line text-[10px] font-sans text-slate-300">{{ $popupText }}</p>
                                                </div>
                                            @else
                                                <div class="py-4 text-center text-slate-600 italic">
                                                    // No Text Content Set
                                                </div>
                                            @endif
                                        @endif
                                    </div>

                                    <!-- Preview Footer -->
                                    <div class="p-2 border-t border-[#2b2b2b] bg-[#1a1a1a] flex justify-end">
                                        <span class="px-2.5 py-1 bg-cyan-900/40 text-cyan-400 font-bold text-[8px] border border-cyan-800/30 rounded font-mono uppercase">
                                            DISMISS
                                        </span>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>
</div>
