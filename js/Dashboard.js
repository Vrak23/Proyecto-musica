document.addEventListener('DOMContentLoaded', () => {
    // --- NAVEGACIÓN ---
    const navItems = document.querySelectorAll('.nav-item[data-target]');
    const sections = document.querySelectorAll('.view-section');

    function switchSection(targetId) {
        // Cerrar modal si estaba abierto
        const modal = document.getElementById('articulo-modal');
        if (modal) modal.style.display = 'none';
        document.body.style.overflow = '';

        navItems.forEach(nav => {
            if (nav.getAttribute('data-target') === targetId) {
                nav.classList.add('active');
            } else {
                nav.classList.remove('active');
            }
        });
        sections.forEach(sec => sec.classList.remove('active-section'));
        const targetSection = document.getElementById(targetId);
        if (targetSection) {
            targetSection.classList.add('active-section');
        }
    }

    navItems.forEach(item => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            const targetId = item.getAttribute('data-target');
            switchSection(targetId);
        });
    });

    // --- NOTIFICACIONES ---
    const bellTrigger = document.getElementById('notification-trigger');
    const badge = bellTrigger.querySelector('.badge');
    const notificationItems = document.querySelectorAll('.notification-item');
    const markAllBtn = document.querySelector('.mark-all');

    // Función para actualizar el contador de notificaciones
    function updateBadge() {
        const unreadCount = document.querySelectorAll('.notification-item.unread').length;
        if (unreadCount > 0) {
            badge.textContent = unreadCount;
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }
    }

    // Inicializar badge
    updateBadge();

    bellTrigger.addEventListener('click', (e) => {
        e.stopPropagation();
        bellTrigger.classList.toggle('show');
    });

    // Marcar individualmente como leído
    notificationItems.forEach(item => {
        item.addEventListener('click', () => {
            item.classList.remove('unread');
            updateBadge();
        });
    });

    // Marcar todo como leído
    if (markAllBtn) {
        markAllBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            notificationItems.forEach(item => item.classList.remove('unread'));
            updateBadge();
        });
    }

    document.addEventListener('click', (e) => {
        if (!bellTrigger.contains(e.target)) {
            bellTrigger.classList.remove('show');
        }
    });

    // --- BUSCADOR UNIVERSAL ---
    const searchInput = document.getElementById('global-search');
    const searchResults = document.getElementById('search-results');

    searchInput.addEventListener('input', (e) => {
        const query = e.target.value.toLowerCase().trim();
        
        if (query.length < 2) {
            searchResults.classList.remove('show');
            return;
        }

        // Recopilar datos para buscar
        const data = [
            // Categoría: Instrumentos
            ...Array.from(document.querySelectorAll('#articulos .track-item')).map(item => ({
                title: item.querySelector('h4').textContent,
                subtitle: item.querySelector('p').textContent,
                type: 'Instrumento',
                icon: 'fa-guitar',
                target: 'articulos'
            })),
            // Categoría: Ventas (Clientes en ventas)
            ...Array.from(document.querySelectorAll('#ventas .recent-sales-table tbody tr')).map(tr => ({
                title: tr.cells[1].textContent, // Nombre cliente
                subtitle: tr.cells[0].textContent.trim(), // Artículo
                type: 'Venta',
                icon: 'fa-cart-shopping',
                target: 'ventas'
            })),
            // Categoría: Clientes
            ...Array.from(document.querySelectorAll('#clientes .recent-sales-table tbody tr')).map(tr => ({
                title: tr.querySelector('.product-info span').textContent, // Nombre cliente
                subtitle: tr.cells[1].textContent, // Email
                type: 'Cliente',
                icon: 'fa-user',
                target: 'clientes'
            })),
            // Categoría: Historial (Proveedores)
            ...Array.from(document.querySelectorAll('#historial .recent-sales-table tbody tr')).map(tr => ({
                title: tr.cells[1].textContent, // Proveedor
                subtitle: tr.querySelector('.product-info span').textContent, // Lote
                type: 'Proveedor/Lote',
                icon: 'fa-truck-field',
                target: 'historial'
            }))
        ];

        const matches = data.filter(item => 
            item.title.toLowerCase().includes(query) || 
            item.subtitle.toLowerCase().includes(query)
        );

        renderSearchResults(matches);
    });

    function renderSearchResults(matches) {
        if (matches.length === 0) {
            searchResults.innerHTML = '<div class="search-category"><span class="search-category-title">Sin resultados</span></div>';
            searchResults.classList.add('show');
            return;
        }

        const categories = {};
        matches.forEach(match => {
            if (!categories[match.type]) categories[match.type] = [];
            categories[match.type].push(match);
        });

        let html = '';
        for (const [type, items] of Object.entries(categories)) {
            html += `
                <div class="search-category">
                    <span class="search-category-title">${type}s</span>
                    ${items.map(item => `
                        <div class="search-result-item" data-target="${item.target}">
                            <i class="fa-solid ${item.icon}"></i>
                            <div class="search-result-info">
                                <h5>${item.title}</h5>
                                <span>${item.subtitle}</span>
                            </div>
                        </div>
                    `).join('')}
                </div>
            `;
        }

        searchResults.innerHTML = html;
        searchResults.classList.add('show');

        // Eventos para los items de búsqueda
        document.querySelectorAll('.search-result-item').forEach(item => {
            item.addEventListener('click', () => {
                const target = item.getAttribute('data-target');
                switchSection(target);
                searchInput.value = '';
                searchResults.classList.remove('show');
            });
        });
    }

    // Cerrar buscador al hacer clic fuera
    document.addEventListener('click', (e) => {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.classList.remove('show');
        }
    });

    // --- AJUSTES FUNCIONALES ---
    
    // 1. Cambio de Tema (Oscuro/Claro)
    const themeBtns = document.querySelectorAll('.theme-btn');
    const currentTheme = localStorage.getItem('theme') || 'dark';

    if (currentTheme === 'light') {
        document.body.classList.add('light-theme');
        themeBtns.forEach(b => b.classList.toggle('active', b.getAttribute('data-theme') === 'light'));
    }

    themeBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const theme = btn.getAttribute('data-theme');
            themeBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            if (theme === 'light') {
                document.body.classList.add('light-theme');
            } else {
                document.body.classList.remove('light-theme');
            }
            localStorage.setItem('theme', theme);
        });
    });

    // 2. Color de Acento
    const colorSwatches = document.querySelectorAll('.color-swatch');
    const savedColor = localStorage.getItem('accent-color') || '#ea9216';
    
    document.documentElement.style.setProperty('--color-accent', savedColor);
    colorSwatches.forEach(s => s.classList.toggle('active', s.getAttribute('data-color') === savedColor));

    colorSwatches.forEach(swatch => {
        swatch.addEventListener('click', () => {
            const color = swatch.getAttribute('data-color');
            document.documentElement.style.setProperty('--color-accent', color);
            colorSwatches.forEach(s => s.classList.remove('active'));
            swatch.classList.add('active');
            localStorage.setItem('accent-color', color);
        });
    });

    // 3. Perfil de Usuario (Nombre, Rol y Foto)
    const saveProfileBtn = document.getElementById('save-profile');
    const headerUserName = document.querySelector('.user-name');
    const headerUserRole = document.querySelector('.user-role');
    const headerAvatarImg = document.querySelector('.user-profile .avatar img');
    const profNameInput = document.getElementById('prof-name');
    const profRoleInput = document.getElementById('prof-role');
    const avatarOpts = document.querySelectorAll('.avatar-opt');
    const currentAvatarPreview = document.getElementById('current-avatar-preview');

    // Sincronizar localStorage con el usuario de la sesión actual
    const currentSessionUser = headerUserName.textContent.trim();
    const lastLoggedUser = localStorage.getItem('last-logged-user');

    if (lastLoggedUser !== currentSessionUser) {
        // Es un usuario nuevo o se cambió de sesión, limpiamos preferencias de perfil antiguas
        localStorage.removeItem('user-name');
        localStorage.removeItem('user-avatar');
        localStorage.setItem('last-logged-user', currentSessionUser);
    }

    // Cargar datos guardados (solo si corresponden al usuario actual)
    if (localStorage.getItem('user-name')) {
        headerUserName.textContent = localStorage.getItem('user-name');
        profNameInput.value = localStorage.getItem('user-name');
    }
    if (localStorage.getItem('user-avatar')) {
        const savedAv = localStorage.getItem('user-avatar');
        headerAvatarImg.src = savedAv;
        currentAvatarPreview.src = savedAv;
        avatarOpts.forEach(opt => opt.classList.toggle('active', opt.getAttribute('data-avatar') === savedAv));
    }

    // Selección de Avatar
    avatarOpts.forEach(opt => {
        opt.addEventListener('click', () => {
            avatarOpts.forEach(o => o.classList.remove('active'));
            opt.classList.add('active');
            const avatarSrc = opt.getAttribute('data-avatar');
            currentAvatarPreview.src = avatarSrc;
        });
    });

    // 4. Notificaciones y Sistema
    const notifDesktop = document.getElementById('notif-desktop');
    const notifSound = document.getElementById('notif-sound');
    const notifEmail = document.getElementById('notif-email');
    const sysLang = document.getElementById('sys-lang');
    const sysCurrency = document.getElementById('sys-currency');

    // Cargar preferencias
    notifDesktop.checked = localStorage.getItem('notif-desktop') !== 'false';
    notifSound.checked = localStorage.getItem('notif-sound') !== 'false';
    notifEmail.checked = localStorage.getItem('notif-email') === 'true';
    if (localStorage.getItem('sys-lang')) sysLang.value = localStorage.getItem('sys-lang');
    if (localStorage.getItem('sys-currency')) sysCurrency.value = localStorage.getItem('sys-currency');

    // Guardado Global
    saveProfileBtn.addEventListener('click', () => {
        const name = profNameInput.value.trim();
        const role = profRoleInput.value.trim();
        const activeAvatar = document.querySelector('.avatar-opt.active');

        // Persistir Nombre y Rol
        if (name) {
            headerUserName.textContent = name;
            localStorage.setItem('user-name', name);
        }
        if (role) {
            headerUserRole.textContent = role;
            localStorage.setItem('user-role', role);
        }

        // Persistir Avatar
        if (activeAvatar) {
            const avatarSrc = activeAvatar.getAttribute('data-avatar');
            headerAvatarImg.src = avatarSrc;
            localStorage.setItem('user-avatar', avatarSrc);
        }

        // Persistir Notificaciones y Sistema
        localStorage.setItem('notif-desktop', notifDesktop.checked);
        localStorage.setItem('notif-sound', notifSound.checked);
        localStorage.setItem('notif-email', notifEmail.checked);
        localStorage.setItem('sys-lang', sysLang.value);
        localStorage.setItem('sys-currency', sysCurrency.value);

        // Feedback visual
        const originalText = saveProfileBtn.textContent;
        saveProfileBtn.textContent = '¡Todo Guardado!';
        saveProfileBtn.classList.add('success'); 
        // Nota: asumiendo que btn-save tiene transición para el color
        saveProfileBtn.style.backgroundColor = 'var(--color-success)';
        
        setTimeout(() => {
            saveProfileBtn.textContent = originalText;
            saveProfileBtn.style.backgroundColor = '';
            saveProfileBtn.classList.remove('success');
        }, 2000);
    });

    // Botón Restablecer
    const resetBtn = document.getElementById('reset-settings');
    if (resetBtn) {
        resetBtn.addEventListener('click', () => {
            if (confirm('¿Estás seguro de restablecer todos los ajustes? Se perderán las preferencias guardadas.')) {
                localStorage.clear();
                window.location.reload();
            }
        });
    }

    // --- MEJORAS GLOBALES (Keyboard & Logout) ---
    
    // Cierre con Esc
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            const bell = document.getElementById('notification-trigger');
            const search = document.getElementById('global-search');
            const results = document.getElementById('search-results');
            if (bell) bell.classList.remove('show');
            if (results) results.classList.remove('show');
            if (search) search.value = '';
        }
    });

    // Botón Salir
    const logoutBtn = document.querySelector('.bottom-nav .nav-item:last-child');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', (e) => {
            e.preventDefault();
            if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
                window.location.href = 'php/logout.php';
            }
        });
    }
});