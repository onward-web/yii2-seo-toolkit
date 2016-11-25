<?php
namespace voskobovich\seo\behaviors;


use Yii;
use yii\db\ActiveRecord;
use yii\base\Behavior;

use voskobovich\seo\models\UrlRoute;
use voskobovich\seo\interfaces\SeoModelInterface;

/**
 * Class MultilingualUrlBehavior
 * @package voskobovich\seo\behaviors
 */
class CreateUrlBehavior extends Behavior
{
    
    
    /**
     * Event name
     */
    //const EVENT_CHECK_URL = 'event_actuality_url';
    /**
     * Redirect HTTP Code
     * @var int
     */
    public $redirectCode = 301;   
    
    public $routeModelClass;
    
    public $routeRelation = 'slugs';
    
    public $routeAttributes;
    
    public $actionKey = UrlRoute::ACTION_VIEW;
    
    public $objectKey;
    
    public $defaultRouteRelation = 'defaultRoute';
    
    /**
     * @inheritdoc
     */
    public function attach($owner){
        
        parent::attach($owner);
        
        $className = $this->getRouteModelClassName();
        /** @var ActiveRecord $exampleModel */
        $exampleModel = new $className;
        $this->routeAttributes = array_keys(
            $exampleModel->getAttributes(
                null,
                [
                    'action_key',
                    'object_key',
                    'language_id',
                    'object_id'

                ]
            )
        );    
    }
    
     /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_VALIDATE => 'afterValidate',
            ActiveRecord::EVENT_AFTER_INSERT => 'afterSave',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterSave',
            ActiveRecord::EVENT_BEFORE_DELETE => 'beforeDelete',
            //static::EVENT_CHECK_URL => 'checkUrl',
        ];
    }

    
    public function checkUrl()
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
    
    
    /**
     * @inheritdoc
     */
    public function getRouteModelClassName()
    {
        if ($this->routeModelClass === false) {
            return $this->owner->className() . 'UrlRoute';
        } else {
            return $this->routeModelClass;
        }
        
    }
    
    
    public function getPath($language_id = null){
        
        $multilingual = Yii::$app->multilingual;
        
        if ($language_id === null) {
            $language_id = $multilingual->language_id;
        }        
        
        $owner = $this->owner;
        
        // relations
        if($language_id === $multilingual->language_id && !$owner->isRelationPopulated($this->routeRelation)){
            $language_id = $multilingual->language_id;
            $translation = $owner->{$this->defaultRouteRelation};
            if ($translation !== null) {
                return $translation;
            }
        }else{
            // language id specified and it's not default
            $paths = $owner->{$this->routeRelation};
            foreach ($paths as $item) {
                if ($item->language_id === $language_id) {
                    return $item;
                }
            }
        }
        
        // relations does not exists!
        // Если екземплера нету, загружаем значения по умолчанию loadDefaultValues
        
        /* @var ActiveRecord $class */
        $class = $this->getRouteModelClassName();
        /* @var ActiveRecord $translation */
        $path = new $class();
        $path->loadDefaultValues();
        $path->setAttribute('language_id', $language_id);

        $paths = $this->owner->{$this->routeRelation};
        $paths[] = $path;

        $owner->populateRelation($this->routeRelation, $paths);
        
        
        return $path;
    }
    
    
    /**
     * @return void
     */
    public function afterValidate()
    {
        if (!Model::validateMultiple($this->owner->{$this->routeRelation})) {
            /* @var ActiveRecord $owner  */
            $owner = $this->owner;
            $owner->addError($this->routeRelation);
        }
    }

    /**
     * @return void
     */
    public function afterSave()
    {
        /** @var ActiveRecord $owner */
        $owner = $this->owner;
        /* @var ActiveRecord $translation */
        $paths = $owner->{$this->routeRelation};
        // as we link all current related models - they will duplicate in related
        // that's because of "update lazily loaded related objects" in link
        // so we are saving them into variable and empty _related of model
        $owner->populateRelation($this->routeRelation, []);

        foreach ($paths as $path) {
            $owner->link($this->routeRelation, $path);
        }
        // now all translations saved and are in _related !

    }

    /**
     * @return boolean
     */
    public function beforeDelete(ModelEvent $event)
    {
        $result = $event->isValid;
        if ($result !== false) {
            $paths = $this->owner->{$this->routeRelation};
            /* @var ActiveRecord $translation */
            foreach ($paths as $path) {
                $path->delete();
            }
        }
        return $result;
    }
    
    
    
    /**
     * @inheritdoc
     */
    public function canGetProperty($name, $checkVars = true)
    {
        return in_array($name, $this->routeAttributes) ?: parent::canGetProperty($name, $checkVars);
    }

    /**
     * @inheritdoc
     */
    public function canSetProperty($name, $checkVars = true)
    {
        return in_array($name, $this->routeAttributes) ?: parent::canSetProperty($name, $checkVars);
    }

    /**
     * @inheritdoc
     */
    public function __get($name)
    {
        return $this->getPath()->getAttribute($name);
    }

    /**
     * @inheritdoc
     */
    public function __set($name, $value)
    {
               
        $translation = $this->getPath();
        $translation->setAttribute($name, $value);
    }
    
    
   
    
    
    
    
    
    
    
    
    
    
    
    
   
    
    
    
    
    
}