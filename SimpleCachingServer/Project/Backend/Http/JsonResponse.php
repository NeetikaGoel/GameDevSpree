<?php

declare(strict_types=1);

//so this is for representing http response
class JsonResponse
{
    private int $statusCode;
    private array $body; //for the response body
    private array $headers;

    //add constructor now
    public function __construct(array $body,int $statusCode=200,array $headers=[])
    {
        $this->body=$body;
        $this->statusCode=$statusCode;
        $this->headers=$headers;
    }

    public function send():void
    {
        http_response_code($this->statusCode);

        header('Content-Type:application/json');

        foreach ($this->headers as $name=>$value) {
            header($name . ':' . $value);
        }

        echo json_encode($this->body,JSON_PRETTY_PRINT);
    }


    //add getters now
    public function getStatusCode():int
    {
        return $this->statusCode;
    }

    public function getBody():array
    {
        return $this->body;
    }
}