$(document).ready(function() {
    $('#formLogin').submit(function(e) {
        e.preventDefault();
        Swal.fire({
            title: '¡Acceso Concedido!',
            text: 'Bienvenido al Sistema de Matrícula',
            icon: 'success',
            confirmButtonText: 'Continuar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "Dashboard.html";
            }
        });
    });
});