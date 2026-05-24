<?php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Create or update an admin account in the etudiant table.'
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private readonly Connection $connection,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Admin email address')
            ->addArgument('password', InputArgument::REQUIRED, 'Plain-text password for the admin account')
            ->addOption('first-name', null, InputOption::VALUE_REQUIRED, 'First name', 'Admin')
            ->addOption('last-name', null, InputOption::VALUE_REQUIRED, 'Last name', 'System')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Update an existing admin account with the same email');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = strtolower(trim((string) $input->getArgument('email')));
        $password = (string) $input->getArgument('password');
        $firstName = trim((string) $input->getOption('first-name')) ?: 'Admin';
        $lastName = trim((string) $input->getOption('last-name')) ?: 'System';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $io->error('The email address is invalid.');

            return Command::FAILURE;
        }

        if (mb_strlen($password) < 8) {
            $io->error('The password must contain at least 8 characters.');

            return Command::FAILURE;
        }

        try {
            $schemaManager = $this->connection->createSchemaManager();
            $tables = array_map('strtolower', $schemaManager->listTableNames());

            if (!in_array('etudiant', $tables, true)) {
                $io->error('The etudiant table does not exist in the current database.');

                return Command::FAILURE;
            }

            $existing = $this->connection->fetchAssociative(
                'SELECT id, role FROM etudiant WHERE email = ?',
                [$email]
            );

            $passwordHash = password_hash($password, PASSWORD_ARGON2ID);

            if ($existing) {
                if (!$input->getOption('force')) {
                    $io->error(sprintf('An account with email "%s" already exists. Re-run with --force to update it.', $email));

                    return Command::FAILURE;
                }

                $this->connection->update('etudiant', [
                    'nom' => $lastName,
                    'prenom' => $firstName,
                    'mot_de_passe' => $passwordHash,
                    'role' => 'admin',
                ], [
                    'id' => $existing['id'],
                ]);

                $io->success(sprintf('Admin account for %s was updated.', $email));

                return Command::SUCCESS;
            }

            $this->connection->insert('etudiant', [
                'nom' => $lastName,
                'prenom' => $firstName,
                'email' => $email,
                'mot_de_passe' => $passwordHash,
                'role' => 'admin',
            ]);

            $io->success(sprintf('Admin account for %s was created.', $email));

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }
    }
}
