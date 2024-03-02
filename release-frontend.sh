#!/bin/bash
set -o errexit
set -o pipefail
set -o nounset

export VITE_PUSHER_APP_KEY="BFA9mqh3bek.wtz4xub"
export VITE_PUSHER_HOST="ws.platform.nembadminton.dk"
export VITE_PUSHER_PORT="443"
export VITE_PUSHER_SCHEME="https"

echo "Building frontend"
yarn install --frozen-lockfile --no-progress
yarn run production

echo "Removing old frontend"
ssh badminton.social@linux368.unoeuro.com rm -rf /var/www/badminton.social/projects/holdkamp/public/css &
ssh badminton.social@linux368.unoeuro.com rm -rf /var/www/badminton.social/projects/holdkamp/public/js &
ssh badminton.social@linux368.unoeuro.com rm -rf /var/www/badminton.social/projects/holdkamp/public/images &
wait
echo "Copying new frontend"
scp -r public/css badminton.social@linux368.unoeuro.com:/var/www/badminton.social/projects/holdkamp/public/css &
scp -r public/js badminton.social@linux368.unoeuro.com:/var/www/badminton.social/projects/holdkamp/public/js &
scp -r public/images badminton.social@linux368.unoeuro.com:/var/www/badminton.social/projects/holdkamp/public/images &
scp public/mix-manifest.json badminton.social@linux368.unoeuro.com:/var/www/badminton.social/projects/holdkamp/public/mix-manifest.json &
wait
