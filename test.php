<?php
function numberOfItems(array $arr, $item) : int
{
    $count = 0;
    foreach ($arr as $array_item) {
        if (is_array($array_item)) {
            $count += numberOfItems($array_item, $item);
        } elseif  ($array_item === $item) {
            $count++;
        }
    }

    return $count;
}

$arr = [
    25,
    "apple",
    25,
    ["banana", "strawberry", "apple", 25, 25, [25]]
];

$test_cases = [
    25,
    "apple",
    "banana"
];

foreach ($test_cases as $case) {
    echo sprintf("%s was found %s time(s) \r\n", $case, numberOfItems($arr, $case));
}