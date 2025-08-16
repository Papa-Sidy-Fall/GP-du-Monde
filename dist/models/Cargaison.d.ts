import { Produit } from './Produit';
export interface Coordonnee {
    latitude: number;
    longitude: number;
    nom: string;
}
export declare enum EtatCargaison {
    EN_ATTENTE = "EN_ATTENTE",
    EN_COURS = "EN_COURS",
    ARRIVE = "ARRIVE",
    COMPLETE = "COMPLETE"
}
export declare enum StatutCargaison {
    OUVERTE = "OUVERTE",
    FERMEE = "FERMEE"
}
export declare abstract class Cargaison {
    private _numero;
    private _poidsMax;
    private _produits;
    private _prixTotal;
    private _distance;
    private _lieuDepart;
    private _lieuArrivee;
    private _etat;
    private _statut;
    private _dateCreation;
    private _dateDepart?;
    private _dateArrivee?;
    private _prixReouvrir;
    constructor(numero: string, poidsMax: number, distance: number, lieuDepart: Coordonnee, lieuArrivee: Coordonnee);
    get numero(): string;
    get poidsMax(): number;
    get produits(): Produit[];
    get prixTotal(): number;
    get distance(): number;
    get lieuDepart(): Coordonnee;
    get lieuArrivee(): Coordonnee;
    get etat(): EtatCargaison;
    get statut(): StatutCargaison;
    get dateCreation(): Date;
    get dateDepart(): Date | undefined;
    get dateArrivee(): Date | undefined;
    set etat(value: EtatCargaison);
    set dateDepart(value: Date | undefined);
    set dateArrivee(value: Date | undefined);
    abstract getType(): string;
    abstract calculerFrais(produit: Produit): number;
    ajouterProduit(produit: Produit): boolean;
    private peutAccepterProduit;
    sommeTotale(): number;
    nbProduits(): number;
    fermerCargaison(): void;
    rouvrirCargaison(): boolean;
    getPoidsTotal(): number;
}
export declare class Maritime extends Cargaison {
    constructor(numero: string, poidsMax: number, distance: number, lieuDepart: Coordonnee, lieuArrivee: Coordonnee);
    getType(): string;
    calculerFrais(produit: Produit): number;
}
export declare class Aerienne extends Cargaison {
    constructor(numero: string, poidsMax: number, distance: number, lieuDepart: Coordonnee, lieuArrivee: Coordonnee);
    getType(): string;
    calculerFrais(produit: Produit): number;
}
export declare class Routiere extends Cargaison {
    constructor(numero: string, poidsMax: number, distance: number, lieuDepart: Coordonnee, lieuArrivee: Coordonnee);
    getType(): string;
    calculerFrais(produit: Produit): number;
}
//# sourceMappingURL=Cargaison.d.ts.map