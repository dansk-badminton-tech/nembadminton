#!/bin/bash
set -o errexit
set -o pipefail
set -o nounset

read -sp 'badminton.social@linux368.unoeuro.com SSH password: ' SSH_PASS
echo ""

sshpass -p "${SSH_PASS}" ssh badminton.social@linux368.unoeuro.com echo "Testing SSH password"

echo "Building frontend"
yarn install --frozen-lockfile --no-progress
yarn run prod

echo "Deploying frontend"
sshpass -p "${SSH_PASS}" ssh badminton.social@linux368.unoeuro.com rm -rf /var/www/badminton.social/projects/holdkamp/public/css
sshpass -p "${SSH_PASS}" scp -r public/css badminton.social@linux368.unoeuro.com:/var/www/badminton.social/projects/holdkamp/public/css
sshpass -p "${SSH_PASS}" ssh badminton.social@linux368.unoeuro.com rm -rf /var/www/badminton.social/projects/holdkamp/public/js
sshpass -p "${SSH_PASS}" scp -r public/js badminton.social@linux368.unoeuro.com:/var/www/badminton.social/projects/holdkamp/public/js
sshpass -p "${SSH_PASS}" scp public/mix-manifest.json badminton.social@linux368.unoeuro.com:/var/www/badminton.social/projects/holdkamp/public/mix-manifest.json
