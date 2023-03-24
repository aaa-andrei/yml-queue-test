<?php

require_once __DIR__ . '/../vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->queue_declare('sequence_queue', false, true, false, false);
$channel->queue_declare('result_queue', false, true, false, false);

echo " [*] Waiting for messages. To exit press CTRL+C\n";

$callback = function ($msg) use ($channel) {
    $number = $msg->body;

    $offer_row = sprintf('<offer id="%s"><price>%s</price><name>%s</name></offer>%s', $number, $number, $number, PHP_EOL);

    // Sleep for demonstration purposes
    sleep(2);

    $result_msg = new AMQPMessage($offer_row, ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);
    $channel->basic_publish($result_msg, '', 'result_queue');
    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
};

$channel->basic_qos(null, 1, null);
$channel->basic_consume('sequence_queue', '', false, false, false, false, $callback);

while ($channel->is_consuming()) {
    $channel->wait();
}

$channel->close();
$connection->close();
