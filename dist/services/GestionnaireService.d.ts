import { Cargaison, Coordonnee } from '../models/Cargaison';
import { Colis, EtatColis, Client } from '../models/Colis';
import { Produit } from '../models/Produit';
export declare class GestionnaireService {
    private cargaisons;
    private colis;
    private clients;
    creerCargaison(type: 'maritime' | 'aerienne' | 'routiere', poidsMax: number, distance: number, lieuDepart: Coordonnee, lieuArrivee: Coordonnee): Cargaison;
    rechercherCargaisonParCode(code: string): Cargaison | undefined;
    rechercherCargaisonsParLieu(lieu: string, type: 'depart' | 'arrivee'): Cargaison[];
    rechercherCargaisonsParType(type: string): Cargaison[];
    fermerCargaison(numeroCargaison: string): boolean;
    rouvrirCargaison(numeroCargaison: string): boolean;
    enregistrerClient(nom: string, prenom: string, telephone: string, adresse: string, email?: string): Client;
    rechercherClient(id: string): Client | undefined;
    enregistrerColis(expediteur: Client, destinataire: Client, produits: Produit[]): Colis;
    rechercherColisParCode(code: string): Colis | undefined;
    ajouterColisACargaison(codeColis: string, numeroCargaison: string): boolean;
    marquerColisCommeRecupere(code: string): boolean;
    marquerColisCommePerdu(code: string): boolean;
    archiverColis(code: string): boolean;
    annulerColis(code: string): boolean;
    changerEtatColis(code: string, nouvelEtat: EtatColis): boolean;
    suivreColis(code: string): any | null;
    getStatistiques(): any;
    getCargaisonsRecentes(limite?: number): Cargaison[];
    private genererNumeroCargaison;
    private genererIdClient;
    private isColisEnRetard;
    private calculerDureeEstimee;
    exporterDonnees(): any;
    importerDonnees(donnees: any): void;
}
export declare const gestionnaireService: GestionnaireService;
//# sourceMappingURL=GestionnaireService.d.ts.map