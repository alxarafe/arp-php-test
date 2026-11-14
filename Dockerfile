FROM php:8.5-cli-bookworm

# DOM (including Lexbor), mbstring and XML extensions are built into PHP 8.5.
# Rebuilding DOM separately loses the bundled Lexbor build configuration.
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libzip-dev \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        zip \
    && php -r 'foreach (["pdo_mysql", "mbstring", "dom", "xml", "xmlwriter", "simplexml", "zip"] as $extension) { if (!extension_loaded($extension)) { fwrite(STDERR, "Missing extension: $extension\n"); exit(1); } }' \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /app
