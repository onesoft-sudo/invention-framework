<?php


namespace OSN\Framework\Events\BuiltIn;

use OSN\Framework\Events\Event;
use OSN\Framework\Foundation\App;

class AppRunCompleteEvent extends Event
{
    /**
     * @var App
     */
    public App $app;

    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->app = $data['app'];
    }
}
