<?php 
    session_start();
    require_once "connect.php";
?>

<!DOCTYPE HTML>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width">
    <title>Library Search</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

    <?php 

        // The variables storing which results are kept in the $_SESSION variable so they are preserved on refreshes. 
        // On first visit to the page, results 0 - 5 are shown from the database
        if (!isset($_POST['next']) && !isset($_POST['previous'])) {
            
            $_SESSION['results_start'] = 0;
            $_SESSION['results_limit'] = 5;

        } else if (isset($_POST['next'])) {

            // Will only show next page if there are results to show.
            if ($_SESSION['results_start'] + 5 < $_SESSION['results_size']) {
                $_SESSION['results_start'] += 5;
            } // end if 
            $_SESSION['results_limit'] = $_SESSION['results_start'] + 5;
            // Done to avoid unintentional activations.
            unset($_POST['next']);
            
        } else if (isset($_POST['previous'])) {

            // Will only show previous page if there are results to show.
            if ($_SESSION['results_start'] >= 5) {
                $_SESSION['results_start'] = $_SESSION['results_start'] - 5;
            } // end if 
            $_SESSION['results_limit'] = $_SESSION['results_start'] + 5;
            // Done to avoid unintentional activations.
            unset($_POST['previous']);
        }

        function display_results($loop_start, $loop_limit, $isbn, $title, $author, $edition, $year, $category) {
            for ($i = $loop_start; $i < $loop_limit; $i++) {
                if (!empty($isbn[$i])) {
                    
                    echo "<tr><td>" . $isbn[$i] . "</td>";
                    echo "<td>" . $title[$i] . "</td>";
                    echo "<td>" . $author[$i] . "</td>";
                    echo "<td>" . $edition[$i] . "</td>";
                    echo "<td>" . $year[$i] . "</td>";
                    echo "<td>" . $category[$i] . "</td>";

                    echo "<td><a href='reserve.php?isbn=" . $isbn[$i] . "&user=" . $_SESSION['username'] . "'>Reserve</a></td></tr>";
                } // end if 
            } // end for
        } // end display_results()

        $not_logged_in = 0;
    
        if (!isset($_SESSION['username']) && !isset($_SESSION['password'])) {
            $not_logged_in = 1;
        }  // end if 

    ?>

    <header>

        <div class="title">
            <nav>
                <h1 class>
                    PHP Library
                </h1>

                <ul class="navbar">
                    <li><a href="reserve.php">Reserved Books</a></li>
                    <li><a href="index.php">Log Out</a></li>
                </ul>
            </nav>
        </div>

    </header>

    <main>

        <div class="centerTitle">
            <h2>Library Search</h2>
        </div>

        <div class="centerContent" id="searchTable">

            <?php 

                // The page will not progress any further if the user is not logged in.
                if ($not_logged_in == 1) {
                    echo "<p>You are not logged in. Please <a href='index.php'>return to the homepage.</a></p>";

                    return;
                } else {

                    // Arrays are created to store data from the database.
                    $db_isbn = array();
                    $db_title = array();
                    $db_author = array();
                    $db_edition = array();
                    $db_year = array();
                    $db_category = array();

                    if (isset($_POST['category_select'])) {

                        // If the search box is empty, the query is run using only the category box.
                        if (empty($_POST['search_field'])) {

                            if ($_POST['category_select'] == 'all') {
                                $sql = "SELECT ISBN, Title, Author, Edition, Year, Description FROM Books
                                    LEFT JOIN Category
                                    ON Books.Category = Category.CategoryID
                                    WHERE Reserved = 'N'";
    
                                $result = $conn -> query($sql);
                            } else {
                                $sql = "SELECT ISBN, Title, Author, Edition, Year, Description FROM Books
                                    LEFT JOIN Category
                                    ON Books.Category = Category.CategoryID
                                    WHERE Reserved = 'N' 
                                    AND Description = '" . $_POST['category_select'] . "'";
    
                                $result = $conn -> query($sql);
                            } // end if          

                        } else {

                            // This function adds the escape character to any special characters entered in the search, and copies the result into $protected_input.
                            // This protects the page against SQL injection.
                            $protected_input = $conn -> real_escape_string($_POST['search_field']);

                            // This function splits the string the user entered into 3 parts. This is used to get the best chances of finding a match.
                            $split = str_split($protected_input, 3);

                            // The following code is run if all categories are being searched.
                            if ($_POST['category_select'] == 'all') {

                                // At first, the database is searched using the string the user entered exactly.
                                $sql = "SELECT ISBN, Title, Author, Edition, Year, Description FROM Books
                                        LEFT JOIN Category
                                        ON Books.Category = Category.CategoryID
                                        WHERE Reserved = 'N' AND (Title LIKE 
                                        '%" . $protected_input . "%' OR AUTHOR LIKE '%" . $protected_input . "%')";
                                
                                $result = $conn -> query($sql);

                                // If no results are found, the database is searched using the string split into 3.
                                if ($result -> num_rows == 0) {

                                    $sql = "SELECT ISBN, Title, Author, Edition, Year, Description FROM Books
                                        LEFT JOIN Category
                                        ON Books.Category = Category.CategoryID
                                        WHERE Reserved = 'N' AND (Title LIKE 
                                        '%" . $split[0] . "%' OR
                                        Title LIKE '%" . $split[1] . "%' OR 
                                        Title LIKE '%" . $split [2] . "%') OR (Author LIKE 
                                        '%" . $split[0] . "%' OR
                                        Author LIKE '%" . $split[1] . "%' OR 
                                        Author LIKE '%" . $split [2] . "%')";
    
                                    $result = $conn -> query($sql);

                                } // end if 

                            } else {
                                // This code does the same thing as above, but specifies which category to specifically search.

                                $split = str_split($protected_input, 3);

                                $sql = "SELECT ISBN, Title, Author, Edition, Year, Description FROM Books
                                    LEFT JOIN Category
                                    ON Books.Category = Category.CategoryID
                                    WHERE Reserved = 'N' 
                                    AND Description = '" . $_POST['category_select'] . "' AND (Title LIKE
                                    '%" . $protected_input . "%' OR AUTHOR LIKE '%" . $protected_input . "%')";

                                $result = $conn -> query($sql);

                                
                                if ($result -> num_rows == 0) {

                                    $sql = "SELECT ISBN, Title, Author, Edition, Year, Description FROM Books
                                        LEFT JOIN Category
                                        ON Books.Category = Category.CategoryID
                                        WHERE Reserved = 'N' AND DESCRIPTION = '" . $_POST['category_select'] . "' 
                                        AND (Title LIKE '%" . $split[0] . "%' OR
                                        Title LIKE '%" . $split[1] . "%' OR 
                                        Title LIKE '%" . $split [2] . "%') OR (Author LIKE 
                                        '%" . $split[0] . "%' OR
                                        Author LIKE '%" . $split[1] . "%' OR 
                                        Author LIKE '%" . $split [2] . "%')";
    
                                    $result = $conn -> query($sql);

                                } // end if 
                                
                            } // end if  

                        } // end if 

                    } else {
                        // If the dropdown menu is not set, i.e on the first visit of the page, all results are shown.

                        $sql = "SELECT ISBN, Title, Author, Edition, Year, Description FROM Books
                                LEFT JOIN Category
                                ON Books.Category = Category.CategoryID
                                WHERE Reserved = 'N'";

                        $result = $conn -> query($sql);
                    } // end if 

                    $no_results = 0;

                    if ($result -> num_rows > 0) {

                        // The results of the query are added to an array representing each column.
                        while ($row = $result -> fetch_assoc()) {
    
                            $db_isbn[] = $row['ISBN'];
                            $db_title[] = $row['Title'];
                            $db_author[] = $row['Author'];
                            $db_edition[] = $row['Edition'];
                            $db_year[] = $row['Year'];
                            $db_category[] = $row['Description'];
    
                        } // end while

                    } else {
                        // If no results is found, this flag is changed and is used later.
                        $no_results = 1;
                    }


                    // This query is run to populate the dropdown menu.
                    // Querying the database means that if new categories are added, no code needs to change.
                    $sql = "SELECT Description FROM Category";
                    $result = $conn -> query($sql);

                    while ($row = $result -> fetch_assoc()) {
                        $category[] = $row['Description'];
                    } // end while

                    // This is used at the beginning of the file to determine if the user can go to the next page or not.
                    $_SESSION['results_size'] = count($db_isbn);

                    echo "<form method='post' id='librarySearch'>";
                    echo "<ul id='searchBox'>";
                    echo "<input type='text' class='searchField' name='search_field' ";

                    // The value the user last entered is retained. 
                    echo "value='" . (isset($_POST['search_field']) ? $_POST['search_field'] : null);
                    echo "'>";
                    
                    echo "<select class='searchField' id='searchSelect' name='category_select'>";
                    echo "<option value='all' selected>All</option>";

                    // The categories retrieved from the DB query are shown as options in the dropdown menu.
                    foreach ($category as $value) {
                        echo "<option value='" . $value . "'>" . $value . "</option>"; 
                    } // end foreach

                    echo "</select>";
                    echo "<button type='submit' class='submitButton' name='enter_search'>Search</button>";
                    echo "</ul>";

                    // If no results are found, the user is told so.
                    if ($no_results == 1) {
                        echo "<p>No results found.</p>";
                        return;
                    } else {
                        
                        if (isset($_POST['category_select'])) {

                            // The page tells the user from which category the results are being displayed.
                            if ($_POST['category_select'] == "all") {
                                echo "<p>Showing results from all categories.</p>";
                            } else {
                                echo "<p>Showing results from the '" . 
                                $_POST['category_select'] . "' category.</p>";
                            } // end if 
                        
                        } else {
                            // This is only displayed upon the first visit to the page.
                            echo "<p>Showing results from all categories.</p>";
                        } // end if 
                    
                    } // end if 

                    echo "<table class='search'>";
                    echo "<tr id='tableTitle'><td>ISBN</td>
                          <td>Book Title</td>    
                          <td>Author</td>    
                          <td>Edition</td>    
                          <td>Year</td>    
                          <td>Category</td>
                          <td>Reserve</td></tr>";

                    // This function displays the results of the main query in the correct format for the table.
                    display_results($_SESSION['results_start'], $_SESSION['results_limit'], $db_isbn, 
                    $db_title, $db_author, $db_edition, $db_year, $db_category);

                } // end if 
            
            ?>
                </table>

                <button type='submit' class='submitButton' name='previous'>Previous Page</button>
                <button type='submit' class='submitButton' name='next'>Next Page</button>
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