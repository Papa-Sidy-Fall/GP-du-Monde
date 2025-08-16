<?php include 'includes/header.php'; ?>

<div class="app-container">
    <?php include 'includes/sidebar.php'; ?>

    <!-- Contenu principal -->
    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">Tableau de bord</h1>
            <p class="page-subtitle">Vue d'ensemble de vos cargaisons et colis</p>
        </div>

        <div class="stats-grid" id="stats-grid">
            <!-- Les statistiques seront chargées via JavaScript -->
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Cargaisons récentes</h3>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>N° Cargaison</th>
                            <th>Type</th>
                            <th>Départ</th>
                            <th>Arrivée</th>
                            <th>Nb Colis</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="cargaisons-recentes">
                        <!-- Les données seront chargées via JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<script>
async function loadDashboardData() {
    try {
        // Charger les statistiques
        const statsResponse = await fetch('?page=api&action=get_stats');
        const stats = await statsResponse.json();
        
        if (stats.success) {
            displayStats(stats.data);
        }
        
        // Charger les cargaisons récentes
        const cargaisonsResponse = await fetch('?page=api&action=get_recent_cargaisons');
        const cargaisons = await cargaisonsResponse.json();
        
        if (cargaisons.success) {
            displayRecentCargaisons(cargaisons.data);
        }
        
    } catch (error) {
        console.error('Erreur lors du chargement des données:', error);
    }
}

function displayStats(stats) {
    const statsGrid = document.getElementById('stats-grid');
    statsGrid.innerHTML = `
        <div class="stat-card">
            <div class="stat-value">${stats.cargaisonsActives}</div>
            <div class="stat-label">Cargaisons actives</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">${stats.colisEnCours}</div>
            <div class="stat-label">Colis en cours</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">${stats.colisLivres}</div>
            <div class="stat-label">Colis livrés</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">${stats.colisEnRetard}</div>
            <div class="stat-label">Colis en retard</div>
        </div>
    `;
}

function displayRecentCargaisons(cargaisons) {
    const tbody = document.getElementById('cargaisons-recentes');
    tbody.innerHTML = cargaisons.map(cargaison => `
        <tr>
            <td>${cargaison.numero}</td>
            <td><i class="fas fa-${getTypeIcon(cargaison.type)}"></i> ${capitalize(cargaison.type)}</td>
            <td>${cargaison.lieuDepart.nom}</td>
            <td>${cargaison.lieuArrivee.nom}</td>
            <td>${cargaison.nbColis || 0}</td>
            <td><span class="status-badge status-${cargaison.etat.toLowerCase().replace('_', '-')}">${cargaison.etat}</span></td>
            <td>
                <button class="btn-sm btn-primary" onclick="voirCargaison('${cargaison.numero}')">Voir</button>
                ${cargaison.statut === 'OUVERTE' ? 
                    `<button class="btn-sm btn-secondary" onclick="fermerCargaison('${cargaison.numero}')">Fermer</button>` :
                    (cargaison.etat === 'EN_ATTENTE' ? 
                        `<button class="btn-sm btn-warning" onclick="rouvrirCargaison('${cargaison.numero}')">Rouvrir</button>` : '')
                }
            </td>
        </tr>
    `).join('');
}

function getTypeIcon(type) {
    switch(type) {
        case 'maritime': return 'ship';
        case 'aerienne': return 'plane';
        case 'routiere': return 'truck';
        default: return 'box';
    }
}

function capitalize(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function voirCargaison(numero) {
    window.location.href = `?page=cargaisons&action=view&numero=${numero}`;
}

async function fermerCargaison(numero) {
    if (confirm('Êtes-vous sûr de vouloir fermer cette cargaison ?')) {
        try {
            const response = await fetch('?page=api&action=close_cargaison', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ numero })
            });
            
            const result = await response.json();
            if (result.success) {
                loadDashboardData();
            } else {
                alert(result.message);
            }
        } catch (error) {
            alert('Erreur lors de la fermeture de la cargaison');
        }
    }
}

async function rouvrirCargaison(numero) {
    if (confirm('Êtes-vous sûr de vouloir rouvrir cette cargaison ? Cela doublera le coût total.')) {
        try {
            const response = await fetch('?page=api&action=reopen_cargaison', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ numero })
            });
            
            const result = await response.json();
            if (result.success) {
                loadDashboardData();
            } else {
                alert(result.message);
            }
        } catch (error) {
            alert('Erreur lors de la réouverture de la cargaison');
        }
    }
}

function logout() {
    if (confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
        window.location.href = '?page=api&action=logout';
    }
}

// Charger les données au chargement de la page
document.addEventListener('DOMContentLoaded', loadDashboardData);
</script>

<?php include 'includes/footer.php'; ?>
