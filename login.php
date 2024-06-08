<?php

 if(isset($_POST['Username'])&& isset($_POST['Password']))
 {
	 require_once "dbconfigure.php";
         $conn=new mysqli($server,$db_username,$db_password,$dbname);
         mysqli_query($conn,"set names utf8");
         //echo $server;
         if($conn->connect_error){
            die("Connection failed:" . $conn->connect_error);
         }
	 $Username=$_POST['Username'];
	 $Password=$_POST['Password'];
	 $sql="select * from users where Username='$Username'";
	 $result=$conn->query($sql);
	 $row = $result->fetch_assoc();
	 if(!empty($row))
	 {
		$password_hash= $row['password'];
	 }
	if($result->num_rows >0&&password_verify($Password, $password_hash))
	{
			
		$_POST['Result']="success";
	}
	else
	{
		$_POST['Result']="failure";
	}
 }