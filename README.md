# domis86/TranslatorBundle
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/1b5d278f-6fd5-4cc0-a81b-6223bad63fc4/small.png)](https://insight.sensiolabs.com/projects/1b5d278f-6fd5-4cc0-a81b-6223bad63fc4)
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

In this example `hello` is translated to `Hallo` when language is ![german](https://github.com/domis86/TranslatorBundle/raw/master/Resources/doc/flags/de.png)german, but when language is ![french](https://github.com/domis86/TranslatorBundle/raw/master/Resources/doc/flags/fr.png)french then it is translated to `Bonjour` (which resides somewhere in messages.fr.yml). Hit `( Click to edit )` above `Bonjour` to change it.


You can also browse `[your_domain.com]/app_dev.php/domis86translator/backend` to edit all translations used in your application.

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

Add the `domis86/translator-bundle` package to your `require` section in the `composer.json` file:
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
// app/AppKernel.php
public function registerBundles()
{
    $bundles = array(
        // ...
        new Domis86\TranslatorBundle\Domis86TranslatorBundle(),
        // ...
    );
    ...
}
```

Enable this bundle and configure `managed_locales` in your `config.yml`:
``` yaml
# config.yml
domis86_translator:
    is_enabled: true
    managed_locales: [en, fr, de]
```

Enable Dialog for `dev` environment in your `config_dev.yml`:
``` yaml
# config_dev.yml
domis86_translator:
    is_web_debug_dialog_enabled: true
```

Add routes in your `routing_dev.yml`:
``` yaml
# routing_dev.yml
domis86_translator_routing:
    resource: "@Domis86TranslatorBundle/Resources/config/routing.yml"
    prefix:   /domis86translator
```

Update your database:
``` console
php app/console doctrine:schema:update --force
```

Install assets
``` console
php app/console assets:install
```

#### Optional config:

If your web server's DocumentRoot points to some other dir than symfony's `/web` dir then you can change `domis86_translator.assets_base_path` accordingly (default is `/bundles/domis86translator/`).
Assuming your app.php url is `http://localhost/uglydirectory/web/app.php` then you should do:

``` yaml
domis86_translator:
    managed_locales: [en, fr, de]
    assets_base_path: /uglydirectory/web/bundles/domis86translator/
```

## Used libraries:

* [jQuery](http://jquery.com/)
* [jQuery-ui](http://jqueryui.com/)
* [DataTables](http://datatables.net/)
* [Jeditable](http://www.appelsiini.net/projects/jeditable)
* [yepnope.js](http://yepnopejs.com/)

[![Latest Stable Version](https://poser.pugx.org/domis86/translator-bundle/v/stable.png)](https://packagist.org/packages/domis86/translator-bundle) [![Total Downloads](https://poser.pugx.org/domis86/translator-bundle/downloads.png)](https://packagist.org/packages/domis86/translator-bundle) [![Latest Unstable Version](https://poser.pugx.org/domis86/translator-bundle/v/unstable.png)](https://packagist.org/packages/domis86/translator-bundle) [![License](https://poser.pugx.org/domis86/translator-bundle/license.png)](https://packagist.org/packages/domis86/translator-bundle)
