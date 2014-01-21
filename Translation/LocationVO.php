<?php
namespace Domis86\TranslatorBundle\Translation;

/**
 * LocationVO
 *
 * Value object, describes location in project
 *
 * @author Dominik Frankowicz <domis86@gmail.com>
 */
class LocationVO extends BaseValueObject
{
    /**
     * @param string $bundleName
     * @param string $controllerName
     * @param string $actionName
     */
    public function __construct($bundleName, $controllerName, $actionName)
    {
        parent::__construct(get_defined_vars());
    }

    /**
     * @return string
     */
    public function getBundleName()
    {
        return parent::get('bundleName');
    }

    /**
     * @return string
     */
    public function getControllerName()
    {
        return parent::get('controllerName');
    }

    /**
     * @return string
     */
    public function getActionName()
    {
        return parent::get('actionName');
    }
}
