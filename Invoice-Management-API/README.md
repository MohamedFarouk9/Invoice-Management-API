# Invoice Management API - Project Documentation

## **Table of Contents**
1. [Project Overview](#project-overview)
2. [Architecture & Design](#architecture--design)
3. [Database Schema](#database-schema)
4. [Quick Start Guide](#quick-start-guide)
5. [Implementation Batches](#implementation-batches)
6. [API Endpoints](#api-endpoints)
7. [Code Structure](#code-structure)
8. [Key Technologies](#key-technologies)

---

## **Project Overview**

### Purpose
Build a professional-grade Invoice Management API for a real estate management platform. The system handles:
- ✅ Invoice creation from rental contracts
- ✅ Multi-tenant data isolation
- ✅ Flexible tax calculation (VAT, Municipal Fee, extensible)
- ✅ Payment tracking with automatic status transitions
- ✅ Financial summaries and reporting

### Core Features
| Feature | Description |
|---------|-------------|
| **Multi-Tenancy** | Complete data isolation per tenant |
| **Invoice Management** | Create, read, list, and track invoices |
| **Payment Recording** | Record payments with automatic status updates |
| **Tax Calculation** | Pluggable tax strategies (VAT, Municipal, etc.) |
| **Authorization** | Policy-based access control per tenant |
| **Type Safety** | PHP 8.1+ Backed Enums for all statuses |

### Technology Stack
- **Framework:** Laravel 11+
- **Database:** MySQL 8.0+
- **PHP Version:** 8.1+
- **API Format:** JSON REST
- **Testing:** PHPUnit + Pest
- **Code Quality:** PHPStan, PHPCS

---

## **Architecture & Design**

### Layered Architecture Diagram
```
┌─────────────────────────────────────────────┐
│         HTTP Request / Response             │
├─────────────────────────────────────────────┤
│  Form Request (Validation & Authorization)  │
├─────────────────────────────────────────────┤
│       Controller (Orchestration)            │
│  • Create DTO from request                  │
│  • Call $this->authorize()                  │
│  • Delegate to Service                      │
│  • Return API Resource                      │
├─────────────────────────────────────────────┤
│      DTO (Immutable Data Transfer)          │
│  • No logic, no Eloquent                    │
│  • fromRequest() factory method             │
├─────────────────────────────────────────────┤
│     Policy (Authorization Logic)            │
│  • Can user perform action?                 │
│  • Tenant-level checks                      │
├─────────────────────────────────────────────┤
│     Service (Business Logic)                │
│  • Validation rules                         │
│  • Calculations                             │
│  • Orchestration                            │
│  • Transaction management                   │
├─────────────────────────────────────────────┤
│   Repository (Data Access Layer)            │
│  • Only layer touching database             │
│  • Eloquent abstraction                     │
│  • Query optimization (eager loading)       │
├─────────────────────────────────────────────┤
│  API Resource (Response Formatting)         │
│  • Transform models to JSON                 │
│  • Conditional relationships                │
│  • Computed fields                          │
├─────────────────────────────────────────────┤
│        Eloquent Models & Database           │
└─────────────────────────────────────────────┘
```

### Design Patterns Applied

| Pattern | Where Applied | Benefit |
|---------|-------------|---------|
| **Strategy Pattern** | Tax Calculators | Extensible, pluggable behavior |
| **Repository Pattern** | Data Access | Database abstraction, testability |
| **DTO Pattern** | Inter-layer Communication | Type safety, immutability |
| **Policy Pattern** | Authorization | Centralized access control |
| **Observer Pattern** | Side Effects | Decoupled logging/events |
| **Decorator Pattern** | Caching (Bonus) | Add behavior without modifying |
| **Service Locator** | Service Container | Dependency injection |

### SOLID Principles

```
S - Single Responsibility
  ✓ Service = business logic only
  ✓ Repository = data access only
  ✓ Controller = orchestration only

O - Open/Closed
  ✓ Add new tax type without modifying existing code
  ✓ Tax calculators are closed for modification, open for extension

L - Liskov Substitution
  ✓ Any TaxCalculatorInterface implementation works identically

I - Interface Segregation
  ✓ Repositories have focused interfaces
  ✓ Services depend only on needed methods

D - Dependency Inversion
  ✓ Services depend on ContractRepositoryInterface
  ✓ Container injects EloquentContractRepository
```

---

## **Database Schema**

### Models Overview

```
┌──────────────┐       ┌──────────────┐       ┌──────────────┐
│   Contract   │◄──────│   Invoice    │◄──────│   Payment    │
├──────────────┤       ├──────────────┤       ├──────────────┤
│ id           │       │ id           │       │ id           │
│ tenant_id    │       │ contract_id  │       │ invoice_id   │
│ unit_name    │       │ invoice_no   │       │ amount       │
│ customer_name│       │ subtotal     │       │ method       │
│ rent_amount  │       │ tax_amount   │       │ reference    │
│ start_date   │       │ total        │       │ paid_at      │
│ end_date     │       │ status       │       │ created_at   │
│ status       │       │ due_date     │       │ updated_at   │
│ created_at   │       │ paid_at      │       └──────────────┘
│ updated_at   │       │ created_at   │
└──────────────┘       │ updated_at   │
                       └──────────────┘

Relationships:
- Contract has many Invoices
- Invoice belongs to Contract
- Invoice has many Payments
- Payment belongs to Invoice
```

### Status Enums

```php
// Contract Status
enum ContractStatus: string {
    case DRAFT = 'draft';
    case ACTIVE = 'active';
    case EXPIRED = 'expired';
    case TERMINATED = 'terminated';
}

// Invoice Status
enum InvoiceStatus: string {
    case PENDING = 'pending';
    case PARTIALLY_PAID = 'partially_paid';
    case PAID = 'paid';
    case OVERDUE = 'overdue';
    case CANCELLED = 'cancelled';
}

// Payment Method
enum PaymentMethod: string {
    case CASH = 'cash';
    case BANK_TRANSFER = 'bank_transfer';
    case CREDIT_CARD = 'credit_card';
}
```

### Database Relationships

**Contract Model:**
```php
public function invoices(): HasMany { ... }
public function payments(): HasManyThrough { ... }  // via invoices
```

**Invoice Model:**
```php
public function contract(): BelongsTo { ... }
public function payments(): HasMany { ... }
```

**Payment Model:**
```php
public function invoice(): BelongsTo { ... }
public function contract(): BelongsTo { ... }  // via invoice
```

---

## **Quick Start Guide**

### Prerequisites
- PHP 8.1+
- Composer
- MySQL 8.0+
- Git

### Installation Steps

```bash
# 1. Clone repository (or navigate to existing project)
cd d:\Invoice-Management-API

# 2. Install dependencies
composer install

# 3. Copy environment file
cp .env.example .env

# 4. Generate application key
php artisan key:generate

# 5. Create database
# Edit .env with your database credentials, then:
php artisan migrate --seed

# 6. Run tests to verify setup
php artisan test

# 7. Start development server
php artisan serve  # http://localhost:8000
```

### Environment Configuration (.env)
```
APP_NAME="Invoice Management API"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=invoice_api
DB_USERNAME=root
DB_PASSWORD=

CACHE_DRIVER=redis
QUEUE_CONNECTION=database
```

---

## **Implementation Batches**

### Batch Overview & Dependencies

```
Batch 1: Setup & Database
├── ✓ Laravel scaffolding
├── ✓ Composer dependencies
├── ✓ Database configuration
└── ✓ Models with migrations
    ↓
    Dependencies for all other batches

Batch 2: DTOs & Tax System
├── ✓ CreateInvoiceDTO
├── ✓ RecordPaymentDTO
├── ✓ TaxCalculatorInterface & implementations
└── ✓ TaxService
    ↓
    Dependencies: Batch 1 (models)

Batch 3: Repositories
├── ✓ ContractRepository
├── ✓ InvoiceRepository
├── ✓ PaymentRepository
└── ✓ Service provider bindings
    ↓
    Dependencies: Batch 1

Batch 4: Policies & Services
├── ✓ InvoicePolicy (authorization)
├── ✓ InvoiceService (business logic)
└── ✓ Service provider bindings
    ↓
    Dependencies: Batch 1, 2, 3

Batch 5: Form Requests
├── ✓ StoreInvoiceRequest
└── ✓ RecordPaymentRequest
    ↓
    Dependencies: Batch 1

Batch 6: Controllers
├── ✓ InvoiceController
├── ✓ All endpoints
└── ✓ Routes definition
    ↓
    Dependencies: Batch 4, 5

Batch 7: API Resources
├── ✓ InvoiceResource
├── ✓ PaymentResource
└── ✓ ContractSummaryResource
    ↓
    Dependencies: Batch 1, 6

Batch 8: Bonus Features (Optional)
├── ✓ Observers/Events
├── ✓ Global Scopes
├── ✓ Custom Exceptions
├── ✓ Artisan Commands
├── ✓ Caching Decorator
└── ✓ Pagination & Filtering
    ↓
    Dependencies: Batch 1-7

Batch 9: Testing & Polish
├── ✓ Unit tests
├── ✓ Feature tests
├── ✓ API documentation
└── ✓ Performance optimization
    ↓
    Dependencies: All batches
```

---

## **API Endpoints**

### Base URL
```
http://localhost:8000/api
```

### Authentication
All endpoints require authentication and multi-tenant isolation.
```
Header: Authorization: Bearer {token}
```

### Invoice Endpoints

#### 1. Create Invoice
```http
POST /contracts/{contract_id}/invoices

Request Body:
{
    "due_date": "2026-03-31"
}

Response (201 Created):
{
    "data": {
        "id": 1,
        "invoice_number": "INV-001-202602-0001",
        "subtotal": 1500.00,
        "tax_amount": 262.50,
        "total": 1762.50,
        "status": "pending",
        "due_date": "2026-03-31",
        "paid_at": null,
        "remaining_balance": 1762.50,
        "contract": {
            "id": 1,
            "unit_name": "Unit A",
            "customer_name": "John Doe"
        }
    }
}

Error Responses:
- 422: Contract not found / not active / invalid due_date
- 403: User not authorized for this contract
```

#### 2. List Invoices for Contract
```http
GET /contracts/{contract_id}/invoices?status=pending&per_page=20

Response (200 OK):
{
    "data": [
        {
            "id": 1,
            "invoice_number": "INV-001-202602-0001",
            "subtotal": 1500.00,
            ...
        }
    ],
    "links": { ... },
    "meta": {
        "current_page": 1,
        "total": 5,
        "per_page": 20
    }
}
```

#### 3. Get Invoice Details
```http
GET /invoices/{invoice_id}

Response (200 OK):
{
    "data": {
        "id": 1,
        "invoice_number": "INV-001-202602-0001",
        "subtotal": 1500.00,
        "tax_amount": 262.50,
        "total": 1762.50,
        "status": "partially_paid",
        "due_date": "2026-03-31",
        "paid_at": null,
        "remaining_balance": 1000.00,
        "contract": { ... },
        "payments": [
            {
                "id": 1,
                "amount": 762.50,
                "method": "bank_transfer",
                "reference_number": "TXN-123456",
                "paid_at": "2026-03-15"
            }
        ]
    }
}
```

#### 4. Record Payment
```http
POST /invoices/{invoice_id}/payments

Request Body:
{
    "amount": 500.00,
    "payment_method": "bank_transfer",
    "reference_number": "TXN-789456"
}

Response (201 Created):
{
    "data": {
        "id": 2,
        "amount": 500.00,
        "method": "bank_transfer",
        "reference_number": "TXN-789456",
        "paid_at": "2026-03-16T10:30:00Z"
    }
}

Invoice Status Auto-Update:
- If payment == remaining balance → status = "paid"
- If 0 < payment < remaining balance → status = "partially_paid"

Error Responses:
- 422: Amount exceeds remaining balance / invalid method
- 403: Cannot pay cancelled/overdue invoice
- 404: Invoice not found
```

#### 5. Contract Financial Summary
```http
GET /contracts/{contract_id}/summary

Response (200 OK):
{
    "data": {
        "contract_id": 1,
        "total_invoiced": 7525.00,
        "total_paid": 2500.00,
        "outstanding_balance": 5025.00,
        "invoices_count": 5,
        "latest_invoice_date": "2026-02-28"
    }
}
```

### Query Parameters

#### Filtering
```
GET /contracts/1/invoices?status=pending
GET /contracts/1/invoices?status=paid,partially_paid
GET /contracts/1/invoices?date_from=2026-01-01&date_to=2026-02-28
```

#### Pagination
```
GET /contracts/1/invoices?per_page=50&page=2
```

#### Eager Loading
```
GET /invoices/1?include=contract,payments
```

---

## **Code Structure**

### Directory Organization
```
app/
├── Enums/
│   ├── ContractStatus.php
│   ├── InvoiceStatus.php
│   └── PaymentMethod.php
│
├── Models/
│   ├── Contract.php
│   ├── Invoice.php
│   ├── Payment.php
│   └── User.php
│
├── DTOs/
│   ├── CreateInvoiceDTO.php
│   └── RecordPaymentDTO.php
│
├── Http/
│   ├── Controllers/
│   │   └── InvoiceController.php
│   │
│   ├── Requests/
│   │   ├── StoreInvoiceRequest.php
│   │   └── RecordPaymentRequest.php
│   │
│   └── Resources/
│       ├── InvoiceResource.php
│       ├── PaymentResource.php
│       └── ContractSummaryResource.php
│
├── Services/
│   ├── InvoiceService.php
│   └── TaxService.php
│
├── Repositories/
│   ├── Contracts/
│   │   ├── ContractRepositoryInterface.php
│   │   └── EloquentContractRepository.php
│   ├── Invoices/
│   │   ├── InvoiceRepositoryInterface.php
│   │   └── EloquentInvoiceRepository.php
│   └── Payments/
│       ├── PaymentRepositoryInterface.php
│       └── EloquentPaymentRepository.php
│
├── Policies/
│   └── InvoicePolicy.php
│
├── Tax/
│   ├── TaxCalculatorInterface.php
│   ├── VatTaxCalculator.php
│   └── MunicipalFeeTaxCalculator.php
│
├── Exceptions/
│   ├── ContractNotActiveException.php
│   └── InsufficientBalanceException.php
│
├── Observers/
│   └── InvoiceObserver.php
│
├── Events/
│   ├── InvoiceCreated.php
│   └── PaymentRecorded.php
│
├── Listeners/
│   ├── LogInvoiceCreated.php
│   └── LogPaymentRecorded.php
│
├── Scopes/
│   └── TenantScope.php
│
├── Commands/
│   └── MarkOverdueInvoices.php
│
└── Providers/
    ├── AppServiceProvider.php
    ├── RepositoryServiceProvider.php
    └── TaxServiceProvider.php

database/
├── migrations/
│   ├── 2026_02_23_000000_create_users_table.php
│   ├── 2026_02_23_000001_create_contracts_table.php
│   ├── 2026_02_23_000002_create_invoices_table.php
│   └── 2026_02_23_000003_create_payments_table.php
│
├── factories/
│   ├── UserFactory.php
│   ├── ContractFactory.php
│   ├── InvoiceFactory.php
│   └── PaymentFactory.php
│
└── seeders/
    ├── DatabaseSeeder.php
    ├── UserSeeder.php
    └── ContractSeeder.php

routes/
├── api.php          # API routes
└── console.php      # Artisan commands

tests/
├── Unit/
│   ├── Services/
│   │   └── InvoiceServiceTest.php
│   ├── Repositories/
│   │   └── InvoiceRepositoryTest.php
│   └── Tax/
│       └── TaxServiceTest.php
│
└── Feature/
    ├── InvoiceControllerTest.php
    └── PaymentControllerTest.php
```

---

## **Key Technologies**

### Laravel 11+
- **Eloquent ORM** for database interactions
- **Query Builder** for complex queries
- **Migrations** for schema management
- **Form Requests** for validation
- **Policies** for authorization
- **Service Container** for dependency injection
- **Events & Listeners** for side effects
- **Artisan CLI** for commands

### Database
- **MySQL 8.0+** with InnoDB engine
- **Transactions** for data consistency
- **Indexed columns** for performance
- **Foreign keys** for referential integrity

### PHP Features (8.1+)
- **Named Arguments** for clarity
- **Readonly Properties** in DTOs
- **Enum Backed Types** for status fields
- **Match Expressions** for logic flow
- **Constructor Property Promotion** for DRY code

### Testing Framework
- **PHPUnit** for unit tests
- **Pest** for feature tests
- **Mockery** for mocking dependencies
- **Factories** for test data

### Code Quality Tools
- **PHPStan** for static analysis
- **PHPCS** for coding standards
- **Laravel Pint** for code formatting
- **Psalm** for type checking

---

## **Common Development Tasks**

### Running Tests
```bash
# All tests
php artisan test

# Specific test file
php artisan test tests/Feature/InvoiceControllerTest.php

# With coverage
php artisan test --coverage

# Watch mode (re-run on file change)
php artisan test --watch
```

### Database Operations
```bash
# Create migration
php artisan make:migration create_invoices_table

# Run migrations
php artisan migrate

# Rollback migrations
php artisan migrate:rollback

# Reset database
php artisan migrate:refresh

# Seed database
php artisan db:seed
```

### Artisan Commands
```bash
# Create model with migration
php artisan make:model Invoice -m

# Create controller
php artisan make:controller InvoiceController

# Create form request
php artisan make:request StoreInvoiceRequest

# Create policy
php artisan make:policy InvoicePolicy

# Create service provider
php artisan make:provider TaxServiceProvider
```

### Code Quality
```bash
# Static analysis
./vendor/bin/phpstan analyse

# Code style check
./vendor/bin/phpcs app/

# Code style fix
./vendor/bin/php-cs-fixer fix app/

# Type checking
./vendor/bin/psalm
```

---

## **Checklist for Completion**

### ✅ Core Implementation
- [ ] Batch 1: Models, Migrations, Enums, Timestamps
- [ ] Batch 2: DTOs with readonly properties & fromRequest()
- [ ] Batch 2: Tax Calculators with interface
- [ ] Batch 3: Repository interfaces & Eloquent implementations
- [ ] Batch 4: InvoicePolicy with authorization logic
- [ ] Batch 4: InvoiceService with all business logic
- [ ] Batch 5: Form Request validation
- [ ] Batch 6: Thin controllers, all endpoints
- [ ] Batch 7: API Resources with full transformation

### ✅ Data Integrity
- [ ] Invoice auto-generation: INV-{TENANT}-{YYYYMM}-{SEQUENCE}
- [ ] Status transitions: pending → partially_paid → paid
- [ ] Payment recording in transaction
- [ ] Contract validation (must be active)
- [ ] Balance validation (cannot overpay)

### ✅ Multi-Tenancy
- [ ] Policy authorization on all endpoints
- [ ] Global scope on models (optional)
- [ ] Tenant ID in DTO creation
- [ ] Test tenant isolation

### ✅ Bonus Features
- [ ] Observer for event logging
- [ ] Custom Exception classes
- [ ] Artisan command for overdue invoices
- [ ] Caching layer for contract summary
- [ ] Pagination & filtering on list endpoint
- [ ] Error handling & HTTP status codes

### ✅ Code Quality
- [ ] Follows SOLID principles
- [ ] Proper PHPDoc comments
- [ ] Type hints on all methods
- [ ] Consistent naming conventions
- [ ] No code duplication

### ✅ Testing
- [ ] Unit tests for Service layer
- [ ] Feature tests for API endpoints
- [ ] Policy authorization tests
- [ ] Tax calculation tests
- [ ] Multi-tenancy tests
- [ ] Test coverage > 80%

### ✅ Documentation
- [ ] README with setup instructions
- [ ] API documentation (Swagger/OpenAPI)
- [ ] Code comments on complex logic
- [ ] PHPDoc for all classes
- [ ] Migration documentation

---

## **Performance Considerations**

### Database
- Index frequently queried columns (tenant_id, status, due_date)
- Use eager loading to prevent N+1 queries
- Paginate large result sets
- Use connection pooling in production

### Caching
- Cache tax calculation (rates rarely change)
- Cache contract summary (invalidate on payment)
- Use Redis for session and cache
- Implement cache expiration strategies

### API
- Limit request size (JSON payload)
- Implement rate limiting per tenant
- Compress responses (gzip)
- Use CDN for static assets

### Monitoring
- Log all API requests and responses
- Monitor error rates
- Track transaction times
- Alert on failed payments

---

## **Security Considerations**

### Input Validation
- All user input validated via Form Request
- Type casting in DTO
- Amount validation (cannot overpay)

### Authorization
- Policy checked before Service execution
- Tenant ID isolation on all queries
- HTTP status 403 for unauthorized access

### Data Protection
- Payments stored securely
- Sensitive data not in logs
- HTTPS enforced in production
- Payment reference (not full card/account)

### Database
- Prepared statements (Eloquent ORM)
- Input parameterization
-Foreign key constraints
- Regular backups

---

## **Getting Help**

### Debugging
1. Check logs: `storage/logs/laravel.log`
2. Use Tinker: `php artisan tinker`
3. Enable query logging in config/database.php
4. Use Laravel Debugbar in development

### Common Issues

**Problem: Migrations fail**
```bash
# Solution: Check database connection
php artisan migrate --seed --fresh
```

**Problem: 403 Unauthorized responses**
```bash
# Solution: Check InvoicePolicy authorization
php artisan tinker
> auth()->login(User::first());
> User::first()->tenant_id
```

**Problem: Invoice numbers not sequential**
```bash
# Solution: Use cache or counter table instead of querying last invoice
```

---

## **Next Steps**

1. **Start with Batch 1:** Set up Laravel project and database structure
2. **Follow batches sequentially:** Each batch depends on previous ones
3. **Run tests after each batch:** Ensure nothing breaks
4. **Deploy to staging:** Test entire flow before production
5. **Monitor production:** Watch error logs and performance metrics

---

**Ready to implement? Start with Batch 1! 🚀**
