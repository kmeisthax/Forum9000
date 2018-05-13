<?php

namespace Forum9000\OnsiteDatabaseAdmin;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Doctrine\DBAL\Migrations\OutputWriter;
use Doctrine\DBAL\Migrations\Version;

trait DBAControllerTrait {
    private function server_only_params($connection_name = null, $shard_id = null) {
        if ($connection_name === null) {
            $connection_name = $this->get('doctrine')->getDefaultConnectionName();
        }

        $connection = $this->get('doctrine')->getConnection($connection_name);

        $params = $connection->getParams();
        if (isset($params['master'])) $params = $params['master'];

        if (isset($params['shards'])) {
            $shards = $params['shards'];
            // Default select global
            $params = array_merge($params, $params['global']);
            unset($params['global']['dbname']);
            if ($shard_id) {
                foreach ($shards as $i => $shard_params) {
                    if ($shard_params['id'] === (int) $shard_id) {
                        // Select sharded database
                        $params = array_merge($params, $shard_params);
                        unset($params['shards'][$i]['dbname'], $params['id']);
                        break;
                    }
                }
            }
        }

        $has_path = isset($params['path']);
        $has_dbname = isset($params['dbname']);
        $name = $has_path ? $params['path'] : ($has_dbname ? $params['dbname'] : null);
        if ($name === null) throw new \InvalidArgumentException("Database does not have path or dbname to create with");

        unset($params['dbname'], $params['path'], $params['url']);

        return array($params, $name);
    }

    private function check_database_exists($connection_name = null, $shard_id = null) {
        $parray = $this->server_only_params($connection_name, $shard_id);
        $params = $parray[0];
        $name = $parray[1];

        if ($params["driver"] === "pdo_sqlite") {
            return file_exists($name);
        }

        $server_conn = DriverManager::getConnection($params);
        $server_conn->connect($shard_id);

        $db_exists = in_array($name, $server_conn->getSchemaManager()->listDatabases());

        $server_conn->close();

        return $db_exists;
    }

    private function create_empty_database($connection_name = null, $shard_id = null) {
        $parray = $this->server_only_params($connection_name, $shard_id);
        $params = $parray[0];
        $name = $parray[1];

        $server_conn = DriverManager::getConnection($params);
        $server_conn->connect($shard_id);

        $server_conn->getSchemaManager()->createDatabase($name);
        $server_conn->close();
    }

    private function create_migration_configuration(?\Closure $cl = null) {
        $container = $this->container;
        $connection = $this->get("doctrine")->getConnection();
        $dir = $container->getParameter('doctrine_migrations.dir_name');
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        $configuration = new Configuration($connection, new OutputWriter($cl));
        $configuration->setMigrationsNamespace($container->getParameter('doctrine_migrations.namespace'));
        $configuration->setMigrationsDirectory($dir);
        $configuration->registerMigrationsFromDirectory($dir);
        $configuration->setName($container->getParameter('doctrine_migrations.name'));
        $configuration->setMigrationsTableName($container->getParameter('doctrine_migrations.table_name'));

        return $configuration;
    }

    private function get_migration_infos(Configuration $configuration, $version) {
        $infos = array();

        //TODO: getVersion will fail if the migration class is missing.
        //This isn't acceptable for developer console; we need to distinguish
        //between an unknown update and an update that's been applied but whose
        //migration class is missing.
        $version_obj = $configuration->getVersion($version);
        $migration = $version_obj->getMigration();
        $migration_refl = new \ReflectionClass(get_class($migration));

        $infos["datetime"] = $configuration->getDateTime($version);
        $infos["comment"] = $migration_refl->getDocComment();
        $infos["canup"] = $migration_refl->hasMethod("up") && !$configuration->hasVersionMigrated($version_obj);
        $infos["candown"] = $migration_refl->hasMethod("down") && $configuration->hasVersionMigrated($version_obj);

        $infos["uplist"] = $configuration->getMigrationsToExecute(Version::DIRECTION_UP, $version);
        $infos["downlist"] = $configuration->getMigrationsToExecute(Version::DIRECTION_DOWN, $version) + [$version_obj];

        return $infos;
    }
}
