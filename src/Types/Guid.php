<?php

namespace TheRealMchaggis\DefinitionTypes\Types;

use TheRealMchaggis\DefinitionTypes\AbstractDataType;

class Guid extends AbstractDataType
{
    public string $type    = 'guid';
    public string $display = 'GUID';

    public function getFormated(string $value, string|array|null $definition = null, ?array $data = null): string
    {
        return self::toString($value);
    }

    public function convertToRaw(mixed $value, array $definition = []): mixed
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value)) {
            array_walk($value, static function (&$item): void {
                $item = self::toBinary($item);
            });

            return implode('|', $value);
        }

        return self::toBinary($value);
    }

    /**
     * The following functions are internal utility methods
     * the are artifacts of an external library, but it was easier to
     * keep them here.
     */


    /**
     * Generate a guid
     *
     * @param bool $asString
     *
     * @return false|string
     */
    public function generate($asString = true): false|string {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // version 4
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // variant bits
        $guid = strtolower(vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4)));
        if ($asString)
        {
            return $guid;
        }
        else
        {
            return self::toBinary($guid);
        }
    }

    /**
     * @param mixed $guid
     *
     * @return false|string
     */
    public static function toBinary(mixed $guid): false|string
    {
        if (self::isBinary($guid))
        {
            // This is already a binary
            return $guid;

        }
        elseif (is_string($guid))
        {

            try
            {
                return hex2bin(str_replace('-', '', $guid));
            }
            catch (Exception $e)
            {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Check if string is binary
     *
     * @param mixed $guid
     *
     * @return bool
     */
    public static function isBinary(mixed $guid): bool
    {
        if (!is_string($guid)) return false;
        return !empty($guid) && preg_match('~[^\x20-\x7E\t\r\n]~', $guid) > 0;
    }

    /**
     * Converts a binary GUID into string. If a string is passed, it will be re
     *
     * @param mixed $guid
     *
     * @return false|string
     */
    public static function toString(mixed $guid): false|string
    {
        // Check if binary
        if (!self::isBinary($guid))
        {
            // Check if valid GUID
            return $guid ?? false;

        }
        $string = unpack("H*", $guid);

        $guidArray = preg_replace(
            "/([0-9a-f]{8})([0-9a-f]{4})([0-9a-f]{4})([0-9a-f]{4})([0-9a-f]{12})/",
            "$1-$2-$3-$4-$5",
            $string
        );

        $guid = array_pop($guidArray);
        if (!empty($guid) && !preg_match('/^[0-]+$/', $guid))
        {
            return $guid;
        }

        return false;
    }

    /**
     * Check if a GUID is valid
     *
     * @param string $guid
     *
     * @return bool
     */
    public static function isValid(string|binary $guid): bool
    {
        if(self::typeOf($guid) === 'binary')
        {
            $guid = self::toString($guid);
        }
        if (!empty($guid) && preg_match('/^[a-f\d]{8}-(?:[a-f\d]{4}-){3}[a-f\d]{12}$/i', $guid))
        {
            return !preg_match('/^00000000-0000-/', $guid);
        }
        else
        {
            return false;
        }
    }

    /**
     * This function will determine the given GUID's type
     *
     * @param $guid
     *
     * @return false|string
     */
    public static function typeOf($guid): false|string
    {
        if (
            is_string($guid) &&
            preg_match('/^[0-9a-z-]+$/', $guid) &&
            strlen(str_replace('-', '', $guid)) === 32
        )
        {
            return 'string';
        }
        elseif (!empty($guid) && preg_match('~[^\x20-\x7E\t\r\n]~', $guid))
        {
            return 'binary';
        }

        return false;
    }

}
