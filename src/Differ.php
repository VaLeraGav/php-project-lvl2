<?php

namespace Differ\Differ;

use function Differ\Parsers\parse;
use function Differ\Builder\build;
use function Differ\Formatters\format;

function genDiff(string $firstFilePath, string $secondFilePath, string $format = 'stylish')
{

    $firstData = readFile($firstFilePath);
    $secondData = readFile($secondFilePath);

    $parsedFirstData  = parse($firstData, pathinfo($firstFilePath, PATHINFO_EXTENSION));
    $parsedSecondData = parse($secondData, pathinfo($secondFilePath, PATHINFO_EXTENSION));

    $tree = build($parsedFirstData, $parsedSecondData);

    return format($format, $tree);
}

function readFile(string $path): string
{
    if (!file_exists($path)) {
        throw new \Exception("The file {$path} does not exists.\n");
    }
    return file_get_contents($path);
}
