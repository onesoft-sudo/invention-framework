<?php


namespace OSN\Framework\Tests\Feature;


class TestCase extends \PHPUnit\Framework\TestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createApp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->destroyApp();
    }
}
