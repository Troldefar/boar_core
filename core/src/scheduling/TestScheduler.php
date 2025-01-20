<?php

namespace app\core\src\scheduling;

final class TestScheduler extends Cron {

    public function __construct() {
        $this->setCronjobs(['runJob']);
    }

    public function runJob() {
        echo 'works';
    }

}