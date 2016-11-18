mysql -u root -p123 -e "DROP DATABASE IF EXISTS simple_mapper_test"
mysql -u root -p123 -e "CREATE DATABASE simple_mapper_test"
mysql -u root -p123 simple_mapper_test < tests/Data/structure.sql

php vendor/bin/phpunit