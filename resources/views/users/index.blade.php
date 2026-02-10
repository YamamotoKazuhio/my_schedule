<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - My Calendar</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-slate-50 p-4 md:p-8">
    <div class="max-w-6xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-800 tracking-tight">User Management</h1>
                <p class="text-sm text-slate-500 mt-1">システムを利用中のユーザー権限とアカウントを管理します。</p>
            </div>
            <a href="{{ route('schedules.index') }}" class="bg-slate-500 text-white px-4 py-2 rounded-lg shadow hover:bg-slate-600 transition flex items-center gap-2">
                <span>←</span> カレンダーに戻る
            </a>
        </div>

        @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-200 text-green-700 rounded-lg">
            {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-200 text-red-700 rounded-lg">
            {{ session('error') }}
        </div>
        @endif

        <div class="bg-white shadow-md rounded-xl overflow-hidden border border-slate-200">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-100 border-b border-slate-200">
                    <tr>
                        <th class="p-4 font-bold text-slate-700 text-sm">ID</th>
                        <th class="p-4 font-bold text-slate-700 text-sm">名前</th>
                        <th class="p-4 font-bold text-slate-700 text-sm">メールアドレス</th>
                        <th class="p-4 font-bold text-slate-700 text-sm text-center">権限</th>
                        <th class="p-4 font-bold text-slate-700 text-sm text-center">予定数</th>
                        <th class="p-4 font-bold text-slate-700 text-sm text-center">操作</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($users as $user)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="p-4 text-slate-500 text-sm">{{ $user->id }}</td>
                        <td class="p-4 font-medium text-slate-800">{{ $user->name }}</td>
                        <td class="p-4 text-slate-600 text-sm">{{ $user->email }}</td>
                        <td class="p-4 text-center">
                            @if($user->is_admin)
                            <span class="px-3 py-1 bg-red-100 text-red-600 text-[11px] font-bold rounded-full border border-red-200">管理者</span>
                            @else
                            <span class="px-3 py-1 bg-slate-100 text-slate-600 text-[11px] font-bold rounded-full border border-slate-200">一般</span>
                            @endif
                        </td>
                        <td class="p-4 text-center text-slate-600 font-mono text-sm">
                            {{ $user->schedules_count }}
                        </td>
                        <td class="p-4 text-sm">
                            <div class="flex justify-center items-center gap-2">
                                @if($user->id !== Auth::id())
                                <form action="{{ route('users.updateRole', $user) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="whitespace-nowrap bg-amber-500 text-white px-3 py-1.5 rounded shadow-sm hover:bg-amber-600 transition text-[11px] font-bold">
                                        {{ $user->is_admin ? '一般へ変更' : '管理者へ変更' }}
                                    </button>
                                </form>

                                <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('本当にこのユーザー（{{ $user->name }}）を削除しますか？\nこのユーザーに関連するすべての予定も削除されます。');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="whitespace-nowrap bg-white text-red-600 border border-red-200 px-3 py-1.5 rounded shadow-sm hover:bg-red-50 transition text-[11px] font-bold">
                                        削除
                                    </button>
                                </form>
                                @else
                                <span class="text-slate-400 italic text-[11px]">操作不可(ログイン中)</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-8 text-center text-slate-400 text-xs">
            &copy; {{ date('Y') }} My Calendar Management System
        </div>
    </div>
</body>

</html>