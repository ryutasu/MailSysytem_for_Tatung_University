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
    <title>Receive Mail</title>
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
            max-width: 800px;
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

        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
            text-align: center;
        }

        th, td {
            border: 1px solid #dee2e6;
            padding: 12px;
        }

        th {
            background-color: #343a40;
            color: white;
        }
        
        td {
            position: relative;
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
        取件
    </header>
    <main>
    <?php
    date_default_timezone_set('Asia/Taipei');
    require_once 'dbconfigure.php';
    require_once 'Dict_List.php';

    $conn = new mysqli($server, $db_username, $db_password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    $selected_mail_ids = isset($_GET['selected_mail_ids']) ? $_GET['selected_mail_ids'] : '';
    $sql = "SELECT * FROM Mail_List WHERE Registration_Number IN ($selected_mail_ids)";
    $result = $conn->query($sql);
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $recipient_type = $_POST["recipient_type"];
        $receiver_name = $_POST["receiver_name"];
        $receiver_id = $_POST["receiver_id"];
        $selected_mail_ids = $_POST["selected_mail_ids"];

        if (empty($receiver_name) || empty($receiver_id)) {
            echo '<script>';
            echo 'alert("取件人姓名和取件人ID為必填欄位");';
            echo '</script>';
        } else {
            $result = $conn->query("SELECT * FROM Mail_List WHERE Registration_Number IN ($selected_mail_ids)");
            $Now_Time = date("Y-m-d H:i:s");
            while ($row = $result->fetch_assoc()) {
                $sql = "INSERT INTO History_Mail_List (Registration_Number, Recipient_Name, Recipient_ID, Department, Receiver_Name, Receiver_ID, Package_Type, Receive_Time, Pickup_Time, Revoke_Record) 
                VALUES ('{$row['Registration_Number']}', '{$row['Recipient_Name']}','{$row['Recipient_ID']}','{$row['Department']}','$receiver_name','$receiver_id','{$row['Package_Type']}','{$row['Receive_Time']}','$Now_Time','Y')";
                if ($conn->query($sql) === TRUE) {
                    $delete_sql = "DELETE FROM Mail_List WHERE Registration_Number IN ($selected_mail_ids)";
                    $conn->query($delete_sql);
                    
                    header("Location: index.php");
                } else {
                    echo '<script>';
                    echo 'alert("取件失敗，請聯繫相關工作人員。");';
                    echo '</script>';
                } 
            }
        }
        
    }

    if (isset($_POST['Remove'])) {
        $mailIdsArray = explode(',', $selected_mail_ids);
        $numberToRemove = $_POST['registration_number'];
        $indexToRemove = array_search($numberToRemove, $mailIdsArray);
        if ($indexToRemove !== false) {
            unset($mailIdsArray[$indexToRemove]);
        }
        $selected_mail_ids = implode(',', $mailIdsArray);
        if ($selected_mail_ids) {
            header("Location: Receive_Mail.php?selected_mail_ids=$selected_mail_ids");
        } else {
            header("Location: index.php");
        }
    }
    ?>

    <form method="post" action="">
        <label for="recipient_type">選擇收件人類型:</label>
        <select id="recipient_type" name="recipient_type">
            <option value="student">學生</option>
            <option value="teacher">教師</option>
        </select><br>

        <label for="receiver_name">取件人姓名:</label>
        <input type="text" id="receiver_name" name="receiver_name"><br>

        <label for="receiver_id">取件人ID:</label>
        <input type="text" id="receiver_id" name="receiver_id"><br>

        <table>
            <tr>
                <th></th>
                <th>掛號編號</th>
                <th>收件人</th>
                <th>收件人ID</th>
                <th>系所單位</th>
                <th>類型</th>
                <th>收件日期</th>
            </tr>
            <?php
            // Reset pointer
            $result = $conn->query("SELECT * FROM Mail_List WHERE Registration_Number IN ($selected_mail_ids)");
            
            while ($row = $result->fetch_assoc()) {
                echo '<tr>
                        <td><form method="post" action="">
                            <input type="hidden" name="selected_mail_ids" value="' . $selected_mail_ids . '">
                            <input type="hidden" name="registration_number" value="' . $row['Registration_Number'] . '">
                            <input type="submit" name="Remove" value="取消收件">
                        </form></td>
                        <td>' . $row['Registration_Number'] . '</td>
                        <td>' . $row['Recipient_Name'] . '</td>
                        <td>' . $row['Recipient_ID'] . '</td>
                        <td>' . $Department_dict[$row['Department']] . '</td>
                        <td>' . $Package_Type_dict[$row['Package_Type']] . '</td>
                        <td>' . $row['Receive_Time'] . '</td>
                      </tr>';
            }
            ?>
        </table>
        <br>
        <button type="submit">確定取件</button>
    </form>
    </main>
    <?php
        $conn->close();
    ?>
    <div class="links">
        <a href="index.php">返回首頁</a>
    </div>
</body>
</html>
