<?php
/**
 * Author: Ivana Petrovic <petrovivana@gmail.com>
 * Date: 3/21/17
 * Time: 8:51 PM
 */

namespace TravelMap\Importer;

use Symfony\Component\Process\Process;
use TravelMap\Entity\User;


final class GoogleImporter implements ImporterInterface {

    /** @var string */
    private $basePath;

    /** @var User */
    private $user;

    public function __construct(User $user, $basePath) {
        $this->user = $user;
        $this->basePath = $basePath;
    }

    /** @inheritdoc */
    public function execute() {
        $userId = $this->user->getId();

        $process = new Process("{$this->basePath}app/console import:google {$userId}");
        $process->start();
    }
}