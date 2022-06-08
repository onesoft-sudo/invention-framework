<?php


namespace OSN\Framework\Utils;


trait JSONAble
{
    public function toJSON(): string
    {
        return json_encode($this);
    }
}