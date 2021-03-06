<?php

require_once 'login.php';
require_once 'tools.php';

session_start();
validate_session();
$is_logout = check_logout();

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
else if ($is_logout)
{
    echo '<script>window.location.href = "../index.php";</script>';
}
else
{
    echo <<<_END
    <html>
    <head>
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <meta name="theme-color" content="#7fffd4">
      <meta name="author" content="Ryan L. Tran">
      <link rel="icon" href="https://raw.githubusercontent.com/ryantran2165/ryantran2165.github.io/source/public/favicon.png" />
      <link href="https://fonts.googleapis.com/css?family=Poppins&display=swap" rel="stylesheet">
      <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
      <link href="../css/styles.css" rel="stylesheet">
      <title>Crypto Online</title>
    </head>
    <body>
      <div class="container pt-5">
        <div class="row text-center">
          <div class="col">
            <h5>Not logged in or session timed out, please <a href="../index.php">login</a>.</h5>
          </div>
        </div>
      </div>
    </body>
    </html>
_END;
}

function print_html($username)
{
    echo <<<_END
    <html>
    <head>
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <meta name="theme-color" content="#7fffd4">
      <meta name="author" content="Ryan L. Tran">
      <link rel="icon" href="https://raw.githubusercontent.com/ryantran2165/ryantran2165.github.io/source/public/favicon.png" />
      <link href="https://fonts.googleapis.com/css?family=Poppins&display=swap" rel="stylesheet">
      <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
      <link href="../css/styles.css" rel="stylesheet">
      <title>Crypto Online - History</title>
    </head>

    <body>
      <div class="container pt-5">
        <div class="row text-center">
          <div class="col">
            <h1 class="mb-4">$username's History</h1>
            <form class="mb-4" method="post" action="history.php">
              <div class="form-group">
                <a href="main.php">Go to Decryptoid</a> or 
                <input class="btn btn-primary" type="submit" name="logout" value="LOGOUT">
              </div>
              <input class="btn btn-primary btn-lg" type="submit" name="clear_history" value="CLEAR HISTORY">
            </form>
          </div>
        </div>
      </div>
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
            <h5 class="text-center pb-5">
              Timestamp: $timestamp<br>
              Cipher: $cipher | $crypto_type<br>
              Input: $input<br>
              Output: $output
            </h5>
_END;
        }
    }
    else
    {
        echo '<h5 class="text-center pb-5">No history!</h5>';
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
