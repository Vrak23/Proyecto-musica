$(document).ready(function() {
    $('#formLogin').submit(function(e) {
        e.preventDefault();
        
        const username = $('#usuario').val();
        const password = $('#password').val();

        $.ajax({
            url: 'php/login_process.php',
            type: 'POST',
            data: {
                username: username,
                password: password
            },
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire({
                        title: '¡Acceso Concedido!',
                        text: 'Bienvenido, ' + username,
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = "Dashboard.php";
                    });
                } else {
                    Swal.fire({
                        title: 'Error de Acceso',
                        text: response.message,
                        icon: 'error',
                        confirmButtonText: 'Reintentar'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    title: 'Error del Servidor',
                    text: 'No se pudo conectar con el servidor',
                    icon: 'error'
                });
            }
        });
    });
});