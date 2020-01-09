<?php

require_once 'login.php';
require_once 'tools.php';

session_start();
validate_session();
check_logout();

if (is_logged_in())
{
    $conn = new mysqli($hn, $un, $pw, $db);
    if ($conn->connect_error) die(mysql_fatal_error());
    create_history_table($conn);

    $username = mysql_entities_fix_string($conn, $_SESSION['username']);

    if (isset($_POST['clear_history']))
    {
        clear_history($conn, $username);
    }

    print_html($username);
    print_history($conn, $username);

    $conn->close();
}
else
{
    echo '<script>window.location.href = "../index.php";</script>';
}

function print_html($username)
{
    echo <<<_END
    <html>
    <head>
      <title>Crypto Online - History</title>
    </head>

    <body>
      <h1>$username's History</h1>
      <form method="post" action="history.php">
        <a href="main.php">Go to Decryptoid</a> or 
        <input type="submit" name="logout" value="LOGOUT">
        <br>
        <input type="submit" name="clear_history" value="CLEAR HISTORY">
      </form>
    </body>
    </html>
_END;
}

function print_history($conn, $username)
{
    $query = 'SELECT timestamp, cipher, crypto_type, input, output FROM history WHERE username=?';
    $stmt = $conn->prepare($query);
    if (!$stmt) die(mysql_fatal_error());
    
    $result = $stmt->bind_param('s', $username);
    if (!$result) die(mysql_fatal_error());
    
    $result = $stmt->execute();
    if (!$result) die(mysql_fatal_error());
    
    $result = $stmt->bind_result($timestamp, $cipher, $crypto_type, $input, $output);
    if (!$result) die(mysql_fatal_error());
    
    $result = $stmt->store_result();
    if (!$result) die(mysql_fatal_error());
    
    if ($stmt->num_rows)
    {
        while ($stmt->fetch())
        {
            echo <<<_END
            <p>
              Timestamp: $timestamp<br>
              Cipher: $cipher | $crypto_type<br>
              Input: $input<br>
              Output: $output
            </p>
_END;
        }
    }
    else
    {
        echo 'No history!';
    }
    
    $stmt->free_result();
    $stmt->close();
}

function clear_history($conn, $username)
{
    $query = 'DELETE FROM history WHERE username=?';
    $stmt = $conn->prepare($query);
    if (!$stmt) die(mysql_fatal_error());
    
    $result = $stmt->bind_param('s', $username);
    if (!$result) die(mysql_fatal_error());
    
    $result = $stmt->execute();
    if (!$result) die(mysql_fatal_error());
    
    $stmt->close();
}

?>
