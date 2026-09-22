<?php

use App\Http\Controllers\PublicController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\Member\DashboardController as MemberDashboard;
use App\Http\Controllers\Member\FamilyController as MemberFamily;
use App\Http\Controllers\Member\AwardController as MemberAward;
use App\Http\Controllers\Member\EventController as MemberEvent;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\MemberController as AdminMember;
use App\Http\Controllers\Admin\BusinessController as AdminBusiness;
use App\Http\Controllers\Admin\EventController as AdminEvent;
use App\Http\Controllers\Admin\GalleryController as AdminGallery;
use App\Http\Controllers\Admin\ContentController as AdminContent;
use App\Http\Controllers\Admin\SettingsController as AdminSettings;
use App\Http\Controllers\Admin\EmailSettingsController as AdminEmailSettings;
use App\Http\Controllers\Admin\AwardController as AdminAward;
use App\Http\Controllers\Admin\AreaController as AdminArea;
use App\Http\Controllers\Admin\SponsorshipController as AdminSponsorship;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\SubAdminController;
use App\Http\Controllers\Business\BusinessAuthController;
use App\Http\Controllers\Business\DashboardController as BusinessDashboard;
use App\Http\Controllers\Admin\NotificationController as AdminNotification;
use Illuminate\Support\Facades\Route;

// ================= LANGUAGE TOGGLE =================
Route::get('/locale/{lang}', [PublicController::class, 'setLocale'])->name('locale.set');

// ================= PUBLIC SITE =================
Route::get('/', [PublicController::class, 'index'])->name('home');
Route::get('/about-us', [PublicController::class, 'about'])->name('about');
Route::get('/management-desk', [PublicController::class, 'managementDesk'])->name('management_desk');
Route::get('/events', [PublicController::class, 'events'])->name('events');
Route::get('/events/{id}', [PublicController::class, 'eventDetails'])->name('event.details');
Route::get('/events/{id}/register', [PublicController::class, 'showPublicRegistrationForm'])->name('events.public_register_form');
Route::post('/events/{id}/register', [PublicController::class, 'registerEvent'])->name('events.public_register');
Route::post('/events/{id}/sponsor', [PublicController::class, 'registerSponsor'])->name('events.sponsor.register');
Route::get('/updates', [PublicController::class, 'updates'])->name('updates');
Route::get('/updates/{id}', [PublicController::class, 'updateDetails'])->name('update.details');
Route::get('/gallery', [PublicController::class, 'gallery'])->name('gallery');
Route::get('/business-directory', [PublicController::class, 'businessDirectory'])->name('business.directory');
Route::get('/business-directory/{id}', [PublicController::class, 'businessDetails'])->name('business.details');
Route::get('/contact-us', [PublicController::class, 'contact'])->name('contact');
Route::post('/contact-us', [PublicController::class, 'contactSubmit'])->name('contact.submit')->middleware('throttle:5,1');

// ================= REGISTRATION FORMS =================
Route::middleware('guest')->group(function () {
    // Single-page member signup
    Route::get('/register/member', [RegistrationController::class, 'showMemberRegister'])->name('register.member');
    Route::post('/register/member', [RegistrationController::class, 'submitMemberRegister'])->name('register.member.submit');
    Route::post('/register/member/pre-validate', [RegistrationController::class, 'preValidateMember'])->name('register.member.pre_validate');
    Route::post('/register/member/send-otp', [RegistrationController::class, 'sendRegistrationOtp'])->name('register.member.send_otp')->middleware('throttle:5,10');
    Route::post('/register/member/verify-otp', [RegistrationController::class, 'verifyRegistrationOtp'])->name('register.member.verify_otp');
});


// Business signup (Public - can be submitted by guests or logged-in members)
Route::get('/register/business', [RegistrationController::class, 'showBusinessRegister'])->name('register.business');
Route::post('/register/business', [RegistrationController::class, 'submitBusinessRegister'])->name('register.business.submit');
Route::post('/register/business/send-otp', [RegistrationController::class, 'sendBusinessRegistrationOtp'])->name('register.business.send_otp')->middleware('throttle:5,10');
Route::post('/register/business/verify-otp', [RegistrationController::class, 'verifyBusinessRegistrationOtp'])->name('register.business.verify_otp');
Route::get('/api/check-member-id', [RegistrationController::class, 'checkMemberId'])->name('api.check_member_id')->middleware('throttle:30,1');
Route::get('/api/lookup-father-member', [RegistrationController::class, 'lookupFatherMember'])->name('api.lookup_father_member')->middleware('throttle:30,1');

// ================= BUSINESS PANEL AUTH =================
Route::prefix('business')->name('business.')->group(function () {
    Route::get('/login', [BusinessAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [BusinessAuthController::class, 'login'])->name('login.submit')->middleware('throttle:10,1');
    Route::post('/logout', [BusinessAuthController::class, 'logout'])->name('logout');

    // Business Forgot / Reset Password
    Route::get('/forgot-password', [\App\Http\Controllers\Business\BusinessPasswordResetController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [\App\Http\Controllers\Business\BusinessPasswordResetController::class, 'sendOtp'])->name('password.email')->middleware('throttle:5,10');
    Route::get('/verify-otp', [\App\Http\Controllers\Business\BusinessPasswordResetController::class, 'showVerifyOtpForm'])->name('password.otp.verify.form');
    Route::post('/verify-otp', [\App\Http\Controllers\Business\BusinessPasswordResetController::class, 'verifyOtp'])->name('password.otp.verify.submit');
    Route::get('/reset-password', [\App\Http\Controllers\Business\BusinessPasswordResetController::class, 'showResetPasswordForm'])->name('password.reset');
    Route::post('/reset-password', [\App\Http\Controllers\Business\BusinessPasswordResetController::class, 'resetPassword'])->name('password.store');
});

// ================= BUSINESS PANEL (Protected) =================
Route::middleware(['auth:business'])->prefix('business')->name('business.')->group(function () {
    Route::get('/', function () {
        return redirect()->route('business.profile.edit');
    });
    Route::get('/dashboard', [BusinessDashboard::class, 'index'])->name('dashboard');
    Route::get('/profile', [BusinessDashboard::class, 'editProfile'])->name('profile.edit');
    Route::post('/profile', [BusinessDashboard::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/email/send-otp', [BusinessDashboard::class, 'sendProfileEmailOtp'])->name('profile.email.send_otp')->middleware('throttle:5,10');
    Route::post('/profile/email/verify-otp', [BusinessDashboard::class, 'verifyProfileEmailOtp'])->name('profile.email.verify_otp');
    Route::post('/password', [BusinessDashboard::class, 'updatePassword'])->name('password.update');
    Route::get('/renewal', [BusinessDashboard::class, 'renewal'])->name('renewal');
    Route::post('/renewal/pay', [BusinessDashboard::class, 'processRenewal'])->name('renewal.pay');
    Route::post('/renewal/generate-link', [BusinessDashboard::class, 'generateRenewalLink'])->name('renewal.generateLink');
    Route::get('/renewal/callback', [BusinessDashboard::class, 'renewalCallback'])->name('renewal.callback');
});

// ================= ACCOUNT STATUS PAGE & REDIRECT =================
// Guarded by auth, but accessible even if NOT approved (so they see the pending/rejected status)
Route::middleware(['auth'])->group(function () {
    Route::get('/account-status', [MemberDashboard::class, 'status'])->name('account.status');
    Route::get('/dashboard', function () {
        $user = auth()->user();
        if ($user->hasRole('Administrator')) {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('member.dashboard');
    })->name('dashboard');
});

// ================= MEMBER PANEL =================
// Guarded by auth, Role Member, and EnsureUserIsApproved middleware
Route::middleware(['auth', 'role:Member', 'approved'])->prefix('member')->name('member.')->group(function () {
    // Dashboard & Profile
    Route::get('/dashboard', [MemberDashboard::class, 'index'])->name('dashboard');
    Route::get('/profile', [MemberDashboard::class, 'editProfile'])->name('profile.edit');
    Route::post('/profile', [MemberDashboard::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/password', [MemberDashboard::class, 'updatePassword'])->name('profile.update_password');
    Route::get('/account-settings', [MemberDashboard::class, 'accountSettings'])->name('account.settings');
    Route::post('/account-settings/email/send-otp', [MemberDashboard::class, 'sendEmailOtp'])->name('account.settings.send_otp');
    Route::post('/account-settings/email/verify-otp', [MemberDashboard::class, 'verifyEmailOtp'])->name('account.settings.verify_otp');
    Route::get('/account-settings/email/cancel-otp', [MemberDashboard::class, 'cancelEmailOtp'])->name('account.settings.cancel_otp');
    Route::post('/account-settings/password', [MemberDashboard::class, 'updatePassword'])->name('account.settings.update_password');
    Route::get('/membership-card', [MemberDashboard::class, 'membershipCard'])->name('card');
    Route::get('/my-businesses', [MemberDashboard::class, 'myBusinesses'])->name('businesses.my');
    Route::get('/directory', [MemberDashboard::class, 'directory'])->name('directory');

    // Family CRUD
    Route::resource('family', MemberFamily::class);

    // Event Registration & Listing/Viewing inside Member Portal
    Route::get('/events', [MemberEvent::class, 'index'])->name('events.index');
    Route::get('/events/{id}', [MemberEvent::class, 'show'])->name('events.show');
    Route::get('/events/{id}/register', [MemberEvent::class, 'showRegistrationForm'])->name('events.register_form');
    Route::post('/events/{id}/register', [PublicController::class, 'registerEvent'])->name('events.register');
    Route::delete('/events/registrations/{id}', [PublicController::class, 'deleteRegistration'])->name('events.registrations.destroy');

    // Award Claims
    Route::get('/awards', [MemberAward::class, 'index'])->name('awards.index');
    Route::get('/awards/apply', [MemberAward::class, 'create'])->name('awards.create');
    Route::post('/awards/apply', [MemberAward::class, 'store'])->name('awards.store');
});

// ================= ADMINISTRATOR AUTH & PANEL =================
// Dedicated Admin Login & Logout
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
});

// Guarded by auth, and Role Administrator or Sub Admin
Route::middleware(['auth', 'role:Administrator|Sub Admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard overview (all logged in admins/sub-admins)
    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');

    // Notifications (all logged in admins/sub-admins manage their own inbox)
    Route::get('/notifications', [AdminNotification::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{id}/read', [AdminNotification::class, 'read'])->name('notifications.read');
    Route::post('/notifications/mark-all-read', [AdminNotification::class, 'markAllRead'])->name('notifications.markAllRead');

    // Sub-Admins Management (Administrator role only)
    Route::get('/sub-admins', [SubAdminController::class, 'index'])->name('sub_admins.index');
    Route::post('/sub-admins', [SubAdminController::class, 'store'])->name('sub_admins.store');
    Route::put('/sub-admins/{id}', [SubAdminController::class, 'update'])->name('sub_admins.update');
    Route::put('/sub-admins/{id}/events', [SubAdminController::class, 'updateEventPermissions'])->name('sub_admins.update_events');
    Route::delete('/sub-admins/{id}', [SubAdminController::class, 'destroy'])->name('sub_admins.destroy');

    // Members list, detail sheet, approvals, CSV export, print
    Route::middleware(['permission_check:members_manage'])->group(function () {
        Route::get('/members', [AdminMember::class, 'index'])->name('members.index');
        Route::get('/members/export', [AdminMember::class, 'exportCsv'])->name('members.export');
        Route::get('/members/print', [AdminMember::class, 'printList'])->name('members.print');
        Route::get('/members/create', [AdminMember::class, 'create'])->name('members.create');
        Route::post('/members/store', [AdminMember::class, 'store'])->name('members.store');
        Route::get('/members/{id}', [AdminMember::class, 'show'])->name('members.show');
        Route::get('/members/{id}/edit', [AdminMember::class, 'edit'])->name('members.edit');
        Route::post('/members/{id}/update', [AdminMember::class, 'update'])->name('members.update');
        Route::post('/members/{id}/approve', [AdminMember::class, 'approve'])->name('members.approve');
        Route::post('/members/{id}/reject', [AdminMember::class, 'reject'])->name('members.reject');
        Route::post('/members/{id}/toggle-account-status', [AdminMember::class, 'toggleAccountStatus'])->name('members.toggle_account_status');
        Route::delete('/members/{id}', [AdminMember::class, 'destroy'])->name('members.destroy');
    });

    // Areas Management
    Route::middleware(['permission_check:areas_manage'])->group(function () {
        Route::get('/areas', [AdminArea::class, 'index'])->name('areas.index');
        Route::get('/areas/export', [AdminArea::class, 'exportCsv'])->name('areas.export');
        Route::get('/areas/sample-csv', [AdminArea::class, 'downloadSampleCsv'])->name('areas.sample_csv');
        Route::post('/areas/import', [AdminArea::class, 'importCsv'])->name('areas.import');
        Route::post('/areas', [AdminArea::class, 'store'])->name('areas.store');
        Route::put('/areas/{id}', [AdminArea::class, 'update'])->name('areas.update');
        Route::delete('/areas/{id}', [AdminArea::class, 'destroy'])->name('areas.destroy');
    });

    // Businesses and Categories
    Route::middleware(['permission_check:businesses_manage'])->group(function () {
        Route::get('/businesses', [AdminBusiness::class, 'index'])->name('businesses.index');
        Route::get('/businesses/export', [AdminBusiness::class, 'exportCsv'])->name('businesses.export');
        Route::get('/businesses/{id}', [AdminBusiness::class, 'show'])->name('businesses.show');
        Route::get('/businesses/{id}/edit', [AdminBusiness::class, 'edit'])->name('businesses.edit');
        Route::post('/businesses/{id}/update', [AdminBusiness::class, 'update'])->name('businesses.update');
        Route::post('/businesses/{id}/approve', [AdminBusiness::class, 'approve'])->name('businesses.approve');
        Route::post('/businesses/{id}/reject', [AdminBusiness::class, 'reject'])->name('businesses.reject');
        Route::post('/businesses/{id}/deactivate', [AdminBusiness::class, 'deactivate'])->name('businesses.deactivate');
        Route::post('/businesses/{id}/activate', [AdminBusiness::class, 'activate'])->name('businesses.activate');
        Route::delete('/businesses/{id}', [AdminBusiness::class, 'destroy'])->name('businesses.destroy');

        Route::post('/businesses/{id}/payment-links', [AdminBusiness::class, 'generatePaymentLink'])->name('businesses.paymentLinks.generate');
        Route::post('/businesses/{id}/payment-links/{linkId}/resend', [AdminBusiness::class, 'resendPaymentLinkEmail'])->name('businesses.paymentLinks.resend');
        Route::post('/businesses/{id}/payment-links/{linkId}/mark-paid', [AdminBusiness::class, 'markPaymentLinkPaid'])->name('businesses.paymentLinks.markPaid');

        Route::get('/business-categories', [AdminBusiness::class, 'categories'])->name('businesses.categories');
        Route::post('/business-categories', [AdminBusiness::class, 'storeCategory'])->name('businesses.categories.store');
        Route::get('/business-categories/{id}/edit', [AdminBusiness::class, 'editCategory'])->name('businesses.categories.edit');
        Route::post('/business-categories/{id}/update', [AdminBusiness::class, 'updateCategory'])->name('businesses.categories.update');
        Route::delete('/business-categories/{id}', [AdminBusiness::class, 'destroyCategory'])->name('businesses.categories.destroy');
    });

    // Events and Registrations
    Route::middleware(['permission_check:events_manage'])->group(function () {
        Route::get('/events/export', [AdminEvent::class, 'exportCsv'])->name('events.export');
        Route::resource('events', AdminEvent::class);
        Route::get('/events/{id}/registrations/export', [AdminEvent::class, 'exportRegistrationsCsv'])->name('events.registrations.export');
        Route::get('/events/{id}/inam-submissions/export', [AdminEvent::class, 'exportInamSubmissionsCsv'])->name('events.inam_submissions.export');
        Route::get('/events/{id}/yuva-submissions/export', [AdminEvent::class, 'exportYuvaSubmissionsCsv'])->name('events.yuva_submissions.export');
        Route::get('/events/{id}/registrations', [AdminEvent::class, 'registrations'])->name('events.registrations');
        Route::get('/events/registrations/{id}/edit', [AdminEvent::class, 'editRegistration'])->name('events.registrations.edit');
        Route::post('/events/registrations/{id}/update', [AdminEvent::class, 'updateRegistration'])->name('events.registrations.update');
        Route::post('/events/registrations/{id}/toggle-select', [AdminEvent::class, 'toggleSelectRegistration'])->name('events.registrations.toggle_select');
        Route::post('/events/registrations/{id}/approve', [AdminEvent::class, 'approveRegistration'])->name('events.registrations.approve');
        Route::post('/events/registrations/{id}/reject', [AdminEvent::class, 'rejectRegistration'])->name('events.registrations.reject');
        Route::get('/events/{id}/gallery', [AdminEvent::class, 'gallery'])->name('events.gallery');
        Route::post('/events/{id}/gallery', [AdminEvent::class, 'uploadGallery'])->name('events.gallery.upload');
        Route::delete('/events/gallery/{id}', [AdminEvent::class, 'deleteGalleryPhoto'])->name('events.gallery.destroy');

        // Event Gate Scanner & QR Verification
        Route::get('/events/{id}/scanner', [AdminEvent::class, 'showScanner'])->name('events.scanner');
        Route::post('/events/{id}/verify-pass', [AdminEvent::class, 'verifyPass'])->name('events.verify_pass');

        // Event Sponsorships
        Route::post('/events/{id}/sponsorship-types', [AdminSponsorship::class, 'storeType'])->name('events.sponsorship_types.store');
        Route::put('/events/sponsorship-types/{id}', [AdminSponsorship::class, 'updateType'])->name('events.sponsorship_types.update');
        Route::delete('/events/sponsorship-types/{id}', [AdminSponsorship::class, 'destroyType'])->name('events.sponsorship_types.destroy');
        Route::post('/events/sponsorship-types/{id}/toggle-status', [AdminSponsorship::class, 'toggleTypeStatus'])->name('events.sponsorship_types.toggle_status');

        Route::post('/events/{id}/sponsors', [AdminSponsorship::class, 'storeSponsor'])->name('events.sponsors.store');
        Route::put('/events/sponsors/{id}', [AdminSponsorship::class, 'updateSponsor'])->name('events.sponsors.update');
        Route::post('/events/sponsors/{id}/approve', [AdminSponsorship::class, 'approveSponsor'])->name('events.sponsors.approve');
        Route::post('/events/sponsors/{id}/reject', [AdminSponsorship::class, 'rejectSponsor'])->name('events.sponsors.reject');
        Route::delete('/events/sponsors/{id}', [AdminSponsorship::class, 'destroySponsor'])->name('events.sponsors.destroy');
        Route::get('/events/{id}/sponsors/export', [AdminSponsorship::class, 'exportSponsorsCsv'])->name('events.sponsors.export');

        // Student Awards Applications
        Route::get('/awards', [AdminAward::class, 'index'])->name('awards.index');
        Route::get('/awards/export', [AdminAward::class, 'exportCsv'])->name('awards.export');
        Route::get('/awards/{id}', [AdminAward::class, 'show'])->name('awards.show');
        Route::post('/awards/{id}/approve', [AdminAward::class, 'approve'])->name('awards.approve');
        Route::post('/awards/{id}/reject', [AdminAward::class, 'reject'])->name('awards.reject');
        Route::delete('/awards/{id}', [AdminAward::class, 'destroy'])->name('awards.destroy');
    });

    // General Gallery
    Route::middleware(['permission_check:gallery_manage'])->group(function () {
        Route::get('/gallery', [AdminGallery::class, 'index'])->name('gallery.index');
        Route::get('/gallery/export', [AdminGallery::class, 'exportCsv'])->name('gallery.export');
        Route::post('/gallery', [AdminGallery::class, 'store'])->name('gallery.store');
        Route::delete('/gallery/{id}', [AdminGallery::class, 'destroy'])->name('gallery.destroy');
    });

    // Content Management
    Route::middleware(['permission_check:sliders_manage'])->group(function () {
        Route::get('/content/sliders', [AdminContent::class, 'sliders'])->name('content.sliders');
        Route::get('/content/sliders/export', [AdminContent::class, 'exportSlidersCsv'])->name('content.sliders.export');
        Route::post('/content/sliders', [AdminContent::class, 'storeSlider'])->name('content.sliders.store');
        Route::put('/content/sliders/{id}', [AdminContent::class, 'updateSlider'])->name('content.sliders.update');
        Route::delete('/content/sliders/{id}', [AdminContent::class, 'destroySlider'])->name('content.sliders.destroy');
    });

    Route::middleware(['permission_check:agendas_manage'])->group(function () {
        Route::get('/content/agendas', [AdminContent::class, 'agendas'])->name('content.agendas');
        Route::get('/content/agendas/export', [AdminContent::class, 'exportAgendasCsv'])->name('content.agendas.export');
        Route::post('/content/agendas', [AdminContent::class, 'storeAgenda'])->name('content.agendas.store');
        Route::put('/content/agendas/{id}', [AdminContent::class, 'updateAgenda'])->name('content.agendas.update');
        Route::delete('/content/agendas/{id}', [AdminContent::class, 'destroyAgenda'])->name('content.agendas.destroy');
    });

    Route::middleware(['permission_check:desk_manage'])->group(function () {
        Route::get('/content/management-desk', [AdminContent::class, 'managementDesk'])->name('content.desk');
        Route::get('/content/management-desk/export', [AdminContent::class, 'exportDeskCsv'])->name('content.desk.export');
        Route::post('/content/management-desk', [AdminContent::class, 'storeDesk'])->name('content.desk.store');
        Route::put('/content/management-desk/{id}', [AdminContent::class, 'updateDesk'])->name('content.desk.update');
        Route::delete('/content/management-desk/{id}', [AdminContent::class, 'destroyDesk'])->name('content.desk.destroy');
    });

    Route::middleware(['permission_check:committee_manage'])->group(function () {
        Route::get('/content/committee', [AdminContent::class, 'committee'])->name('content.committee');
        Route::get('/content/committee/export', [AdminContent::class, 'exportCommitteeCsv'])->name('content.committee.export');
        Route::post('/content/committee', [AdminContent::class, 'storeCommittee'])->name('content.committee.store');
        Route::put('/content/committee/{id}', [AdminContent::class, 'updateCommittee'])->name('content.committee.update');
        Route::delete('/content/committee/{id}', [AdminContent::class, 'destroyCommittee'])->name('content.committee.destroy');
    });

    Route::middleware(['permission_check:timelines_manage'])->group(function () {
        Route::get('/content/timelines', [AdminContent::class, 'timelines'])->name('content.timelines');
        Route::get('/content/timelines/export', [AdminContent::class, 'exportTimelinesCsv'])->name('content.timelines.export');
        Route::post('/content/timelines', [AdminContent::class, 'storeTimeline'])->name('content.timelines.store');
        Route::put('/content/timelines/{id}', [AdminContent::class, 'updateTimeline'])->name('content.timelines.update');
        Route::delete('/content/timelines/{id}', [AdminContent::class, 'destroyTimeline'])->name('content.timelines.destroy');
    });

    Route::middleware(['permission_check:announcements_manage'])->group(function () {
        Route::get('/content/updates', [AdminContent::class, 'updates'])->name('content.updates');
        Route::get('/content/updates/export', [AdminContent::class, 'exportUpdatesCsv'])->name('content.updates.export');
        Route::post('/content/updates', [AdminContent::class, 'storeUpdate'])->name('content.updates.store');
        Route::put('/content/updates/{id}', [AdminContent::class, 'updateUpdate'])->name('content.updates.update');
        Route::delete('/content/updates/{id}', [AdminContent::class, 'destroyUpdate'])->name('content.updates.destroy');
    });

    // Site & Email Settings
    Route::middleware(['permission_check:settings_manage'])->group(function () {
        Route::get('/settings', [AdminSettings::class, 'index'])->name('settings.index');
        Route::post('/settings', [AdminSettings::class, 'update'])->name('settings.update');

        Route::get('/settings/about', [AdminSettings::class, 'about'])->name('settings.about');
        Route::post('/settings/about', [AdminSettings::class, 'updateAbout'])->name('settings.about.update');

        Route::get('/email-settings', [AdminEmailSettings::class, 'index'])->name('email_settings.index');
        Route::post('/email-settings', [AdminEmailSettings::class, 'update'])->name('email_settings.update');
        Route::post('/email-settings/test', [AdminEmailSettings::class, 'sendTestMail'])->name('email_settings.test');
    });

    // Receipts Download Routes
    Route::prefix('receipts')->name('receipts.')->group(function () {
        Route::get('/membership/{id}', [\App\Http\Controllers\ReceiptController::class, 'downloadMembership'])->name('membership');
        Route::get('/business/{id}', [\App\Http\Controllers\ReceiptController::class, 'downloadBusiness'])->name('business');
        Route::get('/event-pass/{id}', [\App\Http\Controllers\ReceiptController::class, 'downloadEventPass'])->name('event_pass');
        Route::get('/sponsorship/{id}', [\App\Http\Controllers\ReceiptController::class, 'downloadSponsorship'])->name('sponsorship');
    });
});

// Public / Member Receipt Download Routes
Route::prefix('receipts')->name('receipts.')->group(function () {
    Route::get('/event-pass/{id}', [\App\Http\Controllers\ReceiptController::class, 'downloadEventPass'])->name('event_pass');
    Route::get('/sponsorship/{id}', [\App\Http\Controllers\ReceiptController::class, 'downloadSponsorship'])->name('sponsorship');
    Route::get('/membership/{id}', [\App\Http\Controllers\ReceiptController::class, 'downloadMembership'])->name('membership');
    Route::get('/business/{id}', [\App\Http\Controllers\ReceiptController::class, 'downloadBusiness'])->name('business');
});

// Sponsorship Preview Route
Route::get('/preview/sponsorship', [\App\Http\Controllers\ReceiptController::class, 'previewSponsorship'])->name('preview.sponsorship');

// Fallback to Breeze default auth routes
require __DIR__ . '/auth.php';
