<?php include 'includes/header.php'; ?>

<div class="app-container">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
        <div class="page-header">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h1 class="page-title">Gestion des Clients</h1>
                    <p class="page-subtitle">Liste des expéditeurs et destinataires</p>
                </div>
                <button class="btn-primary" onclick="showCreateClientModal()">
                    <i class="fas fa-plus"></i> Nouveau Client
                </button>
            </div>
        </div>

        <!-- Filtres de recherche -->
        <div class="card" style="margin-bottom: 20px;">
            <div class="card-body">
                <div class="filters-row">
                    <div class="filter-group">
                        <label>Recherche:</label>
                        <input type="text" id="searchInput" placeholder="Nom, prénom, téléphone...">
                    </div>
                    <div class="filter-group">
                        <label>Type:</label>
                        <select id="typeFilter">
                            <option value="">Tous</option>
                            <option value="expediteur">Expéditeurs</option>
                            <option value="destinataire">Destinataires</option>
                        </select>
                    </div>
                    <button class="btn-secondary" onclick="applyFilters()">Filtrer</button>
                </div>
            </div>
        </div>

        <!-- Liste des clients -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Liste des Clients</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID Client</th>
                                <th>Nom & Prénom</th>
                                <th>Téléphone</th>
                                <th>Email</th>
                                <th>Adresse</th>
                                <th>Nb Colis</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="clients-table">
                            <!-- Données chargées via JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Modal création/modification client -->
<div id="clientModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Nouveau Client</h3>
            <span class="close" onclick="closeClientModal()">&times;</span>
        </div>
        <form id="clientForm">
            <input type="hidden" id="clientId">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="nom">Nom *</label>
                    <input type="text" id="nom" required>
                </div>
                <div class="form-group">
                    <label for="prenom">Prénom *</label>
                    <input type="text" id="prenom" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="telephone">Téléphone *</label>
                    <input type="tel" id="telephone" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email">
                </div>
            </div>
            
            <div class="form-group">
                <label for="adresse">Adresse *</label>
                <textarea id="adresse" rows="3" required></textarea>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeClientModal()">Annuler</button>
                <button type="submit" class="btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal détails client -->
<div id="detailsModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Détails du Client</h3>
            <span class="close" onclick="closeDetailsModal()">&times;</span>
        </div>
        <div style="padding: 25px;">
            <div id="clientDetails">
                <!-- Contenu chargé dynamiquement -->
            </div>
            
            <!-- Historique des colis -->
            <div style="margin-top: 30px;">
                <h4 style="margin-bottom: 15px; color: #2c3e50;">
                    <i class="fas fa-history"></i> Historique des Colis
                </h4>
                <div id="clientColis">
                    <!-- Liste des colis du client -->
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" onclick="closeDetailsModal()">Fermer</button>
        </div>
    </div>
</div>

<script>
let clients = [];
let allColis = [];

// Charger les données initiales
async function loadData() {
    try {
        // Charger les clients depuis les colis (expéditeurs et destinataires)
        const colisResponse = await fetch('?page=api&action=get_colis');
        const colisResult = await colisResponse.json();
        if (colisResult.success) {
            allColis = colisResult.data;
            extractClientsFromColis();
            displayClients(clients);
        }
    } catch (error) {
        console.error('Erreur lors du chargement:', error);
        GPMonde.Notifications.error('Erreur lors du chargement des données');
    }
}

function extractClientsFromColis() {
    const clientsMap = new Map();
    
    allColis.forEach(colis => {
        // Ajouter l'expéditeur
        const expediteurKey = `${colis.expediteur.nom}_${colis.expediteur.prenom}_${colis.expediteur.telephone}`;
        if (!clientsMap.has(expediteurKey)) {
            clientsMap.set(expediteurKey, {
                ...colis.expediteur,
                type: 'expediteur',
                nbColisExpedies: 0,
                nbColisRecus: 0
            });
        }
        clientsMap.get(expediteurKey).nbColisExpedies++;
        
        // Ajouter le destinataire
        const destinataireKey = `${colis.destinataire.nom}_${colis.destinataire.prenom}_${colis.destinataire.telephone}`;
        if (!clientsMap.has(destinataireKey)) {
            clientsMap.set(destinataireKey, {
                ...colis.destinataire,
                type: 'destinataire',
                nbColisExpedies: 0,
                nbColisRecus: 0
            });
        }
        clientsMap.get(destinataireKey).nbColisRecus++;
        
        // Si c'est le même client, fusionner les types
        if (expediteurKey === destinataireKey) {
            clientsMap.get(expediteurKey).type = 'les-deux';
        }
    });
    
    clients = Array.from(clientsMap.values());
}

function displayClients(data) {
    const tbody = document.getElementById('clients-table');
    tbody.innerHTML = data.map(client => `
        <tr>
            <td><strong>${client.id}</strong></td>
            <td>
                <strong>${client.prenom} ${client.nom}</strong>
                <br><small class="badge-${client.type}">${getTypeLabel(client.type)}</small>
            </td>
            <td>
                <a href="tel:${client.telephone}">${client.telephone}</a>
            </td>
            <td>
                ${client.email ? `<a href="mailto:${client.email}">${client.email}</a>` : '-'}
            </td>
            <td title="${client.adresse}">
                ${client.adresse.length > 30 ? client.adresse.substring(0, 30) + '...' : client.adresse}
            </td>
            <td>
                <span class="stats-badge">
                    ${client.nbColisExpedies + client.nbColisRecus} 
                    <small>(${client.nbColisExpedies}E / ${client.nbColisRecus}R)</small>
                </span>
            </td>
            <td>
                <div class="action-buttons">
                    <button class="btn-sm btn-primary" onclick="viewClient('${client.id}')">Voir</button>
                    <button class="btn-sm btn-info" onclick="editClient('${client.id}')">Modifier</button>
                    <button class="btn-sm btn-success" onclick="createColisForClient('${client.id}')">Nouveau Colis</button>
                </div>
            </td>
        </tr>
    `).join('');
}

function getTypeLabel(type) {
    switch(type) {
        case 'expediteur': return 'Expéditeur';
        case 'destinataire': return 'Destinataire';
        case 'les-deux': return 'Exp. & Dest.';
        default: return 'Client';
    }
}

function showCreateClientModal() {
    document.getElementById('modalTitle').textContent = 'Nouveau Client';
    document.getElementById('clientForm').reset();
    document.getElementById('clientId').value = '';
    GPMonde.Modal.show('clientModal');
}

function closeClientModal() {
    GPMonde.Modal.hide('clientModal');
}

function closeDetailsModal() {
    GPMonde.Modal.hide('detailsModal');
}

async function viewClient(clientId) {
    const client = clients.find(c => c.id === clientId);
    if (!client) return;
    
    // Afficher les détails du client
    const detailsDiv = document.getElementById('clientDetails');
    detailsDiv.innerHTML = `
        <div class="client-info-grid">
            <div class="info-card">
                <h5><i class="fas fa-user"></i> Informations Personnelles</h5>
                <div class="info-row">
                    <span class="label">Nom complet:</span>
                    <span class="value">${client.prenom} ${client.nom}</span>
                </div>
                <div class="info-row">
                    <span class="label">Téléphone:</span>
                    <span class="value"><a href="tel:${client.telephone}">${client.telephone}</a></span>
                </div>
                <div class="info-row">
                    <span class="label">Email:</span>
                    <span class="value">${client.email ? `<a href="mailto:${client.email}">${client.email}</a>` : 'Non renseigné'}</span>
                </div>
                <div class="info-row">
                    <span class="label">Adresse:</span>
                    <span class="value">${client.adresse}</span>
                </div>
            </div>
            
            <div class="info-card">
                <h5><i class="fas fa-chart-bar"></i> Statistiques</h5>
                <div class="stats-grid">
                    <div class="stat-item">
                        <span class="stat-number">${client.nbColisExpedies}</span>
                        <span class="stat-label">Colis expédiés</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">${client.nbColisRecus}</span>
                        <span class="stat-label">Colis reçus</span>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Afficher l'historique des colis
    const clientColis = allColis.filter(colis => 
        colis.expediteur.id === clientId || colis.destinataire.id === clientId
    );
    
    const colisDiv = document.getElementById('clientColis');
    if (clientColis.length > 0) {
        colisDiv.innerHTML = `
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Role</th>
                        <th>Autre partie</th>
                        <th>État</th>
                        <th>Date</th>
                        <th>Prix</th>
                    </tr>
                </thead>
                <tbody>
                    ${clientColis.map(colis => {
                        const isExpediteur = colis.expediteur.id === clientId;
                        const autrePartie = isExpediteur ? 
                            `${colis.destinataire.prenom} ${colis.destinataire.nom}` :
                            `${colis.expediteur.prenom} ${colis.expediteur.nom}`;
                        
                        return `
                            <tr>
                                <td><strong>${colis.code}</strong></td>
                                <td><span class="badge-${isExpediteur ? 'expediteur' : 'destinataire'}">${isExpediteur ? 'Expéditeur' : 'Destinataire'}</span></td>
                                <td>${autrePartie}</td>
                                <td><span class="status-badge status-${colis.etat.toLowerCase().replace('_', '-')}">${colis.etat}</span></td>
                                <td>${new Date(colis.dateCreation).toLocaleDateString('fr-FR')}</td>
                                <td><strong>${GPMonde.Utils.formatPrice(colis.prix)} F</strong></td>
                            </tr>
                        `;
                    }).join('')}
                </tbody>
            </table>
        `;
    } else {
        colisDiv.innerHTML = '<p class="text-center text-muted">Aucun colis trouvé pour ce client.</p>';
    }
    
    GPMonde.Modal.show('detailsModal');
}

function editClient(clientId) {
    const client = clients.find(c => c.id === clientId);
    if (!client) return;
    
    document.getElementById('modalTitle').textContent = 'Modifier Client';
    document.getElementById('clientId').value = client.id;
    document.getElementById('nom').value = client.nom;
    document.getElementById('prenom').value = client.prenom;
    document.getElementById('telephone').value = client.telephone;
    document.getElementById('email').value = client.email || '';
    document.getElementById('adresse').value = client.adresse;
    
    GPMonde.Modal.show('clientModal');
}

function createColisForClient(clientId) {
    // Rediriger vers la page de création de colis avec le client pré-sélectionné
    window.location.href = `?page=colis&action=create&client=${clientId}`;
}

function applyFilters() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const typeFilter = document.getElementById('typeFilter').value;
    
    let filtered = clients.filter(client => {
        const matchesSearch = !searchTerm || 
            client.nom.toLowerCase().includes(searchTerm) ||
            client.prenom.toLowerCase().includes(searchTerm) ||
            client.telephone.toLowerCase().includes(searchTerm) ||
            (client.email && client.email.toLowerCase().includes(searchTerm));
            
        const matchesType = !typeFilter || client.type === typeFilter || 
            (typeFilter === 'expediteur' && (client.type === 'expediteur' || client.type === 'les-deux')) ||
            (typeFilter === 'destinataire' && (client.type === 'destinataire' || client.type === 'les-deux'));
        
        return matchesSearch && matchesType;
    });
    
    displayClients(filtered);
}

// Écouter les changements dans le champ de recherche
document.getElementById('searchInput')?.addEventListener('input', 
    GPMonde.Utils.debounce(applyFilters, 300)
);

// Initialiser au chargement
document.addEventListener('DOMContentLoaded', loadData);
</script>

<style>
.badge-expediteur {
    background: #d4edda;
    color: #155724;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
}

.badge-destinataire {
    background: #d1ecf1;
    color: #0c5460;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
}

.badge-les-deux {
    background: #fff3cd;
    color: #856404;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
}

.stats-badge {
    background: #f8f9fa;
    padding: 5px 10px;
    border-radius: 8px;
    border: 1px solid #dee2e6;
    font-weight: 600;
}

.client-info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.info-card {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #dee2e6;
}

.info-card h5 {
    margin: 0 0 15px 0;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 10px;
}

.info-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    padding-bottom: 10px;
    border-bottom: 1px solid #dee2e6;
}

.info-row:last-child {
    margin-bottom: 0;
    border-bottom: none;
}

.info-row .label {
    font-weight: 600;
    color: #555;
}

.info-row .value {
    color: #2c3e50;
}

.stats-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.stat-item {
    text-align: center;
    padding: 15px;
    background: white;
    border-radius: 6px;
}

.stat-number {
    display: block;
    font-size: 24px;
    font-weight: bold;
    color: #3498db;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 12px;
    color: #666;
    text-transform: uppercase;
}

.table-sm th,
.table-sm td {
    padding: 8px 12px;
    font-size: 13px;
}

@media (max-width: 768px) {
    .client-info-grid {
        grid-template-columns: 1fr;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
