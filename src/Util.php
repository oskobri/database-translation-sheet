<?php

namespace Oskobri\DatabaseTranslationSheet;

class Util
{
    public static function snakeCaseToWords($snake_case): string
    {
        return ucfirst(str_replace('_', ' ', $snake_case));
    }

    public static function wordsToSnakeCase($words): string
    {
        return strtolower(str_replace(' ', '_', $words));
    }

    /**
     * Transform sheet header to database json column name.
     * Ex: 'Name (en)' => 'name->en'
     * @param $string
     * @return string
     */
    public static function headerLocaleToDatabaseColumn($string): string
    {
        $pattern = '#\(([^\)]+)\)#';
        $replacement = '->${1}';
        return strtolower(
            str_replace(
                ' ',
                '',
                preg_replace($pattern, $replacement, $string)
            )
        );
    }
}
