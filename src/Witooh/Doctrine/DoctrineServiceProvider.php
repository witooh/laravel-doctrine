<?php namespace Witooh\Doctrine;

use Doctrine\ORM\Tools\Console\Command\GenerateEntitiesCommand;
use Illuminate\Support\ServiceProvider;
use Doctrine\ORM\Tools\SchemaTool;
use Witooh\Doctrine\Console\CreateSchemaCommand;
use Witooh\Doctrine\Console\UpdateSchemaCommand;
use Witooh\Doctrine\Console\DropSchemaCommand;

class DoctrineServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
        $this->app->singleton('Witooh\Doctrine\DoctrineManager',
            function ($app) {
                $doctrine = new DoctrineManager();
                return $doctrine;
            }
        );

        $this->app->singleton('doctrine.metadata-factory',
            function ($app) {
                return $app['Witooh\Doctrine\DoctrineManager']->em()->getMetadataFactory();
            }
        );
        $this->app->singleton('doctrine.metadata',
            function ($app) {
                return $app['doctrine.metadata-factory']->getAllMetadata();
            }
        );
        $this->app->bind('doctrine.schema-tool',
            function ($app) {
                return new SchemaTool($app['Witooh\Doctrine\DoctrineManager']->em());
            }
        );
        //
        // Commands
        //
        $this->app->bind('doctrine.schema.create',
            function ($app) {
                return new CreateSchemaCommand($app['Witooh\Doctrine\DoctrineManager']->em());
            }
        );
        $this->app->bind('doctrine.schema.update',
            function ($app) {
                return new UpdateSchemaCommand($app['Witooh\Doctrine\DoctrineManager']->em());
            }
        );
        $this->app->bind('doctrine.schema.drop',
            function ($app) {
                return new DropSchemaCommand($app['Witooh\Doctrine\DoctrineManager']->em());
            }
        );
        $this->commands(
            'doctrine.schema.create',
            'doctrine.schema.update',
            'doctrine.schema.drop'
        );
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}