#!/bin/bash

set -o errexit
set -o pipefail
set -o nounset

read -sp 'badminton.social@linux368.unoeuro.com SSH password: ' SSH_PASS
echo ""

sshpass -p "${SSH_PASS}" ssh badminton.social@linux368.unoeuro.com echo "Testing SSH password"

echo "Deploying backend"
sshpass -p "${SSH_PASS}" ssh badminton.social@linux368.unoeuro.com bash <<EOF
cd /var/www/badminton.social/projects/holdkamp
git pull
EOF

sshpass -p "${SSH_PASS}" ssh badminton.social@linux368.unoeuro.com bash <<EOF
cd /var/www/badminton.social/projects/holdkamp
php81 /usr/local/bin/composer install --ignore-platform-reqs
EOF

sshpass -p "${SSH_PASS}" ssh badminton.social@linux368.unoeuro.com php81 /var/www/badminton.social/projects/holdkamp/artisan migrate --force
sshpass -p "${SSH_PASS}" ssh badminton.social@linux368.unoeuro.com php81 /var/www/badminton.social/projects/holdkamp/artisan lighthouse:clear-cache
sshpass -p "${SSH_PASS}" ssh badminton.social@linux368.unoeuro.com php81 /var/www/badminton.social/projects/holdkamp/artisan lighthouse:cache

echo "Building worker image"
docker build --pull -t ghcr.io/dansk-badminton-tech/nembadminton:latest .
docker push ghcr.io/dansk-badminton-tech/nembadminton:latest

echo "Updating worker"
ssh root@185.134.28.88 podman pull --authfile /root/.podmanauth ghcr.io/dansk-badminton-tech/nembadminton:latest
ssh root@185.134.28.88 podman rm --force "worker"
ssh root@185.134.28.88 podman run -d --name "worker" --env-file .env ghcr.io/dansk-badminton-tech/nembadminton:latest php -d memory_limit=256M artisan -vvv queue:listen --memory 256 --timeout 300
ssh root@185.134.28.88 podman container prune
ssh root@185.134.28.88 podman image prune
