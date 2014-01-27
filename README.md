# domis86/TranslatorBundle

## What is it ?

Symfony2 bundle which helps in translation.

A Symfony2 bundle which helps in editing of translations without need for editing the translations files.
Edit can be performed via symfony WebDebugToolbar (translations from current request) or backend admin interface (all translations).
Translations are stored in db and retrieved in a efficient way (+cached).

The Symfony2 WebDebugToolbar shows number of translations used in current request:
![Domis86Translator in WebDebugToolbar](https://github.com/domis86/TranslatorBundle/raw/master/Resources/doc/domis86translator_in_web_debug_toolbar.png)

If you click on it a Edit Dialog will appear where you can edit translations used in current request:
![Domis86Translator Edit Dialog](https://github.com/domis86/TranslatorBundle/raw/master/Resources/doc/domis86translator_edit_dialog.png)

![Tranlation missing](https://github.com/domis86/TranslatorBundle/raw/master/Resources/doc/translation_missing.png) - indicates that translation for this language is missing - click and add it!

![Tranlation missing](https://github.com/domis86/TranslatorBundle/raw/master/Resources/doc/translation_from_file.png) - Black text is current translation stored in db. Blue text means that it is translation loaded from file - via default Symfony2 Translator service (messages.en.yml etc). It will be used if there is no translation in DB.

In this example `hello` is translated to `Hallo` when language is ![german](https://raw2.github.com/domis86/TranslatorBundle/master/Resources/public/images/flags/de.png)german, but when language is ![french](https://raw2.github.com/domis86/TranslatorBundle/master/Resources/public/images/flags/fr.png)french then it is translated to `Bonjour` (which resides somewhere in messages.fr.yml). Hit `( Click to edit )` above `Bonjour` to change it.


You can also browse `[your_domain.com]/trans/backend` to edit all translations used in your application.

## Features

* edit/add translations without need of messing with translations files
* integration with Symfony2 WebDebugToolbar (dev env)
    * info how many translated/used messages was in request
* robust Edit Dialog (js) activated by clicking on WebDebugToolbar
    * search/sort your translations by name, domain name, content
    * just click on translation, edit it in place and save
    * click on `Help` button in Dialog for more info
* manage all translations from you application in Backend (same features as Edit Dialog)
* translations are stored in DB, and cached per Action


## Installation

Add the `domis86/translator-bundle` package to your `require` section in the `composer.json` file.

``` json
    ...
    "require": {
        ...
        "domis86/translator-bundle": "dev-master"
    },
    ...
```

Add the Domis86TranslatorBundle to your AppKernel:

``` php
public function registerBundles()
{
    $bundles = array(
        // ...
        new domis86\TranslatorBundle\Domis86TranslatorBundle(),
        // ...
    );
    ...
}
```

Configure the `managed_locales` in your `config.yml`:

``` yaml
domis86_translator:
    managed_locales: [en, fr, de]
```

Add routes in your `routing.yml`:

``` yaml
domis86_translator_routing:
    resource: "@Domis86TranslatorBundle/Resources/config/routing.yml"
    prefix:   /trans
```


## Used libraries:

* [jQuery](http://jquery.com/)
* [jQuery-ui](http://jqueryui.com/)
* [DataTables](http://datatables.net/)
* [Jeditable](http://www.appelsiini.net/projects/jeditable)
* [yepnope.js](http://yepnopejs.com/)