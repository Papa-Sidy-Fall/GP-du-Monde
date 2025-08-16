<?php
session_start();

// Vérifier si l'utilisateur est connecté
$is_logged_in = isset($_SESSION['user_id']);
$page = $_GET['page'] ?? 'login';

// Si l'utilisateur n'est pas connecté et tente d'accéder à une page protégée
if (!$is_logged_in && !in_array($page, ['login', 'client', 'api'])) {
    $page = 'login';
}

// Si l'utilisateur est connecté et tente d'accéder à la page de login
if ($is_logged_in && $page === 'login') {
    $page = 'dashboard';
}

$title = 'GP du Monde - Gestion Cargaison';

switch ($page) {
    case 'login':
        $title = 'Connexion - GP du Monde';
        include 'pages/login.php';
        break;
    
    case 'client':
        $title = 'Suivi Colis - GP du Monde';
        include 'pages/client.php';
        break;
        
    case 'dashboard':
        $title = 'Tableau de Bord - GP du Monde';
        include 'pages/dashboard.php';
        break;
        
    case 'cargaisons':
        $title = 'Gestion Cargaisons - GP du Monde';
        include 'pages/cargaisons.php';
        break;
        
    case 'colis':
        $title = 'Gestion Colis - GP du Monde';
        include 'pages/colis.php';
        break;
        
    case 'clients':
        $title = 'Gestion Clients - GP du Monde';
        include 'pages/clients.php';
        break;
        
    case 'suivi':
        $title = 'Suivi Colis - GP du Monde';
        include 'pages/suivi.php';
        break;
        
    case 'api':
        include 'api/handler.php';
        break;
        
    default:
        header('Location: ?page=login');
        exit;
}
?>
