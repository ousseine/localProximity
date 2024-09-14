<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use PharIo\Manifest\InvalidEmailException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Exception\InvalidPasswordException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Stopwatch\Stopwatch;

use function Symfony\Component\String\u;

#[AsCommand(
    name: 'app:add-user',
    description: 'Creates users and stores them in the database',
)]
class AddUserCommand extends Command
{
    private SymfonyStyle $io;

    public function __construct(
        private readonly UserPasswordHasherInterface $hasher,
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::OPTIONAL, 'The email of the user')
            ->addArgument('password', InputArgument::OPTIONAL, 'The plain password of the user')
            ->setHelp("This command allows you to create a user")
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $stopwatch = new Stopwatch();
        $stopwatch->start('add-user-command');

        /** @var string $email */
        $email = $input->getArgument('email');

        /** @var string $password */
        $password = $input->getArgument('password');

        $this->validateEmail($email);
        $this->validatePassword($password);

        $user = new User();
        $user->setEmail($email);
        $user->setPassword($this->hasher->hashPassword($user, $password));
        $user->setRoles(['ROLE_ADMIN']);

        $this->em->persist($user);
        $this->em->flush();

        $this->io->success(sprintf('%s was successfully created: %s', 'User', $user->getEmail()));

        $event = $stopwatch->stop('add-user-command');

        if ($output->isVerbose()) {
            $this->io->comment(sprintf('New user database id: %d / Elapsed time: %.2f ms / Consumed memory: %.2f MB', $user->getId(), $event->getDuration(), $event->getMemory() / (1024 ** 2)));
        }

        return Command::SUCCESS;
    }

    private function validateEmail($email): void
    {
        if (empty($email))
            throw new InvalidPasswordException('Please enter a valid email address');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
            throw new InvalidEmailException('Please enter a valid email address');
    }

    private function validatePassword(string $plainPassword): void
    {
        if (empty($plainPassword))
            throw new InvalidPasswordException('The password cannot be empty');

        if (u($plainPassword)->trim()->length() < 6)
            throw new InvalidPasswordException('The password must be at least 6 characters long');
    }
}
