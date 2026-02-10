<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\AttendanceSession;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MarkMissedCheckoutsAbsent extends Command
{
    protected $signature = 'attendance:mark-missed-absent {--date=}';
    protected $description = 'Mark previous day attendances as absent if checkout is missing (open sessions exist).';

    public function handle(): int
    {
        // Default: process yesterday
        $dateOpt = $this->option('date');
        $targetDate = $dateOpt ? Carbon::parse($dateOpt)->toDateString() : Carbon::yesterday()->toDateString();

        $this->info("Processing missed checkouts for date: {$targetDate}");

        $attendances = Attendance::whereDate('work_date', $targetDate)
            ->whereIn('status', ['partial']) // only unfinished days
            ->whereHas('sessions', function ($q) {
                $q->whereNull('check_out_at'); // open session exists
            })
            ->with('sessions')
            ->get();

        $count = 0;

        foreach ($attendances as $att) {
            DB::transaction(function () use ($att, &$count) {

                $attendance = Attendance::where('id', $att->id)
                    ->lockForUpdate()
                    ->first();

                if (!$attendance) return;

                // if already absent/present, skip
                if (in_array($attendance->status, ['absent','present'], true)) return;

                // lock open sessions
                $openSessions = AttendanceSession::where('attendance_id', $attendance->id)
                    ->whereNull('check_out_at')
                    ->lockForUpdate()
                    ->get();

                // Close any open sessions with zero minutes (so they don’t stay open forever)
                foreach ($openSessions as $s) {
                    $s->check_out_at = $s->check_in_at; // close at same time
                    $s->worked_minutes = 0;
                    $s->save();
                }

                // Mark absent and zero out salary
                $attendance->worked_minutes = 0;
                $attendance->earned_amount = 0;
                $attendance->status = 'absent';
                $attendance->save();

                $count++;
            });
        }

        $this->info("Marked absent: {$count}");
        return 0;
    }
}
