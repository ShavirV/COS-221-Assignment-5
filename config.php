<?php
            $servername = "wheatley.cs.up.ac.za";
            //shavir info
            $username = "";
            $password = '';

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
                $conn->select_db("");
            }
?>
