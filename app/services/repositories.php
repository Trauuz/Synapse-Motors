<?php

declare(strict_types=1);

require_once __DIR__ . '/../repositories/VehicleRepository.php';

function synapse_uuid(): string
{
    $bytes = random_bytes(16);
    $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
    $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);

    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
}

function user_repository(): UserRepository
{
    static $repository = null;

    if ($repository instanceof UserRepository) {
        return $repository;
    }

    $repository = new UserRepository(DatabaseConnection::get());
    return $repository;
}

function admin_audit_log_repository(): AdminAuditLogRepository
{
    static $repository = null;

    if ($repository instanceof AdminAuditLogRepository) {
        return $repository;
    }

    $repository = new AdminAuditLogRepository(DatabaseConnection::get());
    return $repository;
}

function email_verification_token_repository(): EmailVerificationTokenRepository
{
    static $repository = null;

    if ($repository instanceof EmailVerificationTokenRepository) {
        return $repository;
    }

    $repository = new EmailVerificationTokenRepository(DatabaseConnection::get());
    return $repository;
}

function cart_repository(): CartRepository
{
    static $repository = null;

    if ($repository instanceof CartRepository) {
        return $repository;
    }

    $repository = new CartRepository(DatabaseConnection::get());
    return $repository;
}

function vehicle_repository(): VehicleRepository
{
    static $repository = null;

    if ($repository instanceof VehicleRepository) {
        return $repository;
    }

    $repository = new VehicleRepository(DatabaseConnection::get());

    return $repository;
}

function order_repository(): OrderRepository
{
    static $repository = null;

    if ($repository instanceof OrderRepository) {
        return $repository;
    }

    $repository = new OrderRepository(DatabaseConnection::get());
    return $repository;
}

function email_verification_service(): EmailVerificationService
{
    static $service = null;

    if ($service instanceof EmailVerificationService) {
        return $service;
    }

    $service = new EmailVerificationService(
        user_repository(),
        email_verification_token_repository(),
        new SmtpMailer()
    );

    return $service;
}

function admin_invitation_service(): AdminInvitationService
{
    static $service = null;

    if ($service instanceof AdminInvitationService) {
        return $service;
    }

    $service = new AdminInvitationService(new SmtpMailer());

    return $service;
}
