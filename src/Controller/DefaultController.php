<?php

namespace App\Controller;

use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/", name="default")
 *
 * @method json home()
 */
class DefaultController extends AbstractController
{
    /**
     * @Route(name="home", methods={"GET"})
     * @return json
     */
    public function home()
    {
        try {
            return $this->json("Welcome on this API", Response::HTTP_OK);
        } catch (Exception $e) {
            return $this->json($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }
}
