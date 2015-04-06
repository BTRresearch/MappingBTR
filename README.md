# MappingBTR

Application that crawls the crowdfunding platforms Crowdfunder and Spacehive and identifies social projects / activity carried out by 'below the radar' organisations or informal community groups.

*Application setup:

- Install in your computer a local server environment like XAMPP or MAPP.

- Place the App folder MappingBTR in the right folder within your local server environment.

- Set the permissions of the data folder located in MappingBTR/application to Read and Write by Anyone.

- Create a database in MySQL.

- Enter the correct connection details to your database in the database.php file located in MappingBTR/application/config/.

- Create the necessary tables in your database to store the data collected from both crowdfunding platforms (the code to create the tables is provided on the app graphical interface).

*To run the application:

- Open a browser and type in the address bar the path of the application folder in your computer. When using XAMPP it would be something like: 'http://localhost/MappingBTR/index.php'.

- Choose the right settings and click the button 'run' in either the Crowdfunder or the Spacehive crawler.

*If the app failed to execute, try:

- Editing your php.ini file located in one of the folders included in your local server environment. In this file, set the date.timezone method to "Europe/Berlin" and the max_execution_time method to 1200.
