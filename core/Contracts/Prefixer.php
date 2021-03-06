<?php

namespace GravityFormsDateLimiter\Contracts;

trait Prefixer {

	/**
	 * @var string Default prefix value
	 */
	public static $prefixerDefault = 'skape';

	/**
	 * @var string Default operator value
	 */
	public static $prefixerOperatorDefault = '_';

	/**
	 * Prefix a value without duplication
	 *
	 * @param string $something
	 * @param callable|null $filter Value is passed to this function before returning.
     * @param string|null $operator Override the local operator.
	 * @return string
	 */
	public static function prefix( string $something, ?callable $filter = null, ?string $operator = null ) {

		// Get child defined prefix, else use default.
		$prefix = isset( static::$prefix ) ? static::$prefix : self::$prefixerDefault;
		$operator = $operator ?: ( isset( static::$prefixOperator ) ? static::$prefixOperator : self::$prefixerOperatorDefault );

		$fullPrefix = $prefix . $operator;

		// Remove the prefix from the string if it already sexits.
		$pattern = '/^' . preg_quote( $fullPrefix, '/' ) . '/';
		$something = preg_replace( $pattern, '', $something );

		// Combine the prefix and arg.
		$prefixedValue =  $fullPrefix . $something;

		// Apply value filter.
		if ( is_callable( $filter ) ) {
			$prefixedValue = call_user_func( $filter, $prefixedValue );
		}

		// Return value
		return $prefixedValue;
	}

    /**
     * Simply prefix a value.
     *
     * @param string $prefix
     * @param string|null $value
     * @return string
     */
    public static function make( string $prefix, ?string $value ) {
        $pattern = '/^' . preg_quote( $prefix, '/' ) . '/';
        $something = preg_replace( $pattern, '', $value );
        return $prefix . $something;
    }

}
