<?php

class Instagram {
    const API_URL = 'https://graph.instagram.com/me/media?fields=';

    const API_FIELDS = 'caption,media_url,media_type,permalink,timestamp,username';

    private $access_token;

    private $request_url;

    public $modx;

    public function __construct(\modX $modx, string $token) {
        $this->modx = $modx;
        $this->setToken($token);
        $this->setRequestUrl();
    }

    public function setRequestUrl() {
        $this->request_url = self::API_URL . self::API_FIELDS . '&access_token=' . $this->getToken();
    }

    public function getRequestUrl():string {
        return $this->request_url;
    }

    public function setToken($token) {
        $this->access_token = $token;
    }

    public function getToken():string {
        return $this->access_token;
    }
    
    /**
     * makeGraphRequest
     * Get the media from the Instagram Basic Display API
     *
     * @return array
     */
    public function makeGraphRequest():array{
        $rest_client = $this->modx->getService('rest', 'rest.modRest');
        $request = $this->getRequestUrl();
        $response = $rest_client->get($request);

        $response_info = $response->responseInfo;
        $response_error = $response->responseError;
        $response_array = $response->process();

        if (!empty($response_error)) {
            $this->modx->log(xPDO::LOG_LEVEL_ERROR, 'ERROR: _makeGraphRequest');
            print_r($response_err);
            return [];
        }

        if (empty($response_array)) {
            $this->modx->log(xPDO::LOG_LEVEL_ERROR, 'ERROR: _makeGraphRequest - No results returned');
            return [];
        }

        return $response_array['data'];
    }

    /**
     * makeElement
     * Makes the template based on the passed settings, stores in a persistent cache in case of reuse
     * @param array $cache
     * @param [???] $tpl
     * @param string $type
     * @param array $properties
     * @return void
     */
    public function makeElement(string $tpl, string $type, array $properties = null) {
        $output = false;
        switch ($type) {
            case '@INLINE':
                $uid = uniqid();
                $chunk = $this->modx->newObject('modChunk', ['name' => "{$type}-{$uid}"]);
                $chunk->setCacheable(false);
                $output = $chunk->process($properties, $tpl);
                break;
            case '@CHUNK':
            default:
                $chunk = $this->modx->getObject('modChunk', ['name' => $tpl]);
                $chunk->setCacheable(false);
                $output = $chunk->process($properties);
                break;
        }
        return $output;
    }

    /**
     * makePhotos
     * Generates each instagram photo based on the template given
     * @param string $tpl
     * @param string $type
     * @param array $data
     * @param integer $max
     * @return string
     */
    public function makePhotos(string $tpl, string $type, array $data, int $max) {
        $i = 0;
        $output = [];
        foreach($data as $item) {
            if ($item['media_type'] !== 'IMAGE') continue;
            if ($i == $max) break;
            $i++;

            $output[]= $this->makeElement($tpl, $type, [
                'idx' => $i,
                'type' => $item['media_type'],
                'src' => $item['media_url'],
                'link' => $item['permalink'],
                'caption' => $item['caption'],
            ]);
            
        }
        $photos = implode('', $output);
        
        return $photos;
    }

    /**
     * getPhotos
     * The instantiation function. Once the class has been constructed, call getPhotos to make the
     * necessary requests and create the templates
     * 
     * @param string $tpl // might not actually give a string, needs further testing
     * @param integer $max
     * @return string
     */
    public function getPhotos(string $tpl, int $max) {
        if (empty($tpl)) return false;

        $type = '@CHUNK';

        if (strpos($tpl, '@INLINE:') === 0) {
            $endPos = strpos($tpl, ':');
            $type = '@INLINE';
            $tpl = substr($tpl, $endPos + 1);
        }

        $response = $this->makeGraphRequest();
        $elements = $this->makePhotos($tpl, $type, $response, $max);

        return $elements;
    }
}