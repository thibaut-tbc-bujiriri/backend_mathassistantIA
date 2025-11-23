FROM php:8.2-cli

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Définir le répertoire de travail
WORKDIR /app

# Copier les fichiers de dépendances
COPY composer.json composer.lock* ./

# Installer les dépendances
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copier le reste de l'application
COPY . .

# Exposer le port (Railway définit PORT automatiquement)
EXPOSE 8080

# Commande de démarrage
CMD php -S 0.0.0.0:$PORT router.php

