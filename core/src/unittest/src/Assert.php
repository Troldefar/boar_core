<?php

namespace app\core\src\unittest\src;

use \app\core\src\unittest\assert\AssertSame;
use \app\core\src\unittest\assert\AssertIdentity;
use \app\core\src\unittest\assert\AssertEquality;
use \app\core\src\unittest\assert\AssertIterable;
use \app\core\src\unittest\assert\AssertObjects;
use \app\core\src\unittest\assert\AssertCardinality;
use \app\core\src\unittest\assert\AssertTypes;
use \app\core\src\unittest\assert\AssertStrings;
use \app\core\src\unittest\assert\AssertJSON;
use \app\core\src\unittest\assert\AssertXML;
use \app\core\src\unittest\assert\AssertFilesystem;

trait Assert {
    use AssertSame;
    use AssertIdentity;
    use AssertEquality;
    use AssertIterable;
    use AssertObjects;
    use AssertCardinality;
    use AssertTypes;
    use AssertStrings;
    use AssertJSON;
    use AssertXML;
    use AssertFilesystem;
}
