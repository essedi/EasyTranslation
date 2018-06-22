<h1 align="center"><a href="http://www.essedi.es"><img src="http://www.essedi.es/wp-content/uploads/2017/12/cropped-newsletter-logo-essedi.png" alt="Essedi"></a></h1>

# EasyTranslator
***

Install
-------
Installation
============

Applications that use Symfony Flex
----------------------------------

Open a command console, enter your project directory and execute:

```console
$ composer require Essedi\EasyTranslator
```

Applications that don't use Symfony Flex
----------------------------------------

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require Essedi\EasyTranslator
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new <vendor>\<bundle-name>\<bundle-long-name>(),
        );

        // ...
    }

    // ...
}
```]

You can set Translatable form on you EasyAdmin adding this code on your entity fields

```
	    - property: translations
                      label: 'translatables'
                      type: 'Essedi\EasyTranslator\Form\Type\TranslationType'
```


Credits
-------

Created by:
* Essedi It Consulting Slu
  info@essedi.es
  www.essedi.es
	* Dario Spitaleri
	  dario@essedi.es

	* Dani Lozano
	  daniel@essedi.es
	
	* Victor Dos Santos
	  victor@essedi.es

