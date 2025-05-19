# docker related variables
DOCKER_COMPOSE = docker-compose
DOCKER_SERVICES = app nginx mysql-db
CONTAINER_NAME = app

# targets
.PHONY: build up down restart migrate clear-cache install-scraper scrape

# build Docker containers (without cache)
build:
	$(DOCKER_COMPOSE) build --no-cache

# start Docker containers in the background
up:
	$(DOCKER_COMPOSE) up -d --build

# stop and remove Docker containers
down:
	$(DOCKER_COMPOSE) down --remove-orphans --volumes

# restart the Docker containers
restart:
	$(DOCKER_COMPOSE) restart

# run database migrations
migrate:
	$(DOCKER_COMPOSE) exec $(CONTAINER_NAME) php artisan migrate

# clear Laravel caches (config, routes, views)
clear-cache:
	$(DOCKER_COMPOSE) exec $(CONTAINER_NAME) php artisan config:clear
	$(DOCKER_COMPOSE) exec $(CONTAINER_NAME) php artisan route:clear
	$(DOCKER_COMPOSE) exec $(CONTAINER_NAME) php artisan view:clear

# install the required Python packages inside the container
install-scraper:
	$(DOCKER_COMPOSE) exec $(CONTAINER_NAME) /bin/sh /var/www/setup_venv.sh

# run the Python scraper script inside the container
scrape:
	$(DOCKER_COMPOSE) exec $(CONTAINER_NAME) php artisan scrape:vitkac

# install Composer dependencies inside the container
composer-install:
	$(DOCKER_COMPOSE) exec $(CONTAINER_NAME) composer install --no-dev --optimize-autoloader
