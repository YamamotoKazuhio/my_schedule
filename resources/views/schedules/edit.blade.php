<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>予定の編集</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-slate-50 p-10">
    <div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow">
        <h1 class="text-2xl font-bold mb-6">予定を編集</h1>

        <form action="{{ route('schedules.update', $schedule->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="block text-sm font-bold mb-2">日付</label>
                <input type="date" name="date" value="{{ $schedule->date }}" class="w-full border p-2 rounded" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-bold mb-2">カテゴリー</label>
                <select name="category" class="w-full border p-2 rounded">
                    <option value="private" {{ (old('category') ?? $schedule->category ?? '') == 'private' ? 'selected' : '' }}>プライベート</option>
                    <option value="work" {{ (old('category') ?? $schedule->category ?? '') == 'work' ? 'selected' : '' }}>仕事</option>
                    <option value="important" {{ (old('category') ?? $schedule->category ?? '') == 'important' ? 'selected' : '' }}>重要</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-bold mb-2">タイトル</label>
                <input type="text" name="title" value="{{ $schedule->title }}" class="w-full border p-2 rounded" required>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-bold mb-2">内容</label>
                <textarea name="description" class="w-full border p-2 rounded" rows="3">{{ $schedule->description }}</textarea>
            </div>

            <div class="flex justify-between items-center">
                <a href="/schedules" class="text-gray-500 text-sm">キャンセル</a>
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                    更新する
                </button>
            </div>
        </form>
    </div>
</body>

</html>