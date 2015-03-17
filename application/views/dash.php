<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Mapping BTR</title>

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
				$("#filterTA2").fadeToggle();
			  });

		});
	</script>
</head>
<body>

<div id="container">
	<h1>Welcome to Mapping BTR!</h1>

	<div id="body">
	        <div class="cell">
		        <?php
		        echo '<label for="crowdseed"><strong>Crowdfunder crawler</strong></label><br><br>';
		        echo form_open('/crowdfunder/scraper');
				$data = array(
							'name'        => 'seed',
							'id'          => 'seed',
							'value'       => 'http://www.crowdfunder.co.uk/',
							'size'        => 100
							);
		        echo '<label for="crowdseed">Seed project</label><br>'.form_input($data).'<br>';
		        echo '<label for="space"><i>Please enter the full url for the seed project. Example: "http://www.crowdfunder.co.uk/project-to-seed/"</i></label><br><br>';
		        //$js = 'onClick="some_function()"';
				$data = array(
							'name'        => 'filters',
							'id'          => 'filterCB',
							'value'       => 'true',
							'checked'     => TRUE
							);
				echo form_checkbox($data).'<label for="space">Keywords filter</label><br>';
		        //echo form_checkbox('filters', 'true', TRUE)."<br>";
				
		        echo '<div id="filterTA2"><label for="space"><i>Enter additional keywords separated by "/" Example: "cic/charity/otherkeyword"</i></label><br>';
		        $data = array(
							'name'        => 'keywords',
							'id'          => 'filterTA',
							'value'       =>  implode('/',array('CIC', 'foundation', 'charity','social enterprise', 'cooperative',
			'co-op', 'co-operative', 'ltd', 'association', 'company', 'business', 'trust'))
							);
				echo form_textarea($data)."<br><br></div>";
				/*
				$data = array(
							'name'        => 'colink',
							'id'          => 'colinkCB',
							'value'       => 'true',
							'checked'     => TRUE
							);
				echo form_checkbox($data).'<label for="space">Perform Colink analysis</label><br>';*/
				$data = array(
							'name'        => 'network',
							'id'          => 'networkCB',
							'value'       => 'true',
							'checked'     => TRUE
							);
				echo form_checkbox($data).'<label for="space">Create Network files</label><br><br>';
				$data = array(
							'name'        => 'finance',
							'id'          => 'financeCB',
							'value'       => 'true',
							'checked'     => TRUE
							);
				echo form_checkbox($data).'<label for="space">Create Statistics file</label><br><br>';
		        echo form_submit('mysubmit','Run');
		        echo form_close();
		        ?>
		</div>
		<div class="cell">
		        <?php
		        echo form_open('/spacehive/loaddata');
		        echo '<label for="space"><strong>Spacehive crawler</strong></label><br><br>';
				echo '<div id="keys"><label for="space"><i>Enter additional keywords separated by "/" Example: "cic/charity/otherkeyword"</i></label><br>';
		        $data = array(
							'name'        => 'keywords',
							'id'          => 'filterTA',
							'value'       =>  implode('/',array('CIC', 'foundation', 'charity','social enterprise', 'cooperative',
			'co-op', 'co-operative', 'ltd', 'association', 'company', 'business', 'trust'))
							);
				echo form_textarea($data)."<br><br></div>";
				$data = array(
							'name'        => 'finance',
							'id'          => 'financeCB',
							'value'       => 'true',
							'checked'     => TRUE
							);
				echo form_checkbox($data).'<label for="space">Create Statistics file</label><br><br>';
		        echo form_submit('mysubmit','Run');
		        echo form_close();
		
		        ?>
		</div>
		<div class="cell">
		        <?php
		        echo form_open('/config');
		        echo '<label for="config">Show requirements and config advice</label><br><br>';
		        echo form_submit('mysubmit','Show config');
		        echo form_close();
		
		        ?>
		</div>
	</div>

	<p class="footer">Page rendered in <strong>{elapsed_time}</strong> seconds</p>
</div>

</body>
</html>
