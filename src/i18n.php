<?php

namespace AEngine\Orchid\Misc {

    use AEngine\Orchid\App;
    use AEngine\Orchid\Exception\FileNotFoundException;
    use AEngine\Orchid\Exception\NullPointException;

    class i18n
    {
        /**
         * Buffer storage of the language file
         *
         * @var array
         */

        public static $locale = [];

        /**
         * i18n constructor
         *
         * @param array $config
         */
        public static function setup(array $config = [])
        {
            $default = [
                'locale'  => null,
                'default' => null,
                'force'   => null,
            ];
            $config  = array_merge($default, $config);

            if ($config['default'] && !$config['locale']) {
                $config['locale'] = $config['default'];
            }

            if ($config['force'] && $config['default'] != $config['force']) {
                $config['locale'] = $config['force'];
            }

            if (!$config['locale']) {
                throw new NullPointException('Locale is null');
            }

            static::$locale = static::load($config['locale']);
        }

        /**
         * Get language code from header
         *
         * @param string $header
         * @param string $default
         * @return mixed|string
         */
        public static function getLanguageFromHeader($header, $default = 'en')
        {
            preg_match_all('~(?<lang>\w+(?:\-\w+|))(?:\;q=(?<q>\d(?:\.\d|))|)[\,]{0,}~i', $header, $list);

            $data = [];
            foreach (array_combine($list['lang'], $list['q']) as $key => $priority) {
                $data[$key] = (float)($priority ? $priority : 1);
            }
            arsort($data, SORT_NUMERIC);

            return $data ? key($data) : $default;
        }

        /**
         * Load language file for specified local
         *
         * @param string $locale
         *
         * @return array
         * @throws FileNotFoundException
         */
        protected static function load($locale)
        {
            $app = App::getInstance();

            foreach (['php', 'ini'] as $type) {
                $path = $app->path('lang:' . trim($locale) . '.' . $type);

                if ($path && file_exists($path)) {
                    switch ($type) {
                        case 'ini': return parse_ini_file($path, true);
                        case 'php': return require_once $path;
                    }
                }
            }

            throw new FileNotFoundException('Could not find a language file');
        }
    }
}

namespace {

    use AEngine\Orchid\Exception\NullPointException;
    use AEngine\Orchid\Misc\i18n;

    class L
    {
        /**
         * Returns internationalized text for the specified key
         *
         * @param $key
         *
         * @return mixed
         */
        public static function get($key)
        {
            if (isset(i18n::$locale[$key])) {
                return i18n::$locale[$key];
            }

            throw new NullPointException('Key "' . $key . '" not found in locale file');
        }
    }
}
