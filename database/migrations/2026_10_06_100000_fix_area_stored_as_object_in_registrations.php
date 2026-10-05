<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Inam submissions saved the member's whole area record ({"id":4,"name":"Vastral",...}) in
     * form_data.area instead of its name. Keep just the name.
     */
    public function up(): void
    {
        DB::table('event_registrations')->where('form_data', 'like', '%"area":{%')->orderBy('id')
            ->each(function ($row) {
                $data = json_decode($row->form_data, true);
                if (is_array($data) && is_array($data['area'] ?? null)) {
                    $data['area'] = $data['area']['name'] ?? '';
                    DB::table('event_registrations')->where('id', $row->id)
                        ->update(['form_data' => json_encode($data, JSON_UNESCAPED_UNICODE)]);
                }
            });
    }

    public function down(): void
    {
        // Nothing to restore: only the area name is meaningful.
    }
};
