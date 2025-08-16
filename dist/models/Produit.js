"use strict";
// Classes abstraites et concrètes pour les produits/colis
Object.defineProperty(exports, "__esModule", { value: true });
exports.Incassable = exports.Fragile = exports.Materiel = exports.Chimique = exports.Alimentaire = exports.Produit = void 0;
class Produit {
    constructor(libelle, poids) {
        this._libelle = libelle;
        this._poids = poids;
    }
    get libelle() {
        return this._libelle;
    }
    set libelle(value) {
        this._libelle = value;
    }
    get poids() {
        return this._poids;
    }
    set poids(value) {
        this._poids = value;
    }
}
exports.Produit = Produit;
class Alimentaire extends Produit {
    constructor(libelle, poids) {
        super(libelle, poids);
    }
    info() {
        return `Produit Alimentaire - ${this.libelle} (${this.poids} kg)`;
    }
    getType() {
        return "alimentaire";
    }
}
exports.Alimentaire = Alimentaire;
class Chimique extends Produit {
    constructor(libelle, poids, degreToxicite) {
        super(libelle, poids);
        if (degreToxicite < 1 || degreToxicite > 9) {
            throw new Error("Le degré de toxicité doit être entre 1 et 9");
        }
        this._degreToxicite = degreToxicite;
    }
    get degreToxicite() {
        return this._degreToxicite;
    }
    set degreToxicite(value) {
        if (value < 1 || value > 9) {
            throw new Error("Le degré de toxicité doit être entre 1 et 9");
        }
        this._degreToxicite = value;
    }
    info() {
        return `Produit Chimique - ${this.libelle} (${this.poids} kg, toxicité: ${this._degreToxicite})`;
    }
    getType() {
        return "chimique";
    }
}
exports.Chimique = Chimique;
class Materiel extends Produit {
    constructor(libelle, poids) {
        super(libelle, poids);
    }
}
exports.Materiel = Materiel;
class Fragile extends Materiel {
    constructor(libelle, poids) {
        super(libelle, poids);
    }
    info() {
        return `Produit Matériel Fragile - ${this.libelle} (${this.poids} kg)`;
    }
    getType() {
        return "fragile";
    }
}
exports.Fragile = Fragile;
class Incassable extends Materiel {
    constructor(libelle, poids) {
        super(libelle, poids);
    }
    info() {
        return `Produit Matériel Incassable - ${this.libelle} (${this.poids} kg)`;
    }
    getType() {
        return "incassable";
    }
}
exports.Incassable = Incassable;
//# sourceMappingURL=Produit.js.map