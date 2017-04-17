<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 3/21/17
 * Time: 8:50 PM
 */

namespace TravelMap\Importer;

interface ImporterInterface {

    /**
     * Executes the importing of events into db
     */
    public function execute();
}