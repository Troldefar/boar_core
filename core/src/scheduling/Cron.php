<?php

/**
|----------------------------------------------------------------------------
| Base for Cron behaviour
|----------------------------------------------------------------------------
|
| @author RE_WEB
| @package core\src\scheduling
|
*/

namespace app\core\src\scheduling;

use \app\core\src\factories\CronjobFactory;

use \app\models\CronModel;

class Cron {

    protected array $cronjobs = [];

    protected function getCronjobs(): array {
        return $this->cronjobs;
    }

    protected function setCronjobs(array $cronjobs): void {
        $this->cronjobs = $cronjobs;
    }

    public function run() {
        foreach ((new CronModel())->all() as $cronJob) {
            $handler = $cronJob->get('CronjobEntity');
            $cCronjob = (new CronjobFactory(compact('handler')))->create();

            foreach ($cCronjob->getCronjobs() as $cronjob) $cCronjob->{$cronjob}();
        }
    }

}