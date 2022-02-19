<?php


namespace OSN\Framework\Tests\Feature\Controllers;


use OSN\Framework\Core\Controller;

class MainController extends Controller
{
    public function index()
    {
        return 'Hello world!';
    }

    public function store()
    {
        return [
            '123' => 123,
            'data' => request()->all()
        ];
    }
}