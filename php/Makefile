.PHONY: clean

app-init:
	composer init --name="lmbangel/feedback-api-php" --require slim/slim:"^4.0" --no-interaction

dependencies:
	composer require slim/psr7 firebase/php-jwt vlucas/phpdotenv

serve: 
	docker-compose up --build

clean:
	rm -rf vendor composer.lock