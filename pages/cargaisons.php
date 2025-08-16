<?php include 'includes/header.php'; ?>

<div class="app-container">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
        <div class="page-header">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h1 class="page-title">Gestion des Cargaisons</h1>
                    <p class="page-subtitle">Créer, modifier et gérer vos cargaisons</p>
                </div>
                <button class="btn-primary" onclick="showCreateCargaisonModal()">
                    <i class="fas fa-plus"></i> Nouvelle Cargaison
                </button>
            </div>
        </div>

        <!-- Filtres de recherche -->
        <div class="card" style="margin-bottom: 20px;">
            <div class="card-body">
                <div class="filters-row">
                    <div class="filter-group">
                        <label>Recherche:</label>
                        <input type="text" id="searchInput" placeholder="Numéro, lieu...">
                    </div>
                    <div class="filter-group">
                        <label>Type:</label>
                        <select id="typeFilter">
                            <option value="">Tous</option>
                            <option value="maritime">Maritime</option>
                            <option value="aerienne">Aérienne</option>
                            <option value="routiere">Routière</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Statut:</label>
                        <select id="statutFilter">
                            <option value="">Tous</option>
                            <option value="OUVERTE">Ouverte</option>
                            <option value="FERMEE">Fermée</option>
                        </select>
                    </div>
                    <button class="btn-secondary" onclick="applyFilters()">Filtrer</button>
                </div>
            </div>
        </div>

        <!-- Liste des cargaisons -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Liste des Cargaisons</h3>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>N° Cargaison</th>
                            <th>Type</th>
                            <th>Départ → Arrivée</th>
                            <th>Distance</th>
                            <th>Capacité</th>
                            <th>Prix Total</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="cargaisons-table">
                        <!-- Données chargées via JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<!-- Modal création/modification cargaison -->
<div id="cargaisonModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Nouvelle Cargaison</h3>
            <span class="close" onclick="closeCargaisonModal()">&times;</span>
        </div>
        <form id="cargaisonForm">
            <div class="form-row">
                <div class="form-group">
                    <label for="typeCargaison">Type de cargaison *</label>
                    <select id="typeCargaison" required>
                        <option value="">Sélectionnez un type</option>
                        <option value="maritime">Maritime</option>
                        <option value="aerienne">Aérienne</option>
                        <option value="routiere">Routière</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="poidsMax">Poids maximum (kg) *</label>
                    <input type="number" id="poidsMax" min="1" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="lieuDepart">Lieu de départ *</label>
                    <select id="lieuDepart" required>
                        <option value="">Sélectionnez un lieu</option>
                        <!-- Options chargées via JavaScript -->
                    </select>
                </div>
                <div class="form-group">
                    <label for="lieuArrivee">Lieu d'arrivée *</label>
                    <select id="lieuArrivee" required>
                        <option value="">Sélectionnez un lieu</option>
                        <!-- Options chargées via JavaScript -->
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="distance">Distance (km)</label>
                <input type="number" id="distance" min="1" readonly>
                <small>La distance sera calculée automatiquement</small>
            </div>

            <!-- Aperçu de la route -->
            <div id="routePreview" style="height: 250px; margin: 15px 0; display: none;"></div>
            
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeCargaisonModal()">Annuler</button>
                <button type="submit" class="btn-primary">Créer Cargaison</button>
            </div>
        </form>
    </div>
</div>

<script>
let cargaisons = [];
let villes = [];
let routeMap = null;

// Charger les données initiales
async function loadData() {
    try {
        // Charger les cargaisons
        const cargaisonsResponse = await fetch('?page=api&action=get_cargaisons');
        const cargaisonsResult = await cargaisonsResponse.json();
        if (cargaisonsResult.success) {
            cargaisons = cargaisonsResult.data;
            displayCargaisons(cargaisons);
        }
        
        // Charger les villes disponibles
        const villesResponse = await fetch('?page=api&action=get_villes');
        const villesResult = await villesResponse.json();
        if (villesResult.success) {
            villes = villesResult.data;
            populateVilleSelects();
        }
    } catch (error) {
        console.error('Erreur lors du chargement:', error);
    }
}

function displayCargaisons(data) {
    const tbody = document.getElementById('cargaisons-table');
    tbody.innerHTML = data.map(cargaison => `
        <tr>
            <td><strong>${cargaison.numero}</strong></td>
            <td><i class="fas fa-${getTypeIcon(cargaison.type)}"></i> ${capitalize(cargaison.type)}</td>
            <td>
                <div>${cargaison.lieuDepart.nom}</div>
                <div style="font-size: 0.8em; color: #666;">↓</div>
                <div>${cargaison.lieuArrivee.nom}</div>
            </td>
            <td>${cargaison.distance} km</td>
            <td>${cargaison.nbProduits || 0} / 10</td>
            <td>${formatPrice(cargaison.prixTotal)} F</td>
            <td>
                <span class="status-badge status-${cargaison.statut.toLowerCase()}">${cargaison.statut}</span>
                <br>
                <small class="status-badge status-${cargaison.etat.toLowerCase().replace('_', '-')}">${cargaison.etat}</small>
            </td>
            <td>
                <button class="btn-sm btn-primary" onclick="viewCargaison('${cargaison.numero}')">Voir</button>
                ${cargaison.statut === 'OUVERTE' ? 
                    `<button class="btn-sm btn-warning" onclick="closeCargaison('${cargaison.numero}')">Fermer</button>` :
                    (cargaison.etat === 'EN_ATTENTE' ? 
                        `<button class="btn-sm btn-info" onclick="reopenCargaison('${cargaison.numero}')">Rouvrir</button>` : '')
                }
                <button class="btn-sm btn-secondary" onclick="showRoute('${cargaison.numero}')">Carte</button>
            </td>
        </tr>
    `).join('');
}

function populateVilleSelects() {
    const departSelect = document.getElementById('lieuDepart');
    const arriveeSelect = document.getElementById('lieuArrivee');
    
    const options = villes.map(ville => 
        `<option value="${ville.nom}" data-lat="${ville.latitude}" data-lng="${ville.longitude}">${ville.nom}</option>`
    ).join('');
    
    departSelect.innerHTML = '<option value="">Sélectionnez un lieu</option>' + options;
    arriveeSelect.innerHTML = '<option value="">Sélectionnez un lieu</option>' + options;
}

function showCreateCargaisonModal() {
    document.getElementById('modalTitle').textContent = 'Nouvelle Cargaison';
    document.getElementById('cargaisonForm').reset();
    document.getElementById('cargaisonModal').style.display = 'block';
    
    // Détruire la carte existante
    if (routeMap) {
        routeMap.remove();
        routeMap = null;
    }
    document.getElementById('routePreview').style.display = 'none';
}

function closeCargaisonModal() {
    document.getElementById('cargaisonModal').style.display = 'none';
    if (routeMap) {
        routeMap.remove();
        routeMap = null;
    }
}

// Calculer la distance quand les lieux changent
document.getElementById('lieuDepart').addEventListener('change', calculateDistance);
document.getElementById('lieuArrivee').addEventListener('change', calculateDistance);
document.getElementById('typeCargaison').addEventListener('change', updateRoutePreview);

async function calculateDistance() {
    const departSelect = document.getElementById('lieuDepart');
    const arriveeSelect = document.getElementById('lieuArrivee');
    const distanceInput = document.getElementById('distance');
    
    if (departSelect.value && arriveeSelect.value) {
        const departOption = departSelect.selectedOptions[0];
        const arriveeOption = arriveeSelect.selectedOptions[0];
        
        const lat1 = parseFloat(departOption.dataset.lat);
        const lng1 = parseFloat(departOption.dataset.lng);
        const lat2 = parseFloat(arriveeOption.dataset.lat);
        const lng2 = parseFloat(arriveeOption.dataset.lng);
        
        // Calcul de la distance (formule haversine)
        const distance = calculateHaversineDistance(lat1, lng1, lat2, lng2);
        distanceInput.value = Math.round(distance);
        
        updateRoutePreview();
    }
}

function calculateHaversineDistance(lat1, lon1, lat2, lon2) {
    const R = 6371; // Rayon de la Terre en km
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
              Math.sin(dLon/2) * Math.sin(dLon/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R * c;
}

function updateRoutePreview() {
    const depart = document.getElementById('lieuDepart').value;
    const arrivee = document.getElementById('lieuArrivee').value;
    const type = document.getElementById('typeCargaison').value;
    
    if (depart && arrivee && type) {
        const previewDiv = document.getElementById('routePreview');
        previewDiv.style.display = 'block';
        
        // Détruire la carte existante
        if (routeMap) {
            routeMap.remove();
        }
        
        // Créer nouvelle carte
        const departOption = document.querySelector(`#lieuDepart option[value="${depart}"]`);
        const arriveeOption = document.querySelector(`#lieuArrivee option[value="${arrivee}"]`);
        
        const lat1 = parseFloat(departOption.dataset.lat);
        const lng1 = parseFloat(departOption.dataset.lng);
        const lat2 = parseFloat(arriveeOption.dataset.lat);
        const lng2 = parseFloat(arriveeOption.dataset.lng);
        
        routeMap = L.map('routePreview').setView([lat1, lng1], 4);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(routeMap);
        
        // Marqueurs
        L.marker([lat1, lng1]).addTo(routeMap).bindPopup(depart);
        L.marker([lat2, lng2]).addTo(routeMap).bindPopup(arrivee);
        
        // Route (ligne droite pour la prévisualisation)
        const line = L.polyline([[lat1, lng1], [lat2, lng2]], {
            color: getRouteColor(type),
            weight: 4,
            opacity: 0.8
        }).addTo(routeMap);
        
        routeMap.fitBounds(line.getBounds(), {padding: [20, 20]});
    }
}

document.getElementById('cargaisonForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = {
        type: document.getElementById('typeCargaison').value,
        poidsMax: parseInt(document.getElementById('poidsMax').value),
        lieuDepart: document.getElementById('lieuDepart').value,
        lieuArrivee: document.getElementById('lieuArrivee').value,
        distance: parseInt(document.getElementById('distance').value)
    };
    
    try {
        const response = await fetch('?page=api&action=create_cargaison', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            closeCargaisonModal();
            loadData();
            alert('Cargaison créée avec succès !');
        } else {
            alert(result.message || 'Erreur lors de la création');
        }
    } catch (error) {
        alert('Erreur lors de la création de la cargaison');
    }
});

function getTypeIcon(type) {
    switch(type) {
        case 'maritime': return 'ship';
        case 'aerienne': return 'plane';
        case 'routiere': return 'truck';
        default: return 'box';
    }
}

function getRouteColor(type) {
    switch(type) {
        case 'maritime': return 'blue';
        case 'aerienne': return 'red';
        case 'routiere': return 'green';
        default: return 'gray';
    }
}

function capitalize(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function formatPrice(price) {
    return new Intl.NumberFormat('fr-FR').format(price);
}

function applyFilters() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const typeFilter = document.getElementById('typeFilter').value;
    const statutFilter = document.getElementById('statutFilter').value;
    
    let filtered = cargaisons.filter(cargaison => {
        const matchesSearch = !searchTerm || 
            cargaison.numero.toLowerCase().includes(searchTerm) ||
            cargaison.lieuDepart.nom.toLowerCase().includes(searchTerm) ||
            cargaison.lieuArrivee.nom.toLowerCase().includes(searchTerm);
            
        const matchesType = !typeFilter || cargaison.type === typeFilter;
        const matchesStatut = !statutFilter || cargaison.statut === statutFilter;
        
        return matchesSearch && matchesType && matchesStatut;
    });
    
    displayCargaisons(filtered);
}

async function closeCargaison(numero) {
    if (confirm('Êtes-vous sûr de vouloir fermer cette cargaison ?')) {
        try {
            const response = await fetch('?page=api&action=close_cargaison', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ numero })
            });
            
            const result = await response.json();
            if (result.success) {
                loadData();
                alert('Cargaison fermée avec succès');
            } else {
                alert(result.message);
            }
        } catch (error) {
            alert('Erreur lors de la fermeture');
        }
    }
}

async function reopenCargaison(numero) {
    if (confirm('Rouvrir cette cargaison doublera le coût total. Continuer ?')) {
        try {
            const response = await fetch('?page=api&action=reopen_cargaison', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ numero })
            });
            
            const result = await response.json();
            if (result.success) {
                loadData();
                alert('Cargaison rouverte avec succès');
            } else {
                alert(result.message);
            }
        } catch (error) {
            alert('Erreur lors de la réouverture');
        }
    }
}

function viewCargaison(numero) {
    window.location.href = `?page=cargaisons&action=view&numero=${numero}`;
}

function showRoute(numero) {
    window.open(`?page=api&action=show_route&numero=${numero}`, '_blank');
}

// Initialiser au chargement
document.addEventListener('DOMContentLoaded', loadData);
</script>

<style>
.filters-row {
    display: flex;
    gap: 20px;
    align-items: end;
    flex-wrap: wrap;
}

.filter-group {
    display: flex;
    flex-direction: column;
    min-width: 150px;
}

.filter-group label {
    margin-bottom: 5px;
    font-weight: 500;
}

.filter-group input,
.filter-group select {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.modal {
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: white;
    margin: 5% auto;
    padding: 0;
    border-radius: 10px;
    width: 80%;
    max-width: 800px;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    padding: 20px 25px;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    color: #2c3e50;
}

.close {
    color: #aaa;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close:hover {
    color: #000;
}

.modal form {
    padding: 25px;
}

.form-row {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
}

.form-group {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.form-group label {
    margin-bottom: 8px;
    font-weight: 500;
    color: #555;
}

.form-group input,
.form-group select {
    padding: 12px 15px;
    border: 2px solid #e1e8ed;
    border-radius: 8px;
    font-size: 14px;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: #3498db;
}

.form-group small {
    margin-top: 5px;
    color: #666;
    font-size: 12px;
}

.modal-footer {
    padding: 20px 25px;
    border-top: 1px solid #eee;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.btn-sm {
    padding: 4px 8px;
    font-size: 12px;
    margin-right: 5px;
}

.status-ouverte { background: #d4edda; color: #155724; }
.status-fermee { background: #f8d7da; color: #721c24; }
</style>

<?php include 'includes/footer.php'; ?>
