<?php

namespace App\Manager;

use App\Entity\Customer;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @method Customer|null find($id)
 * @method void          save(Customer $customer)
 * @method void          delete(Customer $customer)
 */
class CustomerManager
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManagerInterface;

    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    public function __construct(
        EntityManagerInterface $entityManagerInterface,
        CustomerRepository $customerRepository
    ) {
        $this->entityManagerInterface = $entityManagerInterface;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @return Customer[]
     */
    public function list()
    {
        return $this->customerRepository->findAll();
    }

    /**
     * @param int $customerId
     * @return Customer
     */
    public function find($customerId)
    {
        return $this->customerRepository->find($customerId);
    }

    /**
     * @param Customer $customer
     * @return void
     */
    public function save(Customer $customer)
    {
        $this->entityManagerInterface->persist($customer);
        $this->entityManagerInterface->flush();
    }

    /**
     * @param Customer $customer
     * @return void
     */
    public function delete(Customer $customer)
    {
        $this->entityManagerInterface->remove($customer);
        $this->entityManagerInterface->flush();
    }
}
