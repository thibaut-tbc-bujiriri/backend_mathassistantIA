#!/bin/bash
set -e

# Récupérer le port depuis la variable d'environnement Railway
PORT=${PORT:-8080}

echo "Starting PHP server on port $PORT..."
php -S 0.0.0.0:$PORT -t api

