(() => {
    'use strict';

    const config = window.SUPABASE_CONFIG || {};
    const message = document.querySelector('[data-auth-callback-message]');
    const badge = document.querySelector('[data-auth-callback-badge]');
    const status = document.querySelector('[data-auth-callback-status]');
    const primaryAction = document.querySelector('[data-auth-callback-primary]');
    const meta = document.querySelector('[data-auth-callback-meta]');

    const setState = ({ tone, badgeText, messageText, primaryHref, primaryLabel, metaText }) => {
        if (status) status.dataset.authCallbackStatus = tone;
        if (badge) badge.textContent = badgeText;
        if (message) message.textContent = messageText;
        if (primaryAction) {
            primaryAction.href = primaryHref;
            primaryAction.textContent = primaryLabel;
        }
        if (meta) meta.textContent = metaText;
    };

    const params = new URLSearchParams(window.location.search);
    const hashParams = new URLSearchParams(window.location.hash.startsWith('#') ? window.location.hash.slice(1) : window.location.hash);
    const callbackError = params.get('error_description') || params.get('error') || hashParams.get('error_description') || hashParams.get('error');
    const tokenHash = params.get('token_hash');
    const tokenType = params.get('type');
    const accessToken = hashParams.get('access_token');

    const markSuccess = () => {
        window.history.replaceState({}, document.title, window.location.pathname);
        setState({
            tone: 'success',
            badgeText: 'Email confirmed',
            messageText: 'Your email is confirmed and your account is ready. Continue to sign in and start browsing your saved vehicles.',
            primaryHref: 'index.php',
            primaryLabel: 'Continue to sign in',
            metaText: 'You can close this page any time and return to Synapse Motors from the main site.',
        });
    };

    const markError = (errorMessage) => {
        setState({
            tone: 'error',
            badgeText: 'Confirmation failed',
            messageText: errorMessage,
            primaryHref: 'index.php',
            primaryLabel: 'Back to sign in',
            metaText: 'If this link expired, sign up again or request a fresh confirmation email.',
        });
    };

    const verifyConfirmationToken = async () => {
        if (!config.url || !config.anonKey) {
            markError('The email callback is not configured yet. Add your public Supabase settings and try again.');
            return;
        }

        if (callbackError) {
            markError(callbackError);
            return;
        }

        if (tokenHash && tokenType) {
            try {
                const response = await fetch(`${config.url}/auth/v1/verify`, {
                    method: 'POST',
                    headers: {
                        apikey: config.anonKey,
                        Authorization: `Bearer ${config.anonKey}`,
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        token_hash: tokenHash,
                        type: tokenType,
                    }),
                });
                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    markError(payload.error_description || payload.msg || payload.message || 'We could not verify that confirmation link.');
                    return;
                }

                markSuccess();
                return;
            } catch (error) {
                markError('A network error interrupted confirmation. Please try the link again.');
                return;
            }
        }

        if (accessToken) {
            markSuccess();
            return;
        }

        markError('This confirmation link is incomplete or has already been used.');
    };

    verifyConfirmationToken();
})();
