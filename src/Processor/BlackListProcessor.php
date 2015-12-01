<?php

namespace Sokil\FraudDetector\Processor;

use \Sokil\FraudDetector\AbstractProcessor;

class BlackListProcessor extends AbstractProcessor
{
    /**
     *
     * @var \Sokil\FraudDetector\Processor\BlackList\AbstractStorage
     */
    private $storage;

    private $banOnRateExceed = false;

    public function init()
    {
        $self = $this;

        // rate exceed event handler
        $this->detector->subscribe('requestRate.checkFailed', function() use($self) {
            // ban on rate exceed
            if($self->isBannedOnRateExceed()) {
                $self->ban();
            }
        });
    }

    public function isPassed()
    {
        return !$this->isBanned();
    }

    public function ban()
    {
        $this->storage->store($this->detector->getKey());
        return $this;
    }

    public function isBanned()
    {
        return $this->storage->isStored($this->detector->getKey());
    }

    public function banOnRateExceed()
    {
        $this->banOnRateExceed = true;
        return $this;
    }

    public function isBannedOnRateExceed()
    {
        return true === $this->banOnRateExceed;
    }

    public function setStorage($type, $configuratorCallable = null)
    {
        $className = '\Sokil\FraudDetector\Processor\BlackList\Storage\\' . ucfirst($type) . 'Storage';

        if(!class_exists($className)) {
            throw new \Exception('Storage ' . $type . ' not found');
        }

        $this->storage = new $className();

        // configure
        if(is_callable($configuratorCallable)) {
            call_user_func($configuratorCallable, $this->storage);
        }

        return $this;

    }
}