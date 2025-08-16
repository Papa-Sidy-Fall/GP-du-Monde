import { Cargaison, Maritime, Aerienne, Routiere, Coordonnee, EtatCargaison } from '../models/Cargaison';
import { Colis, EtatColis, Client } from '../models/Colis';
import { Produit } from '../models/Produit';

export class GestionnaireService {
    private cargaisons: Map<string, Cargaison> = new Map();
    private colis: Map<string, Colis> = new Map();
    private clients: Map<string, Client> = new Map();

    // Gestion des cargaisons
    creerCargaison(
        type: 'maritime' | 'aerienne' | 'routiere',
        poidsMax: number,
        distance: number,
        lieuDepart: Coordonnee,
        lieuArrivee: Coordonnee
    ): Cargaison {
        const numero = this.genererNumeroCargaison();
        let cargaison: Cargaison;

        switch (type) {
            case 'maritime':
                cargaison = new Maritime(numero, poidsMax, distance, lieuDepart, lieuArrivee);
                break;
            case 'aerienne':
                cargaison = new Aerienne(numero, poidsMax, distance, lieuDepart, lieuArrivee);
                break;
            case 'routiere':
                cargaison = new Routiere(numero, poidsMax, distance, lieuDepart, lieuArrivee);
                break;
        }

        this.cargaisons.set(numero, cargaison);
        return cargaison;
    }

    rechercherCargaisonParCode(code: string): Cargaison | undefined {
        return this.cargaisons.get(code);
    }

    rechercherCargaisonsParLieu(lieu: string, type: 'depart' | 'arrivee'): Cargaison[] {
        const cargaisons: Cargaison[] = [];
        
        for (const cargaison of this.cargaisons.values()) {
            const lieuAComparer = type === 'depart' ? cargaison.lieuDepart.nom : cargaison.lieuArrivee.nom;
            if (lieuAComparer.toLowerCase().includes(lieu.toLowerCase())) {
                cargaisons.push(cargaison);
            }
        }
        
        return cargaisons;
    }

    rechercherCargaisonsParType(type: string): Cargaison[] {
        const cargaisons: Cargaison[] = [];
        
        for (const cargaison of this.cargaisons.values()) {
            if (cargaison.getType() === type) {
                cargaisons.push(cargaison);
            }
        }
        
        return cargaisons;
    }

    fermerCargaison(numeroCargaison: string): boolean {
        const cargaison = this.cargaisons.get(numeroCargaison);
        if (cargaison) {
            cargaison.fermerCargaison();
            return true;
        }
        return false;
    }

    rouvrirCargaison(numeroCargaison: string): boolean {
        const cargaison = this.cargaisons.get(numeroCargaison);
        if (cargaison) {
            return cargaison.rouvrirCargaison();
        }
        return false;
    }

    // Gestion des clients
    enregistrerClient(
        nom: string,
        prenom: string,
        telephone: string,
        adresse: string,
        email?: string
    ): Client {
        const id = this.genererIdClient();
        const client: Client = {
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

    rechercherClient(id: string): Client | undefined {
        return this.clients.get(id);
    }

    // Gestion des colis
    enregistrerColis(
        expediteur: Client,
        destinataire: Client,
        produits: Produit[]
    ): Colis {
        const code = Colis.genererCode();
        const colis = new Colis(code, expediteur, destinataire, produits);
        this.colis.set(code, colis);
        return colis;
    }

    rechercherColisParCode(code: string): Colis | undefined {
        return this.colis.get(code);
    }

    ajouterColisACargaison(codeColis: string, numeroCargaison: string): boolean {
        const colis = this.colis.get(codeColis);
        const cargaison = this.cargaisons.get(numeroCargaison);

        if (!colis || !cargaison) {
            return false;
        }

        return colis.ajouterACargaison(cargaison);
    }

    marquerColisCommeRecupere(code: string): boolean {
        const colis = this.colis.get(code);
        if (colis) {
            colis.marquerCommeRecupere();
            return true;
        }
        return false;
    }

    marquerColisCommePerdu(code: string): boolean {
        const colis = this.colis.get(code);
        if (colis) {
            colis.marquerCommePerdu();
            return true;
        }
        return false;
    }

    archiverColis(code: string): boolean {
        const colis = this.colis.get(code);
        if (colis) {
            colis.archiver();
            return true;
        }
        return false;
    }

    annulerColis(code: string): boolean {
        const colis = this.colis.get(code);
        if (colis) {
            return colis.annuler();
        }
        return false;
    }

    changerEtatColis(code: string, nouvelEtat: EtatColis): boolean {
        const colis = this.colis.get(code);
        if (colis) {
            colis.etat = nouvelEtat;
            return true;
        }
        return false;
    }

    // Suivi des colis
    suivreColis(code: string): any | null {
        const colis = this.colis.get(code);
        if (!colis || colis.etat === EtatColis.ANNULE) {
            return null;
        }
        return colis.getInfoSuivi();
    }

    // Statistiques
    getStatistiques(): any {
        const cargaisonsActives = Array.from(this.cargaisons.values())
            .filter(c => c.etat !== EtatCargaison.COMPLETE).length;
        
        const colisEnCours = Array.from(this.colis.values())
            .filter(c => c.etat === EtatColis.EN_COURS).length;
            
        const colisLivres = Array.from(this.colis.values())
            .filter(c => c.etat === EtatColis.ARRIVE || c.etat === EtatColis.RECUPERE).length;
            
        const colisEnRetard = Array.from(this.colis.values())
            .filter(c => this.isColisEnRetard(c)).length;

        return {
            cargaisonsActives,
            colisEnCours,
            colisLivres,
            colisEnRetard
        };
    }

    getCargaisonsRecentes(limite: number = 10): Cargaison[] {
        return Array.from(this.cargaisons.values())
            .sort((a, b) => b.dateCreation.getTime() - a.dateCreation.getTime())
            .slice(0, limite);
    }

    // Méthodes utilitaires
    private genererNumeroCargaison(): string {
        const timestamp = Date.now().toString().slice(-6);
        const random = Math.random().toString(36).substr(2, 3).toUpperCase();
        return `CAR-${timestamp}${random}`;
    }

    private genererIdClient(): string {
        const timestamp = Date.now().toString().slice(-6);
        const random = Math.random().toString(36).substr(2, 4).toUpperCase();
        return `CLI-${timestamp}${random}`;
    }

    private isColisEnRetard(colis: Colis): boolean {
        // Logique simplifiée pour déterminer si un colis est en retard
        if (!colis.cargaison || !colis.dateExpedition) {
            return false;
        }

        const maintenant = new Date();
        const dateExpedition = colis.dateExpedition;
        const dureeEstimee = this.calculerDureeEstimee(colis.cargaison);
        const dateArriveeEstimee = new Date(dateExpedition.getTime() + dureeEstimee * 24 * 60 * 60 * 1000);

        return maintenant > dateArriveeEstimee && colis.etat !== EtatColis.ARRIVE && colis.etat !== EtatColis.RECUPERE;
    }

    private calculerDureeEstimee(cargaison: Cargaison): number {
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
    exporterDonnees(): any {
        return {
            cargaisons: Array.from(this.cargaisons.entries()),
            colis: Array.from(this.colis.entries()),
            clients: Array.from(this.clients.entries())
        };
    }

    importerDonnees(donnees: any): void {
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

// Instance singleton
export const gestionnaireService = new GestionnaireService();
