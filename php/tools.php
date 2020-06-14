<?php

function mysql_entities_fix_string($conn, $string)
{
    return htmlentities(mysql_fix_string($conn, $string));
}

function mysql_fix_string($conn, $string)
{
    if (get_magic_quotes_gpc()) $string = stripslashes($string);
    return $conn->real_escape_string($string);
}

function mysql_fatal_error()
{
    echo 'Sorry something went wrong, please try again.';
}

function validate_session()
{
    // Remote address and user agent does not match, different user
    if (is_logged_in() && $_SESSION['check'] != get_check_hash())
    {
        destroy_session_and_data();
    }
    
    // New session, generate new id
    if (!isset($_SESSION['initiated']))
    {
        session_regenerate_id();
        $_SESSION['initiated'] = 1;
    }
}

function destroy_session_and_data()
{
    $_SESSION = array();
    setcookie(session_name(), '', time() - 2592000, '/');
    session_destroy();
}

function check_logout()
{
    if ($_POST['logout'])
    {
        destroy_session_and_data();
        return true;
    }
    return false;
}

function is_logged_in()
{
    return isset($_SESSION['check']);
}

function create_users_table($conn)
{
    $query = 'CREATE TABLE IF NOT EXISTS users (
    email VARCHAR(254) NOT NULL UNIQUE,
    username VARCHAR(32) NOT NULL PRIMARY KEY,
    password_encrypted CHAR(32) NOT NULL,
    salt CHAR(32) NOT NULL)';
    $result = $conn->query($query);
    if (!$result) die(mysql_fatal_error());
}

function create_history_table($conn)
{
    $query = 'CREATE TABLE IF NOT EXISTS history (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    username VARCHAR(32) NOT NULL,
    cipher VARCHAR(32) NOT NULL,
    crypto_type CHAR(7) NOT NULL,
    input TEXT NOT NULL,
    output TEXT NOT NULL)';
    $result = $conn->query($query);
    if (!$result) die(mysql_fatal_error());
}

function get_check_hash()
{
    return hash('ripemd128', $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']);
}

?>
