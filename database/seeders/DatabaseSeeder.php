<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Setting;
use App\Models\Slider;
use App\Models\Agenda;
use App\Models\ManagementDesk;
use App\Models\CommitteeMember;
use App\Models\Timeline;
use App\Models\Event;
use App\Models\Update;
use App\Models\Gallery;
use App\Models\BusinessCategory;
use App\Models\MemberProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        // 1. Spatie Roles
        $adminRole = Role::firstOrCreate(['name' => 'Administrator']);
        $memberRole = Role::firstOrCreate(['name' => 'Member']);

        // 2. Default Users
        // Admin
        $admin = User::updateOrCreate(
            ['email' => 'admin@community.com'],
            [
                'name' => 'Admin Administrator',
                'password' => Hash::make('password'),
               'status' => 'approved',
                'member_code' => 'ADMIN001',
            ]
        );
        $admin->assignRole($adminRole);

        // Approved Member
        $member = User::updateOrCreate(
            ['email' => 'member@community.com'],
            [
                'name' => 'Karan Sathwara',
                'password' => Hash::make('password'),
                 'status' => 'approved',
                'member_code' => 'MEMBER001',
            ]
        );
        $member->assignRole($memberRole);

        // Add Member Profile for Karan
        MemberProfile::updateOrCreate(
            ['user_id' => $member->id],
            [
                'first_name' => 'Karan',
                'middle_name' => 'Ramanlal',
                'last_name' => 'Sathwara',
                'gender' => 'Male',
                'dob' => '1992-05-12',
                'blood_group' => 'O+',
                'education' => 'Bachelor of Engineering',
                'occupation' => 'Software Developer',
                'phone' => '9876543210',
                'whatsapp' => '9876543210',
                'address' => '402 Sunrise Apartments, Bodakdev',
                'city' => 'Ahmedabad',
                'state' => 'Gujarat',
                'pincode' => '380054',
                'photo_path' => 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?q=80&w=300',
                'aadhaar_number' => '123456789012',
                'aadhaar_path' => 'dummy/aadhaar.pdf',
                'pan_number' => 'ABCDE1234F',
                'pan_path' => 'dummy/pan.pdf',
            ]
        );

        // Pending Member
        $pendingUser = User::updateOrCreate(
            ['email' => 'pending@community.com'],
            [
                'name' => 'Vijay Sathwara',
                'password' => Hash::make('password'),
                'status' => 'pending',
                'member_code' => 'PENDING001',
            ]
        );
        $pendingUser->assignRole($memberRole);

        MemberProfile::updateOrCreate(
            ['user_id' => $pendingUser->id],
            [
                'first_name' => 'Vijay',
                'middle_name' => 'Manilal',
                'last_name' => 'Sathwara',
                'gender' => 'Male',
                'dob' => '1995-10-20',
                'blood_group' => 'A+',
                'education' => 'Bachelor of Commerce',
                'occupation' => 'Accountant',
                'phone' => '9988776655',
                'whatsapp' => '9988776655',
                'address' => '102 Shivalik Residency, Satellite',
                'city' => 'Ahmedabad',
                'state' => 'Gujarat',
                'pincode' => '380015',
                'photo_path' => 'https://images.unsplash.com/photo-1599566150163-29194dcaad36?q=80&w=300',
                'aadhaar_number' => '987654321098',
                'aadhaar_path' => 'dummy/aadhaar.pdf',
            ]
        );

        // 3. Business Categories
        $categories = [
            ['name' => 'Retail & Shops', 'slug' => 'retail-shops'],
            ['name' => 'Construction & Real Estate', 'slug' => 'construction-real-estate'],
            ['name' => 'Information Technology', 'slug' => 'information-technology'],
            ['name' => 'Medical & Healthcare', 'slug' => 'medical-healthcare'],
            ['name' => 'Education & Coaching', 'slug' => 'education-coaching'],
            ['name' => 'Professional Services', 'slug' => 'professional-services'],
            ['name' => 'Food & Restaurants', 'slug' => 'food-restaurants'],
            ['name' => 'Textiles & Garments', 'slug' => 'textiles-garments'],
        ];
        foreach ($categories as $cat) {
            BusinessCategory::updateOrCreate(['slug' => $cat['slug']], $cat);
        }

        // 4. Default Settings
        $settings = [
            'website_name' => 'Shree Satwara Gnati Mandal, Ahmedabad',
            'website_logo' => '',
            'website_favicon' => '',
            'primary_color' => '#2563EB',
            'seo_title' => 'Shree Satwara Gnati Mandal, Ahmedabad',
            'seo_description' => 'Welcome to the official portal of the Shree Satwara Gnati Mandal, Ahmedabad. Stay connected, register your business, view events, and manage membership details.',
            'contact_address' => "1, Satwara Samaj Bhavan,\nOpp. Siddheswar Shopping,\nViratnagar-Manmohan Road,\nOdhav, Ahmedabad -382415",
            'contact_email' => 'info@sathwaracommunity.com',
            'contact_phone' => '+91-6353785519',
            'contact_whatsapp' => '9876543210',
            'contact_map_iframe' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d117512.63777524958!2d72.48202534579998!3d23.014588975924775!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x395e848aba5bd449%3A0x4fccd11d080e11ec!2sAhmedabad%2C%20Gujarat!5e0!3m2!1sen!2sin!4v1700000000000!5m2!1sen!2sin',
            'social_facebook' => 'https://facebook.com',
            'social_twitter' => 'https://twitter.com',
            'social_instagram' => 'https://instagram.com',
            'footer_text' => '© 2026 Shree Satwara Gnati Mandal, Ahmedabad. All rights reserved.',
        ];
        foreach ($settings as $key => $val) {
            Setting::set($key, $val);
        }

        // 5. Sliders
        Slider::truncate();
        Slider::create([
            'title' => 'Shree Satwara Gnati Mandal, Ahmedabad',
            'subtitle' => 'Connecting families, preserving traditions, and empowering local businesses.',
            'image_path' => 'https://images.unsplash.com/photo-1543269865-cbf427effbad?q=80&w=1200',
            'button_text' => 'Join Membership',
            'button_link' => '/register/member',
            'status' => true,
            'display_order' => 1
        ]);
        Slider::create([
            'title' => 'Promote Community Businesses',
            'subtitle' => 'Register your business in our directory and build strong network connections.',
            'image_path' => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?q=80&w=1200',
            'button_text' => 'Business Directory',
            'button_link' => '/business-directory',
            'status' => true,
            'display_order' => 2
        ]);

        // 6. Agendas
        Agenda::truncate();
        Agenda::create([
            'title' => 'Social Integration',
            'title_gu' => 'સામાજિક એકતા',
            'description' => 'Uniting families through annual gatherings, cultural events, and social initiatives.',
            'description_gu' => 'વાર્ષિક સ્નેહ મિલન, સાંસ્કૃતિક કાર્યક્રમો અને સામાજિક પહેલો દ્વારા પરિવારોને એકત્રિત કરવા.',
            'icon' => 'users',
            'display_order' => 1
        ]);
        Agenda::create([
            'title' => 'Educational Excellence',
            'title_gu' => 'શૈક્ષણિક શ્રેષ્ઠતા',
            'description' => 'Honoring students, offering scholarships, and guiding youth towards successful careers.',
            'description_gu' => 'વિદ્યાર્થીઓનું સન્માન, શિષ્યવૃત્તિ અને યુવાનોને સફળ કારકિર્દી તરફ માર્ગદર્શન આપવું.',
            'icon' => 'academic-cap',
            'display_order' => 2
        ]);
        Agenda::create([
            'title' => 'Economic Empowerment',
            'title_gu' => 'આર્થિક સશક્તિકરણ',
            'description' => 'Fostering growth by promoting community businesses and professional collaborations.',
            'description_gu' => 'સમુદાયના ઉદ્યોગસાહસિકોને ટેકો આપવો અને વ્યવસાયિક નેટવર્કિંગની તકો ઊભી કરવી.',
            'icon' => 'briefcase',
            'display_order' => 3
        ]);

        // 7. Management Desk
        ManagementDesk::truncate();
        ManagementDesk::create([
            'name' => 'Ramanbhai Sathwara',
            'designation' => 'President',
            'photo_path' => 'https://images.unsplash.com/photo-1566492031773-4f4e44671857?q=80&w=300',
            'display_order' => 1,
            'status' => true
        ]);
        ManagementDesk::create([
            'name' => 'Gitaben Sathwara',
            'designation' => 'Secretary',
            'photo_path' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?q=80&w=300',
            'display_order' => 2,
            'status' => true
        ]);

        // 8. Committee Members
        CommitteeMember::truncate();
        CommitteeMember::create([
            'name' => 'Kiritbhai Sathwara',
            'designation' => 'Vice President',
            'photo_path' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?q=80&w=300',
            'display_order' => 1,
            'status' => true
        ]);
        CommitteeMember::create([
            'name' => 'Arvindbhai Sathwara',
            'designation' => 'Treasurer',
            'photo_path' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?q=80&w=300',
            'display_order' => 2,
            'status' => true
        ]);
        CommitteeMember::create([
            'name' => 'Dineshbharthi Sathwara',
            'designation' => 'Executive Committee',
            'photo_path' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?q=80&w=300',
            'display_order' => 3,
            'status' => true
        ]);

        // 9. Timelines
        Timeline::truncate();
        Timeline::create([
            'year' => '1995',
            'title' => 'Community Inception',
            'description' => 'Founded by senior community members to build mutual trust and organize cultural functions.',
            'display_order' => 1
        ]);
        Timeline::create([
            'year' => '2010',
            'title' => 'Community Building Inauguration',
            'description' => 'Completed construction of the community center with modern facilities for sports and banquets.',
            'display_order' => 2
        ]);
        Timeline::create([
            'year' => '2026',
            'title' => 'Digital Management Portal',
            'description' => 'Launched this website to streamline registrations, directory management, and award submissions.',
            'display_order' => 3
        ]);

        // 10. Events
        Event::truncate();
        $event1 = Event::create([
            'title' => 'Annual Sports Festival 2026',
            'description' => 'Get ready for our annual sports championship! Cricket, Volleyball, Badminton, and races for children. Free registrations for all members.',
            'venue' => 'Sathwara Ground, Satellite, Ahmedabad',
            'date' => '2026-08-15',
            'time' => '08:00:00',
            'banner_path' => 'https://images.unsplash.com/photo-1461896836934-ffe607ba8211?q=80&w=800',
            'registration_option' => true,
            'max_participants' => 300,
            'status' => 'published'
        ]);

        $event2 = Event::create([
            'title' => 'Shikshan Sanman & Inam Vitaran 2026',
            'description' => '',
            'venue' => '1, Satwara Samaj Bhavan, Opp. Siddheswar Shopping, Viratnagar-Manmohan Road, Odhav, Ahmedabad -382415',
            'date' => '2026-09-05',
            'time' => '17:00:00',
            'banner_path' => 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?q=80&w=800',
            'registration_option' => true,
            'max_participants' => 500,
            'status' => 'published'
        ]);

        // 11. Updates
        Update::truncate();
        Update::create([
            'title' => 'Medical Camp for Seniors',
            'description' => 'Free medical consultation and health check-ups for senior citizens on Sunday morning. Register in advance.',
            'publish_date' => '2026-07-20',
            'image_path' => 'https://images.unsplash.com/photo-1505751172876-fa1923c5c528?q=80&w=800',
            'status' => 'published'
        ]);
        Update::create([
            'title' => 'Career Guidance & Skill Seminar',
            'description' => 'Learn from industrial veterans and choose the right career path. Useful session for graduates.',
            'publish_date' => '2026-07-28',
            'image_path' => 'https://images.unsplash.com/photo-1475721027785-f74eccf877e2?q=80&w=800',
            'status' => 'published'
        ]);

        // 12. Galleries
        Gallery::truncate();
        Gallery::create([
            'event_id' => null,
            'image_path' => 'https://images.unsplash.com/photo-1511795409834-ef04bbd61622?q=80&w=800',
            'caption' => 'Annual Gathering Celebration 2025',
            'display_order' => 1
        ]);
        Gallery::create([
            'event_id' => null,
            'image_path' => 'https://images.unsplash.com/photo-1517457373958-b7bdd4587205?q=80&w=800',
            'caption' => 'Youth Cultural Event',
            'display_order' => 2
        ]);

        // 13. Members Dataset Import
        $this->call(MemberImportSeeder::class);

        Schema::enableForeignKeyConstraints();
    }
}
