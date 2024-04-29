@php
    $act_compo = $act_match->competition;
    $gamer_1 = $act_match->participations->get(0)->gamer;
    $gamer_2 = $act_match->participations->get(1)->gamer;
@endphp

<style>
.pairbutton {
    width: 300px;
    height:150px;
    font-size: 24pt;
    user-select: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
}
</style>

<script>

var target = null;

</script>

<table style="width: 100%">
    <tr>
        <td><button id="p1button" class="pairbutton">{{ $gamer_1->nickname }}</button></td>
        <td style="width: 100%; text-align: center; font-size: 18pt">{{ $act_match->game_station->name }}<br/><font style="color: red">{{ $act_match->status }}</font></td>
        <td><button id="p2button" class="pairbutton">{{ $gamer_2->nickname }}</button></td>
    </tr>
</table>

<script>

const p1button = document.getElementById('p1button');
p1button_state = "idle"

const p2button = document.getElementById('p2button');
p2button_state = "idle"

timer_state = "idle"

function action(){
    location.reload();
}

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
    setTimeout(dblButtonAction, 1000);
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

</script>