<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ChartOfAccount;

class ChartOfAccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data
        // ChartOfAccount::truncate();

        // ASSETS (1000-1999)
        $this->createAssetsAccounts();

        // LIABILITIES (2000-2999)
        $this->createLiabilitiesAccounts();

        // EQUITY (3000-3999)
        $this->createEquityAccounts();

        // REVENUE (4000-4999)
        $this->createRevenueAccounts();

        // EXPENSES (5000-9999)
        $this->createExpenseAccounts();
    }

    private function createAssetsAccounts()
    {
        // Main Assets Category
        $assets = ChartOfAccount::create([
            'account_code' => '1000',
            'account_name' => 'ASSETS',
            'account_type' => 'asset',
            'account_subtype' => 'current_asset',
            'normal_balance' => 'debit',
            'level' => 1,
            'allow_posting' => false,
            'description' => 'All company assets',
            'sort_order' => 1000
        ]);

        // Current Assets
        $currentAssets = ChartOfAccount::create([
            'account_code' => '1100',
            'account_name' => 'Current Assets',
            'account_type' => 'asset',
            'account_subtype' => 'current_asset',
            'normal_balance' => 'debit',
            'parent_id' => $assets->id,
            'level' => 2,
            'allow_posting' => false,
            'description' => 'Assets expected to be converted to cash within one year',
            'sort_order' => 1100
        ]);

        // Cash and Cash Equivalents
        ChartOfAccount::create([
            'account_code' => '1110',
            'account_name' => 'Cash in Hand',
            'account_type' => 'asset',
            'account_subtype' => 'current_asset',
            'normal_balance' => 'debit',
            'parent_id' => $currentAssets->id,
            'level' => 3,
            'description' => 'Physical cash available',
            'sort_order' => 1110
        ]);

        ChartOfAccount::create([
            'account_code' => '1120',
            'account_name' => 'Cash at Bank',
            'account_type' => 'asset',
            'account_subtype' => 'current_asset',
            'normal_balance' => 'debit',
            'parent_id' => $currentAssets->id,
            'level' => 3,
            'description' => 'Money in bank accounts',
            'sort_order' => 1120
        ]);

        ChartOfAccount::create([
            'account_code' => '1130',
            'account_name' => 'Petty Cash',
            'account_type' => 'asset',
            'account_subtype' => 'current_asset',
            'normal_balance' => 'debit',
            'parent_id' => $currentAssets->id,
            'level' => 3,
            'description' => 'Small cash fund for minor expenses',
            'sort_order' => 1130
        ]);

        // Accounts Receivable
        ChartOfAccount::create([
            'account_code' => '1200',
            'account_name' => 'Accounts Receivable',
            'account_type' => 'asset',
            'account_subtype' => 'current_asset',
            'normal_balance' => 'debit',
            'parent_id' => $currentAssets->id,
            'level' => 3,
            'description' => 'Money owed by customers',
            'sort_order' => 1200
        ]);

        ChartOfAccount::create([
            'account_code' => '1210',
            'account_name' => 'Allowance for Doubtful Accounts',
            'account_type' => 'asset',
            'account_subtype' => 'current_asset',
            'normal_balance' => 'credit',
            'parent_id' => $currentAssets->id,
            'level' => 3,
            'description' => 'Estimated uncollectible receivables',
            'sort_order' => 1210
        ]);

        // Inventory
        ChartOfAccount::create([
            'account_code' => '1300',
            'account_name' => 'Inventory',
            'account_type' => 'asset',
            'account_subtype' => 'current_asset',
            'normal_balance' => 'debit',
            'parent_id' => $currentAssets->id,
            'level' => 3,
            'description' => 'Goods available for sale',
            'sort_order' => 1300
        ]);

        ChartOfAccount::create([
            'account_code' => '1310',
            'account_name' => 'Raw Materials',
            'account_type' => 'asset',
            'account_subtype' => 'current_asset',
            'normal_balance' => 'debit',
            'parent_id' => $currentAssets->id,
            'level' => 3,
            'description' => 'Materials used in production',
            'sort_order' => 1310
        ]);

        ChartOfAccount::create([
            'account_code' => '1320',
            'account_name' => 'Work in Progress',
            'account_type' => 'asset',
            'account_subtype' => 'current_asset',
            'normal_balance' => 'debit',
            'parent_id' => $currentAssets->id,
            'level' => 3,
            'description' => 'Partially completed products',
            'sort_order' => 1320
        ]);

        ChartOfAccount::create([
            'account_code' => '1330',
            'account_name' => 'Finished Goods',
            'account_type' => 'asset',
            'account_subtype' => 'current_asset',
            'normal_balance' => 'debit',
            'parent_id' => $currentAssets->id,
            'level' => 3,
            'description' => 'Completed products ready for sale',
            'sort_order' => 1330
        ]);

        // Prepaid Expenses
        ChartOfAccount::create([
            'account_code' => '1400',
            'account_name' => 'Prepaid Expenses',
            'account_type' => 'asset',
            'account_subtype' => 'current_asset',
            'normal_balance' => 'debit',
            'parent_id' => $currentAssets->id,
            'level' => 3,
            'description' => 'Expenses paid in advance',
            'sort_order' => 1400
        ]);

        ChartOfAccount::create([
            'account_code' => '1410',
            'account_name' => 'Prepaid Insurance',
            'account_type' => 'asset',
            'account_subtype' => 'current_asset',
            'normal_balance' => 'debit',
            'parent_id' => $currentAssets->id,
            'level' => 3,
            'description' => 'Insurance premiums paid in advance',
            'sort_order' => 1410
        ]);

        ChartOfAccount::create([
            'account_code' => '1420',
            'account_name' => 'Prepaid Rent',
            'account_type' => 'asset',
            'account_subtype' => 'current_asset',
            'normal_balance' => 'debit',
            'parent_id' => $currentAssets->id,
            'level' => 3,
            'description' => 'Rent paid in advance',
            'sort_order' => 1420
        ]);

        // Fixed Assets
        $fixedAssets = ChartOfAccount::create([
            'account_code' => '1500',
            'account_name' => 'Fixed Assets',
            'account_type' => 'asset',
            'account_subtype' => 'fixed_asset',
            'normal_balance' => 'debit',
            'parent_id' => $assets->id,
            'level' => 2,
            'allow_posting' => false,
            'description' => 'Long-term tangible assets',
            'sort_order' => 1500
        ]);

        ChartOfAccount::create([
            'account_code' => '1510',
            'account_name' => 'Land',
            'account_type' => 'asset',
            'account_subtype' => 'fixed_asset',
            'normal_balance' => 'debit',
            'parent_id' => $fixedAssets->id,
            'level' => 3,
            'description' => 'Real estate land',
            'sort_order' => 1510
        ]);

        ChartOfAccount::create([
            'account_code' => '1520',
            'account_name' => 'Buildings',
            'account_type' => 'asset',
            'account_subtype' => 'fixed_asset',
            'normal_balance' => 'debit',
            'parent_id' => $fixedAssets->id,
            'level' => 3,
            'description' => 'Company buildings and structures',
            'sort_order' => 1520
        ]);

        ChartOfAccount::create([
            'account_code' => '1530',
            'account_name' => 'Machinery & Equipment',
            'account_type' => 'asset',
            'account_subtype' => 'fixed_asset',
            'normal_balance' => 'debit',
            'parent_id' => $fixedAssets->id,
            'level' => 3,
            'description' => 'Production machinery and equipment',
            'sort_order' => 1530
        ]);

        ChartOfAccount::create([
            'account_code' => '1540',
            'account_name' => 'Vehicles',
            'account_type' => 'asset',
            'account_subtype' => 'fixed_asset',
            'normal_balance' => 'debit',
            'parent_id' => $fixedAssets->id,
            'level' => 3,
            'description' => 'Company vehicles',
            'sort_order' => 1540
        ]);

        ChartOfAccount::create([
            'account_code' => '1550',
            'account_name' => 'Furniture & Fixtures',
            'account_type' => 'asset',
            'account_subtype' => 'fixed_asset',
            'normal_balance' => 'debit',
            'parent_id' => $fixedAssets->id,
            'level' => 3,
            'description' => 'Office furniture and fixtures',
            'sort_order' => 1550
        ]);

        ChartOfAccount::create([
            'account_code' => '1560',
            'account_name' => 'Computer Equipment',
            'account_type' => 'asset',
            'account_subtype' => 'fixed_asset',
            'normal_balance' => 'debit',
            'parent_id' => $fixedAssets->id,
            'level' => 3,
            'description' => 'Computers and IT equipment',
            'sort_order' => 1560
        ]);

        // Accumulated Depreciation
        ChartOfAccount::create([
            'account_code' => '1600',
            'account_name' => 'Accumulated Depreciation - Buildings',
            'account_type' => 'asset',
            'account_subtype' => 'fixed_asset',
            'normal_balance' => 'credit',
            'parent_id' => $fixedAssets->id,
            'level' => 3,
            'description' => 'Accumulated depreciation on buildings',
            'sort_order' => 1600
        ]);

        ChartOfAccount::create([
            'account_code' => '1610',
            'account_name' => 'Accumulated Depreciation - Machinery',
            'account_type' => 'asset',
            'account_subtype' => 'fixed_asset',
            'normal_balance' => 'credit',
            'parent_id' => $fixedAssets->id,
            'level' => 3,
            'description' => 'Accumulated depreciation on machinery',
            'sort_order' => 1610
        ]);

        ChartOfAccount::create([
            'account_code' => '1620',
            'account_name' => 'Accumulated Depreciation - Vehicles',
            'account_type' => 'asset',
            'account_subtype' => 'fixed_asset',
            'normal_balance' => 'credit',
            'parent_id' => $fixedAssets->id,
            'level' => 3,
            'description' => 'Accumulated depreciation on vehicles',
            'sort_order' => 1620
        ]);
    }

    private function createLiabilitiesAccounts()
    {
        // Main Liabilities Category
        $liabilities = ChartOfAccount::create([
            'account_code' => '2000',
            'account_name' => 'LIABILITIES',
            'account_type' => 'liability',
            'account_subtype' => 'current_liability',
            'normal_balance' => 'credit',
            'level' => 1,
            'allow_posting' => false,
            'description' => 'All company liabilities',
            'sort_order' => 2000
        ]);

        // Current Liabilities
        $currentLiabilities = ChartOfAccount::create([
            'account_code' => '2100',
            'account_name' => 'Current Liabilities',
            'account_type' => 'liability',
            'account_subtype' => 'current_liability',
            'normal_balance' => 'credit',
            'parent_id' => $liabilities->id,
            'level' => 2,
            'allow_posting' => false,
            'description' => 'Debts due within one year',
            'sort_order' => 2100
        ]);

        ChartOfAccount::create([
            'account_code' => '2110',
            'account_name' => 'Accounts Payable',
            'account_type' => 'liability',
            'account_subtype' => 'current_liability',
            'normal_balance' => 'credit',
            'parent_id' => $currentLiabilities->id,
            'level' => 3,
            'description' => 'Money owed to suppliers',
            'sort_order' => 2110
        ]);

        ChartOfAccount::create([
            'account_code' => '2120',
            'account_name' => 'Short-term Notes Payable',
            'account_type' => 'liability',
            'account_subtype' => 'current_liability',
            'normal_balance' => 'credit',
            'parent_id' => $currentLiabilities->id,
            'level' => 3,
            'description' => 'Short-term loans and notes',
            'sort_order' => 2120
        ]);

        ChartOfAccount::create([
            'account_code' => '2130',
            'account_name' => 'Accrued Expenses',
            'account_type' => 'liability',
            'account_subtype' => 'current_liability',
            'normal_balance' => 'credit',
            'parent_id' => $currentLiabilities->id,
            'level' => 3,
            'description' => 'Expenses incurred but not yet paid',
            'sort_order' => 2130
        ]);

        ChartOfAccount::create([
            'account_code' => '2140',
            'account_name' => 'Wages Payable',
            'account_type' => 'liability',
            'account_subtype' => 'current_liability',
            'normal_balance' => 'credit',
            'parent_id' => $currentLiabilities->id,
            'level' => 3,
            'description' => 'Unpaid employee wages',
            'sort_order' => 2140
        ]);

        ChartOfAccount::create([
            'account_code' => '2150',
            'account_name' => 'Income Tax Payable',
            'account_type' => 'liability',
            'account_subtype' => 'current_liability',
            'normal_balance' => 'credit',
            'parent_id' => $currentLiabilities->id,
            'level' => 3,
            'description' => 'Income taxes owed',
            'sort_order' => 2150
        ]);

        ChartOfAccount::create([
            'account_code' => '2160',
            'account_name' => 'Sales Tax Payable',
            'account_type' => 'liability',
            'account_subtype' => 'current_liability',
            'normal_balance' => 'credit',
            'parent_id' => $currentLiabilities->id,
            'level' => 3,
            'description' => 'Sales taxes collected but not remitted',
            'sort_order' => 2160
        ]);

        ChartOfAccount::create([
            'account_code' => '2170',
            'account_name' => 'Unearned Revenue',
            'account_type' => 'liability',
            'account_subtype' => 'current_liability',
            'normal_balance' => 'credit',
            'parent_id' => $currentLiabilities->id,
            'level' => 3,
            'description' => 'Advance payments from customers',
            'sort_order' => 2170
        ]);

        // Long-term Liabilities
        $longTermLiabilities = ChartOfAccount::create([
            'account_code' => '2200',
            'account_name' => 'Long-term Liabilities',
            'account_type' => 'liability',
            'account_subtype' => 'long_term_liability',
            'normal_balance' => 'credit',
            'parent_id' => $liabilities->id,
            'level' => 2,
            'allow_posting' => false,
            'description' => 'Debts due after one year',
            'sort_order' => 2200
        ]);

        ChartOfAccount::create([
            'account_code' => '2210',
            'account_name' => 'Long-term Notes Payable',
            'account_type' => 'liability',
            'account_subtype' => 'long_term_liability',
            'normal_balance' => 'credit',
            'parent_id' => $longTermLiabilities->id,
            'level' => 3,
            'description' => 'Long-term loans and notes',
            'sort_order' => 2210
        ]);

        ChartOfAccount::create([
            'account_code' => '2220',
            'account_name' => 'Mortgage Payable',
            'account_type' => 'liability',
            'account_subtype' => 'long_term_liability',
            'normal_balance' => 'credit',
            'parent_id' => $longTermLiabilities->id,
            'level' => 3,
            'description' => 'Mortgage loans on property',
            'sort_order' => 2220
        ]);

        ChartOfAccount::create([
            'account_code' => '2230',
            'account_name' => 'Bonds Payable',
            'account_type' => 'liability',
            'account_subtype' => 'long_term_liability',
            'normal_balance' => 'credit',
            'parent_id' => $longTermLiabilities->id,
            'level' => 3,
            'description' => 'Corporate bonds issued',
            'sort_order' => 2230
        ]);
    }

    private function createEquityAccounts()
    {
        // Main Equity Category
        $equity = ChartOfAccount::create([
            'account_code' => '3000',
            'account_name' => 'EQUITY',
            'account_type' => 'equity',
            'account_subtype' => 'owner_equity',
            'normal_balance' => 'credit',
            'level' => 1,
            'allow_posting' => false,
            'description' => 'Owner\'s equity in the business',
            'sort_order' => 3000
        ]);

        ChartOfAccount::create([
            'account_code' => '3100',
            'account_name' => 'Owner\'s Capital',
            'account_type' => 'equity',
            'account_subtype' => 'owner_equity',
            'normal_balance' => 'credit',
            'parent_id' => $equity->id,
            'level' => 2,
            'description' => 'Owner\'s investment in the business',
            'sort_order' => 3100
        ]);

        ChartOfAccount::create([
            'account_code' => '3200',
            'account_name' => 'Owner\'s Drawings',
            'account_type' => 'equity',
            'account_subtype' => 'owner_equity',
            'normal_balance' => 'debit',
            'parent_id' => $equity->id,
            'level' => 2,
            'description' => 'Owner\'s withdrawals from the business',
            'sort_order' => 3200
        ]);

        ChartOfAccount::create([
            'account_code' => '3300',
            'account_name' => 'Retained Earnings',
            'account_type' => 'equity',
            'account_subtype' => 'retained_earnings',
            'normal_balance' => 'credit',
            'parent_id' => $equity->id,
            'level' => 2,
            'description' => 'Accumulated profits retained in business',
            'sort_order' => 3300
        ]);

        ChartOfAccount::create([
            'account_code' => '3400',
            'account_name' => 'Common Stock',
            'account_type' => 'equity',
            'account_subtype' => 'owner_equity',
            'normal_balance' => 'credit',
            'parent_id' => $equity->id,
            'level' => 2,
            'description' => 'Common stock issued',
            'sort_order' => 3400
        ]);

        ChartOfAccount::create([
            'account_code' => '3500',
            'account_name' => 'Additional Paid-in Capital',
            'account_type' => 'equity',
            'account_subtype' => 'owner_equity',
            'normal_balance' => 'credit',
            'parent_id' => $equity->id,
            'level' => 2,
            'description' => 'Premium received on stock issuance',
            'sort_order' => 3500
        ]);
    }

    private function createRevenueAccounts()
    {
        // Main Revenue Category
        $revenue = ChartOfAccount::create([
            'account_code' => '4000',
            'account_name' => 'REVENUE',
            'account_type' => 'revenue',
            'account_subtype' => 'operating_revenue',
            'normal_balance' => 'credit',
            'level' => 1,
            'allow_posting' => false,
            'description' => 'Income generated from business operations',
            'sort_order' => 4000
        ]);

        // Operating Revenue
        $operatingRevenue = ChartOfAccount::create([
            'account_code' => '4100',
            'account_name' => 'Operating Revenue',
            'account_type' => 'revenue',
            'account_subtype' => 'operating_revenue',
            'normal_balance' => 'credit',
            'parent_id' => $revenue->id,
            'level' => 2,
            'allow_posting' => false,
            'description' => 'Revenue from core business activities',
            'sort_order' => 4100
        ]);

        ChartOfAccount::create([
            'account_code' => '4110',
            'account_name' => 'Sales Revenue',
            'account_type' => 'revenue',
            'account_subtype' => 'operating_revenue',
            'normal_balance' => 'credit',
            'parent_id' => $operatingRevenue->id,
            'level' => 3,
            'description' => 'Revenue from product sales',
            'sort_order' => 4110
        ]);

        ChartOfAccount::create([
            'account_code' => '4120',
            'account_name' => 'Service Revenue',
            'account_type' => 'revenue',
            'account_subtype' => 'operating_revenue',
            'normal_balance' => 'credit',
            'parent_id' => $operatingRevenue->id,
            'level' => 3,
            'description' => 'Revenue from services provided',
            'sort_order' => 4120
        ]);

        ChartOfAccount::create([
            'account_code' => '4130',
            'account_name' => 'Consulting Revenue',
            'account_type' => 'revenue',
            'account_subtype' => 'operating_revenue',
            'normal_balance' => 'credit',
            'parent_id' => $operatingRevenue->id,
            'level' => 3,
            'description' => 'Revenue from consulting services',
            'sort_order' => 4130
        ]);

        // Other Revenue
        $otherRevenue = ChartOfAccount::create([
            'account_code' => '4200',
            'account_name' => 'Other Revenue',
            'account_type' => 'revenue',
            'account_subtype' => 'other_revenue',
            'normal_balance' => 'credit',
            'parent_id' => $revenue->id,
            'level' => 2,
            'allow_posting' => false,
            'description' => 'Revenue from non-core activities',
            'sort_order' => 4200
        ]);

        ChartOfAccount::create([
            'account_code' => '4210',
            'account_name' => 'Interest Income',
            'account_type' => 'revenue',
            'account_subtype' => 'other_revenue',
            'normal_balance' => 'credit',
            'parent_id' => $otherRevenue->id,
            'level' => 3,
            'description' => 'Interest earned on investments',
            'sort_order' => 4210
        ]);

        ChartOfAccount::create([
            'account_code' => '4220',
            'account_name' => 'Dividend Income',
            'account_type' => 'revenue',
            'account_subtype' => 'other_revenue',
            'normal_balance' => 'credit',
            'parent_id' => $otherRevenue->id,
            'level' => 3,
            'description' => 'Dividends received from investments',
            'sort_order' => 4220
        ]);

        ChartOfAccount::create([
            'account_code' => '4230',
            'account_name' => 'Rental Income',
            'account_type' => 'revenue',
            'account_subtype' => 'other_revenue',
            'normal_balance' => 'credit',
            'parent_id' => $otherRevenue->id,
            'level' => 3,
            'description' => 'Income from property rentals',
            'sort_order' => 4230
        ]);

        ChartOfAccount::create([
            'account_code' => '4240',
            'account_name' => 'Gain on Sale of Assets',
            'account_type' => 'revenue',
            'account_subtype' => 'other_revenue',
            'normal_balance' => 'credit',
            'parent_id' => $otherRevenue->id,
            'level' => 3,
            'description' => 'Profit from asset sales',
            'sort_order' => 4240
        ]);

        // Sales Returns and Allowances
        ChartOfAccount::create([
            'account_code' => '4900',
            'account_name' => 'Sales Returns and Allowances',
            'account_type' => 'revenue',
            'account_subtype' => 'operating_revenue',
            'normal_balance' => 'debit',
            'parent_id' => $revenue->id,
            'level' => 2,
            'description' => 'Contra revenue account for returns and discounts',
            'sort_order' => 4900
        ]);

        ChartOfAccount::create([
            'account_code' => '4910',
            'account_name' => 'Sales Discounts',
            'account_type' => 'revenue',
            'account_subtype' => 'operating_revenue',
            'normal_balance' => 'debit',
            'parent_id' => $revenue->id,
            'level' => 2,
            'description' => 'Discounts given to customers',
            'sort_order' => 4910
        ]);
    }

    private function createExpenseAccounts()
    {
        // Main Expense Category
        $expenses = ChartOfAccount::create([
            'account_code' => '5000',
            'account_name' => 'EXPENSES',
            'account_type' => 'expense',
            'account_subtype' => 'operating_expense',
            'normal_balance' => 'debit',
            'level' => 1,
            'allow_posting' => false,
            'description' => 'All business expenses',
            'sort_order' => 5000
        ]);

        // Cost of Goods Sold
        $cogs = ChartOfAccount::create([
            'account_code' => '5100',
            'account_name' => 'Cost of Goods Sold',
            'account_type' => 'expense',
            'account_subtype' => 'cost_of_goods_sold',
            'normal_balance' => 'debit',
            'parent_id' => $expenses->id,
            'level' => 2,
            'allow_posting' => false,
            'description' => 'Direct costs of producing goods sold',
            'sort_order' => 5100
        ]);

        ChartOfAccount::create([
            'account_code' => '5110',
            'account_name' => 'Purchases',
            'account_type' => 'expense',
            'account_subtype' => 'cost_of_goods_sold',
            'normal_balance' => 'debit',
            'parent_id' => $cogs->id,
            'level' => 3,
            'description' => 'Cost of inventory purchased',
            'sort_order' => 5110
        ]);

        ChartOfAccount::create([
            'account_code' => '5120',
            'account_name' => 'Direct Labor',
            'account_type' => 'expense',
            'account_subtype' => 'cost_of_goods_sold',
            'normal_balance' => 'debit',
            'parent_id' => $cogs->id,
            'level' => 3,
            'description' => 'Labor costs directly related to production',
            'sort_order' => 5120
        ]);

        ChartOfAccount::create([
            'account_code' => '5130',
            'account_name' => 'Manufacturing Overhead',
            'account_type' => 'expense',
            'account_subtype' => 'cost_of_goods_sold',
            'normal_balance' => 'debit',
            'parent_id' => $cogs->id,
            'level' => 3,
            'description' => 'Indirect manufacturing costs',
            'sort_order' => 5130
        ]);

        ChartOfAccount::create([
            'account_code' => '5140',
            'account_name' => 'Freight-in',
            'account_type' => 'expense',
            'account_subtype' => 'cost_of_goods_sold',
            'normal_balance' => 'debit',
            'parent_id' => $cogs->id,
            'level' => 3,
            'description' => 'Shipping costs on inventory purchases',
            'sort_order' => 5140
        ]);

        // Operating Expenses
        $operatingExpenses = ChartOfAccount::create([
            'account_code' => '6000',
            'account_name' => 'Operating Expenses',
            'account_type' => 'expense',
            'account_subtype' => 'operating_expense',
            'normal_balance' => 'debit',
            'parent_id' => $expenses->id,
            'level' => 2,
            'allow_posting' => false,
            'description' => 'Regular business operating expenses',
            'sort_order' => 6000
        ]);

        // Selling Expenses
        $sellingExpenses = ChartOfAccount::create([
            'account_code' => '6100',
            'account_name' => 'Selling Expenses',
            'account_type' => 'expense',
            'account_subtype' => 'operating_expense',
            'normal_balance' => 'debit',
            'parent_id' => $operatingExpenses->id,
            'level' => 3,
            'allow_posting' => false,
            'description' => 'Expenses related to selling activities',
            'sort_order' => 6100
        ]);

        ChartOfAccount::create([
            'account_code' => '6110',
            'account_name' => 'Advertising Expense',
            'account_type' => 'expense',
            'account_subtype' => 'operating_expense',
            'normal_balance' => 'debit',
            'parent_id' => $sellingExpenses->id,
            'level' => 4,
            'description' => 'Costs of advertising and marketing',
            'sort_order' => 6110
        ]);

        ChartOfAccount::create([
            'account_code' => '6120',
            'account_name' => 'Sales Commission',
            'account_type' => 'expense',
            'account_subtype' => 'operating_expense',
            'normal_balance' => 'debit',
            'parent_id' => $sellingExpenses->id,
            'level' => 4,
            'description' => 'Commission paid to sales staff',
            'sort_order' => 6120
        ]);

        ChartOfAccount::create([
            'account_code' => '6130',
            'account_name' => 'Delivery Expense',
            'account_type' => 'expense',
            'account_subtype' => 'operating_expense',
            'normal_balance' => 'debit',
            'parent_id' => $sellingExpenses->id,
            'level' => 4,
            'description' => 'Costs of delivering products to customers',
            'sort_order' => 6130
        ]);

        // Administrative Expenses
        $adminExpenses = ChartOfAccount::create([
            'account_code' => '6200',
            'account_name' => 'Administrative Expenses',
            'account_type' => 'expense',
            'account_subtype' => 'operating_expense',
            'normal_balance' => 'debit',
            'parent_id' => $operatingExpenses->id,
            'level' => 3,
            'allow_posting' => false,
            'description' => 'General administrative expenses',
            'sort_order' => 6200
        ]);

        ChartOfAccount::create([
            'account_code' => '6210',
            'account_name' => 'Salaries Expense',
            'account_type' => 'expense',
            'account_subtype' => 'operating_expense',
            'normal_balance' => 'debit',
            'parent_id' => $adminExpenses->id,
            'level' => 4,
            'description' => 'Employee salaries and wages',
            'sort_order' => 6210
        ]);

        ChartOfAccount::create([
            'account_code' => '6220',
            'account_name' => 'Office Supplies Expense',
            'account_type' => 'expense',
            'account_subtype' => 'operating_expense',
            'normal_balance' => 'debit',
            'parent_id' => $adminExpenses->id,
            'level' => 4,
            'description' => 'Cost of office supplies consumed',
            'sort_order' => 6220
        ]);

        ChartOfAccount::create([
            'account_code' => '6230',
            'account_name' => 'Rent Expense',
            'account_type' => 'expense',
            'account_subtype' => 'operating_expense',
            'normal_balance' => 'debit',
            'parent_id' => $adminExpenses->id,
            'level' => 4,
            'description' => 'Office and facility rent',
            'sort_order' => 6230
        ]);

        ChartOfAccount::create([
            'account_code' => '6240',
            'account_name' => 'Utilities Expense',
            'account_type' => 'expense',
            'account_subtype' => 'operating_expense',
            'normal_balance' => 'debit',
            'parent_id' => $adminExpenses->id,
            'level' => 4,
            'description' => 'Electricity, water, gas expenses',
            'sort_order' => 6240
        ]);

        ChartOfAccount::create([
            'account_code' => '6250',
            'account_name' => 'Telephone Expense',
            'account_type' => 'expense',
            'account_subtype' => 'operating_expense',
            'normal_balance' => 'debit',
            'parent_id' => $adminExpenses->id,
            'level' => 4,
            'description' => 'Phone and communication costs',
            'sort_order' => 6250
        ]);

        ChartOfAccount::create([
            'account_code' => '6260',
            'account_name' => 'Insurance Expense',
            'account_type' => 'expense',
            'account_subtype' => 'operating_expense',
            'normal_balance' => 'debit',
            'parent_id' => $adminExpenses->id,
            'level' => 4,
            'description' => 'Insurance premiums',
            'sort_order' => 6260
        ]);

        ChartOfAccount::create([
            'account_code' => '6270',
            'account_name' => 'Depreciation Expense',
            'account_type' => 'expense',
            'account_subtype' => 'operating_expense',
            'normal_balance' => 'debit',
            'parent_id' => $adminExpenses->id,
            'level' => 4,
            'description' => 'Depreciation of fixed assets',
            'sort_order' => 6270
        ]);

        ChartOfAccount::create([
            'account_code' => '6280',
            'account_name' => 'Bad Debt Expense',
            'account_type' => 'expense',
            'account_subtype' => 'operating_expense',
            'normal_balance' => 'debit',
            'parent_id' => $adminExpenses->id,
            'level' => 4,
            'description' => 'Estimated uncollectible accounts',
            'sort_order' => 6280
        ]);

        ChartOfAccount::create([
            'account_code' => '6290',
            'account_name' => 'Professional Fees',
            'account_type' => 'expense',
            'account_subtype' => 'operating_expense',
            'normal_balance' => 'debit',
            'parent_id' => $adminExpenses->id,
            'level' => 4,
            'description' => 'Legal, accounting, consulting fees',
            'sort_order' => 6290
        ]);

        // Other Expenses
        $otherExpenses = ChartOfAccount::create([
            'account_code' => '7000',
            'account_name' => 'Other Expenses',
            'account_type' => 'expense',
            'account_subtype' => 'other_expense',
            'normal_balance' => 'debit',
            'parent_id' => $expenses->id,
            'level' => 2,
            'allow_posting' => false,
            'description' => 'Non-operating expenses',
            'sort_order' => 7000
        ]);

        ChartOfAccount::create([
            'account_code' => '7010',
            'account_name' => 'Interest Expense',
            'account_type' => 'expense',
            'account_subtype' => 'other_expense',
            'normal_balance' => 'debit',
            'parent_id' => $otherExpenses->id,
            'level' => 3,
            'description' => 'Interest paid on loans and borrowings',
            'sort_order' => 7010
        ]);

        ChartOfAccount::create([
            'account_code' => '7020',
            'account_name' => 'Loss on Sale of Assets',
            'account_type' => 'expense',
            'account_subtype' => 'other_expense',
            'normal_balance' => 'debit',
            'parent_id' => $otherExpenses->id,
            'level' => 3,
            'description' => 'Loss incurred on asset disposals',
            'sort_order' => 7020
        ]);

        ChartOfAccount::create([
            'account_code' => '7030',
            'account_name' => 'Bank Charges',
            'account_type' => 'expense',
            'account_subtype' => 'other_expense',
            'normal_balance' => 'debit',
            'parent_id' => $otherExpenses->id,
            'level' => 3,
            'description' => 'Bank fees and charges',
            'sort_order' => 7030
        ]);

        // Tax Expenses
        ChartOfAccount::create([
            'account_code' => '8000',
            'account_name' => 'Income Tax Expense',
            'account_type' => 'expense',
            'account_subtype' => 'other_expense',
            'normal_balance' => 'debit',
            'parent_id' => $expenses->id,
            'level' => 2,
            'description' => 'Corporate income tax expense',
            'sort_order' => 8000
        ]);
    }
}
