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
    <title>Mail History</title>
    <style>
        body {
            font-family: 'Microsoft JhengHei', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        header {
            background-color: #343a40;
            color: white;
            padding: 10px;
            text-align: center;
            font-size: 24px;
            width: 100%;
            box-sizing: border-box;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
            text-align: center;
        }
        table, th, td {
            border: 1px solid #d1d1d1;
        }
        th, td {
            padding: 12px;
        }
        input[type="text"] {
            margin-right: 10px;
            width: 300px;
            padding: 8px;
            box-sizing: border-box;
        }
        th.sortable {
            cursor: pointer;
        }
        a.nostyle {
            text-decoration: none;
            color: inherit;
        }
        a {
            text-decoration: none;
            color: #007bff;
            transition: color 0.3s ease;
        }
        a:hover {
            color: #0056b3;
        }
        .pagination {
            text-align: center;
            margin-top: 20px;
        }
        .page-link {
            display: inline-block;
            padding: 8px 16px;
            margin: 0 5px;
            background-color: #f2f2f2;
            border: 1px solid #d4d4d4;
            border-radius: 4px;
            text-decoration: none;
            color: black;
            cursor: pointer;
        }
        .page-link.active {
            background-color: #4CAF50;
            color: white;
        }
        .return-link {
            display: inline-block;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border-radius: 5px;
            font-size: 16px;
            text-decoration: none;
            position: absolute;
            bottom: 10px;
        }
        .return-link:hover {
            background-color: #0056b3;
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
        歷史紀錄
    </header>
    <main>
    <?php
    require_once 'dbconfigure.php';
    require_once 'Dict_List.php';

    $conn = new mysqli($server, $db_username, $db_password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $search_value = "";
    if (isset($_POST['search'])) {
        $search_value = $_POST['search_value'];
        header("Location: Mail_History.php?search_value=$search_value");
        exit();
    } elseif (isset($_GET['search_value'])) {
        $search_value = $_GET['search_value'];
    }

    $sql = "SELECT * FROM History_Mail_List";

    if (!empty($search_value)) {
        $sql = "SELECT * FROM History_Mail_List WHERE Recipient_Name LIKE '%$search_value%' OR Recipient_ID LIKE '%$search_value%'";
    }

    $sortable_columns = array('Registration_Number', 'Recipient_Name', 'Recipient_ID', 'Department', 'Receiver_Name', 'Receiver_ID', 'Package_Type', 'Receive_Time', 'Pickup_Time', 'Revoke_Record');
    $sort_column = isset($_GET['sort']) && in_array($_GET['sort'], $sortable_columns) ? $_GET['sort'] : 'Receive_Time';
    $sort_order = (isset($_GET['order']) && $_GET['order'] == 'asc') ? 'asc' : 'desc';

    $sql .= " ORDER BY $sort_column $sort_order";
    $result = $conn->query($sql);

    $results_per_page = 10;
    $total_results = $result->num_rows;
    $total_pages = ceil($total_results / $results_per_page);

    $current_page = isset($_GET['page']) ? $_GET['page'] : 1;
    $start_index = ($current_page - 1) * $results_per_page;

    $sql .= " LIMIT $start_index, $results_per_page";
    $result = $conn->query($sql);

    if (isset($_POST['revoke'])) {
        $History_Mail_ID = $_POST['History_Mail_ID'];
        $check_sql = "SELECT Revoke_Record FROM History_Mail_List WHERE History_Mail_ID = $History_Mail_ID";
        $check_result = $conn->query($check_sql);

        if ($check_result->num_rows > 0) {
            $check_row = $check_result->fetch_assoc();

            if ($check_row['Revoke_Record'] == 'Y') {
                $update_sql = "UPDATE History_Mail_List SET Revoke_Record = 'N' WHERE History_Mail_ID = $History_Mail_ID";
                $conn->query($update_sql);
                
                $select_sql = "SELECT * FROM History_Mail_List WHERE History_Mail_ID = $History_Mail_ID";
                $revoke_result = $conn->query($select_sql);

                if ($revoke_result->num_rows > 0) {
                    $revoke_row = $revoke_result->fetch_assoc();
                    $insert_sql = "INSERT INTO Mail_List (Registration_Number, Recipient_Name, Recipient_ID, Department, Package_Type, Receive_Time)
                                   VALUES ('{$revoke_row['Registration_Number']}', '{$revoke_row['Recipient_Name']}', '{$revoke_row['Recipient_ID']}',
                                           '{$revoke_row['Department']}', '{$revoke_row['Package_Type']}', '{$revoke_row['Receive_Time']}')";
                    $conn->query($insert_sql);
                }
            }
        }
        header("Refresh:0");
    }
    ?>
    <form method="post" action="" style="margin-top: 20px; text-align: center;">
    <label for="search_value">搜尋:</label>
    <input type="text" id="search_value" name="search_value" value="<?php echo $search_value; ?>">
    <input type="submit" name="search" value="送出">
     </form>
    <table style="margin: 0 auto; background-color: white;">
        <tr>
            <th>註銷</th>
            <th class="sortable"><a href="?page=<?php echo $current_page; ?>&search_value=<?php echo $search_value; ?>&sort=Registration_Number&order=<?php echo ($sort_column == 'Registration_Number' ? ($sort_order == 'asc' ? 'desc' : 'asc') : 'asc'); ?>">掛號編號</a></th>
            <th class="sortable"><a href="?page=<?php echo $current_page; ?>&search_value=<?php echo $search_value; ?>&sort=Recipient_Name&order=<?php echo ($sort_column == 'Recipient_Name' ? ($sort_order == 'asc' ? 'desc' : 'asc') : 'asc'); ?>">收件人</a></th>
            <th class="sortable"><a href="?page=<?php echo $current_page; ?>&search_value=<?php echo $search_value; ?>&sort=Recipient_ID&order=<?php echo ($sort_column == 'Recipient_ID' ? ($sort_order == 'asc' ? 'desc' : 'asc') : 'asc'); ?>">收件人ID</a></th>
            <th class="sortable"><a href="?page=<?php echo $current_page; ?>&search_value=<?php echo $search_value; ?>&sort=Department&order=<?php echo ($sort_column == 'Department' ? ($sort_order == 'asc' ? 'desc' : 'asc') : 'asc'); ?>">系所單位</a></th>
            <th class="sortable"><a href="?page=<?php echo $current_page; ?>&search_value=<?php echo $search_value; ?>&sort=Receiver_Name&order=<?php echo ($sort_column == 'Receiver_Name' ? ($sort_order == 'asc' ? 'desc' : 'asc') : 'asc'); ?>">取件人</a></th>
            <th class="sortable"><a href="?page=<?php echo $current_page; ?>&search_value=<?php echo $search_value; ?>&sort=Receiver_ID&order=<?php echo ($sort_column == 'Receiver_ID' ? ($sort_order == 'asc' ? 'desc' : 'asc') : 'asc'); ?>">取件人ID</a></th>
            <th class="sortable"><a href="?page=<?php echo $current_page; ?>&search_value=<?php echo $search_value; ?>&sort=Package_Type&order=<?php echo ($sort_column == 'Package_Type' ? ($sort_order == 'asc' ? 'desc' : 'asc') : 'asc'); ?>">類型</a></th>
            <th class="sortable"><a href="?page=<?php echo $current_page; ?>&search_value=<?php echo $search_value; ?>&sort=Receive_Time&order=<?php echo ($sort_column == 'Receive_Time' ? ($sort_order == 'asc' ? 'desc' : 'asc') : 'asc'); ?>">收件日期</a></th>
            <th class="sortable"><a href="?page=<?php echo $current_page; ?>&search_value=<?php echo $search_value; ?>&sort=Pickup_Time&order=<?php echo ($sort_column == 'Pickup_Time' ? ($sort_order == 'asc' ? 'desc' : 'asc') : 'asc'); ?>">取件日期</a></th>
            <th class="sortable"><a href="?page=<?php echo $current_page; ?>&search_value=<?php echo $search_value; ?>&sort=Revoke_Record&order=<?php echo ($sort_column == 'Revoke_Record' ? ($sort_order == 'asc' ? 'desc' : 'asc') : 'asc'); ?>">註銷紀錄</a></th>
        </tr>

    <?php
    while ($row = $result->fetch_assoc()) {
        echo '<tr>
                <td>';
        
        if ($row['Revoke_Record'] == 'Y') {
            echo '<form method="post" action="">
                <input type="hidden" name="History_Mail_ID" value="'.$row['History_Mail_ID'].'">
                    <input type="submit" name="revoke" value="註銷" onclick="return confirmRevoke();">
                    </form>';
        }

        echo '</td>
                <td>' . $row['Registration_Number'] . '</td>
                <td>' . $row['Recipient_Name'] . '</td>
                <td>' . $row['Recipient_ID'] . '</td>
                <td>' . $Department_dict[$row['Department']] . '</td>
                <td>' . $row['Receiver_Name'] . '</td>
                <td>' . $row['Receiver_ID'] . '</td>
                <td>' . $Package_Type_dict[$row['Package_Type']] . '</td>
                <td>' . $row['Receive_Time'] . '</td>
                <td>' . $row['Pickup_Time'] . '</td>
                <td>' . $Revoke_dict[$row['Revoke_Record']] . '</td>
              </tr>';
    }

    echo '<div class="pagination">';
    if ($current_page > 1) {
        echo '<a class="page-link" href="?page=' . ($current_page - 1) . '&search_value=' . $search_value . '&sort=' . $sort_column . '&order=' . $sort_order . '">上一頁</a>';
    }
    for ($i = 1; $i <= $total_pages; $i++) {
        $active_class = ($i == $current_page) ? 'active' : '';
        echo '<a class="page-link ' . $active_class . '" href="?page=' . $i . '&search_value=' . $search_value . '&sort=' . $sort_column . '&order=' . $sort_order . '">' . $i . '</a>';
    }
    if ($current_page < $total_pages) {
        echo '<a class="page-link" href="?page=' . ($current_page + 1) . '&search_value=' . $search_value . '&sort=' . $sort_column . '&order=' . $sort_order . '">下一頁</a>';
    }
    echo '</div><br>';
    ?>
    </table>
    
    <div class="links">
        <a href="index.php">返回首頁</a>
    </div>

    <?php
        $conn->close();
    ?>
    <script>
        function confirmRevoke() {
            return confirm("確定要註銷郵件嗎？");
        }
    </script>
</body>
</html>