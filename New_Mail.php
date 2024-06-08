<!DOCTYPE html>
<?php
    session_start();

    // Check if the user is not logged in (session variable not set)
    if (!isset($_SESSION['Username'])) {
        // Redirect to the login page
        header("Location: Loginpage.php");
        exit(); // Make sure to stop the script execution after redirecting
    }

    // Continue with the rest of your code

    if(!empty($_POST['Send']) && !empty($_POST['Username']) && !empty($_POST['Password'])) {      
        require_once "login.php";
    }
?>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Mail</title>
    <style>
        body {
            font-family: 'Microsoft JhengHei', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #343a40;
            color: white;
            padding: 10px;
            text-align: center;
            font-size: 24px;
        }

        main {
            max-width: 600px;
            margin: 20px auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        form {
            margin-top: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
        }

        select,
        input {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        .error {
            color: red;
            margin-top: 10px;
        }

        .links {
            text-align: center;
            margin-top: 20px;
        }

        .links a {
            display: inline-block;
            padding: 12px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
        }

        .links a:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
	<header>
            新增信件
    	</header>
    <main>
    <?php
    date_default_timezone_set('Asia/Taipei');
    include 'dbconfigure.php';
    include 'Dict_List.php';
    
    $conn = new mysqli($server, $db_username, $db_password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    $recipient_type = isset($_GET['recipient_type']) ? $_GET['recipient_type'] : '';
    
    if (isset($_POST['submit'])) {
        $registration_number = $_POST['registration_number'];
        $recipient_name = $_POST['recipient_name'];
        $recipient_id = $_POST['recipient_id'];
        $department = $_POST['department'];
        $package_type = $_POST['package_type'];

        $error = false;
        $error_message = '';
        
        if (empty($registration_number) || empty($recipient_name) || empty($recipient_id) || empty($department) || empty($package_type)) {
            $error = true;
            $error_message = '所有欄位均為必填';
        } else {
            $sql = "SELECT * FROM Mail_List WHERE Registration_Number = '$registration_number'";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                $error = true;
                $error_message = '資料表內已有此信件';
            } else {
                $Now_Time = date("Y-m-d H:i:s");
                $sql = "INSERT INTO Mail_List (Registration_Number, Recipient_Name, Recipient_ID, Department, Package_Type, Receive_Time) 
                        VALUES ('$registration_number', '$recipient_name', '$recipient_id', '$department', '$package_type', '$Now_Time')";

                if ($conn->query($sql) === TRUE) {
                    echo '<div>新郵件已新增到資料表</div>';
                } else {
                    echo '<div class="error">新增郵件時出現問題</div>';
                }
            }
        }

        if ($error) {
            echo '<div class="error">' . $error_message . '</div>';
        }
    }
    ?>
    
    <form method="post" action="">
        <label for="recipient_type">選擇收件人類型:</label>
        <select id="recipient_type" name="recipient_type">
            <option value="student" <?php if ($recipient_type === 'student') echo 'selected'; ?>>學生</option>
            <option value="teacher" <?php if ($recipient_type === 'teacher') echo 'selected'; ?>>教師</option>
        </select><br>
        
        <label for="registration_number">掛號編號:</label>
        <input type="text" id="registration_number" name="registration_number"><br>

        <label for="recipient_name">收件人:</label>
        <input type="text" id="recipient_name" name="recipient_name"><br>

        <label for="recipient_id">收件人ID:</label>
        <input type="text" id="recipient_id" name="recipient_id"><br>

        <label for="department">系所單位:</label>
        <select id="department" name="department">
            <?php
            foreach ($Department_dict as $key => $value) {
                echo "<option value='$key'>$value</option>";
            }
            ?>
        </select><br>

        <label for="package_type">類型:</label>
        <select id="package_type" name="package_type">
            <?php
            foreach ($Package_Type_dict as $key => $value) {
                echo "<option value='$key'>$value</option>";
            }
            ?>
        </select><br>
        
        <button type="submit" name="submit">新增郵件</button>
    </form>
    </main>
    <div class="links">
        <a href="index.php">返回首頁</a>
    </div>
</body>
</html>
