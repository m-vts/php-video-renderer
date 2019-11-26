<?php
    include './src/renderer.php';

    $elements = [
        // Картинка в оригинальном размере
        [
            'type' => 'image',
            'config' => [
                'src' => 'https://vk.com/sticker/1-14084-512',
                'format' => 'png', // jpeg, bpm

                'top' => '100px',
                'left' => '100px',
            ],
            'animations' => []
        ],
        // Картинка, ужатая до 100 x 100px
        [
            'type' => 'image',
            'config' => [
                'src' => 'https://vk.com/sticker/1-14084-256',
                'format' => 'png', // jpeg,

                'top' => '100px',
                'left' => '100px',

                'height' => '100px',
                'width' => '100px',
            ],
            'animations' => []
        ],
        // Картинка с загрузкой через ресурс
        [
            'type' => 'image',
            'config' => [
                'resource' => imagecreatefrompng('https://vk.com/sticker/1-14084-256'),

                'top' => '100px',
                'left' => '100px',
            ],
            'animations' => []
        ],
    ];

    $renderer = new VideoRenderer(25, 5);

    $renderer->setOutputSettings(720, 1280);
    $renderer->setRenderSettings($elements);

    $renderer->render(__DIR__ . '/result', 'test2.mp4');
