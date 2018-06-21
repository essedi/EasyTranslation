<?php

namespace App\Form\Type;

use App\Entity\FieldTranslation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\LocaleType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Form\Type\TabsType;

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
            "placeholder" => "Chose one"
        ));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
                array(
                    'data_class' => null,
                    'choices' => null,
                )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_fieldtranslation';
    }

    public function getBlockPrefix()
    {
        return "translation";
    }

}
