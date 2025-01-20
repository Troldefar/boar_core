<?php

/**
|----------------------------------------------------------------------------
| HTML Snippets
|----------------------------------------------------------------------------
| 
| HTML Snippets for reuseability
|
| @author RE_WEB
| @package app\core\src\html
|
*/

namespace app\core\src\html;

class Html {

    public static function datePicker(string $value, string $name, string $placeholder): string {
        ob_start(); ?>
            <div data-mdb-start-day="1" class="form-outline" data-mdb-datepicker-init data-mdb-input-init data-mdb-inline="true" data-mdb-format="dd-mm-yyyy">
                <input data-inject-data-type="" value="<?= hs($value ?? ''); ?>" name="<?= hs($name); ?>" type="text" class="form-control" id="datetimepickerExample" placeholder="<?= hs($placeholder); ?>" />
            </div>
        <?php return ob_get_clean();
    }

}