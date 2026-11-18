import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

// Confirm destructive actions consistently throughout the admin/editor UI.
document.addEventListener('click', (event) => {
    const trigger = event.target.closest('[data-confirm]');
    if (trigger && !window.confirm(trigger.dataset.confirm || 'Bạn có chắc chắn muốn thực hiện thao tác này?')) {
        event.preventDefault();
    }
});
