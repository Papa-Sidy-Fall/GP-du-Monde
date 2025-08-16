<?php include 'includes/header.php'; ?>

<div class="app-container">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">Suivi des Colis</h1>
            <p class="page-subtitle">Interface gestionnaire pour le suivi des colis</p>
        </div>

        <!-- Recherche rapide -->
        <div class="card" style="margin-bottom: 20px;">
            <div class="card-header">
                <h3 class="card-title">Recherche de Colis</h3>
            </div>
            <div class="card-body">
                <form class="search-form" id="searchForm">
                    <div class="search-group">
                        <input type="text" class="search-input" id="searchCode" 
                               placeholder="Code du colis (ex: COL-123456)" required>
                        <button type="submit" class="btn-search">Rechercher</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Résultat de recherche -->
        <div id="searchResult" style="display: none;">
            <!-- Contenu chargé dynamiquement -->
        </div>

        <!-- Liste de tous les colis -->
        <div class="card">
            <div class="card-header">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h3 class="card-title">Tous les Colis</h3>
                    <div class="filters-controls">
                        <select id="statutFilter" onchange="applyFilters()">
                            <option value="">Tous les statuts</option>
                            <option value="EN_ATTENTE">En attente</option>
                            <option value="EN_COURS">En cours</option>
                            <option value="ARRIVE">Arrivé</option>
                            <option value="RECUPERE">Récupéré</option>
                            <option value="PERDU">Perdu</option>
                            <option value="ARCHIVE">Archivé</option>
                        </select>
                        <select id="cargaisonFilter" onchange="applyFilters()">
                            <option value="">Toutes les cargaisons</option>
                            <!-- Options chargées via JavaScript -->
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Code Colis</th>
                                <th>Expéditeur</th>
                                <th>Destinataire</th>
                                <th>Cargaison</th>
                                <th>État</th>
                                <th>Date Création</th>
                                <th>Prix</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="colis-table">
                            <!-- Données chargées via JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Modal détails colis -->
<div id="detailsModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Détails du Colis</h3>
            <span class="close" onclick="closeDetailsModal()">&times;</span>
        </div>
        <div id="colisDetails" style="padding: 25px;">
            <!-- Contenu chargé dynamiquement -->
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" onclick="closeDetailsModal()">Fermer</button>
            <button class="btn-primary" onclick="imprimerEtiquette()">
                <i class="fas fa-print"></i> Imprimer Étiquette
            </button>
        </div>
    </div>
</div>

<!-- Modal changement d'état -->
<div id="changeStateModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3>Changer l'État du Colis</h3>
            <span class="close" onclick="closeChangeStateModal()">&times;</span>
        </div>
        <form id="changeStateForm">
            <div style="padding: 25px;">
                <input type="hidden" id="currentColisCode">
                
                <div class="form-group">
                    <label for="nouvelEtat">Nouvel état *</label>
                    <select id="nouvelEtat" required>
                        <option value="">Sélectionnez un état</option>
                        <option value="EN_ATTENTE">En attente</option>
                        <option value="EN_COURS">En cours</option>
                        <option value="ARRIVE">Arrivé</option>
                        <option value="RECUPERE">Récupéré</option>
                        <option value="PERDU">Perdu</option>
                        <option value="ARCHIVE">Archivé</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="commentaire">Commentaire</label>
                    <textarea id="commentaire" rows="3" placeholder="Commentaire sur le changement d'état (optionnel)"></textarea>
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Note:</strong> Certains changements d'état peuvent être irréversibles. Vérifiez bien avant de confirmer.
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeChangeStateModal()">Annuler</button>
                <button type="submit" class="btn-primary">Confirmer</button>
            </div>
        </form>
    </div>
</div>

<script>
let allColis = [];
let cargaisons = [];

// Charger les données initiales
async function loadData() {
    try {
        // Charger les colis
        const colisResponse = await fetch('?page=api&action=get_colis');
        const colisResult = await colisResponse.json();
        if (colisResult.success) {
            allColis = colisResult.data;
            displayAllColis(allColis);
        }
        
        // Charger les cargaisons pour les filtres
        const cargaisonsResponse = await fetch('?page=api&action=get_cargaisons');
        const cargaisonsResult = await cargaisonsResponse.json();
        if (cargaisonsResult.success) {
            cargaisons = cargaisonsResult.data;
            populateCargaisonFilter();
        }
    } catch (error) {
        console.error('Erreur lors du chargement:', error);
        GPMonde.Notifications.error('Erreur lors du chargement des données');
    }
}

function displayAllColis(colisList) {
    const tbody = document.getElementById('colis-table');
    tbody.innerHTML = colisList.map(colis => `
        <tr class="colis-row" data-etat="${colis.etat}">
            <td>
                <strong>${colis.code}</strong>
                <br><small class="text-muted">${new Date(colis.dateCreation).toLocaleDateString('fr-FR')}</small>
            </td>
            <td>
                <div class="client-info">
                    <strong>${colis.expediteur.prenom} ${colis.expediteur.nom}</strong>
                    <br><small>${colis.expediteur.telephone}</small>
                </div>
            </td>
            <td>
                <div class="client-info">
                    <strong>${colis.destinataire.prenom} ${colis.destinataire.nom}</strong>
                    <br><small>${colis.destinataire.telephone}</small>
                </div>
            </td>
            <td>
                ${colis.cargaisonNumero ? 
                    `<span class="cargaison-badge">${colis.cargaisonNumero}</span>` : 
                    '<span class="text-muted">Non affecté</span>'
                }
            </td>
            <td>
                <span class="status-badge status-${colis.etat.toLowerCase().replace('_', '-')}">${colis.etat.replace('_', ' ')}</span>
            </td>
            <td>${new Date(colis.dateCreation).toLocaleDateString('fr-FR')}</td>
            <td><strong>${GPMonde.Utils.formatPrice(colis.prix)} F</strong></td>
            <td>
                <div class="action-buttons">
                    <button class="btn-sm btn-primary" onclick="viewColisDetails('${colis.code}')">Voir</button>
                    <button class="btn-sm btn-warning" onclick="changeColisState('${colis.code}')">État</button>
                    ${getActionButtons(colis)}
                </div>
            </td>
        </tr>
    `).join('');
}

function getActionButtons(colis) {
    let buttons = '';
    
    switch(colis.etat) {
        case 'ARRIVE':
            buttons += `<button class="btn-sm btn-success" onclick="markAsRecupere('${colis.code}')">Récupéré</button>`;
            break;
        case 'EN_ATTENTE':
        case 'EN_COURS':
            buttons += `<button class="btn-sm btn-danger" onclick="markAsPerdu('${colis.code}')">Perdu</button>`;
            break;
        case 'RECUPERE':
        case 'PERDU':
            buttons += `<button class="btn-sm btn-secondary" onclick="archiveColis('${colis.code}')">Archiver</button>`;
            break;
    }
    
    return buttons;
}

function populateCargaisonFilter() {
    const select = document.getElementById('cargaisonFilter');
    const options = cargaisons.map(cargaison => 
        `<option value="${cargaison.numero}">${cargaison.numero} (${cargaison.type})</option>`
    ).join('');
    
    select.innerHTML = '<option value="">Toutes les cargaisons</option>' + options;
}

// Recherche de colis
document.getElementById('searchForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const code = document.getElementById('searchCode').value.trim();
    const resultDiv = document.getElementById('searchResult');
    
    if (!code) return;
    
    try {
        const response = await fetch(`?page=api&action=track_colis&code=${encodeURIComponent(code)}`);
        const result = await response.json();
        
        if (result.success && result.data) {
            displaySearchResult(result.data);
            resultDiv.style.display = 'block';
        } else {
            GPMonde.Notifications.error('Colis non trouvé');
            resultDiv.style.display = 'none';
        }
    } catch (error) {
        GPMonde.Notifications.error('Erreur lors de la recherche');
    }
});

function displaySearchResult(colis) {
    const resultDiv = document.getElementById('searchResult');
    resultDiv.innerHTML = `
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Résultat de Recherche - ${colis.code}</h3>
            </div>
            <div class="card-body">
                <div class="tracking-result-grid">
                    <div class="info-section">
                        <h4><i class="fas fa-info-circle"></i> Informations Générales</h4>
                        <div class="info-table">
                            <div class="info-row">
                                <span class="label">Code:</span>
                                <span class="value"><strong>${colis.code}</strong></span>
                            </div>
                            <div class="info-row">
                                <span class="label">État:</span>
                                <span class="value">
                                    <span class="status-badge status-${colis.etat.toLowerCase().replace('_', '-')}">${colis.etat}</span>
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="label">Date de création:</span>
                                <span class="value">${colis.dateCreation}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="info-section">
                        <h4><i class="fas fa-users"></i> Parties</h4>
                        <div class="info-table">
                            <div class="info-row">
                                <span class="label">Expéditeur:</span>
                                <span class="value">${colis.expediteur}</span>
                            </div>
                            <div class="info-row">
                                <span class="label">Destinataire:</span>
                                <span class="value">${colis.destinataire}</span>
                            </div>
                        </div>
                    </div>
                    
                    ${colis.typeCargaison ? `
                    <div class="info-section">
                        <h4><i class="fas fa-ship"></i> Transport</h4>
                        <div class="info-table">
                            <div class="info-row">
                                <span class="label">Type:</span>
                                <span class="value">
                                    <i class="fas fa-${GPMonde.Utils.getTypeIcon(colis.typeCargaison)}"></i>
                                    ${GPMonde.Utils.capitalize(colis.typeCargaison)}
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="label">Origine:</span>
                                <span class="value">${colis.origine}</span>
                            </div>
                            <div class="info-row">
                                <span class="label">Destination:</span>
                                <span class="value">${colis.destination}</span>
                            </div>
                            <div class="info-row">
                                <span class="label">Distance:</span>
                                <span class="value">${colis.distance} km</span>
                            </div>
                        </div>
                    </div>
                    ` : ''}
                </div>
                
                <div style="margin-top: 20px; text-align: center;">
                    <button class="btn-primary" onclick="viewColisDetails('${colis.code}')">Voir les détails complets</button>
                    <button class="btn-secondary" onclick="document.getElementById('searchResult').style.display='none'">Masquer</button>
                </div>
            </div>
        </div>
    `;
}

async function viewColisDetails(code) {
    const colis = allColis.find(c => c.code === code);
    if (!colis) return;
    
    const detailsDiv = document.getElementById('colisDetails');
    detailsDiv.innerHTML = `
        <div class="colis-details-grid">
            <div class="details-section">
                <h4><i class="fas fa-package"></i> Informations du Colis</h4>
                <div class="details-table">
                    <div class="detail-row">
                        <span class="label">Code:</span>
                        <span class="value"><strong>${colis.code}</strong></span>
                    </div>
                    <div class="detail-row">
                        <span class="label">État actuel:</span>
                        <span class="value">
                            <span class="status-badge status-${colis.etat.toLowerCase().replace('_', '-')}">${colis.etat}</span>
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Prix:</span>
                        <span class="value"><strong>${GPMonde.Utils.formatPrice(colis.prix)} F</strong></span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Date de création:</span>
                        <span class="value">${new Date(colis.dateCreation).toLocaleDateString('fr-FR')}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Cargaison:</span>
                        <span class="value">${colis.cargaisonNumero || 'Non affecté'}</span>
                    </div>
                </div>
            </div>
            
            <div class="details-section">
                <h4><i class="fas fa-user"></i> Expéditeur</h4>
                <div class="details-table">
                    <div class="detail-row">
                        <span class="label">Nom:</span>
                        <span class="value">${colis.expediteur.prenom} ${colis.expediteur.nom}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Téléphone:</span>
                        <span class="value"><a href="tel:${colis.expediteur.telephone}">${colis.expediteur.telephone}</a></span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Email:</span>
                        <span class="value">${colis.expediteur.email ? `<a href="mailto:${colis.expediteur.email}">${colis.expediteur.email}</a>` : 'Non renseigné'}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Adresse:</span>
                        <span class="value">${colis.expediteur.adresse}</span>
                    </div>
                </div>
            </div>
            
            <div class="details-section">
                <h4><i class="fas fa-user-check"></i> Destinataire</h4>
                <div class="details-table">
                    <div class="detail-row">
                        <span class="label">Nom:</span>
                        <span class="value">${colis.destinataire.prenom} ${colis.destinataire.nom}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Téléphone:</span>
                        <span class="value"><a href="tel:${colis.destinataire.telephone}">${colis.destinataire.telephone}</a></span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Email:</span>
                        <span class="value">${colis.destinataire.email ? `<a href="mailto:${colis.destinataire.email}">${colis.destinataire.email}</a>` : 'Non renseigné'}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Adresse:</span>
                        <span class="value">${colis.destinataire.adresse}</span>
                    </div>
                </div>
            </div>
            
            <div class="details-section">
                <h4><i class="fas fa-box-open"></i> Contenu</h4>
                <div class="produits-list">
                    ${colis.produits.map(produit => `
                        <div class="produit-item">
                            <div class="produit-header">
                                <i class="fas fa-${GPMonde.Utils.getTypeIcon(produit.type)}"></i>
                                <strong>${produit.libelle}</strong>
                                <span class="produit-type">${GPMonde.Utils.capitalize(produit.type)}</span>
                            </div>
                            <div class="produit-details">
                                <span>Poids: ${produit.poids} kg</span>
                                ${produit.degreToxicite ? `<span>Toxicité: ${produit.degreToxicite}/9</span>` : ''}
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        </div>
    `;
    
    GPMonde.Modal.show('detailsModal');
}

function closeDetailsModal() {
    GPMonde.Modal.hide('detailsModal');
}

function closeChangeStateModal() {
    GPMonde.Modal.hide('changeStateModal');
}

function changeColisState(code) {
    document.getElementById('currentColisCode').value = code;
    document.getElementById('nouvelEtat').value = '';
    document.getElementById('commentaire').value = '';
    GPMonde.Modal.show('changeStateModal');
}

document.getElementById('changeStateForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const code = document.getElementById('currentColisCode').value;
    const nouvelEtat = document.getElementById('nouvelEtat').value;
    const commentaire = document.getElementById('commentaire').value;
    
    if (!nouvelEtat) return;
    
    try {
        const response = await fetch('?page=api&action=change_state_colis', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ code, etat: nouvelEtat, commentaire })
        });
        
        const result = await response.json();
        
        if (result.success) {
            GPMonde.Notifications.success('État du colis modifié avec succès');
            closeChangeStateModal();
            loadData(); // Recharger les données
        } else {
            GPMonde.Notifications.error(result.message || 'Erreur lors de la modification');
        }
    } catch (error) {
        GPMonde.Notifications.error('Erreur lors de la modification');
    }
});

async function markAsRecupere(code) {
    if (!confirm('Marquer ce colis comme récupéré ?')) return;
    
    try {
        const response = await fetch('?page=api&action=mark_recupere', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ code })
        });
        
        const result = await response.json();
        
        if (result.success) {
            GPMonde.Notifications.success('Colis marqué comme récupéré');
            loadData();
        } else {
            GPMonde.Notifications.error(result.message);
        }
    } catch (error) {
        GPMonde.Notifications.error('Erreur lors de la mise à jour');
    }
}

async function markAsPerdu(code) {
    if (!confirm('Marquer ce colis comme perdu ? Cette action ne peut être annulée.')) return;
    
    try {
        const response = await fetch('?page=api&action=mark_perdu', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ code })
        });
        
        const result = await response.json();
        
        if (result.success) {
            GPMonde.Notifications.warning('Colis marqué comme perdu');
            loadData();
        } else {
            GPMonde.Notifications.error(result.message);
        }
    } catch (error) {
        GPMonde.Notifications.error('Erreur lors de la mise à jour');
    }
}

function applyFilters() {
    const statutFilter = document.getElementById('statutFilter').value;
    const cargaisonFilter = document.getElementById('cargaisonFilter').value;
    
    let filtered = allColis.filter(colis => {
        const matchesStatut = !statutFilter || colis.etat === statutFilter;
        const matchesCargaison = !cargaisonFilter || colis.cargaisonNumero === cargaisonFilter;
        
        return matchesStatut && matchesCargaison;
    });
    
    displayAllColis(filtered);
}

function imprimerEtiquette() {
    // Implémenter l'impression d'étiquette
    window.print();
}

// Initialiser au chargement
document.addEventListener('DOMContentLoaded', loadData);
</script>

<style>
.search-form {
    margin: 0;
}

.search-group {
    display: flex;
    gap: 15px;
    max-width: 600px;
}

.search-input {
    flex: 1;
    padding: 12px 15px;
    border: 2px solid #e1e8ed;
    border-radius: 8px;
    font-size: 14px;
}

.search-input:focus {
    outline: none;
    border-color: #3498db;
}

.btn-search {
    padding: 12px 25px;
    background: #3498db;
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
}

.btn-search:hover {
    background: #2980b9;
}

.filters-controls {
    display: flex;
    gap: 15px;
}

.filters-controls select {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.tracking-result-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.info-section {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    border-left: 4px solid #3498db;
}

.info-section h4 {
    margin: 0 0 15px 0;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 10px;
}

.info-table {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #dee2e6;
}

.info-row:last-child {
    border-bottom: none;
}

.info-row .label {
    font-weight: 600;
    color: #555;
    min-width: 100px;
}

.info-row .value {
    color: #2c3e50;
    text-align: right;
}

.client-info {
    line-height: 1.4;
}

.cargaison-badge {
    background: #e9ecef;
    color: #495057;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.colis-details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 25px;
}

.details-section {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #dee2e6;
}

.details-section h4 {
    margin: 0 0 15px 0;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 10px;
}

.details-table {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 8px 0;
    border-bottom: 1px solid #dee2e6;
}

.detail-row:last-child {
    border-bottom: none;
}

.detail-row .label {
    font-weight: 600;
    color: #555;
    min-width: 90px;
    flex-shrink: 0;
}

.detail-row .value {
    color: #2c3e50;
    text-align: right;
    flex: 1;
    margin-left: 15px;
}

.produits-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.produit-item {
    background: white;
    padding: 15px;
    border-radius: 6px;
    border: 1px solid #e1e8ed;
}

.produit-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 8px;
}

.produit-header strong {
    flex: 1;
    color: #2c3e50;
}

.produit-type {
    background: #e9ecef;
    color: #495057;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.produit-details {
    display: flex;
    gap: 15px;
    font-size: 13px;
    color: #666;
}

.alert {
    padding: 12px 15px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 15px 0;
}

.alert-info {
    background: #d1ecf1;
    color: #0c5460;
    border: 1px solid #bee5eb;
}

@media (max-width: 768px) {
    .search-group {
        flex-direction: column;
        max-width: 100%;
    }
    
    .filters-controls {
        flex-direction: column;
        gap: 10px;
    }
    
    .tracking-result-grid,
    .colis-details-grid {
        grid-template-columns: 1fr;
    }
    
    .info-row,
    .detail-row {
        flex-direction: column;
        align-items: flex-start;
        text-align: left;
    }
    
    .info-row .value,
    .detail-row .value {
        text-align: left;
        margin-left: 0;
        margin-top: 5px;
    }
    
    .produit-header {
        flex-wrap: wrap;
    }
    
    .produit-details {
        flex-direction: column;
        gap: 5px;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
