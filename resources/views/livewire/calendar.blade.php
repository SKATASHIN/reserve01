<div>
    <div class="text-center text-sm mb-4 mt-4">
        日付を選択してください。本日から最大30日先まで選択可能です。
    </div>

        <input id="calendar" class="block mt-1 mb-4 mx-auto" 
        type="text" name="calender"
        value="{{ $currentDate }}"
        wire:change="getDate($event.target.value)" />

        <div class="flex border border-green-400 mx-auto">
            <x-calendar-time />
            <x-day />
            <x-day />
            <x-day />
            <x-day />
            <x-day />
            <x-day />
            <x-day />
        </div>

    <div class="flex">
        @for($day = 0; $day < 7; $day++)
            {{ $currentWeek[$day] }}
        @endfor
    </div>
    @foreach ($events as $event )
        {{ $event->start_date }}<br>
    @endforeach
</div>
