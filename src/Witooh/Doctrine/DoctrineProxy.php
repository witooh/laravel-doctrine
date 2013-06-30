<?php

namespace Witooh\Doctrine;


use Config;
use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Logging\EchoSQLLogger;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Illuminate\Support\Collection;

class DoctrineProxy
{

    /**
     * @var Collection
     */
    protected $entityManagerContainer;
    protected $DBALContainer;

    public function __construct()
    {
        $this->entityManagerContainer = new Collection();
        $this->DBALContainer = new Collection();
    }

    /**
     * @param string $name
     * @return EntityManager
     */
    public function EM($name = 'default')
    {
        if ($this->entityManagerContainer->has($name)) {
            return $this->entityManagerContainer->get($name);
        } else {
            $name = $name == 'default' ? Config::get('doctrine.default') : $name;
            $con = $this->getConnection($name);
            $config = $this->createEntityManagerConfiguration();
            $entityManager = EntityManager::create($con, $config);
            $this->entityManagerContainer->put($name, $entityManager);

            return $entityManager;
        }
    }

    /**
     * @param string $name
     * @return \Doctrine\DBAL\Connection
     */
    public function DBAL($name = 'default')
    {
        if ($this->DBALContainer->has($name)) {
            return $this->DBALContainer->get($name);
        } else {
            $name = $name == 'default' ? Config::get('doctrine.default') : $name;
            $con = $this->getConnection($name);
            $config = $this->createDBALConfiguration();
            $dbal = DriverManager::getConnection($con, $config);
            $this->DBALContainer->put($name, $dbal);

            return $dbal;
        }
    }

    protected function getConnection($name)
    {
        $name = 'doctrine.connection.' . $name;

        return Config::has($name) ? Config::get($name) : null;
    }

    protected function createDBALConfiguration()
    {
        $config = new Configuration();

        return $config;
    }

    protected function createEntityManagerConfiguration()
    {
        $cache = Config::get('doctrine.cache');
        $config = Setup::createAnnotationMetadataConfiguration(
            Config::get('doctrine.metadata'),
            Config::get('development'),
            Config::get('doctrine.proxyDir')
        );
        if ($cache == 'file') {
            $config->setMetadataCacheImpl(new FilesystemCache('../app/cache/storage'));
            $config->setQueryCacheImpl(new FilesystemCache('../app/cache/storage'));
        } elseif ($cache == 'apc') {
            $config->setMetadataCacheImpl(new ApcCache());
            $config->setQueryCacheImpl(new ApcCache());
        }
        if (Config::get('doctrine.sqlLogger')) {
            $config->setSQLLogger(new EchoSQLLogger());
        }
        $config->setAutoGenerateProxyClasses(Config::get('doctrine.autiGenerateProxy'));

        return $config;
    }
}