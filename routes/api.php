<?php

use Dingo\Api\Routing\Router;

/** @var Router $api */
$api = app(Router::class);

$api->version('v1', function (Router $api) {
    $api->group(['prefix' => 'auth'], function(Router $api) {
        $api->post('signup', 'App\\Api\\V1\\Controllers\\SignUpController@signUp');
        $api->post('login', 'App\\Api\\V1\\Controllers\\LoginController@login');
    });

    $api->group(['middleware' => 'jwt.auth'], function(Router $api) {
        $api->get('protected', function() {
            return response()->json([
                'message' => 'Access to protected resources granted! You are seeing this text as you provided the token correctly.'
            ]);
        });

        $api->get('refresh', [
            'middleware' => 'jwt.refresh',
            function() {
                return response()->json([
                    'message' => 'By accessing this endpoint, you can refresh your access token at each request. Check out this response headers!'
                ]);
            }
        ]);

        $api->group(['prefix' => 'sheets'], function(Router $api){
            $api->get('/', 'App\\Api\\V1\\Controllers\\SheetController@index');
            $api->post('/', 'App\\Api\\V1\\Controllers\\SheetController@save');
            $api->group(['middleware' => 'time.owner'], function(Router $api){
                $api->get('/{id}', 'App\\Api\\V1\\Controllers\\SheetController@show');
                $api->put('/{id}', 'App\\Api\\V1\\Controllers\\SheetController@update');
                $api->delete('/{id}', 'App\\Api\\V1\\Controllers\\SheetController@delete');
            });            
        });

        $api->get('/me', 'App\\Api\\V1\\Controllers\\UserController@me');
        $api->post('/preferred_hours', 'App\\Api\\V1\\Controllers\\UserController@preferred_hours');

        $api->group(['middleware' => 'role.manager'], function(Router $api){
            $api->group(['prefix' => 'users'], function(Router $api){
                $api->get('/', 'App\\Api\\V1\\Controllers\\UserController@index');
                $api->get('/{id}', 'App\\Api\\V1\\Controllers\\UserController@show');
                $api->post('/', 'App\\Api\\V1\\Controllers\\UserController@save');    
                $api->put('/{id}', 'App\\Api\\V1\\Controllers\\UserController@update');
                $api->delete('/{id}', 'App\\Api\\V1\\Controllers\\UserController@delete');
            });    
        });                
    });

    $api->get('hello', function() {
        return response()->json([
            'message' => 'This is a simple example of item returned by your APIs. Everyone can see it.'
        ]);
    });
});
