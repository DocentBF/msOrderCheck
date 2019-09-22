<?php

return [
    'msOrderCheck' => [
        'file' => 'msordercheck',
        'description' => '',
        'properties' => [
            'tplForm' => [
                'type' => 'textfield',
                'value' => 'tpl.msOrderCheck.form',
            ],
            'tplResult' => [
                'type' => 'textfield',
                'value' => 'tpl.msOrderCheck.result',
            ],
            'tplResultWrapper' => [
                'type' => 'textfield',
                'value' => 'tpl.msOrderCheck.result.wrapper',
            ],
            'fields' => [
                'type' => 'textfield',
                'value' => 'num,phone',
            ],
            'allRequired' => [
                'type' => 'checkbox',
                'value' => 'true',
            ],
            'actionUrl' => [
                'type' => 'textfield',
                'value' => '[[+assetsUrl]]action.php',
            ],
            'showHistory' => [
                'type' => 'checkbox',
                'value' => true,
            ],
            'historySortBy' => [
                'type' => 'textfield',
                'value' => 'createdon',
            ],
            'historySortDir' => [
                'type' => 'textfield',
                'value' => 'ASC',
            ],
            'frontend_css' => [
                'type' => 'textfield',
                'value' => '[[+assetsUrl]]css/default.css',
            ],
            'frontend_js' => [
                'type' => 'textfield',
                'value' => '[[+assetsUrl]]js/default.js',
            ],
            'form' => [
                'type' => 'textfield',
                'value' => '.msOrderCheck',
            ],
            'resultBlock' => [
                'type' => 'textfield',
                'value' => '.msOrderCheckResult',
            ],

        ],
    ],
];