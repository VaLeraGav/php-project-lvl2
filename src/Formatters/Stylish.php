<?php

namespace Differ\Formatters\Stylish;

function format(array $data): string
{
    $result = iter($data);
    return "{\n{$result}\n}";
}

function iter(array $data, int $depth = 0)
{
    $indent = str_repeat(' ', 4 * $depth);
    $stylish = array_map(function ($unit) use ($indent, $depth) {

        $status = $unit['status'];
        $name = $unit['name'];

        switch ($status) {
            case 'unchanged':
                $preparedValue = preparedValues($unit['value'], $depth + 1);
                return "{$indent}    {$name}: {$preparedValue}";

            case 'added':
                $preparedValue = preparedValues($unit['value'], $depth + 1);
                return "{$indent}  + {$name}: {$preparedValue}";

            case 'removed':
                $preparedValue = preparedValues($unit['value'], $depth + 1);
                return "{$indent}  - {$name}: {$preparedValue}";

            case 'changed':
                $preparedOldValue = preparedValues($unit['oldValue'], $depth + 1);
                $preparedNewValue = preparedValues($unit['newValue'], $depth + 1);

                $deletedLine = "{$indent}  - {$name}: {$preparedOldValue}";
                $addedLine =  "{$indent}  + {$name}: {$preparedNewValue}";
                return implode("\n", [$deletedLine, $addedLine]);

            case 'nested':
                $children = iter($unit['child'], $depth + 1);
                return "{$indent}    {$name}: {\n{$children}\n{$indent}    }";

            default:
                throw new \Exception("Incorrect status '{$status}'.");
        }
    }, $data);
    return implode("\n", $stylish);
}

/**
 * @param mixed $value
 * @param int $depth
 * @return string
 */
function preparedValues($value, int $depth): string
{
    if (is_bool($value)) {
        return $value ? 'true' : 'false';
    }
    if (is_null($value)) {
        return 'null';
    }
    if (!is_object($value)) {
        return $value;
    }

    $keys = array_keys(get_object_vars($value));
    $indent = str_repeat(' ', 4 * $depth);
    $lines = array_map(function ($key) use ($value, $depth, $indent) {
        $children = preparedValues($value->$key, $depth + 1);
        return "{$indent}    {$key}: {$children}";
    }, $keys);

    $result = implode("\n", $lines);
    return "{\n{$result}\n{$indent}}";
}
