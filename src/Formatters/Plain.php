<?php

namespace Differ\Formatters\Plain;

function format(array $data): string
{
    return iter($data);
}

function iter(array $data, string $ancestry = null)
{
    $plain = array_map(function ($unit) use ($ancestry) {
        $newAncestry = $ancestry . $unit['name'];
        $status = $unit['status'];

        switch ($status) {
            case 'nested':
                return iter($unit['child'], "{$newAncestry}.");

            case 'added':
                $value = preparedValues($unit['value']);
                return "Property '{$newAncestry}' was added with value: {$value}";

            case 'removed':
                return "Property '{$newAncestry}' was removed";

            case 'changed':
                $newValue = preparedValues($unit['newValue']);
                $oldValue = preparedValues($unit['oldValue']);
                return "Property '{$newAncestry}' was updated. From {$oldValue} to {$newValue}";
            case 'unchanged':
                return;
            default:
                throw new \Exception("Incorrect status '{$status}'.");
        }
    }, $data);
    $filteredData = array_filter($plain);
    $result = implode("\n", $filteredData);
    return $result;
}

/**
 * @param mixed $value
 * @return string
 */
function preparedValues($value): string
{
    if (is_array($value) || is_object($value)) {
        return "[complex value]";
    }
    if (is_null($value)) {
        return 'null';
    }
    return var_export($value, true);
}
