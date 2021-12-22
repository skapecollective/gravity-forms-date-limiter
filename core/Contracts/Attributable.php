<?php

namespace GravityFormsDateLimiter\Contracts;

use ReflectionClass;
use ReflectionMethod;
use GravityFormsDateLimiter\Utilities\Strings;

trait Attributable {

    public $attributes = [];

    /**
     * Attribute getter magic property handler.
     *
     * @param string $name
     * @return mixed
     * @throws \Exception
     */
    public function __get( string $name ) {
        return $this->getAttribute( $name );
    }

    /**
     * Attribute setter magic property handler.
     *
     * @param string $name
     * @param mixed $value
     */
    public function __set( string $name, $value ) {
        $this->setAttribute( $name, $value );
    }

    /**
     * @return array
     */
    public function __debugInfo() {
        return $this->getAttributes();
    }

    /**
     * Convert object data to associative array.
     *
     * @return array
     */
    public function getAttributes() {
        $class = new ReflectionClass( static::class );
        $methods = $class->getMethods( ReflectionMethod::IS_PUBLIC );

        $attributes = $this->attributes;

        foreach ( $methods as $method ) {
            if ( preg_match( '/^get(\w+)Attribute$/', $method->name, $matches ) ) {
                $key = Strings::init( $matches[ 1 ] )->snake()->get();
                $attributes[ $key ] = call_user_func( [ $this, $matches[ 0 ] ] );
            }
        }

        return $attributes;
    }

    /**
     * Get a property value.
     *
     * @param string $property
     * @return mixed
     * @throws \Exception
     */
    public function getAttribute( string $property ) {

        /**
         * Generate a magic attribute method name from getter key. Inspired by Laravel.
         *
         * @example color -> getColorAttribute()
         * @example full_name -> getFullNameAttribute()
         */
        $method = 'get' . Strings::init( $property )->camel()->ucfirst()->get() . 'Attribute';

        /**
         * See if we have a magic attribute method.
         */
        if ( method_exists( $this, $method ) ) {
            return call_user_func( [ $this, $method ] );
        }

        /**
         * Check if property exists on WP_Post object
         */
        else if ( array_key_exists( $property, $this->attributes ) ) {
            return $this->attributes[ $property ];
        }

        throw new \Exception( 'Trying to GET undefined property "' . $property . '" of ' . self::class . '.', 420 );
    }

    /**
     * Get a property value.
     *
     * @param string $property
     * @param mixed $value
     */
    public function setAttribute( string $property, $value ) {

        /**
         * Generate a magic attribute method name from setter key. Inspired by Laravel.
         *
         * @example color -> setColorAttribute()
         * @example full_name -> setFullNameAttribute()
         */
        $method = 'set' . Strings::init( $property )->camel()->ucfirst()->get() . 'Attribute';

        /**
         * See if we have a magic attribute method.
         */
        if ( method_exists( $this, $method ) ) {
            call_user_func( [ $this, $method ], $value );
        } else {
            $this->attributes[ $property ] = $value;
        }
    }

}
