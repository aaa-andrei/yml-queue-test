<?php

require_once __DIR__ . '/../vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->queue_declare('sequence_queue', false, false, false, true);
//$channel->queue_declare('result_queue', false, false, false, true);

$dbconn = pg_connect("host=localhost dbname=postgres user=postgres password=example")
or die('Could not connect: ' . pg_last_error($dbconn));

echo " [*] Waiting for messages. To exit press CTRL+C\n";

$callback = function ($msg) use ($channel, $dbconn) {
    $message = explode(':', $msg->body);
    $categoryId = (int) $message[1];
    $queueId = $message[0];

    $result = pg_query($dbconn, "SELECT * FROM products WHERE category = $categoryId AND show = 1") or die(pg_last_error($dbconn));

    $offers = "";
    while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
        $offers .= sprintf('<offer id="%s"><price>%s</price><name>%s</name></offer>%s', $line['id'], $line['id'] * 10, $line['title'], PHP_EOL);
    }

    $result_msg = new AMQPMessage($offers, ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);
    $channel->basic_publish($result_msg, '', 'result_queue_' . $queueId);
    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
};

$channel->basic_qos(null, 1, null);
$channel->basic_consume('sequence_queue', '', false, false, false, false, $callback);

while ($channel->is_consuming()) {
    $channel->wait();
}

$channel->close();
$connection->close();
