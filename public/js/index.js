document.addEventListener('DOMContentLoaded', () => {
    // --- REFERENCIAS A ELEMENTOS DEL DOM ---
    const formLogin = document.getElementById('formLogin');
    const linkRegistro = document.getElementById('link-registro');
    const alertContainer = document.getElementById('alertContainer');

    if (!formLogin || !linkRegistro || !alertContainer) {
        console.error("Faltan elementos esenciales en el HTML: formLogin, link-registro o alertContainer.");
        return;
    }

    // --- FUNCIONES ---

    /** Muestra una alerta de Bootstrap que se cierra automáticamente. */
    const showAlert = (message, type = 'info') => {
        const wrapper = document.createElement('div');
        wrapper.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>`;
        alertContainer.innerHTML = ''; // Limpiar alertas anteriores
        alertContainer.append(wrapper.firstElementChild);

        setTimeout(() => {
            wrapper.querySelector('.alert')?.remove();
        }, 5000);
    };

    /** Verifica si hay un período de registro activo en el servidor. */
    const verificarPeriodoActivo = async () => {
        try {
            // La URL ahora apunta a la ruta web de Laravel
            const response = await fetch('/verificar-periodo-activo', { cache: 'no-store' });
            if (!response.ok) {
                throw new Error(`Error del servidor: ${response.statusText}`);
            }
            const data = await response.json();

            if (data.activo) {
                linkRegistro.classList.remove('link-disabled');
                linkRegistro.href = 'registrar.html'; // Cambia esta ruta a la de Laravel cuando la crees
            } else {
                linkRegistro.classList.add('link-disabled');
                linkRegistro.removeAttribute('href');
            }
        } catch (error) {
            console.error('Error al verificar el período:', error);
            linkRegistro.classList.add('link-disabled');
            linkRegistro.removeAttribute('href');
        }
    };

    // --- INICIALIZACIÓN ---
    verificarPeriodoActivo();
});