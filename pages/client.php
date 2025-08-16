<?php include 'includes/header.php'; ?>

<!-- Page client -->
<div class="client-container">
    <header class="client-header">
        <nav class="client-nav">
            <div class="navbar-brand">
                <div class="logo-small">GP</div>
                GP du Monde
            </div>
            <a href="?page=login" class="btn-back">Espace Gestionnaire</a>
        </nav>
    </header>

    <main class="client-main">
        <div class="tracking-card">
            <h1 class="tracking-title">Suivi de votre colis</h1>
            <p style="color: #7f8c8d; margin-bottom: 30px;">
                Entrez le numéro de votre colis pour connaître son statut en temps réel
            </p>

            <form class="search-form" id="trackingForm">
                <div class="search-group">
                    <input type="text" class="search-input" id="trackingNumber" 
                           placeholder="Entrez le numéro de votre colis (ex: COL-123456)" required>
                    <button type="submit" class="btn-search">Rechercher</button>
                </div>
            </form>

            <div class="tracking-result" id="trackingResult" style="display: none;">
                <div class="tracking-info">
                    <div class="info-row">
                        <span class="info-label">Numéro de colis:</span>
                        <span class="info-value" id="colisNumber"></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Statut:</span>
                        <span class="info-value">
                            <span class="status-badge" id="colisStatus"></span>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Expéditeur:</span>
                        <span class="info-value" id="expediteur"></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Destinataire:</span>
                        <span class="info-value" id="destinataire"></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Origine:</span>
                        <span class="info-value" id="origine"></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Destination:</span>
                        <span class="info-value" id="destination"></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Type de cargaison:</span>
                        <span class="info-value" id="typeCargaison"></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Distance:</span>
                        <span class="info-value" id="distance"></span>
                    </div>
                    <div class="info-row" id="dateArriveeRow" style="display: none;">
                        <span class="info-label">Date estimée d'arrivée:</span>
                        <span class="info-value" id="dateArrivee"></span>
                    </div>
                </div>
                
                <!-- Carte pour l'itinéraire -->
                <div id="map" style="height: 300px; margin-top: 20px; border-radius: 10px; display: none;"></div>
            </div>

            <div id="error-message" class="error-message" style="display: none; margin-top: 20px;">
                <p>Colis non trouvé. Vérifiez le numéro saisi ou contactez notre service client.</p>
            </div>
        </div>
    </main>
</div>

<script>
let map = null;

document.getElementById('trackingForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const trackingNumber = document.getElementById('trackingNumber').value.trim();
    const resultDiv = document.getElementById('trackingResult');
    const errorDiv = document.getElementById('error-message');
    
    // Cacher les résultats précédents
    resultDiv.style.display = 'none';
    errorDiv.style.display = 'none';
    
    try {
        const response = await fetch(`?page=api&action=track_colis&code=${encodeURIComponent(trackingNumber)}`);
        const result = await response.json();
        
        if (result.success && result.data) {
            displayTrackingResult(result.data);
            resultDiv.style.display = 'block';
        } else {
            errorDiv.style.display = 'block';
        }
    } catch (error) {
        console.error('Erreur lors du suivi:', error);
        errorDiv.style.display = 'block';
    }
});

function displayTrackingResult(data) {
    document.getElementById('colisNumber').textContent = data.code;
    document.getElementById('expediteur').textContent = data.expediteur;
    document.getElementById('destinataire').textContent = data.destinataire;
    
    const statusElement = document.getElementById('colisStatus');
    statusElement.textContent = data.etat;
    statusElement.className = `status-badge status-${data.etat.toLowerCase().replace('_', '-')}`;
    
    if (data.typeCargaison) {
        document.getElementById('typeCargaison').innerHTML = `<i class="fas fa-${getTypeIcon(data.typeCargaison)}"></i> ${capitalize(data.typeCargaison)}`;
        document.getElementById('origine').textContent = data.origine || '';
        document.getElementById('destination').textContent = data.destination || '';
        document.getElementById('distance').textContent = data.distance ? `${data.distance} km` : '';
        
        // Afficher la carte avec l'itinéraire
        if (data.origine && data.destination && data.typeCargaison) {
            showRoute(data);
        }
    } else {
        document.getElementById('typeCargaison').textContent = 'En attente d\'affectation';
        document.getElementById('origine').textContent = '-';
        document.getElementById('destination').textContent = '-';
        document.getElementById('distance').textContent = '-';
    }
    
    // Afficher la date d'arrivée estimée si disponible
    if (data.dateArriveeEstimee) {
        document.getElementById('dateArrivee').textContent = data.dateArriveeEstimee;
        document.getElementById('dateArriveeRow').style.display = 'flex';
    } else {
        document.getElementById('dateArriveeRow').style.display = 'none';
    }
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

async function showRoute(data) {
    const mapDiv = document.getElementById('map');
    mapDiv.style.display = 'block';
    
    // Détruire la carte existante si elle existe
    if (map) {
        map.remove();
    }
    
    // Récupérer les coordonnées des villes
    try {
        const response = await fetch(`?page=api&action=get_route&origine=${encodeURIComponent(data.origine)}&destination=${encodeURIComponent(data.destination)}&type=${data.typeCargaison}`);
        const routeData = await response.json();
        
        if (routeData.success) {
            const { origine, destination, route } = routeData.data;
            
            // Initialiser la carte
            map = L.map('map').setView([origine.latitude, origine.longitude], 4);
            
            // Ajouter les tuiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);
            
            // Ajouter les marqueurs
            L.marker([origine.latitude, origine.longitude])
                .addTo(map)
                .bindPopup(`<b>Départ:</b> ${data.origine}`)
                .openPopup();
                
            L.marker([destination.latitude, destination.longitude])
                .addTo(map)
                .bindPopup(`<b>Arrivée:</b> ${data.destination}`);
            
            // Dessiner la route
            const latlngs = route.map(point => [point.latitude, point.longitude]);
            const polyline = L.polyline(latlngs, {
                color: getRouteColor(data.typeCargaison),
                weight: 4,
                opacity: 0.8
            }).addTo(map);
            
            // Ajuster la vue pour inclure toute la route
            map.fitBounds(polyline.getBounds(), {padding: [20, 20]});
        }
    } catch (error) {
        console.error('Erreur lors du chargement de la route:', error);
        mapDiv.innerHTML = '<p style="text-align: center; padding: 20px;">Impossible de charger la carte</p>';
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
</script>

<style>
.error-message {
    background: #f8d7da;
    color: #721c24;
    padding: 15px;
    border-radius: 10px;
    border: 1px solid #f5c6cb;
    text-align: center;
}

.status-badge {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-en-attente { background: #fff3cd; color: #856404; }
.status-en-cours { background: #d4edda; color: #155724; }
.status-arrive { background: #d1ecf1; color: #0c5460; }
.status-recupere { background: #d1ecf1; color: #0c5460; }
.status-perdu { background: #f8d7da; color: #721c24; }
</style>

<?php include 'includes/footer.php'; ?>
