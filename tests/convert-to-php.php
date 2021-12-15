<?php

$json = '';

file_put_contents(__DIR__.'/'.mt_rand().'-usercase.php','<?php '.PHP_EOL.PHP_EOL.'return '.var_export(json_decode($json, true, 512, JSON_THROW_ON_ERROR), true).';');
