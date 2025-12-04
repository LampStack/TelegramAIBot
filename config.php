<?php

header('Content-type: text/html; charset=UTF-8');
date_default_timezone_set('Asia/Tehran');
ini_set("log_errors", "off");
error_reporting(0);

$DB = [
    'dbname' => 'xxxxxxxxx',
    'username' => 'xxxxxxxxx',
    'password' => 'xxxxxxxxx'
];

$bytez_api_key = 'xxxxxxxxx';

$bytez_base_url = 'https://api.bytez.com/models/v2/';

$apiKey = '0000000000:xxxxxxxxxxxxxxxxxxxxxxxxxxx';

$models = [
    'chat' => [
        'openai/gpt-4o',
        'openai/gpt-4o-mini',
        'openai/gpt-4',
        'openai/gpt-4.1',
        'openai/gpt-4.1-mini',
        'openai/gpt-3.5-turbo',
        'openai/gpt-5',
        'google/gemini-2.5-flash',
        'google/gemini-2.5-flash-lite',
    ],
    
    'text_to_image' => [
        'openai/dall-e-2',
        'google/imagen-4.0-ultra-generate-001',
    ],
];