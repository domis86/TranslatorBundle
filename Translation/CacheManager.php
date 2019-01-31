<?php
namespace Domis86\TranslatorBundle\Translation;

use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

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
        $this->debug = $debug;
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
        $messageCollection->import(include $filename);
        return $messageCollection;
    }

    /**
     * @param LocationVO $location
     * @param MessageCollection $messageCollection
     */
    public function saveMessageCollectionForLocation(LocationVO $location, MessageCollection $messageCollection)
    {
        if (!$messageCollection->isModified()) return;

        $filename = $this->buildCacheFilename($location);
        $cache = new ConfigCache($filename, $this->debug);
        $content = '<?php
return ' . var_export($messageCollection->export(), true) . ';

';
        $cache->write($content);
        $messageCollection->setModified(false);
    }

    /**
     * @return array List of deleted files
     */
    public function clearCache()
    {
        $dirs = array();
        $dirs[] = $this->cacheDir.'/../../*/domis86translator'; // for all environments

        $finder = array();
        try {
            $finder = Finder::create()
                ->files()
                ->files()
                ->in($dirs)
        ;
        } catch (\InvalidArgumentException $e) {
            // cache dir was empty
        }

        $deletedFiles = [];
        /** @var SplFileInfo[] $finder */
        foreach ($finder as $file) {
            $deletedFiles[] = $file->getRealpath();
            unlink($file->getRealpath());
        }
        return $deletedFiles;
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
