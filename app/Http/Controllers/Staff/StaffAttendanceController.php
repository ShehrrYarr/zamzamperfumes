<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Shop;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StaffAttendanceController extends Controller
{
    public function show(string $token, Request $request)
    {
        $user = auth()->user();
        abort_if(!$user, 403);
        abort_if($user->role !== 'staff', 403);

        $shop = Shop::where('qr_token', $token)->firstOrFail();
        abort_if((int)$user->shop_id !== (int)$shop->id, 403);

        $today = Carbon::today();

        // Anti double-toggle (mobile refresh/back)
        $last = (int) session('att_last_toggle_ts', 0);
        $lastToken = (string) session('att_last_token', '');
        $nowTs = time();

        $justToggled = ($lastToken === $token && ($nowTs - $last) <= 15); // 15 sec protection window

        $action = null;
        $message = null;

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', $today)
            ->first();

        if (!$justToggled) {
            $result = DB::transaction(function () use ($user, $shop, $today, &$attendance, &$action, &$message) {

                // Reload inside transaction with lock (safe)
                $attendance = Attendance::where('user_id', $user->id)
                    ->whereDate('work_date', $today)
                    ->lockForUpdate()
                    ->first();

                // Calculate salary snapshot for today (fallback logic)
                $workHours = (int)($user->work_hours_per_day ?? 10);

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
                    // First scan => Check-in
                    $attendance = Attendance::create([
                        'shop_id' => $shop->id,
                        'user_id' => $user->id,
                        'work_date' => $today->toDateString(),
                        'check_in_at' => now(),
                        'check_out_at' => null,
                        'worked_minutes' => 0,
                        'daily_salary_snapshot' => round($daily, 2),
                        'hourly_salary_snapshot' => round($hourly, 4),
                        'earned_amount' => 0,
                        'status' => 'partial',
                    ]);

                    $action = 'checkin';
                    $message = 'Checked in successfully.';
                    return $attendance;
                }

                // If already checked out, do nothing (avoid multiple outs)
                if ($attendance->check_in_at && $attendance->check_out_at) {
                    $action = 'already_done';
                    $message = 'Today attendance already completed.';
                    return $attendance;
                }

                // Second scan => Check-out
                if ($attendance->check_in_at && !$attendance->check_out_at) {
                    $in = Carbon::parse($attendance->check_in_at);
                    $out = now();

                    $mins = max(0, $in->diffInMinutes($out));
                    $attendance->check_out_at = $out;
                    $attendance->worked_minutes = $mins;

                    // Earned = worked_hours * hourly, capped at daily
                    $earned = ($mins / 60) * (float)$attendance->hourly_salary_snapshot;
                    $earned = min($earned, (float)$attendance->daily_salary_snapshot);

                    $attendance->earned_amount = round($earned, 2);
                    $attendance->status = 'present';
                    $attendance->save();

                    $action = 'checkout';
                    $message = 'Checked out successfully.';
                    return $attendance;
                }

                // Fallback
                $action = 'noop';
                $message = 'No action performed.';
                return $attendance;
            });

            // remember last toggle to prevent refresh double action
            session([
                'att_last_toggle_ts' => time(),
                'att_last_token' => $token,
            ]);

            $attendance = $result;
        }

        // Load today attendance after possible toggle
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', $today)
            ->first();

        return view('staff.attendance', compact('shop', 'attendance', 'action', 'message'));
    }
}
