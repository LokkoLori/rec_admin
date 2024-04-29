<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

class MatchClientController extends Controller
{
    public function index()
    {
        return view('matchclient.index');
    }
}
