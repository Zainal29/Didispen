{{-- ========================================================================= --}}
{{-- DIDISPEN PWA ANIMATED SPLASH & LOADING SCREEN COMPONENT                   --}}
{{-- Khusus mode PWA (Installed / Standalone) dengan tema Biru Modern & Cepat   --}}
{{-- ========================================================================= --}}

<script>
    (function() {
        try {
            var isStandalone = window.matchMedia('(display-mode: standalone)').matches 
                            || window.navigator.standalone === true 
                            || document.referrer.includes('android-app://')
                            || window.location.search.indexOf('splash=1') !== -1
                            || window.location.search.indexOf('pwa=1') !== -1;
            var hasBooted = sessionStorage.getItem('didispen_pwa_splash_booted');
            
            // Aktifkan hanya jika di mode PWA (atau mode test ?splash=1) dan belum boot pada sesi ini
            if (isStandalone && !hasBooted) {
                document.documentElement.classList.add('pwa-splash-active');
                window.__pwaSplashStartTime = Date.now();
            }
        } catch (e) {
            // Silently pass
        }
    })();
</script>

<style>
    /* Splash Screen Container */
    #pwa-animated-splash {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        width: 100vw;
        height: 100vh;
        height: 100dvh;
        z-index: 9999999;
        background: linear-gradient(145deg, #1e40af 0%, #1d4ed8 45%, #0f172a 100%);
        align-items: center;
        justify-content: center;
        overflow: hidden;
        user-select: none;
        -webkit-user-select: none;
        opacity: 1;
        transform: scale(1);
        transition: opacity 0.38s cubic-bezier(0.16, 1, 0.3, 1), transform 0.38s cubic-bezier(0.16, 1, 0.3, 1), filter 0.38s ease;
    }

    html.pwa-splash-active #pwa-animated-splash {
        display: flex !important;
    }

    /* Ambient Glow di belakang */
    .pwa-splash-glow {
        position: absolute;
        width: 280px;
        height: 280px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(59, 130, 246, 0.45) 0%, rgba(37, 99, 235, 0.15) 50%, transparent 70%);
        filter: blur(35px);
        animation: pwaGlowPulse 2.4s ease-in-out infinite alternate;
        pointer-events: none;
    }

    /* Konten Pusat */
    .pwa-splash-content {
        position: relative;
        z-index: 2;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 24px;
        max-width: 320px;
    }

    /* Wrapper Logo + Gelombang Ripple */
    .pwa-logo-wrapper {
        position: relative;
        width: 100px;
        height: 100px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 22px;
    }

    .pwa-ripple-ring {
        position: absolute;
        border-radius: 50%;
        border: 1.5px solid rgba(255, 255, 255, 0.35);
        pointer-events: none;
    }

    .pwa-ripple-1 {
        width: 96px;
        height: 96px;
        animation: pwaRipple 1.8s cubic-bezier(0.2, 0.8, 0.2, 1) infinite;
    }

    .pwa-ripple-2 {
        width: 96px;
        height: 96px;
        animation: pwaRipple 1.8s cubic-bezier(0.2, 0.8, 0.2, 1) infinite 0.7s;
    }

    /* Kartu Logo */
    .pwa-logo-card {
        position: relative;
        z-index: 3;
        width: 86px;
        height: 86px;
        background: rgba(255, 255, 255, 0.12);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1.5px solid rgba(255, 255, 255, 0.28);
        border-radius: 26px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 16px 36px -8px rgba(15, 23, 42, 0.5), inset 0 1px 1px rgba(255, 255, 255, 0.3);
        animation: pwaLogoFloat 2.2s ease-in-out infinite;
    }

    .pwa-logo-img {
        width: 58px;
        height: 58px;
        object-fit: contain;
        filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.25));
    }

    /* Teks Branding */
    .pwa-text-group {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-bottom: 24px;
    }

    .pwa-title {
        color: #ffffff;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        font-size: 24px;
        font-weight: 800;
        letter-spacing: 0.08em;
        margin: 0 0 4px 0;
        text-shadow: 0 2px 10px rgba(0, 0, 0, 0.25);
    }

    .pwa-tagline {
        color: #bfdbfe;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        font-size: 12px;
        font-weight: 500;
        letter-spacing: 0.02em;
        margin: 0 0 3px 0;
        opacity: 0.95;
    }

    .pwa-school {
        color: rgba(191, 219, 254, 0.7);
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        margin: 0;
    }

    /* Slim Indeterminate Progress Bar */
    .pwa-progress-track {
        width: 140px;
        height: 3.5px;
        background: rgba(255, 255, 255, 0.15);
        border-radius: 9999px;
        overflow: hidden;
        position: relative;
        box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.2);
    }

    .pwa-progress-bar {
        position: absolute;
        top: 0;
        left: 0;
        height: 100%;
        width: 45%;
        background: linear-gradient(90deg, transparent 0%, #60a5fa 50%, #ffffff 100%);
        border-radius: 9999px;
        animation: pwaProgressSlide 1.1s cubic-bezier(0.4, 0, 0.2, 1) infinite;
        box-shadow: 0 0 10px #60a5fa;
    }

    /* Keyframe Animations */
    @keyframes pwaRipple {
        0% {
            transform: scale(0.9);
            opacity: 0.75;
        }
        100% {
            transform: scale(1.6);
            opacity: 0;
        }
    }

    @keyframes pwaLogoFloat {
        0%, 100% {
            transform: translateY(0) scale(1);
        }
        50% {
            transform: translateY(-4px) scale(1.02);
        }
    }

    @keyframes pwaGlowPulse {
        0% {
            transform: scale(0.9);
            opacity: 0.6;
        }
        100% {
            transform: scale(1.15);
            opacity: 0.95;
        }
    }

    @keyframes pwaProgressSlide {
        0% {
            transform: translateX(-120%);
        }
        100% {
            transform: translateX(320%);
        }
    }
</style>

{{-- HTML Elemen Splash Screen --}}
<div id="pwa-animated-splash" aria-hidden="true">
    <div class="pwa-splash-glow"></div>
    <div class="pwa-splash-content">
        <div class="pwa-logo-wrapper">
            <div class="pwa-ripple-ring pwa-ripple-1"></div>
            <div class="pwa-ripple-ring pwa-ripple-2"></div>
            <div class="pwa-logo-card">
                <img src="{{ asset('icons/icon-192.png') }}" 
                     alt="DIDISPEN Logo" 
                     class="pwa-logo-img"
                     onerror="this.src='{{ asset('images/logo-didispen.png') }}'">
            </div>
        </div>

        <div class="pwa-text-group">
            <h1 class="pwa-title">DIDISPEN</h1>
            <p class="pwa-tagline">Digital Dispensasi Pendidikan</p>
            <p class="pwa-school">SMK Negeri 1 Bangsri</p>
        </div>

        <div class="pwa-progress-track">
            <div class="pwa-progress-bar"></div>
        </div>
    </div>
</div>

<script>
    (function() {
        var splash = document.getElementById('pwa-animated-splash');
        if (!splash) return;

        function dismissSplash() {
            if (!splash || splash.getAttribute('data-dismissed')) return;
            splash.setAttribute('data-dismissed', 'true');

            // Cek elapsed time agar animasi dinikmati minimal 500ms (cepat & smooth)
            var startTime = window.__pwaSplashStartTime || Date.now();
            var elapsed = Date.now() - startTime;
            var minDisplayTime = 500;
            var remaining = Math.max(0, minDisplayTime - elapsed);

            setTimeout(function() {
                splash.style.opacity = '0';
                splash.style.transform = 'scale(1.05)';
                splash.style.filter = 'blur(4px)';
                splash.style.pointerEvents = 'none';

                try {
                    sessionStorage.setItem('didispen_pwa_splash_booted', 'true');
                } catch (e) {}

                setTimeout(function() {
                    if (splash && splash.parentNode) {
                        splash.parentNode.removeChild(splash);
                    }
                    document.documentElement.classList.remove('pwa-splash-active');
                }, 400);
            }, remaining);
        }

        // Jalankan saat load selesai
        if (document.readyState === 'complete') {
            dismissSplash();
        } else {
            window.addEventListener('load', dismissSplash);
        }

        // Safety fallback: maksimal 1.8 detik splash akan selalu tertutup agar user tidak terhalang
        setTimeout(dismissSplash, 1800);
    })();
</script>
