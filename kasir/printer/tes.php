<?php

echo "<pre>";

echo "User: ";
system('whoami');

echo "\n\nGroups:\n";
system('groups');

echo "\n\nDevice:\n";
system('ls -l /dev/rfcomm* 2>&1');

echo "\n\nWrite Test:\n";

$result = @file_put_contents(
    '/dev/rfcomm0',
    "TEST DARI WEBSITE\n\n\n"
);

var_dump($result);

echo "</pre>";