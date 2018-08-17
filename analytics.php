<?php
namespace Vanderbilt\AnalyticsExternalModule;

$columns = [];
foreach(AnalyticsExternalModule::COLUMNS as $name=>$label){
	$columns[] = ['data' => $name, 'title' => $label];
}

?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha256-eSi1q2PG6J7g7ib17yAaWMcrr5GrtohYChqibrV7PBE=" crossorigin="anonymous" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha256-VsEqElsCHSGmnmHXGQzvoWjWwoznFSZc6hs7ARLRacQ=" crossorigin="anonymous"></script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/css/dataTables.bootstrap4.min.css" integrity="sha256-F+DaKAClQut87heMIC6oThARMuWne8+WzxIDT7jXuPA=" crossorigin="anonymous" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/js/jquery.dataTables.min.js" integrity="sha256-t5ZQTZsbQi8NxszC10CseKjJ5QeMw5NINtOXQrESGSU=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/js/dataTables.bootstrap4.min.js" integrity="sha256-hJ44ymhBmRPJKIaKRf3DSX5uiFEZ9xB/qx8cNbJvIMU=" crossorigin="anonymous"></script>

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
</style>

<h4><?=\REDCap::getProjectTitle()?> - Analytics</h4>

<p>The Analytics module automatically logs various user actions and displays them here, along with logs from other modules.  We will likely expand this reporting capability in the future.  Suggestions are always welcome.</p>

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
					return '<div class="record-column-content">' + record + '</div>'
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
			"columns": columns
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
