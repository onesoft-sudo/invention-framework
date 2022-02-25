<?php


namespace OSN\Framework\Tests\Feature\Controllers;


use OSN\Framework\Core\Controller;

class APIController extends Controller
{
    public function index(): string
    {
        return 'index';
    }

    public function view(): string
    {
        return 'view';
    }

    public function store(): string
    {
        return 'store';
    }

    public function update(): string
    {
        return 'update';
    }

    public function delete(): string
    {
        return 'delete';
    }
}