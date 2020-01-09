<?php

function validate_email($field)
{
    if ($field == '')
    {
        return 'No email entered.<br>';
    }
    else if (!preg_match('/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/', $field))
    {
        return 'Invalid email.<br>';
    }
    return '';
}

function validate_username($field)
{
    $min_length = 6;
    
    if ($field == '')
    {
        return 'No username entered.<br>';
    }
    else if (strlen($field) < $min_length)
    {
        return "Username must be at least $min_length characters.<br>";
    }
    else if (preg_match("/[^a-zA-Z0-9_-]/"))
    {
        return 'Only a-z, A-Z, 0-9, _ and - allowed in username.<br>';
    }
    return '';
}

function validate_password($field)
{
    $min_length = 8;
    
    if ($field == '')
    {
        return 'No password entered.<br>';
    }
    else if (strlen($field) < $min_length)
    {
        return "Password must be at least $min_length characters.<br>";
    }
    return '';
}

function validate_key($field, $cipher)
{
    if ($field == '')
    {
        return 'No key entered.<br>';
    }
    
    if ($cipher == 'Simple Substitution') {
        $length = 26;
        
        if (strlen($field) != $length) {
            return 'Key must be 26 characters for simple substitution cipher.<br>';
        }
    } else if ($cipher == 'Double Transposition') {
        $dict = array();
        
        for ($i = 0; $i < strlen($field); $i++) {
            $char = $field[$i];
            
            if (array_key_exists($char, $dict)) {
                return 'Key must have unique characters for double transposition cipher.<br>';
            } else {
                $dict[$char] = true;
            }
        }
    }
    return '';
}

?>
