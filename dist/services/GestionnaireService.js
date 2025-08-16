"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.gestionnaireService = exports.GestionnaireService = void 0;
const Cargaison_1 = require("../models/Cargaison");
const Colis_1 = require("../models/Colis");
class GestionnaireService {
    constructor() {
        this.cargaisons = new Map();
        this.colis = new Map();
        this.clients = new Map();
    }
    // Gestion des cargaisons
    creerCargaison(type, poidsMax, distance, lieuDepart, lieuArrivee) {
        const numero = this.genererNumeroCargaison();
        let cargaison;
        switch (type) {
            case 'maritime':
                cargaison = new Cargaison_1.Maritime(numero, poidsMax, distance, lieuDepart, lieuArrivee);
                break;
            case 'aerienne':
                cargaison = new Cargaison_1.Aerienne(numero, poidsMax, distance, lieuDepart, lieuArrivee);
                break;
            case 'routiere':
                cargaison = new Cargaison_1.Routiere(numero, poidsMax, distance, lieuDepart, lieuArrivee);
                break;
        }
        this.cargaisons.set(numero, cargaison);
        return cargaison;
    }
    rechercherCargaisonParCode(code) {
        return this.cargaisons.get(code);
    }
    rechercherCargaisonsParLieu(lieu, type) {
        const cargaisons = [];
        for (const cargaison of this.cargaisons.values()) {
            const lieuAComparer = type === 'depart' ? cargaison.lieuDepart.nom : cargaison.lieuArrivee.nom;
            if (lieuAComparer.toLowerCase().includes(lieu.toLowerCase())) {
                cargaisons.push(cargaison);
            }
        }
        return cargaisons;
    }
    rechercherCargaisonsParType(type) {
        const cargaisons = [];
        for (const cargaison of this.cargaisons.values()) {
            if (cargaison.getType() === type) {
                cargaisons.push(cargaison);
            }
        }
        return cargaisons;
    }
    fermerCargaison(numeroCargaison) {
        const cargaison = this.cargaisons.get(numeroCargaison);
        if (cargaison) {
            cargaison.fermerCargaison();
            return true;
        }
        return false;
    }
    rouvrirCargaison(numeroCargaison) {
        const cargaison = this.cargaisons.get(numeroCargaison);
        if (cargaison) {
            return cargaison.rouvrirCargaison();
        }
        return false;
    }
    // Gestion des clients
    enregistrerClient(nom, prenom, telephone, adresse, email) {
        const id = this.genererIdClient();
        const client = {
            id,
            nom,
            prenom,
            telephone,
            adresse,
            email
        };
        this.clients.set(id, client);
        return client;
    }
    rechercherClient(id) {
        return this.clients.get(id);
    }
    // Gestion des colis
    enregistrerColis(expediteur, destinataire, produits) {
        const code = Colis_1.Colis.genererCode();
        const colis = new Colis_1.Colis(code, expediteur, destinataire, produits);
        this.colis.set(code, colis);
        return colis;
    }
    rechercherColisParCode(code) {
        return this.colis.get(code);
    }
    ajouterColisACargaison(codeColis, numeroCargaison) {
        const colis = this.colis.get(codeColis);
        const cargaison = this.cargaisons.get(numeroCargaison);
        if (!colis || !cargaison) {
            return false;
        }
        return colis.ajouterACargaison(cargaison);
    }
    marquerColisCommeRecupere(code) {
        const colis = this.colis.get(code);
        if (colis) {
            colis.marquerCommeRecupere();
            return true;
        }
        return false;
    }
    marquerColisCommePerdu(code) {
        const colis = this.colis.get(code);
        if (colis) {
            colis.marquerCommePerdu();
            return true;
        }
        return false;
    }
    archiverColis(code) {
        const colis = this.colis.get(code);
        if (colis) {
            colis.archiver();
            return true;
        }
        return false;
    }
    annulerColis(code) {
        const colis = this.colis.get(code);
        if (colis) {
            return colis.annuler();
        }
        return false;
    }
    changerEtatColis(code, nouvelEtat) {
        const colis = this.colis.get(code);
        if (colis) {
            colis.etat = nouvelEtat;
            return true;
        }
        return false;
    }
    // Suivi des colis
    suivreColis(code) {
        const colis = this.colis.get(code);
        if (!colis || colis.etat === Colis_1.EtatColis.ANNULE) {
            return null;
        }
        return colis.getInfoSuivi();
    }
    // Statistiques
    getStatistiques() {
        const cargaisonsActives = Array.from(this.cargaisons.values())
            .filter(c => c.etat !== Cargaison_1.EtatCargaison.COMPLETE).length;
        const colisEnCours = Array.from(this.colis.values())
            .filter(c => c.etat === Colis_1.EtatColis.EN_COURS).length;
        const colisLivres = Array.from(this.colis.values())
            .filter(c => c.etat === Colis_1.EtatColis.ARRIVE || c.etat === Colis_1.EtatColis.RECUPERE).length;
        const colisEnRetard = Array.from(this.colis.values())
            .filter(c => this.isColisEnRetard(c)).length;
        return {
            cargaisonsActives,
            colisEnCours,
            colisLivres,
            colisEnRetard
        };
    }
    getCargaisonsRecentes(limite = 10) {
        return Array.from(this.cargaisons.values())
            .sort((a, b) => b.dateCreation.getTime() - a.dateCreation.getTime())
            .slice(0, limite);
    }
    // Méthodes utilitaires
    genererNumeroCargaison() {
        const timestamp = Date.now().toString().slice(-6);
        const random = Math.random().toString(36).substr(2, 3).toUpperCase();
        return `CAR-${timestamp}${random}`;
    }
    genererIdClient() {
        const timestamp = Date.now().toString().slice(-6);
        const random = Math.random().toString(36).substr(2, 4).toUpperCase();
        return `CLI-${timestamp}${random}`;
    }
    isColisEnRetard(colis) {
        // Logique simplifiée pour déterminer si un colis est en retard
        if (!colis.cargaison || !colis.dateExpedition) {
            return false;
        }
        const maintenant = new Date();
        const dateExpedition = colis.dateExpedition;
        const dureeEstimee = this.calculerDureeEstimee(colis.cargaison);
        const dateArriveeEstimee = new Date(dateExpedition.getTime() + dureeEstimee * 24 * 60 * 60 * 1000);
        return maintenant > dateArriveeEstimee && colis.etat !== Colis_1.EtatColis.ARRIVE && colis.etat !== Colis_1.EtatColis.RECUPERE;
    }
    calculerDureeEstimee(cargaison) {
        // Durée estimée en jours selon le type de cargaison
        const type = cargaison.getType();
        switch (type) {
            case 'maritime': return Math.ceil(cargaison.distance / 500); // 500 km/jour
            case 'aerienne': return Math.ceil(cargaison.distance / 3000); // 3000 km/jour
            case 'routiere': return Math.ceil(cargaison.distance / 800); // 800 km/jour
            default: return 7;
        }
    }
    // Export/Import données
    exporterDonnees() {
        return {
            cargaisons: Array.from(this.cargaisons.entries()),
            colis: Array.from(this.colis.entries()),
            clients: Array.from(this.clients.entries())
        };
    }
    importerDonnees(donnees) {
        if (donnees.cargaisons) {
            this.cargaisons = new Map(donnees.cargaisons);
        }
        if (donnees.colis) {
            this.colis = new Map(donnees.colis);
        }
        if (donnees.clients) {
            this.clients = new Map(donnees.clients);
        }
    }
}
exports.GestionnaireService = GestionnaireService;
// Instance singleton
exports.gestionnaireService = new GestionnaireService();
//# sourceMappingURL=GestionnaireService.js.map