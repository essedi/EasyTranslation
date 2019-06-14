<?php

namespace Essedi\EasyTranslation\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\Common\Annotations\AnnotationReader;
use Essedi\EasyTranslation\Annotation\Translatable;
use Essedi\EasyTranslation\Annotation\TranslateMe;
use Essedi\EasyTranslation\Entity\FieldTranslation;
use Doctrine\Common\Util\ClassUtils;
use ErrorException;

/** @MappedSuperclass */
abstract class Translation
{

    protected $locale = 'es';

    /**
     * @var FieldTranslation[]
     * @ManyToMany(targetEntity="Essedi\EasyTranslation\Entity\FieldTranslation", cascade={"persist", "remove"})
     */
    protected $translations;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
        //sets default translations
        $this->setTranslations($this->getTranslations());
    }

    public function getLocale()
    {
        return $this->locale;
    }

//    public function getFields(): Collection
//    {
//        return $this->fields;
//    }
//
//    public function addField(FieldTranslation $field): self
//    {
//        if (!$this->fields->contains($field))
//        {
//            $this->fields[] = $field;
//            $field->setTranslation($this);
//        }
//
//        return $this;
//    }
//
//    public function removeField(FieldTranslation $field): self
//    {
//        if ($this->fields->contains($field))
//        {
//            $this->fields->removeElement($field);
//            // set the owning side to null (unless already changed)
//            if ($field->getTranslation() === $this)
//            {
//                $field->setTranslation(null);
//            }
//        }
//
//        return $this;
//    }
//
//    public function setFields(Collection $fields): self
//    {
//        //unset all fields
//        foreach ($this->fields as $field)
//        {
//            $field->setTranslation(null);
//        }
//        //set new fields 
//        $this->fields = $fields;
//        foreach ($fields as $field)
//        {
//            $field->setTranslation($field);
//        }
//
//        return $this;
//    }

    public function getTranslations($locale = null)
    {
        $locale = $locale ? $locale : $this->locale;

        $toRet = [];
        if ($this->translations)
        {
            foreach ($this->translations as $translation)
            {
                if (!isset($toRet[$translation->getLocale()]))
                {
                    $toRet[$translation->getLocale()] = array();
                }

                $toRet[$translation->getLocale()][$translation->getFieldName()] = $translation;
            }
        }
        if (!isset($toRet[$locale]))
        {
            $toRet[$locale] = array();
        }
        //check if all translations contains all fields
        $avFields = $this->getTranslatableAnnotations();
        foreach ($avFields as $avField => $annotation)
        {
            foreach ($toRet as $lang => $field)
            {
                if (!isset($field[$avField]))
                {
                    $ftran                  = new FieldTranslation();
                    $ftran->setFieldName($avField);
                    $ftran->setLocale($lang);
                    $ftran->setFieldType($annotation->type);
                    $toRet[$lang][$avField] = $ftran;
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
            if ($locale == "new" || !$locale)
            {
                continue;
            }
            if (is_array($field))
            {
                foreach ($field as $name => $value)
                {
                    if (!is_object($value))
                    {
                        if (is_array($value))
                        {
                            $type  = key($value);
                            $value = $value[$type];
                        }
                        else
                        {
                            $type = $this->getFieldType($name);
                        }
                    }
                    else
                    {
                        $type  = $value->getFieldType();
                        $value = $value->getFieldValue();
                    }
                    $ftran = $this->getFieldTranslation($name, $locale);
                    if ($ftran)
                    {
                        //set new value
                        $ftran->setFieldValue($value);
                        $ftran->setFieldType($type);
                        $ftrans[] = $ftran;
                    }
                }
            }
            else
            {
//                die(var_dump("HERE", $locale, $field, $translations));
            }
        }
        $this->translations = new ArrayCollection($ftrans);
    }

    public function getFieldTranslation($fieldName, $locale = null): FieldTranslation
    {
        foreach ($this->translations as $currentTrans)
        {

            if ($currentTrans->getFieldName() == $fieldName && $currentTrans->getLocale() == $locale)
            {
                return $currentTrans;
            }
        }

        // If not exist create new 
        $ftran = new FieldTranslation();
        $ftran->setFieldName($fieldName);
        $ftran->setLocale($locale);
        return $ftran;
    }

    public function getFieldTranslations($translatableField)
    {
        return new ArrayCollection(
                array_values(
                        $this->translations->filter(
                                function (FieldTranslation $fieldTranslation) use ($translatableField)
                        {
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
                        function (FieldTranslation $fieldTranslation) use ($locale)
                {
                    return $fieldTranslation->getLocale() == $locale;
                }
                )->toArray()
        );
    }

    public function getTranslation($fieldName, $locale = null): ?FieldTranslation
    {
        $locale = $locale ? $locale : $this->locale;
        $values = array_values(
                $this->translations->filter(
                        function (FieldTranslation $fieldTranslation) use ($fieldName, $locale)
                {
                    return $fieldTranslation->getFieldName() == $fieldName && $fieldTranslation->getLocale() == $locale;
                }
                )->toArray()
        );
        if (count($values))
        {
            return $values[0];
        }
        else
        {

            //needs implement create new  fieldTranslation for this field.
//            throw new ErrorException("Fail on attempt search field $fieldName on lang $locale");
            return null;
        }
    }

    public function setTranslation($value, $fieldName, $locale = null)
    {
        $locale = $locale ? $locale : $this->locale;
        $trans  = $this->getTranslation($fieldName, $locale);
        if (!$trans)
        {
            //create new
            throw new ErrorException("Set new translation not implemented yet");
        }
        $trans->setFieldValue($value);
        $type = $this->getFieldType($fieldName);
        if ($trans->getFieldType() !== $type)
        {
            $trans->setFieldType($type);
        }
        return $this;
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
                }
                else
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

    public function getFieldType($propertyName): ?string
    {
        $annotationReader = new AnnotationReader();
        $reflectedEntity  = new \ReflectionClass(ClassUtils::getClass($this));
        $res              = $annotationReader->getClassAnnotation($reflectedEntity, Translatable::class);
        if ($res)
        {
            $classProperties = $reflectedEntity->getProperties();
            foreach ($classProperties as $currentProperty)
            {
                if ($currentProperty && $currentProperty->getName() == $propertyName)
                {
                    $annotatedProp = $annotationReader->getPropertyAnnotation($currentProperty, TranslateMe::class);
                    if ($annotatedProp)
                    {
                        $type = $annotatedProp->type;
                        return $type;
                    }
                }
            }
        }
        return null;
    }

    public function getTranslatableFields()
    {
        $annotationReader = new AnnotationReader();
        $reflectedEntity  = new \ReflectionClass(ClassUtils::getClass($this));
        $res              = $annotationReader->getClassAnnotation($reflectedEntity, Translatable::class);
        $fields           = [];
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

    public function getTranslatableAnnotations()
    {
        $annotationReader = new AnnotationReader();
        $reflectedEntity  = new \ReflectionClass(ClassUtils::getClass($this));
        $res              = $annotationReader->getClassAnnotation($reflectedEntity, Translatable::class);
        $fields           = [];
        if ($res)
        {
            $classProperties = $reflectedEntity->getProperties();
            foreach ($classProperties as $currentProperty)
            {
                $annotatedProp = $annotationReader->getPropertyAnnotation($currentProperty, TranslateMe::class);

                if ($annotatedProp)
                {
                    $fields[$currentProperty->getName()] = $annotatedProp;
                }
            }
        }
        return $fields;
    }

//    public function getDefaultLocaleTranslations($locale = null): Collection
//    {
//        $locale   = $locale ? $locale : $this->locale;
//        //check if all translations contains all fields
//        $avFields = $this->getTranslatableAnnotations();
//        foreach ($avFields as $avField => $annotation)
//        {
//            if (!isset($field[$avField]))
//            {
//                $ftran = new FieldTranslation();
//                $ftran->setFieldName($avField);
//                $ftran->setLocale($locale);
//                $ftran->setFieldType($annotation->type);
//            }
//        }
//        return new ArrayCollection();
//    }
}
