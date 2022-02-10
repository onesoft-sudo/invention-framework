<?php


namespace OSN\Framework\Http;


class Response
{
    use ResponseTrait;

    protected string $httpVersion = '1.1';

    public function __construct(?string $response = null, int $code = 200, array $headers = [])
    {
        $this->setContent($response);
        $this->setCode($code);
        $this->setStatusFromCode($code);
        $this->setHeadersParsed($headers);
    }

    protected function setData()
    {
        @header("HTTP/{$this->httpVersion} {$this->getCode()} {$this->getStatusText()}");
        foreach ($this->headers as $header => $value) {
            @header("$header: $value");
        }
    }

    public function __toString()
    {
        $this->setData();

        if ($this->getCode() > 299 && $this->getCode() < 400) {
            exit();
        }

        return $this->getContent();
    }

    public function __invoke(): string
    {
        $this->setData();

        if ($this->getCode() > 299 && $this->getCode() < 400 && server('APP_ENV') != 'testing') {
            app()->dispatch('AppRunningComplete', [app()]);
            exit();
        }

        return $this->getContent();
    }

    public function redirect($url, int $code = 302): self
    {
        $this->setCode($code);
        $this->header('Location', $url);
        return $this;
    }

    public function redirectImmediately($header, $code = 302)
    {
        $text = $this->getStatusFromCode($code);
        header("HTTP/{$this->httpVersion} $code $text");
        header("Location: $header");
        app()->dispatch('AppRunningComplete', [app()]);
        exit();
    }
}
