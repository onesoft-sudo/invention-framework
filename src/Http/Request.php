<?php


namespace OSN\Framework\Http;

use OSN\Framework\Exceptions\PropertyNotFoundException;
use OSN\Framework\Http\RequestValidator;

class Request
{
    use HTTPRequestParser;
    use RequestValidator;

    public string $method;
    public string $realMethod;
    public string $uri;
    public string $baseURI;
    public string $protocol;
    public string $host;
    public string $hostname;
    public string $port;
    public bool $ssl;
    public string $queryString;
    public string $ip;

    public object $post;
    public object $get;
    protected ?array $old;
    public array $uploadedFiles;

    public object $headers;

    private bool $errmode_exception;
    /**
     * @var bool
     */
    public bool $autoValidate = false;

    public function __construct(?array $data = null, bool $errmode_exception = true)
    {
        $this->update($data);
        $this->errmode_exception = $errmode_exception;

        if (!empty($this->all()))
            session()->set('__old_data', $this->all());
    }

    public function old($key = null)
    {
        if (!isset($this->old) || $this->old == null)
            $this->old = session()->getFlash('__old_data');

        return $key === null ? $this->old : ($this->old[$key] ?? null);
    }

    public function get(string $key)
    {
        return $this->get->$key ?? false;
    }

    public function post(string $key)
    {
        return $this->post->$key ?? false;
    }

    public function isWriteRequest(): bool
    {
        if(in_array($this->method, ["POST", 'PUT', 'PATCH', "DELETE"])) {
            return true;
        }

        return false;
    }

    /**
     * @throws PropertyNotFoundException
     */
    public function __get($name)
    {
        if (!$this->isWriteRequest()) {
            $prop = $this->get($name);
        }
        else {
            $prop = $this->post($name);
        }

        if ($prop === false && $this->errmode_exception)
            throw new PropertyNotFoundException();

        return $prop;
    }

    public function header($key)
    {
        return $this->headers->$key ?? false;
    }

    public function getMethod()
    {
        $realMethod = $this->realMethod;

        if ($realMethod !== 'POST' || !isset($this->post->__method))
            return $realMethod;

        return strtoupper($this->post->__method);
    }

    public function only(array $only): array
    {
        $arr = [];

        foreach ($only as $key) {
            $arr[$key] = $this->$key ?? null;
        }

        return $arr;
    }


    public function except(array $except): array
    {
        $arr = array_merge((array) $this->get, (array) $this->post);

        foreach ($except as $key) {
            if (isset($arr[$key]))
                unset($arr[$key]);
        }

        return $arr;
    }

    public function all(): array
    {
        return $this->except([]);
    }

    public function rules(): array
    {
        return [];
    }

    public function authorize(): bool
    {
        return true;
    }

    public function has(string $field): bool
    {
        try {
            $tmp = $this->{$field};

            if ($tmp === null)
                return false;

            return true;
        }
        catch (PropertyNotFoundException $e) {
            return false;
        }
    }

    public function hasField(string $field): bool
    {
        return $this->has($field);
    }

    public function update(?array $data = null)
    {
        if ($data === null) {
            $data = [
                "get" => $_GET,
                "post" => $_POST,
                "files" => $_FILES,
                "method" => strtoupper(trim($_SERVER['REQUEST_METHOD'] ?? '')),
                "uri" => $_SERVER['REQUEST_URI'] ?? '',
                "protocol" => $_SERVER['SERVER_PROTOCOL'] ?? '',
                "host" => $_SERVER["HTTP_HOST"] ?? '',
                "ssl" => isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] === 'on',
                "ip" => $_SERVER['REMOTE_ADDR'],
                "headers" => headers()
            ];
        }

        $data['get'] = array_map(function ($val) {
            return filter_var($val,FILTER_SANITIZE_SPECIAL_CHARS);
        }, $data['get']);

        $data['post'] = array_map(function ($val) {
            return filter_var($val,FILTER_SANITIZE_SPECIAL_CHARS);
        }, $data['post']);

        $this->post = (object) $data["post"];
        $this->get = (object) $data["get"];
        $this->uploadedFiles = $data["files"];

        $this->realMethod = $data["method"];
        $this->method = $this->getMethod();
        $this->uri = $data["uri"];
        $this->baseURI = $this->getBaseURI($this->uri);
        $this->protocol = $data["protocol"];
        $this->host = $data["host"];
        $this->hostname = $this->getHost($this->host);
        $this->port = $this->getPort($this->host);
        $this->ssl = $data["ssl"];
        $this->queryString = $this->getQueryString($this->uri);
        $this->ip = $data["ip"];

        $this->headers = (object) $data["headers"];
    }
}
