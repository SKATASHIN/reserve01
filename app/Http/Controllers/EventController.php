<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\EventService;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $today = Carbon::today();

        //人数を合計したクエリ
        $reservedPeople = DB::table('reservations')
        ->select('event_id', DB::raw('sum(number_of_people) as number_of_people'))
        ->whereNull('canceled_date')
        ->groupBy('event_id');

        //外部結合で結合、サブクエリ
        $events = DB::table('events')
        ->leftJoinSub($reservedPeople, 'reservedPeople', function($join){
            $join->on('events.id', '=', 'reservedPeople.event_id');
        })
        ->whereDate('start_date', '>=', $today)
        ->orderBy('start_date', 'asc')
        ->paginate(10);

        return view('manager.events.index',
        compact('events'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('manager.events.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreEventRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreEventRequest $request)
    {
        //重複チェック
        $check = EventService::checkEventDuplication(
            $request['event_date'], $request['start_time'], $request['end_time']);

        // dd($check);
        if($check){  //重複なら　true
            session()->flash('status', 'この時間帯の予約が既に存在します。');
            return view('manager.events.create');
        }

        //日付・時間結合
        $startDate = EventService::joinDateAndTime($request['event_date'], $request['start_time']);
        $endDate = EventService::joinDateAndTime($request['event_date'], $request['end_time']);

        Event::create([
            'name' => $request['event_name'],
            'information' => $request['information'],
            'start_date' => $startDate,
            'end_date' => $endDate,
            'max_people' => $request['max_people'],
            'is_visible' => $request['is_visible'],
        ]);

        session()->flash('status', '登録完了しました。');

        return to_route('events.index');
    }


    public function show(Event $event)
    {
        $event = Event::findOrFail($event->id);
        $users = $event->users;

        $reservations = [];  //連想配列を作成しておく キャンセル関係

        foreach($users as $user)
        {
            $reservedInfo = [
                'name' => $user->name,
                'number_of_people' => $user->pivot->number_of_people,
                'canceled_date' => $user->pivot->canceled_date,
            ];
            array_push($reservations, $reservedInfo);
        }

        $eventDate = $event->eventDate;
        $startTime = $event->startTime;
        $endTime = $event->endTime;

        return view('manager.events.show', compact('event', 'users', 'reservations', 'eventDate', 'startTime', 'endTime'));
    }


    public function edit(Event $event)
    {
        $event = Event::findOrFail($event->id);
        $eventDate = $event->editEventDate;
        $startTime = $event->startTime;
        $endTime = $event->endTime;

        return view('manager.events.edit', compact('event', 'eventDate', 'startTime', 'endTime'));
    }


    public function update(UpdateEventRequest $request, Event $event)
    {
        //重複チェック
        $check = EventService::countEventDuplication(
            $request['event_date'], $request['start_time'], $request['end_time']);

        // dd($check);
        //重複１件だけtrue  updateなので1回の重複はOK
        if($check > 1){  
            $event = Event::findOrFail($event->id);
            $eventDate = $event->editEventDate;
            $startTime = $event->startTime;
            $endTime = $event->endTime;
            session()->flash('status', 'この時間帯の予約が既に存在します。');
            return view('manager.events.edit',
            compact('event', 'eventDate', 'startTime', 'endTime'));
        }

        //日付・時間結合
        $startDate = EventService::joinDateAndTime($request['event_date'], $request['start_time']);
        $endDate = EventService::joinDateAndTime($request['event_date'], $request['end_time']);

        $event = Event::findOrFail($event->id);
        $event->name = $request['event_name'];
        $event->information = $request['information'];
        $event->start_date = $startDate;
        $event->end_date = $endDate;
        $event->max_people = $request['max_people'];
        $event->is_visible = $request['is_visible'];
        $event->save();

        session()->flash('status', '更新しました。');

        return to_route('events.index');
    }

    public function past()
    {
        $today = Carbon::today();

        //人数を合計したクエリ
        $reservedPeople = DB::table('reservations')
        ->select('event_id', DB::raw('sum(number_of_people) as number_of_people'))
        ->whereNull('canceled_date')
        ->groupBy('event_id');

        //過去イベント・外部結合、サブクエリ
        $events = DB::table('events')
        ->leftJoinSub($reservedPeople, 'reservedPeople', function($join){
            $join->on('events.id', '=', 'reservedPeople.event_id');
        })
        ->whereDate('start_date', '<', $today)
        ->orderBy('start_date', 'desc')
        ->paginate(10);

        return view('manager.events.past', compact('events'));
    }

    public function destroy(Event $event)
    {
        //
    }
}
