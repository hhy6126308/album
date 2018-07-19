<?php
function debug_point ($data) {
    if (gettype($data) == 'array') {
        echo "<pre>";
        var_dump($data);
        echo "</pre>";
    } else {
        var_dump($data);
    }
}

function safe_string ($string, $default = '') {
    if (gettype($string) != 'string') return false;
    $string = htmlspecialchars(addslashes(trim($string)));
    return !$string && $default ? $default : $string;
}

