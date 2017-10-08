<?php
require __DIR__ . '/../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use AppBundle\AppBundle;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();


$queue = "books_queue";
//create a queue
$channel->queue_declare($queue, false, true, false, false); //3rd param: true=>to make the queue durable when the RabbitMQ server dies

echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";

$callback = function ($msg) {
    echo " [x] Received ", $msg->body, "\n";

    $elastic_search_end_point = "https://24904dfdbe966c5d725b1ae26f58b678.us-east-1.aws.found.io:9243/books_index/books";

    $r = AppBundle::sendHttpRequest($elastic_search_end_point, "POST", $msg->body, [], "json", "elastic", "ag0y58khFn7CPoonK2F4zCtV");

    echo " [x] Elasticsearch response:", $r, "\n";
    echo " [x] Done", "\n";

    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
};

$channel->basic_qos(null, 1, null); //Fair dispatch: to make sure that the RabbitMQ server will only dispatch 1 message at a time to a Consumer.

$channel->basic_consume($queue, '', false, false, false, false, $callback);// 4th param: false=>to enable message ack to RabbitMQ server

while (count($channel->callbacks)) {
    $channel->wait();
}

$channel->close();
$connection->close();