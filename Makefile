.PHONY: deploy install start stop

deploy:
	./bin/deploy
	ssh -A fdm 'cd domains/ousseine.site/public_html/localProximity.site && composer2 install --no-dev --optimize-autoloader && composer2 dump-env prod && touch vendor/autoload.php && php bin/console d:m:m -n && php bin/console tailwind:build --minify && php bin/console importmap:install && php bin/console asset-map:compile && APP_ENV=prod APP_DEBUG=0 php bin/console cache:clear'

install:
	composer install
	symfony console importmap:install
	docker compose up -d
	symfony console doctrine:migration
	symfony console doctrine:migrations:migrate -n

start:
	docker compose start
	symfony serve -d
	php bin/console tailwind:build --watch

stop:
	docker compose stop
	symfony server:stop
