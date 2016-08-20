<?php
/**
 * Adds the new helpscout command to WP-CLI.
 *
 * @author     Alessandro Tesoro
 * @version    1.0.0
 * @copyright  (c) 2016 Alessandro Tesoro
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU LESSER GENERAL PUBLIC LICENSE
*/

class HelpScout_Parser_CLI extends WP_CLI_Command {

	/**
	 * Generate an offline documentation for the current theme.
	 *
	 * ## OPTIONS
	 *
	 * <collection_id>
	 * : Help Scout collection ID from where we're going to collect categories and articles. Example: 1111111111111
	 *
	 * ## EXAMPLES
	 *
	 *     wp helpscout generate 111111111
	 *
	 * @access		public
	 * @param		  array $args
	 * @param		  array $assoc_args
	 * @return		void
	 */
	public function generate( $args, $assoc_args ) {

		$helpscout_api_key = $this->get_api_key();

		$collection_id = $args[0];

		if( ! $helpscout_api_key ) {
			WP_CLI::error( esc_html__( 'Could not find an api key, please define a constant with the name WPCLI_HELPSCOUT_DOCS_KEY to set your api key.' ) );
		}

		// Confirm we're running this process for the current theme.
		$theme            = wp_get_theme();
		$theme_name       = $theme->get( 'Name' );
		$theme_version    = $theme->get( 'Version' );
		$theme_author     = $theme->get( 'Author' );
		$theme_author_uri = $theme->get( 'Author URI' );

		WP_CLI::confirm( sprintf( 'Are you sure you want to create a documentation for the "%s" theme?', $theme_name ), $assoc_args );

		// Go ahead and show information about the current theme.
		WP_CLI::line();
		WP_CLI::line( esc_html( 'The following information will be used to generate the documentation:' ) );
		WP_CLI::line( sprintf( esc_html( 'Theme name: %s' ), $theme_name ) );
		WP_CLI::line( sprintf( esc_html( 'Theme version: %s' ), $theme_version ) );
		WP_CLI::line( sprintf( esc_html( 'Theme author: %s' ), $theme_author  ) );
		WP_CLI::line( sprintf( esc_html( 'Theme author uri: %s' ), $theme_author_uri  ) );
		WP_CLI::line();

		// Get categories.
		$find_categories  = $this->get_categories( $collection_id );
		$categories       = $find_categories->categories->items;
		$categories_count = $find_categories->categories->count;

		WP_CLI::line();
		WP_CLI::line( sprintf( esc_html( 'Found: %s categories.' ), $categories_count ) );

	}

	/**
	 * Get the API KEY.
	 *
	 * @return string api key.
	 */
	private function get_api_key() {

		$key = ( defined( 'WPCLI_HELPSCOUT_DOCS_KEY' ) ) ? WPCLI_HELPSCOUT_DOCS_KEY : false;

		return $key;

	}

	/**
	 * Get categories from a collection.
	 *
	 * @param  string $collection_id the collection ID.
	 * @return object                
	 */
	private function get_categories( $collection_id ) {

		$params = array(
			'method'          => 'GET',
			'headers'         => array(
			  'Authorization' => 'Basic ' . base64_encode( $this->get_api_key() . ':' . 'X' )
			),
			'sslverify'       => false,
			'timeout'         => 15
		);

		$request = wp_remote_get( 'https://docsapi.helpscout.net/v1/collections/'. $collection_id .'/categories', $params );
		$request = wp_remote_retrieve_body( $request );
		$request = json_decode( $request );

		return $request;

	}

}

WP_CLI::add_command( 'helpscout', 'HelpScout_Parser_CLI' );
