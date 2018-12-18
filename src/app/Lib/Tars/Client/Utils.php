<?php
namespace App\Lib\Tars\Client;

class Utils
{
    public static $preEnums;
    public static $preStructs;

    public static $wholeTypeMap = array(
        'bool'           => '\TARS::BOOL',
        'boolean'        => '\TARS::BOOL',
        'byte'           => '\TARS::CHAR',
        'char'           => '\TARS::CHAR',
        'unsigned byte'  => '\TARS::UINT8',
        'unsigned char'  => '\TARS::UINT8',
        'short'          => '\TARS::SHORT',
        'unsigned short' => '\TARS::UINT16',
        'int'            => '\TARS::INT32',
        'unsigned int'   => '\TARS::UINT32',
        'long'           => '\TARS::INT64',
        'float'          => '\TARS::FLOAT',
        'double'         => '\TARS::DOUBLE',
        'string'         => '\TARS::STRING',
        'vector'         => 'new \TARS_Vector',
        'map'            => 'new \TARS_Map',
    );

    public static $typeMap = array(
        'bool'           => '\TARS::BOOL',
        'boolean'        => '\TARS::BOOL',
        'byte'           => '\TARS::CHAR',
        'char'           => '\TARS::CHAR',
        'unsigned byte'  => '\TARS::UINT8',
        'unsigned char'  => '\TARS::UINT8',
        'short'          => '\TARS::SHORT',
        'unsigned short' => '\TARS::UINT16',
        'int'            => '\TARS::INT32',
        'unsigned int'   => '\TARS::UINT32',
        'long'           => '\TARS::INT64',
        'float'          => '\TARS::FLOAT',
        'double'         => '\TARS::DOUBLE',
        'string'         => '\TARS::STRING',
        'vector'         => '\TARS::VECTOR',
        'map'            => '\TARS::MAP',
        'enum'           => '\TARS::UINT8', // 应该不会出现
        'struct'         => '\TARS::STRUCT', // 应该不会出现
    );

    public static function getPackMethods($type)
    {
        $packMethods = [
            'bool'           => 'putBool',
            'boolean'        => 'putBool',
            'byte'           => 'putChar',
            'char'           => 'putChar',
            'unsigned byte'  => 'putUInt8',
            'unsigned char'  => 'putUInt8',
            'short'          => 'putShort',
            'unsigned short' => 'putUInt16',
            'int'            => 'putInt32',
            'unsigned int'   => 'putUInt32',
            'long'           => 'putInt64',
            'float'          => 'putFloat',
            'double'         => 'putDouble',
            'string'         => 'putString',
            'enum'           => 'putUInt8',
            'map'            => 'putMap',
            'vector'         => 'putVector',
            'Bool'           => 'putBool',
            'Boolean'        => 'putBool',
            'Byte'           => 'putChar',
            'Char'           => 'putChar',
            'Unsigned byte'  => 'putUInt8',
            'Unsigned char'  => 'putUInt8',
            'Short'          => 'putShort',
            'Unsigned short' => 'putUInt16',
            'Int'            => 'putInt32',
            'Unsigned int'   => 'putUInt32',
            'Long'           => 'putInt64',
            'Float'          => 'putFloat',
            'Double'         => 'putDouble',
            'String'         => 'putString',
            'Enum'           => 'putUInt8',
            'Map'            => 'putMap',
            'Vector'         => 'putVector',
        ];

        if (isset($packMethods[$type])) {
            return $packMethods[$type];
        } else {
            return 'putStruct';
        }
    }

    public static function getUnpackMethods($type)
    {
        $unpackMethods = [
            'bool'           => 'getBool',
            'boolean'        => 'getBool',
            'byte'           => 'getChar',
            'char'           => 'getChar',
            'unsigned byte'  => 'getUInt8',
            'unsigned char'  => 'getUInt8',
            'short'          => 'getShort',
            'unsigned short' => 'getUInt16',
            'int'            => 'getInt32',
            'unsigned int'   => 'getUInt32',
            'long'           => 'getInt64',
            'float'          => 'getFloat',
            'double'         => 'getDouble',
            'string'         => 'getString',
            'enum'           => 'getUInt8',
            'map'            => 'getMap',
            'vector'         => 'getVector',
            'Bool'           => 'getBool',
            'Boolean'        => 'getBool',
            'Byte'           => 'getChar',
            'Char'           => 'getChar',
            'Unsigned byte'  => 'getUInt8',
            'Unsigned char'  => 'getUInt8',
            'Short'          => 'getShort',
            'Unsigned short' => 'getUInt16',
            'Int'            => 'getInt32',
            'Unsigned int'   => 'getUInt32',
            'Long'           => 'getInt64',
            'Float'          => 'getFloat',
            'Double'         => 'getDouble',
            'String'         => 'getString',
            'Enum'           => 'getUInt8',
            'Map'            => 'getMap',
            'Vector'         => 'getVector',
        ];

        if (isset($unpackMethods[strtolower($type)])) {
            return $unpackMethods[strtolower($type)];
        } else {
            return 'getStruct';
        }
    }

    /**
     * @param $char
     *
     * @return int
     *             判断是不是tag
     */
    public static function isTag($word)
    {
        if (!is_numeric($word)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @param $word
     *
     * @return bool
     *              判断收集到的word是不是
     */
    public static function isRequireType($word)
    {
        return in_array(strtolower($word), ['require', 'optional']);
    }

    public static function isBasicType($word)
    {
        $basicTypes = [
            'bool', 'boolean', 'byte', 'char', 'unsigned byte', 'unsigned char', 'short', 'unsigned short',
            'int', 'unsigned int', 'long', 'float', 'double', 'string', 'void',
        ];

        return in_array(strtolower($word), $basicTypes);
    }

    public static function isEnum($word, $preEnums = [])
    {
        return strtolower($word) == 'enum';
    }

    public static function isMap($word)
    {
        return strtolower($word) == 'map';
    }

    public static function isStruct($word, $preStructs = [])
    {
        return strtolower($word) == 'struct';
    }

    public static function isVector($word)
    {
        return strtolower($word) == 'vector';
    }

    public static function isSpace($char)
    {
        if ($char == ' ' || $char == "\t") {
            return true;
        } else {
            return false;
        }
    }

    public static function paramTypeMap($paramType)
    {
        if (self::isBasicType($paramType) || self::isMap($paramType) || self::isVector($paramType)) {
            return '';
        } else {
            return $paramType;
        }
    }

    public static function getRealType($type)
    {
        if (isset(self::$typeMap[strtolower($type)])) {
            return self::$typeMap[strtolower($type)];
        } else {
            return '\TARS::STRUCT';
        }
    }

    public static function inIdentifier($char)
    {
        return ($char >= 'a' & $char <= 'z') |
        ($char >= 'A' & $char <= 'Z') |
        ($char >= '0' & $char <= '9') |
            ($char == '_');
    }

    public static function abnormalExit($level, $msg)
    {
        echo "[$level]$msg" . "\n";
        exit;
    }

    public static function pregMatchByName($name, $line)
    {
        // 处理第一行,正则匹配出classname
        $Tokens = preg_split("/$name/", $line);

        $mathName = $Tokens[1];
        $mathName = trim($mathName, " \r\0\x0B\t\n{");

        preg_match('/[a-zA-Z][0-9a-zA-Z]/', $mathName, $matches);
        if (empty($matches)) {
            //Utils::abnormalExit('error',$name.'名称有误'.$line);
        }

        return $mathName;
    }

    public static function isReturn($char)
    {
        if ($char == "\n" || $char == '\r' || bin2hex($char) == '0a' || bin2hex($char) == '0b' ||
            bin2hex($char) == '0c' || bin2hex($char) == '0d') {
            return true;
        } else {
            return false;
        }
    }
}
