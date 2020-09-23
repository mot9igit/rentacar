<?php

return [
    'getOffers' => [
        'file' => 'getOffers',
        'description' => 'offers of users',
        'properties' => [
            'tpl' => [
                'type' => 'textfield',
                'value' => 'tpl.rentacar.item',
            ],
            'sortby' => [
                'type' => 'textfield',
                'value' => 'name',
            ],
            'sortdir' => [
                'type' => 'list',
                'options' => [
                    ['text' => 'ASC', 'value' => 'ASC'],
                    ['text' => 'DESC', 'value' => 'DESC'],
                ],
                'value' => 'ASC',
            ],
            'limit' => [
                'type' => 'numberfield',
                'value' => 10,
            ],
            'outputSeparator' => [
                'type' => 'textfield',
                'value' => "\n",
            ],
            'toPlaceholder' => [
                'type' => 'combo-boolean',
                'value' => false,
            ],
        ],
    ],
	'getActualPrices' => [
		'file' => 'getActualPrices',
		'description' => 'ActualPrices',
		'properties' => [],
	],
	'checkCity' => [
		'file' => 'checkCity',
		'description' => 'Check region',
		'properties' => [],
	],
	'getCarOptions' => [
		'file' => 'getCarOptions',
		'description' => 'getCarOptions',
		'properties' => [],
	],
	'getCarWarrantys' => [
		'file' => 'getCarWarrantys',
		'description' => 'getCarWarrantys',
		'properties' => [],
	],
	'getAutos' => [
		'file' => 'getAutos',
		'description' => 'getAutos',
		'properties' => [],
	],
	'shahmat' => [
		'file' => 'shahmat',
		'description' => 'shahmat',
		'properties' => [],
	]
];