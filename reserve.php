<?php 

    session_start();
    require_once "connect.php";

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserved Books</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

    <?php 

        $not_logged_in = 0;
    
        if (!isset($_SESSION['username']) && !isset($_POST['password'])) {
            $not_logged_in = 1;
        } // end if
    
    ?>

    <header>

        <div class='title'>
            <nav>
                <h1>PHP Library</h1>

                <ul class="navbar">
                    <?php echo $not_logged_in == 0 ? "<li><a href='search.php'>Search Library</a></li>" : "" ?>
                    <li><a href="index.php">Log Out</a></li>
                </ul>
            </nav>
        </div>

    </header>

    <main>
        
        <div class="centerTitle">
            <h2>Reserved Books</h2>
        </div>

        <div class="centerContent" id="searchTable">

            <?php 

                if ($not_logged_in == 1) {
                    echo "<p>You are not logged in. Please <a href='index.php'>return to the homepage.</a></p>";
                    return;
                } else {

                    if ($_GET['unreserve'] == 1) {

                        $sql = "DELETE FROM Reserved WHERE ReservedISBN = '" . $_GET['isbn'] . "'";
                        
                        if ($conn -> query($sql) === TRUE) {
                            echo "<p>Successfully unreserved.</p>";

                            $sql = "UPDATE Books SET Reserved = 'N' WHERE ISBN = '" . $_GET['isbn'] . "'";
                            $conn -> query($sql);
                        } // end if 
                        
                    } else if (isset($_GET['isbn']) && isset($_GET['user'])) {

                        $sql = "INSERT INTO Reserved (ReservedISBN, Username) VALUES ('" . $_GET['isbn'] . "', '" . $_GET['user'] . "')";
                        $conn -> query($sql);
                        $sql = "UPDATE Books SET Reserved = 'Y' WHERE ISBN = '" . $_GET['isbn'] . "'";
                        $conn -> query($sql);

                    } // end if 

                    $sql = "SELECT ReservedISBN, Title, Author, Edition, Year, Description FROM Books
                            JOIN Category ON 
                            Books.Category = Category.CategoryID
                            JOIN Reserved ON
                            Books.ISBN = Reserved.ReservedISBN
                            WHERE Reserved.Username = (SELECT Username FROM Users WHERE Username = '" . $_SESSION['username'] . "')";
                    $result = $conn -> query($sql);

                    if ($result -> num_rows == 0) {
                        echo "<p>You haven't reserved any books. <a href='search.php'>Click here to view our books in stock.</a></p>";

                        return;
                    } else {

                        if (isset($_POST['test'])) {
                            echo "Set";
                        } // end if 

                        while ($row = $result -> fetch_assoc()) {
                            $db_isbn[] = $row['ReservedISBN'];
                            $db_title[] = $row['Title'];
                            $db_author[] = $row['Author'];
                            $db_edition[] = $row['Edition'];
                            $db_year[] = $row['Year'];
                            $db_category[] = $row['Description'];
                        } // end while

                        $results_size = count($db_isbn);
                        
                        echo "<p>Showing books reserved by " . $_SESSION['username'] . ".</p>";
                        echo "<form method='post' id='reservedSearch'>";
                        echo "<table class='search'>";
                        echo "<tr id='tableTitle'><td>ISBN</td>
                              <td>Book Title</td>    
                              <td>Author</td>    
                              <td>Edition</td>    
                              <td>Year</td>    
                              <td>Category</td>
                              <td>Unreserve</td></tr>";

                        for ($i = 0; $i < $results_size; $i++) {

                            echo "<tr><td>" . $db_isbn[$i] . "</td>";
                            echo "<td>" . $db_title[$i] . "</td>";
                            echo "<td>" . $db_author[$i] . "</td>";
                            echo "<td>" . $db_edition[$i] . "</td>";
                            echo "<td>" . $db_year[$i] . "</td>";
                            echo "<td>" . $db_category[$i] . "</td>";
                            echo "<td><a href='reserve.php?unreserve=1&isbn=" . $db_isbn[$i] . "' name='test'>Unreserve</a></td></tr>";

                        } // end for 

                    } // end if 

                } // end if 

            ?>

            </table>

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