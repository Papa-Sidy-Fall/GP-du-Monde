"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.Routiere = exports.Aerienne = exports.Maritime = exports.Cargaison = exports.StatutCargaison = exports.EtatCargaison = void 0;
var EtatCargaison;
(function (EtatCargaison) {
    EtatCargaison["EN_ATTENTE"] = "EN_ATTENTE";
    EtatCargaison["EN_COURS"] = "EN_COURS";
    EtatCargaison["ARRIVE"] = "ARRIVE";
    EtatCargaison["COMPLETE"] = "COMPLETE";
})(EtatCargaison || (exports.EtatCargaison = EtatCargaison = {}));
var StatutCargaison;
(function (StatutCargaison) {
    StatutCargaison["OUVERTE"] = "OUVERTE";
    StatutCargaison["FERMEE"] = "FERMEE";
})(StatutCargaison || (exports.StatutCargaison = StatutCargaison = {}));
class Cargaison {
    constructor(numero, poidsMax, distance, lieuDepart, lieuArrivee) {
        this._produits = [];
        this._prixTotal = 0;
        this._etat = EtatCargaison.EN_ATTENTE;
        this._statut = StatutCargaison.OUVERTE;
        this._prixReouvrir = 0;
        this._numero = numero;
        this._poidsMax = poidsMax;
        this._distance = distance;
        this._lieuDepart = lieuDepart;
        this._lieuArrivee = lieuArrivee;
        this._dateCreation = new Date();
    }
    // Getters
    get numero() { return this._numero; }
    get poidsMax() { return this._poidsMax; }
    get produits() { return this._produits; }
    get prixTotal() { return this._prixTotal; }
    get distance() { return this._distance; }
    get lieuDepart() { return this._lieuDepart; }
    get lieuArrivee() { return this._lieuArrivee; }
    get etat() { return this._etat; }
    get statut() { return this._statut; }
    get dateCreation() { return this._dateCreation; }
    get dateDepart() { return this._dateDepart; }
    get dateArrivee() { return this._dateArrivee; }
    // Setters
    set etat(value) { this._etat = value; }
    set dateDepart(value) { this._dateDepart = value; }
    set dateArrivee(value) { this._dateArrivee = value; }
    ajouterProduit(produit) {
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
    peutAccepterProduit(produit) {
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
    sommeTotale() {
        return this._prixTotal;
    }
    nbProduits() {
        return this._produits.length;
    }
    fermerCargaison() {
        if (this._statut === StatutCargaison.FERMEE) {
            console.log("La cargaison est déjà fermée");
            return;
        }
        this._statut = StatutCargaison.FERMEE;
        this._prixReouvrir = this._prixTotal * 2; // Le double du prix pour rouvrir
        console.log(`Cargaison ${this._numero} fermée`);
    }
    rouvrirCargaison() {
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
    getPoidsTotal() {
        return this._produits.reduce((total, produit) => total + produit.poids, 0);
    }
}
exports.Cargaison = Cargaison;
class Maritime extends Cargaison {
    constructor(numero, poidsMax, distance, lieuDepart, lieuArrivee) {
        super(numero, poidsMax, distance, lieuDepart, lieuArrivee);
    }
    getType() {
        return "maritime";
    }
    calculerFrais(produit) {
        const typeProduit = produit.getType();
        let frais = 0;
        if (typeProduit === "chimique") {
            frais = 100 * produit.poids * this.distance; // 100 F/kg.km
        }
        else if (typeProduit === "alimentaire") {
            frais = 5000; // Frais de chargement uniquement
        }
        else {
            // Produits matériels incassables (fragiles interdits en maritime)
            frais = 100 * produit.poids * this.distance;
        }
        // Prix minimum de 10,000 F
        return Math.max(frais, 10000);
    }
}
exports.Maritime = Maritime;
class Aerienne extends Cargaison {
    constructor(numero, poidsMax, distance, lieuDepart, lieuArrivee) {
        super(numero, poidsMax, distance, lieuDepart, lieuArrivee);
    }
    getType() {
        return "aerienne";
    }
    calculerFrais(produit) {
        const typeProduit = produit.getType();
        let frais = 0;
        if (typeProduit === "chimique") {
            frais = 90 * produit.poids * this.distance; // 90 F/kg.km
        }
        else {
            // Autres produits
            frais = 90 * produit.poids * this.distance;
        }
        // Prix minimum de 10,000 F
        return Math.max(frais, 10000);
    }
}
exports.Aerienne = Aerienne;
class Routiere extends Cargaison {
    constructor(numero, poidsMax, distance, lieuDepart, lieuArrivee) {
        super(numero, poidsMax, distance, lieuDepart, lieuArrivee);
    }
    getType() {
        return "routiere";
    }
    calculerFrais(produit) {
        const typeProduit = produit.getType();
        let frais = 0;
        if (typeProduit === "chimique") {
            frais = 300 * produit.poids * this.distance; // 300 F/kg.km
        }
        else {
            // Autres produits
            frais = 300 * produit.poids * this.distance;
        }
        // Prix minimum de 10,000 F
        return Math.max(frais, 10000);
    }
}
exports.Routiere = Routiere;
//# sourceMappingURL=Cargaison.js.map