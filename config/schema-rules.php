<?php

return [
    /**
     * In MySQL for instance there is no nativ boolean data type.
     * Laravel creates a tinyint(1) if you migrate a boolean.
     * Switch this off if you want an actual tinyint
     * validation rule to be generated...
     */
    'tinyint1_to_bool' => env('SCHEMA_RULES_TINYINT1_TO_BOOL', true),

    /**
     * The min default length for a required string validation rule is 1 character.
     * Changes this to what ever fits best for you!
     */
    'string_min_length' => env('SCHEMA_RULES_STRING_MIN_LENGTH', 1),
];
