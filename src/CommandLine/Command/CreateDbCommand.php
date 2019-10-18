<?php

declare(strict_types=1);

namespace JStormes\dbTool\CommandLine\Command;

use JStormes\dbTool\Adapter\AdapterFactory;
use JStormes\dbTool\Adapter\AdapterInterface;
use JStormes\dbTool\Exception\DatabaseException;
use JStormes\dbTool\Lib\parseDatabaseURL;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PDO;
use Exception;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Input\InputOption;

class CreateDbCommand extends Command
{
    /** @var LoggerInterface  */
    private $logger;

    /** @var AdapterFactory  */
    private $databaseAdapterFactory;

    /** @var string */
    private $privilegedDbUser;

    /** @var string */
    private $privilegedDbPassword;

    /**
     * TestDbCommand Constructor.
     * @param LoggerInterface $logger
     * @param AdapterFactory $databaseAdapterFactory
     * @param string $privilegedDbUser
     * @param string $privilegedDbPassword
     */
    public function __construct(LoggerInterface $logger,
                                AdapterFactory $databaseAdapterFactory,
                                string $privilegedDbUser,
                                string $privilegedDbPassword)
    {
        $this->logger = $logger;

        $this->databaseAdapterFactory = $databaseAdapterFactory;

        $this->privilegedDbUser = $privilegedDbUser;

        $this->privilegedDbPassword = $privilegedDbPassword;

        parent::__construct();
    }

    /**
     * Configures the command line
     */
    protected function configure()
    {
        $this->setName('create-db')
            ->setDescription('Create database and set database access permissions');

        $this->addArgument('database_url', InputArgument::REQUIRED, 'Database URL or Environment Variable with Database URL');
        $this->addOption(
            'permissions',
            'p',
            InputOption::VALUE_OPTIONAL,
            'access type (application | history)',
            'application'
        );
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

            $databaseUrl = $input->getArgument('database_url');

            if (!(empty(getenv($databaseUrl)))) {
                $databaseUrl = getenv($databaseUrl);
            }

            $urlParser = new parseDatabaseURL();

            $databaseName = $urlParser->getDbName($databaseUrl);

            $output->writeln("Creating Database '$databaseName' ...");

            /** @var AdapterInterface $databaseAdapter */
            $databaseAdapter = $this->databaseAdapterFactory->getAdapter($databaseUrl);

            if ((empty($this->privilegedDbUser))&&(empty($this->privilegedDbPassword))) {
                $helper = $this->getHelper('question');
                $usernameQuestion = new Question('Privileged (root) database user name: ');
                $usernamePassword = new Question('Privileged (root) database user password: ');
                $this->privilegedDbUser = $helper->ask($input, $output, $usernameQuestion);
                $this->privilegedDbPassword = $helper->ask($input, $output, $usernamePassword);
            }


            /** @var PDO $pdo */
            $pdo = $databaseAdapter->connectToHost(
                $urlParser->getDbScheme($databaseUrl),
                $urlParser->getDbHost($databaseUrl),
                $this->privilegedDbUser,
                $this->privilegedDbPassword
            );

            $databaseAdapter->createDb($pdo, $databaseName);

            $databaseAdapter->createDbUser($pdo, $urlParser->getDbUser($databaseUrl), $urlParser->getDbPassword($databaseUrl));

            $permissions = $input->getOption('permissions');

            switch ($permissions) {
                case 'application':
                    $databaseAdapter->grantDbApplicationPermissions($pdo, $urlParser->getDbUser($databaseUrl), $databaseName);
                    break;
                case 'history':
                    $databaseAdapter->grantDbHistoryPermissions($pdo, $urlParser->getDbUser($databaseUrl), $databaseName);
                    break;
                default:
                    throw new DatabaseException('Unknown Database Permission');
            }

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