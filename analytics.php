<?php
namespace Vanderbilt\AnalyticsExternalModule;

$columns = [];
foreach(AnalyticsExternalModule::$COLUMNS as $name=>$label){
	$columns[] = [
		'data' => $name,
		'title' => $label,
		'sClass' => "cell-$name"
	];
}

?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha256-eSi1q2PG6J7g7ib17yAaWMcrr5GrtohYChqibrV7PBE=" crossorigin="anonymous" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha256-VsEqElsCHSGmnmHXGQzvoWjWwoznFSZc6hs7ARLRacQ=" crossorigin="anonymous"></script>

<link rel="stylesheet" href="https://cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css" integrity="sha384-EkHEUZ6lErauT712zSr0DZ2uuCmi3DoQj6ecNdHQXpMpFNGAQ48WjfXCE5n20W+R" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.5.2/css/buttons.dataTables.min.css" integrity="sha384-4zgE69bwrfaNYUZPA2TaKwT/mjqMcBEvQmjHf1qkjg3c2JSWfEGflXXz6xXBLGGN" crossorigin="anonymous">
<script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js" integrity="sha384-rgWRqC0OFPisxlUvl332tiM/qmaNxnlY46eksSZD84t+s2vZlqGeHrncwIRX7CGp" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/buttons/1.5.2/js/dataTables.buttons.min.js" integrity="sha384-zOjU8Lmrn7aY/0op2Zr4DRXhg0el3XJ4SEMVakZ7bni+KP5F9geHOJ0cWYSvj0HN" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js" integrity="sha384-uiSTMvD1kcI19sAHJDVf68medP9HA2E2PzGis9Efmfsdb8p9+mvbQNgFhzii1MEX" crossorigin="anonymous"></script>

<style>
	body{
		padding: 20px;
	}

	h4{
		margin-bottom: 20px;
	}

	.log-parameter{
		font-size: .77rem;
	}

	.log-parameter > span{
		font-weight: bold;
		font-size: .77rem;
		text-transform: capitalize;
		letter-spacing: .5px;
	}

	.record-column-content{
		max-width: 250px;
	}

	.cell-message{
		text-transform: capitalize;
	}
</style>

<h4><?=\REDCap::getProjectTitle()?> - Analytics</h4>

<p>The Analytics module automatically logs various user actions and displays them here, along with logs from other modules.  We will likely expand this reporting capability in the future.  Suggestions are always welcome.</p>

<?php
// This method will probably make it into 8.7.2
if(!method_exists($module, 'getQueryLogsSql')){
	?><p style="color: red">This report is not supported in your REDCap version.</p><?php
	die();
}
?>

<table id="analytics-log-entries" class="table table-striped table-bordered"></table>

<script>
	$(function(){
		$.fn.dataTable.ext.errMode = 'throw';

		var formatParamName = function(name){
			var parts = name.split(' ')

			for(var i=0; i<parts.length; i++){
				var part = parts[i]

				if(['id', 'url'].indexOf(part) !== -1){
					parts[i] = part.toUpperCase()
				}
			}

			return parts.join(' ')
		}

		var columns = <?=json_encode($columns)?>;

		columns.forEach(function(column){
			if(column.data === 'record'){
				column.render = function(record){
					if(record === null){
						return ''
					}
					else{
						return '<div class="record-column-content">' + record + '</div>'
					}
				}
			}
		})

		columns.push({
			data: 'parameters',
			title: 'Parameters',
			orderable: false,
			render: function(parameters){
				var html = '';

				for(var name in parameters){
					var value = parameters[name]
					name = formatParamName(name)
					html += "<div class='log-parameter'><span>" + name + ":</span> " + value + "<br>"
				}

				return html
			}
		})

		var table = $('#analytics-log-entries').DataTable({
	        "processing": true,
	        "serverSide": true,
	        "ajax": <?=json_encode($module->getUrl('analytics-ajax.php'))?>,
			"autoWidth": false,
			"searching": false,
			"order": [[ 0, "desc" ]],
			"columns": columns,
			// Uncomment the following line to enable the button below it.
			//"dom": 'Bfrtip',
			"buttons": [
				{
					text: 'Export as CSV',
					action: function (e, dt, node, config) {
						$.ajax({
							"url": <?=json_encode($module->getUrl('csv.php'))?>,
							"data": dt.ajax.params(),
							"success": function(res, status, xhr) {
								var csvData = new Blob([res], {type: 'text/csv;charset=utf-8;'});
								var csvURL = window.URL.createObjectURL(csvData);
								var tempLink = document.createElement('a');
								tempLink.href = csvURL;
								tempLink.setAttribute('download', 'data.csv');
								tempLink.click();
							}
						});
					}
				}
			]
	    }).on( 'draw', function () {
			var ellipsisButtons = $('.paginate_button.disabled')
			ellipsisButtons.removeClass('disabled')
			ellipsisButtons.find('a').click(function(e){
				setTimeout(function(){
					var page = prompt("What page number would like like to jump to?");
					var pageCount = table.page.info().pages

					if(isNaN(page) || page < 1 || page > pageCount){
						alert('You must enter a page between 1 and ' + pageCount)
					}
					else{
						table.page(page-1).draw('page')
					}
				}, 0)

				return false
			})
	    })
	});
</script>
