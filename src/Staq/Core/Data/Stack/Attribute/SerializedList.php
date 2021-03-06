<?php

/* This file is part of the Staq project, which is under MIT license */


namespace Staq\Core\Data\Stack\Attribute;

class SerializedList extends SerializedMap
{


    /* PUBLIC USER METHODS
     *************************************************************************/
    public function get()
    {
        $list = parent::get();
        return array_values($list);
    }

    public function set($map)
    {
        $map = array_values($map);
        return parent::set($map);
    }
}