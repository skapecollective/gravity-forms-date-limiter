<?php

namespace GravityFormsDateLimiter\Utilities;

class Strings {

	/**
	 * @var array The cache of snake-cased words
	 */
	public static $snakeCache = [];

	/**
	 * @var array The cache of camel-cased words
	 */
    public static $camelCache = [];

	/**
	 * @var array The cache of studly-cased words
	 */
    public static $studlyCache = [];


	/**
	 * @var null|string Store the original value if needed
	 */
    public $originalValue = null;

	/**
	 * @var null|string This is the string we will be mutating
	 */
    public $workingValue = null;

	/**
	 * @var string String encoding
	 */
    public $encoding = 'UTF-8';

	/**
	 * Store string values
	 *
	 * @param null|string $value
     * @param null|string $encoding
	 */
	public function __construct( ?string $value = null, ?string $encoding = null ) {
		$this->originalValue = $value;
		$this->workingValue = $value;

		if ( $encoding ) {
			$this->encoding = $encoding;
		}
	}

	/**
	 * Return working value
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->get();
	}

	/**
	 * Return working value
	 *
	 * @return string
	 */
	public function get() {
		return (string)$this->workingValue;
	}

	/**
	 * Create new instance with this static method.
	 *
	 * @param null|string $value
	 * @return $this
	 */
	public static function init( ?string $value = null ) {
		return new self( $value );
	}

    /**
     * Get the first character of each word
     *
     * @return $this
     */
	public function initials() {
        preg_match_all( '/(?<=\s|^)[a-z]/i', $this->get(), $matches );
        $value = implode( '', $matches[ 0 ] );
        $this->workingValue = strtoupper( $value );
        return $this;
    }

    /**
     * Replace mustache tags ( {{key}} ) with values matching keys in the `$data` array.
     *
     * @param array $data
     * @return $this
     */
	public function mustache( array $data ) {
        $subject = $this->get();
        $this->workingValue = preg_replace_callback( '/{{\s*([\.\-_a-zA-Z0-9]+?)\s*}}/', function( $match ) use ( $data ) {

            /**
             * @var string Get the matching tag
             */
            $tag = $match[ 1 ];

            // Replace matching tage with found data
            if ( !empty( $data[ $tag ] ) ) {
                return (string)$data[ $tag ];
            }

            // Do nothing, return original match
            return $match[ 0 ];
        }, $subject );
        return $this;
    }

	/**
	 * Return the remainder of a string after the first occurrence of a given value
	 *
	 * @param string $search
	 * @return $this
	 */
	public function after( string $search ) {
		$subject = $this->get();
		$this->workingValue = $search === '' ? $subject : array_reverse( explode( $search, $subject, 2 ) )[ 0 ];
		return $this;
	}

	/**
	 * Return the remainder of a string after the last occurrence of a given value
	 *
	 * @param string $search
	 * @return $this
	 */
	public function afterLast( string $search ) {

		$subject = $this->get();

		if ( $search === '' ) {
			return $this;
		}

		$position = strrpos( $subject, (string)$search );

		if ( $position === false ) {
			return $this;
		}

		$this->workingValue = substr( $subject, $position + strlen( $search ) );

		return $this;
	}

	/**
	 * Get the portion of a string before the first occurrence of a given value
	 *
	 * @param string $search
	 * @return $this
	 */
	public function before( string $search ) {
		$subject = $this->get();
		$this->workingValue = $search === '' ? $subject : explode( $search, $subject )[ 0 ];
		return $this;
	}

	/**
	 * Get the portion of a string before the last occurrence of a given value
	 *
	 * @param string $search
	 * @return $this
	 */
	public function beforeLast( string $search ) {

		$subject = $this->get();

		if ( $search === '' ) {
			return $this;
		}

		$pos = mb_strrpos( $subject, $search );

		if ( $pos === false ) {
			return $this;
		}

		$this->workingValue = $this->substr( 0, $pos );

		return $this;
	}

	/**
	 * Get the portion of a string between two given values
	 *
	 * @param string $from
	 * @param  string  $to
	 * @return $this
	 */
	public function between( string $from, string $to ) {

		if ( $from !== '' && $to !== '' ) {
			$this->after( $from );
			$this->beforeLast( $to );
		}

		return $this;
	}

	/**
	 * Convert a value to camel case
	 *
	 * @return $this
	 */
	public function camel() {

		$subject = $this->get();

		if ( !isset( static::$camelCache[ $subject ] ) ) {
			static::$camelCache[ $subject ] = lcfirst( $this->studly()->get() );
		}

		$this->workingValue = static::$camelCache[ $subject ];

		return $this;
	}

	/**
	 * Determine if a given string contains a given substring
	 *
	 * @param string|string[] $needles
	 * @return bool
	 */
	public function contains( $needles ) {

		$haystack = $this->get();

		foreach ( (array)$needles as $needle ) {
			if ( $needle !== '' && mb_strpos( $haystack, $needle ) !== false ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Determine if a given string contains all array values
	 *
	 * @param string[] $needles
	 * @return bool
	 */
	public function containsAll( array $needles ) {

		foreach ( $needles as $needle ) {
			if ( !$this->contains( $needle ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Determine if a given string ends with a given substring
	 *
	 * @param string $haystack
	 * @param  string|string[]  $needles
	 * @return bool
	 */
	public function endsWith( $needles ) {

		$haystack = $this->get();

		foreach ( (array)$needles as $needle ) {
			if ( substr( $haystack, -strlen( $needle ) ) === (string)$needle ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Cap a string with a single instance of a given value.
	 *
	 * @param string $cap
	 * @return $this
	 */
	public function finish( string $cap ) {
		$this->workingValue = preg_replace( '/(?:' . preg_quote( $cap, '/' ) . ')+$/u', '', $this->get() ) . $cap;
		return $this;
	}

	/**
	 * Determine if a given string is a valid UUID
	 *
	 * @return bool
	 */
	public function isUuid() {

		$value = $this->get();

		if ( is_string( $value ) ) {
			return preg_match( '/^[\da-f]{8}-[\da-f]{4}-[\da-f]{4}-[\da-f]{4}-[\da-f]{12}$/iD', $value ) > 1;
		}

		return false;
	}

	/**
	 * Convert a string to kebab case
	 *
	 * @return $this
	 */
	public function kebab() {
		$this->snake( '-' );
		return $this;
	}

	/**
	 * Return the length of the given string
	 *
	 * @param ?string $encoding
	 * @return int
	 */
	public function length( ?string $encoding = null ) {
		$value = $this->get();
		return $encoding ? mb_strlen( $value, $encoding ) : mb_strlen( $value );
	}

	/**
	 * Limit the number of characters in a string
	 *
	 * @param int $limit
	 * @param  string  $end
	 * @return $this
	 */
	public function limit( int $limit = 100, string $end = '...' ) {

		$value = $this->get();

		if ( mb_strwidth( $value, $this->encoding ) > $limit ) {
			$this->workingValue = rtrim( mb_strimwidth( $value, 0, $limit, '', $this->encoding ) ) . $end;
		}

		return $this;
	}

	/**
	 * Convert the given string to lower-case
	 *
	 * @return $this
	 */
	public function lower() {
		$this->workingValue = mb_strtolower( $this->get(), $this->encoding );
		return $this;
	}

	/**
	 * Limit the number of words in a string
	 *
	 * @param int $words
	 * @param  string  $end
	 * @return $this
	 */
	public function words( int $words = 100, string $end = '...' ) {

		$value = $this->get();

		preg_match( '/^\s*+(?:\S++\s*+){1,' . $words . '}/u', $value, $matches );

		if ( isset( $matches[ 0 ] ) && $this->length() !== self::init( $matches[ 0 ] )->length() ) {
			$this->workingValue = rtrim( $matches[ 0 ] ) . $end;
		}

		return $this;
	}

	/**
	 * Parse a Class[@]method style callback into class and method
	 *
	 * @param string|null $default
	 * @return array<int, string|null>
	 */
	public function parseCallback( ?string $default = null ) {
		$callback = $this->get();
		return $this->contains( '@' ) ? explode( '@', $callback, 2 ) : [ $callback, $default ];
	}

	/**
	 * Generate a more truly "random" alpha-numeric string
	 *
	 * @param int $length
	 * @return $this
	 */
	public function random( int $length = 16 ) {
		$string = '';

		while ( ( $len = strlen( $string ) ) < $length ) {
			$size = $length - $len;
			$bytes = random_bytes( $size );
			$string .= substr( str_replace( [ '/', '+', '=' ], '', base64_encode( $bytes ) ), 0, $size );
		}

		$this->workingValue = $string;

		return $this;
	}

    /**
     * Replace a given value in the string
     *
     * @param string $search
     * @param mixed $replace
     * @return string
     */
    public function replace( string $search, $replace ) {
        $this->workingValue = str_replace( $search, $replace, $this->get() );
        return $this;
    }

	/**
	 * Replace a given value in the string sequentially with an array
	 *
	 * @param string $search
	 * @param  array<int|string, string>  $replace
	 * @return string
	 */
	public function replaceArray( string $search, $replace ) {

		$subject = $this->get();

		$segments = explode( $search, $subject );

		$result = array_shift( $segments );

		foreach ( $segments as $segment ) {
			$result .= ( array_shift( $replace ) ?? $search ) . $segment;
		}

		return $result;
	}

	/**
	 * Replace the first occurrence of a given value in the string
	 *
	 * @param string $search
	 * @param  string  $replace
	 * @return $this
	 */
	public function replaceFirst( string $search, string $replace ) {

		$subject = $this->get();

		$position = strpos( $subject, $search );

		if ( $position !== false ) {
			$this->workingValue = substr_replace( $subject, $replace, $position, strlen( $search ) );
		}

		return $this;
	}

	/**
	 * Replace the last occurrence of a given value in the string.
	 *
	 * @param string $search
	 * @param  string  $replace
	 * @return $this
	 */
	public function replaceLast( string $search, string $replace ) {

		$subject = $this->get();

		$position = strrpos( $subject, $search );

		if ( $position !== false ) {
			$this->workingValue = substr_replace( $subject, $replace, $position, strlen( $search ) );
		}

		return $this;
	}

	/**
	 * Begin a string with a single instance of a given value.
	 *
	 * @param string $prefix
	 * @return $this
	 */
	public function start( string $prefix ) {
		$this->workingValue = $prefix . preg_replace('/^(?:' . preg_quote( $prefix, '/' ) . ')+/u', '', $this->get() );
		return $this;
	}

	/**
	 * Convert the given string to upper-case
	 *
	 * @return $this
	 */
	public function upper() {
		$this->workingValue = mb_strtoupper( $this->get(), $this->encoding );
		return $this;
	}

	/**
	 * Convert the given string to title case
	 *
	 * @return $this
	 */
	public function title() {
		$this->workingValue = mb_convert_case( $this->get(), MB_CASE_TITLE, $this->encoding );
		return $this;
	}

	/**
	 * Convert a string to snake case
	 *
	 * @param string $value
	 * @param  string  $delimiter
	 * @return $this
	 */
	public function snake( string $delimiter = '_' ) {
		$value = $this->get();
		$key = $value;

		// Check if any uppercase letters exist
		if ( !isset( static::$snakeCache[ $key ][ $delimiter ] ) ) {

		    if ( !ctype_lower( $value ) ) {
                $value = preg_replace( '/\s+/u', '', ucwords( $value ) );
                $value = self::init( preg_replace( '/(.)(?=[A-Z])/u', '$1' . $delimiter, $value ) )->lower()->get();
            }

			static::$snakeCache[ $key ][ $delimiter ] = $value;
		}

		$this->workingValue = static::$snakeCache[ $key ][ $delimiter ];

		return $this;
	}

	/**
	 * Determine if a given string starts with a given substring.
	 *
	 * @param string|string[] $needles
	 * @return bool
	 */
	public function startsWith( $needles ) {
		$haystack = $this->get();

		foreach ( (array)$needles as $needle ) {
			if ( (string)$needle !== '' && strncmp( $haystack, $needle, strlen( $needle ) ) === 0 ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Convert a value to studly caps case
	 *
	 * @return $this
	 */
	public function studly() {
		$value = $this->get();
		$key = $value;

		if ( !isset( static::$studlyCache[ $key ] ) ) {
			$value = ucwords( str_replace( [ '-', '_' ], ' ', $value ) );
			static::$studlyCache[ $key ] = str_replace( ' ', '', $value );
		}

		$this->workingValue = static::$studlyCache[ $key ];

		return $this;
	}

	/**
	 * Returns the portion of string specified by the start and length parameters
	 *
	 * @param int $start
	 * @param  int|null  $length
	 * @return $this
	 */
	public function substr( int $start, ?int $length = null ) {
		$this->workingValue = mb_substr( $this->get(), $start, $length, $this->encoding );
		return $this;
	}

	/**
	 * Make a string's first character uppercase
	 *
	 * @return $this
	 */
	public function ucfirst() {
		$firstValue = new static( $this->get() );
		$firstValue->substr( 0, 1 );
		$firstValue->upper();

		$lastValue = new static( $this->get() );
		$lastValue->substr( 1 );

		$this->workingValue = $firstValue->get() . $lastValue->get();
		return $this;
	}


    /**
     * Make a string's first character lowercase
     *
     * @return $this
     */
    public function lcfirst() {
        $firstValue = new static( $this->get() );
        $firstValue->substr( 0, 1 );
        $firstValue->lower();

        $lastValue = new static( $this->get() );
        $lastValue->substr( 1 );

        $this->workingValue = $firstValue->get() . $lastValue->get();
        return $this;
    }

}
