<?php
    include './src/renderer.php';

    $elements = [
        [
            'type' => 'text',
            'config' => [
                'size' => '25px',
                'color' => '#000000',
                'font' => __DIR__.'/files/OpenSans.ttf',
                'text' => 'Some text',

                'top' => '100px',
                'left' => '50px'
            ],
            'animations' => [
                // Разовое перемещение текста по вертикали от 100px до 200px и обратно
                [
                    'settings' => [
                        'infinite' => false,
                        'reverse' => true,
                        'duration' => '1s',
                        'type' => 'linear', // ease-in, ease-out, ease-in-out
                        'property' => 'top'
                    ],
                    'keyframes' => [
                        'from' => '100px',
                        'to' => '200px'
                    ]
                ]
            ]
        ],
        [
            'type' => 'text',
            'config' => [
                'size' => '25px',
                'color' => '#000000',
                'font' => __DIR__.'/files/OpenSans.ttf',
                'text' => 'Some text',

                'top' => '100px',
                'left' => '50px'
            ],
            'animations' => [
                // Бексонечные перемещения текста по вертикали от 100px до 200px и обратно
                [
                    'settings' => [
                        'infinite' => true,
                        'reverse' => true,
                        'duration' => '1s',
                        'type' => 'ease-in-out',
                        'property' => 'top'
                    ],
                    'keyframes' => [
                        'from' => '100px',
                        'to' => '200px'
                    ]
                ]
            ]
        ],
        [
            'type' => 'text',
            'config' => [
                'size' => '25px',
                'color' => '#000000',
                'font' => __DIR__.'/files/OpenSans.ttf',
                'text' => 'Some text',

                'top' => '100px',
                'left' => '50px'
            ],
            'animations' => [
                // Бексонечные изменения свойства прозрачности в течение 2 секунд
                [
                    'settings' => [
                        'infinite' => true,
                        'reverse' => true,
                        'duration' => '200ms',
                        'type' => 'ease-in-out',
                        'property' => 'opacity'
                    ],
                    'keyframes' => [
                        'from' => 1.00,
                        'to' => 0.1
                    ]
                ]
            ]
        ]
    ];

    $renderer = new VideoRenderer(25, 5);

    $renderer->setOutputSettings(720, 1280);
    $renderer->setRenderSettings($elements);

    $renderer->render(__DIR__ . '/result', 'test3.mp4');
