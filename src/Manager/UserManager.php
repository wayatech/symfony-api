<?php

namespace App\Manager;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Mime\Email;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Environment as TwigEnvironment;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @method User|null find($id)
 * @method void      save(User $user)
 * @method void      delete(User $user)
 * @method User      createAdminFromCommand(TwigEnvironment $templatingEngine, string $email)
 * @method string    generatePassword(User $user)
 * @method void      sendCreationEmail(TwigEnvironment $templatingEngine, User $user, string $password)
 */
class UserManager
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManagerInterface;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $userPasswordEncoderInterface;

    /**
     * @var MailerInterface
     */
    private $mailerInterface;

    public function __construct(
        EntityManagerInterface $entityManagerInterface,
        UserRepository $userRepository,
        UserPasswordEncoderInterface $userPasswordEncoderInterface,
        MailerInterface $mailerInterface
    ) {
        $this->entityManagerInterface = $entityManagerInterface;
        $this->userRepository = $userRepository;
        $this->userPasswordEncoderInterface = $userPasswordEncoderInterface;
        $this->mailerInterface = $mailerInterface;
    }

    /**
     * @param int $userId
     * @return User
     */
    public function find($userId)
    {
        return $this->userRepository->find($userId);
    }

    /**
     * @param User $user
     * @return void
     */
    public function save(User $user)
    {
        $this->entityManagerInterface->persist($user);
        $this->entityManagerInterface->flush();
    }

    /**
     * @param User $user
     * @return void
     */
    public function delete(User $user)
    {
        $this->entityManagerInterface->remove($user);
        $this->entityManagerInterface->flush();
    }

    /**
     * @param TwigEnvironment $templatingEngine
     * @param string $email
     * @return User
     */
    public function createAdminFromCommand(TwigEnvironment $templatingEngine, $email)
    {
        $user = new User();
        $user->setEmail($email);
        $user->setRoles([User::ROLE_ADMIN]);

        $password = $this->generatePassword($user);

        $this->entityManagerInterface->persist($user);
        $this->entityManagerInterface->flush();

        $this->sendCreationEmail($templatingEngine, $user, $password);

        return $user;
    }

    /**
     * @param User $user
     * @return string
     */
    public function generatePassword(User $user)
    {
        $password = md5(random_bytes(10));
        $encodedPassword = $this->userPasswordEncoderInterface->encodePassword($user, $password);
        $user->setPassword($encodedPassword);

        return $password;
    }

    /**
     * @param TwigEnvironment $templatingEngine
     * @param User $user
     * @param string $password
     */
    public function sendCreationEmail(TwigEnvironment $templatingEngine, User $user, string $password)
    {
        $email = (new Email())
            ->from($_ENV['MAILER_SENDER'])
            ->to($user->getEmail())
            ->priority(Email::PRIORITY_HIGH)
            ->subject('Creation of your account')
            ->text($templatingEngine->render(
                'emails/userRegistration.txt.twig',
                [
                    'email' => $user->getEmail(),
                    'password' => $password,
                    'url' => $_ENV['APP_FO_URL']
                ]
            ))
            ->html($templatingEngine->render(
                'emails/userRegistration.html.twig',
                [
                    'email' => $user->getEmail(),
                    'password' => $password,
                    'url' => $_ENV['APP_FO_URL']
                ]
            ));

        $this->mailerInterface->send($email);
    }
}
