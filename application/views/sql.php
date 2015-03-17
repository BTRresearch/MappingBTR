<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Welcome to BeTRaGMap</title>

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
	td{
		border: 1px solid #D0D0D0;
		padding: 2px;
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
</head>
<body>

<div id="container">
<!--<h1>Load csv files:</h1>

	<div id="body">
        <div class="cell">
		        <?php
		        echo form_open('/spacehive/importCSVFiles');
		        echo '<label for="import">Import CSV files for CIC and Charities</label><br><label for="import">Files should be in data folder</label><br><br><br>';
		        echo form_submit('mysubmit','Import CSVs');
		        echo form_close();
		
		        ?>
		</div></div>-->
	<h1>Requirements</h1>

	<div id="body">
	        <div class="cell">
	        		<p><u>Application setup:</u></p>
	        		<p>- Install in your computer a local server environment like <i>XAMPP</i> or <i>MAPP.</i></p>
	        		<p>- Place the App folder <i>MappingBTR</i> in the right folder within your local server environment.</p>
	        		<p>- Set the permissions of the <i>data</i> folder located in <i>MappingBTR/application</i> to <i>Read and Write by Anyone</i>.</p>
	        		<p>- Create a database in <i>MySQL.</i></p>
	        		<p>- Enter the correct connection details to your  database in the <i>database.php</i> file located in <i>MappingBTR/application/config/</i>.</p>
	        		<p>- Create the necessary tables in your database to store the data collected from both crowdfunding platforms (the code to create the tables is provided below)*.</p>
	        		<p><u>To run the app: </u></p>
	        		<p>-Choose the right settings and click the button 'run' in either the Crowdfunder or the Spacehive crawler.</p>
	        		<p><u>If the app failed to execute, try:</u></p>
	        		<p>- Editing your php.ini file located in one of the folders included in your local server environment. In this file, set  the <i>date.timezone</i> method to <i>"Europe/Berlin"</i> and the <i>max_execution_time</i> method to <i>1200</i>.</p>

	        </div>	
	        <div class="cell">
				<p>*Your database should contain the following tables for Crowdfunder:</p>
				<table id="table_results" class="data ajax"><thead><tr>
					<th class="draggable" data-column="Table">Table
					</th><th class="draggable" data-column="Create Table">Create Table
					</th>
					</tr></thead><tbody>
					<tr class="odd"><td class="data  not_null    ">backer</td>
					<td class="data  not_null    ">CREATE TABLE backer (<br> &nbsp;ID int(11) NOT NULL AUTO_INCREMENT,<br> &nbsp;NAME varchar(100) NOT NULL,<br> &nbsp;URL varchar(100) NOT NULL,<br> &nbsp;SEED varchar(200) NOT NULL,<br> &nbsp;PRIMARY KEY (ID)<br>) ENGINE=InnoDB AUTO_INCREMENT=848 DEFAULT CHARSET=latin1</td>
					</tr>
					<tr class="odd"><td class="data  not_null    ">projects</td>
					<td class="data  not_null    ">CREATE TABLE projects (<br /> &nbsp;ID int(10) NOT NULL AUTO_INCREMENT,<br /> &nbsp;NAME varchar(200) NOT NULL,<br /> &nbsp;URL varchar(200) NOT NULL,<br /> &nbsp;OWNER varchar(200) NOT NULL,<br /> &nbsp;COUNTED int(10) NOT NULL,<br /> &nbsp;GOAL int(10) NOT NULL,<br /> &nbsp;RAISED int(10) NOT NULL,<br /> &nbsp;FUNDEDDATE date NOT NULL,<br /> &nbsp;BACKERS int(10) NOT NULL,<br /> &nbsp;DESCRIPTION longtext NOT NULL,<br /> &nbsp;SEED varchar(200) NOT NULL,<br /> &nbsp;BACKERLIST varchar(1000) NOT NULL,<br /> &nbsp;PRIMARY KEY (ID)<br />) ENGINE=MyISAM AUTO_INCREMENT=6679 DEFAULT CHARSET=latin1</td>
					</tr>
					<tr class="odd"><td class="data  not_null    ">results</td>
					<td class="data  not_null    ">CREATE TABLE results (<br /> &nbsp;ID int(10) NOT NULL AUTO_INCREMENT,<br /> &nbsp;NAME varchar(200) NOT NULL,<br /> &nbsp;URL varchar(200) NOT NULL,<br /> &nbsp;OWNER varchar(200) NOT NULL,<br /> &nbsp;COUNTED int(10) NOT NULL,<br /> &nbsp;GOAL int(10) NOT NULL,<br /> &nbsp;RAISED int(10) NOT NULL,<br /> &nbsp;FUNDEDDATE date NOT NULL,<br /> &nbsp;BACKERS int(10) NOT NULL,<br /> &nbsp;DESCRIPTION longtext NOT NULL,<br /> &nbsp;SEED varchar(200) NOT NULL,<br /> &nbsp;BACKERLIST varchar(1000) NOT NULL,<br /> &nbsp;PRIMARY KEY (ID)<br />) ENGINE=MyISAM AUTO_INCREMENT=940 DEFAULT CHARSET=latin1</td>
					</tr>
					<tr class="odd"><td class="data  not_null    ">files</td>
					<td class="data  not_null    ">CREATE TABLE files (<br /> &nbsp;ID int(11) NOT NULL AUTO_INCREMENT,<br /> &nbsp;EDGES varchar(200) NOT NULL,<br /> &nbsp;NODES varchar(200) NOT NULL,<br /> &nbsp;PRIMARY KEY (ID)<br />) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1</td>
					</tr>
					<tr class="odd"><td class="data  not_null    ">nodes</td>
					<td class="data  not_null    ">CREATE TABLE nodes (<br /> &nbsp;ID int(10) NOT NULL AUTO_INCREMENT,<br /> &nbsp;NODE varchar(200) NOT NULL,<br /> &nbsp;LABEL varchar(200) NOT NULL,<br /> &nbsp;PCATEGORY varchar(200) NOT NULL,<br /> &nbsp;NLINKS int(10) NOT NULL,<br /> &nbsp;PRIMARY KEY (ID)<br />) ENGINE=MyISAM AUTO_INCREMENT=116 DEFAULT CHARSET=latin1</td>
					</tr>
					<tr class="odd"><td class="data  not_null    ">edges</td>
					<td class="data  not_null    ">CREATE TABLE edges (<br /> &nbsp;ID int(10) NOT NULL AUTO_INCREMENT,<br /> &nbsp;SOURCE varchar(100) NOT NULL,<br /> &nbsp;TARGET varchar(100) NOT NULL,<br /> &nbsp;TYPE varchar(20) NOT NULL,<br /> &nbsp;LABEL varchar(2000) NOT NULL,<br /> &nbsp;WEIGHT int(10) NOT NULL,<br /> &nbsp;PRIMARY KEY (ID)<br />) ENGINE=MyISAM AUTO_INCREMENT=2336 DEFAULT CHARSET=latin1</td>
					</tr>
					<tr class="odd"><td class="data  not_null    ">crowdfunderstats</td>
					<td class="data  not_null    ">CREATE TABLE crowdfunderstats (<br /> &nbsp;ID int(11) NOT NULL AUTO_INCREMENT,<br /> &nbsp;PROJECTID int(10) NOT NULL,<br /> &nbsp;PROJECTNAME varchar(200) NOT NULL,<br /> &nbsp;BACKERID int(10) NOT NULL,<br /> &nbsp;BACKERNAME varchar(60) NOT NULL,<br /> &nbsp;PLEDGED int(10) NOT NULL,<br /> &nbsp;PRIMARY KEY (ID)<br />) ENGINE=MyISAM AUTO_INCREMENT=15346 DEFAULT CHARSET=latin1</td>
					</tr>
					
					</tbody>
					</table>
					<table id="table_results" class="data ajax"><thead><tr>
					<th class="draggable" data-column="Table">Table
					</th><th class="draggable" data-column="Create Table">Create Table
					</th>
					</tr></thead><tbody>
					<p>And for Spacehive:</p>
					<tr class="odd"><td class="data  not_null    ">spacehive</td>
					<td class="data  not_null    ">CREATE TABLE spacehive (<br> &nbsp;ID int(11) NOT NULL AUTO_INCREMENT,<br> &nbsp;URL varchar(200) NOT NULL,<br> &nbsp;NAME varchar(100) NOT NULL,<br> &nbsp;PROMOTER varchar(100) NOT NULL,<br> &nbsp;BACKERS int(11) NOT NULL,<br> &nbsp;GOAL int(11) NOT NULL,<br> &nbsp;RAISED int(11) NOT NULL,<br> &nbsp;FDATE varchar(100) NOT NULL,<br> &nbsp;DESCRIPTION longtext NOT NULL,<br> &nbsp;PDESCRIPTION longtext NOT NULL,<br> &nbsp;PRIMARY KEY (ID)<br>) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=latin1</td>
					</tr>
					<tr class="odd"><td class="data  not_null    ">cic</td>
					<td class="data  not_null    ">CREATE TABLE cic (<br> &nbsp;ID varchar(100) NOT NULL,<br> &nbsp;NAME varchar(100) NOT NULL,<br> &nbsp;STATUS varchar(100) NOT NULL,<br> &nbsp;PRIMARY KEY (ID)<br>) ENGINE=InnoDB DEFAULT CHARSET=latin1</td>
					</tr>
					<tr class="odd"><td class="data  not_null    ">charity</td>
					<td class="data  not_null    ">CREATE TABLE charity (<br> &nbsp;ID varchar(100) NOT NULL,<br> &nbsp;NAME varchar(100) NOT NULL,<br> &nbsp;STATUS varchar(100) NOT NULL,<br> &nbsp;PRIMARY KEY (ID)<br>) ENGINE=InnoDB DEFAULT CHARSET=latin1</td>
					</tr>
					<tr class="odd"><td class="data  not_null    ">spacehivestats</td>
					<td class="data  not_null    ">CREATE TABLE spacehivestats (<br /> &nbsp;PROJECTID int(11) NOT NULL,<br /> &nbsp;PROJECTNAME varchar(80) NOT NULL,<br /> &nbsp;BACKERID int(11) NOT NULL AUTO_INCREMENT,<br /> &nbsp;BACKERNAME varchar(80) NOT NULL,<br /> &nbsp;PLEDGED int(11) NOT NULL,<br /> &nbsp;PRIMARY KEY (BACKERID)<br />) ENGINE=MyISAM AUTO_INCREMENT=3361 DEFAULT CHARSET=latin1</td>
					</tr>
					</tbody>
					</table>
			</div>
		
	</div>

	<p class="footer">Page rendered in <strong>{elapsed_time}</strong> seconds</p>
</div>

</body>
</html>
