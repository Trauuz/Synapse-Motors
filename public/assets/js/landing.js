(() => {
    'use strict';

    const authModal = document.querySelector('[data-auth-modal]');
    const authMessage = authModal?.querySelector('[data-auth-message]');
    const authKicker = authModal?.querySelector('[data-auth-kicker]');
    const authTitle = authModal?.querySelector('[data-auth-title]');
    const authSubmit = authModal?.querySelector('[data-auth-submit]');
    const authNameField = authModal?.querySelector('[data-auth-name-field]');
    const authContactField = authModal?.querySelector('[data-auth-contact-field]');
    const authConfirmField = authModal?.querySelector('[data-auth-confirm-field]');
    const authAddressField = authModal?.querySelector('[data-auth-address-field]');
    const authForgotLink = authModal?.querySelector('[data-auth-forgot-link]');
    const authRememberRow = authModal?.querySelector('[data-auth-remember-row]');
    const authSwitches = authModal?.querySelectorAll('[data-auth-switch]');
    const authTriggers = document.querySelectorAll('[data-auth-trigger]');
    const authCloseButtons = authModal?.querySelectorAll('[data-auth-close]');
    const authAction = authModal?.querySelector('[data-auth-action]');
    const authRedirect = authModal?.querySelector('[data-auth-redirect]');
    const authForm = authModal?.querySelector('[data-auth-form]');
    const authFormError = authModal?.querySelector('[data-auth-form-error]');
    const authEmailError = authModal?.querySelector('[data-auth-email-error]');
    const inventorySearchInput = document.querySelector('[data-inventory-search]');
    const inventoryFilterButtons = document.querySelectorAll('[data-filter]');
    const inventoryCards = document.querySelectorAll('[data-category]');
    let lastAuthTrigger = null;
    let authModalMode = 'signin';
    let activeInventoryFilter = 'all';
    const guestAuthMessages = {
        signin: 'Sign in to continue with your Synapse Motors account.',
        cart: 'Sign in to add vehicles to your cart and keep your shortlist in sync.',
        save: 'Sign in to save vehicles and pick up your shortlist across devices.',
    };
    const authModalModes = {
        signin: {
            kicker: 'Member sign in',
            title: 'Sign in to your Synapse Motors account.',
            submit: 'Sign in',
            showNameField: false,
            showContactField: false,
            showConfirmField: false,
            showAddressField: false,
            showForgotLink: true,
            showRememberRow: true,
            switchToShow: 'signup',
        },
        signup: {
            kicker: 'Create your account',
            title: 'Create your Synapse Motors account.',
            submit: 'Create account',
            message: 'Set up your account to save vehicles, build your cart, and revisit your picks anytime.',
            showNameField: true,
            showContactField: true,
            showConfirmField: true,
            showAddressField: true,
            showForgotLink: false,
            showRememberRow: false,
            switchToShow: 'signin',
        },
    };

    const applyFilter = (selected) => {
        const availableFilters = new Set(Array.from(inventoryFilterButtons, (item) => item.dataset.filter));
        const activeFilter = availableFilters.has(selected) ? selected : 'all';
        const searchTerm = inventorySearchInput?.value.trim().toLowerCase() ?? '';

        activeInventoryFilter = activeFilter;

        inventoryFilterButtons.forEach((item) => {
            const active = item.dataset.filter === activeFilter;
            item.classList.toggle('is-active', active);
            item.setAttribute('aria-pressed', String(active));
        });

        inventoryCards.forEach((card) => {
            const matchesCategory = activeFilter === 'all' || card.dataset.category.split(' ').includes(activeFilter);
            const matchesSearch = searchTerm === '' || card.dataset.search?.includes(searchTerm);

            card.hidden = !matchesCategory || !matchesSearch;
        });
    };

    const setAuthModalMode = (mode, triggerName = 'signin', clearErrorsOnChange = true) => {
        if (!authModal || !Object.hasOwn(authModalModes, mode)) return;
        authModalMode = mode;
        authModal.dataset.authMode = mode;
        const modalState = authModalModes[mode];
        const authKey = Object.hasOwn(guestAuthMessages, triggerName) ? triggerName : 'signin';

        if (authKicker) authKicker.textContent = modalState.kicker;
        if (authTitle) authTitle.textContent = modalState.title;
        if (authSubmit) authSubmit.textContent = modalState.submit;
        if (authMessage) authMessage.textContent = mode === 'signup' ? modalState.message : guestAuthMessages[authKey];
        if (authNameField) authNameField.hidden = !modalState.showNameField;
        if (authContactField) authContactField.hidden = !modalState.showContactField;
        if (authConfirmField) authConfirmField.hidden = !modalState.showConfirmField;
        if (authAddressField) authAddressField.hidden = !modalState.showAddressField;
        if (authForgotLink) authForgotLink.hidden = !modalState.showForgotLink;
        if (authRememberRow) authRememberRow.hidden = !modalState.showRememberRow;
        if (authAction) authAction.value = mode;
        if (authRedirect) authRedirect.value = `${window.location.pathname.split('/').pop() || 'index.php'}${window.location.search}`;
        if (clearErrorsOnChange) {
            clearAuthErrors();
        }

        authSwitches?.forEach((link) => {
            link.hidden = link.dataset.authSwitch !== modalState.switchToShow;
        });
    };

    const setInlineError = (element, message) => {
        if (!element) return;
        if (!message) {
            element.textContent = '';
            element.classList.add('is-hidden');
            return;
        }

        element.textContent = message;
        element.classList.remove('is-hidden');
    };

    const clearAuthErrors = () => {
        setInlineError(authFormError, '');
        setInlineError(authEmailError, '');
    };

    const renderAuthErrors = (errors) => {
        setInlineError(authFormError, errors.form || '');
        setInlineError(authEmailError, errors.email || '');
    };

    const openAuthModal = (triggerName, triggerElement = null, mode = 'signin') => {
        if (!authModal) return;
        lastAuthTrigger = triggerElement;
        setAuthModalMode(mode, triggerName);
        authModal.hidden = false;
        document.body.classList.add('auth-modal-open');
        const focusField = mode === 'signup'
            ? authModal.querySelector('input[name="name"]')
            : authModal.querySelector('input[name="email"]');
        focusField?.focus();
    };

    const closeAuthModal = () => {
        if (!authModal || authModal.hidden) return;
        authModal.hidden = true;
        document.body.classList.remove('auth-modal-open');
        authModalMode = 'signin';
        setAuthModalMode('signin');
        lastAuthTrigger?.focus();
        lastAuthTrigger = null;
    };

    const menuTrigger = document.querySelector('[data-menu-trigger]');
    const menuPanel = document.querySelector('[data-menu-panel]');
    const scrim = document.querySelector('[data-scrim]');
    const mobileToggle = document.querySelector('[data-mobile-toggle]');
    const mobileMenu = document.querySelector('[data-mobile-menu]');
    const mobileClose = document.querySelector('[data-mobile-close]');

    const setExpanded = (trigger, expanded) => {
        trigger?.setAttribute('aria-expanded', String(expanded));
    };

    const closeMegaMenu = () => {
        if (!menuPanel || !scrim) return;
        menuPanel.hidden = true;
        scrim.hidden = true;
        setExpanded(menuTrigger, false);
    };

    const toggleMegaMenu = () => {
        if (!menuPanel || !scrim) return;
        const shouldOpen = menuPanel.hidden;
        menuPanel.hidden = !shouldOpen;
        scrim.hidden = !shouldOpen;
        setExpanded(menuTrigger, shouldOpen);
    };

    const closeMobileMenu = () => {
        if (!mobileMenu) return;
        mobileMenu.hidden = true;
        document.body.classList.remove('menu-open');
        setExpanded(mobileToggle, false);
        mobileToggle?.focus();
    };

    const openMobileMenu = () => {
        if (!mobileMenu) return;
        mobileMenu.hidden = false;
        document.body.classList.add('menu-open');
        setExpanded(mobileToggle, true);
        mobileClose?.focus();
    };

    menuTrigger?.addEventListener('click', toggleMegaMenu);
    scrim?.addEventListener('click', closeMegaMenu);
    mobileToggle?.addEventListener('click', openMobileMenu);
    mobileClose?.addEventListener('click', closeMobileMenu);
    mobileMenu?.querySelectorAll('a').forEach((link) => link.addEventListener('click', closeMobileMenu));
    authTriggers.forEach((trigger) => {
        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            openAuthModal(trigger.dataset.authTrigger, trigger);
        });
    });
    authSwitches?.forEach((link) => {
        link.addEventListener('click', (event) => {
            event.preventDefault();
            setAuthModalMode(link.dataset.authSwitch, authModalMode);
            const focusField = link.dataset.authSwitch === 'signup'
                ? authModal?.querySelector('input[name="name"]')
                : authModal?.querySelector('input[name="email"]');
            focusField?.focus();
        });
    });
    authCloseButtons?.forEach((button) => button.addEventListener('click', closeAuthModal));
    authForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearAuthErrors();

        if (!authSubmit) return;

        const originalLabel = authSubmit.textContent;
        authSubmit.disabled = true;
        authSubmit.textContent = 'Please wait...';

        try {
            const formData = new FormData(authForm);
            const response = await fetch(authForm.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData,
            });
            const payload = await response.json();

            if (payload.ok) {
                if (payload.redirect) {
                    window.location.href = payload.redirect;
                    return;
                }

                if (payload.message) {
                    setInlineError(authFormError, payload.message);
                }

                return;
            }

            renderAuthErrors(payload.errors || {});
        } catch (error) {
            setInlineError(authFormError, 'Something went wrong. Please try again.');
        } finally {
            authSubmit.disabled = false;
            authSubmit.textContent = originalLabel;
        }
    });
    if (authModal?.dataset.authOpenOnLoad === 'true') {
        document.body.classList.add('auth-modal-open');
        setAuthModalMode(authModal.dataset.authMode || 'signin', 'signin', false);
    }

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') return;
        if (mobileMenu && !mobileMenu.hidden) closeMobileMenu();
        closeAuthModal();
        closeMegaMenu();
    });

    inventoryFilterButtons.forEach((button) => {
        button.addEventListener('click', () => {
            applyFilter(button.dataset.filter);
        });
    });
    inventorySearchInput?.addEventListener('input', () => {
        applyFilter(activeInventoryFilter);
    });

    const requestedFilter = new URLSearchParams(window.location.search).get('filter');
    if (requestedFilter) applyFilter(requestedFilter);

    document.querySelectorAll('[data-save]').forEach((button) => {
        button.addEventListener('click', () => {
            if (button.dataset.authTrigger === 'save') return;
            const saved = button.getAttribute('aria-pressed') !== 'true';
            button.setAttribute('aria-pressed', String(saved));
            button.setAttribute('aria-label', button.getAttribute('aria-label').replace(saved ? 'Save' : 'Saved', saved ? 'Saved' : 'Save'));
        });
    });
})();
