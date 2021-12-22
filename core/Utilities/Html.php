<?php

namespace GravityFormsDateLimiter\Utilities;

class Html {

    private static $ids = [];

    /**
     * Inline array of classes into a single line ready for printing.
     *
     * @param array|string $classes
     * @param null|string $prefix Prefix all classes with this value.
     * @param bool $sort Whether to sort classes alphabetically or not.
     * @param null|string $case Convert all classes to specific case typing: pascal | studly | camel | kebab | snake
     * @return string
     */
    public static function escClasses( $classes, ?string $prefix = null, bool $sort = true, ?string $case = null ) {

        // Bail early if classes is anything but an array or string.
        if ( !is_array( $classes ) && !is_string( $classes ) ) {
            return null;
        }

        // Convert string to array.
        if ( is_string( $classes ) ) {
            $classes = (array)$classes;
        }

        // Split array strings values with multiple values.
        array_walk_recursive( $classes, function( &$item ) {
            if ( is_string( $item ) && preg_match( '/\s/', $item ) ) {
                $item = preg_split( '/\s/', $item );
            }
        } );

        // Flatten nested arrays.
        $classes = iterator_to_array( new \RecursiveIteratorIterator( new \RecursiveArrayIterator( $classes ) ), false );

        // Remove empty values.
        $classes = array_filter( $classes );

        // Add sanitize class and add prefix.
        $classes = array_map( function( $value ) use ( $prefix, $case ) {
            if ( $case ) {
                $mod = Strings::init( $value );

                switch ( $case ) {
                    case 'pascal':
                    case 'studly':
                        $mod->studly();
                        break;
                    case 'camel':
                        $mod->camel();
                        break;
                    case 'kebab':
                        $mod->kebab()->replace( '_', '-' );
                    case 'snake':
                        $mod->snake()->replace( '-', '_' );

                }

                $value = $mod->get();
            }
            return sanitize_html_class( $prefix . $value );
        }, $classes );

        // Remove duplicates.
        $classes = array_unique( $classes );

        // Remove empty values one last time.
        $classes = array_filter( $classes );

        // Sort classes
        if ( $sort ) {
            sort( $classes );
        }

        // Implode values into a single string.
        $classes = implode( ' ', array_map( 'trim', $classes ) );

        // Remove multiple white spaces.
        $classes = preg_replace( '/\s{2,}/', ' ', $classes );

        // Trim any extra white space.
        $classes = trim( $classes );

        // Return final value.
        return $classes;
    }

    /**
     * Escape and inline HTML attribute.
     *
     * @param array $args,...
     * @return string
     */
    public static function escAttributes( ...$args ): string {

        $return = '';

        $attributes = array_merge_recursive( ...$args );

        foreach ( $attributes as $key => $value ) {

            // Skip attribute if value is empty
            if ( empty( $value ) ) {
                continue;
            }

            // Handle custom class inliner
            if ( $key === 'class' ) {
                $value = self::escClasses( $value );
            }

            // Value is string
            else if ( is_string( $value ) ) {
                $value = trim( $value );
            }

            // Value is boolean
            else if ( is_bool( $value ) ) {
                $value = $value ? 1 : 0;
            }

            // Value is array or object
            else if ( is_array( $value ) || is_object( $value ) ) {
                $value = json_encode( $value );
            }

            // append
            $return .= esc_attr( $key ) . '="' . esc_attr( $value ) . '" ';

        }

        return trim( $return );
    }

    /**
     * Generate a unique DOM id for each method call.
     *
     * @param string $string The desired ID.
     * @param int $length Limit the id length (exluding the numerical appenage).
     * @return string
     */
    public static function uniqueId( string $string, int $length = 150 ) {

        $string = Strings::init( $string );

        // Limit id length (optional)
        if ( $length > 0 ) {
            $string->limit( $length, '' );
        }

        // Kebab case string (using helper function for converting camel case etc).
        $kebab = sanitize_title( $string->kebab()->get() );

        // Get the string without appended count
        $numberless = preg_replace( '/-[0-9]+$/', '', $kebab );

        /**
         * @var int[] An array of matching appended counts.
         */
        $matches = [];

        foreach ( self::$ids as $_id ) {
            if ( $_id === $numberless ) {
                $matches[] = 1;
            } else {
                preg_match( '/^' . preg_quote( $numberless, '/' ) . '(?:-([0-9]+))?$/i', $_id, $_matches, PREG_UNMATCHED_AS_NULL );
                if ( $match = array_get( $_matches, '1' ) ) {
                    $matches[] = $match;
                }
            }
        }

        // Get the maximum count from matches
        $count = $matches ? max( $matches ) : null;

        // Generate new id
        $id = $count !== null ? $numberless . '-' . ( $count + 1 ) : $numberless;

        // Store new ID
        self::$ids[] = $id;

        // Return
        return $id;
    }
}
