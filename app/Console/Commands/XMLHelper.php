<?php
declare(strict_types = 1);


namespace App\Console\Commands;

use Illuminate\Contracts\Filesystem\Filesystem;

class XMLHelper
{

    public static function loadXML(string $path, Filesystem $filesystem){
        $content = $filesystem->get($path);
        if ($content === false) {
            throw new \RuntimeException('Failed to load ' . $path);
        }
        $data = simplexml_load_string($content);
        $errors = '';
        if ($data === false) {
            foreach (libxml_get_errors() as $error) {
                $errors .= $error->message;
            }
            throw new \RuntimeException("Failed loading XML: ".$errors);
        }
        return $data;
    }
}
