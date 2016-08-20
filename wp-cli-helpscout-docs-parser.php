<?php
/*
Plugin Name: WP-CLI Help Scout Docs Parser
Plugin URI:  https://alessandrotesoro.me
Description: Extract helpscout docs articles and categories to build an offline documentation.
Version: 1.0.0
Author:      Alessandro Tesoro
Author URI:  http://alessandrotesoro.me
License:     GPLv2+
*/

require plugin_dir_path( __FILE__ ) . '/vendor/autoload.php';

/**
 * Load extension if WP CLI exists.
 *
 * @return void
 * @since 1.0.0
 */
function wpcli_helpscout_docs_parser_load() {

	if ( defined( 'WP_CLI' ) && WP_CLI ) {

		include plugin_dir_path( __FILE__ ) . '/class-helpscout-parser-cli.php';

	}

}
add_action( 'plugins_loaded', 'wpcli_helpscout_docs_parser_load' );

/*
$docsApiClient = new DocsApiClient();
$docsApiClient->setKey( 'b4a9f7a84e130750d3158002a0bfe7389d9788e1' );

// Get Collections.
$collection_id            = '561277879033606ab4cbf60c';
$documentation_categories = $docsApiClient->getCategories( $collection_id );

print_r( $documentation_categories );*/
