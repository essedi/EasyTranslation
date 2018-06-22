<?php

namespace Essedi\EasyTranslationBundle\EventSubscriber;

use Essedi\EasyTranslationBundle\Entity\Translation;
use Essedi\EasyTranslationBundle\Entity\PackageType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Doctrine\ORM\Event\LifecycleEventArgs;
use JMS\Serializer\EventDispatcher\PreSerializeEvent;
use Doctrine\Common\Annotations\Reader;
use Essedi\EasyTranslationBundle\Annotation\Translatable;
use Essedi\EasyTranslationBundle\Annotation\TranslateMe;
use Symfony\Component\Form\Exception\InvalidConfigurationException;

class TranslatableSubscriber implements EventSubscriberInterface
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
        return [
            'postLoad'
        ];
    }

    /*
     * Run when charge entity data from database
     */

    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();

        $class = get_class($entity);
        $reflectedEntity = new \ReflectionClass($class);
        $res = $this->annotationReader->getClassAnnotation($reflectedEntity, Translatable::class);
        //the clas has been marked as Translatable
        if ($res && $entity instanceof Translation)
        {
            //getting all class translations
            $mappedTranslations = $entity->getTranslations();
            //the container locale
            $currentLocale = "es";

            $classProperties = $entity->getTranslatableFields();

            $mappedTranslationsUpdated = false;
            foreach ($classProperties as $currentProperty)
            {

                $setterMethod = $reflectedEntity->getMethod("set" . ucfirst($currentProperty));
                if (isset($mappedTranslations[$currentLocale]) && isset($mappedTranslations[$currentLocale][$currentProperty]))
                {
                    $setterMethod->invoke($entity, $mappedTranslations[$currentLocale][$currentProperty]);
                } else
                {
                    if (!isset($mappedTranslations[$currentLocale]))
                    {
                        $mappedTranslations[$currentLocale] = [];
                        $mappedTranslationsUpdated = true;
                    }
                    if (!isset($mappedTranslations[$currentLocale][$currentProperty]))
                    {
                        $mappedTranslations[$currentLocale][$currentProperty] = "";
                        $mappedTranslationsUpdated = true;
                    }
                    $setterMethod->invoke($entity, "");
                }
            }

            if ($mappedTranslationsUpdated)
            {
                $entity->setTranslations($mappedTranslations, $args);
                $entityManager->persist($entity);
                $entityManager->flush();
            }
        } else if ($res)
        {
            throw new InvalidConfigurationException("The $class is annotated as Translatable but does not extends the Translation abstract class");
        }
    }

}