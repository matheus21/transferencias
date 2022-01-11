PHPUNIT_PATH=/var/www/vendor/phpunit/phpunit/phpunit
PHPUNIT_XML=/var/www/phpunit.xml
TESTS_PATH=/var/www/tests
DOCKER_EXEC=docker exec -it transferencias-api

.PHONY: init down migrate seed test test-cov mutation docs

init:
	docker-compose up -d
	cp -r .env.example .env

down:
	docker-compose down

migrate:
	@$(DOCKER_EXEC) php artisan migrate

seed:
	@$(DOCKER_EXEC) php artisan db:seed

test:
	@$(DOCKER_EXEC) php $(PHPUNIT_PATH) --configuration $(PHPUNIT_XML) $(TESTS_PATH) --testdox

test-cov:
	@$(DOCKER_EXEC) php $(PHPUNIT_PATH) --configuration $(PHPUNIT_XML) $(TESTS_PATH) --coverage-text

mutation:
	@$(DOCKER_EXEC) infection --only-covered

docs:
	@$(DOCKER_EXEC) php artisan l5-swagger:generate

