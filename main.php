<?php

require_once 'login.php';
require_once 'tools.php';
require_once 'crypto.php';
require_once 'validate.php';

session_start();
validate_session();
check_logout();

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
            echo $fail;
        }
    }

    $conn->close();
}
else
{
    echo '<script>window.location.href = "crypto-online.php";</script>';
}

function print_html($username)
{
    echo <<<_END
    <html>
    <head>
      <title>Crypto Online</title>
      <script src="validate.js"></script>
      <script src="random_key.js"></script>
    </head>

    <body>
      <h1>Crypto Online</h1>
      <h4>Logged in as $username.</h4>
      <form method="post" action="main.php">
        <a href="history.php">Go to History</a> or 
        <input type="submit" name="logout" value="LOGOUT">
      </form>
      <form method="post" action="main.php" enctype="multipart/form-data" onSubmit="return validateCrypto(this)">
        Cipher:
        <select name="cipher" id="cipher">
            <option value="Simple Substitution">Simple Substitution</option>
            <option value="Double Transposition">Double Transposition</option>
            <option value="RC4">RC4</option>
        </select>
        <br>
        <input type="radio" name="crypto_type" value="Encrypt" checked="checked"> Encrypt
        <input type="radio" name="crypto_type" value="Decrypt"> Decrypt
        <br>
        Key: <input type="text" name="key" id="key">
        <input type="button" name="random_key" value="RANDOM" onClick="getRandomKey()">
        <br>
        Text input:
        <br>
        <textarea rows="10" cols="50" name="text_input"></textarea>
        <br>
        or select text (.txt) file: <input type="file" name="file_input" size="10">
        <br>
        <input type="submit" name="run" value="RUN">
      </form>
    </body>
    </html>
_END;
}

function run($conn, $username, $cipher, $crypto_type, $key, $input)
{
    // Sanitize output just in case
    $output = mysql_entities_fix_string($conn, crypto($cipher, $crypto_type, $key, $input));
    
    echo <<<_END
    <p>
      Cipher: $cipher | $crypto_type<br>
      Key: $key<br>
      Input: $input<br>
      Output: $output
    </p>
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
