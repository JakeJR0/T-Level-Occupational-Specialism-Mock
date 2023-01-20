const socket = io('http://172.22.18.4:5000/');

document.addEventListener("DOMContentLoaded", function(event) {
    socket.on('connect', function() {
        console.log('Connected!');
    });
});
