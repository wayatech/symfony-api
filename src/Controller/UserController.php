<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class UserController extends AbstractController
{
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @Route(name="create", methods={"POST"})
     */
    public function create()
    {

    }

    /**
     * @Route(name="read", methods={"GET"})
     */
    public function read()
    {

    }

    /**
     * @Route(name="update", methods={"PUT"})
     */
    public function update()
    {

    }

    /**
     * @Route(name="delete", methods={"DELETE"})
     */
    public function delete()
    {
        
    }
}
