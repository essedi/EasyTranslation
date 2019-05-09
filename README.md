<h1 align="center"><a href="http://www.essedi.es"><img src="http://www.essedi.es/wp-content/uploads/2017/12/cropped-newsletter-logo-essedi.png" alt="Essedi"></a></h1>

# EasyTranslator
***

Installation
============

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:
Adds repo to composer repository
```php
"repositories": [
        {
            "type": "vcs",
            "url": "git@github.com:essedi/EasyTranslation.git"
        }
    ],
```
```console
$ composer require Essedi\EasyTranslator
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Once, you have registered the bundle, you need to install CKEditor:
```console
$ php bin/console ckeditor:install

```
Once, you have downloaded CKEditor, you need to install it in the web directory.
```console
$ php bin/console assets:install public
```

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
<?php
////config/bundles.php
return [
...
    Essedi\EasyTranslation\EssediEasyTranslationBundle::class => ['all' => true],
];

```
### Step 3: Copy Configs

#### Services
```php
<?php
////App/config/services.php
services:
    Essedi\EasyTranslation\EventSubscriber\TranslatableSubscriber:
        arguments: ["@annotation_reader"]
        tags:
            - { name: doctrine.event_listener, event: postLoad} 

            
    Essedi\EasyTranslation\Annotation\Driver\AnnotationDriver:
        class: Essedi\EasyTranslation\Annotation\Driver\AnnotationDriver
        tags: 
            - {name: kernel.event_listener, event: kernel.controller, method: onKernelController}
        arguments: ["@annotation_reader"]
```

#### Twing config
Only if want integrate with EasyAdmin
```php
<?php
////App/config/packages/twing.php
twig:
    paths: 
        "%kernel.root_dir%/../vendor/Essedi/EasyTranslation/src/Resources": Essedi
    debug: '%kernel.debug%'
    strict_variables: '%kernel.debug%'
    form_themes:
        - '@Essedi/Form/translation.html.twig'

```
### Step 4: Set Entity for translate

#### add uses
```php
<?php
////App/Entity/YourEntity.php

```

You need to set the Entity like translatable 
```php
use Essedi\EasyTranslation\Entity\Translation;
use Essedi\EasyTranslation\Annotation as Essedi;
use Essedi\EasyTranslation\Annotation\Translatable;
use Essedi\EasyTranslation\Annotation\TranslateMe;
```

#### add Annotations 
class annotation
```php
<?php
/*
 * @Essedi\Translatable
 */
```
property annotation
```php
<?php
/*
 * @Essedi\TranslateMe(type="text")
 */
```
Attributes:
   * type:
        * text : default value
        * textarea
        * checkbox
        * number
        * date
        * email
        * password
        * color
        * ckeditor
         
#### extend class 
```php
<?php
	class YourEntity extends Translation
```

### Step 5: Set EasyAdminSubscriber
Only if want integrate with EasyAdmin

```php
<?php
////App/EventSubscriber/EasyAdminSubscriber.php
namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Essedi\EasyTranslation\Entity\Translation;
use EasyCorp\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
use Doctrine\Common\Annotations\Reader;

class EasyAdminSubscriber implements EventSubscriberInterface
{

    /**
     *
     * @var Reader 
     */
    private $annotationReader;

    public function __construct(Reader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }

    public static function getSubscribedEvents()
    {
        return array(
            EasyAdminEvents::PRE_PERSIST => array('editTranslatable'),
            EasyAdminEvents::PRE_UPDATE => array('editTranslatable')
        );
    }

    public function editTranslatable(GenericEvent $event)
    {

        $args = $event->getArguments();
        $requestParams = $args["request"]->request->all();
        $entity = $event->getSubject();
        if ($entity instanceof Translation)
        {
            if (isset($requestParams["translations"]))
            {
                //get new locale 
                foreach ($requestParams as $requestParam)
                {
                    if (isset($requestParam["translations"]["newlocale"]))
                    {
                        $newLocale = $requestParam["translations"]["newlocale"];
                        break;
                    }
                }

                if ($newLocale)
                {
                    $requestParams["translations"][$newLocale] = [];
                    foreach ($requestParams["translations"]['new'] as $field => $value)
                    {
                        if ($field != 'locale')
                        {
                            $requestParams["translations"][$newLocale][$field] = $value;
                        }
                    }
                }
                unset($requestParams["translations"]['new']);
                $entity->setTranslations($requestParams["translations"]);
            }
        }
    }

}

```

### Step 6: Set EasyAdmin form fields translatables
Only if want integrate with EasyAdmin

You can set Translatable form on you EasyAdmin adding this code on your entity fields

```
		- property: translations
                  label: 'translatables'
                  type: 'Essedi\EasyTranslation\Resources\Form\Type\TranslationType'
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

