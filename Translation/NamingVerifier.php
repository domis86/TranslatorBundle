<?php

namespace Domis86\TranslatorBundle\Translation;

/**
 * NamingVerifier
 *
 * This class makes sure that names of Messages and Domains and Locales are correct.
 * Throws exceptions when it encounters invalid naming. For example "abc/d." is invalid Domain name.
 *
 * @author Dominik Frankowicz <domis86@gmail.com>
 */
class NamingVerifier
{
    const DEFAULT_DOMAIN_NAME = 'messages';

    /** @var array */
    private $managedLocales;

    public function __construct(array $managedLocales)
    {
        $this->managedLocales = $managedLocales;
    }

    /**
     * @param string $localeCandidate
     * @return string|bool Locale(string) or false(bool) if given locale is not in %domis86_translator.managed_locales%
     */
    public function determineLocale($localeCandidate)
    {
        if (strlen($localeCandidate) < 1) {
            // return default locale
            return $this->managedLocales[0];
        }
        if (!$this->verifyLocale($localeCandidate)) {
            // we don't manage this locale
            return false;
        }
        return $localeCandidate;
    }

    /**
     * @param string $domainNameCandidate
     * @return string Domain name
     */
    public function determineDomainName($domainNameCandidate)
    {
        if (strlen(trim($domainNameCandidate)) < 1) {
            return self::DEFAULT_DOMAIN_NAME;
        }
        return $domainNameCandidate;
    }

    /**
     * @param string $uncleanTranslation
     * @return string|bool Clean translation or false(bool)
     */
    public function determineTranslation($uncleanTranslation)
    {
        $translation = trim($uncleanTranslation);
        if (strlen($translation) < 1) {
            return false;
        }
        return $translation;
    }

    /**
     * @param $messageName
     * @param $domainName
     * @return bool True when valid
     */
    public function verifyNames($messageName, $domainName)
    {
        $this->verifyDomainName($domainName);
        $this->verifyMessageName($messageName);
        return true;
    }

    /**
     * @param string $locale
     * @return bool true if given locale is in %domis86_translator.managed_locales%
     */
    public function verifyLocale($locale)
    {
        if (!in_array($locale, $this->managedLocales)) {
            // we don't manage this locale
            return false;
        }
        return true;
    }

    /**
     * @param $domainName
     * @throws \Exception When invalid
     */
    private function verifyDomainName($domainName)
    {
        $allowedInside              = ' _\\+\\-\\=\\!\\#';
        $allowedInsideHumanReadable = 'letters, ciphers, spaces or _+-=!#';
        $this->verifyName('Domain', $domainName, $allowedInside, $allowedInsideHumanReadable);
    }

    /**
     * @param $messageName
     * @throws \Exception When invalid
     */
    private function verifyMessageName($messageName)
    {
        if (strlen(trim($messageName)) < 1) {
            throw new \InvalidArgumentException("Message Name cannot be empty\n"
                . "Tested Message Name: '$messageName'"
            );
        }
    }

    /**
     * @param string $type
     * @param string $name
     * @param string $allowedInside
     * @param string $allowedInsideHumanReadable
     * @throws \InvalidArgumentException
     */
    private function verifyName($type, $name, $allowedInside, $allowedInsideHumanReadable)
    {
        $alphanumeric = 'a-z0-9';
        $alphanumericHumanReadable = 'letter or cipher';

        $allowedInside .= $alphanumeric.$allowedInside;

        $firstChar = substr($name, 0, 1);
        if (!preg_match('/^[' . $alphanumeric . ']{1}$/i', $firstChar)) {
            throw new \InvalidArgumentException("First character of translation $type Name must be $alphanumericHumanReadable\n"
                . "Tested $type Name: '$name'"
            );
        }

        $lastChar = substr($name, -1);
        if (!preg_match('/^[' . $alphanumeric . ']{1}$/i', $lastChar)) {
            throw new \InvalidArgumentException("Last character of translation $type Name must be $alphanumericHumanReadable\n"
                . "Tested $type Name: '$name'"
            );
        }

        if (!preg_match('/^[' . $allowedInside . ']*$/i', $name)) {
            throw new \InvalidArgumentException("$type Name may contain only following characters: $allowedInsideHumanReadable\n"
                . "Tested $type Name: '$name'"
            );
        }
    }

}
