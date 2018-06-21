<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class FieldTranslation
{
    
     /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @ORM\Column(type="string")
     * 
     */
    private $locale;
    
    /**
     * @ORM\Column(type="string")
     * 
     */
    private $fieldName;
    
    /**
     * @ORM\Column(type="string")
     * 
     */
    private $fieldValue;

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

    public function __toString()
    {
        return $this->locale . ' - ' . $this->fieldName . ' - ' . $this->fieldValue;
    }
}