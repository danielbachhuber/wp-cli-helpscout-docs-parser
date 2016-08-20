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
	 * <folder_name>
	 * : Plugin's folder name. Example: jetpack
	 *
	 * <previous_version>
	 * : Plugin's previous version.
	 *
	 * <new_version>
	 * : Plugin's new version.
	 *
	 * ## EXAMPLES
	 *
	 *     wp helpscout generate test
	 *
	 * @access		public
	 * @param		  array $args
	 * @param		  array $assoc_args
	 * @return		void
	 */
	public function generate( $args, $assoc_args ) {

  }

}

WP_CLI::add_command( 'helpscout', 'HelpScout_Parser_CLI' );
