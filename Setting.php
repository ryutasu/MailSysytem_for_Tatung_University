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
    <title>Setting Page</title>
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
            display: flex;
            max-width: 900px;
            margin: 20px auto;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .book-page {
            width: 100px;
            padding: 20px;
            border-right: 1px solid black;
            display: flex;
            flex-direction: column;
            align-items: center; /* 居中對齊 */
        }

        .content-page {
            width: 700px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center; /* 居中對齊 */
        }

        h2 {
            color: #007bff;
        }

        ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        li {
            margin-bottom: 10px;
            cursor: pointer;
        }

        li:hover {
            text-decoration: underline;
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
        
        a {
            text-decoration: none;
            color: #000000;
            transition: color 0.3s ease;
        }
    </style>
</head>
<body>
    <header>
        設定
    </header>
    <main>
    <?php
        date_default_timezone_set('Asia/Taipei');
        include 'dbconfigure.php';

        $conn = new mysqli($server, $db_username, $db_password, $dbname);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
    ?>
        <div class="book-page">
            <h2>選項</h2>
            <ul>
                <li id="option1"><a href="Setting.php?option=1">系所設定</a></li>
                <li id="option2"><a href="Setting.php?option=2">包裹屬性</a></li>
                <li id="option3"><a href="Setting.php?option=3">更改密碼</a></li>
                <li id="option3"><a href="Setting.php?option=4">登出</a></li>
            </ul>
        </div>
        <div class="content-page">
            <h2>設定頁面</h2>
                <?php
                $selectedOption = isset($_GET['option']) ? $_GET['option'] : '1';

                switch ($selectedOption) {
                    case '1':
                        ?>
                            <h3>所屬系所設定</h3>
                            <?php
                                // Handle form submission (update or delete)
                                if ($_SERVER["REQUEST_METHOD"] == "POST") {
                                    if (isset($_POST["edit_id"])) {
                                        // Update record
                                        $edit_id = $_POST["edit_id"];
                                        $edit_dkey = $_POST["edit_dkey"];
                                        $edit_name = $_POST["edit_name"];
                                        if (ctype_lower($edit_dkey)) {
                                            $edit_dkey = strtoupper($edit_dkey);
                                        }

                                        // Check if Dkey is a single alphabet
                                        if (!preg_match('/^[A-Za-z]$/', $edit_dkey)) {
                                            echo '<div class="error">Dkey 必須為一個英文字母</div>';
                                        } else {
                                            // Check if Dkey is unique
                                            $check_duplicate_sql = "SELECT * FROM DictList WHERE Dkey = '$edit_dkey' AND Category = 'A' AND Valid = 'Y' AND ID != $edit_id";
                                            $duplicate_result = $conn->query($check_duplicate_sql);

                                            if ($duplicate_result->num_rows == 0) {
                                                // Update record if Dkey is unique
                                                $update_sql = "UPDATE DictList SET Dkey = '$edit_dkey', Name = '$edit_name' WHERE ID = $edit_id";
                                                if ($conn->query($update_sql) === TRUE) {
                                                    echo '<div class="success">資料更新成功！</div>';
                                                } else {
                                                    echo '<div class="error">資料更新失敗</div>';
                                                }
                                            } else {
                                                echo '<div class="error">Dkey 已存在，請輸入其他值</div>';
                                            }
                                        }
                                    } elseif (isset($_POST["delete_id"])) {
                                        // Delete record
                                        $delete_id = $_POST["delete_id"];
                                        $delete_sql = "UPDATE DictList SET Valid = 'N' WHERE ID = $delete_id";
                                        if ($conn->query($delete_sql) === TRUE) {
                                            echo '<div class="success">資料刪除成功！</div>';
                                        } else {
                                            echo '<div class="error">資料刪除失敗</div>';
                                        }
                                    }
                                }
                                // Check if there is a record with both key and name empty
                                $empty_record_check_sql = "SELECT * FROM DictList WHERE Dkey = '' AND Name = '' AND Category = 'A' AND Valid = 'Y'";
                                $empty_record_check_result = $conn->query($empty_record_check_sql);

                                if ($empty_record_check_result->num_rows == 0) {
                                    // Insert a new record with both key and name empty
                                    $insert_empty_record_sql = "INSERT INTO DictList (Dkey, Name, Category, Valid) VALUES ('', '', 'A', 'Y')";
                                    $conn->query($insert_empty_record_sql);
                                }

                                // Fetch and display sorted records
                                $select_sql = "SELECT * FROM DictList WHERE Category = 'A' AND Valid = 'Y' ORDER BY Dkey";
                                $result = $conn->query($select_sql);

                                if ($result->num_rows > 0) {
                                    echo '<table border="1">
                                            <tr>
                                                <th>Dkey</th>
                                                <th>Name</th>
                                                <th>操作</th>
                                            </tr>';
                                    while ($row = $result->fetch_assoc()) {
                                        echo '<tr>
                                                <form method="post" action="">
                                                    <td><input type="text" name="edit_dkey" value="' . $row["Dkey"] . '" pattern="[A-Za-z]" title="請輸入一個英文字母" required></td>
                                                    <td><input type="text" name="edit_name" value="' . $row["Name"] . '" required></td>
                                                    <td>
                                                        <input type="hidden" name="edit_id" value="' . $row["ID"] . '">';
                                        if($row["Dkey"] == "" && $row["Name"] == ""){
                                            echo '<input type="submit" value="新增">';
                                        }
                                        else{
                                            echo '<input type="submit" value="編輯">';
                                        }
                                        echo '          
                                                    </td>
                                                </form>
                                                <td>
                                                    <form onsubmit="return confirm(\'確定刪除？\');" method="post" action="">
                                                        <input type="hidden" name="delete_id" value="' . $row["ID"] . '">
                                                        <input type="submit" value="刪除">
                                                    </form>
                                                </td>
                                            </tr>';
                                    }
                                    echo '</table>';
                                } else {
                                    echo '<p>目前沒有字典資料。</p>';
                                }
                                $conn->close();
                            ?>
                        <?php
                        break;

                    case '2':
                        ?>
                        <h3>包裹類別設定</h3>
                        <?php
                            // Handle form submission (update or delete)
                            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                                if (isset($_POST["edit_id2"])) {
                                    // Update record
                                    $edit_id2 = $_POST["edit_id2"];
                                    $edit_dkey2 = $_POST["edit_dkey2"];
                                    $edit_name2 = $_POST["edit_name2"];
                                    if (ctype_lower($edit_dkey2)) {
                                        $edit_dkey2 = strtoupper($edit_dkey2);
                                    }

                                    // Check if Dkey is a single alphabet
                                    if (!preg_match('/^[A-Za-z]$/', $edit_dkey2)) {
                                        echo '<div class="error">Dkey 必須為一個英文字母</div>';
                                    } else {
                                        // Check if Dkey is unique
                                        $check_duplicate_sql2 = "SELECT * FROM DictList WHERE Dkey = '$edit_dkey2' AND Category = 'B' AND Valid = 'Y' AND ID != $edit_id2";
                                        $duplicate_result2 = $conn->query($check_duplicate_sql2);

                                        if ($duplicate_result2->num_rows == 0) {
                                            // Update record if Dkey is unique
                                            $update_sql2 = "UPDATE DictList SET Dkey = '$edit_dkey2', Name = '$edit_name2' WHERE ID = $edit_id2";
                                            if ($conn->query($update_sql2) === TRUE) {
                                                echo '<div class="success">資料更新成功！</div>';
                                            } else {
                                                echo '<div class="error">資料更新失敗</div>';
                                            }
                                        } else {
                                            echo '<div class="error">Dkey 已存在，請輸入其他值</div>';
                                        }
                                    }
                                } elseif (isset($_POST["delete_id2"])) {
                                    // Delete record
                                    $delete_id2 = $_POST["delete_id2"];
                                    $delete_sql2 = "UPDATE DictList SET Valid = 'N' WHERE ID = $delete_id2";
                                    if ($conn->query($delete_sql2) === TRUE) {
                                        echo '<div class="success">資料刪除成功！</div>';
                                    } else {
                                        echo '<div class="error">資料刪除失敗</div>';
                                    }
                                }
                            }
                            // Check if there is a record with both key and name empty
                            $empty_record_check_sql2 = "SELECT * FROM DictList WHERE Dkey = '' AND Name = '' AND Category = 'B' AND Valid = 'Y'";
                            $empty_record_check_result2 = $conn->query($empty_record_check_sql2);

                            if ($empty_record_check_result2->num_rows == 0) {
                                // Insert a new record with both key and name empty
                                $insert_empty_record_sql2 = "INSERT INTO DictList (Dkey, Name, Category, Valid) VALUES ('', '', 'B', 'Y')";
                                $conn->query($insert_empty_record_sql2);
                            }

                            // Fetch and display sorted records
                            $select_sql2 = "SELECT * FROM DictList WHERE Category = 'B' AND Valid = 'Y' ORDER BY Dkey";
                            $result2 = $conn->query($select_sql2);

                            if ($result2->num_rows > 0) {
                                echo '<table border="1">
                                        <tr>
                                            <th>Dkey</th>
                                            <th>Name</th>
                                            <th>操作</th>
                                        </tr>';
                                while ($row2 = $result2->fetch_assoc()) {
                                    echo '<tr>
                                            <form method="post" action="">
                                                <td><input type="text" name="edit_dkey2" value="' . $row2["Dkey"] . '" pattern="[A-Za-z]" title="請輸入一個英文字母" required></td>
                                                <td><input type="text" name="edit_name2" value="' . $row2["Name"] . '" required></td>
                                                <td>
                                                    <input type="hidden" name="edit_id2" value="' . $row2["ID"] . '">';
                                    if ($row2["Dkey"] == "" && $row2["Name"] == "") {
                                        echo '<input type="submit" value="新增">';
                                    } else {
                                        echo '<input type="submit" value="編輯">';
                                    }
                                    echo '          
                                                </td>
                                            </form>
                                            <td>
                                                <form onsubmit="return confirm(\'確定刪除？\');" method="post" action="">
                                                    <input type="hidden" name="delete_id2" value="' . $row2["ID"] . '">
                                                    <input type="submit" value="刪除">
                                                </form>
                                            </td>
                                        </tr>';
                                }
                                echo '</table>';
                            } else {
                                echo '<p>目前沒有字典資料。</p>';
                            }
                            $conn->close();
                            ?>
                        <?php
                        break;

                    case '3':
                        ?>
    			<h2>更改密碼</h2>
			 <?php

                        
                        if (isset($_SESSION['Username'])) {
                            $Username = $_SESSION['Username'];
                            
                            $user_id_query = "SELECT id FROM users WHERE username='$Username'";
                            $user_id_result = $conn->query($user_id_query);

                            if ($user_id_result->num_rows > 0) {
                                $user_id_row = $user_id_result->fetch_assoc();
                                $user_id = $user_id_row['id'];

                                if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['current_password']) && isset($_POST['new_password']) && isset($_POST['confirm_new_password'])) {
                                    
                                    $current_password = $_POST['current_password'];
                                    $new_password = $_POST['new_password'];
                                    $confirm_new_password = $_POST['confirm_new_password'];

                                    $password_query = "SELECT * FROM users WHERE id='$user_id'";
                                    $password_result = $conn->query($password_query);

                                    if ($password_result->num_rows > 0) {
                                        $password_row = $password_result->fetch_assoc();
                                        $password_hash = $password_row['password'];

                                        if (password_verify($current_password, $password_hash)) {
                                            if ($new_password == $confirm_new_password) {
                                                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                                                $update_password_sql = "UPDATE users SET password = '$hashed_password' WHERE id = '$user_id'";

                                                if ($conn->query($update_password_sql) === TRUE) {
                                                    $_POST['Result'] = "success";
                                                } else {
                                                    $_POST['Result'] = "failure";
                                                }
                                            } else {
                                                $_POST['Result'] = "new_password_mismatch";
                                            }
                                        } else {
                                            $_POST['Result'] = "current_password_incorrect";
                                        }
                                    } else {
                                        $_POST['Result'] = "user_not_found";
                                    }
                                }
                            } else {
                                echo "Error: Unable to retrieve user ID.";
                            }
                        } else {
                            header("Location: Loginpage.php");
                            exit();
                        }

                        ?>
                        <form method="post" action="">
                            <label for="current_password">當前密碼：</label>
                            <input type="password" name="current_password" required>
                            <br>
                            <label for="new_password">新密碼：</label>
                            <input type="password" name="new_password" required>
                            <br>
                            <label for="confirm_new_password">確認新密碼：</label>
                            <input type="password" name="confirm_new_password" required>
                            <br>
                            <input type="submit" value="確定更改">
                        </form>
                        <?php
                        break;
                    
                    case '4':
                        ?>
    			<h2>確定登出</h2>
    			<p>您確定要登出嗎？</p>
    			<form method="post" action="">
        			<input type="hidden" name="confirm_logout" value="true">
        			<input type="submit" value="確定登出">
    			</form>
    			<?php

    			if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["confirm_logout"])) {
        			session_start(); 
        			session_unset(); 
        			session_destroy(); 
        			header("Location: LoginPage.php"); 
        			exit();
    			}
    			break;
                    
                    default:
                        ?>
                        <h2>Welcome to the Setting Page</h2>
                        <p>Please select an option from the sidebar.</p>
                        <?php
                        break;
                    }
                ?>
        </div>
    </main>
    <div class="links">
        <a href="index.php">返回首頁</a>
    </div>
</body>
</html>
