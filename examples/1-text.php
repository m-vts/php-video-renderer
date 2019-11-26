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
            'animations' => []
        ]
    ];

    $renderer = new VideoRenderer(25, 5);

    $renderer->setOutputSettings(720, 1280);
    $renderer->setRenderSettings($elements);

    $renderer->render(__DIR__ . '/result', 'test1.mp4');
