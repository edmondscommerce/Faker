<?php

namespace Faker\ORM\Doctrine;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;

class ColumnTypeGuesser
{
    protected $generator;

    /**
     * @param \Faker\Generator $generator
     */
    public function __construct(\Faker\Generator $generator)
    {
        $this->generator = $generator;
    }

    /**
     * @param ClassMetadata $class
     *
     * @return \Closure|null
     */
    public function guessFormat($fieldName, ClassMetadata $class)
    {
        $generator = $this->generator;
        $type      = $class->getTypeOfField($fieldName);
        switch ($type) {
            case 'boolean':
                return function () use ($generator) {
                    return $generator->boolean;
                };
            case 'decimal':
                $nbDigits = isset($class->fieldMappings[$fieldName]['precision']) ? $class->fieldMappings[$fieldName]['precision'] : 4;
                while ($nbDigits > 4) {
                    $max = pow(10, $nbDigits);
                    if ($max > mt_getrandmax()) {
                        $nbDigits--;
                        continue;
                    }
                    break;
                }

                return function () use ($generator, $nbDigits) {
                    return $generator->randomNumber($nbDigits) / 100;
                };
            case 'smallint':
                return function () {
                    return mt_rand(0, 65535);
                };
            case 'integer':
                return function () {
                    return mt_rand(0, intval('2147483647'));
                };
            case 'bigint':
                return function () {
                    return mt_rand(0, intval('18446744073709551615'));
                };
            case 'float':
                return function () {
                    return mt_rand(0, intval('4294967295')) / mt_rand(1, intval('4294967295'));
                };
            case 'string':
                $size = isset($class->fieldMappings[$fieldName]['length']) ? $class->fieldMappings[$fieldName]['length'] : 255;

                return function () use ($generator, $size) {
                    return $generator->text($size);
                };
            case 'text':
                return function () use ($generator) {
                    return $generator->text;
                };
            case 'datetime':
            case 'date':
            case 'time':
                return function () use ($generator) {
                    return $generator->datetime;
                };
            default:
                // no smart way to guess what the user expects here
                return null;
        }
    }
}
