<?php
    class VideoRenderer {
        private $fps = 25;
        private $duration = 10;
        private $frames_count = 250;

        private $width = 100;
        private $height = 100;

        private $elements = [];
        private $temp_path = '';
        private $video_id = '';
        private $result_path = '';
        private $result_file = 'output.mp4';

        private $options = [];

        public function __construct($fps, $seconds_duration) {
            $this->fps = $fps;
            $this->duration = $seconds_duration;
            $this->frames_count = $fps * $seconds_duration;
        }
        public function setOutputSettings($width, $height) {
            $this->width = $width;
            $this->height = $height;
        }
        public function setRenderSettings($elements) {
            $this->elements = $elements;
        }
        public function setOption($key, $value) {
            $this->options[$key] = $value;
        }

        public function render($result_path = null, $result_file = null) {
            $this->temp_path = $result_path.'/temp';

            if($result_file !== null) $this->result_path = $result_path;
            if($result_file !== null) $this->result_file = $result_file;

            $this->video_id = md5('video'.rand(0, 1000000));

            $this->formatElements();
            $this->clearTempDirectory();

            for($frame_id = 1; $frame_id <= $this->frames_count; $frame_id++) {
                $im = imagecreatetruecolor($this->width, $this->height);

                $white_color = imagecolorallocate($im, 255, 255, 255);
                imagefill($im, 0, 0, $white_color);

                $this->renderElements($im, $frame_id);
                $frame_name = $this->formatFrameIdentifier($frame_id);

                imagepng($im, $this->temp_path."/frame{$frame_name}-{$this->video_id}.png");
                imagedestroy($im);
            }

            exec("ffmpeg -framerate {$this->fps} -pattern_type glob -i '{$this->temp_path}/*{$this->video_id}.png' -c:v libx264 -pix_fmt yuv420p '{$this->result_path}/{$this->result_file}'");

            $this->clearTempDirectory();
        }

        // Configuration
        private function formatElements() {
            foreach ($this->elements as &$element) {
                $config = &$element['config'];

                if(!isset($element['config'])) $element['config'] = [];
                if(!isset($element['animations'])) $element['animations'] = [];

                if(!isset($config['render_start'])) $config['render_start'] = 1;
                else $config['render_start'] = $this->formatFramesCount($config['render_start']);

                if(!isset($config['render_end'])) $config['render_end'] = $this->frames_count;
                else $config['render_end'] = $this->formatFramesCount($config['render_end']);

                if($element['type'] == 'text') {
                    $config['size'] = isset($config['size']) ? $this->formatPixelsSize($config['size']) : 10;
                }
                elseif($element['type'] == 'image') {
                    if(!isset($config['resource'])) {
                        if($config['format'] == 'jpeg') $resource = imagecreatefromjpeg($config['src']);
                        elseif($config['format'] == 'bmp') $resource = imagecreatefrombmp($config['src']);
                        else $resource = imagecreatefrompng($config['src']);

                        if($config['opacity'] < 1) {
                            imagefilter($resource, IMG_FILTER_COLORIZE, 255, 255, 255, round($this->formatAlphaColor($config['opacity'])));
                        }

                        $config['resource'] = $resource;
                    }

                    if(isset($config['width'])) $config['width'] = $this->formatPixelsSize($config['width']);
                    if(isset($config['height'])) $config['height'] = $this->formatPixelsSize($config['height']);
                }

                foreach ($element['animations'] as &$animation) {
                    if(!isset($animation['settings']['infinite'])) $animation['settings']['infinite'] = false;
                    if(!isset($animation['settings']['reverse'])) $animation['settings']['reverse'] = false;

                    $settings = $animation['settings'];
                    $keyframes = $animation['keyframes'];

                    $keyframes['to'] = $this->formatPixelsSize($keyframes['to']);
                    $keyframes['from'] = $this->formatPixelsSize($keyframes['from']);

                    $animation_frames = [];
                    $animation_frames_count = $this->formatFramesCount($settings['duration']) - 1;
                    $animation_step = round(($keyframes['to'] - $keyframes['from']) / $animation_frames_count, 2);

                    $animation_frames[] = $keyframes['from'];

                    for($animation_frame_id = 1; $animation_frame_id <= $animation_frames_count; $animation_frame_id++) {
                        $animation_time_key = round($animation_frame_id / $animation_frames_count, 2);

                        if($settings['type'] == 'linear') {
                            $result = $animation_step * $animation_frame_id + $keyframes['from'];
                        }
                        elseif($settings['type'] == 'ease-in') {
                            $result = VideoRendererUtils::countEaseIn($animation_time_key, $keyframes['from'], $animation_step, $animation_frames_count);
                        }
                        elseif($settings['type'] == 'ease-out') {
                            $result = VideoRendererUtils::countEaseOut($animation_time_key, $keyframes['from'], $animation_step, $animation_frames_count);
                        }
                        elseif($settings['type'] == 'ease-in-out') {
                            $result = VideoRendererUtils::countEaseInOut($animation_time_key);
                            $result = $animation_frames_count * $animation_step * $result + $keyframes['from'];
                        }
                        else $result = 0;

                        $animation_frames[] = round($result, 2);
                    }

                    $animation_frames_count = $animation_frames_count + 1;

                    if(isset($settings['reverse']) && $settings['reverse'] == true) {
                        $animation_frames_count = $animation_frames_count * 2;

                        $animation_frames = array_merge(
                            $animation_frames,
                            array_reverse($animation_frames)
                        );
                    }

                    $animation['frames'] = $animation_frames;
                    $animation['settings']['frames_count'] = $animation_frames_count;
                }
            }
        }
        private function renderElements(&$im, $frame_id) {
            foreach ($this->elements as &$element) {
                $config = $element['config'];

                if($config['render_start'] > $frame_id) continue;
                if($config['render_end'] < $frame_id) continue;

                if($element['type'] == 'text') {
                    $data = $this->getRenderElementData($frame_id, $element);

                    imagettftext(
                        $im,
                        $config['size'],
                        $data['rotate'],
                        $data['x'],
                        $data['y'],
                        imagecolorallocatealpha($im, $data['color']['r'], $data['color']['g'], $data['color']['b'], $data['color']['a']),
                        $config['font'],
                        $config['text']
                    );
                }
                elseif($element['type'] == 'image') {
                    $data = $this->getRenderElementData($frame_id, $element);
                    $resource = $config['resource'];
                    
                    if($data['rotate'] != 0) {
                        $resource = imagerotate($resource, $data['rotate'], imagecolorallocatealpha($resource, 255, 255, 255, 127));
                    }

                    imagecopyresampled(
                        $im,
                        $resource,
                        $data['x'],
                        $data['y'],
                        0,
                        0,
                        isset($config['width']) ? $config['width'] : imagesx($config['resource']),
                        isset($config['height']) ? $config['height'] : imagesy($config['resource']),
                        imagesx($config['resource']),
                        imagesy($config['resource'])
                    );
                }
            }
        }
        private function getRenderElementData($frame_id, $element) {
            $config = $element['config'];

            $result = [
                'x' => 0,
                'y' => 0,
                'rotate' => 0,
                'color' => [
                    'r' => 0,
                    'g' => 0,
                    'b' => 0,
                    'a' => 0
                ]
            ];

            /**
             * Handling animations settings
             */
            foreach ($element['animations'] as $animation) {
                $frames = $animation['frames'];
                $settings = $animation['settings'];

                if($settings['infinite'] == true) $animation_frame_id = ($frame_id % $settings['frames_count']) - 1;
                else $animation_frame_id = $frame_id - 1;

                if(isset($frames[$animation_frame_id])) {
                    $frame_value = $frames[$animation_frame_id];
                    $config[$settings['property']] = $frame_value;
                }
            }

            /**
             * Handling position settings
             */
            if(isset($config['top'])) $result['y'] = $this->formatPixelsSize($config['top']);
            elseif(isset($config['bottom'])) $result['y'] = $this->height - $this->formatPixelsSize($config['bottom']);

            if(isset($config['left'])) $result['x'] = $this->formatPixelsSize($config['left']);
            elseif(isset($config['right'])) $result['x'] = $this->width - $this->formatPixelsSize($config['right']);

            if(isset($config['rotate'])) {
                if($config['rotate'] > 0) $result['rotate'] = -$config['rotate'];
                elseif($config < 0) $result['rotate'] = abs($config['rotate']);
            }

            if(isset($config['color'])) {
                $result['color'] = $this->formatRgbaColor($config['color']);
            }

            if(isset($config['opacity'])) {
                $result['color']['a'] = $this->formatAlphaColor($config['opacity']);
            }

            return $result;
        }

        // Formatters
        private function formatRgbaColor($color) {
            $result = [];

            if(is_array($color)) {
                if(!isset($color[3])) $alpha = $this->formatAlphaColor(1);
                else {
                    $alpha = $this->formatAlphaColor($color[3]);
                }

                $result['r'] = $color[0];
                $result['g'] = $color[1];
                $result['b'] = $color[2];
                $result['a'] = round($alpha);
            }
            else {
                $color = str_replace('#', '', $color);
                $length = strlen($color);

                $result['r'] = hexdec($length == 6 ? substr($color, 0, 2) : ($length == 3 ? str_repeat(substr($color, 0, 1), 2) : 0));
                $result['g'] = hexdec($length == 6 ? substr($color, 2, 2) : ($length == 3 ? str_repeat(substr($color, 1, 1), 2) : 0));
                $result['b'] = hexdec($length == 6 ? substr($color, 4, 2) : ($length == 3 ? str_repeat(substr($color, 2, 1), 2) : 0));
                $result['a'] = $this->formatAlphaColor(1);
            }

            return $result;
        }
        private function formatAlphaColor($opacity) {
            return abs($opacity * 100 - 100) * (127 / 100);
        }
        private function formatFrameIdentifier($frame_id) {
            return str_pad($frame_id, strlen($this->frames_count) + 1, '0', STR_PAD_LEFT);
        }
        private function formatPixelsSize($size) {
            if(VideoRendererUtils::endsWith($size, 'px')) return (int)substr($size, 0, -2);
            else return $size;
        }
        private function formatFramesCount($time) {
            if(VideoRendererUtils::endsWith($time, 'ms')) return $this->fps * substr($time, 0, -2) / 1000;
            elseif(VideoRendererUtils::endsWith($time, 's')) return $this->fps * substr($time, 0, -1);
            elseif(VideoRendererUtils::endsWith($time, 'm')) return $this->fps * substr($time, 0, -1) * 60;
            else return $time;
        }

        private function clearTempDirectory() {
            if (!file_exists($this->temp_path)) return false;

            foreach (glob($this->temp_path.'/*.png') as $file) {
                unlink($file);
            }

            return true;
        }
    }

    class VideoRendererUtils {
        static function startsWith($haystack, $needle) {
            $length = strlen($needle);
            return (substr($haystack, 0, $length) === $needle);
        }
        static function endsWith($haystack, $needle) {
            $length = strlen($needle);
            if ($length == 0) return true;

            return substr($haystack, -$length) === $needle;
        }

        static function countEaseIn($time, $start, $step, $from) {
            return $step * $time * $time * $from + $start;
        }
        static function countEaseOut($time, $start, $step, $from) {
            return -$step * $time * ($time - 2) * $from + $start;
        }
        static function countEaseInOut($time) {
            return $time < 0.5 ? 2 * $time * $time : -1 + (4 - 2 * $time) * $time;
        }
    }