<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalaryReportController extends Controller
{
    private function monthRange(Request $request): array
    {
        // default current month
        $month = $request->get('month') ?: now()->format('Y-m');
        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth()->toDateString();
        $end   = Carbon::createFromFormat('Y-m', $month)->endOfMonth()->toDateString();
        return [$month, $start, $end];
    }

    private function baseQuery(string $start, string $end)
    {
        // Role naming assumption: staff users have role='staff'
        return DB::table('users')
            ->leftJoin('attendances', function ($join) use ($start, $end) {
                $join->on('attendances.user_id', '=', 'users.id')
                    ->whereBetween('attendances.work_date', [$start, $end]);
            })
            ->leftJoin('shops', 'shops.id', '=', 'users.shop_id')
            ->where('users.role', 'staff')
            ->select([
                'users.id as user_id',
                'users.name as staff_name',
                'users.shop_id',
                'shops.name as shop_name',
                'shops.type as shop_type',

                // Salary from user table
                'users.monthly_salary',
                'users.daily_salary',
                'users.hourly_salary',
                'users.work_hours_per_day',
            ])
            ->selectRaw('COUNT(attendances.id) as days_with_record')
            ->selectRaw("SUM(CASE WHEN attendances.status = 'present' THEN 1 ELSE 0 END) as present_days")
            ->selectRaw("SUM(CASE WHEN attendances.status = 'absent' THEN 1 ELSE 0 END) as absent_days")
            ->selectRaw("SUM(CASE WHEN attendances.status = 'partial' THEN 1 ELSE 0 END) as partial_days")
            ->selectRaw('COALESCE(SUM(attendances.worked_minutes),0) as worked_minutes_total')
            ->selectRaw('COALESCE(SUM(attendances.earned_amount),0) as earned_total')

            // Use snapshot if exists in that month (max snapshot is fine)
            ->selectRaw('COALESCE(MAX(attendances.daily_salary_snapshot), users.daily_salary, (users.monthly_salary/30), 0) as daily_rate')
            ->selectRaw('COALESCE(MAX(attendances.hourly_salary_snapshot), users.hourly_salary, 0) as hourly_rate')
            ->groupBy(
                'users.id',
                'users.name',
                'users.shop_id',
                'shops.name',
                'shops.type',
                'users.monthly_salary',
                'users.daily_salary',
                'users.hourly_salary',
                'users.work_hours_per_day'
            )
            ->orderBy('shops.type')
            ->orderBy('shops.name')
            ->orderBy('users.name');
    }

    private function computeRows($rows)
    {
        // Add computed fields per row (expected + deduction)
        return collect($rows)->map(function ($r) {
            $daily = (float)($r->daily_rate ?? 0);
            $monthly = $r->monthly_salary !== null ? (float)$r->monthly_salary : ($daily * 30);

            $earned = (float)($r->earned_total ?? 0);
            $deduction = max(0, $monthly - $earned);

            $mins = (int)($r->worked_minutes_total ?? 0);
            $hours = $mins / 60;

            $r->expected_monthly = round($monthly, 2);
            $r->deduction = round($deduction, 2);
            $r->worked_hours = round($hours, 2);
            return $r;
        });
    }

    public function admin(Request $request)
    {
        abort_if(auth()->user()->role !== 'admin', 403);

        [$month, $start, $end] = $this->monthRange($request);

        $shops = Shop::orderBy('type')->orderBy('name')->get();

        $q = $this->baseQuery($start, $end);

        if ($request->filled('shop_id')) {
            $q->where('users.shop_id', (int)$request->shop_id);
        }

        if ($request->filled('staff')) {
            $s = trim($request->staff);
            $q->where('users.name', 'like', "%{$s}%");
        }

        $rows = $this->computeRows($q->get());

        $totals = [
            'staff_count' => $rows->count(),
            'earned_total' => round($rows->sum('earned_total'), 2),
            'expected_total' => round($rows->sum('expected_monthly'), 2),
            'deduction_total' => round($rows->sum('deduction'), 2),
            'worked_hours_total' => round($rows->sum('worked_hours'), 2),
            'present_days_total' => (int)$rows->sum('present_days'),
            'absent_days_total' => (int)$rows->sum('absent_days'),
        ];

        return view('admin.reports.salaries', compact('rows', 'totals', 'shops', 'month', 'start', 'end'));
    }

    public function main(Request $request)
    {
        abort_if(auth()->user()->role !== 'main_shop', 403);

        [$month, $start, $end] = $this->monthRange($request);

        $shopId = (int)auth()->user()->shop_id;

        $q = $this->baseQuery($start, $end)
            ->where('users.shop_id', $shopId);

        if ($request->filled('staff')) {
            $s = trim($request->staff);
            $q->where('users.name', 'like', "%{$s}%");
        }

        $rows = $this->computeRows($q->get());

        $totals = [
            'staff_count' => $rows->count(),
            'earned_total' => round($rows->sum('earned_total'), 2),
            'expected_total' => round($rows->sum('expected_monthly'), 2),
            'deduction_total' => round($rows->sum('deduction'), 2),
            'worked_hours_total' => round($rows->sum('worked_hours'), 2),
            'present_days_total' => (int)$rows->sum('present_days'),
            'absent_days_total' => (int)$rows->sum('absent_days'),
        ];

        return view('panels.main.reports.salaries', compact('rows', 'totals', 'month', 'start', 'end'));
    }

    public function branch(Request $request)
    {
        abort_if(auth()->user()->role !== 'branch_shop', 403);

        [$month, $start, $end] = $this->monthRange($request);

        $shopId = (int)auth()->user()->shop_id;

        $q = $this->baseQuery($start, $end)
            ->where('users.shop_id', $shopId);

        if ($request->filled('staff')) {
            $s = trim($request->staff);
            $q->where('users.name', 'like', "%{$s}%");
        }

        $rows = $this->computeRows($q->get());

        $totals = [
            'staff_count' => $rows->count(),
            'earned_total' => round($rows->sum('earned_total'), 2),
            'expected_total' => round($rows->sum('expected_monthly'), 2),
            'deduction_total' => round($rows->sum('deduction'), 2),
            'worked_hours_total' => round($rows->sum('worked_hours'), 2),
            'present_days_total' => (int)$rows->sum('present_days'),
            'absent_days_total' => (int)$rows->sum('absent_days'),
        ];

        return view('panels.branch.reports.salaries', compact('rows', 'totals', 'month', 'start', 'end'));
    }
}
