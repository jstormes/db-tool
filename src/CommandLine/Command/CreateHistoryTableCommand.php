<?php

declare(strict_types=1);

namespace JStormes\dbTool\CommandLine\Command;

use JStormes\dbTool\Adapter\AdapterFactory;
use JStormes\dbTool\Adapter\AdapterInterface;
use JStormes\dbTool\Exception\DatabaseException;
use JStormes\dbTool\Lib\parseDatabaseURL;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PDO;
use Exception;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Input\InputArgument;

class CreateHistoryTableCommand extends Command
{
    /** @var LoggerInterface  */
    private $logger;

    /** @var AdapterInterface  */
    private $databaseAdapterFactory;

    /** @var string */
    private $privilegedDbUser;

    /** @var string */
    private $privilegedDbPassword;

    /**
     * TestDbCommand Constructor.
     * @param LoggerInterface $logger
     * @param String $databaseURL
     * @param AdapterFactory $databaseAdapterFactory
     * @param string $privilegedDbUser
     * @param string $privilegedDbPassword
     */
    public function __construct(LoggerInterface $logger,
                                AdapterFactory $databaseAdapterFactory,
                                string $privilegedDbUser = '',
                                string $privilegedDbPassword = '')
    {
        $this->logger = $logger;

        $this->databaseAdapterFactory = $databaseAdapterFactory;

        $this->privilegedDbUser = $privilegedDbUser;

        $this->privilegedDbPassword = $privilegedDbPassword;

        parent::__construct();
    }

    /**
     * Configures the command
     */
    protected function configure()
    {
        $this->setName('create-history-table')
            ->setDescription('Create History Table from existing table');

        $this->addArgument('database_url', InputArgument::REQUIRED, 'Database URL or Environment Variable with Database URL');
        $this->addArgument('history_database_url', InputArgument::REQUIRED, 'Database URL or Environment Variable with History Database URL');
        $this->addArgument('table_name', InputArgument::REQUIRED, 'Table add history version of');
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

            $historyDatabaseUrl = $input->getArgument('history_database_url');
            if (!(empty(getenv($historyDatabaseUrl)))) {
                $historyDatabaseUrl = getenv($historyDatabaseUrl);
            }

            $tableName = $input->getArgument('table_name');

            $urlParser = new parseDatabaseURL();

            $databaseName = $urlParser->getDbName($databaseUrl);
            $historyDatabaseName = $urlParser->getDbName($historyDatabaseUrl);

            $output->writeln("Creating History Table  {$tableName} from database {$databaseName} to {$historyDatabaseName} ...");

            if ((empty($this->privilegedDbUser))&&(empty($this->privilegedDbPassword))) {
                $helper = $this->getHelper('question');
                $usernameQuestion = new Question('Privileged (root) database user name: ');
                $usernamePassword = new Question('Privileged (root) database user password: ');
                $this->privilegedDbUser = $helper->ask($input, $output, $usernameQuestion);
                $this->privilegedDbPassword = $helper->ask($input, $output, $usernamePassword);
            }

            $databaseAdapter = $this->databaseAdapterFactory->getAdapter($databaseUrl);

            /** @var PDO $pdo */
            $pdo = $databaseAdapter->connectToHost(
                $urlParser->getDbScheme($databaseUrl),
                $urlParser->getDbHost($databaseUrl),
                $this->privilegedDbUser,
                $this->privilegedDbPassword,
                $urlParser->getDbName($databaseUrl)
            );

            $this->logger->notice("Checking that history table {$tableName} on {$historyDatabaseName} is empty.");
            if ( ! $databaseAdapter->isTableEmpty($pdo, $tableName, $historyDatabaseName) ) {
                throw new DatabaseException('History Table is not Empty!');
            }

            $this->logger->notice("Dropping history table {$tableName}.");
            $databaseAdapter->dropTable($pdo, $historyDatabaseName, $tableName);

            $this->logger->notice("Creating history table {$tableName}.");
            $databaseAdapter->createTableFromExistingTable($pdo, $databaseName, $tableName, $historyDatabaseName, $tableName);

            $this->logger->notice("Adding history columns to table {$tableName}.");
            $databaseAdapter->addHistoryColumns($pdo, $historyDatabaseName, $tableName);

            $this->logger->notice(("Creating Stored Procedure."));
            $databaseAdapter->createHistoryStoredProc($pdo, $databaseName, $tableName, $historyDatabaseName, $tableName);

            $output->writeln("History Table Created ...");

            return 0;
        }
        catch (Exception $ex) {
            $this->logger->alert($ex->getMessage());
            $output->writeln("History Table Create Failed ...");
            return -1;
        }

    }

}