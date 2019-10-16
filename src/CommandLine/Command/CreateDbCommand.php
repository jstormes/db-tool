<?php

declare(strict_types=1);

namespace JStormes\dbTool\CommandLine\Command;

use Database\AdapterInterface;
use Database\parseDatabaseURL;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PDO;
use Exception;
use Symfony\Component\Console\Question\Question;

class CreateDbCommand extends Command
{
    /** @var LoggerInterface  */
    private $logger;

    /** @var EntityManager  */
    private $entityManager;

    /** @var String  */
    private $databaseURL;

    /** @var AdapterInterface  */
    private $databaseAdapter;

    /** @var string */
    private $privilegedDbUser;

    /** @var string */
    private $privilegedDbPassword;

    /**
     * TestDbCommand Constructor.
     * @param LoggerInterface $logger
     * @param EntityManager $entityManager|null
     * @param String $databaseURL
     * @param AdapterInterface $databaseAdapter
     * @param string $privilegedDbUser
     * @param string $privilegedDbPassword
     */
    public function __construct(LoggerInterface $logger,
                                ?EntityManager $entityManager,
                                String $databaseURL,
                                AdapterInterface $databaseAdapter,
                                string $privilegedDbUser,
                                string $privilegedDbPassword)
    {
        $this->logger = $logger;

        $this->entityManager = $entityManager;

        $this->databaseURL = $databaseURL;

        $this->databaseAdapter = $databaseAdapter;

        $this->privilegedDbUser = $privilegedDbUser;

        $this->privilegedDbPassword = $privilegedDbPassword;

        parent::__construct();
    }

    /**
     * Configures the command
     */
    protected function configure()
    {
        $this->setName('create-db')
            ->setDescription('Create Database');
    }

    /**
     * Executes the current command
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $output->writeln("Creating Database  ...");

            $urlParser = new parseDatabaseURL();

            if ((empty($this->privilegedDbUser))&&(empty($this->privilegedDbPassword))) {
                $helper = $this->getHelper('question');
                $usernameQuestion = new Question('Privileged (root) database user name: ');
                $usernamePassword = new Question('Privileged (root) database user password: ');
                $this->privilegedDbUser = $helper->ask($input, $output, $usernameQuestion);
                $this->privilegedDbPassword = $helper->ask($input, $output, $usernamePassword);
            }


            $pdo = $this->databaseAdapter->connectToHost(
                $urlParser->getDbScheme($this->databaseURL),
                $urlParser->getDbHost($this->databaseURL),
                $this->privilegedDbUser,
                $this->privilegedDbPassword
            );

            $database = $urlParser->getDbName($this->databaseURL);
            $historyDatabase = $urlParser->getDbName($this->databaseURL).'_history';

            $this->databaseAdapter->createDb($pdo, $database);
            $this->databaseAdapter->createDb($pdo, $historyDatabase);

            $this->databaseAdapter->createDbUser($pdo, $urlParser->getDbUser($this->databaseURL), $urlParser->getDbPassword($this->databaseURL));

            $this->databaseAdapter->grantDbPermissions($pdo, $urlParser->getDbUser($this->databaseURL), $database);
            $this->databaseAdapter->grantDbSelectOnlyPermissions($pdo, $urlParser->getDbUser($this->databaseURL), $historyDatabase);

//            $this->createSchema($pdo);

//            $this->resetPermissions($pdo);

            $output->writeln("Database Created ...");

            return 0;
        }
        catch (Exception $ex) {
            $this->logger->alert($ex->getMessage());
            $output->writeln("Database Create Failed ...");
            return -1;
        }

    }

}