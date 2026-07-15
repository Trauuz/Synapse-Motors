<?php

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/mail.php';
require_once __DIR__ . '/services/DatabaseConnection.php';
require_once __DIR__ . '/repositories/UserRepository.php';
require_once __DIR__ . '/repositories/EmailVerificationTokenRepository.php';
require_once __DIR__ . '/repositories/AdminAuditLogRepository.php';
require_once __DIR__ . '/repositories/CartRepository.php';
require_once __DIR__ . '/repositories/OrderRepository.php';
require_once __DIR__ . '/repositories/VehicleRepository.php';
require_once __DIR__ . '/services/repositories.php';
require_once __DIR__ . '/helpers/session.php';
require_once __DIR__ . '/helpers/auth.php';
require_once __DIR__ . '/helpers/vehicles.php';
require_once __DIR__ . '/helpers/cart.php';
require_once __DIR__ . '/helpers/checkout.php';
require_once __DIR__ . '/helpers/admin_portal.php';
require_once __DIR__ . '/helpers/view.php';
require_once __DIR__ . '/services/AuthValidator.php';
require_once __DIR__ . '/services/LoginRedirectResolver.php';
require_once __DIR__ . '/services/HtmlMailer.php';
require_once __DIR__ . '/services/SmtpMailer.php';
require_once __DIR__ . '/services/EmailVerificationService.php';
require_once __DIR__ . '/services/AdminInvitationService.php';
require_once __DIR__ . '/services/AuthService.php';
require_once __DIR__ . '/controllers/AuthController.php';

app_start_session();
