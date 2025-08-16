<?php include 'includes/header.php'; ?>

<div class="app-container">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
        <div class="page-header">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h1 class="page-title">Gestion des Colis</h1>
                    <p class="page-subtitle">Enregistrer et gérer les colis des clients</p>
                </div>
                <button class="btn-primary" onclick="showCreateColisModal()">
                    <i class="fas fa-plus"></i> Nouveau Colis
                </button>
            </div>
        </div>

        <!-- Filtres de recherche -->
        <div class="card" style="margin-bottom: 20px;">
            <div class="card-body">
                <div class="filters-row">
                    <div class="filter-group">
                        <label>Code colis:</label>
                        <input type="text" id="codeSearch" placeholder="COL-123456">
                    </div>
                    <div class="filter-group">
                        <label>Client:</label>
                        <input type="text" id="clientSearch" placeholder="Nom du client">
                    </div>
                    <div class="filter-group">
                        <label>État:</label>
                        <select id="etatFilter">
                            <option value="">Tous</option>
                            <option value="EN_ATTENTE">En attente</option>
                            <option value="EN_COURS">En cours</option>
                            <option value="ARRIVE">Arrivé</option>
                            <option value="RECUPERE">Récupéré</option>
                            <option value="PERDU">Perdu</option>
                        </select>
                    </div>
                    <button class="btn-secondary" onclick="applyFilters()">Filtrer</button>
                </div>
            </div>
        </div>

        <!-- Liste des colis -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Liste des Colis</h3>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Code Colis</th>
                            <th>Expéditeur</th>
                            <th>Destinataire</th>
                            <th>Produits</th>
                            <th>Cargaison</th>
                            <th>Prix</th>
                            <th>État</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="colis-table">
                        <!-- Données chargées via JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<!-- Modal création colis -->
<div id="colisModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Nouveau Colis</h3>
            <span class="close" onclick="closeColisModal()">&times;</span>
        </div>
        <form id="colisForm">
            <div class="form-section">
                <h4><i class="fas fa-user"></i> Informations Expéditeur</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label for="expediteurNom">Nom *</label>
                        <input type="text" id="expediteurNom" required>
                    </div>
                    <div class="form-group">
                        <label for="expediteurPrenom">Prénom *</label>
                        <input type="text" id="expediteurPrenom" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="expediteurTelephone">Téléphone *</label>
                        <input type="tel" id="expediteurTelephone" required>
                    </div>
                    <div class="form-group">
                        <label for="expediteurEmail">Email</label>
                        <input type="email" id="expediteurEmail">
                    </div>
                </div>
                <div class="form-group">
                    <label for="expediteurAdresse">Adresse *</label>
                    <textarea id="expediteurAdresse" rows="2" required></textarea>
                </div>
            </div>

            <div class="form-section">
                <h4><i class="fas fa-user-check"></i> Informations Destinataire</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label for="destinataireNom">Nom *</label>
                        <input type="text" id="destinataireNom" required>
                    </div>
                    <div class="form-group">
                        <label for="destinatairePrenom">Prénom *</label>
                        <input type="text" id="destinatairePrenom" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="destinataireTelephone">Téléphone *</label>
                        <input type="tel" id="destinataireTelephone" required>
                    </div>
                    <div class="form-group">
                        <label for="destinataireEmail">Email</label>
                        <input type="email" id="destinataireEmail">
                    </div>
                </div>
                <div class="form-group">
                    <label for="destinataireAdresse">Adresse *</label>
                    <textarea id="destinataireAdresse" rows="2" required></textarea>
                </div>
            </div>

            <div class="form-section">
                <h4><i class="fas fa-box"></i> Produits du Colis</h4>
                <div id="produits-container">
                    <div class="produit-row">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Type de produit *</label>
                                <select class="produit-type" required>
                                    <option value="">Sélectionnez</option>
                                    <option value="alimentaire">Alimentaire</option>
                                    <option value="chimique">Chimique</option>
                                    <option value="fragile">Fragile</option>
                                    <option value="incassable">Incassable</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Libellé *</label>
                                <input type="text" class="produit-libelle" required>
                            </div>
                            <div class="form-group">
                                <label>Poids (kg) *</label>
                                <input type="number" class="produit-poids" min="0.1" step="0.1" required>
                            </div>
                            <div class="form-group toxicite-group" style="display: none;">
                                <label>Toxicité (1-9)</label>
                                <input type="number" class="produit-toxicite" min="1" max="9">
                            </div>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-secondary btn-sm" onclick="ajouterProduit()">
                    <i class="fas fa-plus"></i> Ajouter un produit
                </button>
            </div>

            <div class="form-section">
                <h4><i class="fas fa-ship"></i> Affectation à une Cargaison</h4>
                <div class="form-group">
                    <label for="cargaisonSelect">Cargaison (optionnel)</label>
                    <select id="cargaisonSelect">
                        <option value="">Affecter plus tard</option>
                        <!-- Options chargées via JavaScript -->
                    </select>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeColisModal()">Annuler</button>
                <button type="submit" class="btn-primary">Enregistrer Colis</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal d'information sur le code généré -->
<div id="codeModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3>Colis Enregistré ✅</h3>
            <span class="close" onclick="closeCodeModal()">&times;</span>
        </div>
        <div style="padding: 25px; text-align: center;">
            <div style="background: #d4edda; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                <p style="margin: 0; font-size: 14px; color: #155724;">Code de suivi généré :</p>
                <h2 id="generatedCode" style="margin: 10px 0; color: #155724; font-family: monospace;"></h2>
            </div>
            <p>Ce code doit être communiqué au destinataire pour le suivi du colis.</p>
            <button class="btn-primary" onclick="imprimerRecu()">Imprimer le Reçu</button>
            <button class="btn-secondary" onclick="closeCodeModal()">Fermer</button>
        </div>
    </div>
</div>

<script>
let colis = [];
let cargaisonsDisponibles = [];

// Charger les données initiales
async function loadData() {
    try {
        // Charger les colis
        const colisResponse = await fetch('?page=api&action=get_colis');
        const colisResult = await colisResponse.json();
        if (colisResult.success) {
            colis = colisResult.data;
            displayColis(colis);
        }
        
        // Charger les cargaisons ouvertes
        const cargaisonsResponse = await fetch('?page=api&action=get_cargaisons_ouvertes');
        const cargaisonsResult = await cargaisonsResponse.json();
        if (cargaisonsResult.success) {
            cargaisonsDisponibles = cargaisonsResult.data;
            populateCargaisonSelect();
        }
    } catch (error) {
        console.error('Erreur lors du chargement:', error);
    }
}

function displayColis(data) {
    const tbody = document.getElementById('colis-table');
    tbody.innerHTML = data.map(colis => `
        <tr>
            <td><strong>${colis.code}</strong><br><small>${new Date(colis.dateCreation).toLocaleDateString('fr-FR')}</small></td>
            <td>
                <strong>${colis.expediteur.prenom} ${colis.expediteur.nom}</strong><br>
                <small>${colis.expediteur.telephone}</small>
            </td>
            <td>
                <strong>${colis.destinataire.prenom} ${colis.destinataire.nom}</strong><br>
                <small>${colis.destinataire.telephone}</small>
            </td>
            <td>
                <small>${colis.produits.map(p => `${p.type}: ${p.libelle} (${p.poids}kg)`).join('<br>')}</small>
            </td>
            <td>${colis.cargaisonNumero || '<span style="color: #999;">Non affecté</span>'}</td>
            <td><strong>${formatPrice(colis.prix)} F</strong></td>
            <td><span class="status-badge status-${colis.etat.toLowerCase().replace('_', '-')}">${colis.etat.replace('_', ' ')}</span></td>
            <td>
                <div class="action-buttons">
                    <button class="btn-sm btn-primary" onclick="voirColis('${colis.code}')">Voir</button>
                    ${colis.cargaisonNumero ? '' : `<button class="btn-sm btn-info" onclick="affecterColis('${colis.code}')">Affecter</button>`}
                    ${colis.etat === 'ARRIVE' ? `<button class="btn-sm btn-success" onclick="marquerRecupere('${colis.code}')">Récupéré</button>` : ''}
                    <button class="btn-sm btn-warning" onclick="changerEtat('${colis.code}')">État</button>
                    ${colis.etat === 'EN_ATTENTE' ? `<button class="btn-sm btn-danger" onclick="annulerColis('${colis.code}')">Annuler</button>` : ''}
                </div>
            </td>
        </tr>
    `).join('');
}

function populateCargaisonSelect() {
    const select = document.getElementById('cargaisonSelect');
    const options = cargaisonsDisponibles.map(cargaison => 
        `<option value="${cargaison.numero}">${cargaison.numero} - ${cargaison.type} (${cargaison.lieuDepart.nom} → ${cargaison.lieuArrivee.nom})</option>`
    ).join('');
    
    select.innerHTML = '<option value="">Affecter plus tard</option>' + options;
}

function showCreateColisModal() {
    document.getElementById('modalTitle').textContent = 'Nouveau Colis';
    document.getElementById('colisForm').reset();
    document.getElementById('colisModal').style.display = 'block';
    
    // Réinitialiser les produits à un seul
    const container = document.getElementById('produits-container');
    container.innerHTML = `
        <div class="produit-row">
            <div class="form-row">
                <div class="form-group">
                    <label>Type de produit *</label>
                    <select class="produit-type" required>
                        <option value="">Sélectionnez</option>
                        <option value="alimentaire">Alimentaire</option>
                        <option value="chimique">Chimique</option>
                        <option value="fragile">Fragile</option>
                        <option value="incassable">Incassable</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Libellé *</label>
                    <input type="text" class="produit-libelle" required>
                </div>
                <div class="form-group">
                    <label>Poids (kg) *</label>
                    <input type="number" class="produit-poids" min="0.1" step="0.1" required>
                </div>
                <div class="form-group toxicite-group" style="display: none;">
                    <label>Toxicité (1-9)</label>
                    <input type="number" class="produit-toxicite" min="1" max="9">
                </div>
            </div>
        </div>
    `;
    
    // Réattacher les événements
    attachProduitEvents();
}

function closeColisModal() {
    document.getElementById('colisModal').style.display = 'none';
}

function closeCodeModal() {
    document.getElementById('codeModal').style.display = 'none';
}

function ajouterProduit() {
    const container = document.getElementById('produits-container');
    const newRow = document.createElement('div');
    newRow.className = 'produit-row';
    newRow.innerHTML = `
        <div class="form-row">
            <div class="form-group">
                <label>Type de produit *</label>
                <select class="produit-type" required>
                    <option value="">Sélectionnez</option>
                    <option value="alimentaire">Alimentaire</option>
                    <option value="chimique">Chimique</option>
                    <option value="fragile">Fragile</option>
                    <option value="incassable">Incassable</option>
                </select>
            </div>
            <div class="form-group">
                <label>Libellé *</label>
                <input type="text" class="produit-libelle" required>
            </div>
            <div class="form-group">
                <label>Poids (kg) *</label>
                <input type="number" class="produit-poids" min="0.1" step="0.1" required>
            </div>
            <div class="form-group toxicite-group" style="display: none;">
                <label>Toxicité (1-9)</label>
                <input type="number" class="produit-toxicite" min="1" max="9">
            </div>
            <div class="form-group">
                <button type="button" class="btn-danger btn-sm" onclick="supprimerProduit(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;
    
    container.appendChild(newRow);
    attachProduitEvents();
}

function supprimerProduit(button) {
    const produitRow = button.closest('.produit-row');
    produitRow.remove();
}

function attachProduitEvents() {
    document.querySelectorAll('.produit-type').forEach(select => {
        select.addEventListener('change', function() {
            const toxiciteGroup = this.closest('.produit-row').querySelector('.toxicite-group');
            if (this.value === 'chimique') {
                toxiciteGroup.style.display = 'block';
                toxiciteGroup.querySelector('.produit-toxicite').required = true;
            } else {
                toxiciteGroup.style.display = 'none';
                toxiciteGroup.querySelector('.produit-toxicite').required = false;
            }
        });
    });
}

document.getElementById('colisForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    // Récupérer les données du formulaire
    const expediteur = {
        nom: document.getElementById('expediteurNom').value,
        prenom: document.getElementById('expediteurPrenom').value,
        telephone: document.getElementById('expediteurTelephone').value,
        email: document.getElementById('expediteurEmail').value,
        adresse: document.getElementById('expediteurAdresse').value
    };
    
    const destinataire = {
        nom: document.getElementById('destinataireNom').value,
        prenom: document.getElementById('destinatairePrenom').value,
        telephone: document.getElementById('destinataireTelephone').value,
        email: document.getElementById('destinataireEmail').value,
        adresse: document.getElementById('destinataireAdresse').value
    };
    
    const produits = [];
    document.querySelectorAll('.produit-row').forEach(row => {
        const type = row.querySelector('.produit-type').value;
        const libelle = row.querySelector('.produit-libelle').value;
        const poids = parseFloat(row.querySelector('.produit-poids').value);
        
        const produit = { type, libelle, poids };
        
        if (type === 'chimique') {
            produit.degreToxicite = parseInt(row.querySelector('.produit-toxicite').value);
        }
        
        produits.push(produit);
    });
    
    const cargaisonNumero = document.getElementById('cargaisonSelect').value || null;
    
    const formData = {
        expediteur,
        destinataire,
        produits,
        cargaisonNumero
    };
    
    try {
        const response = await fetch('?page=api&action=create_colis', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            closeColisModal();
            loadData();
            
            // Afficher le code généré
            document.getElementById('generatedCode').textContent = result.data.code;
            document.getElementById('codeModal').style.display = 'block';
        } else {
            alert(result.message || 'Erreur lors de la création');
        }
    } catch (error) {
        alert('Erreur lors de la création du colis');
    }
});

function applyFilters() {
    const codeSearch = document.getElementById('codeSearch').value.toLowerCase();
    const clientSearch = document.getElementById('clientSearch').value.toLowerCase();
    const etatFilter = document.getElementById('etatFilter').value;
    
    let filtered = colis.filter(colis => {
        const matchesCode = !codeSearch || colis.code.toLowerCase().includes(codeSearch);
        
        const matchesClient = !clientSearch || 
            colis.expediteur.nom.toLowerCase().includes(clientSearch) ||
            colis.expediteur.prenom.toLowerCase().includes(clientSearch) ||
            colis.destinataire.nom.toLowerCase().includes(clientSearch) ||
            colis.destinataire.prenom.toLowerCase().includes(clientSearch);
            
        const matchesEtat = !etatFilter || colis.etat === etatFilter;
        
        return matchesCode && matchesClient && matchesEtat;
    });
    
    displayColis(filtered);
}

async function marquerRecupere(code) {
    if (confirm('Marquer ce colis comme récupéré ?')) {
        try {
            const response = await fetch('?page=api&action=mark_recupere', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ code })
            });
            
            const result = await response.json();
            if (result.success) {
                loadData();
            } else {
                alert(result.message);
            }
        } catch (error) {
            alert('Erreur lors de la mise à jour');
        }
    }
}

async function annulerColis(code) {
    if (confirm('Êtes-vous sûr de vouloir annuler ce colis ?')) {
        try {
            const response = await fetch('?page=api&action=cancel_colis', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ code })
            });
            
            const result = await response.json();
            if (result.success) {
                loadData();
            } else {
                alert(result.message);
            }
        } catch (error) {
            alert('Erreur lors de l\'annulation');
        }
    }
}

function formatPrice(price) {
    return new Intl.NumberFormat('fr-FR').format(price);
}

function voirColis(code) {
    window.location.href = `?page=colis&action=view&code=${code}`;
}

function imprimerRecu() {
    // Implémenter la génération du reçu
    window.print();
}

// Initialiser au chargement
document.addEventListener('DOMContentLoaded', function() {
    loadData();
    attachProduitEvents();
});
</script>

<style>
.form-section {
    margin-bottom: 25px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid #3498db;
}

.form-section h4 {
    margin: 0 0 15px 0;
    color: #2c3e50;
    font-size: 16px;
}

.produit-row {
    background: white;
    padding: 15px;
    border-radius: 6px;
    margin-bottom: 10px;
    border: 1px solid #e1e8ed;
}

.toxicite-group {
    transition: all 0.3s ease;
}

.action-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
}

.action-buttons .btn-sm {
    margin: 0;
}

.modal-content {
    max-height: 90vh;
    overflow-y: auto;
}

.status-en-attente { background: #fff3cd; color: #856404; }
.status-en-cours { background: #d4edda; color: #155724; }
.status-arrive { background: #d1ecf1; color: #0c5460; }
.status-recupere { background: #d1ecf1; color: #0c5460; }
.status-perdu { background: #f8d7da; color: #721c24; }
.status-archive { background: #e2e3e5; color: #383d41; }
</style>

<?php include 'includes/footer.php'; ?>
