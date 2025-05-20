<?php
            $servername = "wheatley.cs.up.ac.za";
            //shavir info
            $username = "u23718146";
            $password = 'IIIPL4Q62ZB4O6HGENQWS4AT3UUXA5K2';

            //create connection
            $conn = new mysqli($servername,$username,$password);
            //check connection
            if ($conn->connect_error)
            {
                die("Connection failed: ".$conn->connect_error);
            }
            
            else
            {
                // put db name in ""
                $conn->select_db("u23718146_null&void");
            }
?>