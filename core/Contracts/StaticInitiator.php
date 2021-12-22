<?php

namespace GravityFormsDateLimiter\Contracts;

trait StaticInitiator {

	/**
	 * @var null|$this Store local instance
	 */
	public static $instance = null;

	/**
	 * @var $this[] Store array of multiple instances.
	 */
	public static $instances = [];

	/**
	 * Create a new instance or retrieve current instance
	 *
	 * @return $this
	 */
	public static function init() {

		$multiple = !empty( static::$multipleInstances ) && static::$multipleInstances;

		// Multiple instances
		if ( $multiple ) {
			$instance = new static( ...func_get_args() );
			static::$instances[] = $instance;
			return $instance;
		}

		// Single instance only
		if ( !static::$instance ) {
			static::$instance = new static( ...func_get_args() );
		}

		return static::$instance;
	}

}
