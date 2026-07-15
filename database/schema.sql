CREATE TABLE IF NOT EXISTS vehicles (
    id VARCHAR(120) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    detail VARCHAR(255) NOT NULL,
    price INT NOT NULL,
    category VARCHAR(255) NOT NULL,
    image VARCHAR(255) NOT NULL,
    width INT NOT NULL,
    height INT NOT NULL,
    alt VARCHAR(255) NOT NULL,
    body VARCHAR(120) NOT NULL,
    powertrain VARCHAR(120) NOT NULL,
    drive VARCHAR(120) NOT NULL,
    availability VARCHAR(120) NOT NULL,
    collection VARCHAR(120) NOT NULL,
    stock_quantity INT NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL
);

CREATE TABLE IF NOT EXISTS users (
    id CHAR(36) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NULL,
    role VARCHAR(32) NOT NULL DEFAULT 'Buyer',
    address TEXT NOT NULL,
    contact_no VARCHAR(80) NOT NULL,
    email_verified_at DATETIME NULL,
    access_status VARCHAR(32) NOT NULL DEFAULT 'active',
    invited_by_user_id CHAR(36) NULL,
    invited_at DATETIME NULL,
    last_seen_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL
);

CREATE TABLE IF NOT EXISTS email_verification_tokens (
    id CHAR(36) PRIMARY KEY,
    user_id CHAR(36) NOT NULL,
    token_hash CHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    consumed_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    INDEX idx_email_verification_tokens_user_id (user_id),
    INDEX idx_email_verification_tokens_expires_at (expires_at)
);

CREATE TABLE IF NOT EXISTS admin_audit_logs (
    id VARCHAR(64) PRIMARY KEY,
    actor_user_id CHAR(36) NOT NULL,
    actor_name VARCHAR(255) NOT NULL,
    actor_email VARCHAR(255) NOT NULL,
    action VARCHAR(255) NOT NULL,
    summary TEXT NOT NULL,
    occurred_at DATETIME NOT NULL,
    INDEX idx_admin_audit_logs_actor_occurred_at (actor_user_id, occurred_at)
);

CREATE TABLE IF NOT EXISTS cart_items (
    id CHAR(36) PRIMARY KEY,
    user_id CHAR(36) NOT NULL,
    vehicle_id VARCHAR(120) NOT NULL,
    quantity INT NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    UNIQUE KEY uniq_cart_user_vehicle (user_id, vehicle_id)
);

CREATE TABLE IF NOT EXISTS orders (
    id CHAR(36) PRIMARY KEY,
    user_id CHAR(36) NOT NULL,
    customer_name VARCHAR(255) NOT NULL,
    customer_email VARCHAR(255) NOT NULL,
    contact_number VARCHAR(80) NOT NULL,
    notes TEXT NOT NULL,
    total_php INT NOT NULL,
    payment_method VARCHAR(64) NULL,
    payment_method_label VARCHAR(120) NULL,
    payment_reference VARCHAR(120) NULL,
    status VARCHAR(32) NOT NULL DEFAULT 'draft',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    submitted_at DATETIME NULL,
    INDEX idx_orders_user_status (user_id, status)
);

CREATE TABLE IF NOT EXISTS order_items (
    id CHAR(36) PRIMARY KEY,
    order_id CHAR(36) NOT NULL,
    vehicle_id VARCHAR(120) NOT NULL,
    vehicle_name VARCHAR(255) NOT NULL,
    vehicle_detail VARCHAR(255) NOT NULL,
    unit_price_php INT NOT NULL,
    quantity INT NOT NULL,
    line_total_php INT NOT NULL,
    created_at DATETIME NOT NULL,
    INDEX idx_order_items_order (order_id)
);
