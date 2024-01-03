<div class="wrap">
	<h1>querygenius</h1>

	<p>Welcome to querygenius. Ask a question in the box below and querygenius will generate an SQL query for your question.<br>After running that query, you'll have your answer! querygenius never modifies any of the data in your database.</p>

	<form id="question-form" method="post" action="<?php echo admin_url( 'admin.php' ); ?>" novalidate="novalidate">
		<input type="hidden" name="action" value="translate_query">

		<div class="form-field term-description-wrap">
			<label for="question"><strong>Your Question</strong></label>
			<textarea name="question" id="question" rows="5" cols="40" placeholder="How many users published posts last week?"></textarea>
		</div>

		<input class="button button-primary" type="submit" value="Translate to SQL" />

		<img id="question-spinner" src="/wp-admin/images/spinner.gif" alt="Loading spinner" style="margin-top: 6px;margin-left: 5px;display: none;">
	</form>

	<form id="query-form" method="post" action="<?php echo admin_url( 'admin.php' ); ?>" novalidate="novalidate" style="margin-top: 50px;">
		<input type="hidden" name="action" value="run_query">


		<div class="form-field term-description-wrap">
			<label for="query"><strong>Your Query</strong></label>
			<code-input style="margin: 0;margin-top: 10px;"lang="SQL" name="query" id="query" placeholder="Ask a question above and your query will be populated here." template="syntax-highlighted" disabled></code-input>
		</div>

		<input class="button button-primary" id="query-submit" type="submit" value="Run query" disabled />

		<img id="query-spinner" src="/wp-admin/images/spinner.gif" alt="Loading spinner" style="margin-top: 6px;margin-left: 5px;display: none;">

		<p id="query-error" style="display:none;"><strong>Make sure your query contains only SELECT statements before continuing.</strong></p>
	</form>

	<div id="answer" style="display:none;">
		<div id="answer-data"></div>
	</div>
</div>