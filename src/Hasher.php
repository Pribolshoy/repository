<?php

namespace pribolshoy\repository;

/**
 * Class Hasher
 *
 * Utility class for hashing multidimensional arrays.
 * Recursively sorts array keys in alphabetical order, serializes and hashes via MD5.
 *
 * @package pribolshoy\repository
 */
class Hasher
{
    /**
     * Hash a multidimensional array.
     *
     * Process:
     * 1. Recursively sort array keys in alphabetical order
     * 2. Serialize the sorted array (by default)
     * 3. Hash via MD5
     *
     * @param array $data Multidimensional array to hash
     * @param bool $serialize Whether to serialize before hashing (default: true)
     *
     * @return string MD5 hash of the sorted and serialized array
     */
    public static function hash(array $data, bool $serialize = true): string
    {
        $sorted = self::sortKeysRecursive($data);
        
        if ($serialize) {
            $serialized = serialize($sorted);
        } else {
            $serialized = json_encode($sorted, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        
        return md5($serialized);
    }
    
    /**
     * Recursively sort array keys in alphabetical order.
     *
     * @param array $array Array to sort
     *
     * @return array Array with sorted keys
     */
    protected static function sortKeysRecursive(array $array): array
    {
        // Sort keys alphabetically
        ksort($array, SORT_STRING);
        
        // Recursively sort nested arrays
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = self::sortKeysRecursive($value);
            }
        }
        
        return $array;
    }
    
    /**
     * Get sorted array without hashing.
     * Useful for debugging or when you need sorted array structure.
     *
     * @param array $data Multidimensional array to sort
     *
     * @return array Array with sorted keys
     */
    public static function sort(array $data): array
    {
        return self::sortKeysRecursive($data);
    }
}

