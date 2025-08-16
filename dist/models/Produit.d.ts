export declare abstract class Produit {
    private _libelle;
    private _poids;
    constructor(libelle: string, poids: number);
    get libelle(): string;
    set libelle(value: string);
    get poids(): number;
    set poids(value: number);
    abstract info(): string;
    abstract getType(): string;
}
export declare class Alimentaire extends Produit {
    constructor(libelle: string, poids: number);
    info(): string;
    getType(): string;
}
export declare class Chimique extends Produit {
    private _degreToxicite;
    constructor(libelle: string, poids: number, degreToxicite: number);
    get degreToxicite(): number;
    set degreToxicite(value: number);
    info(): string;
    getType(): string;
}
export declare abstract class Materiel extends Produit {
    constructor(libelle: string, poids: number);
}
export declare class Fragile extends Materiel {
    constructor(libelle: string, poids: number);
    info(): string;
    getType(): string;
}
export declare class Incassable extends Materiel {
    constructor(libelle: string, poids: number);
    info(): string;
    getType(): string;
}
//# sourceMappingURL=Produit.d.ts.map