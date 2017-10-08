<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;
use AppBundle\Entity\Books;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;


class BooksController extends FOSRestController
{
    /**
     * @Rest\Get("/books")
     */
    public function getAction()
    {
        $restresult = $this->getDoctrine()->getRepository('AppBundle:Books')->findAll();
        if ($restresult === null) {
            return new View("there are no users exist", Response::HTTP_NOT_FOUND);
        }
        return $restresult;
    }

    /**
     * @Rest\Post("/books")
     */
    public function postAction(Request $request)
    {
        $model = new Books;
        $attributes = json_decode($request->getContent());

        $model->name = $attributes->name;
        $model->author = $attributes->author;
        $model->year = $attributes->year;

        $model->attributes = (array)$attributes;


        $validator = $this->get('validator');
        $errors = $validator->validate($model);

        if (count($errors) > 0) {

            $response = [
                'status' => 0,
                'errors' => $errors
            ];
            return new View($response, Response::HTTP_NOT_ACCEPTABLE);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($model);
        $em->flush();

        $this->addTaskToQueue($model->attributes); //Adding a new task to RabbitMQ queue named "books_queue"

        $model->attributes['id'] = $model->id;

        $response = [
            'status' => 1,
            'data' => $model->attributes
        ];

        return new View($response, Response::HTTP_OK);
    }

    public function addTaskToQueue($data)
    {
        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $channel = $connection->channel();

        $queue = "books_queue";
        //create a queue
        $channel->queue_declare($queue, false, true, false, false);  //3rd param: true=>to make the queue durable when the RabbitMQ server dies

        //set message

        $msg = new AMQPMessage(json_encode($data), ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);
        //publish message into the queue
        $channel->basic_publish($msg, '', $queue);

        //close channel & connection
        $channel->close();
        $connection->close();
        return true;
    }


}