<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Service;
use App\Models\ServiceBeautician;
use App\Models\BeauticianAvailability;
use App\Models\TimeSlot;
use DateTime;
use DateInterval;

class DemoBookingSeeder extends Seeder
{
    public function run()
    {
        // 1. Create a beautician user
        $beautician = User::create([
            'name' => 'Demo Beautician',
            'email' => 'beautician2@example.com',
            'password' => bcrypt('password'),
            'role' => 2, // Use the actual beautician role ID
        ]);

        // 2. Create a service (15 min duration)
        $service = Service::create([
            'name' => 'Hair Cut',
            'category_id' => 2,
            'duration' => 15,
            'price' => 100,
        ]);

        // 3. Link service and beautician
        ServiceBeautician::create([
            'service_id' => $service->id,
            'beautician_id' => $beautician->id,
        ]);

        // 4. Add beautician availability for tomorrow (full day)
        $date = date('Y-m-d', strtotime('+1 day'));
        $dayOfWeek = date('l', strtotime($date));
        BeauticianAvailability::create([
            'beautician_id' => $beautician->id,
            'day_of_week' => $dayOfWeek,
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
        ]);

        // 5. Seed time slots (09:00 to 17:00, 15 min each)
        $start = new DateTime('09:00');
        $end = new DateTime('17:00');
        $interval = new DateInterval('PT15M');
        while ($start < $end) {
            $slotStart = $start->format('H:i:s');
            $start->add($interval);
            $slotEnd = $start->format('H:i:s');
            if ($start > $end) break;
            TimeSlot::create([
                'start_time' => $slotStart,
                'end_time' => $slotEnd,
            ]);
        }
        // 6. Create demo appointments for the beautician
        $appointmentTimes = [
            ['start' => '09:00:00', 'end' => '09:15:00'],
            ['start' => '10:00:00', 'end' => '10:15:00'],
            ['start' => '11:30:00', 'end' => '11:45:00'],
        ];
        foreach ($appointmentTimes as $i => $times) {
            \App\Models\Appointment::create([
                'customer_id' => $beautician->id, // For demo, use beautician as customer
                'beautician_id' => $beautician->id,
                'branch_id' => 5, // Adjust as needed
                'date' => $date,
                'start_time' => $times['start'],
                'end_time' => $times['end'],
                'status' => 'SCHEDULED',
                'receipt_number' => 'RDEMO' . ($i + 1),
            ]);
        }
    }
}
