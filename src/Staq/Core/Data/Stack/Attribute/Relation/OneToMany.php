<?php

/* This file is part of the Staq project, which is under MIT license */


namespace Staq\Core\Data\Stack\Attribute\Relation;

class OneToMany extends OneToMany\__Parent
{


    /*************************************************************************
    ATTRIBUTES
     *************************************************************************/
    protected $changed = FALSE;
    protected $model;
    protected $remoteModels = NULL;
    protected $remoteModelType;
    protected $remoteAttributeName;


    /*************************************************************************
    CONSTRUCTOR
     *************************************************************************/
    public function initBySetting($model, $setting)
    {
        $this->model = $model;
        if (is_array($setting)) {
            if (!isset($setting['remote_class_type'])) {
                throw new \Stack\Exception\MissingSetting('"remote_class_type" missing for the OneToMany relation.');
            }
            if (!isset($setting['remote_attribute_name'])) {
                throw new \Stack\Exception\MissingSetting('"remote_attribute_name" missing for the OneToMany relation.');
            }
            $this->remoteModelType = $setting['remote_class_type'];
            $this->remoteAttributeName = $setting['remote_attribute_name'];
        }
    }


    /*************************************************************************
    PUBLIC USER METHODS
     *************************************************************************/
    public function get()
    {
        if (is_null($this->remoteModels)) {
            $class = $this->getRemoteClass();
            $this->remoteModels = (new $class)->entity->fetchByRelated($this->remoteAttributeName, $this->model);
        }
        return $this->remoteModels;
    }

    public function getIds()
    {
        $ids = [];
        foreach ($this->get() as $model) {
            $ids[] = $model->id;
        }
        return $ids;
    }

    public function set($remoteModels)
    {
        $this->remoteModels = [];
        $this->changed = TRUE;
        \UArray::doConvertToArray($remoteModels);
        foreach( $remoteModels as $model ) {
            if (empty($model)) {
                $remoteModel = $this->getRemoteModel();
            } else if (is_numeric($model)) {
                $model = $this->getRemoteModel()->entity->fetchById($model);
            } else if (!\Staq\Util::isStack($model, $this->getRemoteClass())) {
                $message = 'Input of type "' . $this->getRemoteClass() . '", but "' . gettype($model) . '" given.';
                throw new \Stack\Exception\NotRightInput($message);
            }
            if ($model->exists()) {
                $this->remoteModels[] = $model;
            }
        }
        return $this;
    }


    /*************************************************************************
    PUBLIC DATABASE METHODS
     *************************************************************************/
    public function getSeed()
    {
        return NULL;
    }

    public function setSeed($seed)
    {
    }


    /*************************************************************************
    HANDLER METHODS
     *************************************************************************/
    public function saveHandler()
    {
        if ($this->changed) {
            $class = $this->getRemoteClass();
            (new $class)->entity->updateRelated($this->remoteAttributeName, $this->getIds(), $this->model);
        }
    }


    /*************************************************************************
    PUBLIC METHODS
     *************************************************************************/
    public function getRelatedModels()
    {
        $class = $this->getRemoteClass();
        return (new $class)->entity->fetchAll();
    }

    public function getRemoteModel()
    {
        $class = $this->getRemoteClass();
        return new $class;
    }

    public function getRemoteModelType()
    {
        return $this->remoteModelType;
    }

    public function getRemoteClass()
    {
        return $class = 'Stack\\Model\\' . $this->remoteModelType;
    }
}