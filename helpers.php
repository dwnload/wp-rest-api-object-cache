<?php declare( strict_types=1 );

namespace Dwnload\WpRestApi\Helpers;

/**
 * Filters a variable with a boolean filter.
 * @param mixed $variable Incoming data.
 * @return bool
 */
function filter_var_bool( $variable ) : bool {
    return \filter_var( $variable, FILTER_VALIDATE_BOOLEAN );
}

/**
 * Filters a variable with an int filter.
 * @param mixed $variable Incoming data.
 * @return int
 */
function filter_var_int( $variable ) : int {
    return \filter_var( $variable, FILTER_VALIDATE_INT );
}

/**
 * Filters a variable with a string filter.
 * @param mixed $variable Incoming data.
 * @return string
 */
function filter_var_string( $variable ) : string {
    return \filter_var( $variable, FILTER_SANITIZE_STRING );
}