// GP du Monde - Application JavaScript

// Utilitaires globaux
const Utils = {
    // Formater les prix
    formatPrice: (price) => {
        return new Intl.NumberFormat('fr-FR').format(price);
    },
    
    // Formater les dates
    formatDate: (dateString) => {
        return new Date(dateString).toLocaleDateString('fr-FR');
    },
    
    // Obtenir l'icône selon le type
    getTypeIcon: (type) => {
        switch(type) {
            case 'maritime': return 'ship';
            case 'aerienne': return 'plane';
            case 'routiere': return 'truck';
            case 'alimentaire': return 'apple-alt';
            case 'chimique': return 'flask';
            case 'fragile': return 'wine-glass';
            case 'incassable': return 'hammer';
            default: return 'box';
        }
    },
    
    // Capitaliser une chaîne
    capitalize: (str) => {
        return str.charAt(0).toUpperCase() + str.slice(1);
    },
    
    // Obtenir la couleur de route selon le type
    getRouteColor: (type) => {
        switch(type) {
            case 'maritime': return '#3498db';
            case 'aerienne': return '#e74c3c';
            case 'routiere': return '#27ae60';
            default: return '#95a5a6';
        }
    },
    
    // Calculer la distance haversine
    calculateDistance: (lat1, lon1, lat2, lon2) => {
        const R = 6371; // Rayon de la Terre en km
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                  Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                  Math.sin(dLon/2) * Math.sin(dLon/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    },
    
    // Générer un ID unique
    generateId: () => {
        return Date.now().toString(36) + Math.random().toString(36).substr(2);
    },
    
    // Débounce function
    debounce: (func, wait) => {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
};

// Gestionnaire d'API avec json-server
const API = {
    jsonServerUrl: 'http://localhost:3001',
    phpUrl: window.location.origin + window.location.pathname,
    
    // Méthode générique pour les requêtes vers json-server
    async jsonRequest(endpoint, method = 'GET', data = null) {
        const url = `${this.jsonServerUrl}/${endpoint}`;
        
        const options = {
            method,
            headers: {
                'Content-Type': 'application/json',
            }
        };
        
        if (data && method !== 'GET') {
            options.body = JSON.stringify(data);
        }
        
        try {
            const response = await fetch(url, options);
            return await response.json();
        } catch (error) {
            console.error('Erreur json-server:', error);
            throw new Error('Erreur de connexion au serveur json-server');
        }
    },
    
    // Méthode pour les requêtes PHP (authentification, etc.)
    async phpRequest(action, method = 'GET', data = null) {
        const url = `${this.phpUrl}?page=api&action=${action}`;
        
        const options = {
            method,
            headers: {
                'Content-Type': 'application/json',
            }
        };
        
        if (data && method !== 'GET') {
            options.body = JSON.stringify(data);
        }
        
        try {
            const response = await fetch(url, options);
            return await response.json();
        } catch (error) {
            console.error('Erreur API PHP:', error);
            throw new Error('Erreur de connexion au serveur PHP');
        }
    },
    
    // Connexion (reste en PHP pour les sessions)
    login: (username, password) => {
        return API.phpRequest('login', 'POST', { username, password });
    },
    
    // Statistiques (calculées côté client)
    getStats: async () => {
        const cargaisons = await API.jsonRequest('cargaisons');
        const colis = await API.jsonRequest('colis');
        
        const cargaisonsActives = cargaisons.filter(c => c.etat !== 'COMPLETE').length;
        const colisEnCours = colis.filter(c => c.etat === 'EN_COURS').length;
        const colisLivres = colis.filter(c => ['ARRIVE', 'RECUPERE'].includes(c.etat)).length;
        const colisEnRetard = 0; // TODO: logique de retard
        
        return {
            cargaisonsActives,
            colisEnCours,
            colisLivres,
            colisEnRetard
        };
    },
    
    // Cargaisons (json-server REST)
    getCargaisons: () => {
        return API.jsonRequest('cargaisons');
    },
    
    getRecentCargaisons: async () => {
        const cargaisons = await API.jsonRequest('cargaisons?_sort=dateCreation&_order=desc&_limit=10');
        return cargaisons;
    },
    
    getCargaisonsOuvertes: () => {
        return API.jsonRequest('cargaisons?statut=OUVERTE');
    },
    
    createCargaison: (data) => {
        // Ajouter les champs manquants
        const cargaison = {
            ...data,
            numero: 'CAR-' + Date.now().toString(36).toUpperCase(),
            etat: 'EN_ATTENTE',
            statut: 'OUVERTE',
            dateCreation: new Date().toISOString(),
            prixTotal: 0
        };
        return API.jsonRequest('cargaisons', 'POST', cargaison);
    },
    
    closeCargaison: async (numero) => {
        const cargaisons = await API.jsonRequest(`cargaisons?numero=${numero}`);
        if (cargaisons.length > 0) {
            const cargaison = cargaisons[0];
            cargaison.statut = 'FERMEE';
            return API.jsonRequest(`cargaisons/${cargaison.id}`, 'PUT', cargaison);
        }
        throw new Error('Cargaison non trouvée');
    },
    
    reopenCargaison: async (numero) => {
        const cargaisons = await API.jsonRequest(`cargaisons?numero=${numero}`);
        if (cargaisons.length > 0) {
            const cargaison = cargaisons[0];
            if (cargaison.etat !== 'EN_ATTENTE') {
                throw new Error('Impossible de rouvrir : la cargaison n\'est plus en attente');
            }
            cargaison.statut = 'OUVERTE';
            cargaison.prixTotal *= 2; // Doubler le prix
            return API.jsonRequest(`cargaisons/${cargaison.id}`, 'PUT', cargaison);
        }
        throw new Error('Cargaison non trouvée');
    },
    
    // Villes (depuis les paramètres)
    getVilles: async () => {
        const parametres = await API.jsonRequest('parametres');
        return parametres.coordonneesVilles || [];
    },
    
    // Routes (logique côté client)
    getRoute: (origine, destination, type) => {
        // Implémentation simplifiée côté client
        return Promise.resolve({
            success: true,
            data: {
                origine: { nom: origine },
                destination: { nom: destination },
                route: [
                    { latitude: 14.6928, longitude: -17.4467 }, // Dakar
                    { latitude: 5.3600, longitude: -4.0083 }    // Abidjan
                ],
                type
            }
        });
    },
    
    // Colis (json-server REST)
    getColis: () => {
        return API.jsonRequest('colis');
    },
    
    createColis: (data) => {
        // Ajouter les champs manquants
        const colis = {
            ...data,
            code: 'COL-' + Date.now().toString(36).toUpperCase(),
            etat: data.cargaisonNumero ? 'EN_COURS' : 'EN_ATTENTE',
            dateCreation: new Date().toISOString(),
            prix: Math.max(data.produits.reduce((total, p) => total + (p.poids * 100), 0), 10000)
        };
        return API.jsonRequest('colis', 'POST', colis);
    },
    
    trackColis: async (code) => {
        const colis = await API.jsonRequest(`colis?code=${code}`);
        if (colis.length > 0) {
            const colisData = colis[0];
            return {
                success: true,
                data: {
                    code: colisData.code,
                    etat: colisData.etat,
                    expediteur: `${colisData.expediteur.prenom} ${colisData.expediteur.nom}`,
                    destinataire: `${colisData.destinataire.prenom} ${colisData.destinataire.nom}`,
                    dateCreation: new Date(colisData.dateCreation).toLocaleDateString('fr-FR'),
                    typeCargaison: colisData.cargaisonNumero ? 'maritime' : null,
                    origine: colisData.cargaisonNumero ? 'Dakar' : null,
                    destination: colisData.cargaisonNumero ? 'Abidjan' : null,
                    distance: colisData.cargaisonNumero ? 1200 : null
                }
            };
        }
        return { success: false };
    },
    
    markRecupere: async (code) => {
        const colis = await API.jsonRequest(`colis?code=${code}`);
        if (colis.length > 0) {
            const colisData = colis[0];
            if (colisData.etat === 'ARRIVE') {
                colisData.etat = 'RECUPERE';
                colisData.dateRecuperation = new Date().toISOString();
                return API.jsonRequest(`colis/${colisData.id}`, 'PUT', colisData);
            }
            throw new Error('Le colis n\'est pas encore arrivé');
        }
        throw new Error('Colis non trouvé');
    },
    
    cancelColis: async (code) => {
        const colis = await API.jsonRequest(`colis?code=${code}`);
        if (colis.length > 0) {
            const colisData = colis[0];
            if (colisData.etat === 'EN_ATTENTE') {
                colisData.etat = 'ANNULE';
                return API.jsonRequest(`colis/${colisData.id}`, 'PUT', colisData);
            }
            throw new Error('Le colis ne peut plus être annulé');
        }
        throw new Error('Colis non trouvé');
    }
};

// Gestionnaire de notifications
const Notifications = {
    show: (message, type = 'info', duration = 5000) => {
        // Créer l'élément de notification
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas fa-${Notifications.getIcon(type)}"></i>
                <span>${message}</span>
            </div>
            <button class="notification-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        // Ajouter les styles si pas encore fait
        if (!document.getElementById('notification-styles')) {
            const styles = document.createElement('style');
            styles.id = 'notification-styles';
            styles.textContent = `
                .notification {
                    position: fixed;
                    top: 90px;
                    right: 20px;
                    background: white;
                    padding: 15px 20px;
                    border-radius: 8px;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
                    border-left: 4px solid;
                    z-index: 1001;
                    min-width: 300px;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    animation: slideInRight 0.3s ease;
                }
                .notification-info { border-color: #3498db; }
                .notification-success { border-color: #27ae60; }
                .notification-warning { border-color: #f39c12; }
                .notification-error { border-color: #e74c3c; }
                .notification-content { display: flex; align-items: center; gap: 10px; }
                .notification-close { 
                    background: none; 
                    border: none; 
                    cursor: pointer; 
                    opacity: 0.6; 
                }
                .notification-close:hover { opacity: 1; }
                @keyframes slideInRight {
                    from { transform: translateX(100%); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
            `;
            document.head.appendChild(styles);
        }
        
        // Ajouter au DOM
        document.body.appendChild(notification);
        
        // Auto-suppression
        if (duration > 0) {
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, duration);
        }
    },
    
    getIcon: (type) => {
        switch(type) {
            case 'success': return 'check-circle';
            case 'warning': return 'exclamation-triangle';
            case 'error': return 'times-circle';
            default: return 'info-circle';
        }
    },
    
    success: (message) => Notifications.show(message, 'success'),
    warning: (message) => Notifications.show(message, 'warning'),
    error: (message) => Notifications.show(message, 'error'),
    info: (message) => Notifications.show(message, 'info')
};

// Gestionnaire de modales
const Modal = {
    show: (modalId) => {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    },
    
    hide: (modalId) => {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    },
    
    // Fermer en cliquant à l'extérieur
    setupClickOutside: (modalId) => {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    Modal.hide(modalId);
                }
            });
        }
    }
};

// Gestionnaire de cartes
const MapManager = {
    maps: new Map(),
    
    // Initialiser une carte
    init: (containerId, options = {}) => {
        const container = document.getElementById(containerId);
        if (!container) return null;
        
        const defaultOptions = {
            center: [14.6928, -17.4467], // Dakar par défaut
            zoom: 4,
            ...options
        };
        
        const map = L.map(containerId).setView(defaultOptions.center, defaultOptions.zoom);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
        
        MapManager.maps.set(containerId, map);
        return map;
    },
    
    // Ajouter une route
    addRoute: (mapId, points, options = {}) => {
        const map = MapManager.maps.get(mapId);
        if (!map || !points || points.length < 2) return;
        
        const defaultOptions = {
            color: '#3498db',
            weight: 4,
            opacity: 0.8,
            ...options
        };
        
        const latlngs = points.map(point => [point.latitude, point.longitude]);
        const polyline = L.polyline(latlngs, defaultOptions).addTo(map);
        
        // Ajouter des marqueurs pour les points de départ et d'arrivée
        L.marker(latlngs[0]).addTo(map).bindPopup('Départ');
        L.marker(latlngs[latlngs.length - 1]).addTo(map).bindPopup('Arrivée');
        
        // Ajuster la vue
        map.fitBounds(polyline.getBounds(), { padding: [20, 20] });
        
        return polyline;
    },
    
    // Détruire une carte
    destroy: (mapId) => {
        const map = MapManager.maps.get(mapId);
        if (map) {
            map.remove();
            MapManager.maps.delete(mapId);
        }
    },
    
    // Obtenir une carte
    get: (mapId) => {
        return MapManager.maps.get(mapId);
    }
};

// Gestionnaire de formulaires
const FormManager = {
    // Valider un formulaire
    validate: (formElement) => {
        const inputs = formElement.querySelectorAll('input[required], select[required], textarea[required]');
        let isValid = true;
        
        inputs.forEach(input => {
            const errorElement = input.parentElement.querySelector('.error-message');
            if (errorElement) {
                errorElement.remove();
            }
            
            if (!input.value.trim()) {
                FormManager.showFieldError(input, 'Ce champ est obligatoire');
                isValid = false;
            } else {
                input.classList.remove('error');
            }
        });
        
        return isValid;
    },
    
    // Afficher une erreur sur un champ
    showFieldError: (inputElement, message) => {
        inputElement.classList.add('error');
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.textContent = message;
        errorDiv.style.cssText = 'color: #e74c3c; font-size: 12px; margin-top: 5px;';
        inputElement.parentElement.appendChild(errorDiv);
    },
    
    // Effacer les erreurs d'un formulaire
    clearErrors: (formElement) => {
        const errorElements = formElement.querySelectorAll('.error-message');
        errorElements.forEach(el => el.remove());
        
        const inputsWithErrors = formElement.querySelectorAll('.error');
        inputsWithErrors.forEach(input => input.classList.remove('error'));
    },
    
    // Réinitialiser un formulaire
    reset: (formElement) => {
        formElement.reset();
        FormManager.clearErrors(formElement);
    }
};

// Gestionnaire de données locales
const LocalStorage = {
    set: (key, value) => {
        try {
            localStorage.setItem(`gp_monde_${key}`, JSON.stringify(value));
        } catch (error) {
            console.error('Erreur LocalStorage set:', error);
        }
    },
    
    get: (key, defaultValue = null) => {
        try {
            const item = localStorage.getItem(`gp_monde_${key}`);
            return item ? JSON.parse(item) : defaultValue;
        } catch (error) {
            console.error('Erreur LocalStorage get:', error);
            return defaultValue;
        }
    },
    
    remove: (key) => {
        try {
            localStorage.removeItem(`gp_monde_${key}`);
        } catch (error) {
            console.error('Erreur LocalStorage remove:', error);
        }
    },
    
    clear: () => {
        try {
            const keys = Object.keys(localStorage).filter(key => key.startsWith('gp_monde_'));
            keys.forEach(key => localStorage.removeItem(key));
        } catch (error) {
            console.error('Erreur LocalStorage clear:', error);
        }
    }
};

// Loading indicator
const Loading = {
    show: (element) => {
        if (element) {
            element.classList.add('loading');
            const spinner = document.createElement('span');
            spinner.className = 'spinner';
            spinner.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            element.prepend(spinner);
        }
    },
    
    hide: (element) => {
        if (element) {
            element.classList.remove('loading');
            const spinner = element.querySelector('.spinner');
            if (spinner) {
                spinner.remove();
            }
        }
    }
};

// Gestionnaire global d'erreurs
window.addEventListener('error', (event) => {
    console.error('Erreur JavaScript:', event.error);
    Notifications.error('Une erreur inattendue s\'est produite');
});

// Gestionnaire de déconnexion
function logout() {
    if (confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
        window.location.href = '?page=api&action=logout';
    }
}

// Responsive sidebar toggle
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    if (sidebar) {
        sidebar.classList.toggle('open');
    }
}

// Initialisation au chargement du DOM
document.addEventListener('DOMContentLoaded', () => {
    // Setup des modales pour fermeture en cliquant à l'extérieur
    document.querySelectorAll('.modal').forEach(modal => {
        Modal.setupClickOutside(modal.id);
    });
    
    // Setup responsive sidebar
    if (window.innerWidth <= 768) {
        const navbar = document.querySelector('.navbar-brand');
        if (navbar && !navbar.querySelector('.menu-toggle')) {
            const menuToggle = document.createElement('button');
            menuToggle.className = 'menu-toggle';
            menuToggle.innerHTML = '<i class="fas fa-bars"></i>';
            menuToggle.style.cssText = 'background: none; border: none; font-size: 20px; margin-left: 15px; cursor: pointer;';
            menuToggle.addEventListener('click', toggleSidebar);
            navbar.appendChild(menuToggle);
        }
    }
    
    // Setup des tooltips si nécessaire
    document.querySelectorAll('[data-tooltip]').forEach(element => {
        element.addEventListener('mouseenter', (e) => {
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = e.target.dataset.tooltip;
            tooltip.style.cssText = `
                position: absolute;
                background: #333;
                color: white;
                padding: 8px 12px;
                border-radius: 4px;
                font-size: 12px;
                z-index: 1002;
                pointer-events: none;
            `;
            document.body.appendChild(tooltip);
            
            const rect = e.target.getBoundingClientRect();
            tooltip.style.left = rect.left + 'px';
            tooltip.style.top = (rect.top - tooltip.offsetHeight - 5) + 'px';
        });
        
        element.addEventListener('mouseleave', () => {
            const tooltip = document.querySelector('.tooltip');
            if (tooltip) tooltip.remove();
        });
    });
});

// Export des utilitaires globaux
window.GPMonde = {
    Utils,
    API,
    Notifications,
    Modal,
    MapManager,
    FormManager,
    LocalStorage,
    Loading
};
