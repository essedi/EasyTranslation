<?php

namespace Essedi\EasyTranslation\Annotation\Driver;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent;//Use essential kernel component
use SomeNamespace\SomeBundle\Security\Permission;//In this class I check correspondence permission to user
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
class AnnotationDriver{
    private $reader;
    public function __construct($reader)
    {
        $this->reader = $reader;//get annotations reader
    }
    /**
    * This event will fire during any controller call
    */
    public function onKernelController(FilterControllerEvent $event)
    {
        if (!is_array($controller = $event->getController())) { //return if no controller
            return;
        }
        $object = new \ReflectionObject($controller[0]);// get controller
        $method = $object->getMethod($controller[1]);// get method
        foreach ($this->reader->getMethodAnnotations($method) as $configuration) { //Start of annotations reading
            if(isset($configuration->perm)){//Found our annotation
                $perm = new Permission($controller[0]->get('doctrine.odm.mongodb.document_manager'));
                $userName = $controller[0]->get('security.context')->getToken()->getUser()->getUserName();
                if(!$perm->isAccess($userName,$configuration->perm)){
                           //if any throw 403
                           throw new AccessDeniedHttpException();
                }
             }
         }
    }
}

