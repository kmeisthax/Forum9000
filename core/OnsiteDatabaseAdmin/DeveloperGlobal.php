<?php

namespace Forum9000\OnsiteDatabaseAdmin;

use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Doctrine\DBAL\Migrations\OutputWriter;
use Symfony\Bridge\Doctrine\RegistryInterface;

class DeveloperGlobal {
    private $doctrine;

    private $migrations_directory;
    private $migrations_namespace;
    private $migrations_name;
    private $migrations_tablename;

    public function __construct(RegistryInterface $doctrine, $migrations_directory, $migrations_namespace, $migrations_name, $migrations_tablename) {
        $this->doctrine = $doctrine;
        $this->migrations_directory = $migrations_directory;
        $this->migrations_namespace = $migrations_namespace;
        $this->migrations_name = $migrations_name;
        $this->migrations_tablename = $migrations_tablename;
    }

    private function create_migration_configuration(?\Closure $cl = null) {
        $connection = $this->doctrine->getConnection();
        $dir = $this->migrations_directory;
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        $configuration = new Configuration($connection, new OutputWriter($cl));
        $configuration->setMigrationsNamespace($this->migrations_namespace);
        $configuration->setMigrationsDirectory($dir);
        $configuration->registerMigrationsFromDirectory($dir);
        $configuration->setName($this->migrations_name);
        $configuration->setMigrationsTableName($this->migrations_tablename);

        return $configuration;
    }

    function getPendingMigrationCount() {
        $configuration = $this->create_migration_configuration();

        $available_migration_count = $configuration->getNumberOfAvailableMigrations();
        $executed_migration_count = $configuration->getNumberOfExecutedMigrations();
        $pending_migration_count = $available_migration_count - $executed_migration_count;

        return $pending_migration_count;
    }
}
