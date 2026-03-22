# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Editorial Standards Platform (formerly OpenSciRank) — a platform for technical evaluation and visibility of scientific journals and academic books. Journals are evaluated against weighted criteria, scored, and may earn an "Editorial Standards Seal" (1-year validity). Books are indexed for a fee.

The master business logic document is `business-logic.md` at the project root.

## Development Environment

This project uses **Laravel Sail** (Docker). All commands must run through Sail:

```bash
./vendor/bin/sail up -d                    # Start containers (app on port 5000)
./vendor/bin/sail artisan migrate          # Run migrations
./vendor/bin/sail artisan db:seed          # Seed all (Admin, Categories, Criteria, Products)
./vendor/bin/sail npm run dev              # Vite dev server with HMR
./vendor/bin/sail artisan test             # Run PHPUnit tests
./vendor/bin/sail artisan test --filter=AuthenticationTest  # Single test
./vendor/bin/sail composer test:lint       # Pint linter
```

**Key URLs in development:**
- App: `http://localhost:5000`
- Filament Admin: `http://localhost:5000/admin`
- Mailpit (email testing): `http://localhost:8025`

**Admin credentials:** `admin@openscirank.com` / `password`

## Architecture

**Stack:** Laravel 12, Livewire 4, Filament v5, Tailwind CSS 4, Stripe, MySQL, Sail

### Status Machine (Journals)

```
draft → [pay $99] → submitted → [admin evaluates] → evaluated / certified / rejected
                                                    ↘ requires_changes_evaluation → [user pays again] → submitted
       → [free]   → pending_listing → [admin reviews] → listed / rejected
                                                       ↘ requires_changes_listing → [user resubmits] → pending_listing
listed → [pay $99] → submitted (evaluation flow)
certified → seal_status: active → expiring_soon (30d) → expired → status reverts to evaluated
```

Books: `draft → [pay $49] → submitted → pending_listing → listed / rejected / requires_changes_listing`

### Scoring Algorithm

`Journal::calculateScore()` — weighted sum of CriteriaItems. If ANY core indicator (`is_core=true`) fails, score is capped at 49%. Seal requires ≥75% AND all 5 critical indicators met. Score shown as percentage (0-100%), no letter levels.

### Payment Flow (Stripe)

1. User selects plan + addons in `PaymentCheckout` Livewire component
2. `StripePaymentService::createCheckoutSession()` creates Stripe session
3. Stripe redirects to `CheckoutSuccessController` (sync verification)
4. `StripeWebhookController` handles `checkout.session.completed` (async)
5. Payment record created, journal/book status updated to `submitted`
6. `PaymentConfirmed` notification sent

Products identified by `slug`: `journal-evaluation`, `journal-reevaluation`, `seal-renewal-2y`, `book-listing`, `express-evaluation`, `premium-report`, `institutional-pack`

### Notifications (app/Notifications/)

All notifications are **synchronous** (no ShouldQueue) — they send immediately via SMTP. In dev, Mailpit captures all emails at localhost:8025.

Triggered from: `CheckoutSuccessController`, `EvaluateJournal::save()`, `ReviewListing::save()`, `JournalResource` (assign evaluator, notify seal).

### Key Patterns

- **Polymorphic payments:** `Payment.payable_type` → Journal or Book
- **Soft deletes:** Journal and Book models
- **Livewire multi-step wizards:** `SubmissionWizard` (7 steps), `BookSubmissionWizard` (6 steps), auto-save drafts
- **Filament custom pages:** `EvaluateJournal`, `ReviewListing` — not standard CRUD, use `InteractsWithRecord`
- **OAI-PMH harvesting:** `OaiPmhService` with resumption tokens, supports `oai_dc` and `marcxml`

## Key Files

| Area | Location |
|------|----------|
| Models | `app/Models/` — Journal.php is the central model (~80 fillable fields) |
| Livewire | `app/Livewire/` — SubmissionWizard, PaymentCheckout, EditorDashboard, SearchJournals |
| Filament | `app/Filament/Resources/` — JournalResource (with EvaluateJournal, ReviewListing pages) |
| Services | `app/Services/` — StripePaymentService, OaiPmhService |
| Controllers | `app/Http/Controllers/` — CheckoutSuccessController, StripeWebhookController |
| Notifications | `app/Notifications/` — 8 notification classes |
| Routes | `routes/web.php` — all routes; webhook at POST `/stripe/webhook` (CSRF excluded) |
| Seeders | `database/seeders/` — ProductSeeder (7 products), CriteriaItemSeeder (18 indicators in 5 categories) |

## Conventions

- **Language:** UI and code comments in Spanish. Class/method names in English.
- **Branding:** System name is "Editorial Standards Platform" — never expose "Laravel" or "OpenSciRank" in user-facing content.
- **Environment config:** Stripe keys in `.env` (`STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET`). Mail via Mailpit locally, Amazon SES in production.
- **Confirmation modals:** All destructive or state-changing user actions must have `wire:confirm` or `onclick="return confirm(...)"`.
- **Seal management:** Admin-driven via Filament (individual + bulk actions). No cron — admin manually notifies via "Notificar" button.
