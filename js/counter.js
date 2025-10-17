 
document.getElementById('callNext').addEventListener('click', function() {
    fetch('php/call_next.php', {
        method: 'POST'
    });
});

document.getElementById('callSpecific').addEventListener('click', function() {
    let number = document.getElementById('specificNumber').value;
    fetch('php/call_specific.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ number: number })
    });
});
