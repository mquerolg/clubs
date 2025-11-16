git checkout master_release
git stash clear
git stash
git fetch origin
git reset --hard origin/master_release
git pull
php -d memory_limit=-1 /usr/bin/composer dump-env env
php74 -d memory_limit=-1 /usr/bin/composer install 
php74 -f bin/console cache:clear --no-warmup -e prod
chown -R apache:apache ./
