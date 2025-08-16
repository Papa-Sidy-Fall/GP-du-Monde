#!/bin/bash

echo "🚀 Démarrage de GP du Monde avec json-server"

# Arrêter les serveurs existants
pkill -f "json-server"
pkill -f "php.*localhost:8000"

# Démarrer json-server en arrière-plan
echo "📡 Démarrage json-server sur http://localhost:3001"
json-server --watch db.json --port 3001 --host 0.0.0.0 &
JSON_PID=$!

# Attendre que json-server soit prêt
sleep 2

# Démarrer le serveur PHP
echo "🐘 Démarrage serveur PHP sur http://localhost:8000"
echo ""
echo "✅ Application prête !"
echo "📱 Interface Gestionnaire : http://localhost:8000"
echo "👥 Interface Client : http://localhost:8000/?page=client"
echo "🔐 Login : admin / admin123"
echo "📦 Code test : COL-123456"
echo ""
echo "💡 Arrêt avec Ctrl+C"

# Fonction pour arrêter proprement
cleanup() {
    echo ""
    echo "🛑 Arrêt des serveurs..."
    kill $JSON_PID 2>/dev/null
    pkill -f "php.*localhost:8000" 2>/dev/null
    exit 0
}

trap cleanup SIGINT

# Démarrer PHP et attendre
php -S localhost:8000 -t .
