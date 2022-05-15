#!/bin/sh -e

# fix corrupt db in case tests was interrupted
docker-compose exec -T db bash -c "mysql -u app -papp -e 'create database if not exists app'"

# run php unit
docker-compose exec -T phpfpm bash -c "cd /framelix && cp /framelix/config/php.ini /opt/bitnami/php/etc/conf.d/xdebug.ini && php vendor/phpunit/phpunit/phpunit --coverage-clover clover.xml --bootstrap modules/FramelixTests/tests/_bootstrap.php --configuration  modules/FramelixTests/tests/_phpunit.xml" &
while kill -0 $! > /dev/null 2>&1; do
  free -m
  sleep 1
done