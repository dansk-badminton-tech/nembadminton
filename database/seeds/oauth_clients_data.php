<?php
/**
 * Password-grant clients for local, Dusk and CI environments.
 * Secrets are plain text here; OAuthClientSeeder hashes them, as Passport 13 expects.
 */
$oauth_clients = array(
    array('id' => '1','owner_type' => NULL,'owner_id' => NULL,'name' => 'default','secret' => 'aeKjMBHf19Xdmg0unihf5LzY5ZnbTl4k4fbXqZfm','provider' => 'users','redirect_uris' => '["http://host.docker.internal"]','grant_types' => '["password","refresh_token"]','revoked' => '0','created_at' => '2026-02-18 18:18:44','updated_at' => '2026-02-18 18:18:44'),
    array('id' => '2','owner_type' => NULL,'owner_id' => NULL,'name' => 'ci','secret' => '8yXaQ7paKXoXpU3zNowzroor0fgvlaGviQwZGWCK','provider' => 'users','redirect_uris' => '["http://host.docker.internal:8000"]','grant_types' => '["password","refresh_token"]','revoked' => '0','created_at' => '2026-02-19 08:48:44','updated_at' => '2026-02-19 08:48:44'),
    array('id' => '3','owner_type' => NULL,'owner_id' => NULL,'name' => 'local','secret' => 'RVRQ6939ohNbhj3u46iBhgolvc0iIAldUjEzvbHf','provider' => 'users','redirect_uris' => '["http://localhost"]','grant_types' => '["password","refresh_token"]','revoked' => '0','created_at' => '2026-02-19 08:48:44','updated_at' => '2026-02-19 08:48:44')
);

return $oauth_clients;
