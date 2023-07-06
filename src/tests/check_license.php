<?php

$license_file = __DIR__ . '/../../LICENSE';

if (!file_exists($license_file)) {
    echo "License file does not exist!\n";
    exit(1);
}

echo "License file exists.\n";
exit(0);
