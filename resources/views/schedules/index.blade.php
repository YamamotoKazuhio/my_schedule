<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Calendar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
</head>

<body class="bg-slate-50 p-4 md:p-8">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-extrabold text-slate-800 tracking-tight">My Calendar</h1>
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
            <div class="flex gap-2">
                <a href="{{ route('schedules.index', ['view' => 'list'] + (request('category') ? ['category' => request('category')] : [])) }}" class="bg-gray-500 text-white px-4 py-2 rounded-lg shadow hover:bg-gray-600 transition">リスト表示</a>
                <a href="{{ route('schedules.create') }}?date={{ date('Y-m-d') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg shadow hover:bg-blue-700 transition">+ 新規予定</a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <form action="{{ url()->current() }}" method="GET" class="flex gap-2 bg-white p-2 rounded-lg shadow-sm border">
                @if(request('view')) <input type="hidden" name="view" value="{{ request('view') }}"> @endif
                @if(request('category')) <input type="hidden" name="category" value="{{ request('category') }}"> @endif
                <input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="予定を検索..." class="flex-1 px-3 py-1 focus:outline-none">
                <button type="submit" class="bg-slate-700 text-white px-4 py-1 rounded-md">検索</button>
            </form>

            <div class="flex items-center gap-2 overflow-x-auto pb-2 md:pb-0">
                <a href="{{ request()->fullUrlWithQuery(['category' => null]) }}" class="whitespace-nowrap px-3 py-1.5 rounded-full border text-xs font-bold {{ !request('category') ? 'bg-slate-800 text-white' : 'bg-white text-slate-600' }}">すべて</a>
                <a href="{{ request()->fullUrlWithQuery(['category' => 'work']) }}" class="whitespace-nowrap px-3 py-1.5 rounded-full border border-blue-500 text-xs font-bold {{ request('category') == 'work' ? 'bg-blue-500 text-white' : 'text-blue-500 bg-white' }}">仕事</a>
                <a href="{{ request()->fullUrlWithQuery(['category' => 'private']) }}" class="whitespace-nowrap px-3 py-1.5 rounded-full border border-green-500 text-xs font-bold {{ request('category') == 'private' ? 'bg-green-500 text-white' : 'text-green-500 bg-white' }}">個人</a>
                <a href="{{ request()->fullUrlWithQuery(['category' => 'important']) }}" class="whitespace-nowrap px-3 py-1.5 rounded-full border border-red-500 text-xs font-bold {{ request('category') == 'important' ? 'bg-red-500 text-white' : 'text-red-500 bg-white' }}">重要</a>
            </div>
        </div>

        <div class="flex gap-2">
            @if(Auth::user()->is_admin)
            <a href="{{ route('users.index') }}" class="bg-purple-600 text-white px-4 py-2 rounded-lg shadow hover:bg-purple-700 transition">
                👤 ユーザー管理
            </a>
            @endif
        </div>

        <div class="flex flex-col lg:flex-row gap-6">
            <div class="lg:w-3/4 bg-white p-6 shadow-md rounded-xl">
                <div id="calendar"></div>
            </div>

            <div class="lg:w-1/4 space-y-6">
                <div class="bg-white p-5 shadow-md rounded-xl border-t-4 border-blue-500">
                    <h2 class="font-bold text-slate-800 mb-3">📅 今日の予定</h2>
                    <div class="space-y-2 text-sm">
                        @forelse($todaySchedules as $s)
                        <div class="p-2 bg-blue-50 rounded border-l-2 border-blue-400">{{ $s->title }}</div>
                        @empty
                        <p class="text-slate-400 italic">なし</p>
                        @endforelse
                    </div>
                </div>
                <div class="bg-white p-5 shadow-md rounded-xl border-t-4 border-orange-400">
                    <h2 class="font-bold text-slate-800 mb-3">📝 未完了リスト</h2>
                    <div class="space-y-2 text-sm">
                        @forelse($incompleteSchedules as $s)
                        <div class="border-b pb-2">
                            <span class="text-[10px] text-slate-400 block">{{ $s->date }}</span>
                            <span class="font-medium text-slate-700">{{ $s->title }}</span>
                        </div>
                        @empty
                        <p class="text-slate-400 italic">なし</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
                initialView: 'dayGridMonth',
                locale: 'ja',
                height: 'auto',
                editable: true,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek'
                },
                buttonText: {
                    today: '今日',
                    month: '月',
                    week: '週'
                },
                dateClick: (info) => window.location.href = '{{ route("schedules.create") }}?date=' + info.dateStr,
                eventDrop: function(info) {
                    fetch(`{{ url('schedules') }}/${info.event.id}/update-date`, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            date: info.event.startStr.split("T")[0]
                        })
                    }).then(res => res.ok ? window.location.reload() : info.revert());
                },

                events: JSON.parse('@json($schedules)')
            });
            calendar.render();
        });
    </script>
    <style>
        .fc-day-sun {
            background-color: #fff5f5;
        }

        .fc-day-sat {
            background-color: #f7fafc;
        }

        .fc-day-today {
            background-color: #fffbeb !important;
        }
    </style>
</body>

</html>