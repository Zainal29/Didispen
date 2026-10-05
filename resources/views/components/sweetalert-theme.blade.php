<style>
/* ========================================================
   MODERN & RESPONSIVE SWEETALERT2 THEME (DIDISPEN)
   Clean, Sleek, Rounded-2xl, Pastel Badge Icons, Anti-AI-Slop
   ======================================================== */
.swal2-container {
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    background: rgba(15, 23, 42, 0.45) !important;
    padding: 1rem !important;
    z-index: 99999 !important;
    transition: opacity 0.15s ease-out, backdrop-filter 0.15s ease-out !important;
}

.swal2-container.swal2-backdrop-hide {
    opacity: 0 !important;
    backdrop-filter: none !important;
    -webkit-backdrop-filter: none !important;
}

.swal2-popup {
    border-radius: 1.25rem !important; /* 20px */
    border: 1px solid rgba(226, 232, 240, 0.9) !important;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05), 0 0 0 1px rgba(0, 0, 0, 0.02) !important;
    padding: 1.75rem 1.5rem 1.5rem !important;
    max-width: 26rem !important;
    width: 92% !important;
    background: #ffffff !important;
    font-family: inherit !important;
}

.swal2-popup.swal2-show {
    animation: swalModernIn 0.2s cubic-bezier(0.16, 1, 0.3, 1) !important;
}

.swal2-popup.swal2-hide {
    animation: swalModernOut 0.15s ease-in forwards !important;
}

@keyframes swalModernIn {
    0% {
        opacity: 0;
        transform: scale(0.95) translateY(6px);
    }
    100% {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

@keyframes swalModernOut {
    0% {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
    100% {
        opacity: 0;
        transform: scale(0.95) translateY(4px);
    }
}

/* ==================== ICONS ==================== */
.swal2-icon {
    width: 3.5rem !important;
    height: 3.5rem !important;
    margin: 0.25rem auto 1.125rem !important;
    border-width: 2px !important;
    border-style: solid !important;
    transform: scale(0.95);
    transition: transform 0.2s ease;
}

/* Warning / Alert */
.swal2-icon.swal2-warning {
    border-color: #fde68a !important;
    background-color: #fffbeb !important;
    color: #d97706 !important;
}
.swal2-icon.swal2-warning .swal2-icon-content {
    font-size: 1.85rem !important;
    font-weight: 800 !important;
    color: #d97706 !important;
    line-height: 3.5rem !important;
}

/* Success (Nonaktifkan animasi & topeng berputar agar centang tampil bersih & statis tanpa bug) */
.swal2-icon.swal2-success {
    border-color: #a7f3d0 !important;
    background-color: #ecfdf5 !important;
    color: #059669 !important;
    animation: none !important;
}
.swal2-icon.swal2-success .swal2-success-circular-line-left,
.swal2-icon.swal2-success .swal2-success-circular-line-right,
.swal2-icon.swal2-success .swal2-success-fix {
    display: none !important;
    background: transparent !important;
}
.swal2-icon.swal2-success [class^='swal2-success-line'] {
    background-color: #059669 !important;
    animation: none !important;
    opacity: 1 !important;
    visibility: visible !important;
}
.swal2-icon.swal2-success .swal2-success-ring {
    border-color: rgba(5, 150, 105, 0.35) !important;
    animation: none !important;
}

/* Error / Danger */
.swal2-icon.swal2-error {
    border-color: #fecdd3 !important;
    background-color: #fff1f2 !important;
    color: #e11d48 !important;
    animation: none !important;
}
.swal2-icon.swal2-error [class^='swal2-x-mark-line'] {
    background-color: #e11d48 !important;
    animation: none !important;
}

/* Info */
.swal2-icon.swal2-info {
    border-color: #bae6fd !important;
    background-color: #f0f9ff !important;
    color: #0284c7 !important;
}
.swal2-icon.swal2-info .swal2-icon-content {
    font-size: 1.85rem !important;
    font-weight: 700 !important;
    color: #0284c7 !important;
    line-height: 3.5rem !important;
}

/* Question */
.swal2-icon.swal2-question {
    border-color: #c7d2fe !important;
    background-color: #eef2ff !important;
    color: #4f46e5 !important;
}
.swal2-icon.swal2-question .swal2-icon-content {
    font-size: 1.85rem !important;
    font-weight: 700 !important;
    color: #4f46e5 !important;
    line-height: 3.5rem !important;
}

/* ==================== TYPOGRAPHY ==================== */
.swal2-title {
    font-size: 1.125rem !important; /* 18px */
    font-weight: 800 !important;
    color: #0f172a !important;
    letter-spacing: -0.015em !important;
    line-height: 1.35 !important;
    padding: 0 !important;
    margin: 0 0 0.5rem 0 !important;
}

.swal2-html-container {
    font-size: 0.875rem !important; /* 14px */
    color: #475569 !important;
    line-height: 1.55 !important;
    margin: 0 0 1.25rem 0 !important;
    padding: 0 !important;
    font-weight: 400 !important;
}

.swal2-html-container strong, .swal2-html-container b {
    color: #0f172a !important;
    font-weight: 700 !important;
}

/* ==================== ACTIONS & BUTTONS ==================== */
.swal2-actions {
    margin: 1.25rem 0 0 !important;
    gap: 0.625rem !important;
    width: 100% !important;
    justify-content: center !important;
}

/* STRICT RULE: DO NOT OVERRIDE SWEETALERT DISPLAY: NONE */
.swal2-actions button[style*="display: none"],
.swal2-actions button[style*="display:none"],
.swal2-actions .swal2-styled[style*="display: none"],
.swal2-actions .swal2-styled[style*="display:none"],
.swal2-deny[style*="display: none"],
.swal2-deny[style*="display:none"],
.swal2-cancel[style*="display: none"],
.swal2-cancel[style*="display:none"],
.swal2-confirm[style*="display: none"],
.swal2-confirm[style*="display:none"] {
    display: none !important;
}

/* Style active/visible buttons ONLY */
.swal2-styled:not([style*="display: none"]):not([style*="display:none"]) {
    margin: 0;
    font-size: 0.875rem;
    font-weight: 700;
    border-radius: 0.75rem; /* 12px */
    padding: 0.625rem 1.25rem;
    min-height: 2.625rem;
    box-shadow: none;
    transition: all 0.15s ease-in-out;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.swal2-styled:active {
    transform: scale(0.97);
}

.swal2-styled.swal2-confirm {
    background-color: #2563eb !important;
    color: #ffffff !important;
    border: 1px solid transparent !important;
}

.swal2-styled.swal2-confirm:hover {
    background-color: #1d4ed8 !important;
    box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.25) !important;
}

.swal2-styled.swal2-confirm:focus {
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.25) !important;
}

.swal2-styled.swal2-cancel {
    background-color: #f8fafc !important;
    color: #475569 !important;
    border: 1px solid #e2e8f0 !important;
}

.swal2-styled.swal2-cancel:hover {
    background-color: #f1f5f9 !important;
    color: #1e293b !important;
    border-color: #cbd5e1 !important;
}

.swal2-styled.swal2-cancel:focus {
    box-shadow: 0 0 0 3px rgba(148, 163, 184, 0.25) !important;
}

/* ==================== FORM INPUTS ==================== */
.swal2-input, .swal2-textarea {
    border-radius: 0.75rem !important;
    border: 1px solid #cbd5e1 !important;
    font-size: 0.875rem !important;
    padding: 0.625rem 0.875rem !important;
    box-shadow: none !important;
    margin: 0.75rem auto 0 !important;
    width: 100% !important;
    box-sizing: border-box !important;
    transition: border-color 0.15s ease, box-shadow 0.15s ease !important;
}

.swal2-input:focus, .swal2-textarea:focus {
    border-color: #2563eb !important;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15) !important;
    outline: none !important;
}

/* ==================== CLOSE BUTTON ==================== */
.swal2-close {
    border-radius: 0.5rem !important;
    color: #94a3b8 !important;
    font-size: 1.25rem !important;
    transition: all 0.15s ease !important;
}
.swal2-close:hover {
    color: #334155 !important;
    background-color: #f1f5f9 !important;
}

/* ==================== MOBILE RESPONSIVE ==================== */
@media (max-width: 640px) {
    .swal2-popup {
        padding: 1.35rem 1.15rem 1.15rem !important;
        border-radius: 1.125rem !important;
        width: calc(100% - 1.5rem) !important;
    }
    .swal2-actions {
        flex-direction: column-reverse !important;
        width: 100% !important;
    }
    .swal2-styled:not([style*="display: none"]):not([style*="display:none"]) {
        width: 100% !important;
    }
}
</style>
