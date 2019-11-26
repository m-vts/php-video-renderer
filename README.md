# php-video-renderer

Библиотека для рендеринга небольших видео с использованием PHP GD и ffmpeg.

На вход подается список из элементов, которые будут использованы в видео.

#### Пример использования
```php
$elements = [
   [
        'type' => 'text',
        'config' => [
            'size' => '25px',
            'color' => '#000000',
            'font' => __DIR__.'/files/OpenSans.ttf',
            'text' => 'text',
        ],
        'animations' => [
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
    ]
];

$renderer = new VideoRenderer(60, 5);

$renderer->setOutputSettings(720, 1280);
$renderer->setRenderSettings($elements);

$renderer->render(__DIR__ . '/', 'output.mp4');
```

#### Пример структуры элемента

```json
{
    "type": "text",
    "config": {
          "size": "25px",
          "color": "#000000",
          "font": "/files/OpenSans.ttf",
          "text": "Some text"
    },
    "animations": []
}
```


### Элемент "text"

| Параметр | Описание | |
| -------- | --------- | --------- |
| size     | Размер в пикселях | Обязателен |
| color    | Цвет в HEX, RGB или RGBA (`массив, до 4 элементов: [r,g,b,a]`) | Обязателен |
| font     | Путь до файла со шрифтом | Обязателен |
| text     | Отображаемый текст | Обязателен |

## Элемент "image"

| Параметр | Описание | |
| -------- | --------- | --------- |
| resource | Ресурс изображения | Обязателен, если не указаны `url` и `type` |
| url      | URL изображения | Обязателен, если не указан `resource` |
| type     | Тип изображения (`png`, `jpeg`, `bmp`) | Обязателен, если не указан `resource` |
| width    | Ширина в пикселях |  |
| height   | Высота в пикселях |  |

## Прочие необязательные параметры

| Параметр | Описание | 
| -------- | --------- |
| top / bottom | Отступ от края кадра по вертикали |
| left / right | Отступ от края кадра по горизонтали |
| opacity      | Степень прозрачности (от 0.01 до 1.00) |
| rotate       | Градус поворота по часовой стрелке (от 0 до 359) |
| render_start | Время начала рендеринга элемента, в секундах |
| render_end   | Время окончания рендеринга элемента (60s / 1000ms / 1m) |
| vertical_align  | Опция выравнивания объекта по вертикали (start, center, end) |
| horizontal_align  | Опция выравнивания объекта по горизонтали (start, center, end) |

## Структура анимации
| Настройка | Тип | Описание |
| -------- | ---- | --- |
| infinite  | `bool` | Опция для бесконечного повторения анимации | 
| reverse | `bool` | Опция для обратного проигрывания анимации после выполнения |
| duration | `int` / `string` | Длина в секундах, миллисикундах или минутах |
| type | `string` | Тип анимации (`linear`, `ease-in`, `ease-out`, `ease-in-out`) |
| proprty | `string` | Название свойства, изменяемого анимацией |
