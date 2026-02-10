<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Shop;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\AttendanceSession;

class StaffAttendanceController extends Controller
{
    public function show(string $token, Request $request)
{
    $user = auth()->user();
    abort_if(!$user, 403);
    abort_if($user->role !== 'staff', 403);

    $shop = \App\Models\Shop::where('qr_token', $token)->firstOrFail();
    abort_if((int)$user->shop_id !== (int)$shop->id, 403);

    $today = \Carbon\Carbon::today();

    // Anti double-toggle protection (refresh/back)
    $last = (int) session('att_last_toggle_ts', 0);
    $lastToken = (string) session('att_last_token', '');
    $nowTs = time();
    $justToggled = ($lastToken === $token && ($nowTs - $last) <= 15);

    $action = null;
    $message = null;

    try {
        if (!$justToggled) {
            $attendance = \Illuminate\Support\Facades\DB::transaction(function () use ($user, $shop, $today, &$action, &$message) {

                // Lock today's attendance row
                $attendance = \App\Models\Attendance::where('user_id', $user->id)
                    ->whereDate('work_date', $today)
                    ->lockForUpdate()
                    ->first();

                // ✅ Work time is 10 hours/day
                $workHours = 10;

                // Salary snapshots
                $daily = $user->daily_salary;
                if ($daily === null && $user->monthly_salary !== null) {
                    $daily = ((float)$user->monthly_salary) / 30;
                }
                $daily = (float)($daily ?? 0);

                $hourly = $user->hourly_salary;
                if ($hourly === null) {
                    $hourly = $workHours > 0 ? ($daily / $workHours) : 0;
                }
                $hourly = (float)$hourly;

                if (!$attendance) {
                    $attendance = \App\Models\Attendance::create([
                        'shop_id' => $shop->id,
                        'user_id' => $user->id,
                        'work_date' => $today->toDateString(),
                        'worked_minutes' => 0,
                        'daily_salary_snapshot' => round($daily, 2),
                        'hourly_salary_snapshot' => round($hourly, 4),
                        'earned_amount' => 0,
                        'status' => 'partial',
                    ]);
                }

                // Lock last session
                $lastSession = \App\Models\AttendanceSession::where('attendance_id', $attendance->id)
                    ->orderByDesc('id')
                    ->lockForUpdate()
                    ->first();

                if (!$lastSession || $lastSession->check_out_at) {
                    // ✅ Start new session (CHECK-IN)
                    \App\Models\AttendanceSession::create([
                        'attendance_id' => $attendance->id,
                        'check_in_at' => now(),
                        'check_out_at' => null,
                        'worked_minutes' => 0,
                    ]);

                    $action = 'checkin';
                    $message = 'Checked in successfully.';

                } else {
                    // ✅ Close session (CHECK-OUT)
                    $in = \Carbon\Carbon::parse($lastSession->check_in_at);
                    $out = now();
                    $mins = max(0, $in->diffInMinutes($out));

                    $lastSession->check_out_at = $out;
                    $lastSession->worked_minutes = $mins;
                    $lastSession->save();

                    $action = 'checkout';
                    $message = 'Checked out successfully.';
                }

                // ✅ Recalculate total worked minutes
                $totalMins = (int) \App\Models\AttendanceSession::where('attendance_id', $attendance->id)
                    ->sum('worked_minutes');

                $attendance->worked_minutes = $totalMins;

                // earned = total_hours * hourly, capped at daily
                $earned = ($totalMins / 60) * (float)$attendance->hourly_salary_snapshot;
                $earned = min($earned, (float)$attendance->daily_salary_snapshot);
                $attendance->earned_amount = round($earned, 2);

                // status
                $openExists = \App\Models\AttendanceSession::where('attendance_id', $attendance->id)
                    ->whereNull('check_out_at')
                    ->exists();

                if ($openExists) {
                    $attendance->status = 'partial';
                } else {
                    $attendance->status = $totalMins > 0 ? 'present' : 'partial';
                }

                $attendance->save();

                return $attendance;
            });

            // Remember last toggle timestamp
            session([
                'att_last_toggle_ts' => time(),
                'att_last_token' => $token,
            ]);
        }

    } catch (\Throwable $e) {
        $message = 'Attendance failed: ' . $e->getMessage();
    }

    // Load attendance + sessions for today
    $attendance = \App\Models\Attendance::where('user_id', $user->id)
        ->whereDate('work_date', $today)
        ->with(['sessions' => function($q){ $q->orderBy('id'); }])
        ->first();

    return view('staff.attendance', compact('shop', 'attendance', 'action', 'message'));
}

}
