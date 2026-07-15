<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/app/bootstrap.php';

require_admin();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || !verify_csrf_token($_POST['_csrf'] ?? null)) {
    header('Location: ../admins.php');
    exit;
}

$action = trim((string) ($_POST['admin_action'] ?? ''));

try {
    if ($action === 'invite') {
        $name = trim((string) ($_POST['name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));

        if ($name === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new RuntimeException('Enter a valid name and email before inviting an administrator.');
        }

        $invitedAdmin = invite_admin_user($name, $email);
        $currentAdmin = current_admin_account();
        $inviterName = is_array($currentAdmin) ? (string) ($currentAdmin['name'] ?? 'Synapse Motors administrator') : 'Synapse Motors administrator';
        admin_invitation_service()->sendForAdmin($invitedAdmin, $inviterName);
        record_admin_activity('Invited administrator', 'Invited ' . (string) ($invitedAdmin['email'] ?? $email) . ' to seller access.');
        flash_set('admin_feedback', ['tone' => 'success', 'message' => 'Administrator invite saved and email sent.']);
        header('Location: ../admins.php');
        exit;
    }

    if ($action === 'update') {
        $adminId = trim((string) ($_POST['admin_id'] ?? ''));

        if ($adminId === '') {
            throw new RuntimeException('Select an administrator to update.');
        }

        $updatedAdmin = update_admin_user($adminId, [
            'name' => trim((string) ($_POST['name'] ?? '')),
            'access_status' => trim((string) ($_POST['access_status'] ?? 'active')),
        ]);
        record_admin_activity('Updated administrator', 'Updated ' . (string) ($updatedAdmin['email'] ?? 'administrator') . ' (' . (string) ($updatedAdmin['access_status'] ?? 'active') . ').');

        flash_set('admin_feedback', ['tone' => 'success', 'message' => 'Administrator access updated.']);
        header('Location: ../admins.php');
        exit;
    }

    throw new RuntimeException('Unsupported administrator action.');
} catch (RuntimeException $exception) {
    flash_set('admin_feedback', ['tone' => 'error', 'message' => $exception->getMessage()]);
    header('Location: ../admins.php');
    exit;
}
