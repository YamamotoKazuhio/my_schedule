<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
// ★ 祝日計算ライブラリを使用
use Yasumi\Yasumi;

class ScheduleController extends Controller
{
    use AuthorizesRequests;

    /**
     * スケジュール一覧（カレンダー表示）
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Schedule::query();

        // 権限チェック：管理者以外は自分の予定のみ
        if (!$user->is_admin) {
            $query->where('user_id', $user->id);
        }

        // キーワード検索
        if ($request->filled('keyword')) {
            $query->where('title', 'like', '%' . $request->keyword . '%');
        }

        // カテゴリ絞り込み
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $allSchedules = $query->get();

        // 1. 通常のスケジュールをFullCalendar形式に変換
        $schedulesForCalendar = $allSchedules->map(function ($item) {
            $colors = [
                'work' => '#3b82f6',      // 青
                'private' => '#10b981',   // 緑
                'important' => '#ef4444'  // 赤
            ];

            return [
                'id' => $item->id,
                'title' => ($item->user->name !== Auth::user()->name ? "[{$item->user->name}] " : "") .
                    ($item->is_completed ? '【済】' : '') . $item->title,
                'start' => $item->date,
                'url' => "/schedules/{$item->id}/edit",
                'color' => $item->is_completed ? '#94a3b8' : ($colors[$item->category] ?? '#3b82f6'),
            ];
        })->toArray();

        // 2. ★ 日本の祝日データを生成して追加
        // カレンダーの表示に合わせて今年と来年の祝日を取得（年を跨ぐ表示に対応）
        $years = [date('Y'), date('Y') + 1];
        $holidayEvents = [];

        foreach ($years as $year) {
            $holidays = Yasumi::create('Japan', (int)$year, 'ja_JP');
            foreach ($holidays as $holiday) {
                $holidayEvents[] = [
                    'title' => '㊗️ ' . $holiday->getName(),
                    'start' => $holiday->format('Y-m-d'),
                    'color' => 'transparent',      // 背景は塗らずに文字だけ表示
                    'textColor' => '#e11d48',      // 祝日らしい赤色
                    'allDay' => true,
                    'classNames' => ['holiday-event'], // CSSで微調整したい場合用
                    'editable' => false,           // 祝日はドラッグ不可
                ];
            }
        }

        // 3. 通常予定と祝日データを合体
        $combinedSchedules = array_merge($schedulesForCalendar, $holidayEvents);

        // 今日の予定と未完了の予定（サイドバー等用）
        $today = date('Y-m-d');
        $todaySchedules = (clone $query)->where('date', $today)->get();
        $incompleteSchedules = (clone $query)->where('is_completed', false)->orderBy('date', 'asc')->take(5)->get();

        // リスト表示モード
        if ($request->query('view') === 'list') {
            return view('schedules.list', ['schedules' => $allSchedules]);
        }

        return view('schedules.index', [
            'schedules' => $combinedSchedules,
            'todaySchedules' => $todaySchedules,
            'incompleteSchedules' => $incompleteSchedules
        ]);
    }

    /**
     * 新規作成画面
     */
    public function create(Request $request)
    {
        $date = $request->query('date', date('Y-m-d'));
        return view('schedules.create', compact('date'));
    }

    /**
     * 保存処理（繰り返し登録機能付き）
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|max:255', 
            'date' => 'required|date'
        ]);

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

    /**
     * 編集画面
     */
    public function edit(Schedule $schedule)
    {
        if (!Auth::user()->is_admin && $schedule->user_id !== Auth::id()) {
            abort(403);
        }
        return view('schedules.edit', compact('schedule'));
    }

    /**
     * 更新処理
     */
    public function update(Request $request, Schedule $schedule)
    {
        if (!Auth::user()->is_admin && $schedule->user_id !== Auth::id()) abort(403);
        $schedule->update($request->all());
        return redirect('/schedules');
    }

    /**
     * 削除処理
     */
    public function destroy(Schedule $schedule)
    {
        if (!Auth::user()->is_admin && $schedule->user_id !== Auth::id()) abort(403);
        $schedule->delete();
        return redirect('/schedules');
    }

    /**
     * 完了・未完了の切り替え
     */
    public function toggle(Schedule $schedule)
    {
        if (!Auth::user()->is_admin && $schedule->user_id !== Auth::id()) abort(403);
        $schedule->update(['is_completed' => !$schedule->is_completed]);
        return back();
    }

    /**
     * ドラッグ＆ドロップ等による日付のみの更新（API用）
     */
    public function updateDate(Request $request, Schedule $schedule)
    {
        if (!Auth::user()->is_admin && $schedule->user_id !== Auth::id()) {
            return response()->json(['success' => false], 403);
        }
        $schedule->update(['date' => $request->date]);
        return response()->json(['success' => true]);
    }
}