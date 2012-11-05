<?php
if (!defined('BOT_VERS')) die("Cannot use error handler alone.");
// Try to catch as many errors as possible
function error_throw_exception($errno, $errstr, $errfile, $errline ) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}
set_error_handler("error_throw_exception", E_ALL & ~E_STRICT);

// TODO catch fatal errors

