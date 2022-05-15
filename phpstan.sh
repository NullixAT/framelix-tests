#!/bin/sh -e

docker-compose exec -T phpfpm bash -c "cd /framelix && php composer.phar run phpstan"