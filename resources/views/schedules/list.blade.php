<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>予定リスト</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-slate-50 p-4 md:p-8">
    <div class="max-w-5xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-extrabold text-slate-800">Schedule List</h1>
            <div class="flex items-center gap-4">
                <div class="text-sm text-slate-600">
                    <span class="font-bold text-slate-900">{{ Auth::user()->name }}</span> さん
                    @if(Auth::user()->is_admin)
                    <span class="ml-1 px-2 py-0.5 bg-red-100 text-red-600 text-[10px] font-bold rounded-full border border-red-200">管理者</span>
                    @endif
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-xs text-gray-500 hover:text-red-500 transition underline">
                        ログアウト
                    </button>
                </form>
            </div>
            <a href="{{ route('schedules.index') }}?{{ request('category') ? 'category='.request('category') : '' }}" class="bg-gray-500 text-white px-4 py-2 rounded-lg shadow">カレンダーへ戻る</a>
        </div>

        <form action="{{ url()->current() }}" method="GET" class="flex gap-2 mb-6 bg-white p-3 rounded-lg shadow border">
            <input type="hidden" name="view" value="list">
            <input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="タイトルで検索..." class="flex-1 px-3 focus:outline-none">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md">検索</button>
        </form>

        <div class="bg-white shadow-md rounded-xl overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-slate-100">
                    <tr>
                        <th class="p-4 w-16 text-center">完了</th>
                        <th class="p-4">日付</th>
                        <th class="p-4">タイトル</th>
                        <th class="p-4 text-center">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($schedules as $s)
                    <tr class="border-b {{ $s->is_completed ? 'bg-gray-50' : '' }}">
                        <td class="p-4 text-center">
                            <form action="{{ route('schedules.toggle', $s->id) }}" method="POST">
                                @csrf @method('PATCH')
                                <input type="checkbox" onchange="this.form.submit()" {{ $s->is_completed ? 'checked' : '' }}>
                            </form>
                        </td>
                        <td class="p-4 {{ $s->is_completed ? 'line-through text-gray-400' : '' }}">{{ $s->date }}</td>
                        <td class="p-4 font-medium {{ $s->is_completed ? 'line-through text-gray-400' : '' }}">{{ $s->title }}</td>
                        <td class="p-4 text-center">
                            <a href="{{ route('schedules.edit', $s->id) }}" class="text-blue-500 mr-2">編集</a>
                            <form action="{{ route('schedules.destroy', $s->id) }}" method="POST" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-500" onclick="return confirm('消去しますか？')">削除</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>