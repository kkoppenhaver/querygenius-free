<?php

class Rest_Api {

	public function __construct() {
		$this->_setup_hooks();
	}

	protected function _setup_hooks() {
		add_action( 'rest_api_init', [ $this, 'add_translate_endpoint' ] );
		add_action( 'rest_api_init', [ $this, 'add_query_endpoint' ] );
	}

	public function add_translate_endpoint() {
		register_rest_route( 'querygenius/v1', '/translate/', [
	      'methods' => 'POST',
	      'callback' => [ $this, 'translate_response' ],
	   	] );
	}

	public function add_query_endpoint() {
		register_rest_route( 'querygenius/v1', '/query/', [
	      'methods' => 'POST',
	      'callback' => [ $this, 'query_response' ],
	   	] );
	}

	public static function translate_response( $data ) {
		if (! is_user_logged_in() && ! (defined( 'WP_CLI' ) && WP_CLI) ) {
			return new WP_REST_Response( 'You must be logged in to use this endpoint.', 403 );
		}

		global $wpdb;

        // VIP-specific tweaks: Removing certain VIP-only tables, allowing for HyperDB, etc
        if( defined( 'VIP_GO_APP_ENVIRONMENT' ) ) {
            $dbname = $wpdb->hyper_servers['global']['read'][1][0]['name'];
        } else {
            $dbname = $wpdb->dbname;
        }

        $prefix = $wpdb->prefix;

        $results = $wpdb->get_results("SELECT 
                table_name, 
                GROUP_CONCAT(CONCAT(column_name, ': ', data_type)) AS column_array_with_types
            FROM 
                information_schema.columns 
            WHERE 
                table_schema = '{$dbname}' 
                AND table_name NOT LIKE 'wp_a8c%' 
                AND table_name NOT LIKE 'wp_cli%' 
                AND table_name NOT LIKE 'wp_actionscheduler%' 
            GROUP BY 
                table_name 
            ORDER BY 
                table_name, ordinal_position;");

        $schema = json_encode($results);

        $post_types = get_post_types(['public' => true], 'objects');
        $registered_post_types = [];

        foreach ($post_types as $post_type => $object) {
            $registered_post_types[$post_type] = $object->label;
        }
        
        if( defined( 'WP_CLI' ) && WP_CLI ) {
        	$question = $data;
        } else {
        	// Retrieve the query parameters from the request
        	$params = $data->get_params();
        	$question = $params['question'];
        }

        // Query to select distinct meta keys
	    $query = "SELECT DISTINCT meta_key FROM {$wpdb->postmeta}";
	    $postmeta_keys = json_encode($wpdb->get_col($query));

	    // Query to select distinct meta keys from the usermeta table
    	$query = "SELECT DISTINCT meta_key FROM {$wpdb->usermeta}";
	    $usermeta_keys = json_encode($wpdb->get_col($query));

	    // Fetching the list of tables
		$tables = $wpdb->get_col("SHOW TABLES", 0);

		$create_statements = [];

		foreach ($tables as $table) {
		    // Retrieve the CREATE TABLE statement for each table
		    $create_table_stmt = $wpdb->get_var("SHOW CREATE TABLE $table", 1);

		    $create_statements[] = $create_table_stmt;
		}

		$prompt_data = [
			'schema' => $create_statements,
			'post_types' => $registered_post_types,
			'question' => $question,
			'postmeta' => $postmeta_keys,
			'usermeta' => $usermeta_keys,
			'prefix' => $prefix
		];

        //TO-DO: Prompt generation via OpenAI API call goes here  

        do_action( 'querygenius_query_created', $question, $query );

        // If called in CLI context, just return the query
        if( defined( 'WP_CLI' ) && WP_CLI ) {
        	return $query;
        } else {
        	return new WP_REST_Response( $query, 200 );
        }
	}

	public function query_response( $data ) {
		if (! is_user_logged_in() ) {
			return new WP_REST_Response( 'You must be logged in to use this endpoint.', 403 );
		}

		global $wpdb;

		$params = $data->get_params();
        $query = $params['query'];

	    // Prepare the query to ensure it's safe to run
	    $prepared_query = $wpdb->prepare($query);

	    // Check if the query is a SELECT query
	    if (stripos($prepared_query, 'SELECT') === 0) {
	        // Execute the query and return the results
	        do_action( 'querygenius_query_run', $prepared_query );
	        return new WP_REST_Response( $wpdb->get_results($prepared_query), 200 );
	    }
	    
	    return new WP_REST_Response( 'All queries must be SELECT queries', 403 );
	}

}