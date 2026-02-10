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
use App\Http\Controllers\Branch\StaffController;
use App\Http\Controllers\MainShop\BranchesController as MainShopBranchesController;


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
