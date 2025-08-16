<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Classe pour gérer les données JSON
class DataManager {
    private $dbFile = __DIR__ . '/../db.json';
    
    public function getData() {
        if (file_exists($this->dbFile)) {
            $content = file_get_contents($this->dbFile);
            return json_decode($content, true);
        }
        return [
            'cargaisons' => [],
            'colis' => [],
            'clients' => [],
            'gestionnaires' => [],
            'parametres' => []
        ];
    }
    
    public function saveData($data) {
        return file_put_contents($this->dbFile, json_encode($data, JSON_PRETTY_PRINT));
    }
}

$dataManager = new DataManager();
$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($action) {
        case 'login':
            handleLogin($dataManager);
            break;
            
        case 'logout':
            handleLogout();
            break;
            
        case 'get_stats':
            handleGetStats($dataManager);
            break;
            
        case 'get_recent_cargaisons':
            handleGetRecentCargaisons($dataManager);
            break;
            
        case 'get_cargaisons':
            handleGetCargaisons($dataManager);
            break;
            
        case 'get_cargaisons_ouvertes':
            handleGetCargaisonsOuvertes($dataManager);
            break;
            
        case 'create_cargaison':
            handleCreateCargaison($dataManager);
            break;
            
        case 'close_cargaison':
            handleCloseCargaison($dataManager);
            break;
            
        case 'reopen_cargaison':
            handleReopenCargaison($dataManager);
            break;
            
        case 'get_villes':
            handleGetVilles($dataManager);
            break;
            
        case 'get_colis':
            handleGetColis($dataManager);
            break;
            
        case 'create_colis':
            handleCreateColis($dataManager);
            break;
            
        case 'track_colis':
            handleTrackColis($dataManager);
            break;
            
        case 'mark_recupere':
            handleMarkRecupere($dataManager);
            break;
            
        case 'cancel_colis':
            handleCancelColis($dataManager);
            break;
            
        case 'get_route':
            handleGetRoute($dataManager);
            break;
            
        default:
            throw new Exception('Action non reconnue');
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

function handleLogin($dataManager) {
    $input = json_decode(file_get_contents('php://input'), true);
    $username = $input['username'] ?? '';
    $password = $input['password'] ?? '';
    
    $data = $dataManager->getData();
    
    foreach ($data['gestionnaires'] as $gestionnaire) {
        if ($gestionnaire['username'] === $username && $gestionnaire['password'] === $password) {
            $_SESSION['user_id'] = $gestionnaire['id'];
            $_SESSION['username'] = $gestionnaire['username'];
            
            echo json_encode([
                'success' => true,
                'message' => 'Connexion réussie'
            ]);
            return;
        }
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'Identifiants incorrects'
    ]);
}

function handleLogout() {
    session_destroy();
    header('Location: ?page=login');
}

function handleGetStats($dataManager) {
    $data = $dataManager->getData();
    
    $cargaisonsActives = count(array_filter($data['cargaisons'], function($c) {
        return $c['etat'] !== 'COMPLETE';
    }));
    
    $colisEnCours = count(array_filter($data['colis'], function($c) {
        return $c['etat'] === 'EN_COURS';
    }));
    
    $colisLivres = count(array_filter($data['colis'], function($c) {
        return in_array($c['etat'], ['ARRIVE', 'RECUPERE']);
    }));
    
    $colisEnRetard = 0; // TODO: implémenter la logique de retard
    
    echo json_encode([
        'success' => true,
        'data' => [
            'cargaisonsActives' => $cargaisonsActives,
            'colisEnCours' => $colisEnCours,
            'colisLivres' => $colisLivres,
            'colisEnRetard' => $colisEnRetard
        ]
    ]);
}

function handleGetRecentCargaisons($dataManager) {
    $data = $dataManager->getData();
    $cargaisons = $data['cargaisons'];
    
    // Trier par date de création (plus récent en premier)
    usort($cargaisons, function($a, $b) {
        return strtotime($b['dateCreation']) - strtotime($a['dateCreation']);
    });
    
    // Prendre les 10 plus récentes
    $recent = array_slice($cargaisons, 0, 10);
    
    // Ajouter le nombre de colis pour chaque cargaison
    foreach ($recent as &$cargaison) {
        $cargaison['nbColis'] = count(array_filter($data['colis'], function($c) use ($cargaison) {
            return $c['cargaisonNumero'] === $cargaison['numero'];
        }));
    }
    
    echo json_encode([
        'success' => true,
        'data' => $recent
    ]);
}

function handleGetCargaisons($dataManager) {
    $data = $dataManager->getData();
    $cargaisons = $data['cargaisons'];
    
    // Ajouter le nombre de produits pour chaque cargaison
    foreach ($cargaisons as &$cargaison) {
        $cargaison['nbProduits'] = count(array_filter($data['colis'], function($c) use ($cargaison) {
            return $c['cargaisonNumero'] === $cargaison['numero'];
        }));
    }
    
    echo json_encode([
        'success' => true,
        'data' => $cargaisons
    ]);
}

function handleGetCargaisonsOuvertes($dataManager) {
    $data = $dataManager->getData();
    $cargaisonsOuvertes = array_filter($data['cargaisons'], function($c) {
        return $c['statut'] === 'OUVERTE';
    });
    
    echo json_encode([
        'success' => true,
        'data' => array_values($cargaisonsOuvertes)
    ]);
}

function handleCreateCargaison($dataManager) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $data = $dataManager->getData();
    $villes = $data['parametres']['coordonneesVilles'];
    
    // Trouver les coordonnées des villes
    $lieuDepart = null;
    $lieuArrivee = null;
    
    foreach ($villes as $ville) {
        if ($ville['nom'] === $input['lieuDepart']) {
            $lieuDepart = $ville;
        }
        if ($ville['nom'] === $input['lieuArrivee']) {
            $lieuArrivee = $ville;
        }
    }
    
    if (!$lieuDepart || !$lieuArrivee) {
        throw new Exception('Lieux de départ ou d\'arrivée introuvables');
    }
    
    // Générer un numéro unique
    $numero = 'CAR-' . substr(time(), -6) . strtoupper(substr(uniqid(), -3));
    
    $cargaison = [
        'numero' => $numero,
        'type' => $input['type'],
        'poidsMax' => $input['poidsMax'],
        'distance' => $input['distance'],
        'lieuDepart' => $lieuDepart,
        'lieuArrivee' => $lieuArrivee,
        'etat' => 'EN_ATTENTE',
        'statut' => 'OUVERTE',
        'dateCreation' => date('c'),
        'dateDepart' => null,
        'dateArrivee' => null,
        'prixTotal' => 0,
        'produits' => []
    ];
    
    $data['cargaisons'][] = $cargaison;
    $dataManager->saveData($data);
    
    echo json_encode([
        'success' => true,
        'data' => $cargaison,
        'message' => 'Cargaison créée avec succès'
    ]);
}

function handleCloseCargaison($dataManager) {
    $input = json_decode(file_get_contents('php://input'), true);
    $numero = $input['numero'];
    
    $data = $dataManager->getData();
    
    foreach ($data['cargaisons'] as &$cargaison) {
        if ($cargaison['numero'] === $numero) {
            if ($cargaison['statut'] === 'FERMEE') {
                throw new Exception('La cargaison est déjà fermée');
            }
            
            $cargaison['statut'] = 'FERMEE';
            $dataManager->saveData($data);
            
            echo json_encode([
                'success' => true,
                'message' => 'Cargaison fermée avec succès'
            ]);
            return;
        }
    }
    
    throw new Exception('Cargaison non trouvée');
}

function handleReopenCargaison($dataManager) {
    $input = json_decode(file_get_contents('php://input'), true);
    $numero = $input['numero'];
    
    $data = $dataManager->getData();
    
    foreach ($data['cargaisons'] as &$cargaison) {
        if ($cargaison['numero'] === $numero) {
            if ($cargaison['statut'] === 'OUVERTE') {
                throw new Exception('La cargaison est déjà ouverte');
            }
            
            if ($cargaison['etat'] !== 'EN_ATTENTE') {
                throw new Exception('Impossible de rouvrir : la cargaison n\'est plus en attente');
            }
            
            $cargaison['statut'] = 'OUVERTE';
            $cargaison['prixTotal'] *= 2; // Doubler le prix
            $dataManager->saveData($data);
            
            echo json_encode([
                'success' => true,
                'message' => 'Cargaison rouverte avec succès. Le coût a été doublé.'
            ]);
            return;
        }
    }
    
    throw new Exception('Cargaison non trouvée');
}

function handleGetVilles($dataManager) {
    $data = $dataManager->getData();
    
    echo json_encode([
        'success' => true,
        'data' => $data['parametres']['coordonneesVilles']
    ]);
}

function handleGetColis($dataManager) {
    $data = $dataManager->getData();
    
    echo json_encode([
        'success' => true,
        'data' => $data['colis']
    ]);
}

function handleCreateColis($dataManager) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $data = $dataManager->getData();
    
    // Générer des IDs pour les clients s'ils n'existent pas
    $expediteurId = 'CLI-' . substr(time(), -6) . strtoupper(substr(uniqid(), -4, 4));
    $destinataireId = 'CLI-' . substr(time(), -5) . strtoupper(substr(uniqid(), -4, 4));
    
    $expediteur = $input['expediteur'];
    $expediteur['id'] = $expediteurId;
    
    $destinataire = $input['destinataire'];
    $destinataire['id'] = $destinataireId;
    
    // Ajouter les clients s'ils n'existent pas
    $data['clients'][] = $expediteur;
    $data['clients'][] = $destinataire;
    
    // Générer le code du colis
    $code = 'COL-' . substr(time(), -6) . strtoupper(substr(uniqid(), -6, 6));
    
    // Calculer le prix (minimum 10000)
    $prixBase = 0;
    foreach ($input['produits'] as $produit) {
        $prixBase += $produit['poids'] * 100; // Prix de base par kg
    }
    $prix = max($prixBase, 10000);
    
    $colis = [
        'code' => $code,
        'expediteur' => $expediteur,
        'destinataire' => $destinataire,
        'produits' => $input['produits'],
        'cargaisonNumero' => $input['cargaisonNumero'] ?: null,
        'etat' => $input['cargaisonNumero'] ? 'EN_COURS' : 'EN_ATTENTE',
        'dateCreation' => date('c'),
        'dateExpedition' => $input['cargaisonNumero'] ? date('c') : null,
        'prix' => $prix
    ];
    
    // Si affecté à une cargaison, vérifier les contraintes
    if ($input['cargaisonNumero']) {
        $cargaison = null;
        foreach ($data['cargaisons'] as &$c) {
            if ($c['numero'] === $input['cargaisonNumero']) {
                $cargaison = &$c;
                break;
            }
        }
        
        if ($cargaison) {
            // Vérifier les contraintes métier
            foreach ($input['produits'] as $produit) {
                if (!peutAjouterProduit($produit, $cargaison)) {
                    throw new Exception("Le produit {$produit['libelle']} ne peut pas être ajouté à cette cargaison");
                }
            }
            
            $cargaison['prixTotal'] += $prix;
        }
    }
    
    $data['colis'][] = $colis;
    $dataManager->saveData($data);
    
    echo json_encode([
        'success' => true,
        'data' => $colis,
        'message' => 'Colis créé avec succès'
    ]);
}

function peutAjouterProduit($produit, $cargaison) {
    $typeProduit = $produit['type'];
    $typeCargaison = $cargaison['type'];
    
    // Les produits chimiques doivent toujours transiter par voie maritime
    if ($typeProduit === 'chimique' && $typeCargaison !== 'maritime') {
        return false;
    }
    
    // Les produits fragiles ne doivent jamais passer par voie maritime
    if ($typeProduit === 'fragile' && $typeCargaison === 'maritime') {
        return false;
    }
    
    return true;
}

function handleTrackColis($dataManager) {
    $code = $_GET['code'] ?? '';
    
    $data = $dataManager->getData();
    
    foreach ($data['colis'] as $colis) {
        if ($colis['code'] === $code && $colis['etat'] !== 'ANNULE') {
            $result = [
                'code' => $colis['code'],
                'etat' => $colis['etat'],
                'expediteur' => $colis['expediteur']['prenom'] . ' ' . $colis['expediteur']['nom'],
                'destinataire' => $colis['destinataire']['prenom'] . ' ' . $colis['destinataire']['nom'],
                'dateCreation' => date('d/m/Y', strtotime($colis['dateCreation']))
            ];
            
            // Si affecté à une cargaison, ajouter les infos de transport
            if ($colis['cargaisonNumero']) {
                foreach ($data['cargaisons'] as $cargaison) {
                    if ($cargaison['numero'] === $colis['cargaisonNumero']) {
                        $result['typeCargaison'] = $cargaison['type'];
                        $result['origine'] = $cargaison['lieuDepart']['nom'];
                        $result['destination'] = $cargaison['lieuArrivee']['nom'];
                        $result['distance'] = $cargaison['distance'];
                        break;
                    }
                }
            }
            
            echo json_encode([
                'success' => true,
                'data' => $result
            ]);
            return;
        }
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'Colis non trouvé'
    ]);
}

function handleMarkRecupere($dataManager) {
    $input = json_decode(file_get_contents('php://input'), true);
    $code = $input['code'];
    
    $data = $dataManager->getData();
    
    foreach ($data['colis'] as &$colis) {
        if ($colis['code'] === $code) {
            if ($colis['etat'] === 'ARRIVE') {
                $colis['etat'] = 'RECUPERE';
                $colis['dateRecuperation'] = date('c');
                $dataManager->saveData($data);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Colis marqué comme récupéré'
                ]);
                return;
            } else {
                throw new Exception('Le colis n\'est pas encore arrivé');
            }
        }
    }
    
    throw new Exception('Colis non trouvé');
}

function handleCancelColis($dataManager) {
    $input = json_decode(file_get_contents('php://input'), true);
    $code = $input['code'];
    
    $data = $dataManager->getData();
    
    foreach ($data['colis'] as &$colis) {
        if ($colis['code'] === $code) {
            if ($colis['etat'] === 'EN_ATTENTE') {
                $colis['etat'] = 'ANNULE';
                $dataManager->saveData($data);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Colis annulé'
                ]);
                return;
            } else {
                throw new Exception('Le colis ne peut plus être annulé');
            }
        }
    }
    
    throw new Exception('Colis non trouvé');
}

function handleGetRoute($dataManager) {
    $origine = $_GET['origine'] ?? '';
    $destination = $_GET['destination'] ?? '';
    $type = $_GET['type'] ?? '';
    
    $data = $dataManager->getData();
    $villes = $data['parametres']['coordonneesVilles'];
    
    $villeOrigine = null;
    $villeDestination = null;
    
    foreach ($villes as $ville) {
        if ($ville['nom'] === $origine) {
            $villeOrigine = $ville;
        }
        if ($ville['nom'] === $destination) {
            $villeDestination = $ville;
        }
    }
    
    if (!$villeOrigine || !$villeDestination) {
        throw new Exception('Villes non trouvées');
    }
    
    // Générer une route simple (ligne droite avec quelques points intermédiaires)
    $route = [$villeOrigine];
    
    // Ajouter des points intermédiaires selon le type de transport
    if ($type === 'maritime') {
        // Route maritime avec escales potentielles
        $route[] = ['latitude' => ($villeOrigine['latitude'] + $villeDestination['latitude']) / 2, 'longitude' => ($villeOrigine['longitude'] + $villeDestination['longitude']) / 2];
    } elseif ($type === 'aerienne') {
        // Route aérienne directe avec arc de cercle
        $midLat = ($villeOrigine['latitude'] + $villeDestination['latitude']) / 2;
        $midLng = ($villeOrigine['longitude'] + $villeDestination['longitude']) / 2;
        $route[] = ['latitude' => $midLat + 2, 'longitude' => $midLng];
    } else {
        // Route routière avec points de passage
        $steps = 3;
        for ($i = 1; $i < $steps; $i++) {
            $ratio = $i / $steps;
            $lat = $villeOrigine['latitude'] + ($villeDestination['latitude'] - $villeOrigine['latitude']) * $ratio;
            $lng = $villeOrigine['longitude'] + ($villeDestination['longitude'] - $villeOrigine['longitude']) * $ratio;
            $route[] = ['latitude' => $lat, 'longitude' => $lng];
        }
    }
    
    $route[] = $villeDestination;
    
    echo json_encode([
        'success' => true,
        'data' => [
            'origine' => $villeOrigine,
            'destination' => $villeDestination,
            'route' => $route,
            'type' => $type
        ]
    ]);
}
?>
