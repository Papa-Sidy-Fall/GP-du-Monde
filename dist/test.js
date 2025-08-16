"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
const Produit_1 = require("./models/Produit");
const Cargaison_1 = require("./models/Cargaison");
const Colis_1 = require("./models/Colis");
// Fonction de test principale
function testApplication() {
    console.log("=== Test de l'application GP du Monde ===\n");
    // Définir les coordonnées des villes
    const dakar = {
        latitude: 14.6928,
        longitude: -17.4467,
        nom: "Dakar, Sénégal"
    };
    const abidjan = {
        latitude: 5.3600,
        longitude: -4.0083,
        nom: "Abidjan, Côte d'Ivoire"
    };
    const paris = {
        latitude: 48.8566,
        longitude: 2.3522,
        nom: "Paris, France"
    };
    console.log("1. Création des cargaisons\n");
    // Créer trois cargaisons (une de chaque type)
    const cargaisonMaritime = new Cargaison_1.Maritime("CAR-001", 5000, 1200, dakar, abidjan);
    const cargaisonAerienne = new Cargaison_1.Aerienne("CAR-002", 2000, 4200, dakar, paris);
    const cargaisonRoutiere = new Cargaison_1.Routiere("CAR-003", 8000, 800, dakar, abidjan);
    console.log(`Cargaison maritime créée: ${cargaisonMaritime.numero} (${cargaisonMaritime.lieuDepart.nom} → ${cargaisonMaritime.lieuArrivee.nom})`);
    console.log(`Cargaison aérienne créée: ${cargaisonAerienne.numero} (${cargaisonAerienne.lieuDepart.nom} → ${cargaisonAerienne.lieuArrivee.nom})`);
    console.log(`Cargaison routière créée: ${cargaisonRoutiere.numero} (${cargaisonRoutiere.lieuDepart.nom} → ${cargaisonRoutiere.lieuArrivee.nom})`);
    console.log("\n2. Création des produits\n");
    // Créer différents types de produits
    const riz = new Produit_1.Alimentaire("Riz parfumé", 25);
    const acide = new Produit_1.Chimique("Acide sulfurique", 50, 8);
    const ordinateur = new Produit_1.Fragile("Ordinateur portable", 2.5);
    const outils = new Produit_1.Incassable("Outils de menuiserie", 15);
    const fruits = new Produit_1.Alimentaire("Fruits tropicaux", 30);
    console.log("Produits créés:");
    console.log(`- ${riz.info()}`);
    console.log(`- ${acide.info()}`);
    console.log(`- ${ordinateur.info()}`);
    console.log(`- ${outils.info()}`);
    console.log(`- ${fruits.info()}`);
    console.log("\n3. Test des contraintes métier\n");
    // Test des contraintes métier
    console.log("=== Tests Cargaison Maritime ===");
    cargaisonMaritime.ajouterProduit(riz); // ✓ Alimentaire → Maritime OK
    cargaisonMaritime.ajouterProduit(acide); // ✓ Chimique → Maritime OK
    cargaisonMaritime.ajouterProduit(ordinateur); // ✗ Fragile → Maritime INTERDIT
    cargaisonMaritime.ajouterProduit(outils); // ✓ Incassable → Maritime OK
    console.log("\n=== Tests Cargaison Aérienne ===");
    cargaisonAerienne.ajouterProduit(fruits); // ✓ Alimentaire → Aérienne OK
    cargaisonAerienne.ajouterProduit(acide); // ✗ Chimique → Aérienne (doit être maritime)
    cargaisonAerienne.ajouterProduit(ordinateur); // ✓ Fragile → Aérienne OK
    console.log("\n=== Tests Cargaison Routière ===");
    const acideRoutes = new Produit_1.Chimique("Produit chimique léger", 10, 3);
    cargaisonRoutiere.ajouterProduit(acideRoutes); // ✗ Chimique → Routière (doit être maritime)
    cargaisonRoutiere.ajouterProduit(outils); // ✓ Incassable → Routière OK
    console.log("\n4. Statistiques des cargaisons\n");
    // Afficher les statistiques
    console.log(`Cargaison Maritime (${cargaisonMaritime.numero}):`);
    console.log(`  - Nombre de produits: ${cargaisonMaritime.nbProduits()}`);
    console.log(`  - Somme totale: ${cargaisonMaritime.sommeTotale().toLocaleString('fr-FR')} F`);
    console.log(`  - Poids total: ${cargaisonMaritime.getPoidsTotal()} kg`);
    console.log(`\nCargaison Aérienne (${cargaisonAerienne.numero}):`);
    console.log(`  - Nombre de produits: ${cargaisonAerienne.nbProduits()}`);
    console.log(`  - Somme totale: ${cargaisonAerienne.sommeTotale().toLocaleString('fr-FR')} F`);
    console.log(`  - Poids total: ${cargaisonAerienne.getPoidsTotal()} kg`);
    console.log(`\nCargaison Routière (${cargaisonRoutiere.numero}):`);
    console.log(`  - Nombre de produits: ${cargaisonRoutiere.nbProduits()}`);
    console.log(`  - Somme totale: ${cargaisonRoutiere.sommeTotale().toLocaleString('fr-FR')} F`);
    console.log(`  - Poids total: ${cargaisonRoutiere.getPoidsTotal()} kg`);
    console.log("\n5. Test de gestion des colis\n");
    // Créer des clients
    const expediteur = {
        id: "CLI-001",
        nom: "Dupont",
        prenom: "Jean",
        telephone: "+221701234567",
        adresse: "Plateau, Dakar",
        email: "jean.dupont@email.com"
    };
    const destinataire = {
        id: "CLI-002",
        nom: "Martin",
        prenom: "Marie",
        telephone: "+225707654321",
        adresse: "Cocody, Abidjan",
        email: "marie.martin@email.com"
    };
    // Créer des colis
    const colis1 = new Colis_1.Colis("COL-123456", expediteur, destinataire, [riz, outils]);
    const colis2 = new Colis_1.Colis("COL-789012", destinataire, expediteur, [ordinateur]);
    console.log("Colis créés:");
    console.log(`- ${colis1.code}: ${colis1.produits.length} produit(s), ${colis1.getPoidsTotal()} kg`);
    console.log(`- ${colis2.code}: ${colis2.produits.length} produit(s), ${colis2.getPoidsTotal()} kg`);
    // Affecter les colis aux cargaisons
    if (colis1.ajouterACargaison(cargaisonMaritime)) {
        console.log(`\n✓ Colis ${colis1.code} affecté à la cargaison maritime`);
    }
    if (colis2.ajouterACargaison(cargaisonAerienne)) {
        console.log(`✓ Colis ${colis2.code} affecté à la cargaison aérienne`);
    }
    console.log("\n6. Test de fermeture et réouverture de cargaison\n");
    console.log(`Prix total avant fermeture: ${cargaisonMaritime.sommeTotale().toLocaleString('fr-FR')} F`);
    // Fermer la cargaison
    cargaisonMaritime.fermerCargaison();
    // Essayer de rouvrir
    cargaisonMaritime.rouvrirCargaison();
    console.log(`Prix total après réouverture: ${cargaisonMaritime.sommeTotale().toLocaleString('fr-FR')} F`);
    console.log("\n7. Suivi des colis\n");
    // Simuler le suivi des colis
    console.log("Informations de suivi:");
    console.log(JSON.stringify(colis1.getInfoSuivi(), null, 2));
    console.log(JSON.stringify(colis2.getInfoSuivi(), null, 2));
    console.log("\n8. Test de capacité maximale\n");
    // Test de la capacité maximale (10 produits max)
    const nouvelleCargaison = new Cargaison_1.Maritime("CAR-004", 1000, 500, dakar, abidjan);
    console.log("Tentative d'ajout de 12 produits (limite: 10):");
    for (let i = 1; i <= 12; i++) {
        const produit = new Produit_1.Alimentaire(`Produit ${i}`, 5);
        const ajout = nouvelleCargaison.ajouterProduit(produit);
        if (!ajout) {
            console.log(`❌ Impossible d'ajouter le produit ${i} (cargaison pleine)`);
            break;
        }
        else {
            console.log(`✅ Produit ${i} ajouté (${nouvelleCargaison.nbProduits()}/10)`);
        }
    }
    console.log("\n=== Fin des tests ===");
}
// Exécuter les tests
testApplication();
//# sourceMappingURL=test.js.map