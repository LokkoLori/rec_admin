<table style="width: 100%">
    <tr>
        <td><button id="p1button" style="width: 200px; height:100px">P1</button></td>
        <td style="width: 100%"></td>
        <td><button id="p2button" style="width: 200px; height:100px">P2</button></td>
    </tr>
</table>

<script>

const p1button = document.getElementById('p1button');
const p2button = document.getElementById('p2button');

function handleTouchStart(event) {
    event.target.style.backgroundColor  = 'red';
}

function handleTouchEnd(event) {
    event.target.style.backgroundColor = 'white';
}

p1button.addEventListener('touchstart', handleTouchStart);
p1button.addEventListener('touchend', handleTouchEnd);

p2button.addEventListener('touchstart', handleTouchStart);
p2button.addEventListener('touchend', handleTouchEnd);

</script>