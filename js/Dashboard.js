document.addEventListener('DOMContentLoaded', () => {
    // --- 1. NAVEGACIÓN ENTRE SECCIONES ---
    const navItems = document.querySelectorAll('.nav-item[data-target]');
    const sections = document.querySelectorAll('.view-section');

    function switchSection(targetId) {
        const modal = document.getElementById('articulo-modal');
        if (modal) modal.style.display = 'none';
        document.body.style.overflow = '';

        navItems.forEach(nav => {
            nav.classList.toggle('active', nav.getAttribute('data-target') === targetId);
        });
        sections.forEach(sec => sec.classList.toggle('active-section', sec.id === targetId));
    }

    navItems.forEach(item => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            switchSection(item.getAttribute('data-target'));
        });
    });

    // --- 2. GESTIÓN DE NOTIFICACIONES ---
    const bellTrigger = document.getElementById('notification-trigger');
    const badge = bellTrigger.querySelector('.badge');
    const notificationItems = document.querySelectorAll('.notification-item');

    function updateBadge() {
        const unreadCount = document.querySelectorAll('.notification-item.unread').length;
        badge.textContent = unreadCount;
        badge.style.display = unreadCount > 0 ? 'flex' : 'none';
    }

    updateBadge();
    bellTrigger.addEventListener('click', (e) => {
        e.stopPropagation();
        bellTrigger.classList.toggle('show');
    });

    // --- 3. BUSCADOR UNIVERSAL ---
    const searchInput = document.getElementById('global-search');
    const searchResults = document.getElementById('search-results');

    searchInput.addEventListener('input', (e) => {
        const query = e.target.value.toLowerCase().trim();
        if (query.length < 2) {
            searchResults.classList.remove('show');
            return;
        }

        const data = [
            ...Array.from(document.querySelectorAll('#articulos .track-item')).map(item => ({
                title: item.querySelector('h4').textContent,
                subtitle: item.querySelector('p').textContent,
                type: 'Instrumento', icon: 'fa-guitar', target: 'articulos'
            })),
            ...Array.from(document.querySelectorAll('#ventas .recent-sales-table tbody tr')).map(tr => ({
                title: tr.cells[1]?.textContent || '',
                subtitle: tr.cells[0]?.textContent.trim() || '',
                type: 'Venta', icon: 'fa-cart-shopping', target: 'ventas'
            }))
        ];

        const matches = data.filter(item => 
            item.title.toLowerCase().includes(query) || item.subtitle.toLowerCase().includes(query)
        );
        renderSearchResults(matches);
    });

    function renderSearchResults(matches) {
        if (matches.length === 0) {
            searchResults.innerHTML = '<div class="search-category"><span class="search-category-title">Sin resultados</span></div>';
        } else {
            let html = '';
            const categories = {};
            matches.forEach(m => {
                if (!categories[m.type]) categories[m.type] = [];
                categories[m.type].push(m);
            });
            for (const [type, items] of Object.entries(categories)) {
                html += `<div class="search-category"><span class="search-category-title">${type}s</span>`;
                html += items.map(i => `
                    <div class="search-result-item" data-target="${i.target}">
                        <i class="fa-solid ${i.icon}"></i>
                        <div class="search-result-info"><h5>${i.title}</h5><span>${i.subtitle}</span></div>
                    </div>`).join('');
                html += `</div>`;
            }
            searchResults.innerHTML = html;
        }
        searchResults.classList.add('show');
        document.querySelectorAll('.search-result-item').forEach(item => {
            item.addEventListener('click', () => {
                switchSection(item.getAttribute('data-target'));
                searchInput.value = '';
                searchResults.classList.remove('show');
            });
        });
    }

    // --- 4. TEMAS ---
    const currentTheme = localStorage.getItem('theme') || 'dark';
    if (currentTheme === 'light') document.body.classList.add('light-theme');
    document.querySelectorAll('.theme-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const theme = btn.getAttribute('data-theme');
            document.body.classList.toggle('light-theme', theme === 'light');
            localStorage.setItem('theme', theme);
        });
    });

    // --- 5. PERSISTENCIA: VENTAS ---
    async function cargarVentasDB() {
        const tablaVentas = document.querySelector('#ventas .recent-sales-table tbody');
        if (!tablaVentas) return;
        try {
            const response = await fetch('php/api_reservas.php');
            const ventas = await response.json();
            tablaVentas.innerHTML = ventas.length === 0 ? '<tr><td colspan="5">No hay ventas</td></tr>' : '';
            ventas.forEach(v => {
                const tr = `<tr>
                    <td>${v.INSTRUMENTO}</td>
                    <td>${v.CLIENTE}</td>
                    <td>${v.FECHA_RESERVA}</td>
                    <td>$${parseFloat(v.ADELANTO).toFixed(2)}</td>
                    <td><span class="status ${v.ESTADO.toLowerCase()}">${v.ESTADO}</span></td>
                </tr>`;
                tablaVentas.innerHTML += tr;
            });
        } catch (e) { console.error(e); }
    }

    // --- 6. PERSISTENCIA: CLIENTES (LO QUE FALTA) ---
    async function cargarClientesDB() {
        const tablaClientes = document.querySelector('#clientes .recent-sales-table tbody');
        if (!tablaClientes) return;

        try {
            // Reutilizamos api_reservas o creamos api_clientes.php
            const response = await fetch('php/api_reservas.php'); 
            const datos = await response.json();

            tablaClientes.innerHTML = '';
            
            // Filtrar clientes únicos de las reservas
            const clientesUnicos = [];
            const mapa = new Map();

            for (const item of datos) {
                if (!mapa.has(item.CLIENTE)) {
                    mapa.set(item.CLIENTE, true);
                    clientesUnicos.push(item);
                }
            }

            clientesUnicos.forEach(c => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>
                        <div class="product-info">
                            <div class="product-img" style="background:var(--color-accent); display:flex; align-items:center; justify-content:center; color:white; border-radius:4px;">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <div class="product-details">
                                <span>${c.CLIENTE}</span>
                                <small>ID Cliente: #${c.ID_CLIENTE}</small>
                            </div>
                        </div>
                    </td>
                    <td>${c.FECHA_RESERVA}</td>
                    <td>${c.INSTRUMENTO}</td>
                    <td><span class="status completed">Activo</span></td>
                `;
                tablaClientes.appendChild(tr);
            });
        } catch (e) { console.error("Error clientes:", e); }
    }

    // CARGA INICIAL
    cargarVentasDB();
    cargarClientesDB();

    // Botón Salir
    const logoutBtn = document.querySelector('.bottom-nav .nav-item:last-child');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', (e) => {
            e.preventDefault();
            if (confirm('¿Cerrar sesión?')) window.location.href = 'php/logout.php';
        });
    }
});