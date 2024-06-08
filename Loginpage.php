<!DOCTYPE html>
<?php
    session_start();
    if(!empty($_POST['Send']) && !empty($_POST['Username']) && !empty($_POST['Password'])) {      
        require_once "login.php";
    }
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <title>登入介面</title>
    <style>
        body {
            font-family: 'Microsoft JhengHei', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa; /* Specify your desired background color */
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .header-image {
            width: 200px; /* Adjust the width as needed */
            height: 200px; /* Adjust the height as needed */
            margin-bottom: 20px;
        }

        .login-system-message {
            background-color: #007bff;
            color: white;
            padding: 10px;
            font-size: 20px;
            width: 30%;
            text-align: center;
        }

        .form-container {
            text-align: center;
            margin-top: 20px;
        }

        .input-container,
        .password-container,
        .submit-container {
            margin: 10px 0;
        }

        input[type="text"],
        input[type="password"] {
            width: 300px;
            padding: 10px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 5px;
            outline: none;
        }

        input[type="submit"] {
            width: 150px;
            padding: 10px;
            box-sizing: border-box;
            border: none;
            border-radius: 5px;
            background-color: #007bff;
            color: white;
            cursor: pointer;
            font-size: 15px;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        .error-message {
            color: red;
            font-size: 20px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <img class="header-image" src="https://upload.wikimedia.org/wikipedia/zh/c/ca/Taiwan_Tatung_University_seal.svg" alt="Header Image">

    <div class="login-system-message">
        登入系統
    </div>

    <div class="form-container">
        <form method="POST" action="">
            <div class="input-container">
                <input type="text" name="Username" placeholder="使用者名稱" />
            </div>
            <div class="password-container">
                <input type="password" name="Password" placeholder="密碼" />
            </div>
            <div class="submit-container">
                <input type="submit" name="Send" value="登入" />
            </div>
        </form>

        <?php
            if(!empty($_POST["Send"]) and $_POST["Send"]=="登入") {
                if(!empty($_POST['Result']) and $_POST['Result']=="success") {
                    $_SESSION['Username']=$_POST['Username'];
                    header("Location: index.php"); 
                    print_r("登入成功");
                } else if(!empty($_POST['Result']) and $_POST['Result']=="failure") {
                    echo  '<div class="error-message">帳號或密碼錯誤 <script>alert("帳號或密碼錯誤")</script></div>';
                } else {
                    echo '<div class="error-message">帳號或密碼不可為空白 <script>alert("帳號或密碼不可為空白")</script></div>';
                }
            }
        ?>
    </div>
</body>
</html>
