<?php

require_once 'php/login.php';
require_once 'php/tools.php';

session_start();
validate_session();

if (is_logged_in())
{
    echo '<script>window.location.href = "php/main.php";</script>';
}
else
{
    $conn = new mysqli($hn, $un, $pw, $db);
    if ($conn->connect_error) die(mysql_fatal_error());
    create_users_table($conn);

    $success = false;
    if (isset($_POST['login']))
    {
        $success = login($conn);
    }

    print_html();
    if (isset($_POST['login']) && !$success)
    {
        echo 'Invalid username/password, please try again.';
    }

    $conn->close();
}

function print_html()
{
    echo <<<_END
    <html>
    <head>
      <title>Crypto Online</title>
    </head>

    <body>
      <h1>Crypto Online</h1>
      <form method="post" action="index.php">
          Username: <input type="text" name="username"><br>
          Password: <input type="text" name="password"><br>
          <input type="submit" name="login" value="LOGIN">
      </form>
      <p>Or <a href="php/register.php">register</a>.</p>
    </body>
    </html>
_END;
}

function login($conn)
{
    $username = mysql_entities_fix_string($conn, $_POST['username']);
    $password = mysql_entities_fix_string($conn, $_POST['password']);
    
    $query = 'SELECT password_encrypted, salt FROM users WHERE username=?';
    $stmt = $conn->prepare($query);
    if (!$stmt) die(mysql_fatal_error());
    
    $result = $stmt->bind_param('s', $username);
    if (!$result) die(mysql_fatal_error());
    
    $result = $stmt->execute();
    if (!$result) die(mysql_fatal_error());
    
    $result = $stmt->bind_result($password_encrypted, $salt);
    if (!$result) die(mysql_fatal_error());

    $success = false;
    if ($stmt->fetch())
    {
        $pwd_encrypted = hash('ripemd128', "$salt$password");
    
        if ($pwd_encrypted == $password_encrypted)
        {
            $_SESSION['username'] = $username;
            $_SESSION['check'] = get_check_hash();
            echo '<script>window.location.href = "php/main.php";</script>';
            $succes = true;
        }
    }
    
    $stmt->free_result();
    $stmt->close();
    return $success;
}

?>
