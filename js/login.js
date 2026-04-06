$(document).ready(function() {
    $('#btnIngresar').click(function(e) {
        e.preventDefault();
        Swal.fire({
            title: '¡Acceso Concedido!',
            text: 'Bienvenido al Sistema de Matrícula',
            icon: 'success',
            confirmButtonText: 'Continuar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "dashboard.html";
            }
        });
    });
});