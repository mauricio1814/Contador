<?php
// modal-logout.php (en la raíz del proyecto)
?>
<!-- Modal de Confirmación de Cierre de Sesión -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <div class="text-center w-100">
                    <i class="fas fa-sign-out-alt fa-3x text-danger mb-3"></i>
                    <h5 class="modal-title" id="logoutModalLabel">Confirmar Cierre de Sesión</h5>
                </div>
                <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <p class="mb-3">¿Estás seguro de que deseas cerrar sesión?</p>
                
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <button type="button" class="btn btn-secondary btn-lg px-4" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancelar
                </button>
                <a href="login.php" class="btn btn-danger btn-lg px-4">
                    <i class="fas fa-sign-out-alt me-2"></i>Sí, Cerrar Sesión
                </a>
            </div>
        </div>
    </div>
</div>

<style>
#logoutModal .modal-content {
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    border: none;
}

#logoutModal .modal-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 15px 15px 0 0;
    padding: 2rem 1rem 1rem 1rem;
}

#logoutModal .modal-body {
    background: white;
    padding: 1.5rem 2rem;
}

#logoutModal .modal-footer {
    background: #f8f9fa;
    border-radius: 0 0 15px 15px;
    padding: 1.5rem;
}

#logoutModal .btn-warning {
    background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
    border: none;
    color: white;
    font-weight: 600;
    transition: all 0.3s ease;
}

#logoutModal .btn-warning:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(255, 193, 7, 0.4);
}

#logoutModal .btn-secondary {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
    border: none;
    color: white;
    font-weight: 600;
    transition: all 0.3s ease;
}

#logoutModal .btn-secondary:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(108, 117, 125, 0.4);
}
</style>

<script>
// Script 
document.addEventListener('DOMContentLoaded', function() {
    const logoutLinks = document.querySelectorAll('a[href*="Vista/login.php"]');
    
    logoutLinks.forEach(link => {
        // Solo aplicamos el modal a los enlaces que tienen data-bs-toggle="modal"
        if (link.getAttribute('data-bs-toggle') === 'modal') {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const logoutModal = new bootstrap.Modal(document.getElementById('logoutModal'));
                logoutModal.show();
            });
        }
    });
});
</script>