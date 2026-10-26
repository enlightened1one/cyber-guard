<?php

class Validator
{
    public static function normalizeEmail(string $email): string
    {
        return strtolower(trim($email));
    }

    public static function required(string $value, int $min = 1): bool
    {
        return is_string($value) ? trim($value) !== '' && mb_strlen(trim($value)) >= $min : false;
    }

    public static function isSafeText(string $value): bool
    {
        $value = strip_tags($value);
        return $value !== '';
    }
}
