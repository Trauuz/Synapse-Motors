# Synapse Motors

Synapse Motors is a school-based project developed for academic purposes. This system was created as part of a student project to demonstrate web application development concepts such as user management, authentication, and buyer/admin workflows in an automotive-themed platform.

## Purpose

This project is intended for school use and educational presentation. It serves as a sample application for learning, demonstration, and project evaluation.

**Link:** https://synapse-motors.onrender.com/

## Sample User Accounts

### Admin

Email: `synapsemotors@admin.com`  
Password: `pass`

### Buyer

Email: `synapsemotors@buyer.com`  
Password: `pass`

## System Overview

Synapse Motors provides two main user experiences:

- **Buyer-facing experience** - browse vehicles, view vehicle information, manage a cart, check out, and complete a simulated vehicle reservation.
- **Administrator experience** - view dashboard statistics, manage vehicle inventory, manage administrator access, send invitations, and review reports and the audit log.

## Buyer Features

### 1. Landing Page

The landing page is the main entry point for guests and buyers. It presents featured vehicles, promotional content, and quick navigation throughout the website.

### 2. Expanded Navigation and Vehicle Directory

The expanded navigation organizes vehicle categories and shopping options so users can browse cars based on their preferences.

The Vehicle Directory displays the available vehicles and supports:

- Browsing available vehicles
- Searching for vehicles
- Filtering by category
- Viewing vehicle details
- Adding preferred vehicles to the shopping cart

### 3. About Us

The About Us page provides an overview of Synapse Motors and introduces the developer responsible for designing and building the website.

### 4. Sign In and Sign Up

The authentication interface provides sign-in and sign-up forms. New users can register and verify their accounts before accessing buyer features.

### 5. Email Confirmation

After registration, the system sends an automated email that allows the user to verify their email address and activate the account before accessing the system.

### 6. Cart and Checkout

The Cart page shows the vehicles selected by the buyer and provides controls to:

- Review selected vehicles
- Remove vehicles
- View the order summary
- Proceed to checkout

Guest users do not have access to the cart page.

The Checkout page collects buyer contact information, reviews the selected vehicles, and confirms the order details before continuing to the payment step.

### 7. Payment and Completed Order

The Payment page provides a simulated checkout process. Users can select a payment method, review the reservation summary, and confirm a vehicle reservation without using an actual payment gateway.

After confirmation, the Completed Order modal displays the reservation reference and summary and allows the user to continue browsing or close the confirmation dialog.

## Administrator Features

### 1. Admin Dashboard

The Admin Dashboard is the landing page for users with the Administrator role after a successful login. It provides an overview of the system, including:

- Inventory statistics
- Administrator accounts
- Recent activities
- Quick access to inventory management
- Quick access to reports

### 2. Inventory Management

The Inventory page allows administrators to manage the vehicle catalog by:

- Adding vehicle listings
- Editing vehicle listings
- Deleting vehicle listings
- Updating prices
- Updating stock levels
- Monitoring vehicle availability

### 3. Admin Users and Email Invitation

The Admin Users page enables administrators to manage administrator accounts by:

- Inviting new administrators
- Monitoring email verification status
- Updating account information
- Managing administrator access

The email invitation workflow sends an invitation to the recipient, who can use the invited email address to register and activate the administrator account.

### 4. Reports and Audit Log

The Reports page provides an overview of the current inventory status and a comprehensive audit log that records activities performed by administrator accounts within the system.

## Main Workflow

```text
Guest / Buyer
    |
    v
Landing Page
    |
    +--> Vehicle Directory --> Vehicle Selection --> Cart
    |                                      |
    |                                      v
    |                                  Checkout
    |                                      |
    |                                      v
    |                               Payment (Simulated)
    |                                      |
    |                                      v
    |                              Completed Order
    |
    +--> Sign In / Sign Up --> Email Confirmation --> Buyer Features

Administrator
    |
    v
Admin Dashboard
    |
    +--> Inventory Management
    +--> Admin Users / Invitations
    +--> Reports / Audit Log
```

## Feature Summary

| Area | Functionality |
| --- | --- |
| Landing Page | Entry point, featured vehicles, promotional content, navigation |
| Vehicle Directory | Browse, search, filter, view details, add to cart |
| Authentication | Sign in, sign up, account verification |
| Email Confirmation | Activate registered user accounts |
| Cart | Review and manage selected vehicles |
| Checkout | Collect buyer details and confirm order information |
| Payment | Simulated payment/reservation process |
| Order Completion | Reservation confirmation and reference details |
| Admin Dashboard | System overview, statistics, recent activity, quick access |
| Inventory | Add, edit, delete vehicles, update price/stock, monitor availability |
| Admin Users | Manage administrator access and invitations |
| Reports | Inventory overview and administrator audit log |

## Academic Project

Synapse Motors is presented as a school-based project for demonstration, learning, and evaluation. The project documentation focuses on the application's user interface, buyer workflow, authentication flow, and administrator management features.
