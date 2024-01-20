<?php

class Stream_Connector extends \WP_Stream\Connector {
	/**
	 * Connector slug
	 *
	 * @var string
	 */
	public $name = 'querygenius';

	/**
	 * Actions registered for this connector
	 *
	 * These are actions that AskWP has created, we are defining them here to
	 * tell Stream to run a callback each time this action is fired so we can
	 * log information about what happened.
	 *
	 * @var array
	 */
	public $actions = array(
		'querygenius_query_created',
		'querygenius_query_run',
	);

	/**
	 * The minimum version required for AskWP
	 *
	 * @const string
	 */
	const PLUGIN_MIN_VERSION = '1.0.0';

	/**
	 * Display an admin notice if plugin dependencies are not satisfied
	 *
	 * If My Plugin does not have the minimum required version number specified
	 * in the constant above, then Stream will display an admin notice for us.
	 *
	 * @return bool
	 */
	public function is_dependency_satisfied() {
		$version_compare = version_compare( QUERYGENIUS_VERSION, self::PLUGIN_MIN_VERSION, '>=' );
		if ( QUERYGENIUS_VERSION && $version_compare ) {
			return true;
		}

		return false;
	}

	/**
	 * Return translated connector label
	 *
	 * @return string
	 */
	public function get_label() {
		return __( 'querygenius', 'querygenius' );
	}

	/**
	 * Return translated context labels
	 *
	 * @return array
	 */
	public function get_context_labels() {
		return array(
			'query'    => __( 'Query', 'querygenius' ),
			'question' => __( 'Question', 'querygenius' ),
		);
	}

	/**
	 * Return translated action labels
	 *
	 * @return array
	 */
	public function get_action_labels() {
		return array(
			'asked'   => __( 'Asked', 'querygenius'), 
			'created' => __( 'Created', 'querygenius' ),
			'run' => __( 'Run', 'querygenius' ),
		);
	}

	/**
	 * Track when queries are created
	 *
	 * @param string $question
	 * @param string $query
	 *
	 * @return void
	 */
	public function callback_querygenius_query_created( $question, $query ) {
		$this->log(
			// Summary message
			sprintf(
				__( 'Question: "%1$s"', 'querygenius' ),
				$question,
			),
			// This array is compacted and saved as Stream meta
			[
				'action' => 'asked',
				'question'  => $question,
			],
			'0', // Object ID
			'question', // Context
			'asked'
		);

		$this->log(
			// Summary message
			sprintf(
				__( '%1$s', 'querygenius' ),
				$query,
			),
			// This array is compacted and saved as Stream meta
			[
				'action' => 'created',
				'query'  => $query,
			],
			'0', // Object ID
			'query', // Context
			'created'
		);
	}

	/**
	 * Track when queries are run
	 *
	 * @param string $query
	 *
	 * @return void
	 */
	public function callback_querygenius_query_run( $query ) {
		$this->log(
			// Summary message
			sprintf(
				__( '%1$s', 'querygenius' ),
				$query,
			),
			// This array is compacted and saved as Stream meta
			[
				'action' => 'run',
				'query'  => $query,
			],
			'0', // Object ID
			'query', // Context
			'run'
		);
	}
}