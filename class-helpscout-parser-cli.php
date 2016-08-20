<?php
/**
 * Adds the new helpscout command to WP-CLI.
 *
 * @author     Alessandro Tesoro
 * @version    1.0.0
 * @copyright  (c) 2016 Alessandro Tesoro
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU LESSER GENERAL PUBLIC LICENSE
*/

use HelpScoutDocs\DocsApiClient;

class HelpScout_Parser_CLI extends WP_CLI_Command {

	/**
	 * Generate an offline documentation for the current theme.
	 *
	 * ## EXAMPLES
	 *
	 *     wp helpscout generate
	 *
	 * @access		public
	 * @param		  array $args
	 * @param		  array $assoc_args
	 * @return		void
	 */
	public function generate( $args, $assoc_args ) {

		$helpscout_api_key = $this->get_api_key();

		if( ! $helpscout_api_key ) {
			WP_CLI::error( esc_html__( 'Could not find an api key, please define a constant with the name WPCLI_HELPSCOUT_DOCS_KEY to set your api key.' ) );
		}

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

}

WP_CLI::add_command( 'helpscout', 'HelpScout_Parser_CLI' );
