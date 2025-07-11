# Accounting Management System

A comprehensive Laravel-based accounting and financial management system designed to handle ledgers, vouchers, chart of accounts, customer management, and entity relationships.

## Features

### Core Accounting Features
- **Chart of Accounts Management** - Complete hierarchical chart of accounts with asset, liability, equity, income, and expense categories
- **Ledger Management** - Create and manage ledgers with detailed transaction history
- **Voucher System** - Create, edit, post, and cancel financial vouchers
- **Transaction Processing** - Record and track all financial transactions
- **PDF Export** - Generate PDF reports for ledgers and financial statements

### Entity Management
- **Customer Management** - Comprehensive customer records with statements and document uploads
- **Party Management** - Handle various business entities (vendors, suppliers, employees)
- **Entity Configuration** - Configure how different entity types interact with the accounting system
- **Automated Ledger Creation** - Automatically create appropriate ledgers when new entities are added

### User & Access Control
- **Role-Based Access Control** - Using Spatie Laravel Permission package
- **User Management** - Create and manage users with specific roles and permissions
- **Permission System** - Granular permission control for different system features

### Reporting & Export
- **Financial Statements** - Generate balance sheets, income statements, and other reports
- **Customer Statements** - Detailed customer account statements
- **Export Capabilities** - Export data to Excel and PDF formats
- **Activity Logging** - Track all system activities using Spatie Activity Log

## Technology Stack

- **Backend**: Laravel 12.x (PHP 8.2+)
- **Database**: MySQL/SQLite
- **PDF Generation**: DomPDF
- **Permissions**: Spatie Laravel Permission
- **Activity Logging**: Spatie Laravel Activity Log
- **Frontend**: Blade templates with Bootstrap
- **Development Tools**: Laravel Sail, Pint, Pail

## Requirements

- PHP 8.2 or higher
- Composer
- Node.js & NPM
- MySQL 8.0+ or SQLite (for development)

## Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd accounting-system
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**
   ```bash
   npm install
   ```

4. **Environment Setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure Database**
   Edit `.env` file with your database credentials:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=accounting_system
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

6. **Run Migrations and Seeders**
   ```bash
   php artisan migrate --seed
   ```

7. **Build Frontend Assets**
   ```bash
   npm run build
   ```

## Development

### Using Laravel Sail (Recommended)
```bash
# Start the development environment
composer run dev
```
This command will start:
- Laravel development server
- Queue worker
- Log viewer (Pail)
- Vite development server

### Manual Setup
```bash
# Start the development server
php artisan serve

# In separate terminals:
php artisan queue:listen --tries=1
php artisan pail --timeout=0
npm run dev
```

## Key Modules

### 1. Chart of Accounts
- Hierarchical account structure
- Asset, Liability, Equity, Income, and Expense categories
- Automated account code generation
- Parent-child relationships

### 2. Ledger Management
- Individual ledgers for each account
- Transaction history tracking
- Balance calculations
- Export functionality

### 3. Voucher System
- Create various types of vouchers (sales, purchase, expense, etc.)
- Post vouchers to update ledgers
- Cancel and modify vouchers
- Print voucher receipts

### 4. Entity Management
- Configure entity types (customers, vendors, employees, suppliers)
- Define chart of account mappings
- Set voucher type associations
- Automated ledger assignment

### 5. Customer Management
- Complete customer profiles
- Transaction history
- Document management
- Customer statements

## API Endpoints

### Entity Management API
- `GET /api/entity-management/entity-creation-head` - Get entity creation configuration
- `GET /api/entity-management/voucher-ledger` - Get voucher ledger mapping
- `GET /api/accounts/type/{type}` - Get accounts by type

## Configuration

### Entity Management Setup
The system uses entity management records to automatically:
1. **Entity Creation**: Determine which chart of account to use when creating new entities
2. **Voucher Posting**: Determine which ledger to use when posting transactions

Example configurations:
- **Customers** → Accounts Receivable → Sale Ledger
- **Vendors** → Accounts Payable → Purchase Ledger
- **Employees** → Salary Payable → Salary Expense Ledger

### Default Chart of Accounts
The system includes a comprehensive chart of accounts seeder with:
- Current Assets (Cash, Bank, Accounts Receivable, Inventory)
- Fixed Assets (Buildings, Machinery, Vehicles, Equipment)
- Current Liabilities (Accounts Payable, Accrued Expenses)
- Long-term Liabilities
- Equity Accounts
- Revenue and Expense Categories

## Testing

```bash
# Run tests
composer run test

# Run with coverage
php artisan test --coverage
```

## Code Quality

The project uses Laravel Pint for code formatting:
```bash
./vendor/bin/pint
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Run tests and code formatting
5. Submit a pull request

## Security

- All routes are protected with appropriate middleware
- Role-based access control implemented
- Input validation on all forms
- CSRF protection enabled
- Activity logging for audit trails

## License

This project is licensed under the MIT License.

## Support

For support and questions, please refer to the project documentation or contact the development team.

---

**Note**: This is an accounting system designed for business use. Ensure proper backup procedures and consult with accounting professionals before using in production environments.
