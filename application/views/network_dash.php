<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Welcome to BTRMap!</title>

	<style type="text/css">

	::selection{ background-color: #E13300; color: white; }
	::moz-selection{ background-color: #E13300; color: white; }
	::webkit-selection{ background-color: #E13300; color: white; }

	body {
		background-color: #fff;
		margin: 40px;
		font: 13px/20px normal Helvetica, Arial, sans-serif;
		color: #4F5155;
	}
        i {
                font-family: Consolas, Monaco, Courier New, Courier, monospace;
		font-size: 10px;
		
		color: #333333;
		
		
        }
	a {
		color: #003399;
		background-color: transparent;
		font-weight: normal;
	}

	h1 {
		color: #444;
		background-color: transparent;
		border-bottom: 1px solid #D0D0D0;
		font-size: 19px;
		font-weight: normal;
		margin: 0 0 14px 0;
		padding: 14px 15px 10px 15px;
	}

	code {
		font-family: Consolas, Monaco, Courier New, Courier, monospace;
		font-size: 12px;
		background-color: #f9f9f9;
		border: 1px solid #D0D0D0;
		color: #002166;
		display: block;
		margin: 14px 0 14px 0;
		padding: 12px 10px 12px 10px;
	}

	#body{
		margin: 0 15px 0 15px;
	}
	
	p.footer{
		text-align: right;
		font-size: 11px;
		border-top: 1px solid #D0D0D0;
		line-height: 32px;
		padding: 0 10px 0 10px;
		margin: 20px 0 0 0;
	}
	.cell{
	        font-family: Consolas, Monaco, Courier New, Courier, monospace;
		font-size: 12px;
		background-color: #EEEEEE;
		border: 1px solid #D0D0D0;
		color: #002166;
		display: block;
		margin: 14px 0 14px 0;
		padding: 12px 10px 12px 10px; 
	}
	#container{
		margin: 10px;
		border: 1px solid #D0D0D0;
		-webkit-box-shadow: 0 0 8px #D0D0D0;
	}
	</style>
	
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
	<script>
		$(document).ready(function(){
			$("#filterCB").click(function(){
				$("#filterTA").fadeToggle();
			  });

		});
	</script>
</head>
<body>

<div id="container">


	<div id="body">
	        <div class="cell">
		        <?php
		        echo '<label for="crowdseed"><strong>The Gephi files for the results below:</strong></label><br><br>';
				echo "<a href=".base_url()."index.php/download/plaintext/".$links['edges']." target='_blank'>Edges CSV file</a><br>";
				echo "<a href=".base_url()."index.php/download/plaintext/".$links['nodes']." target='_blank'>Nodes CSV file</a><br>";
				
		        ?>
		</div>
	</div>

	<!--<p class="footer">Page rendered in <strong>{elapsed_time}</strong> seconds</p>-->
</div>

</body>
</html>
