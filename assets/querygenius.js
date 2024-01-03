(function($) {
	function debounce(func, delay) {
	    let debounceTimer;
	    return function() {
	        const context = this;
	        const args = arguments;
	        clearTimeout(debounceTimer);
	        debounceTimer = setTimeout(() => func.apply(context, args), delay);
	    };
	}

	function checkQuery() {
    	let parser = new NodeSQLParser.Parser()
        let ast = parser.astify($('#query').val());

        if (Array.isArray(ast)) {
	    	ast = ast;
	    } else {
	        ast = [ast];
	    }

        for (const query of ast) {
	      if ("select" !== query.type) {
	        $('#query-error').show();
	        $('#query-submit').prop("disabled", true);
	      } else {
	      	$('#query-error').hide();
			$('#query-submit').prop("disabled", false);
	      }
	    }
    }

	$('#question-form').submit(function(event) {
        event.preventDefault();

        var formData = $(this).serialize();

        $('#question-spinner').css('display','inline-block');

        $.ajax({
            url: wpApi.root + 'querygenius/v1/translate',
            method: 'POST',
             beforeSend: function ( xhr ) {
        		xhr.setRequestHeader( 'X-WP-Nonce', wpApi.nonce );
    		},
            data: formData,
            success: function(response) {
                // Handle success
                $('#query textarea').prop("disabled", false);

                // Fill query with returned query
                $('#query').val(sqlFormatter.format(response.trim()));

                let correct_height = $('#query pre code').height() + 75;

                $('#query').height(correct_height);

                checkQuery();

                // TO-DO: Handle any suggestions returned by the API
                console.log('Suggestions: ' + response.suggestions);

                $('#question-spinner').css('display','none');
            },
            error: function(error) {
                // Handle error
                console.log('Error:', error);
            }
        });
    });

    $('#query-form').submit(function(event) {
        event.preventDefault();

        var formData = $(this).serialize();

        $('#query-spinner').css('display','inline-block');

        $.ajax({
            url: wpApi.root + 'querygenius/v1/query',
            method: 'POST',
             beforeSend: function ( xhr ) {
        		xhr.setRequestHeader( 'X-WP-Nonce', wpApi.nonce );
    		},
            data: formData,
            success: function(response) {
            	$('#answer').css('display','block');
                $('#query-spinner').css('display','none');

                console.log(response);

                $('#answer-data').empty();
                // If data is array, populate with table

                let columnNames = Object.keys(response[0]);
                let dataSet = response.map(object => Object.values(object));

                // Truncate DB values to 100 chars to make the table neater
                dataSet = dataSet.map(subArr => 
			        subArr.map(str => 
			            typeof str === 'string' && str.length > 100 ? str.substring(0, 100).replace(/(<([^>]+)>)/gi, "") + '...': str
			        )
			    );

                // Create a new table element
				let table = $('<table></table>').attr('id', 'answer-dataset');
				table.appendTo('#answer-data');

                let rendered_table = new DataTable('#answer-dataset', {
				    columns: columnNames.map(key => ({ title: key })),
				    data: dataSet,
                    buttons: [
                       { extend: 'csv', text: 'Download as CSV' },
                       { extend: 'excel', text: 'Download as Excel (xlsx)' }
                    ]
				});

                rendered_table.buttons().container()
                    .insertBefore( '#answer-dataset_filter' );
            },
            error: function(error) {
                // Handle error
                console.log('Error:', error);
            }
        });
    });

    $(document).ready(function(){
    	codeInput.registerTemplate("syntax-highlighted", codeInput.templates.prism(Prism, [
    		new codeInput.plugins.Indent(true, 2)
    	]));

    	$('#query').on('input', debounce(checkQuery, 250));
    });
	
})( jQuery );