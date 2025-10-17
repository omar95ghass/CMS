$(document).ready(function() {
    fetch('php/get_clinics.php')
        .then(response => {
            console.log('Response from get_clinics.php:', response);
            return response.json();
        })
        .then(data => {
            console.log('Data received from get_clinics.php:', data);
            const container = $('.clinics-checkboxes');
            data.forEach(clinic => {
                let checkbox = `<div class="form-check">
                    <label class="form-check-label">
                        <input type="checkbox" class="form-check-input" name="clinics" value="${clinic.name}"> ${clinic.name}
                    </label>
                </div>`;
                container.append(checkbox);
            });
        })
        .catch(error => console.error('Error fetching clinics:', error));

    $('#clinicsForm').on('submit', function(e) {
        e.preventDefault();

        let selectedClinics = [];
        $('input[name="clinics"]:checked').each(function() {
            selectedClinics.push($(this).val());
        });

        console.log('Selected clinics:', selectedClinics);

        $.ajax({
            url: 'php/clinics.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ clinics: selectedClinics }),
            dataType: 'json',
            success: function(data) {
                console.log('Response from clinics.php:', data);
                if (data.status === 'success') {
                    alert('تم الحفظ');
                    window.location.href = 'counter.php';
                } else {
                    alert('حدث خطأ: ' + data.message);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('Error posting to clinics.php:', textStatus, errorThrown);
                console.error('Response from clinics.php:', jqXHR.responseText);
            }
        });
    });
});
