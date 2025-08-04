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
     * Based on continuous availability blocks with proper conflict detection
     */
    public static function getAvailableTimeSlots($beauticianId, $totalDuration, $date)
    {
        // Get all availabilities for the beautician on the given day of week
        $dayOfWeek = date('l', strtotime($date));
        $availabilities = \App\Models\BeauticianAvailability::where('beautician_id', $beauticianId)
            ->where('day_of_week', $dayOfWeek)
            ->orderBy('start_time')
            ->get();

        if ($availabilities->isEmpty()) {
            return collect();
        }

        $availableSlots = collect();

        foreach ($availabilities as $availability) {
            // Get continuous available blocks within this availability window
            $continuousBlocks = self::getContinuousAvailableBlocks(
                $beauticianId, 
                $date, 
                $availability->start_time, 
                $availability->end_time
            );

            // Find slots that can accommodate the total duration
            foreach ($continuousBlocks as $block) {
                if ($block['duration_minutes'] >= $totalDuration) {
                    // Generate possible start times within this block
                    $possibleSlots = self::generatePossibleSlots(
                        $block['start_time'], 
                        $block['end_time'], 
                        $totalDuration
                    );
                    
                    foreach ($possibleSlots as $slot) {
                        // Double-check for conflicts with existing bookings
                        if (self::hasNoConflictsWithExistingBookings($slot, $beauticianId, $date)) {
                            $availableSlots->push([
                                'beautician_id' => $beauticianId,
                                'start_time' => $slot['start_time'],
                                'end_time' => $slot['end_time'],
                                'duration_minutes' => $totalDuration,
                                'date' => $date
                            ]);
                        }
                    }
                }
            }
        }

        return $availableSlots->unique(function ($slot) {
            return $slot['start_time'] . '-' . $slot['end_time'];
        })->values();
    }

    /**
     * Get continuous available blocks within a given time window
     */
    private static function getContinuousAvailableBlocks($beauticianId, $date, $windowStart, $windowEnd)
    {
        // Get all existing appointments for the beautician on this date
        $existingAppointments = \App\Models\Appointment::where('beautician_id', $beauticianId)
            ->where('date', $date)
            ->whereIn('status', ['SCHEDULED', 'CONFIRMED', 'IN_PROGRESS'])
            ->orderBy('start_time')
            ->get(['start_time', 'end_time']);

        $blocks = collect();
        $currentStart = $windowStart;

        foreach ($existingAppointments as $appointment) {
            $appointmentStart = $appointment->start_time;
            $appointmentEnd = $appointment->end_time;

            // If there's a gap before this appointment
            if ($currentStart < $appointmentStart) {
                $gapEnd = min($appointmentStart, $windowEnd);
                $durationMinutes = \Carbon\Carbon::parse($gapEnd)->diffInMinutes(\Carbon\Carbon::parse($currentStart));
                
                if ($durationMinutes > 0) {
                    $blocks->push([
                        'start_time' => $currentStart,
                        'end_time' => $gapEnd,
                        'duration_minutes' => $durationMinutes
                    ]);
                }
            }

            // Move current start to after this appointment
            $currentStart = max($currentStart, $appointmentEnd);
            
            // If we've passed the window end, break
            if ($currentStart >= $windowEnd) {
                break;
            }
        }

        // Add final block if there's time remaining
        if ($currentStart < $windowEnd) {
            $durationMinutes = \Carbon\Carbon::parse($windowEnd)->diffInMinutes(\Carbon\Carbon::parse($currentStart));
            if ($durationMinutes > 0) {
                $blocks->push([
                    'start_time' => $currentStart,
                    'end_time' => $windowEnd,
                    'duration_minutes' => $durationMinutes
                ]);
            }
        }

        return $blocks;
    }

    /**
     * Generate possible appointment slots within a time block
     */
    private static function generatePossibleSlots($blockStart, $blockEnd, $requiredDuration)
    {
        $slots = collect();
        $slotInterval = 15; // 15-minute intervals

        $current = \Carbon\Carbon::parse($blockStart);
        $blockEndCarbon = \Carbon\Carbon::parse($blockEnd);

        while ($current->copy()->addMinutes($requiredDuration) <= $blockEndCarbon) {
            $slotEnd = $current->copy()->addMinutes($requiredDuration);
            
            $slots->push([
                'start_time' => $current->format('H:i:s'),
                'end_time' => $slotEnd->format('H:i:s')
            ]);

            $current->addMinutes($slotInterval);
        }

        return $slots;
    }

    /**
     * Check if a proposed appointment slot conflicts with existing bookings
     * Improved conflict detection with comprehensive overlap checking
     */
    private static function hasNoConflictsWithExistingBookings($proposedSlot, $beauticianId, $date)
    {
        $proposedStart = $proposedSlot['start_time'];
        $proposedEnd = $proposedSlot['end_time'];

        // Convert to Carbon instances for better comparison
        $proposedStartCarbon = \Carbon\Carbon::parse($proposedStart);
        $proposedEndCarbon = \Carbon\Carbon::parse($proposedEnd);

        $conflicts = \App\Models\Appointment::where('beautician_id', $beauticianId)
            ->where('date', $date)
            ->whereIn('status', ['SCHEDULED', 'CONFIRMED', 'IN_PROGRESS'])
            ->where(function ($query) use ($proposedStartCarbon, $proposedEndCarbon) {
                $query->where(function ($q) use ($proposedStartCarbon, $proposedEndCarbon) {
                    // Case 1: Proposed slot starts during existing appointment
                    $q->where('start_time', '<=', $proposedStartCarbon->format('H:i:s'))
                      ->where('end_time', '>', $proposedStartCarbon->format('H:i:s'));
                })->orWhere(function ($q) use ($proposedStartCarbon, $proposedEndCarbon) {
                    // Case 2: Proposed slot ends during existing appointment
                    $q->where('start_time', '<', $proposedEndCarbon->format('H:i:s'))
                      ->where('end_time', '>=', $proposedEndCarbon->format('H:i:s'));
                })->orWhere(function ($q) use ($proposedStartCarbon, $proposedEndCarbon) {
                    // Case 3: Proposed slot completely contains existing appointment
                    $q->where('start_time', '>=', $proposedStartCarbon->format('H:i:s'))
                      ->where('end_time', '<=', $proposedEndCarbon->format('H:i:s'));
                })->orWhere(function ($q) use ($proposedStartCarbon, $proposedEndCarbon) {
                    // Case 4: Existing appointment completely contains proposed slot
                    $q->where('start_time', '<=', $proposedStartCarbon->format('H:i:s'))
                      ->where('end_time', '>=', $proposedEndCarbon->format('H:i:s'));
                });
            })
            ->exists();

        return !$conflicts;
    }

    /**
     * Find the best available beautician for a set of services on a given date
     * Implements the improved pseudocode logic with continuous block detection
     */
    public static function findBestAvailableBeautician($serviceIds, $date, $branchId = null)
    {
        // Calculate total duration needed
        $totalDuration = \App\Models\Service::whereIn('id', $serviceIds)->sum('duration');
        
        if ($totalDuration <= 0) {
            return null;
        }

        // Get beauticians who can perform ALL the requested services
        $beauticianIds = \App\Models\ServiceBeautician::whereIn('service_id', $serviceIds)
            ->select('beautician_id')
            ->groupBy('beautician_id')
            ->havingRaw('COUNT(DISTINCT service_id) = ?', [count($serviceIds)])
            ->pluck('beautician_id');

        if ($beauticianIds->isEmpty()) {
            return null;
        }

        // Filter by branch if specified
        if ($branchId) {
            $beauticianIds = \App\Models\User::whereIn('id', $beauticianIds)
                ->where('branch_id', $branchId)
                ->pluck('id');
        }

        // Implement the improved pseudocode logic
        foreach ($beauticianIds as $beauticianId) {
            // Get beautician schedule and continuous available blocks
            $availableBlocks = self::getContinuousAvailableBlocksForBeautician($beauticianId, $date);
            
            foreach ($availableBlocks as $block) {
                // Check if block duration is sufficient for total service duration
                if ($block['duration_minutes'] >= $totalDuration) {
                    // Check for conflicts with existing bookings
                    $proposedSlot = [
                        'start_time' => $block['start_time'],
                        'end_time' => \Carbon\Carbon::parse($block['start_time'])->addMinutes($totalDuration)->format('H:i:s')
                    ];
                    
                    if (self::hasNoConflictsWithExistingBookings($proposedSlot, $beauticianId, $date)) {
                        $beautician = \App\Models\User::find($beauticianId);
                        
                        return [
                            'beautician_id' => $beauticianId,
                            'beautician_name' => $beautician->name,
                            'start_time' => $proposedSlot['start_time'],
                            'end_time' => $proposedSlot['end_time'],
                            'total_duration' => $totalDuration,
                            'date' => $date,
                            'block_info' => $block
                        ];
                    }
                }
            }
        }

        return null; // No available beautician found
    }

    /**
     * Get all continuous available blocks for a beautician on a given date
     * This consolidates availability across all their schedule windows
     */
    private static function getContinuousAvailableBlocksForBeautician($beauticianId, $date)
    {
        // Get all availabilities for the beautician on the given day of week
        $dayOfWeek = date('l', strtotime($date));
        $availabilities = \App\Models\BeauticianAvailability::where('beautician_id', $beauticianId)
            ->where('day_of_week', $dayOfWeek)
            ->orderBy('start_time')
            ->get();

        if ($availabilities->isEmpty()) {
            return collect();
        }

        $allBlocks = collect();

        // Get continuous blocks for each availability window
        foreach ($availabilities as $availability) {
            $blocks = self::getContinuousAvailableBlocks(
                $beauticianId, 
                $date, 
                $availability->start_time, 
                $availability->end_time
            );
            $allBlocks = $allBlocks->merge($blocks);
        }

        // Sort blocks by start time and filter out very short blocks (less than 15 minutes)
        return $allBlocks->where('duration_minutes', '>=', 15)
                         ->sortBy('start_time')
                         ->values();
    }

    /**
     * Get available beauticians for specific services on a date
     * Returns all beauticians with their available slots
     */
    public static function getAvailableBeauticiansForServices($serviceIds, $date, $branchId = null)
    {
        // Calculate total duration needed
        $totalDuration = \App\Models\Service::whereIn('id', $serviceIds)->sum('duration');
        
        if ($totalDuration <= 0) {
            return collect();
        }

        // Get beauticians who can perform ALL the requested services
        $beauticianIds = \App\Models\ServiceBeautician::whereIn('service_id', $serviceIds)
            ->select('beautician_id')
            ->groupBy('beautician_id')
            ->havingRaw('COUNT(DISTINCT service_id) = ?', [count($serviceIds)])
            ->pluck('beautician_id');

        if ($beauticianIds->isEmpty()) {
            return collect();
        }

        // Filter by branch if specified
        if ($branchId) {
            $beauticianIds = \App\Models\User::whereIn('id', $beauticianIds)
                ->where('branch_id', $branchId)
                ->pluck('id');
        }

        $availableBeauticians = collect();

        // For each beautician, get their available slots
        foreach ($beauticianIds as $beauticianId) {
            $availableSlots = self::getAvailableTimeSlots($beauticianId, $totalDuration, $date);
            
            if ($availableSlots->isNotEmpty()) {
                $beautician = \App\Models\User::find($beauticianId);
                
                $availableBeauticians->push([
                    'beautician_id' => $beauticianId,
                    'beautician_name' => $beautician->name,
                    'beautician_email' => $beautician->email,
                    'total_duration' => $totalDuration,
                    'available_slots' => $availableSlots,
                    'slots_count' => $availableSlots->count()
                ]);
            }
        }

        // Sort by number of available slots (most available first)
        return $availableBeauticians->sortByDesc('slots_count')->values();
    }
}
