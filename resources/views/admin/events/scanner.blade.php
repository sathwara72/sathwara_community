@extends('layouts.admin')

@section('page_title', 'Gate QR Pass Scanner — ' . $event->title)

@section('content')
    <div class="max-w-4xl mx-auto space-y-4" x-data="gateScannerApp()">

        <!-- Header Bar with Event Title & Live Stats -->
        <div class="bg-slate-900 text-white rounded-2xl p-4 shadow-md flex flex-wrap items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-rose-500 text-white">
                        LIVE GATE SCANNER
                    </span>
                    <span class="text-xs text-slate-400 font-medium">
                        {{ date('d-M-Y', strtotime($event->date)) }} {{ $event->time ? '| ' . date('h:i A', strtotime($event->time)) : '' }}
                    </span>
                </div>
                <h2 class="text-base sm:text-lg font-black text-white leading-tight">
                    {{ $event->title }}
                </h2>
                <p class="text-xs text-slate-400 font-medium truncate max-w-md">
                    📍 {{ $event->venue }}
                </p>
            </div>

            <!-- Stats Badge Group -->
            <div class="flex items-center gap-2 shrink-0">
                <div class="bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-center min-w-[80px]">
                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block">CHECKED IN</span>
                    <span class="text-lg font-black text-emerald-400" x-text="checkedInCount">
                        {{ $checkedInCount }}
                    </span>
                </div>
                <div class="bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-center min-w-[80px]">
                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block">TOTAL PASSES</span>
                    <span class="text-lg font-black text-white" x-text="totalPasses">
                        {{ $totalPasses }}
                    </span>
                </div>
                <a href="{{ route('admin.events.show', $event->id) }}"
                    class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white font-bold text-xs rounded-xl border border-slate-700 transition-colors">
                    Back
                </a>
            </div>
        </div>

        <!-- Scanner Card Container -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            
            <!-- Left: Camera Viewfinder & Camera Selector -->
            <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-2xs space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-black text-slate-900 uppercase tracking-wider flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h0.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Camera Scanner</span>
                    </h3>
                    <button type="button" @click="toggleCamera()"
                        class="px-2.5 py-1 text-[11px] font-bold rounded-lg border transition-colors cursor-pointer"
                        :class="isScanning ? 'bg-rose-50 text-rose-700 border-rose-200 hover:bg-rose-100' : 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100'">
                        <span x-text="isScanning ? 'Stop Camera' : 'Start Camera'"></span>
                    </button>
                </div>

                <!-- HTML5 Scanner Viewfinder Frame -->
                <div class="relative w-full rounded-xl overflow-hidden bg-slate-950 min-h-[260px] flex items-center justify-center border-2 border-slate-900 shadow-inner">
                    <div id="reader" class="w-full h-full"></div>

                    <!-- Scanning Overlay Graphic -->
                    <div x-show="isScanning && !lastResult" class="absolute inset-0 pointer-events-none flex flex-col items-center justify-center p-6">
                        <div class="w-48 h-48 border-2 border-dashed border-rose-500 rounded-2xl animate-pulse flex items-center justify-center">
                            <span class="text-[10px] font-black text-rose-400 uppercase tracking-widest bg-slate-950/80 px-2 py-1 rounded">Position QR Code</span>
                        </div>
                    </div>
                </div>

                <!-- Manual Pass Code Input Option -->
                <form @submit.prevent="submitManualCode()" class="pt-2 border-t border-slate-100 flex items-center gap-2">
                    <input type="text" x-model="manualCode" placeholder="Enter Pass Code or Token Hash manually..."
                        class="flex-1 px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-bold text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500">
                    <button type="submit" :disabled="isVerifying || !manualCode"
                        class="px-3.5 py-1.5 bg-slate-900 hover:bg-slate-800 disabled:opacity-50 text-white font-extrabold text-xs rounded-xl shadow-xs transition-colors shrink-0 cursor-pointer">
                        Verify
                    </button>
                </form>
            </div>

            <!-- Right: Verification Result Display -->
            <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-2xs flex flex-col justify-between space-y-4">
                <div>
                    <h3 class="text-xs font-black text-slate-900 uppercase tracking-wider mb-3 flex items-center justify-between">
                        <span>Scan Status & Details</span>
                        <span x-show="isVerifying" class="text-rose-600 font-bold text-[11px] animate-pulse">Verifying...</span>
                    </h3>

                    <!-- Initial Standby State -->
                    <template x-if="!lastResult">
                        <div class="py-12 px-4 text-center space-y-3 bg-slate-50 rounded-xl border border-dashed border-slate-200">
                            <div class="w-12 h-12 rounded-full bg-slate-200/70 text-slate-500 flex items-center justify-center mx-auto">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                            </div>
                            <h4 class="text-xs font-bold text-slate-700">Ready to Scan</h4>
                            <p class="text-[11px] text-slate-400 max-w-xs mx-auto">Point the device camera at the QR code printed on the attendee pass card or PDF receipt.</p>
                        </div>
                    </template>

                    <!-- Result Status Card -->
                    <template x-if="lastResult">
                        <div class="rounded-xl p-4 border space-y-3 transition-all shadow-xs"
                            :class="{
                                'bg-emerald-50/90 border-emerald-300 text-emerald-950': lastResult.status === 'checked_in',
                                'bg-amber-50/90 border-amber-300 text-amber-950': lastResult.status === 'already_used',
                                'bg-rose-50/90 border-rose-300 text-rose-950': lastResult.status === 'invalid' || lastResult.status === 'wrong_event'
                            }">

                            <!-- Status Icon & Title -->
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 shadow-xs"
                                    :class="{
                                        'bg-emerald-600 text-white': lastResult.status === 'checked_in',
                                        'bg-amber-600 text-white': lastResult.status === 'already_used',
                                        'bg-rose-600 text-white': lastResult.status === 'invalid' || lastResult.status === 'wrong_event'
                                    }">
                                    <template x-if="lastResult.status === 'checked_in'">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    </template>
                                    <template x-if="lastResult.status === 'already_used'">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    </template>
                                    <template x-if="lastResult.status === 'invalid' || lastResult.status === 'wrong_event'">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </template>
                                </div>

                                <div class="space-y-0.5">
                                    <h4 class="text-sm font-black uppercase tracking-wider" x-text="lastResult.message"></h4>
                                    <p class="text-xs font-semibold opacity-90 leading-relaxed" x-text="lastResult.detail"></p>
                                </div>
                            </div>

                            <!-- Attendee Info Table (if token exists) -->
                            <template x-if="lastResult.token">
                                <div class="pt-3 border-t border-slate-200/70 space-y-1.5 text-xs font-medium">
                                    <div class="flex items-center justify-between">
                                        <span class="text-slate-500 font-bold">Attendee Name:</span>
                                        <span class="font-black text-slate-900" x-text="lastResult.token.attendee"></span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-slate-500 font-bold">Mobile Phone:</span>
                                        <span class="font-bold text-slate-800" x-text="lastResult.token.mobile"></span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-slate-500 font-bold">Pass Code:</span>
                                        <span class="font-mono font-bold text-slate-900" x-text="lastResult.token.pass_code"></span>
                                    </div>
                                    <template x-if="lastResult.token.checked_in_at">
                                        <div class="flex items-center justify-between">
                                            <span class="text-slate-500 font-bold">Checked In At:</span>
                                            <span class="font-bold text-rose-700" x-text="lastResult.token.checked_in_at"></span>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                <!-- Footer Action Buttons -->
                <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                    <span class="text-[10px] font-bold text-slate-400">Tap OK on the popup to scan the next pass</span>
                    <button type="button" @click="resetScanResult()"
                        class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-extrabold text-xs rounded-xl shadow-xs transition-colors cursor-pointer">
                        Scan Next Pass ➔
                    </button>
                </div>
            </div>
        </div>

        <!-- Scan Result Popup (stays until OK is tapped, so the gate staff can see it on mobile) -->
        <div x-show="showPopup" x-cloak x-transition.opacity
             class="fixed inset-0 z-[100] bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4">
            <div x-show="showPopup" x-transition.scale.90
                 class="w-full max-w-sm bg-white rounded-3xl shadow-2xl overflow-hidden text-center">
                <div class="px-6 pt-7 pb-5 space-y-3"
                     :class="{
                         'bg-emerald-50': lastResult?.status === 'checked_in',
                         'bg-amber-50': lastResult?.status === 'already_used',
                         'bg-rose-50': lastResult && lastResult.status !== 'checked_in' && lastResult.status !== 'already_used'
                     }">
                    <div class="w-16 h-16 rounded-full mx-auto flex items-center justify-center text-white shadow-md"
                         :class="{
                             'bg-emerald-600': lastResult?.status === 'checked_in',
                             'bg-amber-500': lastResult?.status === 'already_used',
                             'bg-rose-600': lastResult && lastResult.status !== 'checked_in' && lastResult.status !== 'already_used'
                         }">
                        <svg x-show="lastResult?.status === 'checked_in'" class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        <svg x-show="lastResult?.status === 'already_used'" class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <svg x-show="lastResult && lastResult.status !== 'checked_in' && lastResult.status !== 'already_used'" class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                    </div>

                    <h3 class="text-lg font-black uppercase tracking-wide"
                        :class="{
                            'text-emerald-800': lastResult?.status === 'checked_in',
                            'text-amber-800': lastResult?.status === 'already_used',
                            'text-rose-800': lastResult && lastResult.status !== 'checked_in' && lastResult.status !== 'already_used'
                        }"
                        x-text="lastResult?.status === 'checked_in' ? 'Scanned Successfully' : (lastResult?.status === 'already_used' ? 'Already Scanned' : (lastResult?.message || 'Invalid Pass'))"></h3>

                    <template x-if="lastResult?.token">
                        <div class="space-y-1">
                            <p class="text-2xl font-black text-slate-900 leading-tight break-words" x-text="lastResult.token.attendee"></p>
                            <p class="text-xs font-bold text-slate-500">
                                Pass <span class="font-mono" x-text="lastResult.token.pass_code"></span>
                                <template x-if="lastResult.token.mobile && lastResult.token.mobile !== '-'">
                                    <span> · <span x-text="lastResult.token.mobile"></span></span>
                                </template>
                            </p>
                        </div>
                    </template>

                    <p class="text-xs font-semibold text-slate-600 leading-relaxed"
                       x-show="lastResult?.status !== 'checked_in'" x-text="lastResult?.detail"></p>
                </div>

                <div class="p-4 border-t border-slate-100">
                    <button type="button" @click="closePopup()"
                        class="w-full py-3.5 bg-slate-900 hover:bg-slate-800 active:scale-95 text-white font-black text-base rounded-2xl shadow-xs transition-all cursor-pointer">
                        OK
                    </button>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <!-- html5-qrcode CDN -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

    <script>
        function gateScannerApp() {
            return {
                html5QrCode: null,
                isScanning: false,
                isVerifying: false,
                manualCode: '',
                lastCode: '',
                lastResult: null,
                totalPasses: {{ $totalPasses }},
                checkedInCount: {{ $checkedInCount }},
                showPopup: false,
                lastDismissedCode: '',
                lastDismissedAt: 0,

                init() {
                    this.$nextTick(() => {
                        this.startCamera();
                    });
                },

                toggleCamera() {
                    if (this.isScanning) {
                        this.stopCamera();
                    } else {
                        this.startCamera();
                    }
                },

                startCamera() {
                    if (this.isScanning) return;
                    this.html5QrCode = new Html5Qrcode("reader");

                    const config = { fps: 10, qrbox: { width: 220, height: 220 } };

                    this.html5QrCode.start(
                        { facingMode: "environment" },
                        config,
                        (decodedText, decodedResult) => {
                            this.handleQrScan(decodedText);
                        },
                        (errorMessage) => {
                            // ignore silent frame decode errors
                        }
                    ).then(() => {
                        this.isScanning = true;
                    }).catch(err => {
                        console.error("Camera access failed", err);
                        this.isScanning = false;
                    });
                },

                stopCamera() {
                    if (this.html5QrCode && this.isScanning) {
                        this.html5QrCode.stop().then(() => {
                            this.isScanning = false;
                        }).catch(err => console.error(err));
                    }
                },

                handleQrScan(scannedCode) {
                    if (this.isVerifying || this.showPopup) return;
                    // The camera keeps reading the same QR right after OK; ignore it for a moment
                    if (scannedCode === this.lastDismissedCode && Date.now() - this.lastDismissedAt < 3000) return;
                    
                    // Temporarily pause camera scan during API verification
                    this.verifyPassCode(scannedCode);
                },

                submitManualCode() {
                    if (!this.manualCode || this.isVerifying) return;
                    this.verifyPassCode(this.manualCode);
                },

                verifyPassCode(code) {
                    this.isVerifying = true;
                    this.lastCode = code;

                    fetch("{{ route('admin.events.verify_pass', $event->id) }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        body: JSON.stringify({ qr_token: code })
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.isVerifying = false;
                        this.lastResult = data;
                        this.manualCode = '';

                        if (data.checked_in_count !== undefined) {
                            this.checkedInCount = data.checked_in_count;
                        }

                        // Play audio tone feedback
                        if (data.status === 'checked_in') {
                            this.playTone(880, 0.2, 'sine'); // High green success beep
                        } else if (data.status === 'already_used') {
                            this.playTone(440, 0.4, 'triangle'); // Double amber warning tone
                        } else {
                            this.playTone(220, 0.6, 'sawtooth'); // Low red buzz error
                        }

                        this.openPopup(data.status);
                    })
                    .catch(err => {
                        this.isVerifying = false;
                        console.error("Verification failed", err);
                        this.playTone(220, 0.6, 'sawtooth');
                        this.lastResult = {
                            success: false,
                            status: 'invalid',
                            message: 'NETWORK / SERVER ERROR',
                            detail: 'Could not connect to verification server. Check network connection.'
                        };
                        this.openPopup('invalid');
                    });
                },

                openPopup(status) {
                    this.showPopup = true;
                    try {
                        navigator.vibrate?.(status === 'checked_in' ? 150 : [200, 100, 200]);
                    } catch(e) {}
                },

                closePopup() {
                    this.showPopup = false;
                    this.lastDismissedCode = this.lastCode || '';
                    this.lastDismissedAt = Date.now();
                    this.resetScanResult();
                },

                resetScanResult() {
                    this.lastResult = null;
                },

                playTone(freq, duration, type = 'sine') {
                    try {
                        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                        const osc = audioCtx.createOscillator();
                        const gain = audioCtx.createGain();

                        osc.type = type;
                        osc.frequency.setValueAtTime(freq, audioCtx.currentTime);
                        gain.gain.setValueAtTime(0.3, audioCtx.currentTime);

                        osc.connect(gain);
                        gain.connect(audioCtx.destination);

                        osc.start();
                        osc.stop(audioCtx.currentTime + duration);
                    } catch(e) {
                        // ignore if Web Audio is blocked
                    }
                }
            }
        }
    </script>
@endpush
