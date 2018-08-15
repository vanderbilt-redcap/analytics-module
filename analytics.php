<?php
namespace Vanderbilt\AnalyticsExternalModule;
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
</style>

<h1>Analytics</h1>

<p>The Analytics module automatically logs various user actions and displays them here.</p>

<p>In the future, we could choose saved reports (maybe dropdown with save button next to it).</p>

<table id="analytics-log-entries" class="table table-striped table-bordered">
	<thead>
		<?php
		foreach(AnalyticsExternalModule::COLUMNS as $name=>$label){
			echo "<th data-name='$name'>$label</th>";
		}
		?>
	</thead>
</table>

<script>
	$(function() {
	    $('#analytics-log-entries').DataTable( {
	        "processing": true,
	        "serverSide": true,
	        "ajax": <?=json_encode($module->getUrl('analytics-ajax.php'))?>,
			"autoWidth": false,
			"searching": false,
			"order": [[ 0, "desc" ]]
	    } );
	});
</script>
