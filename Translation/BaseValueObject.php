<?php
namespace Domis86\TranslatorBundle\Translation;

/**
 * BaseValueObject
 *
 * Base class for value objects
 *
 * @author Dominik Frankowicz <domis86@gmail.com>
 */
class BaseValueObject
{
    /**
     * @var array
     */
    private $data = array();

    /**
     * @param array $constructorArguments
     */
    public function __construct($constructorArguments = array())
    {
        foreach ($constructorArguments as $argumentName => $value) {
            $this->setData($argumentName, $value);
        }
    }

    /**
     * Compares this another value object
     *
     * @param BaseValueObject $otherValueObject
     *
     * @return bool
     */
    public function isEqualTo(BaseValueObject $otherValueObject)
    {
        $otherData = $otherValueObject->export();
        if (count($this->data) != count($otherData)) {
            return false;
        }
        foreach ($this->data as $key => $val) {
            if (!isset($otherData[$key])) {
                return false;
            }
            if ($val != $otherData[$key]) {
                return false;
            }
        }
        return true;
    }

    /**
     * Returns all data
     *
     * @return array
     */
    public function export()
    {
        return $this->data;
    }

    /**
     * Data accessor
     *
     * @param string $functionName
     *
     * @throws \Exception
     * @return mixed
     */
    protected function get($name)
    {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }
        throw new \Exception(
            "Unknown property '$name' in value object '" . get_class($this) . "'."
        );
    }

    /**
     * Data setter - private, used only in constructor of this base class
     *
     * @param string $name
     * @param mixed $value
     */
    private function setData($name, $value)
    {
        $this->data[$name] = $value;
    }
}
