"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.Colis = exports.EtatColis = void 0;
var EtatColis;
(function (EtatColis) {
    EtatColis["EN_ATTENTE"] = "EN_ATTENTE";
    EtatColis["EN_COURS"] = "EN_COURS";
    EtatColis["ARRIVE"] = "ARRIVE";
    EtatColis["RECUPERE"] = "RECUPERE";
    EtatColis["PERDU"] = "PERDU";
    EtatColis["ARCHIVE"] = "ARCHIVE";
    EtatColis["ANNULE"] = "ANNULE";
})(EtatColis || (exports.EtatColis = EtatColis = {}));
class Colis {
    constructor(code, expediteur, destinataire, produits) {
        this._etat = EtatColis.EN_ATTENTE;
        this._prix = 0;
        this._code = code;
        this._expediteur = expediteur;
        this._destinataire = destinataire;
        this._produits = produits;
        this._dateCreation = new Date();
    }
    // Getters
    get code() { return this._code; }
    get expediteur() { return this._expediteur; }
    get destinataire() { return this._destinataire; }
    get produits() { return this._produits; }
    get cargaison() { return this._cargaison; }
    get etat() { return this._etat; }
    get dateCreation() { return this._dateCreation; }
    get dateExpedition() { return this._dateExpedition; }
    get dateArrivee() { return this._dateArrivee; }
    get dateRecuperation() { return this._dateRecuperation; }
    get prix() { return this._prix; }
    // Setters
    set cargaison(value) { this._cargaison = value; }
    set etat(value) { this._etat = value; }
    set dateExpedition(value) { this._dateExpedition = value; }
    set dateArrivee(value) { this._dateArrivee = value; }
    set dateRecuperation(value) { this._dateRecuperation = value; }
    set prix(value) { this._prix = value; }
    getPoidsTotal() {
        return this._produits.reduce((total, produit) => total + produit.poids, 0);
    }
    ajouterACargaison(cargaison) {
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
            }
            else {
                return false;
            }
        }
        this._cargaison = cargaison;
        this._prix = Math.max(fraisTotal, 10000); // Prix minimum 10,000 F
        return true;
    }
    peutEtreAjouteACargaison(produit, cargaison) {
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
    marquerCommeRecupere() {
        if (this._etat === EtatColis.ARRIVE) {
            this._etat = EtatColis.RECUPERE;
            this._dateRecuperation = new Date();
        }
    }
    marquerCommePerdu() {
        this._etat = EtatColis.PERDU;
    }
    archiver() {
        if (this._etat === EtatColis.RECUPERE || this._etat === EtatColis.PERDU) {
            this._etat = EtatColis.ARCHIVE;
        }
    }
    annuler() {
        // Ne peut être annulé que si la cargaison n'est pas encore fermée
        if (this._cargaison && this._cargaison.statut === "FERMEE") {
            return false;
        }
        this._etat = EtatColis.ANNULE;
        return true;
    }
    getInfoSuivi() {
        const info = {
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
    static genererCode() {
        const timestamp = Date.now().toString().slice(-6);
        const random = Math.random().toString(36).substr(2, 4).toUpperCase();
        return `COL-${timestamp}${random}`;
    }
}
exports.Colis = Colis;
//# sourceMappingURL=Colis.js.map