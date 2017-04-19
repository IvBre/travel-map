<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 4/16/17
 * Time: 10:12 PM
 */

namespace TravelMap\Command;

use Knp\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TravelMap\Importer\ImporterInterface;

/**
 * @codeCoverageIgnore
 */
final class GoogleImportCommand extends Command {

    /** @var ImporterInterface */
    private $importer;

    public function __construct(ImporterInterface $importer) {
        parent::__construct();
        $this->importer = $importer;
    }

    /** @inheritdoc */
    protected function configure() {
        $this
            ->setName("import:google")
            ->setDescription("Execute the import of events from Google")
            ->addArgument('userId', InputArgument::REQUIRED, "User ID to import events for.");
    }

    /** @inheritdoc */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $userId = $input->getArgument('userId');

        $this->importer->execute($userId, $output);

        return 0;
    }
}