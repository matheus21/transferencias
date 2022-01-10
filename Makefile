CONTAINER_API=transferencias-api
PHPUNIT_PATH=/var/www/vendor/phpunit/phpunit/phpunit
PHPUNIT_XML=/var/www/phpunit.xml
TESTS_PATH=/var/www/tests

.PHONY: init down migrate seed test test-cov docs

init:
	docker-compose up -d
	cp -r .env.example .env

down:
	docker-compose down

migrate:
	docker exec -it $(CONTAINER_API) php artisan migrate

seed:
	docker exec -it $(CONTAINER_API) php artisan db:seed

test:
	docker exec -it $(CONTAINER_API) php $(PHPUNIT_PATH) --configuration $(PHPUNIT_XML) $(TESTS_PATH) --testdox

test-cov:
	docker exec -it $(CONTAINER_API) php -dxdebug.mode=coverage $(PHPUNIT_PATH) --configuration $(PHPUNIT_XML) $(TESTS_PATH) --coverage-text

docs:
	docker exec -it $(CONTAINER_API) php artisan l5-swagger:generate

