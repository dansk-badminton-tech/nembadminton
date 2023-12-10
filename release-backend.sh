#!/bin/bash

set -o errexit
set -o pipefail
set -o nounset

echo "Deploying backend"
ssh badminton.social@linux368.unoeuro.com bash <<EOF
cd /var/www/badminton.social/projects/holdkamp
git pull
EOF

ssh badminton.social@linux368.unoeuro.com bash <<EOF
cd /var/www/badminton.social/projects/holdkamp
php81 /usr/local/bin/composer install --ignore-platform-reqs
EOF

ssh badminton.social@linux368.unoeuro.com php81 /var/www/badminton.social/projects/holdkamp/artisan migrate --force
ssh badminton.social@linux368.unoeuro.com php81 /var/www/badminton.social/projects/holdkamp/artisan lighthouse:clear-cache
badminton.social@linux368.unoeuro.com php81 /var/www/badminton.social/projects/holdkamp/artisan lighthouse:cache
