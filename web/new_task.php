<?php
require __DIR__.'/../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;


$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();


$queue="books_queue";
//create a queue
$channel->queue_declare($queue, false, true, false, false);  //3rd param: true=>to make the queue durable when the RabbitMQ server dies

//set message


$data = implode(' ', array_slice($argv, 1));

if(empty($data)) $data = 'Hello World! - Send by Sirin at '.date("d M H:i:s");


//$data=isset($_GET['m'])?$_GET['m']:$data;

$msg = new AMQPMessage($data,['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);


//publish message into the queue
$channel->basic_publish($msg, '', $queue);

/*
echo "<pre>";
print_r($r);
exit;
*/

//close channel & connection
$channel->close();
$connection->close();

echo " [x] Sent ".$data."\n";