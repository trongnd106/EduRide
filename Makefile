ifndef env
env:=example
endif
include .env
APP_CONTAINER=${APP_NAME}_${APP_ENV}_php
conn:
	docker exec -it ${APP_CONTAINER} bash
conn-db:
	docker exec -it ${APP_NAME}_${APP_ENV}_db mysql -u${DB_USERNAME} -p${DB_PASSWORD} ${DB_DATABASE}
tinker:
	docker exec -it ${APP_NAME}_${APP_ENV}_php php artisan tinker
migrate:
	docker exec ${APP_CONTAINER} php artisan migrate
clear:
	docker exec ${APP_CONTAINER} php artisan config:clear
	docker exec ${APP_CONTAINER} php artisan cache:clear
	docker exec ${APP_CONTAINER} php artisan route:clear
	docker exec ${APP_CONTAINER} composer dump-autoload
	docker system prune -f
# Init project (just one time)
init:
	#cp ./.docker/compose/docker-compose.yml.${env} ./docker-compose.yml
	docker-compose up -d --build
	docker exec -it ${APP_CONTAINER} composer install
	make init-data
	make clear
# Restart queue
restart-queue:
	docker exec ${APP_CONTAINER} supervisorctl reread
	docker exec ${APP_CONTAINER} supervisorctl restart hust-queue-work:*
# Create new migration
migration:
ifdef f
ifdef table
	docker exec ${APP_CONTAINER} php artisan make:migration ${f} --table=${table}
else
	docker exec ${APP_CONTAINER} php artisan make:migration ${f}
endif
else
	@echo "Specify filename"
endif
install-passport:
	docker exec ${APP_CONTAINER} php artisan passport:install
	docker exec ${APP_CONTAINER} php artisan passport:client --password --provider admins --name admins
init-data:
	make migrate
	make install-passport
	make seed

seed:
ifdef f
	docker exec ${APP_CONTAINER} php artisan db:seed --class=${f}
else
	docker exec ${APP_CONTAINER} php artisan db:seed
endif

pull:
	- git pull
	make clear
	make restart-queue
up:
	docker-compose up -d --build
down:
	@echo "Shutting down application dockers..."
	- docker exec -it ${APP_CONTAINER} rm -rf psysh
	- docker exec -it ${APP_CONTAINER} rm -rf .docker/mysql/data
	docker-compose down
	docker system prune -f
	docker volume prune -f
	docker network prune -f
stop:
	docker-compose stop
start:
	docker-compose start
ps:
	docker-compose ps
build:
	git pull
	- docker exec ${APP_CONTAINER} chmod -R 777 .docker/mysql/data
	- docker exec ${APP_CONTAINER} chmod -R 777 .docker/mssql/data
	- docker exec ${APP_CONTAINER} chmod -R 777 psysh
	docker-compose up -d --build
	docker exec ${APP_CONTAINER} composer install
	make migrate
	make clear
	make restart-queue
gen-swagger:
	docker exec -it ${APP_CONTAINER} php artisan l5-swagger:generate
