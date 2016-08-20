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
