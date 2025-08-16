# GP du Monde - Application de Gestion de Cargaisons

Une application complète de gestion de transport de colis par cargaisons maritimes, aériennes et routières.

## 📋 Description

GP du Monde est une entreprise de transport de colis mondial qui souhaite avoir une application de gestion de ses cargaisons. L'application permet de gérer les différents types de transport (maritime, aérien, routier) avec leurs contraintes métier spécifiques.

## 🎯 Fonctionnalités

### Core Business
- **Gestion des Cargaisons** : Création, fermeture, réouverture de cargaisons
- **Gestion des Colis** : Enregistrement, affectation, suivi des colis clients  
- **Types de Produits** : Alimentaire, Chimique, Fragile, Incassable
- **Contraintes Métier** : Validation automatique des règles de transport

### Interface Gestionnaire
- **Tableau de Bord** : Vue d'ensemble avec statistiques en temps réel
- **Gestion Cargaisons** : Interface complète avec cartes interactives
- **Gestion Colis** : Enregistrement client et affectation intelligente
- **Gestion Clients** : Base de données des expéditeurs/destinataires
- **Suivi Avancé** : Interface gestionnaire pour le monitoring

### Interface Client  
- **Suivi Colis** : Recherche par code avec géolocalisation
- **Cartes Interactives** : Visualisation des itinéraires selon le transport
- **Informations Temps Réel** : État, localisation, estimation d'arrivée

## 🏗️ Architecture

### Backend
- **json-server** : API REST automatique depuis db.json
- **PHP** : Authentification et routage des pages  
- **JSON** : Base de données fichier (db.json)
- **TypeScript** : Classes métier et logique business

### Frontend  
- **HTML/CSS/JavaScript** : Interface responsive
- **Leaflet** : Cartes interactives avec itinéraires
- **Font Awesome** : Iconographie cohérente

### Classes TypeScript

#### Produits (Abstraite)
```typescript
- Alimentaire : Libellé, Poids
- Chimique : Libellé, Poids, Degré toxicité (1-9) 
- Matériel (Abstraite)
  - Fragile : Libellé, Poids
  - Incassable : Libellé, Poids
```

#### Cargaisons (Abstraite)
```typescript
- Maritime : Contraintes chimiques obligatoires, fragiles interdits
- Aérienne : Chimiques interdits, fragiles autorisés  
- Routière : Chimiques interdits, autres produits autorisés
```

## 🔧 Installation

### Prérequis
- **Node.js 16+** pour json-server et TypeScript
- **PHP 7.4+** pour l'interface web
- **Navigateur moderne** avec support ES6+

### Configuration

1. **Cloner le projet**
```bash
cd /votre/dossier/web/
git clone [url-du-projet] GP-Du-Monde
cd GP-Du-Monde
```

2. **Installer les dépendances**
```bash
npm install
npm install -g json-server
```

3. **Compiler TypeScript**
```bash
npm run build
```

4. **Configuration serveur web**

**Apache (.htaccess déjà inclus)**
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

**Nginx**
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

5. **Permissions fichiers**
```bash
chmod 666 db.json
chmod 755 assets/
```

## 🚀 Utilisation

### Accès Gestionnaire
- URL : `http://localhost/GP-Du-Monde/`
- Login : `admin` / Mot de passe : `admin123`

### Interface Client  
- URL : `http://localhost/GP-Du-Monde/?page=client`
- Code test : `COL-123456`

### Pages Disponibles
- **Dashboard** : `/` - Vue d'ensemble
- **Cargaisons** : `/?page=cargaisons` - Gestion complète  
- **Colis** : `/?page=colis` - Enregistrement et suivi
- **Clients** : `/?page=clients` - Base clients
- **Suivi** : `/?page=suivi` - Interface monitoring
- **Client** : `/?page=client` - Interface publique

## 💼 Règles Métier

### Contraintes Transport
- **Produits chimiques** → Maritime UNIQUEMENT
- **Produits fragiles** → Maritime INTERDIT
- **Capacité** : 1-10 produits maximum par cargaison
- **Prix minimum** : 10,000 F par colis

### Tarification
- **Chimique Maritime** : 100 F/kg·km
- **Chimique Aérien** : 90 F/kg·km  
- **Chimique Routier** : 300 F/kg·km
- **Alimentaire** : +5,000 F (frais chargement)

### Gestion États
- **Cargaisons** : EN_ATTENTE → EN_COURS → ARRIVÉE → COMPLETE
- **Colis** : EN_ATTENTE → EN_COURS → ARRIVÉ → RÉCUPÉRÉ
- **Réouverture** : Possible si EN_ATTENTE (coût × 2)

## 📁 Structure du Projet

```
GP-Du-Monde/
├── api/
│   └── handler.php          # API REST endpoints
├── assets/
│   ├── css/
│   │   └── style.css        # Styles principaux
│   └── js/
│       └── app.js           # JavaScript application
├── dist/                    # TypeScript compilé
├── includes/
│   ├── header.php           # Template header
│   ├── footer.php           # Template footer  
│   └── sidebar.php          # Navigation sidebar
├── pages/
│   ├── login.php            # Authentification
│   ├── dashboard.php        # Tableau de bord
│   ├── cargaisons.php       # Gestion cargaisons
│   ├── colis.php            # Gestion colis
│   ├── clients.php          # Gestion clients
│   ├── suivi.php            # Interface suivi
│   └── client.php           # Interface publique
├── src/
│   ├── models/
│   │   ├── Produit.ts       # Classes produits
│   │   ├── Cargaison.ts     # Classes cargaisons
│   │   └── Colis.ts         # Gestion colis
│   ├── services/
│   │   └── GestionnaireService.ts
│   └── test.ts              # Tests démo
├── db.json                  # Base de données JSON
├── index.php                # Point d'entrée
├── package.json             # Dépendances Node.js
├── tsconfig.json            # Configuration TypeScript
└── README.md                # Documentation
```

## 🧪 Tests

### Test Classes TypeScript
```bash
cd src/
npx ts-node test.ts
```

### Tests API (avec curl)
```bash
# Connexion
curl -X POST http://localhost/GP-Du-Monde/?page=api&action=login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin123"}'

# Suivi colis
curl "http://localhost/GP-Du-Monde/?page=api&action=track_colis&code=COL-123456"
```

## 🔍 Fonctionnalités Avancées

### Cartes Interactives
- **Leaflet.js** : Visualisation géographique
- **Itinéraires intelligents** : Selon le type de transport
- **Couleurs distinctives** : Maritime (bleu), Aérien (rouge), Routier (vert)

### Responsive Design
- **Mobile First** : Adaptatif tablette/mobile
- **Sidebar repliable** : Navigation optimisée
- **Tables responsive** : Défilement horizontal

### Validation Temps Réel
- **Contraintes métier** : Vérification automatique
- **Messages utilisateur** : Notifications contextuelles  
- **États cohérents** : Workflow logique

## 🐛 Dépannage

### Erreurs Communes

**"Cargaisons non chargées"**
```bash
# Vérifier permissions
ls -la db.json
chmod 666 db.json
```

**"TypeScript non compilé"**
```bash
npx tsc --watch
# ou
npm run build
```

**"Cartes non affichées"**
- Vérifier la connexion Internet (OpenStreetMap)
- Contrôler la console navigateur (F12)

## 📞 Support

### Données de Test
- **Gestionnaire** : admin / admin123
- **Colis test** : COL-123456, COL-789012, COL-345678
- **Villes** : Dakar, Abidjan, Paris, Bamako, Casablanca, Londres

### Structure Base de Données
Le fichier `db.json` contient :
- **cargaisons[]** : Liste des cargaisons
- **colis[]** : Liste des colis  
- **clients[]** : Base clients
- **gestionnaires[]** : Comptes admin
- **parametres{}** : Configuration système

---

## 📝 Notes Développement

Cette application implémente une architecture MVC avec :
- **Modèles** : Classes TypeScript business
- **Vues** : Templates PHP avec JavaScript
- **Contrôleur** : API handler PHP

Le système respecte les principes SOLID et utilise l'encapsulation pour toutes les propriétés des classes métier.

**Développé pour GP du Monde** - Système de gestion de transport de colis mondial.
