<?php

namespace Gpupo\CamelSpiderReaderBundle\Entity;
use  Doctrine\Common\Collections\ArrayCollection,
     Doctrine\Common\Util\Inflector;


class Send extends ArrayCollection
{
    public function __get($o)
    {
        return $this->get($o);
    }

    public function __set($o, $v)
    {
        return $this->set($o, $v);
    }

}

