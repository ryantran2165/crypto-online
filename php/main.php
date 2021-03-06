<?php

require_once 'login.php';
require_once 'tools.php';
require_once 'crypto.php';
require_once 'validate.php';

session_start();
validate_session();
$is_logout = check_logout();

if (is_logged_in())
{
    $conn = new mysqli($hn, $un, $pw, $db);
    if ($conn->connect_error) die(mysql_fatal_error());
    create_history_table($conn);

    $username = mysql_entities_fix_string($conn, $_SESSION['username']);
    print_html($username);
    
    // Passed client-side validation (JS)
    if (isset($_POST['run']))
    {
        $cipher = mysql_entities_fix_string($conn, $_POST['cipher']);
        $crypto_type = mysql_entities_fix_string($conn, $_POST['crypto_type']);
        $key = mysql_entities_fix_string($conn, $_POST['key']);
        $input = $_POST['text_input'];
        
        // Server-side validation
        $fail = validate_key($key, $cipher);
        
        // File input has higher priority than text input
        if (is_uploaded_file($_FILES['file_input']['tmp_name']))
        {
            $file_input = $_FILES['file_input']['name'];
            
            if (mime_content_type($file_input) === 'text/plain' && pathinfo($file_input, PATHINFO_EXTENSION) === 'txt')
            {
                $input = file_get_contents($file_input);
            }
            else
            {
                $fail .= 'Text (.txt) files only.<br>';
            }
        }
        $input = str_replace(array("\n", "\r"), '', $input);
        $input = mysql_entities_fix_string($conn, $input);

        if ($fail == '')
        {
            run($conn, $username, $cipher, $crypto_type, $key, $input);
        }
        else
        {
            echo "<h5 class='text-center pb-5'>$fail</h5>";
        }
    }

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
      <title>Crypto Online</title>
      <script src="../js/validate.js"></script>
      <script src="../js/random_key.js"></script>
    </head>

    <body>
      <div class="container pt-5">
        <div class="row text-center">
          <div class="col">
            <h1 class="mb-4">Crypto Online</h1>
            <h5>Logged in as $username.</h5>
            <form class="mb-4" method="post" action="main.php">
              <a href="history.php">Go to History</a> or 
              <input class="btn btn-primary" type="submit" name="logout" value="LOGOUT">
            </form>
            <form method="post" action="main.php" enctype="multipart/form-data" onSubmit="return validateCrypto(this)">
              <div class="form-group">
                <label for="cipher">Cipher: </label>
                <select name="cipher" id="cipher">
                  <option value="Simple Substitution">Simple Substitution</option>
                  <option value="Double Transposition">Double Transposition</option>
                  <option value="RC4">RC4</option>
                </select>
              </div>
              <div class="form-group">
                <input type="radio" name="crypto_type" value="Encrypt" checked="checked"> Encrypt
                <input type="radio" name="crypto_type" value="Decrypt"> Decrypt
              </div>
              <div class="form-group">
                <input placeholder="Key" type="text" name="key" id="key" required>
                <input class="btn btn-primary" type="button" name="random_key" value="RANDOM" onClick="getRandomKey()">
              </div>
              <div class="form-group">
                <textarea class="form-control" placeholder="Text input" rows="10" name="text_input"></textarea>
              </div>
              <div class="form-group">
                <p>or select text (.txt) file:</p>
                <input type="file" name="file_input" size="10">
              </div>
              <input class="btn btn-primary btn-lg" type="submit" name="run" value="RUN">
            </form>
          </div>
        </div>
      </div>
    </body>
    </html>
_END;
}

function run($conn, $username, $cipher, $crypto_type, $key, $input)
{
    // Sanitize output just in case
    $output = mysql_entities_fix_string($conn, crypto($cipher, $crypto_type, $key, $input));
    
    echo <<<_END
    <h5 class="text-center pb-5">
      Cipher: $cipher | $crypto_type<br>
      Key: $key<br>
      Input: $input<br>
      Output: $output
    </h5>
_END;
    
    add_history($conn, $username, $cipher, $crypto_type, $input, $output);
}

function add_history($conn, $username, $cipher, $crypto_type, $input, $output)
{
    $query = 'INSERT INTO history (username, cipher, crypto_type, input, output) VALUES(?, ?, ?, ?, ?)';
    $stmt = $conn->prepare($query);
    if (!$stmt) die(mysql_fatal_error());
    
    $result = $stmt->bind_param('sssss', $username, $cipher, $crypto_type, $input, $output);
    if (!$result) die(mysql_fatal_error());
    
    $result = $stmt->execute();
    if (!$result) die(mysql_fatal_error());
    
    $stmt->close();
}

?>
