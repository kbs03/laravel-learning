$api_call = Request::create($api_url, $api_method, $api_par, [], [], $_SERVER);
$response = app()->handle($api_call)->getContent();
// $response = Route::dispatch($api_call);
return $response;


public static function create($uri, $method = 'GET', $parameters = [], $cookies = [], $files = [], $server = [])
====>> for callingapi in same project


composer require laravel/passport --ignore-platform-req=ext-sodium ==> removes version comptibility error



dont wants to add manuallyall timecontroller than define
providers=>authserviceprovider