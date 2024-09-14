<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use PharIo\Manifest\InvalidEmailException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Exception\InvalidPasswordException;

#[AsCommand(
    name: 'app:delete-user',
    description: 'Deleted users from the database',
)]
class DeleteUserCommand extends Command
{
    private SymfonyStyle $io;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserRepository $users,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::OPTIONAL, 'The email of the an existing user')
            ->setHelp('This command allows you to delete an existing user')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $email */
        $email = $input->getArgument('email');
        $this->validateEmail($email);

        /** @var User $user */
        $user = $this->users->findOneBy(['email' => $email]);

        if (!$user) throw new RuntimeException(sprintf("User with email '%s' not found.", $email));

        $userId = $user->getId();

        $this->em->remove($user);
        $this->em->flush();

        $userEmail = $user->getEmail();

        $this->io->success(sprintf('User (ID: %d, email: %s) was successfully deleted.', $userId, $userEmail));

        return Command::SUCCESS;
    }

    private function validateEmail($email): void
    {
        if (empty($email))
            throw new InvalidPasswordException('Please enter a valid email address');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
            throw new InvalidEmailException('Please enter a valid email address');
    }
}
