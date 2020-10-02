<?php

class Instagram {
    const API_URL = 'https://graph.instagram.com/me/media?fields=';

    const API_FIELDS = 'caption,media_url,media_type,permalink,timestamp,username';

    private $_access_token;

    private $_request_url;

    public $modx;

    public function __construct(\modX $modx) {
        $this->modx = $modx;
        $this->setToken();
        $this->setRequestUrl();
    }

    public function setRequestUrl() {
        $this->_request_url = self::API_URL . self::API_URL . '&access_token=' . $this->getToken();
    }

    public function getRequestUrl():string {
        return $this->_request_url;
    }

    public function setToken() {
        $this->_access_token = $modx->getOption('instagram_access_token');
    }

    public function getToken():string {
        return $this->_access_token;
    }

    public function makeGraphRequest() {
        $rest_client = $this->modx->getService('rest', 'rest.modRest');
        $request = $this->getRequestUrl();
        $response = $rest_client->get($request);

        $response_info = $response->responseInfo;
        $response_error = $response->responseError;
        $response_array = $response->process();

        if (!empty($response_error)) {
            $this->modx->log(xPDO::LOG_LEVEL_ERROR, 'ERROR: _makeGraphRequest');
            $this->modx->log(xPDO::LOG_LEVEL_ERROR, print_r($response_err));
            return;
        }

        if (empty($response_array)) {
            $this->modx->log(xPDO::LOG_LEVEL_ERROR, 'ERROR: _makeGraphRequest - No results returned');
            return;
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
    public function makeElement(&$cache, $tpl, string $type, array $properties = null) {
        $output = false;
        $content = false;
        switch ($type) {
            case '@INLINE':
                $uid = uniqid();
                $chunk = $this->modx->newObject('modChunk', array('name' => "{$type}-{$uid}"));
                $chunk->setCacheable(false);
                $output = $chunk->process($properties, $tpl);
                break;
            case '@CHUNK':
            default:
                $chunk = null;
                if (!isset($cache['@CHUNK'])) $cache['@CHUNK'] = [];
                if (!array_key_exists($tpl, $cache['@CHUNK'])) {
                    if ($chunk = $this->modx->getObject('modChunk', array('name' => $tpl))) {
                        $cache['@CHUNK'][$tpl] = $chunk->toArray('', true);
                    } else {
                        $cache['@CHUNK'][$tpl] = false;
                    }
                } elseif (is_array($cache['@CHUNK'][$source])) {
                    $chunk = $modx->newObject('modChunk');
                    $chunk->fromArray($cache['@CHUNK'][$tpl], '', true, true, true);
                }
                if (is_object($chunk)) {
                    $chunk->setCacheable(false);
                    $output = $chunk-> process($properties);
                }
                break;
        }
        return $output;
    }

    /**
     * makePhotos
     * Generates each instagram photo based on the template given
     * @param [???] $tpl
     * @param string $type
     * @param array $data
     * @param integer $max
     * @return string
     */
    public function makePhotos($tpl, string $type, array $data, int $max = 8):string {
        static $_tplCache;
        $i = 0;
        $output = [];
        foreach($data as $item) {
            if ($item['media_type'] != 'IMAGE') continue;
            if ($i === $max) break;
            $i++;

            $output[]= $this->makeTpl($_tplCache, $tpl, $type, [
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
     * @param [???] $tpl
     * @param integer $max
     * @return string
     */
    public function getPhotos($tpl, int $max) {
        if (empty($tpl)) return false;

        $tplSettings = [
            'type' => '@CHUNK',
            'value' => $tpl
        ];

        if (strpos($tpl, '@INLINE:') === 0) {
            $endPos = strpos($tpl, ':');
            $tplSettings['type'] = '@INLINE';
            $tplSettings['value'] = substr($tpl, $endPos + 1);
        }
        if (!is_array($tplSettings) || !isset($tplSettings['type']) || !isset($tplSettings['value'])) return false;

        $response = $this->makeGraphRequest();

        $elements = $this->makePhotos($tplSettings['value'], $tplSettings['type'], $response, $max);

        return $elements;
    }
}