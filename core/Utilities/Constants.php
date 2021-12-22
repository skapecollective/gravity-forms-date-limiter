<?php

namespace GravityFormsDateLimiter\Utilities;

use GravityFormsDateLimiter\Contracts\Prefixer;

class Constants {

    use Prefixer;

    /**
     * Conditionally register a constant
     *
     * @param string $key
     * @param mixed $value
     * @returns void
     */
    public static function set( string $key, $value ) {
        $key = self::prefix( $key, 'strtoupper' );

        if ( !defined( $key ) ) {
            define( $key, $value );
        }
    }

    /**
     * Get a prefixed contstant
     *
     * @param string $key
     * @param mixed $default
     * @returns mixed
     */
    public static function get( string $key, $default = null ) {
        $key = self::prefix( $key, 'strtoupper' );

        if ( defined( $key ) ) {
            return constant( $key );
        }

        return $default;
    }

}
