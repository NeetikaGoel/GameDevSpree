<?php

declare(strict_types=1);

class CacheItem
{
    private string $key;
    private $value;
    private int $created_at; //first time inserted
    private int $updated_at; //updated on overwrite
    private int $ttl;
    private int $expires_at; //now time + ttl

    //ADD GETTERS AND SETTERS TOO!!

    public function __construct(string $key,$value,int $ttl)
    {
        $this->key=$key;
        $this->value=$value;
        $this->ttl=$ttl;

        $now=time();

        $this->created_at=$now;
        $this->updated_at=$now;
        $this->expires_at=$now + $ttl;
    }

    public function isExpired(): bool
    {
        return time()>=$this->expires_at;
    }

    public function toArray():array //formats for API response
    {
        return [
            'key'=>$this->key,
            'value'=>$this->value,
            'ttl'=>$this->ttl,
            'created_at'=>gmdate('c',$this->created_at),
            'updated_at'=>gmdate('c',$this->updated_at),
            'expires_at'=>gmdate('c',$this->expires_at)
        ];
    }
}
