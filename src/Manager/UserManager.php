<?php

namespace App\Manager;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Mime\Email;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Environment as TwigEnvironment;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserManager
{
    private $em;
    private $userRepository;
    private $encoder;
    private $mailer;

    public function __construct(
        EntityManagerInterface $em,
        UserRepository $userRepository,
        UserPasswordEncoderInterface $encoder,
        MailerInterface $mailer
    ) {
        $this->em = $em;
        $this->userRepository = $userRepository;
        $this->encoder = $encoder;
        $this->mailer = $mailer;
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
     */
    public function save(User $user)
    {
        $this->em->persist($user);
        $this->em->flush();
    }

    /**
     * @param User $user
     */
    public function delete(User $user)
    {
        $this->em->remove($user);
        $this->em->flush();
    }

    /**
     * @param string $email
     * @return User
     */
    public function createAdminFromCommand(TwigEnvironment $templatingEngine, $email)
    {
        $user = new User();
        $user->setEmail($email);
        $user->setRoles([User::ROLE_ADMIN]);

        $password = $this->generatePassword($user);

        $this->em->persist($user);
        $this->em->flush();

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
        $encoded = $this->encoder->encodePassword($user, $password);
        $user->setPassword($encoded);

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
                // templates/emails/userRegistration.txt.twig
                'emails/userRegistration.txt.twig',
                [
                    'email' => $user->getEmail(),
                    'password' => $password,
                    'url' => $_ENV['APP_FO_URL']
                ]
            ))
            ->html($templatingEngine->render(
                // templates/emails/userRegistration.html.twig
                'emails/userRegistration.html.twig',
                [
                    'email' => $user->getEmail(),
                    'password' => $password,
                    'url' => $_ENV['APP_FO_URL']
                ]
            ));

        $this->mailer->send($email);
    }
}
