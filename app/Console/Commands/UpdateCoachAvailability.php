<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Classes;
use Carbon\Carbon;

class UpdateCoachAvailability extends Command
{
    protected $signature = 'coaches:update-availability';
    protected $description = 'Update availability status of coaches based on scheduled classes';

    public function handle()
    {
        $today = Carbon::now()->format('l'); // Hari ini
        $classesToday = Classes::where('day_of_week', $today)->get();

        // Set semua coach sebagai available
        User::where('role', 'coach')->update(['availability_status' => 1]);

        foreach ($classesToday as $class) {
            User::where('id', $class->coach_id)->update(['availability_status' => 0]);
        }

        $this->info('Ketersediaan coach diperbarui berdasarkan kelas yang dijadwalkan hari ini.');
    }
}
