<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class AppointmentService extends Model
{
    protected $fillable = [
        'appointment_id', 'service_id', 'price', 'duration'
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get available time slots for a beautician on a given date and total duration
     */
    public static function getAvailableTimeSlots($beauticianId, $totalDuration, $date)
    {
        // Get all availabilities for the beautician on the given day of week
        $dayOfWeek = date('l', strtotime($date));
        $availabilities = \App\Models\BeauticianAvailability::where('beautician_id', $beauticianId)
            ->where('day_of_week', $dayOfWeek)
            ->get();

        $availableSlots = collect();

        foreach ($availabilities as $availability) {
            $slots = \App\Models\TimeSlot::where('start_time', '>=', $availability->start_time)
                ->where('end_time', '<=', $availability->end_time)
                ->get();

            foreach ($slots as $slot) {
                // Check for overlapping appointments
                $overlap = \App\Models\Appointment::where('beautician_id', $beauticianId)
                    ->where('date', $date)
                    ->where(function ($query) use ($slot) {
                        $query->where('start_time', '<', $slot->end_time)
                              ->where('end_time', '>', $slot->start_time);
                    })
                    ->exists();

                $slotDuration = \Carbon\Carbon::parse($slot->end_time)->diffInMinutes(\Carbon\Carbon::parse($slot->start_time));

                if (!$overlap && $slotDuration >= $totalDuration) {
                    $availableSlots->push($slot);
                }
            }
        }

        return $availableSlots;
    }
}
