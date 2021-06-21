<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BalansController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\CampaignNewsController;
use App\Http\Controllers\CampaignRealizationController;
use App\Http\Controllers\CampaignSuggestController;
use App\Http\Controllers\CarouselController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\Dash\DonationController as DashDonationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ImageFileController;
use App\Http\Controllers\MemberBankController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MutasiBankController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\QurbanController;
use App\Http\Controllers\RealizationProgressController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SubscriberController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ValidationEmailController;
use App\Http\Controllers\VeritransController;
use App\Http\Controllers\VolunteerController;
use App\Http\Controllers\WhatsappJobController;
use App\Http\Controllers\Dash\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('home', [HomeController::class, 'home']);

Route::post('auth/donor', [AuthController::class, 'authDonor']);
Route::post('auth/staff', [AuthController::class, 'authStaff']);
Route::post('auth/google/login', [AuthController::class, 'googleLogin']);

Route::get('member_bank', [MemberBankController::class, 'index']);

Route::get('whatsapp/chat', [HomeController::class, 'chat']);

Route::post('image/file', [ImageFileController::class, 'upload']);
Route::get('image/{name}', [ImageFileController::class, 'show']);
Route::get('image/{width}/{name}', [ImageFileController::class, 'showByWidth']);


Route::get('page/{slug}', [PageController::class, 'show']);

Route::get('carousel/{slug}', [CarouselController::class, 'show']);
Route::get('carousel', [CarouselController::class, 'index']);

Route::post('donation', [DonationController::class, 'store']);
Route::get('donation/campaign/{slug}', [DonationController::class, 'showByCampaign']);
Route::get('donation/summary', [DonationController::class, 'summary']);
Route::get('donation/show/{id}', [DonationController::class, 'show']);
Route::post('donation/guide', [DonationController::class, 'donationGuide']);
Route::get('donation/notification', [DonationController::class, 'donationNotification']);

Route::get('campaign/news', [CampaignNewsController::class, 'index']);
Route::get('campaign/news/category/{slug}', [CampaignNewsController::class, 'showByCategory']);
Route::get('campaign/news/{slug}', [CampaignNewsController::class, 'show']);

Route::post('suggest/campaign', [CampaignSuggestController::class, 'store']);

Route::get('category', [CategoryController::class, 'index']);

Route::post('volunteer', [VolunteerController::class, 'store']);

Route::post('veritrans/notification', [VeritransController::class, 'notification']);
Route::post('faspay/notification', [PaymentController::class, 'faspayNotification']);
Route::any('faspay/redirect', [PaymentController::class, 'faspayRedirect']);

Route::post('subscriber', [SubscriberController::class, 'store']);
Route::put('subscriber', [SubscriberController::class, 'update']);

Route::get('qurban/location', [QurbanController::class, 'location']);
Route::get('qurban/price/{slug}', [QurbanController::class, 'price']);
Route::post('qurban/order', [QurbanController::class, 'order']);
Route::get('qurban/order/detail/{donationNumber}', [QurbanController::class, 'orderDetail']);
Route::get('qurban/dashboard', [QurbanController::class, 'dashboard']);
Route::post('qurban/report/image', [QurbanController::class, 'storeReportImage']);
Route::get('qurban/receipt/{donation_number}', [QurbanController::class, 'qurbanReceipt']);

Route::get('validation_email/start', [ValidationEmailController::class, 'start']);
Route::post('validation_email/done', [ValidationEmailController::class, 'done']);
Route::post('validation_email/store_file', [ValidationEmailController::class, 'storeFile']);

Route::get('whatsapp/job/attachment/{name}', [WhatsappJobController::class, 'getAttachment']);

Route::get('donation/receipt/{donation_number}', [DonationController::class, 'createReceipt']);

Route::post('mutasi_bank/callback', [MutasiBankController::class, 'callback']);

Route::post('whatsapp/inject_broadcast', [WhatsappJobController::class, 'injectBroadcast']);
Route::post('disbursement/update/balans', [BalansController::class, 'importTransaction']);

Route::post('payment', [PaymentController::class, 'store']);

Route::get('donation/extra/import/template', [DashDonationController::class, 'donationImportTemplate']);

Route::group(['middleware' => ['auth:api', 'logging']], function () {
    Route::get('auth/user', [AuthController::class, 'authUser']);
    Route::get('profile', [AuthController::class, 'authUser']);
    Route::put('profile', [AuthController::class, 'authUser']);
    Route::put('user/bank/selected', [AuthController::class, 'userBankSelected']);

    Route::post('qurban/location/quota', [QurbanController::class, 'locationQuota']);
    Route::get('qurban/location/status', [QurbanController::class, 'locationStatus']);
    Route::post('qurban/location/description', [QurbanController::class, 'locationDescription']);
    Route::post('qurban/location/cover', [QurbanController::class, 'locationCover']);
    Route::get('qurban/order', [QurbanController::class, 'orderList']);
    Route::get('qurban/order/type', [QurbanController::class, 'orderType']);
    Route::get('qurban/type', [QurbanController::class, 'type']);
    Route::post('qurban/order/attachment', [QurbanController::class, 'orderAttachment']);
    Route::post('qurban/order/attachment/delete', [QurbanController::class, 'orderAttachmentDelete']);
    Route::post('qurban/order/status', [QurbanController::class, 'orderStatus']);
    Route::get('qurban/order/payment', [QurbanController::class, 'orderPayment']);
    Route::post('qurban/order/location', [QurbanController::class, 'orderLocationUpdate']);
    Route::post('qurban/order/import', [QurbanController::class, 'orderImport']);
    Route::post('qurban/report/send', [QurbanController::class, 'orderSendReport']);
    Route::post('qurban/order/plotting', [QurbanController::class, 'orderPlotting']);
    Route::post('qurban/order/update', [QurbanController::class, 'orderUpdate']);
    Route::post('qurban/order/check', [QurbanController::class, 'orderCheck']);

    Route::post('image', [ImageFileController::class, 'store']);

    Route::get('donation', [DonationController::class, 'index'])->middleware('permission:show-donation');
    Route::get('donation/download', [DonationController::class, 'download'])->middleware('permission:show-donation');
    Route::get('donation/donor/{donorId}', [DonationController::class, 'byDonor'])->middleware('permission:show-donation');
    Route::get('donation/list', [DonationController::class, 'list'])->middleware('permission:show-donation');
    Route::put('donation/{id}', [DonationController::class, 'update'])->middleware('permission:update-donation');
    Route::delete('donation/{id}', [DonationController::class, 'delete'])->middleware('permission:delete-donation');
    Route::get('donation/campaign/{id}', [DonationController::class, 'showByCampaign'])->middleware('permission:show-donation');
    Route::post('donation/verification/{id}', [DonationController::class, 'verification'])->middleware('permission:verification-donation');
    Route::post('donation/verification/cancel/{id}', [DonationController::class, 'cancelVerification'])->middleware('permission:verification-donation');
    Route::post('donation/offline', [DonationController::class, 'offline'])->middleware('permission:offline-donation');
    Route::post('donation/extra/import', [DashDonationController::class, 'donationExtraImport']);
    Route::put('donation/extra/donor/edit', [DashDonationController::class, 'donationExtraEditDonor']);
    Route::post('donation/extra/campaign/edit', [DashDonationController::class, 'donationExtraEditCampaign']);

    Route::get('donation/history', [DonationController::class, 'donationsHistory']);

    Route::post('user', [UserController::class, 'store'])->middleware('permission:create-user');
    Route::put('user/{id}', [UserController::class, 'update'])->middleware('permission:update-user');
    Route::get('user/{id}', [UserController::class, 'show'])->middleware('permission:show-user');
    Route::get('user', [UserController::class, 'index'])->middleware('permission:show-user');

    Route::get('page', [PageController::class, 'index'])->middleware('permission:show-page');
    Route::post('page', [PageController::class, 'store'])->middleware('permission:create-page');
    Route::put('page/{slug}', [PageController::class, 'update'])->middleware('permission:update-page');
    Route::delete('page/{slug}', [PageController::class, 'destroy'])->middleware('permission:delete-page');

    Route::post('carousel', [CarouselController::class, 'store'])->middleware('permission:create-carousel');
    Route::put('carousel/{slug}', [CarouselController::class, 'update'])->middleware('permission:update-carousel');
    Route::delete('carousel/{slug}', [CarouselController::class, 'destroy'])->middleware('permission:delete-carousel');

    Route::post('campaign/news', [CampaignNewsController::class, 'store'])->middleware('permission:create-campaign-news');
    Route::put('campaign/news/{slug}', [CampaignNewsController::class, 'update'])->middleware('permission:update-campaign-news');
    Route::delete('campaign/news/{slug}', [CampaignNewsController::class, 'destroy'])->middleware('permission:delete-campaign-news');
    Route::get('user/campaign/news/recent', [CampaignNewsController::class, 'userRecentNews']);

    Route::post('campaign/realization', [CampaignRealizationController::class, 'store'])->middleware('permission:create-campaign-realization');
    Route::get('campaign/realization', [CampaignRealizationController::class, 'index'])->middleware('permission:show-campaign-realization');
    Route::put('campaign/realization/{id}', [CampaignRealizationController::class, 'update'])->middleware('permission:update-campaign-realization');
    Route::get('campaign/realization/{id}', [CampaignRealizationController::class, 'show'])->middleware('permission:show-campaign-realization');
    Route::delete('campaign/realization/{id}', [CampaignRealizationController::class, 'destroy'])->middleware('permission:delete-campaign-realization');

    Route::get('suggest/campaign', [CampaignSuggestController::class, 'index']);

    Route::post('campaign', [CampaignController::class, 'store'])->middleware('permission:create-campaign');

    Route::put('campaign/{slug}', [CampaignController::class, 'update'])->middleware('permission:update-campaign');
    Route::delete('campaign/{slug}', [CampaignController::class, 'destroy'])->middleware('permission:delete-campaign');

    Route::get('payment', [PaymentController::class, 'index'])->middleware('permission:show-payment');
    Route::post('payment/import', [PaymentController::class, 'import'])->middleware('permission:create-payment');

    Route::post('realization/progress', [RealizationProgressController::class, 'store'])->middleware('permission:create-realization-progress');
    Route::put('realization/progress/{id}', [RealizationProgressController::class, 'update'])->middleware('permission:update-realization-progress');
    Route::delete('realization/progress/{id}', [RealizationProgressController::class, 'destroy'])->middleware('permission:delete-realization-progress');

    Route::post('category', [CategoryController::class, 'store'])->middleware('permission:create-category');
    Route::delete('category/{id}', [CategoryController::class, 'destroy'])->middleware('permission:delete-category');
    Route::put('category/{id}', [CategoryController::class, 'update'])->middleware('permission:update-category');
    Route::get('category/{id}', [CategoryController::class, 'show'])->middleware('permission:show-category');

    Route::post('member', [MemberController::class, 'store'])->middleware('permission:create-member');
    Route::put('member/{id}', [MemberController::class, 'update'])->middleware('permission:update-member');
    Route::get('member/{id}', [MemberController::class, 'show'])->middleware('permission:show-member');
    Route::get('member', [MemberController::class, 'index'])->middleware('permission:delete-member');

    Route::get('bank', [BankController::class, 'index'])->middleware('permission:show-bank');
    Route::post('bank', [BankController::class, 'store'])->middleware('permission:create-bank');
    Route::delete('bank/{id}', [BankController::class, 'destroy'])->middleware('permission:delete-bank');
    Route::put('bank/{id}', [BankController::class, 'update'])->middleware('permission:update-bank');
    Route::get('bank/{id}', [BankController::class, 'show'])->middleware('permission:show-bank');

    Route::post('member_bank', [MemberBankController::class, 'store'])->middleware('permission:create-member-bank');
    Route::delete('member_bank/{id}', [MemberBankController::class, 'destroy'])->middleware('permission:delete-member-bank');
    Route::put('member_bank/{id}', [MemberBankController::class, 'update'])->middleware('permission:update-member-bank');
    Route::get('member_bank/{id}', [MemberBankController::class, 'show'])->middleware('permission:show-member-bank');

    Route::get('role', [RoleController::class, 'index'])->middleware('permission:show-role');
    Route::post('role', [RoleController::class, 'store'])->middleware('permission:create-role');
    Route::get('role/{slug}', [RoleController::class, 'show'])->middleware('permission:show-role');
    Route::put('role/{slug}', [RoleController::class, 'update'])->middleware('permission:update-role');
    Route::delete('role/{slug}', [RoleController::class, 'destroy'])->middleware('permission:delete-role');

    Route::post('permission/sync', [PermissionController::class, 'sync'])->middleware('permission:sync-permission');
    Route::post('permission/attach', [PermissionController::class, 'attach'])->middleware('permission:attach-permission');
    Route::post('permission/attach/all', [PermissionController::class, 'attachAll'])->middleware('permission:attach-permission');
    Route::post('permission/detach', [PermissionController::class, 'detach'])->middleware('permission:detach-permission');
    Route::post('permission/detach/all', [PermissionController::class, 'detachAll'])->middleware('permission:detach-all-permission');

    Route::get('task', [TaskController::class, 'index']);
    Route::post('task', [TaskController::class, 'store']);
    Route::get('task/{id}', [TaskController::class, 'show']);
    Route::put('task/{id}', [TaskController::class, 'update']);
    Route::delete('task/{id}', [TaskController::class, 'destroy']);

    Route::get('volunteer', [VolunteerController::class, 'index']);
    Route::post('volunteer', [VolunteerController::class, 'index']);

    Route::get('subscriber', [SubscriberController::class, 'index']);

    Route::prefix('dash')->namespace('Dash')->group(function () {
        Route::get('donation/group/year', [DashboardController::class, 'donationsGroupYear']);
        Route::get('donation/group/month', [DashboardController::class, 'donationsGroupMonth']);
    });

    Route::group(['prefix' => 'whatsapp/job'], function() {
        Route::post('', [WhatsappJobController::class, 'store']);
        Route::post('/file', [WhatsappJobController::class, 'storeWithFile']);
        Route::get('/get', [WhatsappJobController::class, 'getJob']);
        Route::post('/report', [WhatsappJobController::class, 'reportJob']);
        Route::post('cancel', [WhatsappJobController::class, 'cancelJob']);
    });
});

Route::get('campaign/list', [CampaignController::class, 'list']);
Route::get('campaign', [CampaignController::class, 'index']);
Route::get('campaign/summary', [CampaignController::class, 'summary']);
Route::get('campaign/suggest', [CampaignController::class, 'suggest']);
Route::get('campaign/info', [CampaignController::class, 'info']);
Route::get('campaign/donation/{slug}', [CampaignController::class, 'donation']);
Route::get('campaign/category/{slug}', [CampaignController::class, 'showByCategory']);
Route::get('campaign/{slug}', [CampaignController::class, 'show']);
