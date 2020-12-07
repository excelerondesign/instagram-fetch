<?php

/**
 * General Authorization Flow is as such:
 * 
 * Client logs in with the Auth Login: Link InstagramAuth->getLoginLink()
 * After logging into Instagram with the window that pops up, it will redirect to the redirect
 */


class InstagramAuth {
    const API_OAUTH_URL = 'https://api.instagram.com/oauth/authorize';

    const API_OAUTH_TOKEN_URL = 'https://api.instagram.com/oauth/access_token';

    const API_OAUTH_EXCHANGE_URL = 'https://graph.instagram.com/access_token';

    const API_TOKEN_REFRESH_URL = 'https://graph.instagram.com/refresh_access_token';

    private static $app_id;

    private $app_secret;

    private $redirect_uri;

    private $access_token;

    public function __construct(\modX $modx) {
        $this->app_id = $modx->getOption('instagram_app_id');
        $this->app_secret = $modx->getOption('instagram_app_secret');
        $this->redirect_uri = $modx->getOption('site_url');
        $this->rest_client = $modx->getService('rest', 'rest.modRest');
    }
    
    public function getLoginLink():string {
        return self::API_OAUTH_URL . '?client_id=' . $this->app_id . '&redirect_uri=' . urlencode($this->redirect_uri) . '&scope=user_profile,user_media&response_type=code';
    }
    
    /**
     * Gets the "Long Lived" token - Expiration 2 months
     */
    public function getLongLivedToken(string $token) {
        
    }

    public function refreshAccessToken(string $token) {
        $params = [
            'grant_type' => 'ig_refresh_token',
            'access_token' => $token
        ];

        
    }
}