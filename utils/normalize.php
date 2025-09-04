<?php
function minMaxNormalize(array $values): array
{
    if (count($values) === 0) {
        throw new InvalidArgumentException("Cannot normalize an empty array.");
    }
    $min = min($values);
    $max = max($values);
    if ($max == $min) {
        // If all values are identical, map to 0.5
        $normalized = array_fill(0, count($values), 0.5);
    } else {
        $normalized = [];
        foreach ($values as $v) {
            $normalized[] = ($v - $min) / ($max - $min);
        }
    }
    return [
        'normalized' => $normalized,
        'min'        => $min,
        'max'        => $max
    ];
}
