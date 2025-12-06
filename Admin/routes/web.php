<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

// Controllers
use App\Http\Controllers\LoginController;
use App\Http\Controllers\BorrowerController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\RepaymentController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\BankTransactionController;
use App\Http\Controllers\AdminSettingsController;
use App\Http\Controllers\AccessControlController;
use App\Http\Controllers\ChartOfAccountController;
use App\Http\Controllers\ReportController; // <-- add this
use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\HelpController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\SupportMessageController;


/*
|--------------------------------------------------------------------------
| Public / Auth
|--------------------------------------------------------------------------
*/

Route::get('/', fn () => view('welcome'));

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| App Routes
|--------------------------------------------------------------------------
|
| If you want these to be auth-protected, change 'web' to ['web','auth']
|
*/
use App\Http\Controllers\LoanDocumentController;

Route::middleware(['auth'])->group(function () {

    // List & upload documents for a borrower (using old loan_documents table - separate system)
    Route::get('/borrowers/{borrower}/loan-documents', [LoanDocumentController::class, 'index'])
        ->name('borrowers.loan-documents.index');

    Route::post('/borrowers/{borrower}/loan-documents', [LoanDocumentController::class, 'store'])
        ->name('borrowers.loan-documents.store');

    // Download a loan document (old system)
    Route::get('/loan-documents/{loanDocument}/download', [LoanDocumentController::class, 'download'])
        ->name('loan-documents.download');

    // Delete a loan document (old system)
    Route::delete('/loan-documents/{loanDocument}', [LoanDocumentController::class, 'destroy'])
        ->name('loan-documents.destroy');
});



Route::middleware('web')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    // ---------- Reports ----------
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');               // Profit & Loss
    Route::get('/reports/balance-sheet', [ReportController::class, 'balanceSheet'])->name('reports.balance-sheet');
    Route::get('/reports/cash-flow', [ReportController::class, 'cashFlow'])->name('reports.cash-flow');


    // ---------- Simple view pages ----------
    Route::view('/investors', 'investors')->name('investors.index');

    // Documents (shared documents table) - public routes for viewing/downloading
    // IMPORTANT: More specific routes must come before less specific ones
    Route::get('/documents/test/{id?}', [\App\Http\Controllers\DocumentController::class, 'test'])->name('documents.test');

    // Download and view routes - must be before /documents route
    Route::get('/documents/{id}/download', [\App\Http\Controllers\DocumentController::class, 'download'])
        ->name('documents.download');
    Route::get('/documents/{id}/view', [\App\Http\Controllers\DocumentController::class, 'view'])
        ->name('documents.view');

    // Direct file serving from uploads (fallback for User backend files)
    Route::get('/uploads/{filename}', function ($filename) {
        $basePath = base_path();
        $possiblePaths = [
            public_path('uploads/' . $filename),
            $basePath . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'User' . DIRECTORY_SEPARATOR . 'backend' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $filename,
            dirname($basePath) . DIRECTORY_SEPARATOR . 'User' . DIRECTORY_SEPARATOR . 'backend' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $filename,
        ];

        foreach ($possiblePaths as $path) {
            $resolvedPath = realpath($path);
            if ($resolvedPath && file_exists($resolvedPath) && is_file($resolvedPath)) {
                $mimeType = mime_content_type($resolvedPath) ?: 'application/octet-stream';
                return response()->file($resolvedPath, ['Content-Type' => $mimeType]);
            }
            // Also try without realpath
            if (file_exists($path) && is_file($path)) {
                $mimeType = mime_content_type($path) ?: 'application/octet-stream';
                return response()->file($path, ['Content-Type' => $mimeType]);
            }
        }

        abort(404, "File not found: {$filename}");
    })->where('filename', '.*');

    // List documents - must be last
    Route::get('/documents', [\App\Http\Controllers\DocumentController::class, 'index'])->name('documents.index');
});

Route::middleware(['web', 'auth'])->group(function () {
    // Documents - protected routes (upload/delete require auth)
    Route::post('/documents', [\App\Http\Controllers\DocumentController::class, 'store'])->name('documents.store');
    Route::delete('/documents/{id}', [\App\Http\Controllers\DocumentController::class, 'destroy'])->name('documents.destroy');

    // ---------- Borrowers ----------
    Route::get('/borrowers', [BorrowerController::class, 'index'])
        ->name('borrowers.index');

    Route::get('/borrowers/export/csv', [BorrowerController::class, 'exportCsv'])
        ->name('borrowers.export.csv');

    Route::post('/borrowers', [BorrowerController::class, 'store'])
        ->name('borrowers.store');

    Route::get('/borrowers/{borrower}', [BorrowerController::class, 'show'])
        ->name('borrowers.show');

    Route::get('/borrowers/{borrower}/edit', [BorrowerController::class, 'edit'])
        ->name('borrowers.edit');

    Route::put('/borrowers/{borrower}', [BorrowerController::class, 'update'])
        ->name('borrowers.update');

    // Archive / Unarchive
    Route::post('/borrowers/{borrower}/archive', [BorrowerController::class, 'archive'])
        ->name('borrowers.archive');

    Route::post('/borrowers/{borrower}/unarchive', [BorrowerController::class, 'unarchive'])
        ->name('borrowers.unarchive');

    // Status update
    Route::patch('/borrowers/{borrower}/status', [BorrowerController::class, 'updateStatus'])
        ->name('borrowers.status');

    // Soft delete
    Route::delete('/borrowers/{borrower}', [BorrowerController::class, 'destroy'])
        ->name('borrowers.destroy');

    // Force delete (permanent)
    Route::delete('/borrowers/{borrower}/force-delete', [BorrowerController::class, 'forceDestroy'])
        ->name('borrowers.forceDestroy');


    // ---------- Loans ----------
    Route::get('/loans', [LoanController::class, 'index'])
        ->name('loans.index');

    Route::get('/loans/create', [LoanController::class, 'create'])
        ->name('loans.create');

    Route::post('/loans', [LoanController::class, 'store'])
        ->name('loans.store');

    Route::get('/loans/{loan}', [LoanController::class, 'show'])
        ->name('loans.show');

    Route::get('/loans/{loan}/edit', [LoanController::class, 'edit'])
        ->name('loans.edit');

    Route::put('/loans/{loan}', [LoanController::class, 'update'])
        ->name('loans.update');

    Route::post('/loans/{loan}/move', [LoanController::class, 'transition'])
        ->name('loans.move');

    // ---------- Bank Transactions ----------
    // IMPORTANT: Route order matters! More specific routes must come first!

    // 1. Most specific: routes with 2 parameters
    Route::get('/transactions/{accountId}/{transactionId}/edit', [BankTransactionController::class, 'edit'])
        ->name('transactions.edit');

    Route::get('/transactions/{accountId}/{transactionId}', [BankTransactionController::class, 'show'])
        ->name('transactions.show');

    // 2. Routes with 1 parameter + action
    Route::get('/transactions/{accountId}/create', [BankTransactionController::class, 'create'])
        ->name('transactions.create');

    // 3. Direct transaction access by ID (redirects to full path) - MUST be before /transactions/{accountId}
    Route::get('/transactions/{transactionId}', function ($transactionId) {
        try {
            $transaction = \App\Models\BankTransaction::findOrFail((int) $transactionId);
            return redirect()->route('transactions.show', [
                'accountId' => $transaction->bank_account_id,
                'transactionId' => $transactionId
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(404, "Transaction ID {$transactionId} not found.");
        } catch (\Exception $e) {
            Log::error('Error redirecting transaction', [
                'transactionId' => $transactionId,
                'error' => $e->getMessage(),
            ]);
            abort(500, "Error: " . $e->getMessage());
        }
    })->where('transactionId', '[0-9]+')->name('transactions.show.direct');

    // 4. Least specific: routes with 1 parameter (must be last)
    Route::get('/transactions/{accountId}', [BankTransactionController::class, 'index'])
        ->name('transactions.index')
        ->whereNumber('accountId');

    // Other transaction routes
    Route::put('/transactions/{accountId}/{transactionId}', [BankTransactionController::class, 'update'])
        ->name('transactions.update');

    Route::post('/transactions/{accountId}/import', [BankTransactionController::class, 'import'])
        ->name('transactions.import')
        ->whereNumber('accountId');

    Route::post('/transactions/{accountId}', [BankTransactionController::class, 'store'])
        ->name('transactions.store')
        ->whereNumber('accountId');

    Route::patch('/transactions/{accountId}/{transactionId}/reconcile', [BankTransactionController::class, 'reconcile'])
        ->name('transactions.reconcile')
        ->whereNumber('accountId')
        ->whereNumber('transactionId');

    Route::patch('/transactions/{accountId}/{transactionId}/post', [BankTransactionController::class, 'markPosted'])
        ->name('transactions.post')
        ->whereNumber('accountId')
        ->whereNumber('transactionId');

    Route::patch('/transactions/{accountId}/{transactionId}/exclude', [BankTransactionController::class, 'markExcluded'])
        ->name('transactions.exclude')
        ->whereNumber('accountId')
        ->whereNumber('transactionId');

    Route::patch('/transactions/{accountId}/{transactionId}/restore', [BankTransactionController::class, 'restorePending'])
        ->name('transactions.restore')
        ->whereNumber('accountId')
        ->whereNumber('transactionId');

    // ---------- Admin Settings ----------
    Route::get('/admin/settings', [AdminSettingsController::class, 'index'])
        ->name('admin.settings.index');

    Route::post('/admin/settings/access-control', [AccessControlController::class, 'updateRolePermissions'])
        ->name('admin.settings.access-control.update');

    // Alternate endpoint you already had
    Route::post('/settings/permissions', [AccessControlController::class, 'updateRolePermissions'])
        ->name('settings.permissions.update');

    // ---------- Chart of Accounts ----------
    // List + filters
    Route::get('/chart-of-accounts', [ChartOfAccountController::class, 'index'])
        ->name('chart-of-accounts.index');

    // Create form
    Route::get('/chart-of-accounts/create', [ChartOfAccountController::class, 'create'])
        ->name('chart-of-accounts.create');

    // Store new account
    Route::post('/chart-of-accounts', [ChartOfAccountController::class, 'store'])
        ->name('chart-of-accounts.store');

    // Edit form
    Route::get('/chart-of-accounts/{chartOfAccount}/edit', [ChartOfAccountController::class, 'edit'])
        ->name('chart-of-accounts.edit');

    // Update account
    Route::put('/chart-of-accounts/{chartOfAccount}', [ChartOfAccountController::class, 'update'])
        ->name('chart-of-accounts.update');

    Route::patch('/chart-of-accounts/{chartOfAccount}', [ChartOfAccountController::class, 'update']);

    // Delete account
    Route::delete('/chart-of-accounts/{chartOfAccount}', [ChartOfAccountController::class, 'destroy'])
        ->name('chart-of-accounts.destroy');

    // Export/Import Chart of Accounts
    Route::get('/chart-of-accounts/export', [ChartOfAccountController::class, 'export'])
        ->name('coa.export');

    Route::post('/chart-of-accounts/import', [ChartOfAccountController::class, 'import'])
        ->name('coa.import');

    // Merge Chart of Accounts
    Route::post('/chart-of-accounts/merge', [ChartOfAccountController::class, 'merge'])
        ->name('chart-of-accounts.merge');

    // ---------- Notifications ----------
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/create', [NotificationController::class, 'create'])->name('notifications.create');
    Route::post('/notifications', [NotificationController::class, 'store'])->name('notifications.store');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/{notification}/unread', [NotificationController::class, 'markAsUnread'])->name('notifications.unread');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    // ---------- Information Pages ----------
    Route::get('/about', [AboutController::class, 'index'])->name('about.index');
    Route::get('/help', [HelpController::class, 'index'])->name('help.index');
    Route::get('/legal', [LegalController::class, 'index'])->name('legal.index');

    // ---------- Support Messages ----------
    Route::get('/support-messages', [SupportMessageController::class, 'index'])->name('support-messages.index');
    Route::get('/support-messages/{supportMessage}', [SupportMessageController::class, 'show'])->name('support-messages.show');
    Route::put('/support-messages/{supportMessage}', [SupportMessageController::class, 'update'])->name('support-messages.update');

    // ---------- Socket.io Token (for admin authentication) ----------
    Route::get('/admin/socket/token', function() {
        $admin = Auth::user();
        if (!$admin) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Create a token payload (matching Node.js backend format)
        $payload = [
            'userId' => $admin->id,
            'username' => $admin->username ?? $admin->name ?? 'admin',
            'userType' => 'admin',
            'iat' => time(),
            'exp' => time() + (24 * 60 * 60) // 24 hours
        ];

        // Base64 encode the payload (Socket.io server will decode it)
        $token = base64_encode(json_encode($payload));

        return response()->json(['token' => $token]);
    })->name('admin.socket.token');
});

// Repayment schedule per loan
Route::get('/loans/{loan}/repayments', [RepaymentController::class, 'index'])->name('repayments.index');
Route::get('/loans/{loan}/repayments/create', [RepaymentController::class, 'create'])->name('repayments.create');
Route::post('/loans/{loan}/repayments', [RepaymentController::class, 'store'])->name('repayments.store');
Route::get('/loans/{loan}/repayments/{repayment}/edit', [RepaymentController::class, 'edit'])->name('repayments.edit');
Route::put('/loans/{loan}/repayments/{repayment}', [RepaymentController::class, 'update'])->name('repayments.update');
Route::delete('/loans/{loan}/repayments/{repayment}', [RepaymentController::class, 'destroy'])->name('repayments.destroy');

// Payment recording for disbursed loans (admin direct entry - auto-approved)
Route::post('/loans/{loan}/payments', [PaymentController::class, 'store'])->name('payments.store');

// Payment approval workflow (for user-submitted payments)
Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
Route::post('/payments/{payment}/approve', [PaymentController::class, 'approve'])->name('payments.approve');
Route::post('/payments/{payment}/reject', [PaymentController::class, 'reject'])->name('payments.reject');

// You already have:
Route::post('/repayments/{repayment}/apply-penalty', [RepaymentController::class, 'applyPenalty'])
    ->name('repayments.applyPenalty');

        // ---------- Bank Accounts ----------
    // List + filters
    Route::get('/bank-accounts', [BankAccountController::class, 'index'])
        ->name('bank-accounts.index');

    // Create form
    Route::get('/bank-accounts/create', [BankAccountController::class, 'create'])
        ->name('bank-accounts.create');

    // Store new account
    Route::post('/bank-accounts', [BankAccountController::class, 'store'])
        ->name('bank-accounts.store');

    // Show single account (details, balances, recent transactions)
    Route::get('/bank-accounts/{bankAccount}', [BankAccountController::class, 'show'])
        ->name('bank-accounts.show');

    // Edit form
    Route::get('/bank-accounts/{bankAccount}/edit', [BankAccountController::class, 'edit'])
        ->name('bank-accounts.edit');

    // Update account
    Route::put('/bank-accounts/{bankAccount}', [BankAccountController::class, 'update'])
        ->name('bank-accounts.update');

    // Delete account (only if no transactions/loans – enforced in controller)
    Route::delete('/bank-accounts/{bankAccount}', [BankAccountController::class, 'destroy'])
        ->name('bank-accounts.destroy');
