<?php
namespace Domis86\TranslatorBundle\Translation;

use Symfony\Component\Config\ConfigCache;

/**
 * CacheManager
 *
 * @author Dominik Frankowicz <domis86@gmail.com>
 */
class CacheManager
{
    /** @var string */
    private $cacheDir;

    /** @var bool */
    private $debug;

    /**
     * @param string $cacheDir
     * @param bool $debug
     */
    public function __construct($cacheDir, $debug)
    {
        $this->cacheDir = $cacheDir;
        $this->debug = false; //$debug;
    }

    /**
     * @param LocationVO $location
     * @return MessageCollection|bool
     */
    public function loadMessageCollectionForLocation(LocationVO $location)
    {
        $filename = $this->buildCacheFilename($location);
        $cache = new ConfigCache($filename, $this->debug);
        if (!$cache->isFresh()) {
            return false;
        }
        $messageCollection = new MessageCollection();
        $messageCollection->import(include $cache);
        return $messageCollection;
    }

    /**
     * @param LocationVO $location
     * @param MessageCollection $messageCollection
     */
    public function saveMessageCollectionForLocation(LocationVO $location, MessageCollection $messageCollection)
    {
        if (!$messageCollection->isModified()) return;
        //my_log('handleMissingObjects - 2 - updating cache');
        $filename = $this->buildCacheFilename($location);
        $cache = new ConfigCache($filename, $this->debug);
        $content = '<?php
return ' . var_export($messageCollection->export(), true) . ';

';
        $cache->write($content);
        $messageCollection->setIsModified(false);
    }

    /**
     * @param LocationVO $location
     * @return string
     */
    private function buildCacheFilename(LocationVO $location)
    {
        $actionName = str_replace(":", "_", $location->getActionName());
        $filename = $this->cacheDir
        . '/' . $location->getBundleName()
        . '/' . $location->getControllerName()
        . '/' . $actionName
        . '_messages.php';
        $filename = str_replace("\\", "/", $filename);
        return $filename;
    }
}
