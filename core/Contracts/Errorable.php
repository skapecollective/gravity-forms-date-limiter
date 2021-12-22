<?php

namespace GravityFormsDateLimiter\Contracts;

trait Errorable {

	public $errorBag = [];

	/**
	 * Empty the error bag of all errors
	 *
	 * @return void
	 */
	public function emptyErrorBag() {
		$this->errorBag = [];
	}

	/**
	 * Get the full error bag
	 *
	 * @return array
	 */
	public function getErrors() {
		return $this->errorBag && is_array( $this->errorBag ) ? $this->errorBag : [];
	}

	/**
	 * Check if any errors exists
	 *
	 * @return bool
	 */
	public function hasErrors() {
		return !empty( $this->getErrors() );
	}

    /**
     * Check if any errors matching $key exists
     *
     * @param string $key
     * @return bool
     */
    public function hasError( $key ) {
        $found = array_filter( $this->getErrors(), function ( $error ) use ( $key ) {
            return $error[ 'key' ] === $key;
        } );

        return count( $found ) > 0;
    }

	/**
	 * Returns all errors matching the key, or the first error if `$first` is true.
     *
	 * @param string $key
	 * @param bool $first
	 * @return array
	 */
	public function getError( string $key, bool $first = false ) {
		$found = array_filter( $this->getErrors(), function ( $error ) use ( $key ) {
			return $error[ 'key' ] === $key;
		} );

		$found = array_values( $found );

		if ( $first ) {
			$return = array_shift( $found );

            // Remove first flash error.
            if ( $return && $return[ 'flash' ] ) {
                $index = array_search( $return, $this->errorBag );

                if ( $index !== false ) {
                    unset( $this->errorBag[ $index ] );
                }
            }

            return $return;
		}

        // Remove all flash errors.
        foreach ( $found as $error ) {
            if ( $error && $error[ 'flash' ] ) {
                $index = array_search( $error, $this->errorBag );

                if ( $index !== false ) {
                    unset( $this->errorBag[ $index ] );
                }
            }
        }

		return $found;
	}

	/**
	 * Check if an error exits in the bag already.
	 *
	 * @param string $key
	 * @param string $message
	 * @return bool
	 */
	public function errorExists( string $key, string $message ) {
		$found = array_filter( $this->getErrors(), function ( $error ) use ( $key, $message ) {
			return $error[ 'key' ] === $key && $error[ 'message' ] === $message;
		} );

		return count( $found ) > 0;
	}

	/**
	 * Conditionall add an arror to the bag. Reurns false if already exists.
	 *
	 * @param string $key
	 * @param string $message
	 * @param int|string $code
	 * @return bool
	 */
	public function addError( string $key, string $message, bool $flash = false ) {
		if ( !$this->errorExists( $key, $message ) ) {
			$this->errorBag[] = [
				'key' => $key,
				'message' => $message,
				'flash' => $flash
			];

			return true;
		}

		return false;
	}

	/**
	 * Add error to bag based on `$condition`
	 *
	 * @param string $key
	 * @param string $message
	 * @param bool $condition
	 * @param int $code
	 * @return bool
	 */
	public function conditionalError( string $key, string $message, bool $condition, bool $flash = false ) {
		if ( $condition ) {
			return $this->addError( $key, $message, $flash );
		}

		return false;
	}

	/**
	 * Remove errors by key from bag. Returns number of errors removed.
	 *
	 * @param string $key
	 * @return int
	 */
	public function removeError( string $key ) {
		$removed = 0;
		foreach ( $this->errorBag as $error ) {
			if ( $error[ 'key' ] === $key ) {
				unset( $this->errorBag[ $key ] );
				$removed++;
			}
		}
		return $removed;
	}

}
