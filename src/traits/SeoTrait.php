<?php
namespace voskobovich\seo\traits;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;


trait SeoTrait
{
    protected static $routeTableName;
    
    
    public function getSlugs(){
        return $this->hasMany($this->getRouteModelClassName(), ['object_id' => 'id'])
            ->andOnCondition(['action_key' => $this->actionKey, 'object_key' => $this->objectKey]);                       
    }
    
    
    public function getDefaultRoute()
    {
        /** @var \yii\db\ActiveRecord|\voskobovich\seo\behaviors\CreateUrlBehavior $this */
        return $this->hasOne($this->getRouteModelClassName(), [$this->object_id => 'id'])
            ->where([static::getRouteTableName() . '.language_id' => Yii::$app->multilingual->language_id,
                     'action_key' => $this->actionKey, 'object_key' => $this->objectKey]);
    }
    
    protected static function getRouteTableName()
    {
        if (self::$routeTableName === null) {
            /** @var ActiveRecord|MultilingualActiveRecord $model */
            $model = new static;
            /** @var ActiveRecord $translationModelClassName */
            $routeModelClassName = $model->getRouteModelClassName();
            self::$routeTableName = $routeModelClassName::tableName();
        }
        return self::$routeTableName;
    }
    
    
}