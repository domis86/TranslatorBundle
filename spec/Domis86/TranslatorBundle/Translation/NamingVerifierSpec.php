<?php

namespace spec\Domis86\TranslatorBundle\Translation;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Domis86\TranslatorBundle\Translation\NamingVerifier;

/**
 * NamingVerifierSpec
 * @mixin NamingVerifier
 * @author Dominik Frankowicz <domis86@gmail.com>
 */
class NamingVerifierSpec extends ObjectBehavior
{
    /** @var array */
    private $managedLocales = array();

    public function let()
    {
        $this->managedLocales = array('en', 'de');
        /** @noinspection PhpParamsInspection */
        $this->beConstructedWith($this->managedLocales);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Domis86\TranslatorBundle\Translation\NamingVerifier');
    }

    public function it_determines_locale()
    {
        $defaultLocale = $this->managedLocales[0];

        // on empty locale return default locale
        foreach (array(null, false, '') as $emptyLocale) {
            $this->determineLocale($emptyLocale)->shouldReturn($defaultLocale);
        }

        // on non managed locale return false
        foreach (array(
            'a',
            'xxx',
            'EN',
            'En',
            'en ',
            ' '
        ) as $nonManagedLocale) {
            $this->determineLocale($nonManagedLocale)->shouldReturn(false);
        }

        // on correct locale return locale
        foreach ($this->managedLocales as $locale) {
            $this->determineLocale($locale)->shouldReturn($locale);
        }
    }

    public function it_determines_domain_name()
    {
        $defaultDomainName = NamingVerifier::DEFAULT_DOMAIN_NAME;

        // on empty domainName return default domainName
        foreach (array(null, false, '', ' ', "  \n ") as $emptyValue) {
            $this->determineDomainName($emptyValue)->shouldReturn($defaultDomainName);
        }

        // in other case return given domain name
        foreach (array('some domain', ' some domain ', "some domain\n", ".:/!%^&") as $value) {
            $this->determineDomainName($value)->shouldReturn($value);
        }
    }

    public function it_verifies_domain_name()
    {
        $correctMessageName = 'simple_message';

        // on non-alphanumeric first and last letters throw exception
        foreach (array(
            '-domain', // invalid first letter
            ' domain', // invalid first letter
            'domain-', // invalid last letter
            'domain ', // invalid last letter
            '',        // empty
        ) as $domainName) {
            $this->shouldThrow('\Exception')->duringVerifyNames($correctMessageName, $domainName);
        }

        // on not allowed characters throw exception
        foreach (array(
            'a%a',
            'a[a',
            'a,a',
            "a\na",
            "a\r\na",
            'a.a'
        ) as $domainName) {
            $this->shouldThrow('\Exception')->duringVerifyNames($correctMessageName, $domainName);
        }

        // on correct names return true
        foreach (array(
            'messages',
            'a',
            "aa",
            'A',
            'a a',
            'a _+-=!#a',
            'A _+-=!#A'
        ) as $domainName) {
            $this->verifyNames($correctMessageName, $domainName)->shouldReturn(true);
        }
    }

    public function it_verifies_message_name()
    {
        $correctDomainName = 'simple_domain';

        // on empty name throw exception
        foreach (array(null, false, '', ' ', "  \n ") as $messageName) {
            $this->shouldThrow('\Exception')->duringVerifyNames($messageName, $correctDomainName);
        }

        // on correct names return true
        foreach (array(
                     'blabla',
                     'a',
                     "a.b.c.",
                     '!@@#$#%^*()+_}{:"?<>/,;][=-105231<>"\'',
                     ' a ',
                 ) as $messageName) {
            $this->verifyNames($messageName, $correctDomainName)->shouldReturn(true);
        }
    }

    public function it_returns_false_on_empty_translation()
    {
        foreach (array(null, false, '', ' ', "  \n ") as $emptyValue) {
            $this->determineTranslation($emptyValue)->shouldReturn(false);
        }
    }

    public function it_determines_translation()
    {
        foreach (array(
                     'Lorem Ipsum',
                     ' Lorem Ipsum ',
                     " Lorem Ipsum \r\n "
                 ) as $string) {
            $this->determineTranslation($string)->shouldReturn('Lorem Ipsum');
        }
    }

}
