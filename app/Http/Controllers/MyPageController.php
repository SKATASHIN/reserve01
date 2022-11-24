<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Event;
use App\Models\Reservation;
use App\Services\MyPageService;
use Carbon\Carbon;

class MyPageController extends Controller
{
    public function index()
    {
        $user = User::findOrFail(Auth::id());
        $events = $user->events;   //紐づくイベント取得
        $fromTodayEvents = MyPageService::reservedEvent($events, 'foromToday');
        $pastEvents = MyPageService::reservedEvent($events, 'past');

        // dd($user, $events, $fromTodayEvents, $pastEvents);

        return view('mypage/index', 
        compact('fromTodayEvents', 'pastEvents'));
    }

    public function show($id)
    {
        $event = Event::findOrFail($id);
        $reservation = Reservation::where('user_id', '=', Auth::id())
        ->where('event_id', '=', $id)
        ->latest()   //一番最新のcreated_at
        ->first();

        return view('mypage/show', 
        compact('reservation', 'event'));
    }

    public function cancel($id)
    {
        $reservation = Reservation::where('user_id', '=', Auth::id())
        ->where('event_id', '=', $id)
        ->latest()   //一番最新のcreated_at
        ->first();

        $reservation->canceled_date = Carbon::now()->format('Y-m-d H:i:s');
        $reservation->save();

        session()->flash('status', 'キャンセルが完了しました。');

        return to_route('dashboard');

    }
}
