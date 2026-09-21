@php
    $sessionReceipt = session('purchase_receipt');
    $isGu = (app()->getLocale() === 'gu');
@endphp

<!-- ================= MINIMAL PAYMENT COMPLETED MODAL (CHECKMARK ANIMATION & 3 BUTTONS) ================= -->
<div x-data="{
    showReceiptModal: {{ $sessionReceipt ? 'true' : 'false' }},
    receipt: {{ $sessionReceipt ? json_encode($sessionReceipt) : 'null' }},

    getViewUrl() {
        if (!this.receipt || !this.receipt.download_url) return '#';
        const url = this.receipt.download_url;
        return url + (url.includes('?') ? '&view=1' : '?view=1');
    }
}"
x-cloak
x-show="showReceiptModal"
x-on:show-purchase-receipt.window="receipt = $event.detail; showReceiptModal = true;"
class="fixed inset-0 flex items-center justify-center p-4"
style="position: fixed !important; top: 0 !important; left: 0 !important; right: 0 !important; bottom: 0 !important; width: 100vw !important; height: 100vh !important; z-index: 999999 !important; background-color: rgba(15, 23, 42, 0.75) !important; backdrop-filter: blur(8px) !important; -webkit-backdrop-filter: blur(8px) !important; display: flex !important; align-items: center !important; justify-content: center !important; padding: 16px !important;"
x-transition:enter="transition ease-out duration-200"
x-transition:enter-start="opacity-0"
x-transition:enter-end="opacity-100"
x-transition:leave="transition ease-in duration-150"
x-transition:leave-start="opacity-100"
x-transition:leave-end="opacity-0">

    <style>
        @keyframes successCheckScale {
            0% { transform: scale(0.5); opacity: 0; }
            60% { transform: scale(1.12); opacity: 1; }
            100% { transform: scale(1); opacity: 1; }
        }
        @keyframes successCheckDraw {
            0% { stroke-dashoffset: 48; }
            100% { stroke-dashoffset: 0; }
        }
        @keyframes successPulseRing {
            0% { transform: scale(0.92); opacity: 0.7; }
            50% { transform: scale(1.22); opacity: 0; }
            100% { transform: scale(1.22); opacity: 0; }
        }
        .anim-check-circle {
            animation: successCheckScale 0.45s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
        }
        .anim-check-stroke {
            stroke-dasharray: 48;
            stroke-dashoffset: 48;
            animation: successCheckDraw 0.4s 0.25s cubic-bezier(0.65, 0, 0.45, 1) forwards;
        }
        .anim-check-ring {
            animation: successPulseRing 2.2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
    </style>

    <!-- Modal Box: Clean, Compact (~420px), Elegant Center Alignment -->
    <div @click.away="showReceiptModal = false"
         x-show="showReceiptModal"
         x-transition:enter="transition ease-out duration-200 delay-50"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="rounded-3xl border border-slate-100 shadow-2xl relative flex flex-col text-center"
         style="max-width: 420px !important; width: 100% !important; background-color: #ffffff !important; position: relative !important; z-index: 1000000 !important; margin: auto !important; padding: 26px 20px 20px 20px !important; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35) !important;">

        <!-- Top Right Close Icon Button -->
        <button type="button"
                @click="showReceiptModal = false"
                class="absolute top-3.5 right-3.5 w-7 h-7 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-400 hover:text-slate-700 flex items-center justify-center transition-colors cursor-pointer text-xs font-bold"
                title="{{ $isGu ? 'બંધ કરો' : 'Close' }}">
            ✕
        </button>

        <!-- 1. True wadu animation (Success Checkmark Animation) -->
        <div class="flex items-center justify-center pt-1 pb-1">
            <div class="relative flex items-center justify-center">
                <!-- Outer Pulse Ring -->
                <div class="anim-check-ring absolute w-20 h-20 rounded-full bg-emerald-400/25 pointer-events-none"></div>

                <!-- Inner Gradient Circle -->
                <div class="anim-check-circle relative w-16 h-16 rounded-full bg-gradient-to-tr from-emerald-600 to-emerald-400 flex items-center justify-center shadow-lg shadow-emerald-500/30">
                    <!-- Checkmark SVG with Draw Animation -->
                    <svg class="w-8 h-8 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round">
                        <path class="anim-check-stroke" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- 2. Heading: "Payment Completed Successfully" -->
        <div class="mt-4 space-y-1">
            <h3 class="text-lg sm:text-xl font-black text-slate-900 tracking-tight leading-snug">
                Payment Completed Successfully
            </h3>
            <p class="text-xs font-medium text-slate-500 leading-relaxed">
                {{ $isGu ? 'તમારી ચૂકવણી સફળતાપૂર્વક પૂર્ણ થઈ ગઈ છે.' : 'Your payment has been processed successfully.' }}
            </p>
        </div>

        <!-- 3. Action Buttons: Single Row, Perfectly Proportioned, No Word Overlap -->
        <div class="mt-5 flex items-center justify-center gap-2 w-full pt-1" style="display: flex !important; gap: 8px !important; width: 100% !important;">

            <!-- Button 1: Download Receipt -->
            <a :href="receipt?.download_url || '#'"
               target="_blank"
               style="flex: 1.25 1 0% !important; min-width: 0 !important; font-size: 11.5px !important; line-height: 1.2 !important; padding: 9px 8px !important; border-radius: 12px !important; white-space: nowrap !important; text-decoration: none !important; box-sizing: border-box !important;"
               class="bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold shadow-xs shadow-emerald-600/25 flex items-center justify-center gap-1.5 transition-all cursor-pointer"
               title="{{ $isGu ? 'પહોંચ ડાઉનલોડ કરો' : 'Download Receipt' }}">
                <svg class="w-3.5 h-3.5 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                <span class="truncate" style="font-size: 11.5px !important; font-weight: 700 !important;">{{ $isGu ? 'ડાઉનલોડ' : 'Download Receipt' }}</span>
            </a>

            <!-- Button 2: View Receipt -->
            <a :href="getViewUrl()"
               target="_blank"
               style="flex: 1.1 1 0% !important; min-width: 0 !important; font-size: 11.5px !important; line-height: 1.2 !important; padding: 9px 8px !important; border-radius: 12px !important; white-space: nowrap !important; text-decoration: none !important; box-sizing: border-box !important;"
               class="bg-slate-900 hover:bg-slate-800 active:scale-95 text-white font-bold shadow-xs shadow-slate-900/20 flex items-center justify-center gap-1.5 transition-all cursor-pointer"
               title="{{ $isGu ? 'પહોંચ જુઓ' : 'View Receipt' }}">
                <svg class="w-3.5 h-3.5 text-slate-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                <span class="truncate" style="font-size: 11.5px !important; font-weight: 700 !important;">{{ $isGu ? 'જુઓ' : 'View Receipt' }}</span>
            </a>

            <!-- Button 3: Close -->
            <button type="button"
                    @click="showReceiptModal = false"
                    style="flex: 0.85 1 0% !important; min-width: 0 !important; font-size: 11.5px !important; line-height: 1.2 !important; padding: 9px 8px !important; border-radius: 12px !important; white-space: nowrap !important; box-sizing: border-box !important;"
                    class="bg-slate-100 hover:bg-slate-200 active:scale-95 text-slate-700 font-bold border border-slate-200 transition-all cursor-pointer flex items-center justify-center">
                <span class="truncate" style="font-size: 11.5px !important; font-weight: 700 !important;">{{ $isGu ? 'બંધ કરો' : 'Close' }}</span>
            </button>
        </div>

    </div>
</div>
