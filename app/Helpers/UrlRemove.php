<?php

namespace App\Helpers;

class UrlRemove
{
    public static function remover(string $text)
    {
        $urlParts = parse_url($text);
        $pathParts = explode('/', $urlParts['path']);
        $objectName = implode('/', array_slice($pathParts, 2));

        return $objectName;
    }
}