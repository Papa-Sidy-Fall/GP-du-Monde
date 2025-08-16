import { Produit } from './Produit';
import { Cargaison } from './Cargaison';
export declare enum EtatColis {
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
export declare class Colis {
    private _code;
    private _expediteur;
    private _destinataire;
    private _produits;
    private _cargaison?;
    private _etat;
    private _dateCreation;
    private _dateExpedition?;
    private _dateArrivee?;
    private _dateRecuperation?;
    private _prix;
    constructor(code: string, expediteur: Client, destinataire: Client, produits: Produit[]);
    get code(): string;
    get expediteur(): Client;
    get destinataire(): Client;
    get produits(): Produit[];
    get cargaison(): Cargaison | undefined;
    get etat(): EtatColis;
    get dateCreation(): Date;
    get dateExpedition(): Date | undefined;
    get dateArrivee(): Date | undefined;
    get dateRecuperation(): Date | undefined;
    get prix(): number;
    set cargaison(value: Cargaison | undefined);
    set etat(value: EtatColis);
    set dateExpedition(value: Date | undefined);
    set dateArrivee(value: Date | undefined);
    set dateRecuperation(value: Date | undefined);
    set prix(value: number);
    getPoidsTotal(): number;
    ajouterACargaison(cargaison: Cargaison): boolean;
    private peutEtreAjouteACargaison;
    marquerCommeRecupere(): void;
    marquerCommePerdu(): void;
    archiver(): void;
    annuler(): boolean;
    getInfoSuivi(): any;
    static genererCode(): string;
}
//# sourceMappingURL=Colis.d.ts.map