// assets/js/app.js

// Auto-dismiss alerts after 4s
document.querySelectorAll('.sk-alert').forEach(el => {
    setTimeout(() => {
        el.style.transition = 'opacity .4s';
        el.style.opacity = '0';
        setTimeout(() => el.remove(), 400);
    }, 4000);
});

// Confirm before dangerous actions
document.querySelectorAll('[data-confirm]').forEach(btn => {
    btn.addEventListener('click', e => {
        if (!confirm(btn.dataset.confirm || 'Da li ste sigurni?')) {
            e.preventDefault();
        }
    });
});

// Stock bar rendering
document.querySelectorAll('.stock-bar-fill').forEach(bar => {
    const pct = parseFloat(bar.dataset.pct) || 0;
    bar.style.width = Math.min(pct, 100) + '%';
    bar.style.background = pct < 30 ? '#ef4444' : pct < 60 ? '#f5a623' : '#22c55e';
});

// Search form: live filter delay
const searchInput = document.getElementById('liveSearch');
if (searchInput) {
    let timer;
    searchInput.addEventListener('input', () => {
        clearTimeout(timer);
        timer = setTimeout(() => {
            const form = searchInput.closest('form');
            if (form) form.submit();
        }, 500);
    });
}

// Tooltip init
const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
tooltips.forEach(t => new bootstrap.Tooltip(t));

// Populate edit modal from data attrs
document.querySelectorAll('[data-edit]').forEach(btn => {
    btn.addEventListener('click', () => {
        const data = btn.dataset;
        const modal = document.getElementById(data.edit + 'Modal');
        if (!modal) return;
        Object.keys(data).forEach(key => {
            if (key === 'edit') return;
            const el = modal.querySelector(`[name="${key}"]`);
            if (el) el.value = data[key];
        });
        const m = new bootstrap.Modal(modal);
        m.show();
    });
});
