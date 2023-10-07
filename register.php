<?php 
    session_start();
    // session_destroy();
    require_once "connect.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create an Account</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

    <?php 

        $user_exists = 0;
        $wrong_pass_length = 0;
        $pass_not_matching = 0;
        $non_numeric = 0;
        $wrong_phone_length = 0;
        $check_fields = 0;
        $phone_taken = 0;

        /* This array contains the name of all required values, so that it can be checked if fields have been left blank. */
        $required = array('new_user', 'pass1', 'pass2', 'firstname', 'surname', 'address1', 'address2', 'city', 'phone_number');

        /* This condition checks if the user has filled out the form before running the code inside it. */
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
            /* The empty() function is used instead of isset(), as isset() will be true if a field has 
            been left blank. The value will be set, but will be empty. Therefore the empty() function is more 
            suitable. */
            if (!empty($_POST['new_user']) && !empty($_POST['pass1']) && !empty($_POST['pass2']) && !empty($_POST['firstname']) 
            && !empty($_POST['surname']) && !empty($_POST['address1']) && !empty($_POST['city']) && !empty($_POST['phone_number'])) {

                /* When inserting data, the real_escape_string() function is used to eliminate security risks
                and the risk of errors caused by special characters, such as single quotes. */
                $new_username = $conn -> real_escape_string($_POST['new_user']);

                /* A quick query is run to make sure the desired username isn't taken. */
                $sql = "SELECT Username FROM Users WHERE Username = '$new_username'";
                $result = $conn -> query($sql);
                if ($result -> num_rows > 0) { 
                    $user_exists = 1;
                } // end if
                
                /* Checks if the password is 6 characters long and the two passwords match. */
                if ($_POST['pass1'] == $_POST['pass2']) {
                    $password = $conn -> real_escape_string($_POST['pass1']);

                    if (strlen($password) != 6) {
                        $wrong_pass_length = 1;
                    }
                } else {
                    $pass_not_matching = 1;
                } // end if 

                $firstname = $conn -> real_escape_string($_POST['firstname']);
                $surname = $conn -> real_escape_string($_POST['surname']);
                $add1 = $conn -> real_escape_string($_POST['address1']);
                $add2 = $conn -> real_escape_string($_POST['address2']);
                $city = $conn -> real_escape_string($_POST['city']);
                $phone = $conn -> real_escape_string($_POST['phone_number']);
                
                /* The phone number must be 10 characters in length. */
                if (strlen($phone) == 10) {

                    /* This function makes sure there are no letters in the phone number. */
                    if (is_numeric($phone)) {
                        /* Another query is run to make sure the desired phone number hasn't been used. */
                        $sql = "SELECT Phone FROM Users WHERE Phone = '$phone'";
                        $result = $conn -> query($sql);
                        if ($result -> num_rows > 0) { 
                            $phone_taken = 1;
                        } // end if 
                    } else {
                        $non_numeric = 1;
                    } // end if 

                } else {
                    $wrong_phone_length = 1;
                } // end if 

                
                /* The data the user entered will be inserted into the database if and only if:
                        - The username is unique 
                        - The passwords entered match
                        - The password is 6 characters long
                        - The phone number is unique 
                        - The phone number is numeric 
                        - The phone number is 10 characters long
                */
                if ($user_exists == 0 && $pass_not_matching == 0  && $wrong_pass_length == 0 
                && $phone_taken == 0 && $non_numeric == 0 && $wrong_phone_length == 0 ) {
                    $sql = "INSERT INTO Users (Username, Password, FirstName, Surname, AddressLine1, AddressLine2, City, Phone) 
                    VALUES ( '$new_username', '$password', '$firstname', '$surname', '$add1', '$add2', '$city', '$phone')";

                    if ($conn -> query($sql) === TRUE) {
                        $_SESSION['username'] = $new_username;
                        $_SESSION['password'] = $password;
                        header("Location: search.php");
                    } else {
                        echo "Error: " . $sql . "<br>" . $conn -> error;
                    } // end if 
                } // end if 

            } else {

                foreach($required as $field) {
                    if (empty($_POST[$field])) {
                        $check_fields = 1;
                    } // end if 
                } // end loop
            } // end if 

        } // end if 

    ?>

    <header>

        <div class='title'>
            <nav>
                <h1 class="titleLink">
                    <a href="index.php">PHP Library</a>
                </h1>

            <ul class="navbar">

                <li><a href="index.php">Home</a></li>

            </ul>
            
            </nav>
        </div>

    </header>

    <main>

        <h2 class="centerTitle">
            Create an Account
        </h2>

        <div class="centerContent">

            <form method="post" class="login">

                <?php 
                    if ($check_fields == 1) {
                        echo "<p class='loginError'>Please check you have entered all fields.</p>";
                    }
                ?>

                <label>Username*</label><br>
                <input type="text" name="new_user" class="inputField" value="<?php echo isset($_POST['new_user']) ? $_POST['new_user'] : '' ?>"><br><br>

                <?php 
                    if ($user_exists == 1) {
                        echo "<p class='loginError'>That username is taken. Please choose another.</p>";
                    } // end if 
                ?>

                <label>Password*</label><br>
                <input type="password" name="pass1" class="inputField"><br><br>

                <label>Confirm your Password*</label><br>
                <input type="password" name="pass2" class="inputField"><br><br>

                <?php 
                    if ($wrong_pass_length == 1) {
                        echo "<p class='loginError'>Passwords must be six characters in length.</p><br>";
                    } else if ($pass_not_matching == 1) {
                        echo "<p class='loginError'>Your passwords do not match.</p><br>";
                    }
                ?>

                <label>First Name*</label><br>
                <input type="text" name="firstname" class="inputField" value="<?php echo isset($_POST['firstname']) ? $_POST['firstname'] : '' ?>"><br><br>

                <label>Surname*</label><br>
                <input type="text" name="surname" class="inputField" value="<?php echo isset($_POST['surname']) ? $_POST['surname'] : '' ?>"><br><br>

                <label>Address Line 1*</label><br>
                <input type="text" name="address1" class="inputField" value="<?php echo isset($_POST['address1']) ? $_POST['address1'] : '' ?>"><br><br>

                <label>Address Line 2*</label><br>
                <input type="text" name="address2" class="inputField" value="<?php echo isset($_POST['address2']) ? $_POST['address2'] : '' ?>"><br><br>

                <label>City*</label><br>
                <input type="text" name="city" class="inputField" value="<?php echo isset($_POST['city']) ? $_POST['city'] : '' ?>"><br><br>

                <label>Phone Number*</label><br>
                <input type="text" name="phone_number" class="inputField" value="<?php echo isset($_POST['phone_number']) ? $_POST['phone_number'] : '' ?>"><br><br>

                <?php 
                    /* These if statements are done separately, as some can occur at the same time. */
                    if ($non_numeric == 1) {
                        echo "<p class='loginError'>Please only use numbers in your phone number.</p><br>";
                    } 

                    if ($wrong_phone_length == 1) {
                        echo "<p class='loginError'>There must be 10 digits in your phone number.</p><br>";
                    }

                    if ($phone_taken == 1) {
                        echo "<p class='loginError'>That phone number is already in use. Please use another.</p><br>";
                    } 
                ?>

                <button type="submit" class="submitButton">Enter</button><br><br>

                <p>*: Mandatory Field</p>
                <p>Please <a href="index.php#loginform">login</a> if you already have an account.</p>

            </form>

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