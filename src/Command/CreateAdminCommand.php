<?php

namespace App\Command;

use App\Entity\AllowedEmail;
use App\Entity\User;
use App\Repository\AllowedEmailRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Creates an administrator account directly in the database.
 *
 * Registration is whitelist-only and only admins can edit the whitelist, so
 * the very first admin has to be created from the command line.
 */
#[AsCommand(
    name: 'app:create-admin',
    description: 'Create an administrator account (bypasses the registration whitelist)',
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface      $em,
        private readonly UserRepository              $users,
        private readonly AllowedEmailRepository      $allowedEmails,
        private readonly UserPasswordHasherInterface $hasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Admin e-mail address (used to sign in)')
            ->addOption('password', 'p', InputOption::VALUE_REQUIRED, 'Password (asked interactively when omitted)')
            ->addOption('firstname', null, InputOption::VALUE_REQUIRED, 'First name', 'Admin')
            ->addOption('lastname', null, InputOption::VALUE_REQUIRED, 'Last name', 'Platform')
            ->addOption('if-not-exists', null, InputOption::VALUE_NONE, 'Exit quietly when the account already exists');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io    = new SymfonyStyle($input, $output);
        $email = strtolower(trim((string) $input->getArgument('email')));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $io->error(sprintf('"%s" is not a valid e-mail address.', $email));
            return Command::INVALID;
        }

        if ($this->users->findOneBy(['email' => $email])) {
            if ($input->getOption('if-not-exists')) {
                $io->writeln(sprintf('Admin account %s already exists, skipping.', $email));
                return Command::SUCCESS;
            }
            $io->error(sprintf('A user with the e-mail %s already exists.', $email));
            return Command::FAILURE;
        }

        $password = $input->getOption('password') ?? $io->askHidden('Password (min. 8 characters)');
        if (!is_string($password) || strlen($password) < 8) {
            $io->error('The password must be at least 8 characters long.');
            return Command::INVALID;
        }

        $user = (new User())
            ->setEmail($email)
            ->setPrenom((string) $input->getOption('firstname'))
            ->setNom((string) $input->getOption('lastname'))
            ->setRoles(['ROLE_ADMIN']);
        $user->setPassword($this->hasher->hashPassword($user, $password));

        // Keep the whitelist consistent with the users table.
        $allowed = $this->allowedEmails->findOneBy(['email' => $email]) ?? (new AllowedEmail())->setEmail($email);
        $allowed->setRole('ROLE_ADMIN')->setIsRegistered(true)->setNote('Created from the command line');

        $this->em->persist($user);
        $this->em->persist($allowed);
        $this->em->flush();

        $io->success(sprintf('Admin account %s created. You can now sign in at /login.', $email));

        return Command::SUCCESS;
    }
}
