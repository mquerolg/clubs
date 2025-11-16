sudo git checkout dvol_release
sudo git stash clear
sudo git stash
sudo git fetch origin
sudo git reset --hard origin/dvol_release	
sudo git pull
sudo php -d memory_limit=-1 /usr/bin/composer dump-env env
sudo php74 -d memory_limit=-1 /usr/bin/composer install 
sudo php74 -f bin/console cache:clear --no-warmup -e dev
sudo chown -R apache:apache ./
