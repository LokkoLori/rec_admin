<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cookie;

use Illuminate\Http\Request;
use App\Models\Competition;
use App\Models\Entry;
use App\Models\Gamer;

class RegistrationController extends Controller
{
    public function index(Request $request)
    {
        
        $competitions = Competition::whereHas('competitionDay', function ($query) {
            $query->where('status', 'started');
        })->get();

        $entries = Entry::whereHas('competition.competitionDay', function ($query) {
            $query->where('status', 'started');
        })->orderBy('created_at', 'desc')->get();

        $gamers = Gamer::orderBy('nickname')->get();

        $last_gamer_id = $request->cookie('reg_last_gamer_id', 0);

        $entry_status_options = Entry::STATUS_OPTIONS;

        return view('registration.index', compact('entries', 'competitions', 'gamers', 'last_gamer_id', 'entry_status_options'));
    }

    public function storeEntry(Request $request)
    {
        $entry = new Entry();

        $gamer_id = $request->input('gamer_id');

        if ($request->input('gamer_id') == 0){
            $nickname = $request->input('new_gamer_nickname');
            $gamer = Gamer::where('nickname', $nickname)->first();
            if (!$gamer){
                $gamer = new Gamer;
                $gamer->nickname = $nickname;
                $gamer->save();
            }
            $gamer_id = $gamer->id;
        }

        $entry->gamer_id = $gamer_id;
        $entry->competition_id = $request->input('competition_id');
        $entry->status = 'accepted';
        $entry->save();

        $cookie = Cookie::make('reg_last_gamer_id', $gamer_id, 60);

        return redirect()->route('registration.index')->cookie($cookie);
    }

    public function updateEntry(Request $request)
    {
        $entry = Entry::find($request->input("entry_id"));
        $entry->status = $request->input("entry_status");
        $entry->points = $request->input("entry_points");
        $entry->save();

        return redirect()->route('registration.index');
    }
}
