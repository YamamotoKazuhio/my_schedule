<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>予定登録</title>
    <base href="/">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-slate-50 p-10">
    <div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow">
        <h1 class="text-2xl font-bold mb-6">新しい予定を登録</h1>

        <form action="{{ route('schedules.store') }}" method="POST">
            @csrf 
            <div class="mb-4">
                <label class="block text-sm font-bold mb-2">日付</label>
                <input type="date" name="date" value="{{ request('date') }}" class="w-full border p-2 rounded" required>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-bold mb-2">カテゴリー</label>
                <select name="category" class="w-full border p-2 rounded">
                    <option value="private" {{ (old('category') ?? optional($schedule ?? null)->category) == 'private' ? 'selected' : '' }}>プライベート</option>
                    <option value="work" {{ (old('category') ?? optional($schedule ?? null)->category) == 'work' ? 'selected' : '' }}>仕事</option>
                    <option value="important" {{ (old('category') ?? optional($schedule ?? null)->category) == 'important' ? 'selected' : '' }}>重要</option>
                </select>
            </div>

            <div class="mb-4 grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold mb-2">繰り返し</label>
                    <select name="repeat_type" class="w-full border p-2 rounded">
                        <option value="none">なし</option>
                        <option value="daily">毎日</option>
                        <option value="weekly">毎週</option>
                        <option value="monthly">毎月</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold mb-2">繰り返す回数</label>
                    <input type="number" name="repeat_count" value="1" min="1" max="52" class="w-full border p-2 rounded">
                    <p class="text-xs text-slate-400 mt-1">※最大52回まで</p>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-bold mb-2">タイトル</label>
                <input type="text" name="title" class="w-full border p-2 rounded" placeholder="例：会議" required>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-bold mb-2">内容</label>
                <textarea name="description" class="w-full border p-2 rounded" rows="3"></textarea>
            </div>

            <div class="flex justify-between items-center">
                <a href="/schedule" class="text-gray-500 text-sm hover:underline">戻る</a>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    保存する
                </button>
            </div>
        </form>
    </div>
</body>

</html>