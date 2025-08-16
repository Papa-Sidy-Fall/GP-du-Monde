// Classes abstraites et concrètes pour les produits/colis

export abstract class Produit {
    private _libelle: string;
    private _poids: number;

    constructor(libelle: string, poids: number) {
        this._libelle = libelle;
        this._poids = poids;
    }

    get libelle(): string {
        return this._libelle;
    }

    set libelle(value: string) {
        this._libelle = value;
    }

    get poids(): number {
        return this._poids;
    }

    set poids(value: number) {
        this._poids = value;
    }

    abstract info(): string;
    abstract getType(): string;
}

export class Alimentaire extends Produit {
    constructor(libelle: string, poids: number) {
        super(libelle, poids);
    }

    info(): string {
        return `Produit Alimentaire - ${this.libelle} (${this.poids} kg)`;
    }

    getType(): string {
        return "alimentaire";
    }
}

export class Chimique extends Produit {
    private _degreToxicite: number;

    constructor(libelle: string, poids: number, degreToxicite: number) {
        super(libelle, poids);
        if (degreToxicite < 1 || degreToxicite > 9) {
            throw new Error("Le degré de toxicité doit être entre 1 et 9");
        }
        this._degreToxicite = degreToxicite;
    }

    get degreToxicite(): number {
        return this._degreToxicite;
    }

    set degreToxicite(value: number) {
        if (value < 1 || value > 9) {
            throw new Error("Le degré de toxicité doit être entre 1 et 9");
        }
        this._degreToxicite = value;
    }

    info(): string {
        return `Produit Chimique - ${this.libelle} (${this.poids} kg, toxicité: ${this._degreToxicite})`;
    }

    getType(): string {
        return "chimique";
    }
}

export abstract class Materiel extends Produit {
    constructor(libelle: string, poids: number) {
        super(libelle, poids);
    }
}

export class Fragile extends Materiel {
    constructor(libelle: string, poids: number) {
        super(libelle, poids);
    }

    info(): string {
        return `Produit Matériel Fragile - ${this.libelle} (${this.poids} kg)`;
    }

    getType(): string {
        return "fragile";
    }
}

export class Incassable extends Materiel {
    constructor(libelle: string, poids: number) {
        super(libelle, poids);
    }

    info(): string {
        return `Produit Matériel Incassable - ${this.libelle} (${this.poids} kg)`;
    }

    getType(): string {
        return "incassable";
    }
}
