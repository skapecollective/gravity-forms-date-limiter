<?php

namespace GravityFormsDateLimiter\Utilities;

class Url {

	public $original;

	public $mutated;

	public function __construct( $url ) {
		$this->original = $url;
		$this->mutated = $url;
	}

	/**
	 * Get mutated value.
	 *
	 * @return mixed
	 */
	public function get() {
		return $this->mutated;
	}

	/**
	 * Compare if url is external.
	 *
	 * @param string|null $compare
	 * @return bool
	 */
	public function isExternal( ?string $compare = null ) {
		$compare = $compare ?: get_site_url();

		$link_fragment = parse_url( $this->mutated, PHP_URL_FRAGMENT );
		$link_path = parse_url( $this->mutated, PHP_URL_PATH );
		$link_host = parse_url( $this->mutated, PHP_URL_HOST );

		$home_host = parse_url( $compare, PHP_URL_HOST );

		// Link is either #link or /link
		if ( $link_fragment || $link_path ) {

			if ( $link_host ) {
				return strpos( $link_host, $home_host ) === false;
			}

			return false;
		}

		return strpos( $this->mutated, $home_host ) === false;
	}

	/**
	 * Set the url scheme.
	 *
	 * @param string $scheme
	 * @return $this
	 */
	public function scheme( string $scheme ) {
		$this->mutated = set_url_scheme( $this->mutated, $scheme );
		return $this;
	}

	/**
	 * Enforce HTTP.
	 *
	 * @return $this
	 */
	public function http() {
		$this->scheme( 'http' );
		return $this;
	}

	/**
	 * Enforce HTTPS.
	 *
	 * @return $this
	 */
	public function https() {
		$this->scheme( 'https' );
		return $this;
	}

}
