<?php

namespace Tests\Traits;

trait InteractWithDomain
{
    private $domain;

    public function setUpDomain(string $domain, $defaultScheme = 'https')
    {
        $parsedUrl = parse_url($domain);

        $this->domain['scheme']   = ($parsedUrl['scheme'] ?? $defaultScheme) . '://';
        $this->domain['host']     = $parsedUrl['host'] ?? '';
        $this->domain['port']     = isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '';
        $this->domain['user']     = $parsedUrl['user'] ?? '';
        $this->domain['pass']     = isset($parsedUrl['pass']) ? ':' . $parsedUrl['pass']  : '';
        $this->domain['pass']     = ($this->domain['user']  || $this->domain['pass']) ? $this->domain['pass'] . '@' : '';
        $this->domain['path']     = $parsedUrl['path'] ?? '';
        $this->domain['path']     = rtrim($this->domain['path'], '/');
        $this->domain['query']    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
    }

    public function getUrl(string $relativePath): string
    {
        $scheme = $this->domain['scheme'];
        $user = $this->domain['user'];
        $pass = $this->domain['pass'];
        $host = $this->domain['host'];
        $port = $this->domain['port'];
        $path = $this->domain['path'];
        $query = $this->domain['query'];

        return "$scheme$user$pass$host$port$path$relativePath$query";
    }
}
