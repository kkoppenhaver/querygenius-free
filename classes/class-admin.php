<?php

class Admin {

	public function __construct() {
		$this->_setup_hooks();
	}

	protected function _setup_hooks() {
		add_action('admin_menu', [ $this, 'admin_menu' ]);
		add_action('admin_enqueue_scripts', [ $this, 'enqueue_scripts' ]);
	}

	public function admin_menu() {
		add_menu_page(
	        'querygenius',        // Page title
	        'querygenius',                 // Menu title
	        'manage_options',        // Capability
	        'querygenius',        // Menu slug
	        [ $this, 'querygenius_main_page' ],   // Function to display the page
	        'dashicons-editor-help'  // Icon URL (using dashicon class)
	    );
	}

	public function querygenius_main_page() {
		ob_start();
		require(QUERYGENIUS_PATH . 'templates/admin-page.php');
		echo ob_get_clean();
	}

	public function enqueue_scripts($hook) {
		if ($hook != 'toplevel_page_querygenius') {
	        return;
	    }

	    wp_enqueue_script('querygenius', QUERYGENIUS_URL . 'assets/askwp.js', ['jquery'], QUERYGENIUS_VERSION, true);
	    wp_localize_script('querygenius', 'wpApi', [
	        'root' => esc_url_raw( rest_url() ),
	        'nonce' => wp_create_nonce( 'wp_rest' )
	    ]);

	    wp_enqueue_script('mysql-parser', QUERYGENIUS_URL . 'assets/mysql.umd.js', ['jquery'], QUERYGENIUS_VERSION, true);

	    wp_enqueue_script('prism', QUERYGENIUS_URL . 'assets/prism.js', [], QUERYGENIUS_VERSION, true);
	    wp_enqueue_style('prism', QUERYGENIUS_URL . 'assets/prism.css', [], QUERYGENIUS_VERSION, 'all');

	    wp_enqueue_script('codeinput', QUERYGENIUS_URL . 'assets/codeinput.min.js', [], QUERYGENIUS_VERSION, true);
	    wp_enqueue_script('codeinput-indent', QUERYGENIUS_URL . 'assets/indent.js', [], QUERYGENIUS_VERSION, true);
        wp_enqueue_style('codeinput', QUERYGENIUS_URL . 'assets/codeinput.min.css', [], QUERYGENIUS_VERSION, 'all');

        wp_enqueue_script('datatables', QUERYGENIUS_URL . 'assets/datatables.min.js', [], QUERYGENIUS_VERSION, true);
        wp_enqueue_style('datatables', QUERYGENIUS_URL . 'assets/datatables.min.css', [], QUERYGENIUS_VERSION, 'all');

        wp_enqueue_script('sql-formatter', QUERYGENIUS_URL . 'assets/sql-formatter.min.js', [], QUERYGENIUS_VERSION, true);
	}

}