<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class CreateDatabaseSchema extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:schema:create
        {--connection= : Connection name from database.php}
        {--database= : Database name to create (defaults to DB_DATABASE)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create the MySQL database (schema) if it does not exist';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $connectionName = $this->option('connection') ?: config('database.default');
        $connection = config("database.connections.{$connectionName}");

        if (!is_array($connection)) {
            $this->error("Database connection [{$connectionName}] not found.");
            return self::FAILURE;
        }

        if (($connection['driver'] ?? null) !== 'mysql') {
            $this->error('This command only supports the MySQL driver.');
            return self::FAILURE;
        }

        $database = $this->option('database') ?: ($connection['database'] ?? null);
        if (!$database) {
            $this->error('DB_DATABASE is not set.');
            return self::FAILURE;
        }

        $tempConnectionName = 'schema_creator';
        $tempConnection = $connection;
        $tempConnection['database'] = null;

        Config::set("database.connections.{$tempConnectionName}", $tempConnection);
        DB::purge($tempConnectionName);

        $schema = str_replace('`', '``', $database);
        $charset = $connection['charset'] ?? 'utf8mb4';
        $collation = $connection['collation'] ?? 'utf8mb4_unicode_ci';

        try {
            DB::connection($tempConnectionName)->statement(
                "CREATE DATABASE IF NOT EXISTS `{$schema}` CHARACTER SET {$charset} COLLATE {$collation}"
            );
        } catch (\Throwable $e) {
            $this->error('Failed to create database: ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->info("Database [{$database}] is ready.");
        return self::SUCCESS;
    }
}
