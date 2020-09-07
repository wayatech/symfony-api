<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Manager\CustomerManager;
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
 * @Route("/api/customers", name="customers_")
 * 
 * @method json create(Request $request, TwigEnvironment $templating, SerializerInterface $serializerInterface, ValidatorInterface $validatorInterface)
 * @method json read(int $customerId)
 * @method json update(int $customerId, Request $request, SerializerInterface $serializerInterface, ValidatorInterface $validatorInterface)
 * @method json delete(int $customerId)
 */
class CustomerController extends AbstractController
{
    /**
     * @var CustomerManager
     */
    private $customerManager;

    public function __construct(CustomerManager $customerManager)
    {
        $this->customerManager = $customerManager;
    }

    /**
     * @Route(name="create", methods={"POST"})
     * 
     * @IsGranted("ROLE_USER")
     * 
     *  @param Request $request
      * @param SerializerInterface $serializerInterface
      * @param ValidatorInterface $validatorInterface
      * @return json
     */
    public function create(Request $request, SerializerInterface $serializerInterface, ValidatorInterface $validatorInterface)
    {
        try {

            $customer = $serializerInterface->deserialize($request->getContent(), Customer::class, 'json');
            
            $errors = $validatorInterface->validate($customer);
            if (count($errors) > 0) {
                return $this->json($errors, Response::HTTP_BAD_REQUEST);
            }

            $this->customerManager->save($customer);

            return $this->json($customer, Response::HTTP_CREATED, [], ['groups' => 'customer:read']);

        } catch (NotEncodableValueException $e) {

            return $this->json($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Route("/{customerId}", name="read", methods={"GET"})
     * 
     * @IsGranted("ROLE_USER")
     * 
     * @param int $customerId
     * @return json
     * @throws NotFoundHttpException
     */
    public function read($customerId)
    {
        $customer = $this->customerManager->find($customerId);

        if (!$customer) {
            throw new NotFoundHttpException('Customer not found');
        }

        return $this->json($customer, Response::HTTP_OK, [], ['groups' => 'customer:read']);
    }

    /**
     * @Route("/{customerId}", name="update", methods={"PUT"})
     * 
     * @IsGranted("ROLE_USER")
     * 
     * @param int $customerId
     * @param Request $request
     * @param SerializerInterface $serializerInterface
     * @param ValidatorInterface $validatorInterface
     * @return json
     * @throws NotFoundHttpException
     */
    public function update($customerId, Request $request, SerializerInterface $serializerInterface, ValidatorInterface $validatorInterface)
    {
        try {

            $currentCustomer = $this->customerManager->find($customerId);
            if (!$currentCustomer) {
                throw new NotFoundHttpException('Customer not found');
            }

            $customer = $serializerInterface->deserialize($request->getContent(), Customer::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $currentCustomer, 'groups' => ['customer:read']]);

            $errors = $validatorInterface->validate($customer);
            if (count($errors) > 0) {
                return $this->json($errors, Response::HTTP_BAD_REQUEST);
            }

            $this->customerManager->save($customer);
                
            return $this->json($customer, Response::HTTP_OK, [], ['groups' => 'customer:read']);

        } catch (NotEncodableValueException $e) {

            return $this->json($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Route("/{customerId}", name="delete", methods={"DELETE"})
     * 
     * @IsGranted("ROLE_USER")
     * 
     * @param int $customerId
     * @return json
     * @throws NotFoundHttpException
     */
    public function delete($customerId)
    {
        $customer = $this->customerManager->find($customerId);
        if (!$customer) {
            throw new NotFoundHttpException('Customer not found');
        }
        
        $this->customerManager->delete($customer);
        
        return $this->json('', Response::HTTP_OK);
    }
}
