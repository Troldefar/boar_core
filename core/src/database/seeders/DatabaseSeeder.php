<?php

/**
|----------------------------------------------------------------------------
| Database seeder
|----------------------------------------------------------------------------
| Object used to create entities instead of manual labor
| 
| @author RE_WEB
| @package core
|
*/

namespace app\core\src\database\seeders;

use \app\core\src\factories\ModelFactory;

class DatabaseSeeder {

    public function up(string $handler, int $amount): void {
        for ($i = 0; $i < $amount; $i++) {
            $entity = (new ModelFactory(compact('handler')))->create();
            $entity->set(...$entity->getEntityTableFields()->getData())->save();
        }
    }

}