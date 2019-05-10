<?php

namespace Essedi\EasyTranslation\Resources\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\LocaleType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Essedi\EasyTranslation\Entity\FieldTranslation;

class TranslationType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add($builder->create('translations', CollectionType::class, array('error_bubbling' => false, 'empty_data' => "")));
        $builder->add('newlocale', LocaleType::class, array(
            'preferred_choices' => [
                "es",
                "en"
            ],
            "placeholder"       => "Chose one"
        ));
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event)
        {
            $translations = $event->getData();
            $form         = $event->getForm();
            $firstLang    = end(array_keys($translations));
            foreach ($translations as $lang => $trans)
            {
                foreach ($trans as $field => $value)
                {
                    //get type
                    $form->add($lang . $field . $value->getFieldType(), $this->getFieldTypeClass($value));
                    //adds to for new lang
                    if ($lang == $firstLang)
                    {
                        $form->add('new' . $field . $value->getFieldType(), $this->getFieldTypeClass($value));
                    }
                }
            }
        });
        $builder->add('ckeditor', CKEditorType::class);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
                array(
                    'data_class' => null,
                    'choices'    => null,
                )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'essedi_easytranslator_type';
    }

    public function getBlockPrefix()
    {
        return "translation";
    }

    protected function getFieldTypeClass(FieldTranslation $field)
    {
        $class = null;
        switch ($field->getFieldType())
        {
            case FieldTranslation::FIELD_TYPE_CHECKBOX:
                $class = CheckboxType::class;
                break;
            case FieldTranslation::FIELD_TYPE_CKEDITOR:
                $class = CKEditorType::class;
                break;
            case FieldTranslation::FIELD_TYPE_TEXTAREA:
                $class = TextareaType::class;
                break;

            case FieldTranslation::FIELD_TYPE_TEXT:
            default:
                $class = TextType::class;
                break;
        }
        return $class;
    }

}
