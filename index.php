<?php

$time_start = microtime(true);

@ini_set('zlib.output_compression',0);
@ini_set('implicit_flush',1);
@ob_end_clean();
set_time_limit(0);

header('Content-type: text/xml');

echo '<yml_catalog>' . PHP_EOL;

require 'src/sequence_producer.php';

$time_end = microtime(true);
echo '<time>Total Execution Time: ' . ($time_end - $time_start) . '</time>' . PHP_EOL;
echo '</yml_catalog>' . PHP_EOL;
