/**
 * FF Panel Store - Plain JavaScript Client Logic
 * No external JS libraries or frameworks
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Auto-dismiss Flash Messages after 5 seconds
    const flashAlerts = document.querySelectorAll('[role="alert"]');
    flashAlerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(function() { alert.remove(); }, 500);
        }, 5000);
    });

    // Close Modals on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modals = document.querySelectorAll('#orderModal, #adminOrderModal, #walletModal');
            modals.forEach(function(m) {
                if (!m.classList.contains('hidden')) {
                    m.classList.add('hidden');
                }
            });
        }
    });

    // Close Modals when clicking outer backdrop
    const modalBackdrops = document.querySelectorAll('#orderModal, #adminOrderModal, #walletModal');
    modalBackdrops.forEach(function(backdrop) {
        backdrop.addEventListener('click', function(e) {
            if (e.target === backdrop) {
                backdrop.classList.add('hidden');
            }
        });
    });

    // Quick UID Input Validation (Numeric check)
    const uidInputs = document.querySelectorAll('input[name="player_uid"], input[name="uid_number"]');
    uidInputs.forEach(function(input) {
        input.addEventListener('input', function() {
            // Keep numeric only
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    });

});

// Global Helper: Copy to Clipboard
function copyToClipboard(text, message) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(function() {
            showToast(message || 'Copied to clipboard: ' + text);
        }).catch(function() {
            prompt('Copy value:', text);
        });
    } else {
        prompt('Copy value:', text);
    }
}

// Global Helper: Mini Toast
function showToast(msg) {
    const toast = document.createElement('div');
    toast.className = 'fixed bottom-5 right-5 z-50 bg-[#161D32] border border-rose-500/40 text-white text-xs px-4 py-3 rounded-xl shadow-2xl flex items-center gap-2';
    toast.innerHTML = '<span>⚡</span> <span>' + msg + '</span>';
    document.body.appendChild(toast);
    setTimeout(function() {
        toast.style.transition = 'opacity 0.4s ease';
        toast.style.opacity = '0';
        setTimeout(function() { toast.remove(); }, 400);
    }, 3000);
}
