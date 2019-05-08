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
        $entity        = $event->getSubject();
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
