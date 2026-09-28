<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\MemberProfile;
use App\Models\Area;

class SheetMemberImportSeeder extends Seeder
{
    /**
     * Google Sheet CSV export URL
     */
    const SHEET_URL = 'https://docs.google.com/spreadsheets/d/1oD6sAeK9IF00tm0gzUH3M7ZQHDtEAiErJj-8FtjJD2c/export?format=csv&gid=2030366341';

    public function run(): void
    {
        $this->command->info('Fetching data from Google Sheets...');

        // --- Download CSV ---
        $csv = @file_get_contents(self::SHEET_URL);
        if ($csv === false) {
            $this->command->error('Failed to download sheet. Check internet / URL.');
            return;
        }

        // Parse CSV
        $lines = array_filter(array_map('trim', explode("\n", $csv)));
        array_shift($lines); // remove header row

        $areaCache = [];   // area name -> id cache
        $inserted  = 0;
        $updated   = 0;
        $skipped   = 0;

        foreach ($lines as $line) {
            // Properly parse CSV line (handles quoted fields with commas/newlines)
            $row = str_getcsv($line);

            if (count($row) < 2) continue;

            // Columns: Request No., Member ID, Surname, Name, FatherName, Email ID, Mobile, Column 1, Address, Area
            $memberCode = trim($row[1] ?? '');
            $surname    = trim($row[2] ?? '');
            $firstName  = trim($row[3] ?? '');
            $fatherName = trim($row[4] ?? '');
            $email      = strtolower(trim($row[5] ?? ''));
            $mobile     = preg_replace('/[^0-9]/', '', trim($row[6] ?? ''));
            $address    = trim($row[8] ?? '');
            $areaName   = trim($row[9] ?? '');

            // Skip if no member code
            if (empty($memberCode)) {
                $skipped++;
                continue;
            }

            // Validate member code pattern
            if (!preg_match('/^SSA?M\d+$/i', $memberCode)) {
                $skipped++;
                continue;
            }

            // Normalise mobile: take only first 10 digits
            $mobile = substr($mobile, 0, 10);
            if (strlen($mobile) < 10) $mobile = '';

            // Email validation
            if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $email = '';
            }

            // Resolve area
            $areaId = null;
            if ($areaName) {
                if (!isset($areaCache[$areaName])) {
                    $area = Area::whereRaw('LOWER(name) = ?', [strtolower($areaName)])->first();
                    $areaCache[$areaName] = $area?->id;
                }
                $areaId = $areaCache[$areaName];
            }

            // Full name for users.name
            $fullName = trim("$firstName $surname");

            DB::transaction(function () use (
                $memberCode, $surname, $firstName, $fatherName,
                $email, $mobile, $address, $areaId, $fullName,
                &$inserted, &$updated
            ) {
                // --- Find or create User ---
                $user = User::withTrashed()->where('member_code', $memberCode)->first();

                if ($user) {
                    // Update existing user
                    $updateData = ['name' => $fullName ?: $user->name];
                    // Set email only if user has none and email isn't used by someone else
                    if ($email && !$user->email) {
                        $emailTaken = User::where('email', $email)->where('id', '!=', $user->id)->exists();
                        if (!$emailTaken) $updateData['email'] = $email;
                    }
                    $user->update($updateData);
                    $updated++;
                } else {
                    // Create new user — ensure email uniqueness
                    $emailForInsert = null;
                    if ($email) {
                        $emailTaken = User::where('email', $email)->exists();
                        if (!$emailTaken) $emailForInsert = $email;
                    }
                    $userData = [
                        'name'           => $fullName ?: ($surname ?: 'Member'),
                        'email'          => $emailForInsert,
                        'password'       => Hash::make('Sathwara@123'),
                        'member_code'    => $memberCode,
                        'status'         => 'approved',
                        'account_status' => 'active',
                        'payment_status' => 'paid',
                    ];
                    $user = User::create($userData);
                    $inserted++;
                }


                // --- Upsert MemberProfile ---
                $profile = MemberProfile::where('user_id', $user->id)->first();

                $profileData = [
                    'user_id'    => $user->id,
                    'first_name' => $firstName ?: null,
                    'last_name'  => $surname    ?: null,
                    'middle_name'=> $fatherName ?: null,   // Store father name as middle_name (sheet: FatherName col)
                    'phone'      => $mobile     ?: null,
                    'address'    => $address    ?: null,
                    'area_id'    => $areaId,
                ];

                if ($profile) {
                    $toUpdate = [];
                    foreach ($profileData as $key => $val) {
                        if ($key === 'user_id') continue;
                        // Always overwrite name fields from the authoritative sheet
                        if (in_array($key, ['first_name', 'last_name', 'middle_name']) && !empty($val)) {
                            $toUpdate[$key] = $val;
                        }
                        // Fill empty fields with sheet data
                        if (!in_array($key, ['first_name', 'last_name', 'middle_name']) && !empty($val) && empty($profile->$key)) {
                            $toUpdate[$key] = $val;
                        }
                    }
                    if ($toUpdate) $profile->update($toUpdate);
                } else {
                    MemberProfile::create($profileData);
                }

            });
        }

        $this->command->info("Import complete!");
        $this->command->info("  Inserted: $inserted");
        $this->command->info("  Updated : $updated");
        $this->command->info("  Skipped : $skipped");
    }
}
