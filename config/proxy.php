<?php

return [
	// Default proxy connection.
    'default' => env('PROXY_ENDPOINT', 'default'),

    'endpoints' => [
    	'default' => [
		    'allowed_headers' => [
		        'authorization',
		        'accept-language',
		        'accept-encoding',
		        'accept',
		        'cookie',
		        'user-agent',
		        'upgrade-insecure-requests',
		        'cache-control',
		        'content-type',
		        'origin',
		        'referer',
		        'sec-fetch-dest',
		        'sec-fetch-mode',
		        'sec-fetch-site',
		        'user-agent',
		        'pragma',
		    ],

		    'forbidden_headers' => [
		    	'accept-encoding', //Because Guzzle might not accept some encodings, example: https://github.com/guzzle/guzzle/issues/2028
		    ],

		    'url' => env('PROXY_URL'),
    	],
    ]
];
