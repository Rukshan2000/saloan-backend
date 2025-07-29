<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TimeSlot;
use DateTime;
use DateInterval;

class TimeSlotSeeder extends Seeder
{
    public function run()
    {
        $start = new DateTime('00:00');
        $end = new DateTime('24:00');
        $interval = new DateInterval('PT15M');
        $slots = [];
        while ($start < $end) {
            $slotStart = $start->format('H:i:s');
            $start->add($interval);
            $slotEnd = $start->format('H:i:s');
            if ($start > $end) break;
            $slots[] = [
                'start_time' => $slotStart,
                'end_time' => $slotEnd,
            ];
        }
        foreach ($slots as $slot) {
            TimeSlot::create($slot);
        }
    }
}
