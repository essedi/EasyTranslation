<?php

namespace Essedi\EasyTranslation\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class FieldTranslation
{

    /**
     * @var int
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string Lang abbr
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $locale;

    /**
     * @var string column filed name 
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $fieldName;

    /**
     * @var string final value
     * @ORM\Column(type="text", length=2555, nullable=true)
     */
    private $fieldValue;

    /**
     * @var string final value
     * @ORM\Column(type="string", length=255, nullable=true,options={"default":"text"})
     */
    private $fieldType;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param mixed $locale
     * @return FieldTranslation
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * @param mixed $fieldName
     * @return FieldTranslation
     */
    public function setFieldName($fieldName)
    {
        $this->fieldName = $fieldName;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFieldValue()
    {
        return $this->fieldValue;
    }

    /**
     * @param mixed $fieldValue
     * @return FieldTranslation
     */
    public function setFieldValue($fieldValue)
    {
        $this->fieldValue = $fieldValue;

        return $this;
    }
    /**
     * @return mixed
     */
    public function getFieldType()
    {
        return $this->fieldType;
    }

    /**
     * @param mixed $fieldName
     * @return FieldTranslation
     */
    public function setFieldType($fieldType)
    {
        $this->fieldType = $fieldType;

        return $this;
    }
    public function __toString()
    {
        return $this->locale . ' - ' . $this->fieldName . ' - ' . $this->fieldValue;
    }

}
