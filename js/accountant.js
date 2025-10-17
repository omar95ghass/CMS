 
document.getElementById('callSpecific').addEventListener('click', function() {
    let number = document.getElementById('specificNumber').value;
    fetch('php/accountant_call.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ number: number })
    });
});
