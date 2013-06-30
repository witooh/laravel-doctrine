<?php namespace Witooh\Doctrine\Facades;

use Illuminate\Support\Facades\Facade;


class Doctrine extends Facade {

	/**
	* Get the registered name of the component.
	*
	* @return string
	*/
	protected static function getFacadeAccessor() { return 'doctrine'; }

}