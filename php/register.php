<?php

require_once 'login.php';
require_once 'tools.php';
require_once 'validate.php';

print_html();

session_start();
validate_session();

// Passed client-side validation (JS)
if (isset($_POST['register']))
{
    $conn = new mysqli($hn, $un, $pw, $db);
    if ($conn->connect_error) die(mysql_fatal_error());
    create_users_table($conn);

    // Sanitize inputs
    $email = mysql_entities_fix_string($conn, $_POST['email']);
    $username = mysql_entities_fix_string($conn, $_POST['username']);
    $password = mysql_entities_fix_string($conn, $_POST['password']);

    // Server-side validation
    $fail = validate_email($email);
    $fail .= validate_username($username);
    $fail .= validate_password($password);

    if ($fail == '') // May still fail uniqueness
    {
        register($conn);
    }
    else // Should not get here unless JS turned off or user manipulated site
    {
        echo "<h5 class='text-center pb-5'>$fail</h5>";
    }
    
    $conn->close();
}

function print_html()
{
    echo <<<_END
    <html>
    <head>
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <link rel="icon" href="../assets/favicon.png">
      <link href="https://fonts.googleapis.com/css?family=Poppins&display=swap" rel="stylesheet">
      <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
      <link href="../css/styles.css" rel="stylesheet">
      <title>Crypto Online - Register</title>
      <script src="../js/validate.js"></script>
    </head>

    <body>
      <div class="container pt-5">
        <div class="row text-center">
          <div class="col">
            <h1 class="mb-4">Register</h1>
            <form method="post" action="register.php" onSubmit="return validateRegister(this)">
              <div class="form-group">
                <input placeholder="Email" type="text" name="email" maxlength="254" required>
              </div>
              <div class="form-group">
                <input placeholder="Username" type="text" name="username" maxlength="32" required>
              </div>
              <div class="form-group">
                <input placeholder="Password" type="text" name="password" maxlength="32" required>
              </div>
                <input class="btn btn-primary btn-lg" type="submit" name="register" value="REGISTER">
            </form>
            <p>Or <a href="../index.php">login</a>.</p>
          </div>
        </div>
      </div>
    </body>
    </html>
_END;
}

function register($conn)
{
    $email = mysql_entities_fix_string($conn, $_POST['email']);
    $username = mysql_entities_fix_string($conn, $_POST['username']);
    $password = mysql_entities_fix_string($conn, $_POST['password']);
    
    if (is_unique_email($conn, $email) && is_unique_username($conn, $username))
    {
        register_user($conn, $email, $username, $password);
    }
}

function is_unique_email($conn, $email)
{
    $query = 'SELECT email FROM users WHERE email=?';
    $stmt = $conn->prepare($query);
    if (!$stmt) die(mysql_fatal_error());
    
    $result = $stmt->bind_param('s', $email);
    if (!$result) die(mysql_fatal_error());
    
    $result = $stmt->execute();
    if (!$result) die(mysql_fatal_error());
    
    $result = $stmt->get_result();
    if (!$result) die(mysql_fatal_error());
    
    if ($result->num_rows)
    {
        $stmt->free_result();
        $stmt->close();
        echo '<h5 class="text-center pb-5">That email is already being used, please try again.</h5>';
        return false;
    }
    
    $stmt->free_result();
    $stmt->close();
    return true;
}

function is_unique_username($conn, $username)
{
    $query = 'SELECT username FROM users WHERE username=?';
    $stmt = $conn->prepare($query);
    if (!$stmt) die(mysql_fatal_error());
    
    $result = $stmt->bind_param('s', $username);
    if (!$result) die(mysql_fatal_error());
    
    $result = $stmt->execute();
    if (!$result) die(mysql_fatal_error());
    
    $result = $stmt->get_result();
    if (!$result) die(mysql_fatal_error());
    
    if ($result->num_rows)
    {
        $stmt->free_result();
        $stmt->close();
        echo '<h5 class="text-center pb-5">That username is already being used, please try again.</h5>';
        return false;
    }
    
    $stmt->free_result();
    $stmt->close();
    return true;
}

function register_user($conn, $email, $username, $password)
{
    $random_bytes = random_bytes(16);
    $salt = bin2hex($random_bytes);
    $password_encrypted = hash('ripemd128', "$salt$password");
    
    $query = 'INSERT INTO users (email, username, password_encrypted, salt) VALUES(?, ?, ?, ?)';
    $stmt = $conn->prepare($query);
    if (!$stmt) die(mysql_fatal_error());
    
    $result = $stmt->bind_param('ssss', $email, $username, $password_encrypted, $salt);
    if (!$result) die(mysql_fatal_error());
    
    $result = $stmt->execute();
    if (!$result) die(mysql_fatal_error());
    
    $stmt->close();
    echo '<script>window.location.href = "../index.php";</script>';
}

?>
