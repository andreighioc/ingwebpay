# Changelog

All notable changes to `ingwebpay` will be documented in this file.

## [Unreleased]

## [1.0.1] - 2026-04-15
### Added
- Database Migrations & Model (`IngTransaction`) to automatically store transactions.
- Testing Support via `IngWebPay::fake()`.
- Multi-Tenancy support using dynamic credentials via `->withCredentials()`.
- Webhook Authentication Middleware (`VerifyIngWebPayWebhook`).
- Native `PaymentStatus` Enum (PHP 8.1+).
- Two new Artisan Commands: `ingwebpay:check-connection` and `ingwebpay:register-webhook`.
- Advanced specific Exceptions (`IngAuthenticationException`, `IngEndpointUnreachableException`, `IngInvalidSignatureException`).
- Automated HTTP request retry logic (Circuit Breaker) for robustness.

## [1.0.0] - 2026-04-15
- Initial release with support for registering transactions, pre-authorization, order status, reverse, deposit, and refund.
- Added strict typing.
- Migrated to Spatie Package Tools.
