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
