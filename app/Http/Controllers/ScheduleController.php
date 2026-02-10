<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ScheduleController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Schedule::query();

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
            $colors = ['work' => '#3b82f6', 'private' => '#10b981', 'important' => '#ef4444'];
            return [
                'id' => $item->id,
                'title' => ($item->user->name !== Auth::user()->name ? "[{$item->user->name}] " : "") .
                    ($item->is_completed ? '【済】' : '') . $item->title,
                'start' => $item->date,
                'url' => "/schedules/{$item->id}/edit",
                'color' => $item->is_completed ? '#94a3b8' : ($colors[$item->category] ?? '#3b82f6'),
            ];
        });

        $today = date('Y-m-d');
        $todaySchedules = (clone $query)->where('date', $today)->get();
        $incompleteSchedules = (clone $query)->where('is_completed', false)->orderBy('date', 'asc')->take(5)->get();

        if ($request->query('view') === 'list') {
            return view('schedules.list', ['schedules' => $allSchedules]);
        }

        return view('schedules.index', [
            'schedules' => $schedulesForCalendar,
            'todaySchedules' => $todaySchedules,
            'incompleteSchedules' => $incompleteSchedules
        ]);
    }

    // ★ ここを追加しました
    public function create(Request $request)
    {
        // カレンダーから日付が渡されている場合はそれを使用、なければ今日の日付
        $date = $request->query('date', date('Y-m-d'));
        return view('schedules.create', compact('date'));
    }

    public function store(Request $request)
    {
        $request->validate(['title' => 'required|max:255', 'date' => 'required|date']);

        $repeatType = $request->input('repeat_type', 'none');
        $repeatCount = (int)$request->input('repeat_count', 1);
        $startDate = Carbon::parse($request->date);

        for ($i = 0; $i < $repeatCount; $i++) {
            $currentDate = $startDate->copy();
            if ($repeatType === 'daily') $currentDate->addDays($i);
            elseif ($repeatType === 'weekly') $currentDate->addWeeks($i);
            elseif ($repeatType === 'monthly') $currentDate->addMonths($i);

            Schedule::create([
                'user_id' => Auth::id(),
                'title' => $request->title,
                'date' => $currentDate->format('Y-m-d'),
                'description' => $request->description,
                'category' => $request->category,
                'is_completed' => false,
            ]);
            if ($repeatType === 'none') break;
        }

        return redirect('/schedules');
    }

    public function edit(Schedule $schedule)
    {
        if (!Auth::user()->is_admin && $schedule->user_id !== Auth::id()) {
            abort(403);
        }
        return view('schedules.edit', compact('schedule'));
    }

    public function update(Request $request, Schedule $schedule)
    {
        if (!Auth::user()->is_admin && $schedule->user_id !== Auth::id()) abort(403);
        $schedule->update($request->all());
        return redirect('/schedules');
    }

    public function destroy(Schedule $schedule)
    {
        if (!Auth::user()->is_admin && $schedule->user_id !== Auth::id()) abort(403);
        $schedule->delete();
        return redirect('/schedules');
    }

    public function toggle(Schedule $schedule)
    {
        if (!Auth::user()->is_admin && $schedule->user_id !== Auth::id()) abort(403);
        $schedule->update(['is_completed' => !$schedule->is_completed]);
        return back();
    }

    public function updateDate(Request $request, Schedule $schedule)
    {
        if (!Auth::user()->is_admin && $schedule->user_id !== Auth::id()) {
            return response()->json(['success' => false], 403);
        }
        $schedule->update(['date' => $request->date]);
        return response()->json(['success' => true]);
    }
}