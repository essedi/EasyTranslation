<?php

namespace Essedi\EasyTranslation\Annotation;

/**
 * @Annotation
 */
class TranslateMe
{

    /**
     * @var mixed
     */
    public $type = 'text';

    /**
     * @var string
     */
    public $label = null;

    /**
     * @var mixed
     */
    public $config = [];

}
