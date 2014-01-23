# domis86/TranslatorBundle

Symfony2 bundle which helps in translation.

A Symfony2 bundle which helps in editing of translations without need for editing the translations files.
Edit can be performed via symfony WebDebugPanel (translations from current request) or backend admin interface (all translations).
Translations are stored in db and retrieved in a efficient way (+cached).


Symfony2 Panel
-----------------------------

The Symfony2 WebDebugPanel shows number of translations used in current request:

![Symfony2 Panel](https://github.com/domis86/TranslatorBundle/raw/master/Resources/doc/web_debug_panel_1.png)

If you click on this you can edit all translations used in current request and edit them in dialog (jQuery-ui + dataTables).

![Symfony2 Edit Dialog](https://github.com/domis86/TranslatorBundle/raw/master/Resources/doc/web_debug_panel_2.png)
