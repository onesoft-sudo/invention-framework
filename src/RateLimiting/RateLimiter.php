<?php


namespace OSN\Framework\RateLimiting;


use OSN\Framework\Core\Middleware;
use OSN\Framework\Exceptions\HTTPException;
use OSN\Framework\Http\Request;

class RateLimiter extends Middleware
{
    protected int $limit;
    protected int $sec;

    protected object $data;
    protected string $confDB = '/storage/invention/ratelimiter.json';

    public function __construct(int $limit = 1, int $sec = 2)
    {
        $this->limit = $limit;
        $this->sec = $sec;
        $this->data = $this->loadData();
        date_default_timezone_set('Asia/Dhaka');
    }

    protected function loadData()
    {
        return json_decode(file_get_contents(basepath($this->confDB)));
    }

    protected function updateData()
    {
        file_put_contents(basepath($this->confDB), json_encode($this->data, JSON_PRETTY_PRINT));
    }

    public function handle(Request $request)
    {
        $ip = $request->ip;
        $client = null;

        foreach ($this->data as $ip1 => &$datum) {
            if ($ip1 === $ip) {
                $client = &$datum;
                break;
            }
        }

        if ($client === null) {
            $client = (object) [
                "request_count" => 0,
                "last_request_time" => date(DATE_ATOM)
            ];
        }

        $client->request_count++;
        $this->data->$ip = $client;

        if ($client->request_count >= $this->limit && (strtotime($client->last_request_time) + $this->sec) >= time()) {
            throw new HTTPException(429, "Too Many Requests", [
                "Retry-After" => date(DATE_RFC2822, strtotime($client->last_request_time) + $this->sec + 1)
            ]);
        }

        if ((strtotime($client->last_request_time) + $this->sec) <= time()) {
            $client->request_count = 1;
        }

        $client->last_request_time = date(DATE_ATOM);
        $this->updateData();
    }
}