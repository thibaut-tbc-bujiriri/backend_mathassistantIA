FROM php:8.2-cli

# Installer git, unzip et l'extension PHP zip (nécessaires pour Composer)
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    zip \
    && docker-php-ext-install zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Définir le répertoire de travail
WORKDIR /app

# Copier les fichiers de dépendances
COPY composer.json composer.lock* ./

# Installer les dépendances
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Copier le reste de l'application
COPY . .

# Copier et rendre exécutable le script de démarrage
COPY start-server.sh /app/start-server.sh
RUN chmod +x /app/start-server.sh

# Exposer le port (Railway définit PORT automatiquement)
EXPOSE 8080

# Commande de démarrage
CMD ["/app/start-server.sh"]

