<?php

namespace GravityFormsDateLimiter\Utilities;

/**
 * @property string $hex
 * @property string $hexAlpha
 * @property string $hex_alpha
 * @property array $rgb
 * @property array $rgba
 * @property string $isDark
 * @property string $is_dark
 * @property string $is_light
 * @property string $isLight
 */
class Color {

    /**
     * @var null|string Store the original value if needed
     */
    public $originalValue = null;

    /**
     * @var integer
     */
    public $red = 0;

    /**
     * @var integer
     */
    public $green = 0;

    /**
     * @var integer
     */
    public $blue = 0;

    /**
     * @var integer
     */
    public $alpha = 0;

    /**
     * Color constructor.
     *
     * @param string|array $color
     */
    public function __construct( $color ) {
        $this->originalValue = $color;

        $this->set( $color );
    }

    /**
     * Create new instance with this static method.
     *
     * @param string $value
     * @return $this
     */
    public static function init( string $value ) {
        return new self( $value );
    }

    /**
     * Return working value
     *
     * @return string
     */
    public function __toString() {
        return $this->hex();
    }

    /**
     * Magic properties.
     *
     * @param string $name
     * @return array|false|string
     */
    public function __get( $name ) {
        switch ( $name ) {
            case 'hex':
                return $this->hex( false );
            case 'hexAlpha':
            case 'hex_alpha':
                return $this->hex( true );
            case 'rgb':
                return $this->rgb();
            case 'rgba':
                return $this->rgba();
            case 'isLight':
            case 'is_light':
                return $this->isLight();
            case 'isDark':
            case 'is_dark':
                return $this->isDark();
        }
    }

    /**
     * Store string values
     *
     * @param string|array $color
     */
    public function set( $color ) {
        if ( is_string( $color ) ) {

            // Hex
            if ( $hex = self::validateHex( $color ) ) {
                $rgba = self::hexToRgba( $hex );
                $this->red = $rgba[ 'red' ];
                $this->green = $rgba[ 'green' ];
                $this->blue = $rgba[ 'blue' ];
                $this->alpha = $rgba[ 'alpha' ];
            }

            // RGBA
            else if ( $rgba = self::validateRgba( $color ) ) {
                $this->red = $rgba[ 'red' ];
                $this->green = $rgba[ 'green' ];
                $this->blue = $rgba[ 'blue' ];
                $this->alpha = $rgba[ 'alpha' ];
            }
        }
    }

    /**
     * Return values in specifed type/
     *
     * @param string $type
     * @return array|string
     */
    public function get( string $type = 'hex' ) {
        switch ( $type ) {
            case 'hex':
            case 'hexadecimal':
                return $this->hex;
            case 'rgb':
                return $this->rgb;
            case 'rgba':
                return $this->rgba;
        }

        return null;
    }

    /**
     * Return the current instance value as rgba array.
     *
     * @param string|int|float|null $alpha
     * @return array
     */
    public function rgba( $alpha = null ) {
        return self::validateRgba( [
            'red' => $this->red,
            'green' => $this->green,
            'blue' => $this->blue,
            'alpha' => !is_null( $alpha ) ? $alpha : $this->alpha,
        ] );
    }

    /**
     * Return the current instance value as rgb array.
     *
     * @return array
     */
    public function rgb() {
        $rgb = $this->rgba();
        unset( $rgb[ 'alpha' ] );
        return $rgb;
    }

    /**
     * Return the current instance value as hexadecimal string.
     *
     * @param bool $include_alpha
     * @return string
     */
    public function hex( bool $include_alpha = false ) {
        return self::rgbaToHex( $this->rgba(), $include_alpha );
    }

    /**
     * Check if the color is light.
     * @link https://stackoverflow.com/a/12228730/3804924
     *
     * @param float $threshold
     * @return bool
     */
    function isLight( $threshold = 0.8 ) {
        $rgba = $this->rgba();
        $r = $rgba[ 'red' ];
        $g = $rgba[ 'green' ];
        $b = $rgba[ 'blue' ];
        $lightness = ( max( $r, $g, $b ) + min( $r, $g, $b ) ) / 510.0;
        return $lightness >= $threshold;
    }

    /**
     * Check if the color is dark.
     *
     * @param float $threshold
     * @return bool
     */
    function isDark( $threshold = 0.8 ) {
        return !$this->isLight( $threshold );
    }

    /**
     * Check if passed value meets the hex conditions.
     *
     * @param $hex
     * @return false|string
     */
    public static function validateHex( $hex ) {

        if ( is_string( $hex ) && trim( $hex ) ) {
            $hex = preg_replace( '/^#/', '', $hex );

            if ( strpos( $hex, ',' ) === false ) {
                $valid = filter_var( mb_strlen( $hex ), FILTER_VALIDATE_INT, [
                    'options' => [
                        'min_range' => 3,
                        'max_range' => 8
                    ]
                ] );

                if ( $valid ) {
                    return $hex;
                }
            }
        }

        return false;
    }

    /**
     * Check if passed value meets the hex conditions.
     *
     * @param string|array $hex
     * @return false|array
     */
    public static function validateRgba( $rgba ) {
        $red = 0;
        $green = 0;
        $blue = 0;
        $alpha = 1;

        // Array has been passed
        if ( is_array( $rgba ) ) {
            $red = array_key_exists( 'red', $rgba ) ? $rgba[ 'red' ] : ( array_key_exists( 'r', $rgba ) ? $rgba[ 'r' ] : $red );
            $green = array_key_exists( 'green', $rgba ) ? $rgba[ 'green' ] : ( array_key_exists( 'g', $rgba ) ? $rgba[ 'g' ] : $green );
            $blue = array_key_exists( 'blue', $rgba ) ? $rgba[ 'blue' ] : ( array_key_exists( 'b', $rgba ) ? $rgba[ 'b' ] : $blue );
            $alpha = array_key_exists( 'alpha', $rgba ) ? $rgba[ 'alpha' ] : ( array_key_exists( 'a', $rgba ) ? $rgba[ 'a' ] : $alpha );
        }

        // String value has been passed
        else if ( is_string( $rgba ) ) {
            $rgba = preg_replace( '/^\(|\)$/', '', $rgba );
            $rgba = array_map( 'trim', explode( ',', $rgba ) );

            $red = $rgba[ 0 ] ?? $red;
            $green = $rgba[ 1 ] ?? $green;
            $blue = $rgba[ 2 ] ?? $blue;
            $alpha = $rgba[ 3 ] ?? $alpha;
        }

        return [
            'red' => self::fixRgbValue( (int)$red ),
            'green' => self::fixRgbValue( (int)$green ),
            'blue' => self::fixRgbValue( (int)$blue ),
            'alpha' => self::fixAlphaValue( (float)$alpha )
        ];
    }

    /**
     * Fix an RGB colour value (round and keep between 0 and 255).
     *
     * @param int $value
     * @return int
     */
    public static function fixRgbValue( int $value ) {
        return max( min( round( $value ), 255 ), 0 );
    }

    /**
     * Fix an alpha value (keep between 0 and 1).
     *
     * @param int|float $value
     * @return int|float
     */
    public static function fixAlphaValue( $value ) {
        return max( min( $value, 1 ), 0 );
    }

    /**
     * Convert an rgb/rgba value to hexadecimal.
     *
     * @param array|string $rgba
     * @param bool $support_alpha
     * @return string
     */
    public static function rgbaToHex( $rgba, bool $support_alpha = false ) {

        $rgba = self::validateRgba( $rgba );

        $red = str_pad( dechex( $rgba[ 'red' ] ), 2, '0', STR_PAD_LEFT );
        $green = str_pad( dechex( $rgba[ 'green' ] ), 2, '0', STR_PAD_LEFT );
        $blue = str_pad( dechex( $rgba[ 'blue' ] ), 2, '0', STR_PAD_LEFT );

        return $red . $green . $blue . ( $support_alpha ? $rgba[ 'alpha' ] : '' );
    }

    /**
     * Convert a hex color to rgba.
     *
     * @param string $hex
     * @return array
     */
    public static function hexToRgba( string $hex ) {
        $hex = preg_replace( '/^#/', '', $hex );

        $r = 0;
        $g = 0;
        $b = 0;
        $a = 1;

        switch ( mb_strlen( $hex ) ) {
            case 8:
                $r = hexdec( $hex[ 0 ] . $hex[ 1 ] );
                $g = hexdec( $hex[ 2 ] . $hex[ 3 ] );
                $b = hexdec( $hex[ 4 ] . $hex[ 5 ] );
                $a = hexdec( $hex[ 6 ] . $hex[ 7 ] );
                break;
            case 6:
                $r = hexdec( $hex[ 0 ] . $hex[ 1 ] );
                $g = hexdec( $hex[ 2 ] . $hex[ 3 ] );
                $b = hexdec( $hex[ 4 ] . $hex[ 5 ] );
                break;
            case 3:
                $r = hexdec( $hex[ 0 ] . $hex[ 0 ] );
                $g = hexdec( $hex[ 1 ] . $hex[ 1 ] );
                $b = hexdec( $hex[ 2 ] . $hex[ 2 ] );
                break;
        }

        return [
            'red' => self::fixRgbValue( $r ),
            'green' => self::fixRgbValue( $g ),
            'blue' => self::fixRgbValue( $b ),
            'alpha' => self::fixAlphaValue( $a )
        ];
    }

}
