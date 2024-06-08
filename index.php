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
    <title>Mail List</title>
    <style>
        body {
            font-family: 'Microsoft JhengHei', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
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
        
        td {
            position: relative;
        }

        th {
            background-color: #343a40;
            color: white;
        }

        th a {
            color: white;
            text-decoration: none;
        }

        th a:hover {
            color: #007bff;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        input[type="text"] {
            margin-right: 10px;
            width: 70%;
            padding: 8px;
            box-sizing: border-box;
        }

        input[type="submit"] {
            padding: 8px;
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        button {
            padding: 10px;
            background-color: #28a745;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background-color: #218838;
        }

        .links {
            text-align: right;
            margin-top: 10px;
        }

        .links a {
            margin-right: 10px;
            color: #007bff;
            text-decoration: none;
        }

        .links a:hover {
            text-decoration: underline;
        }
	.top-right-buttons {
            position: absolute;
            top: 23px;
            right: 10px;
        }
        .custom-checkbox {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100%;
            cursor: pointer;
        }

        .custom-checkbox input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
            margin: 0;
        }

        .checkmark {
            position: relative;
            display: inline-block;
            width: 25px;
            height: 25px;
            background-color: #eee;
        }

        .custom-checkbox input:checked ~ .checkmark:after {
            content: '\2713';
            font-size: 20px;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #000;
        }
    </style>
</head>
<body>
    <?php
    require_once 'dbconfigure.php';
    require_once 'Dict_List.php';

    $conn = new mysqli($server, $db_username, $db_password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $search_value = "";
    if (isset($_GET['search_value'])) {
        $search_value = $_GET['search_value'];
    }
    $sql = "SELECT * FROM Mail_List";
    if (!empty($search_value)) {
        $sql = "SELECT * FROM Mail_List WHERE Recipient_Name LIKE '%$search_value%' OR Recipient_ID LIKE '%$search_value%'";
    }

    $sortable_columns = array('Registration_Number', 'Recipient_Name', 'Recipient_ID', 'Department', 'Package_Type', 'Receive_Time');
    $sort_column = isset($_GET['sort']) && in_array($_GET['sort'], $sortable_columns) ? $_GET['sort'] : 'Receive_Time';
    $sort_order = (isset($_GET['order']) && $_GET['order'] == 'asc') ? 'asc' : 'desc';

    $sql .= " ORDER BY $sort_column $sort_order";
    $result = $conn->query($sql);

    echo '<br><form method="post" action="index.php" onsubmit="return submitForm()">
            <label for="search_value">搜尋:</label>
            <input type="text" id="search_value" name="search_value" value="'.$search_value.'">
            <input type="submit" name="search" value="送出">
          </form>';

    echo '<table>
            <tr>
                <th></th>
                <th class="sortable"><a href="?search_value=' . $search_value . '&sort=Registration_Number&order=' . ($sort_column == 'Registration_Number' ? ($sort_order == 'asc' ? 'desc' : 'asc') : 'asc') . '">掛號編號</a></th>
                <th class="sortable"><a href="?search_value=' . $search_value . '&sort=Recipient_Name&order=' . ($sort_column == 'Recipient_Name' ? ($sort_order == 'asc' ? 'desc' : 'asc') : 'asc') . '">收件人</a></th>
                <th class="sortable"><a href="?search_value=' . $search_value . '&sort=Recipient_ID&order=' . ($sort_column == 'Recipient_ID' ? ($sort_order == 'asc' ? 'desc' : 'asc') : 'asc') . '">收件人ID</a></th>
                <th class="sortable"><a href="?search_value=' . $search_value . '&sort=Department&order=' . ($sort_column == 'Department' ? ($sort_order == 'asc' ? 'desc' : 'asc') : 'asc') . '">系所單位</a></th>
                <th class="sortable"><a href="?search_value=' . $search_value . '&sort=Package_Type&order=' . ($sort_column == 'Package_Type' ? ($sort_order == 'asc' ? 'desc' : 'asc') : 'asc') . '">類型</a></th>
                <th class="sortable"><a href="?search_value=' . $search_value . '&sort=Receive_Time&order=' . ($sort_column == 'Receive_Time' ? ($sort_order == 'asc' ? 'desc' : 'asc') : 'asc') . '">收件日期</a></th>
            </tr>';

    while ($row = $result->fetch_assoc()) {
        echo '<tr data-registration-number="' . $row['Registration_Number'] . '">
                <td><label class="custom-checkbox"><input type="checkbox" name="selected_mail[]" value="'.$row['Registration_Number'].'"><span class="checkmark"></span></label></td>
                <td>' . $row['Registration_Number'] . '</td>
                <td>' . $row['Recipient_Name'] . '</td>
                <td>' . $row['Recipient_ID'] . '</td>
                <td>' . $Department_dict[$row['Department']] . '</td>
                <td>' . $Package_Type_dict[$row['Package_Type']] . '</td>
                <td>' . $row['Receive_Time'] . '</td>
              </tr>';
    }

    echo '</table>';
    
    echo '<div style="text-align: center; margin-top: 20px;">
            <button onclick="collectMail()">統一取件</button>
          </div>';
    ?>

    <script>
        function collectMail() {
            var selectedCheckboxes = document.querySelectorAll('input[name="selected_mail[]"]:checked');

            if (selectedCheckboxes.length === 0) {
                alert("請選取信件");
            } else {
                var selectedMailIDs = Array.from(selectedCheckboxes).map(function (checkbox) {
                    return checkbox.value;
                });
                window.location.href = "Receive_Mail.php?selected_mail_ids=" + selectedMailIDs.join(",");
            }
        }
        function submitForm() {
            var searchValue = document.getElementById('search_value').value;
            window.location.href = "index.php?search_value=" + searchValue;
            return false;
        }
    </script>
    <div class="links">
        <div class="top-right-buttons">
            <a href="New_Mail.php">新增信件</a>|
            <a href="Mail_History.php">歷史紀錄</a>|
            <a href="Setting.php">設定</a>
        </div>
    </div>
</body>
</html>
