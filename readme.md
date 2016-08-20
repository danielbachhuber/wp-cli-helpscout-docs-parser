# WPCLI Help Scout Documentation Parser

Authors on ThemeForest are required to build an offline documentation for each theme we publish. I was tired of having to maintain 2 different documentations ( online and offline ) so I've built this little add-on that generates an offline documentation by grabbing articles on Help Scout docs sites.

**This is a WPCLI add-on - you must install WPCLI to use this**.

### Download:

Download it here from github.

### Requirements:

- Help Scout account.
- Help Scout docs subscription.

### How it works:

- Run the `helpscout generate {collection_id}` command to generate an offline documentation file.
- The command will use the currently enabled theme to grab details of the theme and import them into the documentation.
- A documentation.html file will be created within the currently enabled theme's folder.

The `collection_id` number can be found into the address bar when you edit a collection through Help Scout.

### Configuration:

- Enable the plugin like any other plugin.
- Define your helpscout api key in file wp-config.php: `define( 'WPCLI_HELPSCOUT_DOCS_KEY', 'api key here' );`

### Available filters:

Filters are available for further customizations of the documentation. Each filter should be defined into the currently enabled theme's functions.php file.

#### Setup the demo url:
`add_filter( 'wpcli_helpscout_doc_demo_url', function() { return 'http://demo.com'; } );`

#### Setup the changelog url:
`add_filter( 'wpcli_helpscout_doc_changelog_url', function() { return 'http://demochangelog.com'; } );`

#### Add custom description at the top of the documentation:
`add_filter( 'wpcli_helpscout_doc_description', function() { return 'Set-up & general guide to help you get the most out of your new WordPress theme.'; } );`
