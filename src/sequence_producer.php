<?php

require_once __DIR__ . '/../vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->queue_declare('sequence_queue', false, true, false, false);
$channel->queue_declare('result_queue', false, true, false, false);

// Generate a sequence of numbers from 1 to 10
for ($i = 1; $i <= 20; $i++) {
    $number = strval($i);
    $msg = new AMQPMessage($number, ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);

    $channel->basic_publish($msg, '', 'sequence_queue');
}

// Function to handle received results
$result_callback = function ($msg) {
    echo $msg->body;
    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
};

$channel->basic_qos(0, 1, 0);
$channel->basic_consume('result_queue', '', false, false, false, false, $result_callback);

// Wait for results
$received_results = 0;
while ($received_results < 20) {
    $channel->wait();
    $received_results++;
}

$channel->close();
$connection->close();