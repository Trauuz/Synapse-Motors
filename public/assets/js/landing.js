(() => {
    'use strict';

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

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') return;
        if (mobileMenu && !mobileMenu.hidden) closeMobileMenu();
        closeMegaMenu();
    });

    document.querySelectorAll('[data-filter]').forEach((button) => {
        button.addEventListener('click', () => {
            const selected = button.dataset.filter;
            document.querySelectorAll('[data-filter]').forEach((item) => {
                const active = item === button;
                item.classList.toggle('is-active', active);
                item.setAttribute('aria-pressed', String(active));
            });
            document.querySelectorAll('[data-category]').forEach((card) => {
                card.hidden = selected !== 'all' && !card.dataset.category.split(' ').includes(selected);
            });
        });
    });

    document.querySelectorAll('[data-save]').forEach((button) => {
        button.addEventListener('click', () => {
            const saved = button.getAttribute('aria-pressed') !== 'true';
            button.setAttribute('aria-pressed', String(saved));
            button.setAttribute('aria-label', button.getAttribute('aria-label').replace(saved ? 'Save' : 'Saved', saved ? 'Saved' : 'Save'));
        });
    });
})();
