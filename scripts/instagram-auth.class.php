<?php

class InstagramAuth {
    const API_OAUTH_URL = 'https://api.instagram.com/oauth/authorize';

    const API_OAUTH_TOKEN_URL = 'https://api.instagram.com/oauth/access_token';

    const API_OAUTH_EXCHANGE_URL = 'https://graph.instagram.com/access_token';

    const API_TOKEN_REFRESH_URL = 'https://graph.instagram.com/refresh_access_token';

    private $app_id;

    private $app_secret;

    private $redirect_uri;

    private $access_token;

    public function __construct(\modX $modx) {
        $this->app_id = $modx->getOption('instagram_app_id');
        $this->app_secret = $modx->getOption('instagram_app_secret');
    }

    public function __get(string $name) {
        return this[$name]
    }

    public function getLoginLink() {
        return self::API_OAUTH_URL . '?client_id=' . $this.__get('app_id') . '&redirect_uri=' . urlencode($this->__get('redirect_uri')) . '&scope=user_profile,user_media&response_type=code';
    }
}