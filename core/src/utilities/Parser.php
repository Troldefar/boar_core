<?php

namespace app\core\src\utilities;

use app\core\src\miscellaneous\CoreFunctions;

class Parser {
    
    public static function sqlComparsion($sqlInstruction, array $validComparisonOperators): array {
        $valueParts = explode(' ', $sqlInstruction ?? 'null');

        if (count($valueParts) > 1 && in_array((CoreFunctions::first($valueParts)->scalar), $validComparisonOperators)) 
            return [CoreFunctions::first($valueParts)->scalar, CoreFunctions::getIndex($valueParts, 1)->scalar];
        
        return ['=', $sqlInstruction];
    }

    public static function xml($response): bool|\SimpleXMLElement|null {
        try {
            $xml = simplexml_load_string($response);

            if ($xml === false) {
                app()->addSystemEvent('Invalid XML File');
                throw new \Exception('Failed to parse XML');
            }

            return $xml;
        } catch (\Exception $exception) {
            app()->getLogger()->log(get_called_class() . $exception->getMessage(), "\n");
            return null;
        }
    }
    

}