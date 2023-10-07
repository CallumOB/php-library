<?php 
    session_start();
    // Logs out any users logged in.
    unset($_SESSION['username']);
    unset($_SESSION['password']);
    require_once "connect.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width">
    <title>Library Homepage</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

    <?php 

        $wrong_pass = 0;
        $user_not_found = 0;
    
        if (isset($_POST['username']) && isset($_POST['password'])) {
            $user = $_POST['username'];
            $pass = $_POST['password'];
            
            $sql = "SELECT Username, Password FROM Users WHERE Username = '$user'";
            $result = $conn -> query($sql);

            if ($result -> num_rows > 0) {
                while ($row = $result -> fetch_assoc()) {
                    $db_user = $row['Username'];
                    $db_pass = $row['Password'];
                } // end while

                if ($db_pass == $pass) {
                    $_SESSION['username'] = $user;
                    $_SESSION['password'] = $pass;

                    header("Location: search.php");
                } else {
                    $wrong_pass = 1;
                } // end if 
            } else {
                $user_not_found = 1;
            } // end if 
        } // end if
    
    ?>
    
    <header>

        <div class='title'>
            <nav>
                <h1 class="titleLink">   
                    <a href="index.php">PHP Library</a>
                </h1>
            </nav>
        </div>

    </header>

    <main>

        <h2 class="centerTitle">
            PHP Library
        </h2>

        <div class="flexContent" id="intro">
            <p>
                <i>Welcome to the PHP Library!</i><br><br>
                This website will allow you to search through the library and reserve any book you wish.
                You can search for specific books, authors, categories and so on. You can then reserve a book 
                and view a list of any books you have previously reserved. <br><br>
                You must login to use the system. 
            </p>

            <img src="assets/images/library-1.jpg" alt="A Stock image of a Library" id="library-1">
        </div>

        <div class="centerContent" id="loginform">

            <form action="#loginform" method="post" class="login">

                <h3>Login</h3>

                <label>Username</label><br>
                <input type="text" name="username" class="inputField" value="<?php echo isset($_POST['username']) ? $_POST['username'] : '' ?>"><br><br>

                <?php 
                    if ($user_not_found == 1) {
                        echo "<p class='loginError'>Username not found.</p><br>";
                    } // end if 
                ?>

                <label>Password</label><br>
                <input type="password" name="password" class="inputField"><br><br>

                <?php 
                    if ($wrong_pass == 1) {
                        echo "<p class='loginError'>Your password is incorrect.</p><br>";
                    } // end if 
                ?>

                <button type="submit" class="submitButton">Login</button>

            </form>

            <p>
                Please <a href="register.php">create an account</a> if you don't have one.
            </p>

        </div>

        <footer>
            <h6>
                Page by: Callum O'Brien<br>
                C21306503<br>
                2022
            </h6>
        </footer>

    </main>

</body>

</html>