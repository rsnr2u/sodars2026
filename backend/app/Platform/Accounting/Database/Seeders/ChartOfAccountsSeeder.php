<?php

declare(strict_types=1);

namespace App\Platform\Accounting\Database\Seeders;

use App\Platform\Accounting\ChartOfAccounts\LedgerAccount;
use App\Platform\Accounting\ChartOfAccounts\AccountType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ChartOfAccountsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Assets Parent
        $assets = LedgerAccount::create([
            'id' => (string) Str::uuid(),
            'parent_account_id' => null,
            'name' => 'Assets',
            'code' => '1000-ASSETS',
            'type' => AccountType::Asset->value,
            'normal_balance' => 'debit',
            'is_control_account' => true,
            'allow_manual_posting' => false,
        ]);

        $cash = LedgerAccount::create([
            'id' => (string) Str::uuid(),
            'parent_account_id' => $assets->id,
            'name' => 'Cash',
            'code' => '1100-CASH',
            'type' => AccountType::Asset->value,
            'normal_balance' => 'debit',
            'is_control_account' => false,
            'allow_manual_posting' => true,
        ]);

        $bank = LedgerAccount::create([
            'id' => (string) Str::uuid(),
            'parent_account_id' => $assets->id,
            'name' => 'Bank Account',
            'code' => '1110-BANK',
            'type' => AccountType::Asset->value,
            'normal_balance' => 'debit',
            'is_control_account' => false,
            'allow_manual_posting' => true,
        ]);

        // 2. Liabilities Parent
        $liabilities = LedgerAccount::create([
            'id' => (string) Str::uuid(),
            'parent_account_id' => null,
            'name' => 'Liabilities',
            'code' => '2000-LIABILITIES',
            'type' => AccountType::Liability->value,
            'normal_balance' => 'credit',
            'is_control_account' => true,
            'allow_manual_posting' => false,
        ]);

        $walletLiability = LedgerAccount::create([
            'id' => (string) Str::uuid(),
            'parent_account_id' => $liabilities->id,
            'name' => 'Wallet Liabilities',
            'code' => '2100-WALLET-LIABILITY',
            'type' => AccountType::Liability->value,
            'normal_balance' => 'credit',
            'is_control_account' => false,
            'allow_manual_posting' => true,
        ]);

        $gstPayable = LedgerAccount::create([
            'id' => (string) Str::uuid(),
            'parent_account_id' => $liabilities->id,
            'name' => 'GST Payable',
            'code' => '2200-GST-PAYABLE',
            'type' => AccountType::Liability->value,
            'normal_balance' => 'credit',
            'is_control_account' => false,
            'allow_manual_posting' => true,
        ]);

        $settlementPayable = LedgerAccount::create([
            'id' => (string) Str::uuid(),
            'parent_account_id' => $liabilities->id,
            'name' => 'Provider Settlement Payables',
            'code' => '2300-SETTLEMENT-PAYABLE',
            'type' => AccountType::Liability->value,
            'normal_balance' => 'credit',
            'is_control_account' => false,
            'allow_manual_posting' => true,
        ]);

        // 3. Equity Parent
        LedgerAccount::create([
            'id' => (string) Str::uuid(),
            'parent_account_id' => null,
            'name' => 'Equity',
            'code' => '3000-EQUITY',
            'type' => AccountType::Equity->value,
            'normal_balance' => 'credit',
            'is_control_account' => true,
            'allow_manual_posting' => false,
        ]);

        // 4. Revenue Parent
        $revenue = LedgerAccount::create([
            'id' => (string) Str::uuid(),
            'parent_account_id' => null,
            'name' => 'Revenue',
            'code' => '4000-REVENUE',
            'type' => AccountType::Revenue->value,
            'normal_balance' => 'credit',
            'is_control_account' => true,
            'allow_manual_posting' => false,
        ]);

        LedgerAccount::create([
            'id' => (string) Str::uuid(),
            'parent_account_id' => $revenue->id,
            'name' => 'Booking Advertising Revenue',
            'code' => '4100-ADVERTISING-REVENUE',
            'type' => AccountType::Revenue->value,
            'normal_balance' => 'credit',
            'is_control_account' => false,
            'allow_manual_posting' => true,
        ]);

        // 5. Expenses Parent
        $expenses = LedgerAccount::create([
            'id' => (string) Str::uuid(),
            'parent_account_id' => null,
            'name' => 'Expenses',
            'code' => '5000-EXPENSES',
            'type' => AccountType::Expense->value,
            'normal_balance' => 'debit',
            'is_control_account' => true,
            'allow_manual_posting' => false,
        ]);

        LedgerAccount::create([
            'id' => (string) Str::uuid(),
            'parent_account_id' => $expenses->id,
            'name' => 'Commission Expense',
            'code' => '5100-COMMISSION-EXPENSE',
            'type' => AccountType::Expense->value,
            'normal_balance' => 'debit',
            'is_control_account' => false,
            'allow_manual_posting' => true,
        ]);
    }
}
