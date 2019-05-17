<?php

namespace Essedi\EasyTranslation\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Essedi\EasyTranslation\Entity\Translation;
use EasyCorp\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
use Doctrine\Common\Annotations\Reader;

/**
 * Description of EasyAdminSubscriber
 *
 * @author dani
 */
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
            EasyAdminEvents::PRE_UPDATE  => array('editTranslatable')
        );
    }

    public function editTranslatable(GenericEvent $event)
    {

        $args          = $event->getArguments();
        $requestParams = $args["request"]->request->all();

        $entity = $event->getSubject();
        if ($entity instanceof Translation)
        {
            $this->checkForm($requestParams, $entity);
        }
    }

    protected function checkForm($data, $entity)
    {
        $data = array_values($data)[1]; //ignoring referer
        if (is_array($data) && isset($data["translations"]) && is_object($entity) && $entity instanceof Translation)
        {
            //translate this entity
            $this->setTranslations($data["translations"], $entity);
            //check for subentities 
            $this->forTranslations($data, $entity);
        }
        else
        {
            throw new HttpException(500, 'Fail to cacth form');
        }
    }

    protected function forTranslations($data, $entity)
    {
        foreach ($data as $key => $result)
        {
            //search for relationships
            if (is_array($result))
            {
                if (is_object($entity))
                {
                    $gettername = 'get' . ucfirst($key);
                    if (method_exists($entity, $gettername))
                    {
                        $newEntity = $entity->$gettername();
                    }
                    else
                    {
                        //try if is Collection
                        if ($entity instanceof PersistentCollection)
                        {
                            $newEntity = $entity->get($key);
                        }
                        else
                        {
                            $newEntity = $entity;
                        }
                    }

                    if (is_object($newEntity) && $newEntity instanceof Translation && isset($result["translations"]))
                    {
                        $this->setTranslations($result["translations"], $newEntity);
                    }
                    $this->forTranslations($result, $newEntity);
                }
            }
            else
            {
                //is a common property (string, number ...)
            }
        }
    }

    protected function setTranslations($data, $entity)
    {
        if ($this->validateTranslationParams($data, $entity))
        {
            $translations = [];
            $newlocale    = null;
            foreach ($data as $field => $value)
            {
                if (!is_array($value))
                {
                    if ($field !== "newlocale")
                    {
                        if (strpos($field, "-") !== false)
                        {
                            $fieldData   = explode('-', $field);
                            $fieldLocale = $fieldData[0];

                            $fieldName  = $fieldData[1];
//                        $fieldType  = $fieldData[2]; Unnused type
                            $fieldValue = $value;
                            if (!isset($translations[$fieldLocale]))
                            {
                                $translations[$fieldLocale] = [];
                            }
                            $translations[$fieldLocale][$fieldName] = $fieldValue;
                        }
                    }
                }
                else
                {
                    if (!isset($translations[$field]))
                    {
                        $translations[$field] = [];
                    }
                    $translations[$field] = $value;
                }

                if ($field == "newlocale")
                {
                    $newlocale = $value;
                }
            }
            if ($newlocale)
            {
                $translations = $this->setLocaleKey($translations, $newlocale);
            }
            $entity->setTranslations($translations);
        }
    }

    /**
     * Replace key 'new' on translations array by $locale value
     * @param array $translations
     * @param string $locale
     * @return array
     */
    protected function setLocaleKey($translations, $locale)
    {
        if (!array_key_exists("new", $translations))
        {
            return $translations;
        }
        $keys                             = array_keys($translations);
        $keys[array_search("new", $keys)] = $locale;
        return array_combine($keys, $translations);
    }

    protected function validateTranslationParams($data, $entity, $throw = true): bool
    {
        if (is_array($data) && is_object($entity) && $entity instanceof Translation)
        {
            return true;
        }
        $ms = '';
        if (!is_array($data))
        {
            $ms .= "data is  a " . gettype($data) . "(needs array)";
        }
        if (!is_object($entity))
        {
            $ms .= "entity was not valid " . gettype($entity);
        }
        if (!($entity instanceof Translation))
        {
            $ms .= "entity is not extended Translation " . get_class($entity);
        }
        if ($throw)
        {
            throw new HttpException(500, 'Invalid translation parameters: ' . $ms);
        }
        return false;
    }

}
