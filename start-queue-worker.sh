#!/bin/bash

# Script pour démarrer le worker de queue Laravel
# Ce script redémarre automatiquement le worker s'il s'arrête

echo "🚀 Démarrage du worker de queue Laravel..."
echo "📍 Répertoire: $(pwd)"
echo "⏰ $(date)"
echo ""

# Fonction pour nettoyer à la sortie
cleanup() {
    echo ""
    echo "🛑 Arrêt du worker de queue..."
    exit 0
}

# Capture les signaux d'arrêt
trap cleanup SIGTERM SIGINT

# Boucle infinie pour redémarrer le worker automatiquement
while true; do
    echo "▶️  Lancement du worker..."
    
    # Démarrer le worker avec options optimisées
    php artisan queue:work \
        --verbose \
        --tries=3 \
        --timeout=1800 \
        --memory=512 \
        --sleep=3 \
        --max-jobs=100 \
        --max-time=3600
    
    # Si le worker s'arrête, attendre 5 secondes avant de redémarrer
    echo "⚠️  Worker arrêté. Redémarrage dans 5 secondes..."
    sleep 5
done