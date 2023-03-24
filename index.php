<?php

$time_start = microtime(true);

header('Content-type: text/xml');

echo '<yml_catalog>' . PHP_EOL;

require 'src/sequence_producer.php';

$time_end = microtime(true);
echo '<time>Total Execution Time: ' . ($time_end - $time_start) . '</time>';
echo '</yml_catalog>' . PHP_EOL;
