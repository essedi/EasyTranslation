<?php

namespace Essedi\EasyTranslationBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\Common\Annotations\AnnotationReader;
use Essedi\EasyTranslationBundle\Annotation\Translatable;
use Essedi\EasyTranslationBundle\Annotation\TranslateMe;
use Essedi\EasyTranslationBundle\Entity\FieldTranslation;

/** @MappedSuperclass */
abstract class Translation
{

    protected $locale = 'es';

    /**
     * @var FieldTranslation[]
     * @ManyToMany(targetEntity="FieldTranslation", cascade={"persist", "remove"})
     */
    protected $translations;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
//        $this->locale = $this->container->getParameter('framework.default_locale');
//        $this->locale = $this->locale ? $this->locale :'es';
    }

    public function getLocale()
    {
        return $this->locale;
    }

    
    public function getTranslations()
    {
        $toRet = [];
        if ($this->translations)
        {
            foreach ($this->translations as $translation)
            {
                if (!isset($toRet[$translation->getLocale()]))
                {
                    $toRet[$translation->getLocale()] = array();
                }
                $toRet[$translation->getLocale()][$translation->getFieldName()] = $translation->getFieldValue();
            }
        }
        if (!isset($toRet[$this->locale]))
        {
            $toRet[$this->locale] = array();
        }
        //check if all translations contains all fields
        $avFields = $this->getTranslatableFields();
        foreach ($avFields as $avField)
        {
            foreach ($toRet as $lang => $field)
            {
                if (!isset($field[$avField]))
                {
                    $toRet[$lang][$avField] = '';
                }
            }
        }
        return $toRet;
    }

    public function getTranslationsObjects()
    {
        return $this->translations;
    }

    public function setTranslations($translations, LifecycleEventArgs $args = null)
    {
        $ftrans = [];
        foreach ($translations as $locale => $field)
        {
            if (is_array($field))
            {
                foreach ($field as $name => $value)
                {
                    $encountred = false;
                    if (!$this->translations || !is_array($this->translations))
                    {
                        $this->translations = new ArrayCollection([]);
                    }
                    foreach ($this->translations as $currentTrans)
                    {
                        if ($currentTrans->getFieldName() == $name && $currentTrans->getLocale() == $locale)
                        {
                            $encountred = true;
                            $ftran = $currentTrans;
                            break;
                        }
                    }
                    if (!$encountred)
                    {
                        // If not exist create new 
                        $ftran = new FieldTranslation();
                        $ftran->setFieldName($name);
                        $ftran->setLocale($locale);
                        if ($args)
                        {
                            $entityManager = $args->getEntityManager();
                            $entityManager->persist($ftran);
                        }
                    }
                    //set new value
                    $ftran->setFieldValue($value);

                    $ftrans[] = $ftran;
                }
            }
        }
        $this->translations = new ArrayCollection($ftrans);
    }

    public function getFieldTranslations($translatableField)
    {
        return new ArrayCollection(
                array_values(
                        $this->translations->filter(
                                function (FieldTranslation $fieldTranslation) use ($translatableField) {
                            return $fieldTranslation->getFieldName() == $translatableField;
                        }
                        )->toArray()
                )
        );
    }

    public function getLocaleTranslations($locale)
    {
        return reset(
                $this->translations->filter(
                        function (FieldTranslation $fieldTranslation) use ($locale) {
                    return $fieldTranslation->getLocale() == $locale;
                }
                )->toArray()
        );
    }

    public function getTranslation($translatableField, $locale = null)
    {
        $locale = $locale ? $locale : $this->locale;
        return reset(
                $this->translations->filter(
                        function (FieldTranslation $fieldTranslation) use ($translatableField, $locale) {
                    return $fieldTranslation->getFieldName() == $translatableField && $fieldTranslation->getLocale() == $locale;
                }
                )->toArray()
        );
    }

    /**
     * @param FieldTranslation[]|ArrayCollection $translations
     * @param $translatableField
     */
    public function setFieldTranslations($translations, $translatableField)
    {
        foreach ($this->translations as $translation)
        {
            if (strcmp($translation->getFieldName(), $translatableField) == 0)
            {
                if ($translations->contains($translation))
                {
                    $translations->removeElement($translation);
                } else
                {
                    $this->translations->removeElement($translation);
                }
            }
        }

        foreach ($translations as $translation)
        {
            $translation->setFieldName($translatableField);
            $this->translations->add($translation);
        }
    }

    function setLocale($locale)
    {
        $this->locale = $locale;
    }

    public function getTranslatableFields()
    {
        $annotationReader = new AnnotationReader();
        $reflectedEntity = new \ReflectionClass($this);
        $res = $annotationReader->getClassAnnotation($reflectedEntity, Translatable::class);
        $fields = [];
        if ($res)
        {
            $classProperties = $reflectedEntity->getProperties();
            foreach ($classProperties as $currentProperty)
            {
                $annotatedProp = $annotationReader->getPropertyAnnotation($currentProperty, TranslateMe::class);
                if ($annotatedProp)
                {
                    $fields[] = $currentProperty->getName();
                }
            }
        }
        return $fields;
    }

}
