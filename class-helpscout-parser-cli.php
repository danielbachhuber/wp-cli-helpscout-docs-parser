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
		$this->generate_documentation( $theme, $formatted_categories, '' );

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
	 * @return array
	 */
	private function get_categories( $collection_id ) {

		$transient = get_transient( "wpcli_helpscout_docs_categories_{$collection_id}" );

		if( ! empty( $transient ) ) {

			return $transient;

		} else {

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

			set_transient( "wpcli_helpscout_docs_categories_{$collection_id}", $request, DAY_IN_SECONDS );

			return $request;

		}

	}

	/**
	 * Retrieves a list of articles for a specific category.
	 *
	 * @param  string $category_id the id of the category we'll use to get articles.
	 * @return array
	 */
	private function get_articles( $category_id ) {

		$params = array(
			'method'          => 'GET',
			'headers'         => array(
				'Authorization' => 'Basic ' . base64_encode( $this->get_api_key() . ':' . 'X' )
			),
			'sslverify'       => false,
			'timeout'         => 15
		);

		$request = wp_remote_get( 'https://docsapi.helpscout.net/v1/categories/'. $category_id .'/articles', $params );
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
				'slug'        => sanitize_title( $cat->name ),
				'name'        => $cat->name,
				'description' => $cat->description,
				'articles'    => $cat->articleCount,
			);

		}

		return $formatted_categories;

	}

	/**
	 * Get a specific article from helpscout docs.
	 *
	 * @param  string $id ID of the article.
	 * @return object
	 */
	private function get_article( $id ) {

		$params = array(
			'method'          => 'GET',
			'headers'         => array(
				'Authorization' => 'Basic ' . base64_encode( $this->get_api_key() . ':' . 'X' )
			),
			'sslverify'       => false,
			'timeout'         => 15
		);

		$request = wp_remote_get( 'https://docsapi.helpscout.net/v1/articles/' . $id , $params );
		$request = wp_remote_retrieve_body( $request );
		$request = json_decode( $request );

		return $request;

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

		// Create docs file.
		$this->set_headers( $theme );
		$this->create_menu( $theme, $categories );
		$this->render_sections( $categories );

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

		$handle = fopen( $path . '/' . $file, 'a' ) or WP_CLI::error( esc_html( 'Something went wrong, could not read documentation.html file.' ) );

		$data = '<!DOCTYPE html>';
		$data .= '<html id="html" class="no-js">';
		$data .= '<head lang="en">';
		$data .= '<meta http-equiv="content-type" content="text/html;charset=utf-8">';
		$data .= '<meta name="viewport" content="width=device-width"/>';
		$data .= '<title>'. esc_html( $theme_name ) .' Documentation | '. esc_html( $theme_author ) .'</title>';
		$data .= '<style type="text/css">';
		$data .= $this->get_css();
		$data .= '</style>';
		$data .= '</head>';
		$data .= '<body>';

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

	/**
	 * Creates menu markup for the documentation.
	 *
	 * @param  object $theme      current theme's details.
	 * @param  array $categories  categories uses for the menu.
	 * @return void
	 */
	private function create_menu( $theme, $categories ) {

		$file       = 'documentation.html';
		$path       = get_template_directory();
		$theme_name = $theme->get( 'Name' );

		$handle = fopen( $path . '/' . $file, 'a' ) or WP_CLI::error( esc_html( 'Something went wrong, could not read documentation.html file.' ) );

		$data = '<section id="Menu">';
		$data .= '<header>';
		$data .= '<h1>'. esc_html( $theme_name ) .' <span>Documentation</span></h1>';

		$description = apply_filters( 'wpcli_helpscout_doc_description', '' );

		if( $description !== '' ) {
			$data .= '<p>'. $description .'</p>';
		}

		$data .= '</header>';

		// Other links.
		$data .= '<nav>';
		$data .= '<a href="'. apply_filters( 'wpcli_helpscout_doc_demo_url', '#' ) .'">Live Demo</a>';
		$data .= '<a href="'. apply_filters( 'wpcli_helpscout_doc_changelog_url', '#' ) .'">Changelog</a>';
		$data .= '</nav>';

		// Navigation.
		$data .= '<nav>';
		$total = count( $categories );

		WP_CLI::line();
		$notify = \WP_CLI\Utils\make_progress_bar( "Generating $total menu items", $total );

		for( $i = 0; $i < count( $categories ); $i++ ) {
			$notify->tick();
			$data .= '<a href="#'. esc_html( $categories[$i]['slug'] ) .'">'. esc_html( $categories[$i]['name'] ) .'</a>';
		}

		$notify->finish();
		WP_CLI::line();

		$data .= '</nav></section>';

		fwrite( $handle, $data );

	}

	/**
	 * Render all sections within the documentation.
	 *
	 * @param  array $categories categories to render.
	 * @return void
	 */
	private function render_sections( $categories ) {

		$file = 'documentation.html';
		$path = get_template_directory();

		$handle = fopen( $path . '/' . $file, 'a' ) or WP_CLI::error( esc_html( 'Something went wrong, could not read documentation.html file.' ) );

		$data = '';

		// Generate section block.
		foreach ( $categories as $section ) {

			$data .= '<section id="'. esc_html( $section['slug'] ) .'">';
			$data .= '<h1><a href="'. esc_html( $section['slug'] ) .'">'. esc_html( $section['name'] ) .'</a><small><a href="#html">Back to Top</a></small></h1>';
			$data .= '<p>'. esc_html( $section['description'] ) .'</p>';

			// Generate articles for each section.
			$articles       = $this->get_articles( $section['id'] );
			$articles_found = $articles->articles->items;
			$total          = $articles->articles->count;

			$section_name = '"'.$section['name'].'"';

			WP_CLI::line();
			$notify = \WP_CLI\Utils\make_progress_bar( "Generating $total articles for the $section_name category.", $total );

			for( $i = 0; $i < count( $articles_found ); $i++ ) {

				$notify->tick();
				$data .= '<h2>'. esc_html( $articles_found[$i]->name ) .'</h2>';

				// Append content of the article.
				$single_article = $this->get_article( $articles_found[$i]->id );
				$data          .= $this->format_content($single_article->article->text);

			}

			$notify->finish();
			WP_CLI::line();

			// Close section
			$data .= '</section>';

		}

		fwrite( $handle, $data );

	}

	/**
	 * Format content and fixes some issues that happen during import.
	 *
	 * Issue 1: iframes not using correct protocol.
	 *
	 * @param  string $content the content to format.
	 * @return string
	 */
	private function format_content( $content ) {

		$content = str_replace( '<iframe src="//', '<iframe src="https://' , $content );

		return $content;

	}

}

WP_CLI::add_command( 'helpscout', 'HelpScout_Parser_CLI' );
