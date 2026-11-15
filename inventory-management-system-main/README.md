# Accounting & Inventory Management System (Lightweight ERP)

A web application built with **Laravel 10** for managing integrated business operations — from inventory/warehouse, purchasing, production, and sales, to accounts receivable & payable, accounting journals, and financial statements (Balance Sheet, Income Statement, Cash Flow).

Built with a **multi-level approval workflow** (Staff submits → Admin approves), making it a great fit for small-to-medium manufacturing/trading businesses that need tight control over financial records and stock accuracy.

## Key Features

- **Inventory & Warehouse Management** — track product stock per warehouse, stock movement history, and stock adjustments.
- **Purchases** — record purchase transactions from suppliers, complete with a submit & approval workflow handled by Admin.
- **Production** — record production batches, raw material usage, and production output, including a submit & approval workflow.
- **Sales** — record sales transactions to customers with Bill of Materials (BOM) rule support and a submit & approval workflow.
- **Accounts Receivable (AR)** — track customer invoices and record incoming payments.
- **Accounts Payable (AP)** — track supplier bills and record outgoing payments/installments.
- **Accounting Journals** — manual journal entries, posting, and voiding of accounting transactions.
- **General Ledger & Trial Balance** — automatic account mutation recap and trial balance generated from journal entries.
- **Financial Statements** — Balance Sheet, Income Statement, and Cash Flow reports.
- **Activity Log (Audit Trail)** — logs all important user activity across the system.
- **Role-Based Access Control** — two main access levels:
  - **Admin**: full access including approvals, journals, financial reports, and activity logs.
  - **Staff**: handles daily operational input (stock, purchases, production, sales, AR/AP payments).
- **Dashboard** — summary of cash, receivables, payables, and sales charts (Admin only).

## Tech Stack

- **Backend:** PHP 8.1+, Laravel 10
- **Database:** MySQL
- **Frontend Build Tool:** Vite
- **Authentication:** Laravel Sanctum
- **Testing:** PHPUnit

## Installation & Setup

### Requirements
- PHP >= 8.1
- Composer
- Node.js & NPM
- MySQL

### Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/Angellica-K/inventory-management-system.git
   cd inventory-management-system
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install JavaScript dependencies**
   ```bash
   npm install
   ```

4. **Configure environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   Update the database configuration (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`) in the `.env` file.

5. **Run migrations & seeders**
   ```bash
   php artisan migrate --seed
   ```

6. **Build frontend assets**
   ```bash
   npm run dev
   ```

7. **Run the local server**
   ```bash
   php artisan serve
   ```

   The application will be accessible at `http://localhost:8000`.

### Default Accounts (after seeding)

| Role  | Username | Password   |
|-------|----------|------------|
| Admin | `admin`  | `password` |
| Staff | `staff`  | `password` |

> Change the default passwords before using this in a production environment.

## Module Structure

```
app/Http/Controllers/
├── DashboardController.php        # Dashboard summary (admin & staff)
├── InventoryController.php        # Product stock data & details
├── StockAdjustmentController.php  # Stock adjustments
├── StockReportController.php      # Warehouse stock reports
├── PurchaseController.php         # Purchasing module
├── ProductionController.php       # Production module
├── SaleController.php             # Sales module
├── ArController.php               # Accounts receivable
├── ApController.php               # Accounts payable
├── JournalController.php          # Manual accounting journals
├── LedgerController.php           # General ledger & trial balance
├── FinancialStatementController.php # Financial statements
└── ActivityLogController.php      # System activity log
```

## Testing

Run unit & feature tests with PHPUnit:
```bash
php artisan test
```

## License

This project was built for learning/academic purposes. Feel free to adjust the license as needed.