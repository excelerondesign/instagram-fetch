<?php
include_once $modx->getOption('getresources.core_path',null,$modx->getOption('core_path').'components/getresources/').'include.parsetpl.php';

$API_URL = 'https://graph.instagram.com/me/media?fields=';
    
$API_FIELDS = 'caption,media_url,media_type,permalink,timestamp,username';

$_access_token = $modx->getOption('instagram_access_token');
$max_photos = $modx->getOption('max', $scriptProperties, 8);
$tpl = $modx->getOption('tpl', $scriptProperties, '');

$max_photos = intval($max_photos);

$_requestUrl = API_URL . API_FIELDS . '&access_token=' . $_access_token;

$rest_client = $modx->getService('rest', 'rest.modRest');
$response = $rest_client->get($_requestUrl);

$response_info = $response->responseInfo;
$response_error = $response->responseError;
$response_array = $response->process();

if (!empty($response_error)) {
    $modx->log(xPDO::LOG_LEVEL_ERROR, 'ERROR: _makeGraphRequest');
    $modx->log(xPDO::LOG_LEVEL_ERROR, print_r($response_error));
    return;
}

if (empty($response_array)) {
    $modx->log(xPDO::LOG_LEVEL_ERROR, 'ERROR: _makeGraphRequest - No results returned');
    return;
}

$results = $response_array['data'];
$output = [];

$i = 0;
foreach($results as $item) {
    $isImage = $item['media_type'] == "IMAGE";
    
    if (!$isImage) continue;
    
    if ($i == $max) break;
    $i++;
    
    $type = $item['media_type'];
    $src = $item['media_url'];
    $caption = $item['caption'];
    $link = $item['permalink'];
    
    $tplProperties = [
        'idx' => $i,
        'type' => $type,
        'src' => $src,
        'link' => $link,
        'caption' => $caption
    ];
    
    $resourceTpl = parseTpl($tpl, $tplProperties);
    
    $output[]= $resourceTpl;
}
$output = implode('', $output);

return $output;