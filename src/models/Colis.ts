import { Produit } from './Produit';
import { Cargaison } from './Cargaison';

export enum EtatColis {
    EN_ATTENTE = "EN_ATTENTE",
    EN_COURS = "EN_COURS", 
    ARRIVE = "ARRIVE",
    RECUPERE = "RECUPERE",
    PERDU = "PERDU",
    ARCHIVE = "ARCHIVE",
    ANNULE = "ANNULE"
}

export interface Client {
    id: string;
    nom: string;
    prenom: string;
    telephone: string;
    adresse: string;
    email?: string;
}

export class Colis {
    private _code: string;
    private _expediteur: Client;
    private _destinataire: Client;
    private _produits: Produit[];
    private _cargaison?: Cargaison;
    private _etat: EtatColis = EtatColis.EN_ATTENTE;
    private _dateCreation: Date;
    private _dateExpedition?: Date;
    private _dateArrivee?: Date;
    private _dateRecuperation?: Date;
    private _prix: number = 0;

    constructor(
        code: string,
        expediteur: Client,
        destinataire: Client,
        produits: Produit[]
    ) {
        this._code = code;
        this._expediteur = expediteur;
        this._destinataire = destinataire;
        this._produits = produits;
        this._dateCreation = new Date();
    }

    // Getters
    get code(): string { return this._code; }
    get expediteur(): Client { return this._expediteur; }
    get destinataire(): Client { return this._destinataire; }
    get produits(): Produit[] { return this._produits; }
    get cargaison(): Cargaison | undefined { return this._cargaison; }
    get etat(): EtatColis { return this._etat; }
    get dateCreation(): Date { return this._dateCreation; }
    get dateExpedition(): Date | undefined { return this._dateExpedition; }
    get dateArrivee(): Date | undefined { return this._dateArrivee; }
    get dateRecuperation(): Date | undefined { return this._dateRecuperation; }
    get prix(): number { return this._prix; }

    // Setters
    set cargaison(value: Cargaison | undefined) { this._cargaison = value; }
    set etat(value: EtatColis) { this._etat = value; }
    set dateExpedition(value: Date | undefined) { this._dateExpedition = value; }
    set dateArrivee(value: Date | undefined) { this._dateArrivee = value; }
    set dateRecuperation(value: Date | undefined) { this._dateRecuperation = value; }
    set prix(value: number) { this._prix = value; }

    getPoidsTotal(): number {
        return this._produits.reduce((total, produit) => total + produit.poids, 0);
    }

    ajouterACargaison(cargaison: Cargaison): boolean {
        // Vérifier si le colis peut être ajouté à cette cargaison
        for (const produit of this._produits) {
            if (!this.peutEtreAjouteACargaison(produit, cargaison)) {
                return false;
            }
        }

        // Ajouter tous les produits à la cargaison
        let fraisTotal = 0;
        for (const produit of this._produits) {
            if (cargaison.ajouterProduit(produit)) {
                fraisTotal += cargaison.calculerFrais(produit);
            } else {
                return false;
            }
        }

        this._cargaison = cargaison;
        this._prix = Math.max(fraisTotal, 10000); // Prix minimum 10,000 F
        
        return true;
    }

    private peutEtreAjouteACargaison(produit: Produit, cargaison: Cargaison): boolean {
        const typeProduit = produit.getType();
        const typeCargaison = cargaison.getType();

        // Les produits chimiques doivent toujours transiter par voie maritime
        if (typeProduit === "chimique" && typeCargaison !== "maritime") {
            return false;
        }

        // Les produits fragiles ne doivent jamais passer par voie maritime
        if (typeProduit === "fragile" && typeCargaison === "maritime") {
            return false;
        }

        return true;
    }

    marquerCommeRecupere(): void {
        if (this._etat === EtatColis.ARRIVE) {
            this._etat = EtatColis.RECUPERE;
            this._dateRecuperation = new Date();
        }
    }

    marquerCommePerdu(): void {
        this._etat = EtatColis.PERDU;
    }

    archiver(): void {
        if (this._etat === EtatColis.RECUPERE || this._etat === EtatColis.PERDU) {
            this._etat = EtatColis.ARCHIVE;
        }
    }

    annuler(): boolean {
        // Ne peut être annulé que si la cargaison n'est pas encore fermée
        if (this._cargaison && this._cargaison.statut === "FERMEE") {
            return false;
        }
        
        this._etat = EtatColis.ANNULE;
        return true;
    }

    getInfoSuivi(): any {
        const info: any = {
            code: this._code,
            etat: this._etat,
            expediteur: `${this._expediteur.prenom} ${this._expediteur.nom}`,
            destinataire: `${this._destinataire.prenom} ${this._destinataire.nom}`,
            dateCreation: this._dateCreation.toLocaleDateString('fr-FR')
        };

        if (this._cargaison) {
            info.typeCargaison = this._cargaison.getType();
            info.origine = this._cargaison.lieuDepart.nom;
            info.destination = this._cargaison.lieuArrivee.nom;
            info.distance = this._cargaison.distance;
        }

        if (this._dateExpedition) {
            info.dateExpedition = this._dateExpedition.toLocaleDateString('fr-FR');
        }

        if (this._dateArrivee) {
            info.dateArrivee = this._dateArrivee.toLocaleDateString('fr-FR');
        }

        return info;
    }

    static genererCode(): string {
        const timestamp = Date.now().toString().slice(-6);
        const random = Math.random().toString(36).substr(2, 4).toUpperCase();
        return `COL-${timestamp}${random}`;
    }
}
