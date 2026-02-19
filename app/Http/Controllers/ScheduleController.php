<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Yasumi\Yasumi;

class ScheduleController extends Controller
{
    use AuthorizesRequests;

    /**
     * アクセス権限のチェック
     */
    private function authorizeSchedule(Schedule $schedule)
    {
        if (!Auth::user()->is_admin && $schedule->user_id !== Auth::id()) {
            abort(403, 'この操作を行う権限がありません。');
        }
    }

    /**
     * スケジュール一覧（カレンダー表示）
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        // N+1問題を解決するために with('user') を追加
        $query = Schedule::with('user');

        if (!$user->is_admin) {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('keyword')) {
            $query->where('title', 'like', '%' . $request->keyword . '%');
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $allSchedules = $query->get();

        $schedulesForCalendar = $allSchedules->map(function ($item) {
            $colors = [
                'work' => '#3b82f6',
                'private' => '#10b981',
                'important' => '#ef4444'
            ];

            return [
                'id' => $item->id,
                'title' => ($item->user_id !== Auth::id() ? "[{$item->user->name}] " : "") .
                    ($item->is_completed ? '【済】' : '') . $item->title,
                'start' => $item->date,
                'url' => route('schedules.edit', $item->id),
                'color' => $item->is_completed ? '#94a3b8' : ($colors[$item->category] ?? '#3b82f6'),
            ];
        })->toArray();

        // 祝日データの生成
        $years = [date('Y'), date('Y') + 1];
        $holidayEvents = [];
        foreach ($years as $year) {
            $holidays = Yasumi::create('Japan', (int)$year, 'ja_JP');
            foreach ($holidays as $holiday) {
                $holidayEvents[] = [
                    'title' => '㊗️ ' . $holiday->getName(),
                    'start' => $holiday->format('Y-m-d'),
                    'color' => 'transparent',
                    'textColor' => '#e11d48',
                    'allDay' => true,
                    'classNames' => ['holiday-event'],
                    'editable' => false,
                ];
            }
        }

        $combinedSchedules = array_merge($schedulesForCalendar, $holidayEvents);

        $today = date('Y-m-d');
        // cloneを使用して元のクエリ条件を引き継ぐ
        $todaySchedules = (clone $query)->where('date', $today)->get();
        $incompleteSchedules = (clone $query)->where('is_completed', false)->orderBy('date', 'asc')->take(5)->get();

        if ($request->query('view') === 'list') {
            return view('schedules.list', ['schedules' => $allSchedules]);
        }

        return view('schedules.index', [
            'schedules' => $combinedSchedules,
            'todaySchedules' => $todaySchedules,
            'incompleteSchedules' => $incompleteSchedules
        ]);
    }

    public function create(Request $request)
    {
        $date = $request->query('date', date('Y-m-d'));
        return view('schedules.create', compact('date'));
    }

    public function store(Request $request)
    {
        // repeat_count に上限(例: 52回)を設けて負荷対策
        $validated = $request->validate([
            'title' => 'required|max:255', 
            'date' => 'required|date',
            'category' => 'nullable|string',
            'description' => 'nullable|string',
            'repeat_type' => 'nullable|in:none,daily,weekly,monthly',
            'repeat_count' => 'nullable|integer|min:1|max:52',
        ]);

        $repeatType = $request->input('repeat_type', 'none');
        $repeatCount = $repeatType === 'none' ? 1 : (int)$request->input('repeat_count', 1);
        $startDate = Carbon::parse($validated['date']);

        for ($i = 0; $i < $repeatCount; $i++) {
            $currentDate = $startDate->copy();
            if ($repeatType === 'daily') $currentDate->addDays($i);
            elseif ($repeatType === 'weekly') $currentDate->addWeeks($i);
            elseif ($repeatType === 'monthly') $currentDate->addMonths($i);

            Schedule::create([
                'user_id' => Auth::id(),
                'title' => $validated['title'],
                'date' => $currentDate->format('Y-m-d'),
                'description' => $validated['description'] ?? null,
                'category' => $validated['category'] ?? 'work',
                'is_completed' => false,
            ]);
        }

       return redirect()->route('schedules.index')->with('success', '予定を登録しました。');
    }

    public function edit(Schedule $schedule)
    {
        $this->authorizeSchedule($schedule);
        return view('schedules.edit', compact('schedule'));
    }

    public function update(Request $request, Schedule $schedule)
    {
        $this->authorizeSchedule($schedule);

        $validated = $request->validate([
            'title' => 'required|max:255',
            'date' => 'required|date',
            'category' => 'nullable|string',
            'description' => 'nullable|string',
            'is_completed' => 'boolean'
        ]);

        $schedule->update($validated);
        return redirect()->route('schedules.index')->with('success', '更新しました。');
    }

    public function destroy(Schedule $schedule)
    {
        $this->authorizeSchedule($schedule);
        $schedule->delete();
        return redirect()->route('schedules.index')->with('success', '削除しました。');
    }

    public function toggle(Schedule $schedule)
    {
        $this->authorizeSchedule($schedule);
        $schedule->update(['is_completed' => !$schedule->is_completed]);
        return back();
    }

    public function updateDate(Request $request, Schedule $schedule)
    {
        // API用なので 403 JSONを返す
        if (!Auth::user()->is_admin && $schedule->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'date' => 'required|date'
        ]);

        $schedule->update(['date' => $validated['date']]);
        return response()->json(['success' => true]);
    }
}