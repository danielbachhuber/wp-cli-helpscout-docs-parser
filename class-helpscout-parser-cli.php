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

		// Display list of found categories:
		$formatted_categories = $this->format_categories( $categories );
		WP_CLI\Utils\format_items( 'table', $formatted_categories, array( 'name', 'articles' ) );

		// Create documenatation.
		WP_CLI::line();
		WP_CLI::line( sprintf( esc_html( 'Generating documentation file in %s' ), get_template_directory() ) );
		$this->generate_documentation( $theme, '', '' );

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

	/**
	 * Format categories retrieved from the api.
	 *
	 * @param  array $categories list of categories to format.
	 * @return array             formatted categories.
	 */
	private function format_categories( $categories ) {

		$formatted_categories = array();

		foreach ( $categories as $cat ) {

			$formatted_categories[] = array(
				'id'          => $cat->id,
				'number'      => $cat->number,
				'slug'        => $cat->slug,
				'name'        => $cat->name,
				'description' => $cat->description,
				'articles'    => $cat->articleCount
			);

		}

		return $formatted_categories;

	}

	/**
	 * Generate offline documentation.
	 *
	 * @param  object $theme      current theme's information.
	 * @param  array $categories  categories of the documentation.
	 * @param  array $articles    articles of the documentation.
	 * @return void
	 */
	private function generate_documentation( $theme, $categories, $articles ) {

		$file = 'documentation.html';
		$path = get_template_directory();

		$handle = fopen( $path . '/' . $file, 'w' ) or WP_CLI::error( esc_html( 'Could not create documentation.html file.' ) );

		// Set headers of the html file.
		$this->set_headers( $theme );

	}

	/**
	 * Set the headers of the html file.
	 *
	 * @param object $theme current theme's details.
	 */
	private function set_headers( $theme ) {

		$file          = 'documentation.html';
		$path          = get_template_directory();
		$theme_name    = $theme->get( 'Name' );
		$theme_author  = $theme->get( 'Author' );

		$handle = fopen( $path . '/' . $file, 'w+' ) or WP_CLI::error( esc_html( 'Something went wrong, could not read documentation.html file.' ) );

		$data = '<!DOCTYPE html>';
		$data .= '<html id="html" class="no-js">';
		$data .= '<head lang="en">';
		$data .= '<meta http-equiv="content-type" content="text/html;charset=utf-8">';
		$data .= '<meta name="viewport" content="width=device-width"/>';
		$data .= '<title>'. $theme_name .' Documentation | '. $theme_author .'</title>';
		$data .= '<style type="text/css">';
		$data .= $this->get_css();
		$data .= '</style>';
		$data .= '</head>';

		fwrite( $handle, $data );

	}

	/**
	 * Get css styling for the documentation.
	 *
	 * @return string
	 */
	private function get_css() {

		$file = 'docs_style.css';
		$path = plugin_dir_path( __FILE__ );

		$style = file_get_contents( $path . '/' . $file );

		return $style;

	}

}

WP_CLI::add_command( 'helpscout', 'HelpScout_Parser_CLI' );
