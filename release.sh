#!/bin/bash

set -o errexit
set -o pipefail
set -o nounset

read -sp 'badminton.social@linux368.unoeuro.com SSH password: ' SSH_PASS
echo ""

echo "Building frontend"
yarn install --frozen-lockfile --no-progress
yarn run prod

echo "Deploying frontend"
sshpass -p "${SSH_PASS}" ssh badminton.social@linux368.unoeuro.com rm -rf /var/www/badminton.social/projects/holdkamp/public/css
sshpass -p "${SSH_PASS}" scp -r public/css badminton.social@linux368.unoeuro.com:/var/www/badminton.social/projects/holdkamp/public/css
sshpass -p "${SSH_PASS}" ssh badminton.social@linux368.unoeuro.com rm -rf /var/www/badminton.social/projects/holdkamp/public/js
sshpass -p "${SSH_PASS}" scp -r public/js badminton.social@linux368.unoeuro.com:/var/www/badminton.social/projects/holdkamp/public/js
sshpass -p "${SSH_PASS}" scp public/mix-manifest.json badminton.social@linux368.unoeuro.com:/var/www/badminton.social/projects/holdkamp/public/mix-manifest.json

echo "Deploying backend"
sshpass -p "${SSH_PASS}" ssh badminton.social@linux368.unoeuro.com bash <<EOF
cd /var/www/badminton.social/projects/holdkamp
git pull
EOF
sshpass -p "${SSH_PASS}" ssh badminton.social@linux368.unoeuro.com php /var/www/badminton.social/projects/holdkamp/artisan migrate --force

echo "Building worker image"
docker build -t ghcr.io/flycompanytech/holdkamp:latest .
docker push ghcr.io/flycompanytech/holdkamp:latest

echo "Updating worker"
ssh root@185.134.28.88 podman pull ghcr.io/flycompanytech/holdkamp:latest
ssh root@185.134.28.88 podman rm --force "worker"
ssh root@185.134.28.88 podman run -d --name "worker" --env-file .env ghcr.io/flycompanytech/holdkamp:latest php artisan -vvv queue:listen
ssh root@185.134.28.88 podman container prune
ssh root@185.134.28.88 podman image prune
