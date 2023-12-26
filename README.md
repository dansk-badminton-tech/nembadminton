# Nembadminton

[nembadminton](https://nembadminton.dk) udviklet som et bidrag til badmintonsporten i Danmark for at gøre det nemmere at være træner og frivillig.

## Get started
Kør følgende kommandoer i din terminal
```
docker-compose run --rm composer install
cp .env.example .env
sudo chown -R $USER:33 storage
sudo chmod -R g+w storage
docker-compose up -d app
docker-compose run --rm artisan key:generate
docker-compose run --rm artisan migrate
docker-compose run --rm artisan badmintonplayer-import:clubs
yarn install
yarn run dev
```
Opsætning af auth via password
```
docker-compose run --rm artisan passport:keys
docker-compose run --rm artisan passport:client --password

Ændre i .env, variablerne PASSPORT_CLIENT_ID og PASSPORT_CLIENT_SECRET
```

Opret en bruger `http://localhost/new-user`

## Projekt management

**Forslår en feature:** https://github.com/dansk-badminton-tech/nembadminton/issues/new/choose

**Fundet en fejl?:** https://github.com/dansk-badminton-tech/nembadminton/issues/new/choose

**Project:** https://github.com/dansk-badminton-tech/nembadminton/projects/2
