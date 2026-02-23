<?php

use App\Http\Controllers\AccountsController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\LoginRestrictionController;
use App\Http\Controllers\MasterPasswordController;

use App\Http\Controllers\AccessoryController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\CustomerMessageController;
use App\Http\Controllers\LoginHistoryController;
use App\Http\Controllers\PettyCashController;
use App\Http\Controllers\BankController;

use App\Http\Controllers\SalesLiveController;

use Illuminate\Support\Facades\Route;
use App\Models\User;


use App\Models\company;
use App\Models\group;
use App\Models\vendor;
use App\Http\Controllers\AccessoryBatchController;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Panels\AdminDashboardController;
use App\Http\Controllers\Panels\MainDashboardController;
use App\Http\Controllers\Panels\BranchDashboardController;
use App\Http\Controllers\Panels\StaffDashboardController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\BranchStaffController;
use App\Http\Controllers\Admin\MainShopController;
use App\Http\Controllers\Admin\MainShopStaffController;
use App\Http\Controllers\Admin\AdminAccountsController;
use App\Http\Controllers\Branch\StaffController;
use App\Http\Controllers\Branch\BranchReportsController;
use App\Http\Controllers\Branch\ExpenseController as BranchExpenseController;
use App\Http\Controllers\MainShop\BranchesController as MainShopBranchesController;
use App\Http\Controllers\Admin\PerfumeController as AdminPerfumeController;
use App\Http\Controllers\Admin\AdminExpenseController;
use App\Http\Controllers\MainShop\PerfumeController as MainPerfumeController;
use App\Http\Controllers\MainShop\MainAccountsController;
use App\Http\Controllers\Admin\BatchController as AdminBatchController;
use App\Http\Controllers\MainShop\BatchController as MainBatchController;
use App\Http\Controllers\MainShop\TransferController as MainTransferController;
use App\Http\Controllers\Branch\TransferClaimController;
use App\Http\Controllers\Branch\InventoryController;
use App\Http\Controllers\MainShop\InventoryController as MainInventoryController;
use App\Http\Controllers\MainShop\MainShop\ExpenseController;
use App\Http\Controllers\Branch\TransferHistoryController;
use App\Http\Controllers\Branch\BranchAccountsController;
use App\Http\Controllers\POS\MainPosController;
use App\Http\Controllers\POS\BranchPosController;
use App\Http\Controllers\POS\PosApiController;
use App\Http\Controllers\MainShop\BankController as MainBankController;
use App\Http\Controllers\Branch\BankController as BranchBankController;
use App\Http\Controllers\Admin\AdminReportsController;
use App\Http\Controllers\Staff\AttendanceScanController;
use App\Http\Controllers\Staff\StaffAttendanceController;
use App\Http\Controllers\Reports\SalaryReportController;
use App\Http\Controllers\Main\MainReportsController;

Auth::routes();
Route::post('/logout-user/{user}', [UserController::class, 'logoutUser'])->name('logoutUser');


Route::get('/', function () {
   
    return view('home');

});



Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/adminthread', [App\Http\Controllers\AdminThreadController::class, 'index'])->name('adminthread');
Route::get('/fetchthread/{user_id}', [App\Http\Controllers\AdminThreadController::class, 'fetchThread'])->name('fetchthread');

Route::get('/userthread', [App\Http\Controllers\UserThreadController::class, 'index'])->name('userthread');
Route::get('/sendmessage/{message}/{chat_id}', [App\Http\Controllers\UserThreadController::class, 'store'])->name('sendmessage');

Route::post('/logout', [App\Http\Controllers\HomeController::class, 'logout'])->name('logout');



Route::get('/index', [App\Http\Controllers\UserController::class, 'index'])
    ->name('user.index')
    ->middleware(['auth', 'login.time.restrict']);




Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
});

Route::middleware(['auth', 'role:main_shop'])->group(function () {
    Route::get('/main/dashboard', [MainDashboardController::class, 'index'])->name('main.dashboard');
});

Route::middleware(['auth', 'role:branch_shop'])->group(function () {
    Route::get('/branch/dashboard', [BranchDashboardController::class, 'index'])->name('branch.dashboard');
});

Route::middleware(['auth', 'role:staff'])->group(function () {
    Route::get('/staff/dashboard', [StaffDashboardController::class, 'index'])->name('staff.dashboard');
});


Route::get('/admin/branches', [BranchController::class, 'index'])->name('admin.branches.index');
Route::get('/admin/branches/create', [BranchController::class, 'create'])->name('admin.branches.create');
Route::post('/admin/branches', [BranchController::class, 'store'])->name('admin.branches.store');
Route::post('/admin/branches/{shop}/toggle', [BranchController::class, 'toggle'])->name('admin.branches.toggle');
Route::get('/admin/branches/{shop}/edit', [BranchController::class, 'edit'])->name('admin.branches.edit');
Route::put('/admin/branches/{shop}', [BranchController::class, 'update'])->name('admin.branches.update');
Route::post('/admin/branches/{shop}/reset-login-password', [BranchController::class, 'resetLoginPassword'])
    ->name('admin.branches.reset_login_password');



Route::get('/admin/branches/{shop}/staff', [BranchStaffController::class, 'index'])->name('admin.branches.staff.index');
Route::get('/admin/branches/{shop}/staff/create', [BranchStaffController::class, 'create'])->name('admin.branches.staff.create');
Route::post('/admin/branches/{shop}/staff', [BranchStaffController::class, 'store'])->name('admin.branches.staff.store');
Route::post('/admin/staff/{user}/toggle', [BranchStaffController::class, 'toggle'])->name('admin.staff.toggle');




Route::get('/admin/main-shop', [MainShopController::class, 'show'])->name('admin.mainshop.show');
Route::post('/admin/main-shop', [MainShopController::class, 'store'])->name('admin.mainshop.store');
Route::post('/admin/main-shop/reset-login-password', [MainShopController::class, 'resetLoginPassword'])
    ->name('admin.mainshop.reset_login_password');




Route::get('/admin/main-shop/staff', [MainShopStaffController::class, 'index'])->name('admin.mainshop.staff.index');
Route::get('/admin/main-shop/staff/create', [MainShopStaffController::class, 'create'])->name('admin.mainshop.staff.create');
Route::post('/admin/main-shop/staff', [MainShopStaffController::class, 'store'])->name('admin.mainshop.staff.store');



Route::get('/branch/staff', [StaffController::class, 'index'])->name('branch.staff.index');
Route::get('/branch/staff/create', [StaffController::class, 'create'])->name('branch.staff.create');
Route::post('/branch/staff', [StaffController::class, 'store'])->name('branch.staff.store');
Route::post('/branch/staff/{user}/toggle', [StaffController::class, 'toggle'])->name('branch.staff.toggle');




Route::get('/main/branches', [MainShopBranchesController::class, 'index'])->name('main.branches.index');




Route::get('/admin/perfumes', [AdminPerfumeController::class, 'index'])->name('admin.perfumes.index');
Route::get('/admin/perfumes/create', [AdminPerfumeController::class, 'create'])->name('admin.perfumes.create');
Route::post('/admin/perfumes', [AdminPerfumeController::class, 'store'])->name('admin.perfumes.store');
Route::get('/admin/perfumes/{perfume}/edit', [AdminPerfumeController::class, 'edit'])->name('admin.perfumes.edit');
Route::put('/admin/perfumes/{perfume}', [AdminPerfumeController::class, 'update'])->name('admin.perfumes.update');




Route::get('/main/perfumes', [MainPerfumeController::class, 'index'])->name('main.perfumes.index');
Route::get('/main/perfumes/create', [MainPerfumeController::class, 'create'])->name('main.perfumes.create');
Route::post('/main/perfumes', [MainPerfumeController::class, 'store'])->name('main.perfumes.store');
Route::get('/main/perfumes/{perfume}/edit', [MainPerfumeController::class, 'edit'])->name('main.perfumes.edit');
Route::put('/main/perfumes/{perfume}', [MainPerfumeController::class, 'update'])->name('main.perfumes.update');




Route::get('/admin/batches', [AdminBatchController::class, 'index'])->name('admin.batches.index');
Route::get('/admin/batches/create', [AdminBatchController::class, 'create'])->name('admin.batches.create');
Route::post('/admin/batches', [AdminBatchController::class, 'store'])->name('admin.batches.store');
Route::get('/admin/batches/{batch}/print', [AdminBatchController::class, 'print'])->name('admin.batches.print');




Route::get('/main/batches', [MainBatchController::class, 'index'])->name('main.batches.index');
Route::get('/main/batches/create', [MainBatchController::class, 'create'])->name('main.batches.create');
Route::post('/main/batches', [MainBatchController::class, 'store'])->name('main.batches.store');
Route::get('/main/batches/{batch}/print', [MainBatchController::class, 'print'])->name('main.batches.print');



Route::get('/main/transfers', [MainTransferController::class, 'index'])->name('main.transfers.index');
Route::get('/main/transfers/create', [MainTransferController::class, 'create'])->name('main.transfers.create');
Route::post('/main/transfers', [MainTransferController::class, 'store'])->name('main.transfers.store');




Route::get('/branch/transfers/claim', [TransferClaimController::class, 'showClaimForm'])->name('branch.transfers.claim_form');
Route::post('/branch/transfers/claim', [TransferClaimController::class, 'claim'])->name('branch.transfers.claim');


Route::get('/branch/inventory', [InventoryController::class, 'index'])->name('branch.inventory.index');
Route::get('/branch/batches/{batch}/print', [InventoryController::class, 'print'])->name('branch.batches.print');



Route::get('/main/inventory', [MainInventoryController::class, 'index'])->name('main.inventory.index');



Route::post('/main/transfers/{transfer}/cancel', [MainTransferController::class, 'cancel'])
    ->name('main.transfers.cancel');



// Route::get('/branch/transfers', [TransferHistoryController::class, 'index'])->name('branch.transfers.index');


Route::get('/main/pos', [MainPosController::class, 'index'])->name('main.pos');




Route::get('/branch/pos', [BranchPosController::class, 'index'])->name('branch.pos');



Route::get('/main/pos/items', [PosApiController::class, 'items'])->name('main.pos.items');
Route::get('/main/pos/cart', [PosApiController::class, 'cart'])->name('main.pos.cart');
Route::post('/main/pos/cart/add', [PosApiController::class, 'add'])->name('main.pos.cart.add');
Route::post('/main/pos/cart/update', [PosApiController::class, 'update'])->name('main.pos.cart.update');
Route::post('/main/pos/cart/remove', [PosApiController::class, 'remove'])->name('main.pos.cart.remove');


Route::get('/branch/pos/items', [PosApiController::class, 'items'])->name('branch.pos.items');
Route::get('/branch/pos/cart', [PosApiController::class, 'cart'])->name('branch.pos.cart');
Route::post('/branch/pos/cart/add', [PosApiController::class, 'add'])->name('branch.pos.cart.add');
Route::post('/branch/pos/cart/update', [PosApiController::class, 'update'])->name('branch.pos.cart.update');
Route::post('/branch/pos/cart/remove', [PosApiController::class, 'remove'])->name('branch.pos.cart.remove');




Route::get('/main/banks', [MainBankController::class, 'index'])->name('main.banks.index');
Route::get('/main/banks/create', [MainBankController::class, 'create'])->name('main.banks.create');
Route::post('/main/banks', [MainBankController::class, 'store'])->name('main.banks.store');
Route::get('/main/banks/{bank}/edit', [MainBankController::class, 'edit'])->name('main.banks.edit');
Route::put('/main/banks/{bank}', [MainBankController::class, 'update'])->name('main.banks.update');



Route::get('/branch/banks', [BranchBankController::class, 'index'])->name('branch.banks.index');
Route::get('/branch/banks/create', [BranchBankController::class, 'create'])->name('branch.banks.create');
Route::post('/branch/banks', [BranchBankController::class, 'store'])->name('branch.banks.store');
Route::get('/branch/banks/{bank}/edit', [BranchBankController::class, 'edit'])->name('branch.banks.edit');
Route::put('/branch/banks/{bank}', [BranchBankController::class, 'update'])->name('branch.banks.update');




Route::get('/main/pos/banks', [PosApiController::class, 'banks'])->name('main.pos.banks');
Route::get('/branch/pos/banks', [PosApiController::class, 'banks'])->name('branch.pos.banks');



Route::post('/main/pos/checkout', [\App\Http\Controllers\POS\CheckoutController::class, 'checkout'])->name('main.pos.checkout');
Route::post('/branch/pos/checkout', [\App\Http\Controllers\POS\CheckoutController::class, 'checkout'])->name('branch.pos.checkout');



Route::get('/main/pos/receipt/{sale}', [\App\Http\Controllers\POS\ReceiptController::class, 'show'])
  ->name('main.pos.receipt');

Route::get('/main/pos/today', [\App\Http\Controllers\POS\TodaySalesController::class, 'index'])
  ->name('main.pos.today');



  Route::get('/branch/pos/receipt/{sale}', [\App\Http\Controllers\POS\ReceiptController::class, 'show'])
  ->name('branch.pos.receipt');

Route::get('/branch/pos/today', [\App\Http\Controllers\POS\TodaySalesController::class, 'index'])
  ->name('branch.pos.today');




  Route::post('/main/pos/return', [\App\Http\Controllers\POS\ReturnController::class, 'process'])
  ->name('main.pos.return');

Route::get('/main/pos/return-receipt/{sale}', [\App\Http\Controllers\POS\ReturnReceiptController::class, 'show'])
  ->name('main.pos.return_receipt');




 Route::post('/branch/pos/return', [\App\Http\Controllers\POS\ReturnController::class, 'process'])
  ->name('branch.pos.return');

Route::get('/branch/pos/return-receipt/{sale}', [\App\Http\Controllers\POS\ReturnReceiptController::class, 'show'])
  ->name('branch.pos.return_receipt');



  // main
Route::get('/main/pos/sale/{sale}', [\App\Http\Controllers\POS\PartialReturnController::class,'sale'])
  ->name('main.pos.sale');
Route::post('/main/pos/return-partial', [\App\Http\Controllers\POS\PartialReturnController::class,'process'])
  ->name('main.pos.return_partial');

// branch
Route::get('/branch/pos/sale/{sale}', [\App\Http\Controllers\POS\PartialReturnController::class,'sale'])
  ->name('branch.pos.sale');
Route::post('/branch/pos/return-partial', [\App\Http\Controllers\POS\PartialReturnController::class,'process'])
  ->name('branch.pos.return_partial');




Route::get('/main/returns', [\App\Http\Controllers\ReturnsController::class, 'mainIndex'])
  ->name('main.returns.index');


  Route::get('/branch/returns', [\App\Http\Controllers\ReturnsController::class, 'branchIndex'])
  ->name('branch.returns.index');






Route::middleware(['auth'])->group(function () {

    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {

        Route::get('/reports/batches', [AdminReportsController::class, 'batches'])
            ->name('reports.batches');

        Route::get('/reports/sales', [AdminReportsController::class, 'sales'])
            ->name('reports.sales');

        Route::get('/reports/returns', [AdminReportsController::class, 'returns'])
            ->name('reports.returns');
    });

});


Route::middleware(['auth'])->group(function () {
    Route::get('/staff/scan/{token}', [AttendanceScanController::class, 'scan'])
        ->name('staff.scan');
});


Route::middleware(['auth'])->get('/shop/qr', function () {
    $user = auth()->user();
    abort_if(!in_array($user->role, ['main_shop','branch_shop'], true), 403);

    $shop = \App\Models\Shop::findOrFail($user->shop_id);
    return view('panels.shop.qr', compact('shop'));
})->name('shop.qr');


Route::middleware(['auth'])->group(function () {
    Route::get('/staff/attendance/{token}', [StaffAttendanceController::class, 'show'])
        ->name('staff.attendance');
});



Route::middleware(['auth'])->group(function () {

    // Admin (all shops)
    Route::get('/admin/reports/salaries', [SalaryReportController::class, 'admin'])
        ->name('admin.reports.salaries');

    // Main shop (own staff)
    Route::get('/main/reports/salaries', [SalaryReportController::class, 'main'])
        ->name('main.reports.salaries');

    // Branch shop (own staff)
    Route::get('/branch/reports/salaries', [SalaryReportController::class, 'branch'])
        ->name('branch.reports.salaries');
});





Route::prefix('main')->name('main.')->middleware(['auth'])->group(function () {
    Route::get('/reports/batches', [MainReportsController::class, 'batches'])->name('reports.batches');
    Route::get('/reports/sales',   [MainReportsController::class, 'sales'])->name('reports.sales');
    Route::get('/reports/returns', [MainReportsController::class, 'returns'])->name('reports.returns');
});

Route::get('/main/expenses', [\App\Http\Controllers\MainShop\ExpenseController::class,'index'])->name('main.expenses.index');
Route::get('/main/expenses/create', [\App\Http\Controllers\MainShop\ExpenseController::class,'create'])->name('main.expenses.create');
Route::post('/main/expenses', [\App\Http\Controllers\MainShop\ExpenseController::class,'store'])->name('main.expenses.store');


Route::get('/branch/expenses', [\App\Http\Controllers\Branch\BranchExpenseController::class,'index'])->name('branch.expenses.index');
Route::get('/branch/expenses/create', [\App\Http\Controllers\Branch\BranchExpenseController::class,'create'])->name('branch.expenses.create');
Route::post('/branch/expenses', [\App\Http\Controllers\Branch\BranchExpenseController::class,'store'])->name('branch.expenses.store');

// Route::get('/admin/expenses', [\App\Http\Controllers\Admin\AdminExpenseController::class,'index'])->name('admin.expenses.index');
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {

    Route::get('expenses', [AdminExpenseController::class, 'index'])->name('expenses.index');

    Route::get('expenses/{expense}/edit', [AdminExpenseController::class, 'edit'])
        ->name('expenses.edit');

    Route::put('expenses/{expense}', [AdminExpenseController::class, 'update'])
        ->name('expenses.update');

});

Route::patch('/admin/batches/{batch}/update-quantity',
    [AdminBatchController::class, 'updateQuantity']
)->name('admin.batches.update_quantity');

Route::get('/admin/batches/{batch}/edit-qty', [AdminBatchController::class, 'editQty'])
    ->name('admin.batches.edit_qty');

Route::patch('/admin/batches/{batch}/update-qty', [AdminBatchController::class, 'updateQty'])
    ->name('admin.batches.update_qty');


    // ADMIN
Route::get('/admin/accounts', [\App\Http\Controllers\Admin\AdminAccountsController::class,'index'])->name('admin.accounts.index');
Route::get('/admin/accounts/create', [\App\Http\Controllers\Admin\AdminAccountsController::class,'create'])->name('admin.accounts.create');
Route::post('/admin/accounts', [\App\Http\Controllers\Admin\AdminAccountsController::class,'store'])->name('admin.accounts.store');
Route::get('/admin/accounts/{account}', [\App\Http\Controllers\Admin\AdminAccountsController::class,'show'])->name('admin.accounts.show');
Route::post('/admin/accounts/{account}/entries', [\App\Http\Controllers\Admin\AdminAccountsController::class,'addEntry'])->name('admin.accounts.entries.store');




// MAIN SHOP — Accounts
Route::middleware(['auth'])->group(function () {
    Route::get('/main/accounts', [\App\Http\Controllers\MainShop\MainAccountsController::class,'index'])
        ->name('main.accounts.index');

    Route::get('/main/accounts/{account}', [\App\Http\Controllers\MainShop\MainAccountsController::class,'show'])
        ->name('main.accounts.show');

    Route::post('/main/accounts/{account}/entries', [\App\Http\Controllers\MainShop\MainAccountsController::class,'storeEntry'])
        ->name('main.accounts.entries.store');
});



// Branch ↔ Branch Transfers
Route::prefix('branch')->middleware(['auth'])->group(function () {

    // Transfer create (send to another branch)
    Route::get('/transfers/create', [\App\Http\Controllers\Branch\BranchTransferController::class, 'create'])
        ->name('branch.transfers.create');

    Route::post('/transfers', [\App\Http\Controllers\Branch\BranchTransferController::class, 'store'])
        ->name('branch.transfers.store');

    // Transfers history (sent + received)
    Route::get('/transfers', [\App\Http\Controllers\Branch\BranchTransferController::class, 'index'])
        ->name('branch.transfers.index');

   
});

// Branch Accounts
Route::get('/branch/accounts', [\App\Http\Controllers\Branch\BranchAccountsController::class, 'index'])
    ->name('branch.accounts.index');

Route::get('/branch/accounts/{account}', [\App\Http\Controllers\Branch\BranchAccountsController::class, 'show'])
    ->name('branch.accounts.show');

Route::post('/branch/accounts/{account}/entries', [\App\Http\Controllers\Branch\BranchAccountsController::class, 'storeEntry'])
    ->name('branch.accounts.entries.store');

    Route::get('/reports/sales', [BranchReportsController::class, 'sales'])->name('branch.reports.sales');








































//vendor routes
Route::get('/showvendors', [App\Http\Controllers\VendorController::class, 'showVendors'])->name('showvendors');
Route::post('/vendors/store', [VendorController::class, 'storeVendor'])->name('storeVendor');
Route::get('/editvendor/{id}', [App\Http\Controllers\VendorController::class, 'editVendor'])->name('editvendor');
Route::put('/updatevendor', [VendorController::class, 'updateVendor'])->name('updateVendor');
Route::post('/deletevendor', [VendorController::class, 'destroyVendor'])->name('destroyVendor');
Route::get('/showvrHistory/{id}', [VendorController::class, 'showVRHistory'])->name('showVRHistory');
Route::get('/showvsHistory/{id}', [VendorController::class, 'showVSHistory'])->name('showVSHistory');
Route::get('/vendor-balance/{id}', [VendorController::class, 'getBalance'])->name('vendor.balance');
Route::get('/vendor-balance', [VendorController::class, 'getBalance'])->name('getVendorBalance');
Route::get('/receivablevendors', [VendorController::class, 'listReceivables'])->name('receivablevendors');






//company routes
Route::get('/showcompanies', [App\Http\Controllers\CompanyController::class, 'showCompanies'])->name('showcompanies');
Route::post('/company/store', [CompanyController::class, 'storeCompany'])->name('storeCompany');
Route::get('/editcompany/{id}', [App\Http\Controllers\CompanyController::class, 'editCompany'])->name('editcompany');
Route::put('/updatecompany', [CompanyController::class, 'updateCompany'])->name('updateCompany');
Route::post('/deletecompany', [CompanyController::class, 'destroyCompany'])->name('destroyCompany');

//group routes
Route::get('/showgroups', [App\Http\Controllers\GroupController::class, 'showGroups'])->name('showgroups');
Route::post('/group/store', [GroupController::class, 'storeGroup'])->name('storeGroup');
Route::get('/editgroup/{id}', [App\Http\Controllers\GroupController::class, 'editGroup'])->name('editGroup');
Route::put('/updategroup', [GroupController::class, 'updateGroup'])->name('updateGroup');
Route::post('/deletegroup', [GroupController::class, 'destroyGroup'])->name('destroyGroup');

//password routes
Route::get('/showpassword', [App\Http\Controllers\MasterPasswordController::class, 'showPassword'])->name('showpassword');
Route::post('/password/update', [MasterPasswordController::class, 'updatePassword'])->name('updatePassword');



//Accounts Routes
Route::get('/accounts/{id}', [AccountsController::class, 'showAccounts'])->name('showAccounts');
Route::post('/credit', [AccountsController::class, 'creditAmount'])->name('creditAmount');
Route::post('/debit', [AccountsController::class, 'debitAmount'])->name('debitAmount');
Route::get('/getaccount/{id}', [App\Http\Controllers\AccountsController::class, 'getaccount'])->name('getaccount');
Route::post('/deleteaccount', [AccountsController::class, 'destroyAccount'])->name('destroyAccount');







//Custom Login Restriction Routes
Route::get('/showlogin', [LoginRestrictionController::class, 'showLogin'])->name('showlogin');

Route::post('/admin/login-window', [LoginRestrictionController::class, 'updateLoginWindow'])
    ->name('admin.updateLoginWindow');

//Manage user routes
Route::get('/showusers', [UserController::class, 'showUsers'])->name('showusers');
Route::post('/store-user', [UserController::class, 'store'])->name('storeUser');
Route::get('/edituser/{id}', [App\Http\Controllers\UserController::class, 'editUser'])->name('editUser');
Route::put('/update-user', [UserController::class, 'update'])->name('updateUser');




//Accessory Routes
Route::get('/accessories', [AccessoryController::class, 'index'])->name('accessories.index');
Route::post('/accessories', [AccessoryController::class, 'store'])->name('accessories.store');
Route::get('/accessoryedit/{id}', [AccessoryController::class, 'edit'])->name('accessories.edit');
Route::put('/accessories', [AccessoryController::class, 'update'])->name('accessories.update');
// Route::get('/filteraccessories', [App\Http\Controllers\AccessoryController::class, 'filter'])->name('filteraccessories');
Route::get('/filteraccessory',[AccessoryController::class,'filter'])->name('filter.index');
Route::get('/batchedit/{id}', [AccessoryBatchController::class, 'edit'])->name('accessories.edit');
Route::put('/batches', [AccessoryBatchController::class, 'update'])->name('batch.update');

//Batch Routes
Route::get('/batches', [AccessoryBatchController::class, 'index'])->name('batches.index');
Route::post('/batches', [AccessoryBatchController::class, 'store'])->name('batches.store');
Route::get('/batches/{id}/barcode', [AccessoryBatchController::class, 'barcodeInfo'])->name('batches.barcode');

Route::post('/deletebatch', [AccessoryBatchController::class, 'deleteBatch'])->name('deletebatch');

Route::get('/batches/live', [AccessoryBatchController::class, 'liveIndex'])->name('batches.live');
Route::get('/batches/live/feed', [AccessoryBatchController::class, 'liveFeed'])->name('batches.live.feed');

//Sales Routes
Route::get('/sales', [App\Http\Controllers\SaleController::class, 'index'])->name('sales.index');
Route::get('/sales/create', [App\Http\Controllers\SaleController::class, 'create'])->name('sales.create');
Route::post('/sales', [App\Http\Controllers\SaleController::class, 'store'])->name('sales.store');
Route::post('/sales/{id}/approve', [SaleController::class, 'approve'])->name('sales.approve');
Route::get('/sales/pending', [SaleController::class, 'pending'])->name('sales.pending');
Route::get('/sales/approved', [SaleController::class, 'approved'])->name('sales.approved');
Route::get('/sales/all', [\App\Http\Controllers\SaleController::class, 'allSales'])->name('sales.all');
Route::get('/sales/{sale}/items', [\App\Http\Controllers\SaleController::class, 'ajaxSaleItems']);



Route::get('/pos', [SaleController::class, 'pos'])->name('sales.pos');
Route::post('/pos/checkout', [SaleController::class, 'checkout'])->name('sales.checkout');
Route::get('/pos/invoice/{sale}', [SaleController::class, 'invoice'])->name('sales.invoice');
Route::get('/accessoryreport', [SaleController::class, 'accessoryReport'])->name('saccessoryreport');
Route::get('/reports/sales', [\App\Http\Controllers\SaleController::class, 'salesReport']);
// routes/web.php
Route::get('/api/vendor-balance/{id}', [VendorController::class, 'getVBalance']);





//Custoemr Mesage Routes

// Show the message form
Route::get('/send-message-to-customers', [CustomerMessageController::class, 'showSendMessageForm'])->name('send-message-to-customers');

// Handle form post (send messages)
Route::post('/send-message-to-customers', [CustomerMessageController::class, 'sendMessageToAllCustomers'])->name('send.message.submit');




Route::get('/loginhistory', [App\Http\Controllers\LoginHistoryController::class, 'getAllLogins'])->name('loginhistory');

// Return Routes
Route::post('/sales/{sale}/return', [SaleController::class, 'processReturn'])->name('sales.return');
Route::get('/sales/refunds', [SaleController::class, 'refundsPage'])->name('sales.refunds');

// Route::post('/sales/{sale}/return', [SaleController::class, 'returnItems'])->name('sales.return');

//Petty Cash Routes
Route::get('/petty-cash', [PettyCashController::class, 'index'])->name('pettycash.index');
Route::post('/petty-cash', [PettyCashController::class, 'store'])->name('pettycash.store');


//Bank Routes
Route::get('/banks', [BankController::class, 'index'])->name('banks');
Route::post('/banks', [BankController::class, 'storeBank'])->name('storeBank');
Route::get('/getbank/{id}', [BankController::class, 'getBank'])->name('getBank');
Route::put('/updatebank', [BankController::class, 'updateBank'])->name('updateBank');


//Bulk Batch Store
Route::get('/batches/bulk', [AccessoryBatchController::class, 'bulkCreate'])
    ->name('batches.bulk');

Route::post('/batches/bulk', [AccessoryBatchController::class, 'bulkStore'])
    ->name('batches.bulk.store');

Route::get('/vendors/search', function (\Illuminate\Http\Request $request) {
    $q = trim($request->get('q', ''));
    return \App\Models\vendor::query()
        ->when($q !== '', function($qq) use ($q) {
            $qq->where('name', 'like', "%{$q}%")
               ->orWhere('mobile_no', 'like', "%{$q}%");
        })
        ->orderBy('name')
        ->limit(20)
        ->get()
        ->map(fn($v) => ['id' => $v->id, 'text' => $v->name.' ('.$v->mobile_no.')']);
})->name('vendors.search');




//Daily sales--------> Done
//Pictures of accessory  ---> Done
//Whatsapp send
//Printer setting ------> Done
//Comments in sales ------> Done

Route::get('/sales/live', [SalesLiveController::class, 'index'])->name('sales.live.index');
Route::get('/sales/live/feed', [SalesLiveController::class, 'feed'])->name('sales.live.feed');

//error fix

//Press f2 to hide or show the purchase price , by default hidden rahy (******)
//Claim ki functionality
//Check lagana h agr koi item loss me jarha ho to usko alert dy 
