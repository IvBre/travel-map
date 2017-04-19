<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 3/21/17
 * Time: 8:50 PM
 */

namespace TravelMap\Importer;

use Symfony\Component\Console\Output\OutputInterface;

interface ImporterInterface {

    /**
     * Executes the importing of events into db
     * @param int $userId
     * @param OutputInterface $output
     * @return
     */
    public function execute($userId, OutputInterface $output);
}