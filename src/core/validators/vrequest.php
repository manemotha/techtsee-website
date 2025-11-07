<?php
declare (strict_types=1);


/**
 * @param array $inputData
 * @param array $requiredKeys
 * @throws Exception
 */
function validateRequiredKeys(array $inputData, array $requiredKeys) :void {

    // ENSURE: input data contains required keys
    foreach ($requiredKeys as $key) {
        if (!array_key_exists($key, $inputData)) {
            throw new Exception("missing required key: $key");
        }
    }
}
