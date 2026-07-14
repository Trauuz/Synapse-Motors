(() => {
    'use strict';

    const modalShell = document.querySelector('[data-admin-editor-modal]');
    const editTriggers = document.querySelectorAll('[data-admin-edit-trigger]');
    const closeButtons = modalShell?.querySelectorAll('[data-admin-editor-close]');
    const fieldMap = {
        vehicle_id: modalShell?.querySelector('[data-admin-editor-field="vehicle_id"]'),
        name: modalShell?.querySelector('[data-admin-editor-field="name"]'),
        collection: modalShell?.querySelector('[data-admin-editor-field="collection"]'),
        detail: modalShell?.querySelector('[data-admin-editor-field="detail"]'),
        price: modalShell?.querySelector('[data-admin-editor-field="price"]'),
        stock_quantity: modalShell?.querySelector('[data-admin-editor-field="stock_quantity"]'),
        availability: modalShell?.querySelector('[data-admin-editor-field="availability"]'),
    };

    if (!modalShell) {
        return;
    }

    const openModal = () => {
        modalShell.hidden = false;
        document.body.classList.add('auth-modal-open');
        document.body.classList.add('admin-editor-open');
    };

    const closeModal = () => {
        if (modalShell.hidden) {
            return;
        }

        modalShell.hidden = true;
        document.body.classList.remove('auth-modal-open');
        document.body.classList.remove('admin-editor-open');
    };

    const fillModal = (trigger) => {
        fieldMap.vehicle_id.value = trigger.dataset.vehicleId || '';
        fieldMap.name.value = trigger.dataset.vehicleName || '';
        fieldMap.collection.value = trigger.dataset.vehicleCollection || '';
        fieldMap.detail.value = trigger.dataset.vehicleDetail || '';
        fieldMap.price.value = trigger.dataset.vehiclePrice || '';
        fieldMap.stock_quantity.value = trigger.dataset.vehicleStock || '';
        fieldMap.availability.value = trigger.dataset.vehicleAvailability || '';
    };

    editTriggers.forEach((trigger) => {
        trigger.addEventListener('click', () => {
            fillModal(trigger);
            openModal();
            fieldMap.name?.focus();
        });
    });

    closeButtons?.forEach((button) => {
        button.addEventListener('click', closeModal);
    });

    if (modalShell.dataset.adminEditorOpenOnLoad === 'true') {
        openModal();
    }

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeModal();
        }
    });
})();
