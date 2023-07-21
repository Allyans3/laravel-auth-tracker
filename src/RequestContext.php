<?php

namespace ALajusticia\AuthTracker;

use ALajusticia\AuthTracker\Factories\IpProviderFactory;
use ALajusticia\AuthTracker\Factories\ParserFactory;
use ALajusticia\AuthTracker\Interfaces\IpProvider;
use ALajusticia\AuthTracker\Interfaces\UserAgentParser;
use Illuminate\Support\Facades\Request;

class RequestContext
{
    /**
     * @var UserAgentParser $parser
     */
    protected $parser;

    /**
     * @var IpProvider $ipProvider
     */
    protected $ipProvider = null;

    /**
     * @var string $userAgent
     */
    public $userAgent;

    /**
     * @var string|null $ip
     */
    public $ip;

    /**
     * RequestContext constructor.
     *
     * @throws \Exception|\GuzzleHttp\Exception\GuzzleException
     */
    public function __construct()
    {
        // Initialize the parser
        $this->parser = ParserFactory::build(config('auth_tracker.parser'));

        // Initialize the IP provider
        $this->ipProvider = IpProviderFactory::build(config('auth_tracker.ip_lookup.provider'));

        $this->userAgent = Request::userAgent();
        $this->ip = self::getIp();
    }

    /**
     * @return string|null
     */
    public function getIp(): ?string
    {
        foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key){
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip); // just to be safe

                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false){
                        return $ip;
                    }
                }
            }
        }
        return request()->ip(); // it will return the server IP if the client IP is not found using this method.
    }

    /**
     * Get the parser used to parse the User-Agent header.
     *
     * @return UserAgentParser
     */
    public function parser()
    {
        return $this->parser;
    }

    /**
     * Get the IP lookup result.
     *
     * @return IpProvider
     */
    public function ip()
    {
        if ($this->ipProvider && $this->ipProvider->getResult()) {
            return $this->ipProvider;
        }

        return null;
    }
}
