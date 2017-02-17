<?php
   //would love to successfully convert everything to mysqli
   error_reporting( ~E_DEPRECATED & ~E_NOTICE );

   define('DB_SERVER', 'localhost');
   define('DB_USERNAME', 'root');
   define('DB_PASSWORD', 'password');
   define('DB_DATABASE', 'design_arena');
   $conn = mysql_connect(DB_SERVER,DB_USERNAME,DB_PASSWORD) or die ("Connection failed: " . mysql_error());
   $dbcon = mysql_select_db(DB_DATABASE) or die ("Database connection failed: " . mysql_error());
