<?php
require_once 'php/session.php';
checkSession();

$username = $_SESSION['username'] ?? 'Admin';
$avatar = "img/" . $username . ".jpg";

// Ajuste para el nombre de archivo de Andre
if ($username === "Andre") {
    $avatar = "img/Andree.jpg";
}

// Si no existe la imagen específica, usamos Angel.jpg como respaldo
if (!file_exists($avatar)) {
    $avatar = "img/Angel.jpg";
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Tienda de Música</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/Dashboard.css">
    <style>
        .view-section {
            display: none;
            animation: fadeIn 0.4s ease-out;
        }
        .view-section.active-section {
            display: block;
        }

        /* ── Catálogo de artículos ── */
        .articulos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
            gap: 1.2rem;
            padding: 1rem 0 0.5rem;
        }
        .articulo-card {
            background: var(--bg-secondary, #1a1a2e);
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 14px;
            overflow: hidden;
            cursor: pointer;
            transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
        }
        .articulo-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 32px rgba(0,0,0,0.35);
            border-color: var(--accent-color, #ea9216);
        }
        .art-img-wrap {
            position: relative;
            height: 145px;
            background-size: cover;
            background-position: center;
        }
        .art-badge {
            position: absolute;
            top: 10px; right: 10px;
            font-size: 0.7rem;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 20px;
            letter-spacing: 0.03em;
        }
        .stock-ok  { background: rgba(46,204,113,0.2); color: #2ecc71; }
        .stock-low { background: rgba(241,196,15,0.2);  color: #f1c40f; }
        .stock-out { background: rgba(231,76,60,0.2);   color: #e74c3c; }
        .art-body {
            padding: 0.9rem 1rem;
        }
        .art-cat {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--accent-color, #ea9216);
            font-weight: 600;
        }
        .art-body h4 {
            margin: 0.3rem 0 0.6rem;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-primary, #fff);
            line-height: 1.3;
        }
        .art-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .art-price {
            font-size: 1rem;
            font-weight: 700;
            color: var(--accent-color, #ea9216);
        }
        .art-stock {
            font-size: 0.75rem;
            color: var(--text-secondary, #aaa);
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .art-stock-low { color: #f1c40f !important; }
        .art-stock-out { color: #e74c3c !important; }

        /* ── Modal ── */
        .articulo-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.65);
            backdrop-filter: blur(6px);
            z-index: 9000;
            display: none;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.2s ease;
        }
        .articulo-modal-box {
            background: var(--bg-secondary, #1a1a2e);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 20px;
            width: min(880px, 95vw);
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            animation: slideUp 0.28s cubic-bezier(.22,1,.36,1);
        }
        @keyframes slideUp {
            from { transform: translateY(30px); opacity: 0; }
            to   { transform: translateY(0);    opacity: 1; }
        }
        .modal-close-btn {
            position: absolute;
            top: 1rem; right: 1rem;
            background: rgba(255,255,255,0.08);
            border: none;
            color: var(--text-primary, #fff);
            width: 36px; height: 36px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 1rem;
            display: flex; align-items: center; justify-content: center;
            transition: background 0.2s;
            z-index: 1;
        }
        .modal-close-btn:hover { background: rgba(255,255,255,0.18); }
        .modal-layout {
            display: grid;
            grid-template-columns: 280px 1fr;
            min-height: 340px;
        }
        .modal-img-col { border-radius: 20px 0 0 20px; overflow: hidden; }
        .modal-img-wrap {
            height: 100%;
            min-height: 320px;
            background-size: cover;
            background-position: center;
            position: relative;
        }
        .modal-badge {
            position: absolute;
            top: 14px; left: 14px;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 5px 14px;
            border-radius: 20px;
        }
        .modal-info-col {
            padding: 2rem 2rem 1.8rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .modal-cat {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.09em;
            color: var(--accent-color, #ea9216);
            font-weight: 700;
        }
        .modal-info-col h2 {
            font-size: 1.45rem;
            font-weight: 700;
            color: var(--text-primary, #fff);
            margin: 0.1rem 0;
            line-height: 1.2;
        }
        .modal-sku {
            font-size: 0.75rem;
            color: var(--text-secondary, #aaa);
            margin: 0;
        }
        .modal-desc {
            font-size: 0.85rem;
            color: var(--text-secondary, #aaa);
            line-height: 1.6;
            margin: 0.4rem 0 0.6rem;
        }
        .modal-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.8rem;
            background: rgba(255,255,255,0.04);
            border-radius: 12px;
            padding: 1rem;
            margin-top: auto;
        }
        .modal-stat {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }
        .stat-label {
            font-size: 0.68rem;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: var(--text-secondary, #aaa);
        }
        .stat-val {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text-primary, #fff);
        }
        .modal-actions {
            display: flex;
            gap: 0.8rem;
            margin-top: 1.2rem;
        }
        .modal-actions .btn-save,
        .modal-actions .btn-reset {
            flex: 1;
            padding: 0.7rem;
            font-size: 0.85rem;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            display: flex; align-items: center; justify-content: center; gap: 6px;
        }
        @media (max-width: 640px) {
            .modal-layout { grid-template-columns: 1fr; }
            .modal-img-col { height: 200px; border-radius: 20px 20px 0 0; }
            .modal-stats { grid-template-columns: 1fr 1fr; }
        }

        /* ── Botón Comprar ── */
        .btn-comprar {
            flex: 1;
            width: 100%;
            padding: 0.75rem 1.5rem;
            font-size: 0.95rem;
            font-weight: 700;
            border-radius: 10px;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            background-color: var(--color-accent, #ea9216);
            color: #1a1a2e;
            border: none;
            transition: all 0.25s ease;
        }
        .btn-comprar:hover {
            background-color: #ffad3b;
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(234,146,22,0.4);
        }

        /* ── Panel flotante de compra ── */
        .compra-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.75);
            backdrop-filter: blur(8px);
            z-index: 10000;
            display: none;
            align-items: center;
            justify-content: center;
        }
        .compra-box {
            background: var(--bg-secondary, #1a1a2e);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 20px;
            width: min(560px, 95vw);
            max-height: 92vh;
            overflow-y: auto;
            padding: 2rem 2rem 1.5rem;
            position: relative;
            animation: slideUp 0.28s cubic-bezier(.22,1,.36,1);
        }
        .compra-box h3 {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary, #fff);
            margin-bottom: 0.3rem;
        }
        .compra-box .compra-subtitle {
            font-size: 0.82rem;
            color: var(--text-secondary, #aaa);
            margin-bottom: 1.4rem;
        }
        .compra-close {
            position: absolute;
            top: 1rem; right: 1rem;
            background: rgba(255,255,255,0.08);
            border: none;
            color: #fff;
            width: 34px; height: 34px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 0.95rem;
            display: flex; align-items: center; justify-content: center;
            transition: background 0.2s;
        }
        .compra-close:hover { background: rgba(255,255,255,0.18); }
        .compra-resumen {
            display: flex;
            align-items: center;
            gap: 1rem;
            background: rgba(234,146,22,0.08);
            border: 1px solid rgba(234,146,22,0.2);
            border-radius: 12px;
            padding: 0.9rem 1.1rem;
            margin-bottom: 1.4rem;
        }
        .compra-resumen i { color: var(--color-accent, #ea9216); font-size: 1.4rem; }
        .compra-resumen .res-nombre {
            font-weight: 700;
            font-size: 0.95rem;
            color: #fff;
        }
        .compra-resumen .res-precio {
            font-size: 0.82rem;
            color: var(--color-accent, #ea9216);
            font-weight: 600;
        }
        .compra-form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.9rem;
        }
        .compra-form-grid .full { grid-column: 1 / -1; }
        .compra-field {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .compra-field label {
            font-size: 0.75rem;
            font-weight: 600;
            color: rgba(238,238,238,0.6);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .compra-field input, .compra-field select {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px;
            color: #eee;
            padding: 0.6rem 0.8rem;
            font-size: 0.88rem;
            font-family: inherit;
            outline: none;
            transition: border-color 0.2s;
        }
        .compra-field input:focus, .compra-field select:focus {
            border-color: var(--color-accent, #ea9216);
        }
        .compra-field input::placeholder { color: rgba(238,238,238,0.25); }
        .compra-total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 1.2rem 0 1rem;
            padding: 0.9rem 1.1rem;
            background: rgba(255,255,255,0.04);
            border-radius: 10px;
            border: 1px solid rgba(255,255,255,0.07);
        }
        .compra-total-row span:first-child {
            font-size: 0.88rem;
            color: rgba(238,238,238,0.6);
        }
        .compra-total-row .total-val {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--color-accent, #ea9216);
        }
        .btn-confirmar-compra {
            width: 100%;
            padding: 0.85rem;
            background: var(--color-accent, #ea9216);
            color: #1a1a2e;
            font-weight: 700;
            font-size: 0.95rem;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            transition: all 0.25s ease;
        }
        .btn-confirmar-compra:hover {
            background: #ffad3b;
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(234,146,22,0.4);
        }
        .btn-confirmar-compra:disabled {
            opacity: 0.5; cursor: not-allowed; transform: none;
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo">
                <img src="img/Logo.png" alt="Logo de la tienda" class="logo-img">
                <span>SoundVibe</span>
            </div>
            <nav class="nav-menu">
                <a href="#" class="nav-item active" data-target="articulos"><i class="fa-solid fa-guitar"></i> Artículos
                    (Instrumentos)</a>
                <a href="#" class="nav-item" data-target="ventas"><i class="fa-solid fa-cart-arrow-down"></i> Ventas</a>
                <a href="#" class="nav-item" data-target="historial"><i class="fa-solid fa-clock-rotate-left"></i>
                    Historial de compras</a>
                <a href="#" class="nav-item" data-target="clientes"><i class="fa-solid fa-users"></i> Panel de
                    Clientes</a>
            </nav>
            <div class="bottom-nav">
                <a href="#" class="nav-item" data-target="ajustes"><i class="fa-solid fa-gear"></i> Ajustes</a>
                <a href="php/logout.php" class="nav-item"><i class="fa-solid fa-right-from-bracket"></i> Salir</a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="top-header">
                <div class="search-bar">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="global-search" placeholder="Buscar guitarras, pianos, baterías...">
                    <div id="search-results" class="search-results-dropdown"></div>
                </div>
                <div class="user-profile">
                    <div class="notification-bell" id="notification-trigger">
                        <i class="fa-regular fa-bell"></i>
                        <span class="badge">3</span>
                        <div class="notifications-dropdown" id="notifications-dropdown">
                            <div class="dropdown-header">
                                <h3>Notificaciones</h3>
                                <button class="mark-all">Marcar todo</button>
                            </div>
                            <div class="notifications-list">
                                <div class="notification-item unread">
                                    <div class="ni-icon"><i class="fa-solid fa-box"></i></div>
                                    <div class="ni-content">
                                        <p>Stock bajo en <strong>Fender Stratocaster</strong>.</p>
                                        <span>Hace 5 min</span>
                                    </div>
                                </div>
                                <div class="notification-item unread">
                                    <div class="ni-icon"><i class="fa-solid fa-dollar-sign"></i></div>
                                    <div class="ni-content">
                                        <p>Nueva venta realizada por <strong>Carlos Sánchez</strong>.</p>
                                        <span>Hace 20 min</span>
                                    </div>
                                </div>
                                <div class="notification-item">
                                    <div class="ni-icon"><i class="fa-solid fa-user-plus"></i></div>
                                    <div class="ni-content">
                                        <p>Nuevo cliente registrado: <strong>Fernando Ruiz</strong>.</p>
                                        <span>Hace 2 horas</span>
                                    </div>
                                </div>
                            </div>
                            <div class="dropdown-footer">
                                <a href="#">Ver todas</a>
                            </div>
                        </div>
                    </div>
                    <div class="avatar">
                        <img src="<?php echo $avatar; ?>" alt="Avatar">
                    </div>
                    <div class="user-info">
                        <span class="user-name"><?php echo $username; ?></span>
                        <span class="user-role">Gerente</span>
                    </div>
                </div>
            </header>

            <!-- Dashboard Content Wrapper -->
            <div class="content-wrapper">

                <!-- 1. SECCIÓN: ARTÍCULOS (INSTRUMENTOS) -->
                <div id="articulos" class="view-section active-section">
                    <div class="welcome-section">
                        <h1>Gesti&oacute;n de Art&iacute;culos</h1>
                        <p>Visualiza el estado de tu inventario y los instrumentos más solicitados.</p>
                    </div>

                    <div class="kpi-grid" style="grid-template-columns: repeat(2, 1fr);">
                        <div class="kpi-card">
                            <div class="kpi-icon"><i class="fa-solid fa-boxes-stacked"></i></div>
                            <div class="kpi-details">
                                <h3>Stock Bajo</h3>
                                <h2>8 Equipos</h2>
                                <span class="trend negative"><i class="fa-solid fa-arrow-trend-down"></i> Urgente
                                    reabastecer</span>
                            </div>
                        </div>
                        <div class="kpi-card">
                            <div class="kpi-icon"><i class="fa-solid fa-truck"></i></div>
                            <div class="kpi-details">
                                <h3>Órdenes de Proveedor</h3>
                                <h2>3 Entregas</h2>
                                <span class="trend positive"><i class="fa-solid fa-arrow-trend-up"></i> A tiempo</span>
                            </div>
                        </div>
                    </div>

                    <div class="bento-grid" style="grid-template-columns: 1fr;">
                        <div class="bento-card">
                            <div class="card-header">
                                <h3>Instrumentos más vendidos del mes</h3>
                            </div>
                            <div class="top-tracks-list">
                                <div class="track-item">
                                    <span class="rank">1</span>
                                    <div class="track-info">
                                        <h4>Fender Stratocaster</h4>
                                        <p>Guitarras</p>
                                    </div>
                                    <span class="sales-count">12 uds. en almacén</span>
                                </div>
                                <div class="track-item">
                                    <span class="rank">2</span>
                                    <div class="track-info">
                                        <h4>Yamaha P-125</h4>
                                        <p>Teclados/Pianos</p>
                                    </div>
                                    <span class="sales-count">8 uds. en almacén</span>
                                </div>
                                <div class="track-item">
                                    <span class="rank">3</span>
                                    <div class="track-info">
                                        <h4>Batería Pearl Export</h4>
                                        <p>Percusión</p>
                                    </div>
                                    <span class="sales-count">3 uds. en almacén (Bajo)</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Catálogo de Artículos -->
                    <div class="bento-card" style="margin-top: 1.5rem;">
                        <div class="card-header">
                            <h3><i class="fa-solid fa-store"></i> Catálogo de Artículos</h3>
                            <div style="display:flex; gap: 0.6rem; align-items: center;">
                                <select id="filter-categoria" class="setting-select" style="padding: 0.4rem 0.8rem; font-size: 0.82rem;">
                                    <option value="all">Todas las categorías</option>
                                    <option value="Guitarras">Guitarras</option>
                                    <option value="Teclados/Pianos">Teclados/Pianos</option>
                                    <option value="Percusión">Percusión</option>
                                    <option value="Bajos">Bajos</option>
                                    <option value="Vientos">Vientos</option>
                                </select>
                            </div>
                        </div>

                        <div class="articulos-grid" id="articulos-grid">

                            <div class="articulo-card" data-id="1"
                                data-nombre="Fender Stratocaster Player"
                                data-categoria="Guitarras"
                                data-precio="850.00"
                                data-stock="12"
                                data-sku="GT-FEND-001"
                                data-proveedor="Fender Musical Instruments"
                                data-descripcion="Guitarra eléctrica con cuerpo de aliso, mástil de maple, 3 pastillas Single-Coil y acabado en Polar White. Ideal para rock, blues y pop."
                                data-img="img/inst-fender.jpg">
                                <div class="art-img-wrap inst-1">
                                    <span class="art-badge stock-ok">En stock</span>
                                </div>
                                <div class="art-body">
                                    <span class="art-cat">Guitarras</span>
                                    <h4>Fender Stratocaster Player</h4>
                                    <div class="art-meta">
                                        <span class="art-price">$850.00</span>
                                        <span class="art-stock"><i class="fa-solid fa-boxes-stacked"></i> 12 uds.</span>
                                    </div>
                                </div>
                            </div>

                            <div class="articulo-card" data-id="2"
                                data-nombre="Piano Digital Yamaha P-125"
                                data-categoria="Teclados/Pianos"
                                data-precio="1200.00"
                                data-stock="8"
                                data-sku="TK-YAM-002"
                                data-proveedor="Yamaha Corporation"
                                data-descripcion="Piano digital con 88 teclas contrapesadas, tecnología Pure CF Sound Engine, 24 voces polifónicas y conectividad USB-MIDI. Diseño compacto y elegante."
                                data-img="img/inst-yamaha.jpg">
                                <div class="art-img-wrap inst-2">
                                    <span class="art-badge stock-ok">En stock</span>
                                </div>
                                <div class="art-body">
                                    <span class="art-cat">Teclados/Pianos</span>
                                    <h4>Piano Digital Yamaha P-125</h4>
                                    <div class="art-meta">
                                        <span class="art-price">$1,200.00</span>
                                        <span class="art-stock"><i class="fa-solid fa-boxes-stacked"></i> 8 uds.</span>
                                    </div>
                                </div>
                            </div>

                            <div class="articulo-card" data-id="3"
                                data-nombre="Batería Pearl Export"
                                data-categoria="Percusión"
                                data-precio="950.50"
                                data-stock="3"
                                data-sku="PC-PEARL-003"
                                data-proveedor="Pearl Drums"
                                data-descripcion="Set de batería acústica de 5 piezas con cáscaras de poplar, herrajes incluidos y platillos Zildjian I Series. Perfecta para ensayo y presentaciones en vivo."
                                data-img="img/inst-pearl.jpg">
                                <div class="art-img-wrap inst-3">
                                    <span class="art-badge stock-low">Stock bajo</span>
                                </div>
                                <div class="art-body">
                                    <span class="art-cat">Percusión</span>
                                    <h4>Batería Pearl Export</h4>
                                    <div class="art-meta">
                                        <span class="art-price">$950.50</span>
                                        <span class="art-stock art-stock-low"><i class="fa-solid fa-triangle-exclamation"></i> 3 uds.</span>
                                    </div>
                                </div>
                            </div>

                            <div class="articulo-card" data-id="4"
                                data-nombre="Guitarra Acústica Taylor 114ce"
                                data-categoria="Guitarras"
                                data-precio="1100.00"
                                data-stock="5"
                                data-sku="GT-TAYL-004"
                                data-proveedor="Taylor Guitars"
                                data-descripcion="Guitarra acústica-eléctrica con tapa de abeto Sitka sólido, aros y fondo de sapele. Sistema de amplificación ES2 con control de volumen y tono integrados."
                                data-img="img/inst-taylor.jpg">
                                <div class="art-img-wrap inst-4">
                                    <span class="art-badge stock-ok">En stock</span>
                                </div>
                                <div class="art-body">
                                    <span class="art-cat">Guitarras</span>
                                    <h4>Guitarra Acústica Taylor 114ce</h4>
                                    <div class="art-meta">
                                        <span class="art-price">$1,100.00</span>
                                        <span class="art-stock"><i class="fa-solid fa-boxes-stacked"></i> 5 uds.</span>
                                    </div>
                                </div>
                            </div>

                            <div class="articulo-card" data-id="5"
                                data-nombre="Bajo Eléctrico Squier Affinity"
                                data-categoria="Bajos"
                                data-precio="320.00"
                                data-stock="0"
                                data-sku="BJ-SQR-005"
                                data-proveedor="Fender Musical Instruments"
                                data-descripcion="Bajo eléctrico de 4 cuerdas con cuerpo de álamo, mástil de maple y pastilla de bobina simple. Excelente opción de inicio para bajistas principiantes."
                                data-img="img/bajo.png">
                                <div class="art-img-wrap inst-5">
                                    <span class="art-badge stock-out">Agotado</span>
                                </div>
                                <div class="art-body">
                                    <span class="art-cat">Bajos</span>
                                    <h4>Bajo Eléctrico Squier Affinity</h4>
                                    <div class="art-meta">
                                        <span class="art-price">$320.00</span>
                                        <span class="art-stock art-stock-out"><i class="fa-solid fa-ban"></i> Agotado</span>
                                    </div>
                                </div>
                            </div>

                            <div class="articulo-card" data-id="6"
                                data-nombre="Saxofón Alto Yamaha YAS-280"
                                data-categoria="Vientos"
                                data-precio="1450.00"
                                data-stock="2"
                                data-sku="VT-YAM-006"
                                data-proveedor="Yamaha Corporation"
                                data-descripcion="Saxofón alto en Mi♭, cuerpo de latón laqueado con llaves plateadas, estuche rígido y boquilla 4C incluidos. Respuesta suave ideal para estudiantes avanzados."
                                data-img="img/inst-saxo.jpg">
                                <div class="art-img-wrap inst-6">
                                    <span class="art-badge stock-low">Stock bajo</span>
                                </div>
                                <div class="art-body">
                                    <span class="art-cat">Vientos</span>
                                    <h4>Saxofón Alto Yamaha YAS-280</h4>
                                    <div class="art-meta">
                                        <span class="art-price">$1,450.00</span>
                                        <span class="art-stock art-stock-low"><i class="fa-solid fa-triangle-exclamation"></i> 2 uds.</span>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- MODAL DE ARTÍCULO -->
                <div id="articulo-modal" class="articulo-modal-overlay" style="display:none;">
                    <div class="articulo-modal-box">
                        <button class="modal-close-btn" id="modal-close"><i class="fa-solid fa-xmark"></i></button>
                        <div class="modal-layout">
                            <div class="modal-img-col">
                                <div class="modal-img-wrap" id="modal-img-bg">
                                    <span class="modal-badge" id="modal-badge"></span>
                                </div>
                            </div>
                            <div class="modal-info-col">
                                <span class="modal-cat" id="modal-cat"></span>
                                <h2 id="modal-nombre"></h2>
                                <p class="modal-sku" id="modal-sku"></p>
                                <p class="modal-desc" id="modal-desc"></p>
                                <div class="modal-stats">
                                    <div class="modal-stat">
                                        <span class="stat-label">Precio</span>
                                        <span class="stat-val" id="modal-precio"></span>
                                    </div>
                                    <div class="modal-stat">
                                        <span class="stat-label">Stock</span>
                                        <span class="stat-val" id="modal-stock"></span>
                                    </div>
                                    <div class="modal-stat">
                                        <span class="stat-label">Proveedor</span>
                                        <span class="stat-val" id="modal-proveedor"></span>
                                    </div>
                                </div>
                                <div class="modal-actions">
                                    <button class="btn-comprar" id="btn-abrir-compra"><i class="fa-solid fa-bag-shopping"></i> Comprar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. SECCIÓN: VENTAS -->
                <div id="ventas" class="view-section">
                    <div class="welcome-section">
                        <h1>Panel de Ventas</h1>
                        <p>Estadísticas de ingresos y transacciones recientes de la tienda.</p>
                    </div>

                    <div class="kpi-grid" style="grid-template-columns: repeat(2, 1fr);">
                        <div class="kpi-card">
                            <div class="kpi-icon"><i class="fa-solid fa-dollar-sign"></i></div>
                            <div class="kpi-details">
                                <h3>Ingresos Totales</h3>
                                <h2>$18,450</h2>
                                <span class="trend positive"><i class="fa-solid fa-arrow-trend-up"></i> +12% respecto al
                                    mes pasado</span>
                            </div>
                        </div>
                        <div class="kpi-card">
                            <div class="kpi-icon"><i class="fa-solid fa-credit-card"></i></div>
                            <div class="kpi-details">
                                <h3>Instrumentos Vendidos</h3>
                                <h2>24</h2>
                                <span class="trend positive"><i class="fa-solid fa-arrow-trend-up"></i> +4% esta
                                    semana</span>
                            </div>
                        </div>
                    </div>

                    <div class="bento-grid" style="grid-template-columns: 1fr;">
                        <div class="bento-card">
                            <div class="card-header">
                                <h3>Ventas Recientes</h3>
                            </div>
                            <div class="table-responsive">
                                <table class="recent-sales-table">
                                    <thead>
                                        <tr>
                                            <th>Artículo</th>
                                            <th>Cliente</th>
                                            <th>Fecha</th>
                                            <th>Estado</th>
                                            <th>Monto</th>
                                        </tr>
                                    </thead>
                                    <tbody id="ventas-tbody">
                                        <tr>
                                            <td>
                                                <div class="product-info">
                                                    <div class="product-img inst-1"></div>
                                                    <span>Fender Stratocaster Player</span>
                                                </div>
                                            </td>
                                            <td>Carlos Sánchez</td>
                                            <td>Hoy, 14:30</td>
                                            <td><span class="status completed">Completado</span></td>
                                            <td>$850.00</td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="product-info">
                                                    <div class="product-img inst-2"></div>
                                                    <span>Piano Digital Yamaha P-125</span>
                                                </div>
                                            </td>
                                            <td>María López</td>
                                            <td>Hoy, 12:15</td>
                                            <td><span class="status pending">Enviando</span></td>
                                            <td>$1,200.00</td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="product-info">
                                                    <div class="product-img inst-3"></div>
                                                    <span>Batería Pearl Export</span>
                                                </div>
                                            </td>
                                            <td>Juan Pérez</td>
                                            <td>Ayer, 18:40</td>
                                            <td><span class="status completed">Completado</span></td>
                                            <td>$950.50</td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="product-info">
                                                    <div class="product-img inst-4"></div>
                                                    <span>Guitarra Acústica Taylor 114ce</span>
                                                </div>
                                            </td>
                                            <td>Ana Gómez</td>
                                            <td>Ayer, 10:20</td>
                                            <td><span class="status completed">Completado</span></td>
                                            <td>$1,100.00</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. SECCIÓN: HISTORIAL DE COMPRAS -->
                <div id="historial" class="view-section">
                    <div class="welcome-section">
                        <h1>Historial de Compras (Proveedores)</h1>
                        <p>Revisa el historial de abastecimiento y compras realizadas para nutrir el inventario.</p>
                    </div>

                    <div class="bento-grid" style="grid-template-columns: 1fr;">
                        <div class="bento-card">
                            <div class="card-header">
                                <h3>Registro de Proveedores</h3>
                            </div>
                            <div class="table-responsive">
                                <table class="recent-sales-table">
                                    <thead>
                                        <tr>
                                            <th>Lote / Pedido</th>
                                            <th>Proveedor</th>
                                            <th>Fecha de Solicitud</th>
                                            <th>Estado / Pago</th>
                                            <th>Costo Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <div class="product-info">
                                                    <div
                                                        style="background-color: var(--color-bg-main); padding: 12px; border-radius: 8px; color: var(--color-accent); font-weight: bold;">
                                                        #L-802</div>
                                                    <span>Lote Guitarras Eléctricas (10x)</span>
                                                </div>
                                            </td>
                                            <td>Fender GMI Dist.</td>
                                            <td>02 Abril, 2026</td>
                                            <td><span class="status completed">Liquidado</span></td>
                                            <td>$5,400.00</td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="product-info">
                                                    <div
                                                        style="background-color: var(--color-bg-main); padding: 12px; border-radius: 8px; color: var(--color-accent); font-weight: bold;">
                                                        #L-803</div>
                                                    <span>Sintetizadores y Pianos (5x)</span>
                                                </div>
                                            </td>
                                            <td>Yamaha Latam</td>
                                            <td>28 Marzo, 2026</td>
                                            <td><span class="status pending">En tránsito</span></td>
                                            <td>$4,150.00</td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="product-info">
                                                    <div
                                                        style="background-color: var(--color-bg-main); padding: 12px; border-radius: 8px; color: var(--color-accent); font-weight: bold;">
                                                        #L-804</div>
                                                    <span>Accesorios Básicos (Cables, Púas)</span>
                                                </div>
                                            </td>
                                            <td>MusicStore Wholesale</td>
                                            <td>25 Marzo, 2026</td>
                                            <td><span class="status completed">Liquidado</span></td>
                                            <td>$890.00</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. SECCIÓN: PANEL DE CLIENTES -->
                <div id="clientes" class="view-section">
                    <div class="welcome-section">
                        <h1>Directorio de Clientes</h1>
                        <p>Gestión de tu base de datos de compradores e interacciones recientes.</p>
                    </div>

                    <div class="kpi-grid" style="grid-template-columns: repeat(2, 1fr);">
                        <div class="kpi-card">
                            <div class="kpi-icon"><i class="fa-solid fa-users"></i></div>
                            <div class="kpi-details">
                                <h3>Total de Clientes Registrados</h3>
                                <h2>142</h2>
                                <span class="trend positive"><i class="fa-solid fa-arrow-trend-up"></i> +12 este
                                    mes</span>
                            </div>
                        </div>
                        <div class="kpi-card">
                            <div class="kpi-icon"><i class="fa-solid fa-star"></i></div>
                            <div class="kpi-details">
                                <h3>Clientes Frecuentes</h3>
                                <h2>38</h2>
                                <span class="trend positive"><i class="fa-solid fa-arrow-trend-up"></i> Retención
                                    alta</span>
                            </div>
                        </div>
                    </div>

                    <div class="bento-grid" style="grid-template-columns: 1fr;">
                        <div class="bento-card">
                            <div class="card-header">
                                <h3>Últimos Registros</h3>
                                <button class="btn-more">Exportar a CSV</button>
                            </div>
                            <div class="table-responsive">
                                <table class="recent-sales-table">
                                    <thead>
                                        <tr>
                                            <th>Cliente</th>
                                            <th>Correo Electrónico</th>
                                            <th>Fecha de Registro</th>
                                            <th>Gastos en Tienda</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody id="clientes-tbody">
                                        <tr>
                                            <td>
                                                <div class="product-info">
                                                    <div class="avatar" style="padding: 0;"><img
                                                            src="https://i.pravatar.cc/150?img=33" alt=""
                                                            style="width: 44px; height: 44px; border-radius: 50%; border: none;">
                                                    </div>
                                                    <span>Fernando Ruiz</span>
                                                </div>
                                            </td>
                                            <td>fruiz_per@gmail.com</td>
                                            <td>Hoy, 09:12 AM</td>
                                            <td>$850.00</td>
                                            <td><span class="status completed">Activo</span></td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="product-info">
                                                    <div class="avatar" style="padding: 0;"><img
                                                            src="https://i.pravatar.cc/150?img=47" alt=""
                                                            style="width: 44px; height: 44px; border-radius: 50%; border: none;">
                                                    </div>
                                                    <span>Sara Castillo</span>
                                                </div>
                                            </td>
                                            <td>sara.cast89@outlook.com</td>
                                            <td>Ayer, 04:30 PM</td>
                                            <td>$3,450.00</td>
                                            <td><span class="status completed">Activo</span></td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="product-info">
                                                    <div class="avatar" style="padding: 0;"><img
                                                            src="https://i.pravatar.cc/150?img=12" alt=""
                                                            style="width: 44px; height: 44px; border-radius: 50%; border: none;">
                                                    </div>
                                                    <span>Miguel Torres</span>
                                                </div>
                                            </td>
                                            <td>mitore99@yahoo.com</td>
                                            <td>03 Abril, 2026</td>
                                            <td>$0.00</td>
                                            <td><span class="status pending">Pendiente</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 5. SECCIÓN: AJUSTES -->
                <div id="ajustes" class="view-section">
                    <div class="welcome-section">
                        <h1>Ajustes y Personalización</h1>
                        <p>Configura tu entorno de trabajo y datos de perfil.</p>
                    </div>

                    <div class="bento-grid" style="grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));">

                        <!-- Tarjeta: Apariencia -->
                        <div class="bento-card">
                            <div class="card-header">
                                <h3><i class="fa-solid fa-palette"></i> Apariencia</h3>
                            </div>
                            <div class="settings-form">
                                <div class="setting-group">
                                    <label>Modo de Pantalla</label>
                                    <div class="theme-toggle">
                                        <button class="theme-btn active" data-theme="dark"><i
                                                class="fa-solid fa-moon"></i> Oscuro</button>
                                        <button class="theme-btn" data-theme="light"><i class="fa-solid fa-sun"></i>
                                            Claro</button>
                                    </div>
                                </div>
                                <div class="setting-group">
                                    <label>Color de Acento</label>
                                    <div class="accent-colors">
                                        <div class="color-swatch active" data-color="#ea9216"
                                            style="background-color: #ea9216;"></div>
                                        <div class="color-swatch" data-color="#3498db"
                                            style="background-color: #3498db;"></div>
                                        <div class="color-swatch" data-color="#e74c3c"
                                            style="background-color: #e74c3c;"></div>
                                        <div class="color-swatch" data-color="#2ecc71"
                                            style="background-color: #2ecc71;"></div>
                                        <div class="color-swatch" data-color="#9b59b6"
                                            style="background-color: #9b59b6;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tarjeta: Perfil de Usuario -->
                        <div class="bento-card">
                            <div class="card-header">
                                <h3><i class="fa-solid fa-user-gear"></i> Perfil de Usuario</h3>
                            </div>
                            <div class="settings-form">
                                <div class="setting-group profile-picture-setting">
                                    <label>Foto de Perfil</label>
                                    <div class="avatar-selector">
                                        <img src="<?php echo $avatar; ?>" alt="Actual" class="current-avatar-preview"
                                            id="current-avatar-preview">
                                        <div class="avatar-options">
                                            <img src="img/Angel.jpg" class="avatar-opt <?php echo ($avatar === 'img/Angel.jpg') ? 'active' : ''; ?>" data-avatar="img/Angel.jpg">
                                            <img src="img/Andree.jpg" class="avatar-opt <?php echo ($avatar === 'img/Andree.jpg') ? 'active' : ''; ?>" data-avatar="img/Andree.jpg">
                                            <img src="img/James.jpg" class="avatar-opt <?php echo ($avatar === 'img/James.jpg') ? 'active' : ''; ?>" data-avatar="img/James.jpg">
                                            <img src="img/Rodrigo.jpg" class="avatar-opt <?php echo ($avatar === 'img/Rodrigo.jpg') ? 'active' : ''; ?>" data-avatar="img/Rodrigo.jpg">
                                        </div>
                                    </div>
                                </div>
                                <div class="setting-group">
                                    <label for="prof-name">Nombre</label>
                                    <input type="text" id="prof-name" value="<?php echo $username; ?>" placeholder="Ej. Juan Pérez">
                                </div>
                                <div class="setting-group">
                                    <label for="prof-role">Rol / Cargo</label>
                                    <input type="text" id="prof-role" value="Gerente" placeholder="Ej. Vendedor">
                                </div>
                            </div>
                        </div>

                        <!-- Tarjeta: Notificaciones -->
                        <div class="bento-card">
                            <div class="card-header">
                                <h3><i class="fa-solid fa-bell"></i> Notificaciones</h3>
                            </div>
                            <div class="settings-form">
                                <div class="setting-row">
                                    <div class="setting-info">
                                        <h4>Alertas de Escritorio</h4>
                                        <p>Mostrar notificaciones del sistema.</p>
                                    </div>
                                    <label class="switch">
                                        <input type="checkbox" id="notif-desktop" checked>
                                        <span class="slider"></span>
                                    </label>
                                </div>
                                <div class="setting-row">
                                    <div class="setting-info">
                                        <h4>Sonidos de Alerta</h4>
                                        <p>Reproducir sonido al recibir avisos.</p>
                                    </div>
                                    <label class="switch">
                                        <input type="checkbox" id="notif-sound" checked>
                                        <span class="slider"></span>
                                    </label>
                                </div>
                                <div class="setting-row">
                                    <div class="setting-info">
                                        <h4>Resumen Semanal</h4>
                                        <p>Recibir estadísticas por correo.</p>
                                    </div>
                                    <label class="switch">
                                        <input type="checkbox" id="notif-email">
                                        <span class="slider"></span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Tarjeta: Sistema -->
                        <div class="bento-card">
                            <div class="card-header">
                                <h3><i class="fa-solid fa-globe"></i> Sistema</h3>
                            </div>
                            <div class="settings-form">
                                <div class="setting-group">
                                    <label for="sys-lang">Idioma del Panel</label>
                                    <select id="sys-lang" class="setting-select">
                                        <option value="es" selected>Español</option>
                                        <option value="en">English (Próximamente)</option>
                                    </select>
                                </div>
                                <div class="setting-group">
                                    <label for="sys-currency">Moneda Principal</label>
                                    <select id="sys-currency" class="setting-select">
                                        <option value="USD" selected>USD - Dólar</option>
                                        <option value="MXN">MXN - Peso Mexicano</option>
                                        <option value="EUR">EUR - Euro</option>
                                    </select>
                                </div>
                                <div class="setting-actions">
                                    <button class="btn-save" id="save-profile" style="width: 100%;">Guardar
                                        Todo</button>
                                    <button class="btn-reset" id="reset-settings">Restablecer Todo</button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </main>
    </div>


    <!-- PANEL FLOTANTE DE COMPRA -->
    <div id="compra-overlay" class="compra-overlay">
        <div class="compra-box">
            <button class="compra-close" id="compra-close"><i class="fa-solid fa-xmark"></i></button>
            <h3><i class="fa-solid fa-bag-shopping" style="color:var(--color-accent,#ea9216);margin-right:8px;"></i>Registrar Compra</h3>
            <p class="compra-subtitle">Completa los datos del cliente para finalizar la venta.</p>

            <div class="compra-resumen">
                <i class="fa-solid fa-guitar"></i>
                <div>
                    <div class="res-nombre" id="compra-res-nombre">—</div>
                    <div class="res-precio" id="compra-res-precio">—</div>
                </div>
            </div>

            <div class="compra-form-grid">
                <div class="compra-field">
                    <label>DNI *</label>
                    <input type="text" id="c-dni" placeholder="Ej: 12345678" maxlength="9">
                </div>
                <div class="compra-field">
                    <label>Teléfono *</label>
                    <input type="text" id="c-telefono" placeholder="Ej: 987654321" maxlength="9">
                </div>
                <div class="compra-field">
                    <label>Nombre *</label>
                    <input type="text" id="c-nombre" placeholder="Ej: Carlos">
                </div>
                <div class="compra-field">
                    <label>Apellidos *</label>
                    <input type="text" id="c-apellidos" placeholder="Ej: Sánchez Ruiz">
                </div>
                <div class="compra-field full">
                    <label>Email</label>
                    <input type="email" id="c-email" placeholder="correo@ejemplo.com">
                </div>
                <div class="compra-field full">
                    <label>Dirección</label>
                    <input type="text" id="c-direccion" placeholder="Ej: Av. Principal 123, Lima">
                </div>
                <div class="compra-field">
                    <label>Adelanto (S/.)</label>
                    <input type="number" id="c-adelanto" placeholder="0.00" min="0" step="0.01">
                </div>
                <div class="compra-field">
                    <label>Estado</label>
                    <select id="c-estado">
                        <option value="Completado">Completado</option>
                        <option value="Pendiente">Pendiente</option>
                        <option value="Enviando">Enviando</option>
                    </select>
                </div>
            </div>

            <div class="compra-total-row">
                <span>Total a pagar</span>
                <span class="total-val" id="compra-total-val">—</span>
            </div>

            <button class="btn-confirmar-compra" id="btn-confirmar-compra">
                <i class="fa-solid fa-check"></i> Confirmar Venta
            </button>
        </div>
    </div>

    <!-- Scripts -->
    <script src="js/Dashboard.js"></script>
    <script>
    // ── Catálogo: modal de artículo + panel de compra ─────────────
    (function () {
        const overlay    = document.getElementById('articulo-modal');
        const closeBtn   = document.getElementById('modal-close');
        const cards      = document.querySelectorAll('.articulo-card');
        const compraOverlay = document.getElementById('compra-overlay');
        const compraClose   = document.getElementById('compra-close');
        const btnAbrirCompra = document.getElementById('btn-abrir-compra');
        const btnConfirmar   = document.getElementById('btn-confirmar-compra');

        const badgeClasses = { 'En stock': 'stock-ok', 'Stock bajo': 'stock-low', 'Agotado': 'stock-out' };
        let currentCard = null; // card actualmente abierta

        // avatares aleatorios para nuevos clientes
        const avatarPool = [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30];
        let avatarIdx = 0;

        function getBadgeText(stock) {
            const n = parseInt(stock);
            if (n === 0) return 'Agotado';
            if (n <= 3)  return 'Stock bajo';
            return 'En stock';
        }

        function openModal(card) {
            currentCard = card;
            const d = card.dataset;
            const badgeText = getBadgeText(d.stock);
            const badgeCls  = badgeClasses[badgeText];

            const imgWrap   = card.querySelector('.art-img-wrap');
            const instClass = [...imgWrap.classList].find(c => c.startsWith('inst-')) || 'inst-1';
            const modalImg  = document.getElementById('modal-img-bg');
            modalImg.className = 'modal-img-wrap ' + instClass;

            const badge = document.getElementById('modal-badge');
            badge.textContent = badgeText;
            badge.className   = 'modal-badge ' + badgeCls;

            document.getElementById('modal-cat').textContent       = d.categoria;
            document.getElementById('modal-nombre').textContent    = d.nombre;
            document.getElementById('modal-sku').textContent       = 'SKU: ' + d.sku;
            document.getElementById('modal-desc').textContent      = d.descripcion;
            document.getElementById('modal-precio').textContent    = '$' + parseFloat(d.precio).toLocaleString('es-PE', {minimumFractionDigits:2});
            document.getElementById('modal-stock').textContent     = d.stock + ' unidades';
            document.getElementById('modal-proveedor').textContent = d.proveedor;

            // Deshabilitar comprar si agotado
            if (btnAbrirCompra) {
                btnAbrirCompra.disabled = parseInt(d.stock) === 0;
                btnAbrirCompra.title = parseInt(d.stock) === 0 ? 'Sin stock disponible' : '';
            }

            overlay.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            overlay.style.display = 'none';
            document.body.style.overflow = '';
        }

        // ── Abrir panel de compra ────────────────────────────────
        function openCompra() {
            if (!currentCard) return;
            const d = currentCard.dataset;
            document.getElementById('compra-res-nombre').textContent = d.nombre;
            document.getElementById('compra-res-precio').textContent =
                '$' + parseFloat(d.precio).toLocaleString('es-PE', {minimumFractionDigits:2}) + ' c/u';
            document.getElementById('compra-total-val').textContent =
                '$' + parseFloat(d.precio).toLocaleString('es-PE', {minimumFractionDigits:2});

            // limpiar campos
            ['c-dni','c-telefono','c-nombre','c-apellidos','c-email','c-direccion','c-adelanto'].forEach(id => {
                document.getElementById(id).value = '';
            });
            document.getElementById('c-estado').value = 'Completado';

            overlay.style.display = 'none'; // cerrar modal artículo
            compraOverlay.style.display = 'flex';
        }

        function closeCompra() {
            compraOverlay.style.display = 'none';
            document.body.style.overflow = '';
        }

        // ── Confirmar compra ─────────────────────────────────────
        async function confirmarCompra() {
            const dni      = document.getElementById('c-dni').value.trim();
            const tel      = document.getElementById('c-telefono').value.trim();
            const nombre   = document.getElementById('c-nombre').value.trim();
            const apellidos= document.getElementById('c-apellidos').value.trim();
            const email    = document.getElementById('c-email').value.trim();
            const direccion= document.getElementById('c-direccion').value.trim();
            const adelanto = parseFloat(document.getElementById('c-adelanto').value) || 0;
            const estado   = document.getElementById('c-estado').value;

            if (!dni || !tel || !nombre || !apellidos) {
                alert('Por favor completa los campos obligatorios: DNI, Teléfono, Nombre y Apellidos.');
                return;
            }

            const d = currentCard.dataset;
            const precio    = parseFloat(d.precio);
            const now       = new Date();
            const fechaHora = 'Hoy, ' + now.getHours().toString().padStart(2,'0') + ':' + now.getMinutes().toString().padStart(2,'0');
            const imgWrap   = currentCard.querySelector('.art-img-wrap');
            const instClass = [...imgWrap.classList].find(c => c.startsWith('inst-')) || 'inst-1';

            // Deshabilitar botón mientras se guarda
            const orig = btnConfirmar.innerHTML;
            btnConfirmar.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Guardando...';
            btnConfirmar.disabled = true;

            try {
                const response = await fetch('php/registrar_venta.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        dni, nombre, apellidos,
                        email, telefono: tel,
                        direccion, adelanto, estado,
                        id_instrumento: parseInt(d.id)
                    })
                });

                const result = await response.json();

                if (!result.success) {
                    alert('Error: ' + result.message);
                    btnConfirmar.innerHTML = orig;
                    btnConfirmar.disabled = false;
                    return;
                }

                // ── Insertar en tabla Ventas ─────────────────────
                const ventasTbody = document.getElementById('ventas-tbody');
                if (ventasTbody) {
                    const statusClass = estado === 'Completado' ? 'completed' : 'pending';
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>
                            <div class="product-info">
                                <div class="product-img ${instClass}"></div>
                                <span>${d.nombre}</span>
                            </div>
                        </td>
                        <td>${nombre} ${apellidos}</td>
                        <td>${fechaHora}</td>
                        <td><span class="status ${statusClass}">${estado}</span></td>
                        <td>$${precio.toLocaleString('es-PE', {minimumFractionDigits:2})}</td>
                    `;
                    ventasTbody.insertBefore(tr, ventasTbody.firstChild);
                }

                // ── Insertar en tabla Clientes ───────────────────
                const clientesTbody = document.getElementById('clientes-tbody');
                if (clientesTbody) {
                    const iniciales = encodeURIComponent((nombre.charAt(0) + apellidos.charAt(0)).toUpperCase());
                    const avatarUrl = `https://ui-avatars.com/api/?name=${iniciales}&background=ea9216&color=1a1a2e&size=44&bold=true&rounded=true`;
                    const emailMostrar = email || '—';
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>
                            <div class="product-info">
                                <div class="avatar" style="padding:0;">
                                    <img src="${avatarUrl}" alt=""
                                        style="width:44px;height:44px;border-radius:50%;border:none;">
                                </div>
                                <span>${nombre} ${apellidos}</span>
                            </div>
                        </td>
                        <td>${emailMostrar}</td>
                        <td>${fechaHora}</td>
                        <td>$${precio.toLocaleString('es-PE', {minimumFractionDigits:2})}</td>
                        <td><span class="status completed">Activo</span></td>
                    `;
                    clientesTbody.insertBefore(tr, clientesTbody.firstChild);
                }

                // Actualizar stock visualmente en el card
                const stockEl = currentCard.querySelector('.art-stock');
                const badgeEl = currentCard.querySelector('.art-badge');
                const nuevoStock = parseInt(d.stock) - 1;
                currentCard.dataset.stock = nuevoStock;
                if (nuevoStock === 0) {
                    stockEl.className = 'art-stock art-stock-out';
                    stockEl.innerHTML = '<i class="fa-solid fa-ban"></i> Agotado';
                    badgeEl.className = 'art-badge stock-out';
                    badgeEl.textContent = 'Agotado';
                    if (btnAbrirCompra) btnAbrirCompra.disabled = true;
                } else if (nuevoStock <= 3) {
                    stockEl.className = 'art-stock art-stock-low';
                    stockEl.innerHTML = `<i class="fa-solid fa-triangle-exclamation"></i> ${nuevoStock} uds.`;
                    badgeEl.className = 'art-badge stock-low';
                    badgeEl.textContent = 'Stock bajo';
                } else {
                    stockEl.innerHTML = `<i class="fa-solid fa-boxes-stacked"></i> ${nuevoStock} uds.`;
                }

                closeCompra();

                btnConfirmar.innerHTML = '<i class="fa-solid fa-check"></i> ¡Venta registrada!';
                setTimeout(() => {
                    btnConfirmar.innerHTML = orig;
                    btnConfirmar.disabled = false;
                }, 2000);

            } catch (err) {
                alert('Error de conexión. Intenta de nuevo.');
                btnConfirmar.innerHTML = orig;
                btnConfirmar.disabled = false;
            }
        }

        // ── Eventos ──────────────────────────────────────────────
        cards.forEach(card => card.addEventListener('click', () => openModal(card)));
        closeBtn.addEventListener('click', closeModal);
        overlay.addEventListener('click', e => { if (e.target === overlay) closeModal(); });

        if (btnAbrirCompra) btnAbrirCompra.addEventListener('click', openCompra);
        if (compraClose)    compraClose.addEventListener('click', closeCompra);
        if (btnConfirmar)   btnConfirmar.addEventListener('click', confirmarCompra);
        compraOverlay.addEventListener('click', e => { if (e.target === compraOverlay) closeCompra(); });

        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') {
                if (compraOverlay.style.display === 'flex') closeCompra();
                else closeModal();
            }
        });

        // ── Filtro por categoría ──────────────────────────────────
        const select = document.getElementById('filter-categoria');
        if (select) {
            select.addEventListener('change', function () {
                const val = this.value;
                cards.forEach(card => {
                    const match = val === 'all' || card.dataset.categoria === val;
                    card.style.display = match ? '' : 'none';
                });
            });
        }
    })();
    </script>
</body>

</html>