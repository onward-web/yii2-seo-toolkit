<?php

namespace voskobovich\seo\behaviors;

use voskobovich\seo\models\UrlRoute;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;
use yii\base\Behavior;

/**
 * Class ActualityUrlBehavior
 * @package voskobovich\seo\behaviors
 */
class ActualityUrlBehavior extends Behavior
{
    
    /**
     * Event name
     */
    const EVENT_CHECK_URL = 'event_actuality_url';

    /**
     * Redirect HTTP Code
     * @var int
     */
    public $redirectCode = 301;

    /**
     * Action key
     * @var int
     */
    public $actionKey = UrlRoute::ACTION_VIEW;

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            static::EVENT_CHECK_URL => 'run',
        ];
    }

    /**
     * @throws InvalidConfigException
     * @throws \yii\base\ExitException
     */
    public function run()
    {
       
        $request = Yii::$app->request;

        /** @var ActiveRecord $model */
        $model = $this->owner;

        /** @var UrlRoute $urlRoute */
        $urlRoute = $this->routeModelClass;
        $urlRoute = $urlRoute::find()
            ->select(['action_key', 'object_key', 'object_id', 'language_id', 'path'])
            ->andWhere([
                'action_key' => $this->actionKey,
                'object_key' => $this->objectKey,
                'object_id' => $model->getPrimaryKey(),
                'language_id' => Yii::$app->get('multilingual')->language_id
            ])
            ->one();
        
        if (!$urlRoute) {
            return;
        }

      
        
        if ($urlRoute->path !== $request->getPathInfo()) {
            Yii::$app->getResponse()->redirect([$urlRoute->path], $this->redirectCode);
            Yii::$app->end();
        }
    }
    
   
}