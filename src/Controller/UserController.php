<?php

namespace App\Controller;

use App\Entity\User;
use App\Manager\UserManager;
use Twig\Environment as TwigEnvironment;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;

/**
 * @Route("/api/users", name="users_")
 */
class UserController extends AbstractController
{
    private $userManager;

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * @Route(name="create", methods={"POST"})
     * 
     * @IsGranted("ROLE_ADMIN")
     * 
     *  @param Request $request
      * @param TwigEnvironment $templating
      * @param SerializerInterface $serializerInterface
      * @param ValidatorInterface $validatorInterface
      * @return json
     */
    public function create(Request $request, TwigEnvironment $templating, SerializerInterface $serializerInterface, ValidatorInterface $validatorInterface)
    {
        try {

            $user = $serializerInterface->deserialize($request->getContent(), User::class, 'json');
            
            $errors = $validatorInterface->validate($user);
            if (count($errors) > 0) {
                return $this->json($errors, Response::HTTP_BAD_REQUEST);
            }

            $password = $this->userManager->generatePassword($user);
            $this->userManager->save($user);
            $this->userManager->sendCreationEmail($templating, $user, $password);

            return $this->json($user, Response::HTTP_CREATED, [], ['groups' => 'user:read']);

        } catch (NotEncodableValueException $e) {

            return $this->json($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Route("/{userId}", name="read", methods={"GET"})
     * 
     * @IsGranted("ROLE_USER")
     * 
     * @param int $userId
     * @return json
     * @throws NotFoundHttpException
     */
    public function read($userId)
    {
        $user = $this->userManager->find($userId);

        if (!$user) {
            throw new NotFoundHttpException('User not found');
        }

        return $this->json($user, Response::HTTP_OK, [], ['groups' => 'user:read']);
    }

    /**
     * @Route("/{userId}", name="update", methods={"PUT"})
     * 
     * @IsGranted("ROLE_ADMIN")
     * 
     * @param int $userId
     * @param Request $request
     * @param SerializerInterface $serializerInterface
     * @param ValidatorInterface $validatorInterface
     * @return json
     * @throws NotFoundHttpException
     */
    public function update($userId, Request $request, SerializerInterface $serializerInterface, ValidatorInterface $validatorInterface)
    {
        try {

            $currentUser = $this->userManager->find($userId);
            if (!$currentUser) {
                throw new NotFoundHttpException('User not found');
            }

            $user = $serializerInterface->deserialize($request->getContent(), User::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $currentUser, 'groups' => ['user:read']]);

            $errors = $validatorInterface->validate($user);
            if (count($errors) > 0) {
                return $this->json($errors, Response::HTTP_BAD_REQUEST);
            }

            $this->userManager->save($user);
                
            return $this->json($user, Response::HTTP_OK, [], ['groups' => 'user:read']);

        } catch (NotEncodableValueException $e) {

            return $this->json($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Route("/{userId}", name="delete", methods={"DELETE"})
     * 
     * @IsGranted("ROLE_ADMIN")
     * 
     * @param int $userId
     * @return json
     * @throws NotFoundHttpException
     */
    public function delete($userId)
    {
        $user = $this->userManager->find($userId);
        if (!$user) {
            throw new NotFoundHttpException('User not found');
        }
        
        $this->userManager->delete($user);
        
        return $this->json('', Response::HTTP_OK);
    }
}
