<?php
$defaultAuthState = [
    'mode' => 'signin',
    'errors' => [],
    'old' => [],
    'message' => null,
    'open' => false,
];

$authState = function_exists('auth_form_state') ? auth_form_state() : $defaultAuthState;
$authErrors = is_array($authState['errors'] ?? null) ? $authState['errors'] : [];
$authOld = is_array($authState['old'] ?? null) ? $authState['old'] : [];
$authMode = ($authState['mode'] ?? 'signin') === 'signup' ? 'signup' : 'signin';
$authMessage = is_string($authState['message'] ?? null) ? $authState['message'] : null;
$authShouldOpen = ($authState['open'] ?? false) === true;
$authFormError = is_string($authErrors['form'] ?? null) ? $authErrors['form'] : null;
$authEmailError = is_string($authErrors['email'] ?? null) ? $authErrors['email'] : null;
$authOpenOnLoad = $authShouldOpen ? 'true' : 'false';
$authShellHidden = $authShouldOpen ? '' : 'hidden';

$fieldValue = static function (string $key) use ($authOld): string {
    return e((string) ($authOld[$key] ?? ''));
};
?>

<div class="auth-modal-shell" data-auth-modal data-auth-mode="<?= e($authMode) ?>"
    data-auth-open-on-load="<?= $authOpenOnLoad ?>" <?= $authShellHidden ?>>
    <div class="auth-modal-backdrop" data-auth-close></div>
    <section class="auth-modal" role="dialog" aria-modal="true" aria-labelledby="auth-modal-title">
        <button class="auth-modal-close" type="button" aria-label="Close sign in modal" data-auth-close>
            <span aria-hidden="true">&times;</span>
        </button>
        <div class="auth-modal-copy">
            <p class="section-kicker" data-auth-kicker>Member sign in</p>
            <h2 id="auth-modal-title" data-auth-title>Sign in to your Synapse Motors account.</h2>
        </div>
        <?php if ($authMessage !== null && $authMessage !== ''): ?>
        <p class="auth-feedback" data-auth-feedback><?= e($authMessage) ?></p>
        <?php endif; ?>
        <form class="auth-modal-form" method="post" action="auth/submit.php" data-auth-form>
            <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="auth_action" value="<?= e($authMode) ?>" data-auth-action>
            <input type="hidden" name="redirect_to" value="<?= e(current_request_path()) ?>" data-auth-redirect>
            <div class="auth-form-error<?= $authFormError === null ? ' is-hidden' : '' ?>" data-auth-form-error
                role="alert">
                <?= $authFormError === null ? '' : e($authFormError) ?>
            </div>
            <label class="auth-field auth-field-wide" data-auth-name-field hidden>
                <span>Full name</span>
                <input type="text" name="name" autocomplete="name" placeholder="Enter your full name"
                    value="<?= $fieldValue('name') ?>">
            </label>
            <label class="auth-field">
                <span>Email address</span>
                <input type="email" name="email" autocomplete="email" placeholder="name@example.com"
                    value="<?= $fieldValue('email') ?>">
                <?php if ($authEmailError !== null): ?>
                <small class="auth-field-error" data-auth-email-error><?= e($authEmailError) ?></small>
                <?php else: ?>
                <small class="auth-field-error is-hidden" data-auth-email-error></small>
                <?php endif; ?>
            </label>
            <label class="auth-field" data-auth-contact-field hidden>
                <span>Contact numbers</span>
                <input type="tel" name="contact_number" autocomplete="tel"
                    placeholder="Enter your preferred contact number"
                    value="<?= $fieldValue('contact_number') ?>">
            </label>
            <label class="auth-field auth-field-wide">
                <span>Password</span>
                <input type="password" name="password" autocomplete="current-password"
                    placeholder="Enter your password">
                <a href="index.php#visit" data-auth-forgot-link>Forgot password?</a>
            </label>
            <label class="auth-field auth-field-wide" data-auth-confirm-field hidden>
                <span>Confirm password</span>
                <input type="password" name="password_confirmation" autocomplete="new-password"
                    placeholder="Re-enter your password">
            </label>
            <label class="auth-field auth-field-wide" data-auth-address-field hidden>
                <span>Complete address</span>
                <textarea name="complete_address" rows="3" autocomplete="street-address"
                    placeholder="Street, barangay, city, province, and postal code"><?= $fieldValue('complete_address') ?></textarea>
            </label>
            <label class="auth-checkbox" data-auth-remember-row>
                <input type="checkbox" name="remember" checked>
                <span>Keep me signed in on this device</span>
            </label>
            <button class="auth-submit" type="submit" data-auth-submit>Sign in</button>
        </form>
        <div class="auth-modal-links">
            <a href="#" data-auth-switch="signup">Create an account</a>
            <a href="#" data-auth-switch="signin" hidden>Already have an account? Sign in</a>
        </div>
    </section>
</div>
