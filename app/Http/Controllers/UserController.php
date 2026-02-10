<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        if (!Gate::allows('admin')) {
            abort(403);
        }

        $users = User::withCount('schedules')->get();
        return view('users.index', compact('users'));
    }

    // 権限の切り替え
    public function updateRole(Request $request, User $user)
    {
        if (!Gate::allows('admin')) abort(403);

        // 自分自身の権限は変更できないようにする（詰み防止）
        if ($user->id === Auth::id()) {
            return back()->with('error', '自分自身の権限は変更できません。');
        }

        $user->is_admin = !$user->is_admin;
        $user->save();

        return back()->with('success', 'ユーザー権限を更新しました。');
    }

    // ユーザーの削除
    public function destroy(User $user)
    {
        if (!Gate::allows('admin')) abort(403);

        if ($user->id === Auth::id()) {
            return back()->with('error', '自分自身を削除することはできません。');
        }

        $user->delete();
        return back()->with('success', 'ユーザーを削除しました。');
    }
}
