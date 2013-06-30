<?php

return array(

	/*
	|--------------------------------------------------------------------------
	| Database Connection
	|--------------------------------------------------------------------------
	|
	| This array passes right through to the EntityManager factory.
	|
	| http://www.doctrine-project.org/documentation/manual/2_0/en/dbal
	|
	*/

    'default'=>'mysql',

	'connection' => array(

        'mysql'=>array(
            'driver'    => 'pdo_mysql',
            'user'		=> 'root',
            'password'	=> '',
            'dbname'	=> 'database',
            'host'		=> '127.0.0.1',
        )
	),

	/*
	|--------------------------------------------------------------------------
	| Metadata Sources
	|--------------------------------------------------------------------------
	|
	| This array passes right through to the EntityManager factory.
	|
	| http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/configuration.html
	|
	*/
	'metadata' => array(
		__DIR__.'/../../../../../app/models'
	),

    /*
	|--------------------------------------------------------------------------
	| Cache
	|--------------------------------------------------------------------------
	|
	| 1. apc
    | 2. file
	|
	*/
    //apc or file
    'cache'=>'apc',


    'proxyDir'=>__DIR__.'/../../../../../app/database/proxies',
    'sqlLogger'=>false,
    'development'=>false,
    'autoGenerateProxy'=>false,
);