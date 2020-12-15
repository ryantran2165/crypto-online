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
        echo '<h5 class="text-center pb-5">Invalid username/password, please try again.</h5>';
    }

    $conn->close();
}

function print_html()
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
      <link href="css/styles.css" rel="stylesheet">
      <title>Crypto Online</title>
    </head>

    <body>
      <div class="container pt-5">
        <div class="row text-center">
          <div class="col">
            <h1 class="mb-4">Crypto Online</h1>
            <h5 class="mb-4">Web application for encrypting/decrypting from text input or text file.<br>Available ciphers: simple substitution, double transposition, and RC4.</h5>
            <form method="post" action="index.php">
              <div class="form-group">
                <input placeholder="Username" type="text" name="username" required>
              </div>
              <div class="form-group">
                <input placeholder="Password" type="text" name="password" required>
              </div>
              <input class="btn btn-primary btn-lg" type="submit" name="login" value="LOGIN">
            </form>
            <p>Or <a href="php/register.php">register</a>.</p>
          </div>
        </div>
      <a href="https://github.com/ryantran2165/crypto-online" target="_blank" class="github-corner" aria-label="View source on GitHub"><svg width="80" height="80" viewBox="0 0 250 250" style="fill:#222; color:#7fffd4; position: absolute; top: 0; border: 0; right: 0;" aria-hidden="true"><path d="M0,0 L115,115 L130,115 L142,142 L250,250 L250,0 Z"></path><path d="M128.3,109.0 C113.8,99.7 119.0,89.6 119.0,89.6 C122.0,82.7 120.5,78.6 120.5,78.6 C119.2,72.0 123.4,76.3 123.4,76.3 C127.3,80.9 125.5,87.3 125.5,87.3 C122.9,97.6 130.6,101.9 134.4,103.2" fill="currentColor" style="transform-origin: 130px 106px;" class="octo-arm"></path><path d="M115.0,115.0 C114.9,115.1 118.7,116.5 119.8,115.4 L133.7,101.6 C136.9,99.2 139.9,98.4 142.2,98.6 C133.8,88.0 127.5,74.4 143.8,58.0 C148.5,53.4 154.0,51.2 159.7,51.0 C160.3,49.4 163.2,43.6 171.4,40.1 C171.4,40.1 176.1,42.5 178.8,56.2 C183.1,58.6 187.2,61.8 190.9,65.4 C194.5,69.0 197.7,73.2 200.1,77.6 C213.8,80.2 216.3,84.9 216.3,84.9 C212.7,93.1 206.9,96.0 205.4,96.6 C205.1,102.4 203.0,107.8 198.3,112.5 C181.9,128.9 168.3,122.5 157.7,114.1 C157.9,116.9 156.7,120.9 152.7,124.9 L141.0,136.5 C139.8,137.7 141.6,141.9 141.8,141.8 Z" fill="currentColor" class="octo-body"></path></svg></a><style>.github-corner:hover .octo-arm{animation:octocat-wave 560ms ease-in-out}@keyframes octocat-wave{0%,100%{transform:rotate(0)}20%,60%{transform:rotate(-25deg)}40%,80%{transform:rotate(10deg)}}@media (max-width:500px){.github-corner:hover .octo-arm{animation:none}.github-corner .octo-arm{animation:octocat-wave 560ms ease-in-out}}</style>
      </div>
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
