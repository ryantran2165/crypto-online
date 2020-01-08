<?php

function crypto($cipher, $crypto_type, $key, $input)
{
    if ($cipher == 'Simple Substitution')
    {
        return simple_substitution($crypto_type, $key, $input);
    }
    else if ($cipher == 'Double Transposition')
    {
        return $crypto_type == 'Encrypt' ? double_transposition_encrypt($key, $input) : double_transposition_decrypt($key, $input);
    }
    else if ($cipher == 'RC4')
    {
        return rc4($crypto_type, $key, $input);
    }
    return '';
}

function simple_substitution($crypto_type, $key, $input)
{
    $key = strtoupper($key);
    $key_arr = str_split($key);
    $input = strtoupper($input);
    $alphabet = range('A', 'Z');
    $map = $crypto_type == 'Encrypt' ? array_combine($alphabet, $key_arr) : array_combine($key_arr, $alphabet);
    $output = '';
    
    for ($i = 0; $i < strlen($input); $i++)
    {
        $output .= array_key_exists($input[$i], $map) ? $map[$input[$i]] : $input[$i];
    }
    
    return $output;
}

function double_transposition_encrypt($key, $input)
{
    // Two spaces next to each other are automatically merged into one space, breaking the algorithm
    $input = str_replace(' ', '', $input);
    return columnar_transposition_encrypt($key, columnar_transposition_encrypt($key, $input));
}

function columnar_transposition_encrypt($key, $input)
{
    $arr = array();
    $rows = ceil(strlen($input) / strlen($key));
    
    // Create initial 2D grid
    for ($r = 0; $r < $rows; $r++)
    {
        for ($c = 0; $c < strlen($key); $c++)
        {
            if ($r * strlen($key) + $c < strlen($input))
            {
                $arr[$r][$c] = $input[$r * strlen($key) + $c];
            }
            else
            {
                $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $arr[$r][$c] = $chars[mt_rand(0, strlen($chars) - 1)];
            }
        }
    }

    // Convert key string to array and sort alphabetical
    $key_arr = str_split($key);
    sort($key_arr);
    
    // Output by alphabetical key columns
    $output = '';
    for ($i = 0; $i < count($key_arr); $i++)
    {
        $c = strpos($key, $key_arr[$i]);
        
        for ($r = 0; $r < $rows; $r++)
        {
            $output .= $arr[$r][$c];
        }
    }
    
    return $output;
}

function double_transposition_decrypt($key, $input)
{
    // Two spaces next to each other are automatically merged into one space, breaking the algorithm
    $input = str_replace(' ', '', $input);
    return columnar_transposition_decrypt($key, columnar_transposition_decrypt($key, $input));
}

function columnar_transposition_decrypt($key, $input)
{
    $arr = array();
    $rows = ceil(strlen($input) / strlen($key));
    
    // Convert key string to array and sort alphabetical
    $key_arr = str_split($key);
    sort($key_arr);
    
    // Create 2D grid by alphabetical key columns
    $j = 0;
    for ($i = 0; $i < count($key_arr); $i++)
    {
        $c = strpos($key, $key_arr[$i]);
        
        for ($r = 0; $r < $rows; $r++)
        {
            $arr[$r][$c] = $input[$j];
            $j++;
        }
    }
    
    // Convert 2D grid to string output
    $output = '';
    for ($r = 0; $r < $rows; $r++)
    {
        for ($c = 0; $c < strlen($key); $c++)
        {
            $output .= $arr[$r][$c];
        }
    }
    
    return $output;
}

function rc4($crypto_type, $key, $input)
{
    // Identity permutation
    $S = array();
    for ($i = 0; $i < 256; $i++)
    {
        $S[$i] = $i;
    }
    
    // Permutate S
    $j = 0;
    for ($i = 0; $i < 256; $i++)
    {
        $j = ($j + $S[$i] + ord($key[$i % strlen($key)])) % 256;
        swap($S, $i, $j);
    }
    
    // Keystream
    $i = 0;
    $j = 0;
    $hexKeystream = '';
    $end = $crypto_type == 'Encrypt' ? strlen($input) : strlen($input) / 2;
    for ($k = 0; $k < $end; $k++)
    {
        $i = ($i + 1) % 256;
        $j = ($j + $S[$i]) % 256;
        swap($S, $i, $j);
        $hexKeystream .= bin2hex(chr($S[($S[$i] + $S[$j]) % 256]));
    }
    
    // XOR input and keystream
    $output = '';
    if ($crypto_type == 'Encrypt')
    {
        $hexInput = bin2hex($input);
        
        for ($i = 0; $i < strlen($hexKeystream); $i++)
        {
            $output .= dechex(hexdec($hexInput[$i]) ^ hexdec($hexKeystream[$i]));
        }
    }
    else
    {
        for ($i = 0; $i < strlen($hexKeystream); $i += 2)
        {
            $output .= chr((hexdec(substr($input, $i, 2)) ^ hexdec(substr($hexKeystream, $i, 2))));
        }
    }
    
    return $output;
}

function swap(&$arr, $i, $j)
{
    $temp = $arr[$i];
    $arr[$i] = $arr[$j];
    $arr[$j] = $temp;
}

?>
