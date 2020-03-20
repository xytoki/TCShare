<?php
namespace xyToki\xyShare\Providers;
use xyToki\xyShare\authProvider;
use xyToki\xyShare\contentProvider;
use xyToki\xyShare\Errors\NoPermission;
use xyToki\xyShare\Errors\NotAuthorized;
use xyToki\xyShare\Errors\NotConfigured;
use Tsk\OneDrive\Client;
use Tsk\OneDrive\Services\OAuth2;

class OnedriveCNClient extends Client{
    const API_BASE_PATH = 'https://microsoftgraph.chinacloudapi.cn/v1.0/';
    const AUTH_URL  = 'https://login.partner.microsoftonline.cn/common/oauth2/v2.0/authorize';
    const TOKEN_URL = 'https://login.partner.microsoftonline.cn/common/oauth2/v2.0/token';
    
    public function __construct(array $config = array())
    {
        $this->config = array_merge([
            'client_id'     => '',
            'client_secret' => '',
            'redirect_uri'  => null,
            'base_uri'      => self::API_BASE_PATH
        ], $config);
    }
    
    protected function createOAuth2Service()
    {
        $auth = new OAuth2(
            [
                'client_id'           => $this->getClientId(),
                'client_secret'       => $this->getClientSecret(),
                'authorizationUri'   => self::AUTH_URL,
                'tokenCredentialUri' => self::TOKEN_URL,
                'redirect_uri'        => $this->getRedirectUri(),
                'base_uri'           => self::API_BASE_PATH
            ]
        );

        return $auth;
    }
}

class onedriveCN extends onedrive {
    function __construct($options){
        $this->keyPrefix="onedriveCN";
        $this->client = new OnedriveCNClient();
        $this->init($options);
    }
}
class onedriveCNAuth extends onedriveAuth {
    function __construct($options){
        $this->keyPrefix="onedriveCN";
        $this->client = new OnedriveCNClient();
        $this->init($options);
    }
}