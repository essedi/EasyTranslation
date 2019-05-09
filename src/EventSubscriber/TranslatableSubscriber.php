<?php

namespace Essedi\EasyTranslation\EventSubscriber;

use Essedi\EasyTranslation\Entity\Translation;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\Common\Annotations\Reader;
use Symfony\Component\Form\Exception\InvalidConfigurationException;
use Essedi\EasyTranslation\Annotation\Translatable;
use Doctrine\Common\Util\ClassUtils;

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
        $entity        = $args->getEntity();
        $entityManager = $args->getEntityManager();

        $class           = ClassUtils::getClass($entity);
        $reflectedEntity = new \ReflectionClass($class);
        $res             = $this->annotationReader->getClassAnnotation($reflectedEntity, Translatable::class);
        //the clas has been marked as Translatable
        if ($res && $entity instanceof Translation)
        {
            //getting all class translations
            $mappedTranslations = $entity->getTranslations();
            //the container locale
            $locales = array_keys($mappedTranslations);
            $locales = count($locales) ? $locales : ["es"];

            $classProperties = $entity->getTranslatableAnnotations();

            $mappedTranslationsUpdated = false;
            foreach ($locales as $currentLocale){
                foreach ($classProperties as $currentProperty => $annotation)
                {
                    $setterMethod = $reflectedEntity->getMethod("set" . ucfirst($currentProperty));
                    if (isset($mappedTranslations[$currentLocale]) && isset($mappedTranslations[$currentLocale][$currentProperty]))
                    {
                        $fieldTransEntity = $mappedTranslations[$currentLocale][$currentProperty];
                        if ($fieldTransEntity->getFieldType() != $annotation->type){
                            $fieldTransEntity->setFieldType($annotation->type);
                            $mappedTranslationsUpdated = true;
                        }
                        $setterMethod->invoke($entity, $fieldTransEntity->getFieldValue());
                    } else
                    {
                        if (!isset($mappedTranslations[$currentLocale]))
                        {
                            $mappedTranslations[$currentLocale] = [];
                            $mappedTranslationsUpdated          = true;
                        }
                        if (!isset($mappedTranslations[$currentLocale][$currentProperty]))
                        {
                            $mappedTranslations[$currentLocale][$currentProperty] = "";
                            $mappedTranslationsUpdated                            = true;
                        }
                        $setterMethod->invoke($entity, "");
                    }
                }
            }
            if ($mappedTranslationsUpdated)
            {
                $entity->setTranslations($mappedTranslations, $args);
                $entityManager->persist($entity);
                $entityManager->flush();
            }
        }
        else if ($res)
        {
            throw new InvalidConfigurationException("The $class is annotated as Translatable but does not extends the Translation abstract class");
        }
    }

}
