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
            $this->setTranslations($requestParams, $entity);
            foreach ($requestParams as $data)
            {
                $this->setTranslations($data, $entity);
            }
        }
    }

    protected function setTranslations($data, $entity)
    {
        if (isset($data["translations"]))
        {
            $translations = [];
            $newlocale    = null;
            foreach ($data["translations"] as $field => $value)
            {
                if (!is_array($value))
                {
                    if ($field !== "newlocale")
                    {
                        $fieldData   = explode('-', $field);
                        $fieldLocale = $fieldData[0];

                        $fieldName  = $fieldData[1];
                        $fieldType  = $fieldData[2];
                        $fieldValue = $value;
                        if (!isset($translations[$fieldLocale]))
                        {
                            $translations[$fieldLocale] = [];
                        }
                        $translations[$fieldLocale][$fieldName] = $fieldValue;
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

    function setLocaleKey($translations, $locale)
    {
        if (!array_key_exists("new", $translations))
        {
            return $translations;
        }
        $keys                             = array_keys($translations);
        $keys[array_search("new", $keys)] = $locale;
        return array_combine($keys, $translations);
    }

}
