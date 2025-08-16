<?php
$current_page = $_GET['page'] ?? 'dashboard';
?>

<!-- Navbar -->
<nav class="navbar">
    <div class="navbar-brand">
        <div class="logo-small">GP</div>
        GP du Monde - Gestion Cargaison
    </div>
    <div class="navbar-user">
        <div class="user-info">
            <div class="user-avatar">A</div>
            <span>Admin</span>
        </div>
        <button class="btn-logout" onclick="logout()">Déconnexion</button>
    </div>
</nav>

<!-- Sidebar -->
<aside class="sidebar">
    <nav class="sidebar-menu">
        <a href="?page=dashboard" class="menu-item <?= $current_page === 'dashboard' ? 'active' : '' ?>">
            <i class="fas fa-chart-bar"></i> Tableau de bord
        </a>
        <a href="?page=cargaisons" class="menu-item <?= $current_page === 'cargaisons' ? 'active' : '' ?>">
            <i class="fas fa-ship"></i> Cargaisons
        </a>
        <a href="?page=colis" class="menu-item <?= $current_page === 'colis' ? 'active' : '' ?>">
            <i class="fas fa-box"></i> Gestion des colis
        </a>
        <a href="?page=clients" class="menu-item <?= $current_page === 'clients' ? 'active' : '' ?>">
            <i class="fas fa-users"></i> Clients
        </a>
        <a href="?page=suivi" class="menu-item <?= $current_page === 'suivi' ? 'active' : '' ?>">
            <i class="fas fa-search"></i> Suivi colis
        </a>
    </nav>
</aside>
