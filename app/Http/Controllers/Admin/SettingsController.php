<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    /**
     * Show Settings Form
     */
    public function index()
    {
        $settings = [
            'website_name' => Setting::get('website_name', 'Shree Satwara Gnati Mandal, Ahmedabad'),
            'website_logo' => Setting::get('website_logo'),
            'website_favicon' => Setting::get('website_favicon'),
            'primary_color' => Setting::get('primary_color', '#2563EB'),
            'seo_title' => Setting::get('seo_title'),
            'seo_description' => Setting::get('seo_description'),
            'contact_address' => Setting::get('contact_address'),
            'contact_email' => Setting::get('contact_email'),
            'contact_phone' => Setting::get('contact_phone'),
            'contact_whatsapp' => Setting::get('contact_whatsapp'),
            'contact_map_iframe' => Setting::get('contact_map_iframe'),
            'social_facebook' => Setting::get('social_facebook'),
            'social_twitter' => Setting::get('social_twitter'),
            'social_instagram' => Setting::get('social_instagram'),
            'social_youtube' => Setting::get('social_youtube'),
            'about_mission' => Setting::get('about_mission'),
            'about_vision' => Setting::get('about_vision'),
            'about_history' => Setting::get('about_history'),
            'about_objectives' => Setting::get('about_objectives'),
            'footer_text' => Setting::get('footer_text', '© ' . date('Y') . ' Shree Satwara Gnati Mandal, Ahmedabad. All rights reserved.'),
        ];

        $emailSettings = [
            'mail_mailer'       => Setting::get('mail_mailer', env('MAIL_MAILER', 'smtp')),
            'mail_host'         => Setting::get('mail_host', env('MAIL_HOST', 'smtp.gmail.com')),
            'mail_port'         => Setting::get('mail_port', env('MAIL_PORT', '587')),
            'mail_username'     => Setting::get('mail_username', env('MAIL_USERNAME', '')),
            'mail_password'     => Setting::get('mail_password', env('MAIL_PASSWORD', '')),
            'mail_encryption'   => Setting::get('mail_encryption', env('MAIL_ENCRYPTION', 'tls')),
            'mail_from_address' => Setting::get('mail_from_address', env('MAIL_FROM_ADDRESS', 'noreply@satwaracommunity.com')),
            'mail_from_name'    => Setting::get('mail_from_name', env('MAIL_FROM_NAME', 'Shree Satwara Gnati Mandal, Ahmedabad')),
        ];

        $paymentSettings = [
            'member_signup_fee'          => Setting::get('member_signup_fee', '1000'),
            'business_registration_fee'  => Setting::get('business_registration_fee', '500'),
            'razorpay_key_id'            => Setting::get('razorpay_key_id', env('RAZORPAY_KEY_ID', '')),
            'razorpay_key_secret'        => Setting::get('razorpay_key_secret', env('RAZORPAY_KEY_SECRET', '')),
            'razorpay_webhook_secret'    => Setting::get('razorpay_webhook_secret', env('RAZORPAY_WEBHOOK_SECRET', '')),
        ];

        return view('admin.settings.index', compact('settings', 'emailSettings', 'paymentSettings'));
    }

    /**
     * Save/Update Settings
     */
    public function update(Request $request)
    {
        $request->validate([
            'website_name' => 'sometimes|required|string|max:255',
            'footer_text' => 'nullable|string|max:255',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:2000',
            'primary_color' => 'nullable|string|max:20',
            'contact_phone' => 'nullable|digits:10',
            'contact_email' => 'nullable|email|max:255',
            'website_logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'website_favicon' => 'nullable|file|mimes:png,ico,jpg,jpeg|max:512',
            'contact_whatsapp' => 'nullable|digits:10',
            'contact_address' => 'nullable|string|max:1000',
            'contact_map_iframe' => 'nullable|string',
            'social_facebook' => 'nullable|string|max:255',
            'social_twitter' => 'nullable|string|max:255',
            'social_instagram' => 'nullable|string|max:255',
            'social_youtube' => 'nullable|string|max:255',
            'member_signup_fee' => 'sometimes|required|numeric|min:0|max:1000000',
            'business_registration_fee' => 'sometimes|required|numeric|min:0|max:1000000',
            'razorpay_key_id' => 'nullable|string|max:255',
            'razorpay_key_secret' => 'nullable|string|max:255',
            'razorpay_webhook_secret' => 'nullable|string|max:255',
        ]);

        $activeTab = $request->input('active_tab', 'general');

        // Only the fields these Settings forms actually have; anything else sent is ignored
        $allowed = [
            'website_name', 'footer_text', 'seo_title', 'seo_description', 'primary_color',
            'contact_phone', 'contact_email', 'contact_whatsapp', 'contact_address', 'contact_map_iframe',
            'social_facebook', 'social_twitter', 'social_instagram', 'social_youtube',
            'member_signup_fee', 'business_registration_fee',
            'razorpay_key_id', 'razorpay_key_secret', 'razorpay_webhook_secret',
        ];
        foreach ($request->only($allowed) as $key => $val) {
            Setting::set($key, $val);
        }

        // Logo Upload
        if ($request->hasFile('website_logo')) {
            $oldLogo = Setting::get('website_logo');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }
            $logoPath = $request->file('website_logo')->store('settings', 'public');
            Setting::set('website_logo', $logoPath);
        }

        // Favicon Upload
        if ($request->hasFile('website_favicon')) {
            $oldFavicon = Setting::get('website_favicon');
            if ($oldFavicon && Storage::disk('public')->exists($oldFavicon)) {
                Storage::disk('public')->delete($oldFavicon);
            }
            $faviconPath = $request->file('website_favicon')->store('settings', 'public');
            Setting::set('website_favicon', $faviconPath);
        }

        return redirect()->back()
            ->with('success', 'Settings updated successfully.')
            ->with('active_settings_tab', $activeTab);
    }

    /**
     * Show About Us Page Settings
     */
    public function about()
    {
        $settings = [
            'about_mission_title_en' => Setting::get('about_mission_title_en', 'Empowering People'),
            'about_mission_title_gu' => Setting::get('about_mission_title_gu', 'લોકોને સશક્ત બનાવવું'),
            'about_mission_en' => Setting::get('about_mission_en', Setting::get('about_mission')),
            'about_mission_gu' => Setting::get('about_mission_gu'),

            'about_vision_title_en' => Setting::get('about_vision_title_en', 'Future Prosperity'),
            'about_vision_title_gu' => Setting::get('about_vision_title_gu', 'ભવિષ્યની સમૃદ્ધિ'),
            'about_vision_en' => Setting::get('about_vision_en', Setting::get('about_vision')),
            'about_vision_gu' => Setting::get('about_vision_gu'),

            'about_objectives_title_en' => Setting::get('about_objectives_title_en', 'Strategic Goals'),
            'about_objectives_title_gu' => Setting::get('about_objectives_title_gu', 'વ્યૂહાત્મક લક્ષ્યો'),
            'about_objectives_en' => Setting::get('about_objectives_en', Setting::get('about_objectives')),
            'about_objectives_gu' => Setting::get('about_objectives_gu'),

            'about_history_title_en' => Setting::get('about_history_title_en', 'Heritage & Journey'),
            'about_history_title_gu' => Setting::get('about_history_title_gu', 'વારસો અને યાત્રા'),
            'about_history_en' => Setting::get('about_history_en', Setting::get('about_history')),
            'about_history_gu' => Setting::get('about_history_gu'),
        ];

        $timelines = \App\Models\Timeline::orderBy('display_order')->get();

        return view('admin.settings.about', compact('settings', 'timelines'));
    }

    /**
     * Update About Us Page Settings
     */
    public function updateAbout(Request $request)
    {
        $keys = [
            'about_mission_title_en', 'about_mission_title_gu',
            'about_mission_en', 'about_mission_gu',

            'about_vision_title_en', 'about_vision_title_gu',
            'about_vision_en', 'about_vision_gu',

            'about_objectives_title_en', 'about_objectives_title_gu',
            'about_objectives_en', 'about_objectives_gu',

            'about_history_title_en', 'about_history_title_gu',
            'about_history_en', 'about_history_gu',
        ];

        foreach ($keys as $key) {
            if ($request->has($key)) {
                Setting::set($key, $request->input($key));
            }
        }

        return redirect()->back()->with('success', 'About Us configuration saved successfully.');
    }
}
