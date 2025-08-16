import { Produit, Chimique, Fragile } from './Produit';

export interface Coordonnee {
    latitude: number;
    longitude: number;
    nom: string;
}

export enum EtatCargaison {
    EN_ATTENTE = "EN_ATTENTE",
    EN_COURS = "EN_COURS",
    ARRIVE = "ARRIVE",
    COMPLETE = "COMPLETE"
}

export enum StatutCargaison {
    OUVERTE = "OUVERTE",
    FERMEE = "FERMEE"
}

export abstract class Cargaison {
    private _numero: string;
    private _poidsMax: number;
    private _produits: Produit[] = [];
    private _prixTotal: number = 0;
    private _distance: number;
    private _lieuDepart: Coordonnee;
    private _lieuArrivee: Coordonnee;
    private _etat: EtatCargaison = EtatCargaison.EN_ATTENTE;
    private _statut: StatutCargaison = StatutCargaison.OUVERTE;
    private _dateCreation: Date;
    private _dateDepart?: Date;
    private _dateArrivee?: Date;
    private _prixReouvrir: number = 0;

    constructor(
        numero: string, 
        poidsMax: number, 
        distance: number, 
        lieuDepart: Coordonnee, 
        lieuArrivee: Coordonnee
    ) {
        this._numero = numero;
        this._poidsMax = poidsMax;
        this._distance = distance;
        this._lieuDepart = lieuDepart;
        this._lieuArrivee = lieuArrivee;
        this._dateCreation = new Date();
    }

    // Getters
    get numero(): string { return this._numero; }
    get poidsMax(): number { return this._poidsMax; }
    get produits(): Produit[] { return this._produits; }
    get prixTotal(): number { return this._prixTotal; }
    get distance(): number { return this._distance; }
    get lieuDepart(): Coordonnee { return this._lieuDepart; }
    get lieuArrivee(): Coordonnee { return this._lieuArrivee; }
    get etat(): EtatCargaison { return this._etat; }
    get statut(): StatutCargaison { return this._statut; }
    get dateCreation(): Date { return this._dateCreation; }
    get dateDepart(): Date | undefined { return this._dateDepart; }
    get dateArrivee(): Date | undefined { return this._dateArrivee; }

    // Setters
    set etat(value: EtatCargaison) { this._etat = value; }
    set dateDepart(value: Date | undefined) { this._dateDepart = value; }
    set dateArrivee(value: Date | undefined) { this._dateArrivee = value; }

    abstract getType(): string;
    abstract calculerFrais(produit: Produit): number;

    ajouterProduit(produit: Produit): boolean {
        // Vérifier si la cargaison est fermée
        if (this._statut === StatutCargaison.FERMEE) {
            console.log("Impossible d'ajouter un produit : cargaison fermée");
            return false;
        }

        // Vérifier si la cargaison est pleine (max 10 produits)
        if (this._produits.length >= 10) {
            console.log("Impossible d'ajouter un produit : cargaison pleine (max 10 produits)");
            return false;
        }

        // Vérifications des contraintes métier
        if (!this.peutAccepterProduit(produit)) {
            return false;
        }

        // Ajouter le produit
        this._produits.push(produit);
        const frais = this.calculerFrais(produit);
        this._prixTotal += frais;

        console.log(`Produit ajouté : ${produit.info()}`);
        console.log(`Frais de transport : ${frais} F`);
        console.log(`Montant total de la cargaison : ${this._prixTotal} F`);

        return true;
    }

    private peutAccepterProduit(produit: Produit): boolean {
        const typeProduit = produit.getType();
        const typeCargaison = this.getType();

        // Les produits chimiques doivent toujours transiter par voie maritime
        if (typeProduit === "chimique" && typeCargaison !== "maritime") {
            console.log("Impossible d'ajouter un produit chimique : doit transiter par voie maritime");
            return false;
        }

        // Les produits fragiles ne doivent jamais passer par voie maritime
        if (typeProduit === "fragile" && typeCargaison === "maritime") {
            console.log("Impossible d'ajouter un produit fragile : ne peut pas transiter par voie maritime");
            return false;
        }

        return true;
    }

    sommeTotale(): number {
        return this._prixTotal;
    }

    nbProduits(): number {
        return this._produits.length;
    }

    fermerCargaison(): void {
        if (this._statut === StatutCargaison.FERMEE) {
            console.log("La cargaison est déjà fermée");
            return;
        }

        this._statut = StatutCargaison.FERMEE;
        this._prixReouvrir = this._prixTotal * 2; // Le double du prix pour rouvrir
        console.log(`Cargaison ${this._numero} fermée`);
    }

    rouvrirCargaison(): boolean {
        if (this._statut === StatutCargaison.OUVERTE) {
            console.log("La cargaison est déjà ouverte");
            return false;
        }

        if (this._etat !== EtatCargaison.EN_ATTENTE) {
            console.log("Impossible de rouvrir : la cargaison n'est plus en attente");
            return false;
        }

        this._statut = StatutCargaison.OUVERTE;
        this._prixTotal += this._prixReouvrir; // Ajouter les frais de réouverture
        console.log(`Cargaison ${this._numero} rouverte. Frais de réouverture : ${this._prixReouvrir} F`);
        console.log(`Nouveau montant total : ${this._prixTotal} F`);
        
        return true;
    }

    getPoidsTotal(): number {
        return this._produits.reduce((total, produit) => total + produit.poids, 0);
    }
}

export class Maritime extends Cargaison {
    constructor(
        numero: string, 
        poidsMax: number, 
        distance: number, 
        lieuDepart: Coordonnee, 
        lieuArrivee: Coordonnee
    ) {
        super(numero, poidsMax, distance, lieuDepart, lieuArrivee);
    }

    getType(): string {
        return "maritime";
    }

    calculerFrais(produit: Produit): number {
        const typeProduit = produit.getType();
        let frais = 0;

        if (typeProduit === "chimique") {
            frais = 100 * produit.poids * this.distance; // 100 F/kg.km
        } else if (typeProduit === "alimentaire") {
            frais = 5000; // Frais de chargement uniquement
        } else {
            // Produits matériels incassables (fragiles interdits en maritime)
            frais = 100 * produit.poids * this.distance;
        }

        // Prix minimum de 10,000 F
        return Math.max(frais, 10000);
    }
}

export class Aerienne extends Cargaison {
    constructor(
        numero: string, 
        poidsMax: number, 
        distance: number, 
        lieuDepart: Coordonnee, 
        lieuArrivee: Coordonnee
    ) {
        super(numero, poidsMax, distance, lieuDepart, lieuArrivee);
    }

    getType(): string {
        return "aerienne";
    }

    calculerFrais(produit: Produit): number {
        const typeProduit = produit.getType();
        let frais = 0;

        if (typeProduit === "chimique") {
            frais = 90 * produit.poids * this.distance; // 90 F/kg.km
        } else {
            // Autres produits
            frais = 90 * produit.poids * this.distance;
        }

        // Prix minimum de 10,000 F
        return Math.max(frais, 10000);
    }
}

export class Routiere extends Cargaison {
    constructor(
        numero: string, 
        poidsMax: number, 
        distance: number, 
        lieuDepart: Coordonnee, 
        lieuArrivee: Coordonnee
    ) {
        super(numero, poidsMax, distance, lieuDepart, lieuArrivee);
    }

    getType(): string {
        return "routiere";
    }

    calculerFrais(produit: Produit): number {
        const typeProduit = produit.getType();
        let frais = 0;

        if (typeProduit === "chimique") {
            frais = 300 * produit.poids * this.distance; // 300 F/kg.km
        } else {
            // Autres produits
            frais = 300 * produit.poids * this.distance;
        }

        // Prix minimum de 10,000 F
        return Math.max(frais, 10000);
    }
}
