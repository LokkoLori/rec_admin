<style>
.pairbutton {
    width: 200px;
    height: 100px;
    font-size: 18pt;
    user-select: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
}
.radio-group {
    display: flex;
    justify-content: space-around;
}
</style>

<script>

var target = null;

</script>

<form method="POST" action="{{ route('matchclient.action') }}" id="clientform">
<input type="hidden" name="game_station_id" value="{{ $game_station->id }}"/>
@csrf
<table style="width: 100%"><tr>
@if (is_null($act_match))
    <td style="width: 100%; text-align: center; font-size: 18pt">
    {{ $game_station->name }}<br/><font style="color: red">idle</font>
    </td>
@else
    @php
        $act_compo = $act_match->competition;
        $gamer_1 = $act_match->participations->get(0)->gamer;
        $gamer_2 = $act_match->participations->get(1)->gamer;
    @endphp
    <td><button id="p1button" class="pairbutton">{{ $gamer_1->nickname }}</button></td>
    <td style="width: 100%; text-align: center; font-size: 18pt">
    @if ($act_match->status == "waiting")
        {{ $act_match->game_station->name }}<br/><font style="color: red">{{ $act_match->status }}</font>
    @elseif ($act_match->status == "started")
        {{ $act_match->game_station->name }}<br/>
        <div class="radio-group">
            <label>2<input type="radio" name="match_result" value="2:0">0</label>
            <label>1<input type="radio" name="match_result" value="1:1">1</label>
            <label>0<input type="radio" name="match_result" value="0:2">2</label>
        </label>
    </div>
    @endif
    </td>
    <td><button id="p2button" class="pairbutton">{{ $gamer_2->nickname }}</button></td>
@endif
</tr></table>
</form>

<script>

const clinetform = document.getElementById('clientform');

function action(){
    clinetform.submit();
}

@if (is_null($act_match))

setTimeout(action, 10000);

@else

const p1button = document.getElementById('p1button');
p1button_state = "idle"

const p2button = document.getElementById('p2button');
p2button_state = "idle"

timer_state = "idle"

function dblButtonAction(){
    if (timer_state != "ticking"){
        return;
    }
    action();
}

function double_touch(){
    p1button.style.backgroundColor  = 'green';
    p2button.style.backgroundColor  = 'green';
    timer_state =  "ticking";
    setTimeout(dblButtonAction, 500);
}

function handleP1TouchStart(event) {
    p1button.style.backgroundColor  = 'red';
    p1button_state = "touched"
    if (p2button_state == 'touched') {
        double_touch();
    }
}

function handleP1TouchEnd(event) {
    p1button.style.backgroundColor = 'white';
    p1button_state = "idle";
    timer_state = "idle"
    if (p2button_state == "touched"){
        p2button.style.backgroundColor  = 'red';
    }
}

function handleP2TouchStart(event) {
    p2button.style.backgroundColor  = 'red';
    p2button_state = "touched"
    if (p1button_state == 'touched') {
        double_touch();
    }
}

function handleP2TouchEnd(event) {
    p2button.style.backgroundColor = 'white';
    p2button_state = "idle";
    timer_state = "idle"

    if (p1button_state == "touched"){
        p1button.style.backgroundColor  = 'red';
    }
}


p1button.addEventListener('touchstart', handleP1TouchStart);
p1button.addEventListener('touchend', handleP1TouchEnd);

p2button.addEventListener('touchstart', handleP2TouchStart);
p2button.addEventListener('touchend', handleP2TouchEnd);

@endif

</script>