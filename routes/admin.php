<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\CustomPackageController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\VideoController;
use App\Http\Controllers\Admin\AudioController;
use App\Http\Controllers\Admin\AiaudiobookController;
use App\Http\Controllers\Admin\ArtistController;
use App\Http\Controllers\Admin\AlbumController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\CustomTransactionController;
use App\Http\Controllers\Admin\SmartCollectionController;
use App\Http\Controllers\Auth\AdminController;
use App\Http\Controllers\Admin\CommentController;
use App\Http\Controllers\Admin\AiaudioTransactionController;
use App\Http\Controllers\Admin\EBookController;



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });


Route::get('/', [AdminController::class, 'get_login'])->name('admin.home');
Route::get('admin/login', [AdminController::class, 'get_login'])->name('admin.index');
Route::post('admin/verifyotp', [AdminController::class, 'verifyotp'])->name('admin.verifyotp');
Route::post('admin/login', [AdminController::class, 'post_login'])->name('admin.login');
Route::get('admin/logout', [AdminController::class, 'logout'])->name('admin.logout');
Route::get('admin/signup', [AdminController::class, 'get_signup'])->name('admin.signup');
Route::post('admin/signup', [AdminController::class, 'post_signup'])->name('admin.signup');

// Pages
Route::get('send-mail', [MailController::class,'sendMail'])->name('send.mail');
Route::get('about-us', [AdminController::class, 'Page'])->name('AboutUS');
Route::get('privacy-policy', [AdminController::class, 'Page'])->name('privacyPolicy');
Route::get('terms-and-conditions', [AdminController::class, 'Page'])->name('TermsAndConditions');
Route::get('refund-policy', [AdminController::class, 'Page'])->name('RefundPolicy');
Route::get('remaining', [AdminController::class, 'Page'])->name('remaining');


Route::group(['prefix' => 'admin','middleware' => 'adminauth'], function()  {

    Route::group(['middleware' => 'checkadmin'], function () {

        // ADMIN //
        Route::resource('admins', AdminController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
        Route::get('user_details', [AdminController::class, 'user_details'])->name('admin.user_details');
        // User //
        Route::resource('user', UserController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
        // Artist //
        Route::resource('artist', ArtistController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
        // Album //
        Route::resource('album', AlbumController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
        // Category //
        Route::resource('category', CategoryController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
        // Video //
        Route::resource('video', VideoController::class)->only(['create', 'store', 'edit', 'update', 'show', 'destroy']);
        Route::post('/video/approve', [VideoController::class, 'approvevideo'])->name('video.approvevideo');
        Route::resource('audio', AudioController::class)->only(['create', 'store', 'edit', 'update', 'show', 'destroy']);
        Route::post('/audio/approve', [AudioController::class, 'approveaudio'])->name('audio.approveaudio');
        // AI Audio Book
        Route::resource('aiaudiobook', AiaudiobookController::class)->only(['create', 'store', 'edit', 'update', 'show', 'destroy']);
        Route::post('/aiaudiobook/getconvertedaudio', [AiaudiobookController::class, 'getconvertedaudio'])->name('aiaudiobook.getconvertedaudio');
        

        Route::any('saveChunk', [VideoController::class, 'saveChunk']);

        // Language //
        Route::resource('language', LanguageController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
        // Package
        Route::resource('package', PackageController::class)->only(['create', 'store', 'edit', 'update', 'show', 'destroy']);
        // Page //
        Route::resource('page', PageController::class)->only(['create', 'store', 'edit', 'update', 'show']);
        // Notification //
        Route::resource('notification', NotificationController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
        Route::get('notifications/setting', [NotificationController::class, 'setting'])->name('notification.setting');
        Route::post('notifications/setting', [NotificationController::class, 'settingsave'])->name('notification.settingsave');
        // Payment
        Route::resource('payment', PaymentController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
        
        Route::any('search_user', [UserController::class, 'searchUser'])->name('searchUser');
       
    });
    
    Route::get('dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('multi/language/{id}', [DashboardController::class,'language'])->name('language');
    //  User  //
    Route::resource('admins', AdminController::class)->only(['index']);
    Route::resource('user', UserController::class)->only(['index']);
    // Category //
    Route::resource('category', CategoryController::class)->only(['index']);
    // Artist //
    Route::resource('artist', ArtistController::class)->only(['index']);
    // Album //
    Route::resource('album', AlbumController::class)->only(['index']);
    // Video //
    Route::resource('video', VideoController::class)->only(['index']);
    Route::resource('audio', AudioController::class)->only(['index']);
    Route::resource('aiaudiobook', AiaudiobookController::class)->only(['index']);
    Route::get('video/details/{id}', [VideoController::class,'detail'])->name('videoDetail');
    Route::get('audio/details/{id}', [AudioController::class,'detail'])->name('audioDetail');
    Route::get('aiaudiobook/details/{id}', [AiaudiobookController::class,'detail'])->name('aiAudioBookDetail');
    // Language //
    Route::delete('delete-audio/{id}', [AiaudiobookController::class, 'delete_audio'])->name('aiaudiobook.deleteaudio');
    Route::resource('language', LanguageController::class)->only(['index']);

    // E-Book
    Route::resource('e-book', EBookController::class);
    Route::get('e-book/details/{id}', [EBookController::class,'detail'])->name('eBookDetail');
    Route::get('e-book/delete/{id}', [EBookController::class, 'deleteEBook'])->name('e-book.deleteEBook');
    Route::get('e-book/file/delete/{id}', [EBookController::class, 'deleteEBookFile'])->name('e-book.deleteEBookFile');
    Route::get('e-book/downlaod/{id}', [EBookController::class, 'ebookDownload'])->name('e-book.ebookDownload');
    // Route::resource('e-book', AiaudiobookController::class)->only(['create', 'store', 'edit', 'update', 'show', 'destroy']);

    // Package
    Route::resource('package', PackageController::class)->only(['index']);
    // Page //
    Route::resource('page', PageController::class)->only(['index']);
    // Comment
    Route::resource('comment', CommentController::class);
    // Notification
     Route::resource('notification', NotificationController::class)->only(['index']);
    // Transaction //
    Route::resource('transaction', TransactionController::class);
    Route::resource('aiaudio_transaction', AiaudioTransactionController::class);
    Route::get('aiaudio_transaction/invoice/{id}', [AiaudioTransactionController::class,'aiaudio_invoice']);
    // Setting //
    
    Route::get('setting', [SettingController::class, 'index'])->name('setting');
    Route::post('setting/app', [SettingController::class, 'app'])->name('settingapp');
    Route::post('setting/currency', [SettingController::class, 'currency'])->name('settingcurrency');
    Route::post('setting/changepassword', [SettingController::class, 'changepassword'])->name('settingchangepassword');
    Route::post('setting/admob', [SettingController::class, 'admob_android'])->name('settingadmob_android');
    Route::post('setting/admob-ios', [SettingController::class, 'admob_ios'])->name('settingadmob_ios');
    Route::post('setting/facebookad', [SettingController::class, 'facebookad'])->name('settingfacebookad');
    Route::post('setting/facebookad-ios', [SettingController::class, 'facebookad_ios'])->name('settingfacebookad_ios');
    // Setting (SMTP)
    Route::get('setting/smtp', [SettingController::class, 'smtpindex'])->name('settingsmtpindex');
    Route::post('setting/smtp', [SettingController::class, 'smtp'])->name('settingsmtp');
    Route::any('check_smtp', [SettingController::class,'check_smtp'])->name('check_smtp');

    // Payment
    Route::resource('payment', PaymentController::class)->only(['index']);

    // Custom Subscription
    Route::resource('custom-package', CustomPackageController::class)->only(['index']);
    Route::resource('custom-package', CustomPackageController::class)->only(['create', 'store', 'edit', 'update', 'show', 'destroy']);
    Route::resource('smart-collection', SmartCollectionController::class);
    Route::get('smart-collection/delete/{id}', [EBookController::class, 'deleteSmartCollection'])->name('smart-collection.deleteSmartCollection');
    Route::post('smart-collection/add-item', [SmartCollectionController::class, 'addItemToSmartCollection'])->name('smart-collection.add-item');
    Route::post('smart-collections/get-list/by-type', [SmartCollectionController::class, 'getSmartCollectionsListByType'])->name('smart-collection.lis-by-type');
    Route::resource('custom-transaction', CustomTransactionController::class);
    Route::post('smart-collection/e-books/get-all', [SmartCollectionController::class, 'getAllEbooks'])->name('smart-collection.ebooks.get-all');
    Route::post('smart-collection/audio-books/get-all', [SmartCollectionController::class, 'getAllAudioBooks'])->name('smart-collection.audiobooks.get-all');
    Route::post('smart-collection/artists/get-all', [SmartCollectionController::class, 'getArtists'])->name('smart-collection.artists.get-all');
    Route::post('smart-collection/categories/get-all', [SmartCollectionController::class, 'getCategories'])->name('smart-collection.categories.get-all');
    Route::post('smart-collection/create', [SmartCollectionController::class, 'createSmartCollection'])->name('smart-collection.create');
    Route::post('smart-collection/update-data', [SmartCollectionController::class, 'updateSmartCollection'])->name('smart-collection.update-data');
    Route::post('smart-collection/get/{id}', [SmartCollectionController::class, 'getSmartCollectionDataById'])->name('smart-collection.get');
});

