const mix = require( 'laravel-mix' );

/**
 * Build preferences
 */
mix
	.setPublicPath( './build' )
	.options( { processCssUrls: false } );

if ( !mix.inProduction() ) {
	mix.sourceMaps();
	mix.webpackConfig( {
		devtool: 'inline-source-map'
	} );
}


/**
 * Javascripts
 */
mix
	.js( 'assets/js/backend.js', 'js' )
	.js( 'assets/js/frontend.js', 'js' );

/**
 * Stylesheets
 */
mix
	.sass( 'assets/scss/backend.scss', 'css' );

/**
 * Images.
 */
mix
	.copyDirectory( 'assets/images', './build/images' );

