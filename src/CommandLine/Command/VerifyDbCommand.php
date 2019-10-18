<?php

declare(strict_types=1);

namespace JStormes\dbTool\CommandLine\Command;

use JStormes\dbTool\Adapter\AdapterFactory;
use JStormes\dbTool\Adapter\AdapterInterface;
use JStormes\dbTool\Lib\parseDatabaseURL;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use PDO;
use Exception;

class VerifyDbCommand extends Command
{
    /** @var LoggerInterface  */
    private $logger;

    /** @var String  */
    private $databaseURL;

    /** @var AdapterFactory  */
    private $databaseAdapterFactory;

    /**
     * TestDbCommand Constructor.
     * @param LoggerInterface $logger
     * @param EntityManager $entityManager|null
     * @param String $databaseURL
     * @param AdapterInterface $databaseAdapterFactory
     */
    public function __construct(LoggerInterface $logger, AdapterFactory $databaseAdapterFactory)
    {
        $this->logger = $logger;

        $this->databaseAdapterFactory = $databaseAdapterFactory;

        parent::__construct();
    }

    /**
     * Configures the command
     */
    protected function configure()
    {
        $this->setName('connect-db')
            ->setDescription('Verify Database is reachable.');

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

            $output->writeln("Checking Database {$databaseName}...");

            $databaseAdapter = $this->databaseAdapterFactory->getAdapter($databaseUrl);

            /** @var PDO $pdo */
            $pdo = $databaseAdapter->connectToDatabase($databaseUrl);

            $tables = $databaseAdapter->getDbTables($pdo);

            if (count($tables)==0) {
                $this->logger->info("No tables found.");
            }

            $output->writeln("Database Testing Passed ...");

            return 0;
        }
        catch (Exception $ex) {
            $this->logger->alert($ex->getMessage());
            $output->writeln("Database Testing Failed ...");
            return -1;
        }

    }


}