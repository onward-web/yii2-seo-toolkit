<?php

namespace voskobovich\seo\behaviors;

use voskobovich\seo\interfaces\SeoModelInterface;
use voskobovich\seo\models\UrlRoute;
use Yii;
use yii\base\Behavior;
use yii\base\InvalidConfigException;


/**
 * Class BaseUrlBehavior
 * @package voskobovich\seo\behaviors
 */
abstract class BaseUrlBehavior extends Behavior
{
    /**
     * UrlRoute model namespace
     * @var string
     */
    public $routeModelClass;
    /**
     * UrlRoute object key
     * @var int
     */
    public $objectKey;

    /**
     * @param \yii\base\Component $owner
     * @throws InvalidConfigException
     */
    public function attach($owner)
    {
        parent::attach($owner);

        if ($owner && !$owner instanceof SeoModelInterface) {
            throw new InvalidConfigException('Owner must be implemented "app\seo\interfaces\SeoModelInterface"');
        }
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        
        
        if ($this->objectKey == null) {
            throw new InvalidConfigException('Param "actionKey" must be contain object key.');
        }

        if (!$this->routeModelClass) {
            throw new InvalidConfigException('Param "routeModelClass" can not be empty.');
        }

        if (!is_subclass_of($this->routeModelClass, UrlRoute::className())) {
            throw new InvalidConfigException('Object "routeModelClass" must be implemented ' . UrlRoute::className());
        }

        parent::init();
    }
}