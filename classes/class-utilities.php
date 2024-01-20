<?php

class Utilities {

	public function __construct() {
		$this->_setup_hooks();
	}

	protected function _setup_hooks() {
		add_filter( 'wp_stream_connectors', [ $this, 'add_stream_connector' ] );
	}

	function add_stream_connector( $classes ) {
		require QUERYGENIUS_PATH . 'classes/class-stream-connector.php';

		$class_name = '\Stream_Connector';

		if ( ! class_exists( $class_name ) ) {
			return;
		}

		$stream = wp_stream_get_instance();
		$class = new $class_name();

		if ( ! method_exists( $class, 'is_dependency_satisfied' ) ) {
			return;
		}

		if ( $class->is_dependency_satisfied() ) {
			$classes[] = $class;
		}

		return $classes;
	}
}