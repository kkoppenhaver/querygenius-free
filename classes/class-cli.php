<?php

class CLI {
	/**
     * Translates a natural language question into an SQL query and, after confirmation from the user, runs it.
     *
     * ## OPTIONS
     * 
     * [--question=<question>]
     * : The question you want to translate to a query
     * 
     * [--run]
     * : Add this flag to run the query without confirming
     * 
     * [--return]
     * : Add this flag to return the results instead of displaying them
     *
     * ## EXAMPLES
     *
     *     wp qg ask --question="What is the most recently published post?"
     *
     */
	public function ask( $args, $assoc_args ) {
		if ( ! isset( $assoc_args['question'] ) ) {
			WP_CLI::error('You must supply a question using the --question argument. For example: wp qg ask --question="What is the most recently published post?"');
		}

		$query = Rest_Api::translate_response($assoc_args['question']);

		do_action( 'querygenius_query_created', $assoc_args['question'], $query );

		WP_CLI::line( 'Your generated query: ' . $query );
		
		if( ! isset( $assoc_args['run'] ) ) {
			WP_CLI::confirm( 'Do you want to run this query and see the results?' );
		}
		
		global $wpdb;

		// Prepare the query to ensure it's safe to run
	    $prepared_query = $wpdb->prepare($query);

	    // Check if the query is a SELECT query
	    if (stripos($prepared_query, 'SELECT') === 0) {
	        // Execute the query and return the results
	        do_action( 'querygenius_query_run', $prepared_query );
	        $results = $wpdb->get_results($prepared_query, ARRAY_A);

	        if (!empty($results)) {
			    // Get column names from the keys of the first row of the results
			    $columns = array_keys($results[0]);

			    // Truncate each item in the array to 100 characters
			    foreach ($results as $key => $row) {
			        foreach ($row as $field => $value) {
			            if (strlen($value) > 100) {
			                // Truncate and add an ellipsis
			                $results[$key][$field] = substr($value, 0, 100) . '...';
			            }
			        }
			    }

			    // Format and display the results as a table
			    if( ! isset( $assoc_args['return'] ) ) {
			    	WP_CLI\Utils\format_items('table', $results, $columns);
			    } else {
			    	return $results;
			    }
			} else {
			    WP_CLI::error("No results found.");
			}
	    } else {
	    	WP_CLI::error('Currently, you can only run SELECT queries. Please check your query and try again.');
	    }
	}
}

/**
 * Registers our command when cli get's initialized.
 *
 * @since  1.0.0
 */
function querygenius_cli_register_commands() {
	WP_CLI::add_command( 'qg', 'CLI' );
}

add_action( 'cli_init', 'querygenius_cli_register_commands' );