<html>
<head>
<body>
<div id="countdown"></div>
</body>
</head>
<script>
    let end = new Date('10/30/2023 8:15 PM');

    let _second = 1000;
    let _minute = _second * 60;
    let _hour = _minute * 60;
    let _day = _hour * 24;
    let timer;

    function showRemaining() {
        let now = new Date();
        let distance = end - now;
        if (distance < 0) {

            clearInterval(timer);
            document.getElementById('countdown').innerHTML = 'EXPIRED!';

            return;
        }
        let days = Math.floor(distance / _day);
        let hours = Math.floor((distance % _day) / _hour);
        let minutes = Math.floor((distance % _hour) / _minute);
        let seconds = Math.floor((distance % _minute) / _second);

        let width = 2;
        let fill = 0;
        days = pad(days, width, fill);
        hours = pad(hours, width, fill);
        minutes = pad(minutes, width, fill);
        seconds = pad(seconds, width, fill);

        document.getElementById('countdown').innerHTML = days + ':';
        document.getElementById('countdown').innerHTML += hours + ':';
        document.getElementById('countdown').innerHTML += minutes + ':';
        document.getElementById('countdown').innerHTML += seconds;
    }

    //code from http://stackoverflow.com/questions/10073699/pad-a-number-with-leading-zeros-in-javascript
    function pad(n, width, fill) {
        n = n + '';
        return n.length >= width ? n : new Array(width - n.length + 1).join(fill) + n;
    }

    timer = setInterval(showRemaining, 1000);

</script>

</html>