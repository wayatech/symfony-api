<?php

namespace App\Command;

use App\Manager\UserManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Twig\Environment as TwigEnvironment;

class CreateAdminCommand extends Command
{
    /**
     * @var TwigEnvironment
     */
    private $templatingEngine;

    /**
     * @var UserManager
     */
    private $userManager;

    /**
     * @param TwigEnvironment $templatingEngine
     * @param UserManager $userManager
     */
    public function __construct(TwigEnvironment $templatingEngine, UserManager $userManager)
    {
        $this->templatingEngine = $templatingEngine;
        $this->userManager = $userManager;
        parent::__construct();
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('app:admin:create')
            ->setDescription('Create a new admin.')
            ->setHelp('This command allows you to create a new admin.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(
            [
                'WARNING : This command will create an administrator with all privileges.',
                '============',
                '',
            ]
        );

        $helper = $this->getHelper('question');

        $question = new Question('Enter the email : ', '');
        $question->setNormalizer(
            function ($value) {
                return $value ? trim($value) : '';
            }
        );
        $email = $helper->ask($input, $output, $question);

        if (empty($email)) {
            $output->writeln('Email MUST be provided.');

            return 0;
        }

        $question = new ConfirmationQuestion(
            sprintf('You are about to create an administrator with all privileges for email "%s". Do you confirm (yes/no) ? ', $email),
            false
        );
        if (!$helper->ask($input, $output, $question)) {
            $output->writeln('Creation canceled.');

            return 0;
        }

        try {
            $admin = $this->userManager->createAdminFromCommand($this->templatingEngine, $email);
            if ($admin) {
                $output->writeln(sprintf('Admin has been created. Email has been sent to %s', $admin->getEmail()));
            } else {
                $output->writeln(sprintf('Admin %s has been NOT been created.', $email));
                return 1;
            }
        } catch (\Exception $e) {
            $output->writeln(sprintf('An error occurred : %s', $e->getMessage()));
            return 1;
        }

        $output->writeln('End.');

        return 0;
    }
}
