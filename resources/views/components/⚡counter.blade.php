<?php

use Livewire\Component;

new class extends Component
{
    public int $count = 0;

    public function increment()
    {
        $this->count++;
    }

    public function decrement()
    {
        if ($this->count > 0) {
            $this->count--;
        }
    }

    public function resetCount()
    {
        $this->count = 0;
    }
};
?>

<div class="max-w-md w-full mx-auto my-12 bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl border border-slate-200 dark:border-zinc-800 rounded-3xl p-8 shadow-2xl shadow-slate-200/50 dark:shadow-none transition-all duration-300">
    <!-- Header -->
    <div class="text-center mb-8">
        <span class="px-3 py-1 text-xs font-semibold tracking-wider text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/30 rounded-full">
            Stack Demo Active
        </span>
        <h2 class="mt-4 text-2xl font-bold tracking-tight text-slate-800 dark:text-slate-100">
            Interactive Playpen
        </h2>
        <p class="mt-2 text-sm text-slate-500 dark:text-zinc-400">
            Testing Laravel + Livewire + Tailwind v4 + Alpine.js
        </p>
    </div>

    <!-- Livewire Counter Section -->
    <div class="bg-slate-50 dark:bg-zinc-950 rounded-2xl p-6 border border-slate-100 dark:border-zinc-900 text-center mb-6">
        <span class="text-xs font-medium text-slate-400 dark:text-zinc-500 uppercase tracking-wider block mb-1">
            Livewire (Server State)
        </span>
        
        <div class="text-5xl font-extrabold text-slate-900 dark:text-white my-4 transition-all duration-200" wire:loading.class="opacity-50">
            {{ $count }}
        </div>

        <div class="flex items-center justify-center gap-3 mt-4">
            <button wire:click="decrement" 
                    class="w-12 h-12 flex items-center justify-center rounded-xl bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 hover:border-slate-300 dark:hover:border-zinc-700 text-slate-700 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-800/50 active:scale-95 transition-all duration-150 shadow-sm font-semibold">
                -
            </button>
            
            <button wire:click="resetCount" 
                    class="px-4 h-12 flex items-center justify-center rounded-xl bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 hover:border-slate-300 dark:hover:border-zinc-700 text-xs text-slate-500 dark:text-zinc-400 hover:bg-slate-50 dark:hover:bg-zinc-800/50 active:scale-95 transition-all duration-150 shadow-sm font-medium">
                Reset
            </button>

            <button wire:click="increment" 
                    class="w-12 h-12 flex items-center justify-center rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white active:scale-95 transition-all duration-150 shadow-md shadow-indigo-200 dark:shadow-none font-semibold">
                +
            </button>
        </div>
    </div>

    <!-- Alpine.js Interactivity Section -->
    <div x-data="{ open: false, lastAction: '' }" class="border border-slate-100 dark:border-zinc-800/60 rounded-2xl p-6 bg-slate-50/50 dark:bg-zinc-950/50">
        <div class="flex items-center justify-between">
            <div>
                <span class="text-xs font-medium text-slate-400 dark:text-zinc-500 uppercase tracking-wider block">
                    Alpine.js (Client State)
                </span>
                <span class="text-sm font-semibold text-slate-700 dark:text-zinc-300">
                    Diagnostics Panel
                </span>
            </div>
            
            <!-- Alpine Toggle Switch -->
            <button @click="open = !open; lastAction = 'Toggled panel ' + (open ? 'open' : 'closed')" 
                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                    :class="open ? 'bg-indigo-600' : 'bg-slate-200 dark:bg-zinc-800'">
                <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                      :class="open ? 'translate-x-5' : 'translate-x-0'"></span>
            </button>
        </div>

        <!-- Collapsible Content with Alpine Transition -->
        <div x-show="open" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
             class="mt-4 pt-4 border-t border-slate-100 dark:border-zinc-800/80 text-xs text-slate-600 dark:text-zinc-400 space-y-2">
            
            <div class="flex justify-between py-1 border-b border-slate-100/50 dark:border-zinc-800/30">
                <span>Laravel Version:</span>
                <span class="font-mono font-medium text-slate-800 dark:text-zinc-200">v{{ app()->version() }}</span>
            </div>
            <div class="flex justify-between py-1 border-b border-slate-100/50 dark:border-zinc-800/30">
                <span>PHP Version:</span>
                <span class="font-mono font-medium text-slate-800 dark:text-zinc-200">{{ PHP_VERSION }}</span>
            </div>
            <div class="flex justify-between py-1">
                <span>Alpine.js State:</span>
                <span class="text-indigo-600 dark:text-indigo-400 font-semibold">Running (bundled)</span>
            </div>
            
            <div x-show="lastAction" class="bg-indigo-50/50 dark:bg-indigo-950/20 p-2.5 rounded-lg border border-indigo-100/30 dark:border-indigo-900/30 mt-3 text-[11px]">
                <span class="font-semibold text-indigo-700 dark:text-indigo-400">Activity Log:</span>
                <span x-text="lastAction" class="italic text-slate-500 dark:text-zinc-400 ml-1"></span>
            </div>
        </div>
    </div>
</div>