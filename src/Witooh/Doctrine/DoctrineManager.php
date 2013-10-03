<?php

namespace Witooh\Doctrine;

use Config;
use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Persistence\Mapping\Driver\PHPDriver;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Logging\EchoSQLLogger;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Mapping\Driver\SimplifiedYamlDriver;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Doctrine\ORM\Mapping\Driver\YamlDriver;
use Doctrine\ORM\Tools\Setup;
use Illuminate\Support\Collection;

class DoctrineManager
{

    /**
     * @var Collection
     */
    protected $entityManagerContainer;
    protected $DBALContainer;

    public function __construct()
    {
        $this->entityManagerContainer = new Collection();
        $this->DBALContainer          = new Collection();
    }

    /**
     * @param string $name
     * @return \Doctrine\ORM\EntityManager
     */
    public function em($name = 'default')
    {
        if ($this->entityManagerContainer->has($name)) {
            return $this->entityManagerContainer->get($name);
        } else {
            if ($name == 'default') {
                $con = $this->getConnection(Config::get('doctrine.default'));
            } else {
                $con = $this->getConnection($name);
            }

            return $this->createEM($name, $con);
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
            if ($name == 'default') {
                $con = $this->getConnection(Config::get('doctrine.default'));
            } else {
                $con = $this->getConnection($name);
            }

            return $this->createDBAL($name, $con);
        }
    }

    /**
     * @param $name
     * @param $connection
     * @return \Doctrine\ORM\EntityManager
     * @throws \Exception
     */
    public function createEM($name, $connection)
    {
        if ($this->entityManagerContainer->has($name)) {
            throw new \Exception('Dupplicate Doctrine Key Container');
        }

        $config        = $this->createEntityManagerConfiguration();
        $entityManager = EntityManager::create($connection, $config);
        $this->entityManagerContainer->put($name, $entityManager);

        return $entityManager;
    }

    /**
     * @param $name
     * @param $connection
     * @return \Doctrine\DBAL\Connection
     * @throws \Exception
     */
    public function createDBAL($name, $connection)
    {
        if ($this->entityManagerContainer->has($name)) {
            throw new \Exception('Dupplicate Doctrine Key Container');
        }

        $config = $this->createDBALConfiguration();
        $dbal   = DriverManager::getConnection($connection, $config);
        $this->DBALContainer->put($name, $dbal);

        return $dbal;
    }

    protected function getConnection($name)
    {
        if (is_string($name)) {
            $name = 'doctrine.connection.' . $name;

            return Config::has($name) ? Config::get($name) : null;
        }

        return $name;
    }

    protected function createDBALConfiguration()
    {
        $config = new Configuration();

        return $config;
    }

    /**
     * @param \Doctrine\ORM\Configuration $config
     * @param string $mapper
     * @param array $metaData
     * @param array $YAMLCongif
     * @return \Doctrine\ORM\Configuration
     */
    protected function createDriver($config, $mapper, $metaData, $YAMLCongif)
    {
        if ($mapper == 'php') {
            $driver = new PHPDriver($metaData);
        } elseif ($mapper == 'yaml') {
            $driver = new YamlDriver($metaData);
//            $driver = new SimplifiedYamlDriver($YAMLCongif['namespaces']);
        } elseif ($mapper == 'xml') {
            $driver = new XmlDriver($metaData);
        } else {
            $driver = $config->newDefaultAnnotationDriver($metaData);
        }

        $config->setMetadataDriverImpl($driver);

        return $config;
    }

    /**
     * @param \Doctrine\ORM\Configuration $config
     * @param string $cache
     * @param string $cahcePath
     */
    protected function createCache($config, $cache, $cahcePath)
    {
        if ($cache == 'file') {
            $config->setMetadataCacheImpl(new FilesystemCache($cahcePath));
            $config->setQueryCacheImpl(new FilesystemCache($cahcePath));
        } elseif ($cache == 'apc') {
            $config->setMetadataCacheImpl(new ApcCache());
            $config->setQueryCacheImpl(new ApcCache());
        }
    }

    protected function createEntityManagerConfiguration()
    {
        $pkgConfig  = Config::get("doctrine");
        $cache      = $pkgConfig['cache'];
        $mapper     = $pkgConfig['mapper'];
        $metaData   = $pkgConfig['metadata'];
        $dev        = $pkgConfig['development'];
        $proxyDir   = $pkgConfig['proxyDir'];
        $cahcePath  = app_path() . '/storage/cache';
        $sqlLogger  = $pkgConfig['sqlLogger'];
        $autoProxy  = $pkgConfig['autoGenerateProxy'];
        $YAMLCongif = $pkgConfig['yaml'];

        $config = SetUp::createConfiguration(
            $dev,
            $proxyDir
        );

        $this->createDriver($config, $mapper, $metaData, $YAMLCongif);
        $this->createCache($config, $cache, $cahcePath);

        if ($sqlLogger) {
            $config->setSQLLogger(new EchoSQLLogger());
        }

        $config->setAutoGenerateProxyClasses($autoProxy);

        return $config;
    }

    /**
     * @return Criteria
     */
    public function createCriteria()
    {
        return Criteria::create();
    }
}