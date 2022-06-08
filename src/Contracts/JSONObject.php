<?php


namespace OSN\Framework\Contracts;


interface JSONObject extends \JsonSerializable
{
    public function toJSON(): string;
}