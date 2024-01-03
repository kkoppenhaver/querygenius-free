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


        $prompt_data = [
            'model' => 'gpt-3.5-turbo',
            'temperature' => 0.7,
            'messages' => [
                [
                    'role'    => 'system',
                    'content' => "You are a database administrator who is very familiar with writing performant queries against a SQL database containing WordPress data."
                ],
                [
                    'role'    => 'user',
                    'content' => "Given the provided database schema (formatted in JSON) and the provided list of registered post types for this WordPress site (formatted in JSON with the slug of the post type first, and then the name), write a SQL query that would answer the provided question for a WordPress database. Return just the SQL query with no additional explanation.
                        
                        If the question asks about a specific post type, prefer the post types provided as Registered post types. For example, if a custom post type exists on this site with the name of Gallery and a slug of gallery and a user asks for the most recent gallery post, the resulting SQL query should enforce WHERE 'post_type' = 'gallery'.
	            		
	            		If the question mentions a specific post type, use the slug from the provided list of registered post types that matches the name.

	            		If the provided database schema doesn't have columns that match the values asked, use the lists post meta and user meta keys to identify a potential meta key match to include in the query.
	            		
	            		If no post status is provided, assume that post_status = publish.
	            		
	            		Your output should be a single string containing the SQL query.
	            		
	            		For example {'query' : 'SELECT * FROM wp_posts WHERE post_type = 'post' AND post_status = 'publish'', 'suggestions' : 'Specify a post type'}
	            		Database schema: {$schema}
	            		Registered post types: {$registered_post_types}	
	            		Post meta: {$post_meta}
	            		User meta: {$user_meta}
	            		Question: {$question}"
                ],
            ],
        ];

        $response = wp_remote_post( 'https://api.openai.com/v1/chat/completions', [
            'timeout'     => 30,
            'body'        => json_encode( $prompt_data ),
            'headers'     => [
                'Authorization' => "Bearer $open_ai_api_key",
                'Content-Type'  => 'application/json'
            ],
        ] );

        $body = json_decode( wp_remote_retrieve_body( $response ) );
        $query = $body->choices[0]->message->content;

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