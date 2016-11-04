<?php

namespace AEngine\Orchid\Misc;

/**
 * @method static string text(string $name, array $options = [])
 * @method static string search(string $name, array $options = [])
 * @method static string url(string $name, array $options = [])
 * @method static string email(string $name, array $options = [])
 * @method static string tel(string $name, array $options = [])
 * @method static string password(string $name, array $options = [])
 * @method static string number(string $name, array $options = [])
 * @method static string range(string $name, array $options = [])
 * @method static string time(string $name, array $options = [])
 * @method static string date(string $name, array $options = [])
 * @method static string datetime(string $name, array $options = [])
 * @method static string week(string $name, array $options = [])
 * @method static string month(string $name, array $options = [])
 * @method static string color(string $name, array $options = [])
 * @method static string textarea(string $name, array $options = [])
 * @method static string checkbox(string $name, array $options = [])
 * @method static string radio(string $name, array $options = [])
 * @method static string submit(string $name, array $options = [])
 * @method static string reset(string $name, array $options = [])
 * @method static string button(string $name, array $options = [])
 * @method static string file(string $name, array $options = [])
 * @method static string hidden(string $name, array $options = [])
 */
class Form
{
    /**
     * An array of supported types
     *
     * @var array
     */
    protected static $type = [
        'text', 'search', 'url', 'email', 'tel', 'password',
        'number', 'range',
        'time', 'date', 'datetime', 'week', 'month',
        'color',
        'textarea',
        'checkbox', 'radio', 'select',
        'submit', 'reset', 'button', 'file',
        'hidden',
    ];

    /**
     * @param string $type
     * @param array  $args
     *
     * @return string|null
     */
    public static function __callStatic($type, $args)
    {
        if (in_array($type, static::$type)) {
            if (count($args) == 2) {
                list($name, $data) = $args;
            } else {
                $name = reset($args);
                $data = [];
            }

            return static::render(array_merge($data, ['name' => $name, 'type' => $type]));
        }

        return null;
    }

    /**
     * @param array $data
     *
     * @return string
     */
    protected static function render(array $data = [])
    {
        $default = [
            'method'       => 'post',
            'id'           => null,
            'class'        => [],
            'error'        => '',
            'style'        => null,
            'type'         => 'text',
            'name'         => null,
            'data'         => [],
            'placeholder'  => null,
            'tabindex'     => null,
            'form'         => null,
            'list'         => null,
            'readonly'     => false,
            'disabled'     => false,
            'required'     => false,
            'autofocus'    => false,
            'autocomplete' => null,
        ];
        $form = '';

        // determine the type of the required form
        switch ($data['type']) {
            case 'textarea':
                $attr = [
                    'maxlength' => null,
                    'cols'      => null,
                    'rows'      => null,
                    'wrap'      => null,
                ];
                $data = array_merge($default, $attr, $data);

                $form .= '<textarea ' . static::getAttr($data, ['value', 'type']) . '>';
                $form .= isset($data['value']) ? $data['value'] : '';
                $form .= '</textarea>';

                break;
            case 'select':
                $attr = [
                    'option'   => [],
                    'selected' => null,
                    'multiple' => false,
                ];
                $data = array_merge($default, $attr, $data);

                $form .= '<select  ' . static::getAttr($data) . '>';
                foreach ($data['option'] as $key => $val) {
                    $form .= '<option';
                    $form .= ' value="' . $key . '"';

                    if ($data['selected'] && $data['selected'] == $key) {
                        $form .= ' selected';
                    }

                    $form .= '>';
                    $form .= $val;
                    $form .= '</option>';
                }
                $form .= '</select>';

                break;
            default:
                // select a specific type
                switch ($data['type']) {
                    case 'radio':
                    case 'checkbox':
                        $attr = [
                            'value'   => null,
                            'checked' => false,
                        ];

                        break;
                    case 'file':
                        $attr = [
                            'value'    => null,
                            'accept'   => null,
                            'multiple' => false,
                        ];

                        break;
                    case 'number':
                    case 'range':
                    case 'date':
                    case 'week':
                    case 'month':
                        $attr = [
                            'value' => null,
                            'max'   => null,
                            'min'   => null,
                            'step'  => null,
                        ];

                        break;
                    case 'datetime':
                        $attr = [
                            'value' => null,
                            'max'   => null,
                            'min'   => null,
                            'step'  => null,
                        ];
                        $data['type'] = 'datetime-local';

                        break;
                    default:
                        $attr = [
                            'value'     => null,
                            'maxlength' => null,
                            'pattern'   => null,
                        ];

                        break;
                }
                $data = array_merge($default, $attr, $data);
                $form .= '<input ' . static::getAttr($data) . ' />';

                break;
        }

        return $form;
    }

    /**
     * Method for generating an auxiliary attributes and properties
     *
     * @param array $data
     * @param array $exclude
     *
     * @return string
     */
    protected static function getAttr(array &$data = [], array $exclude = [])
    {
        $attr = [];

        // substituted values
        switch (strtolower($data['method'])) {
            case 'get':
                if (isset($_GET[$data['name']])) {
                    if (in_array($data['type'], ['radio', 'checkbox'])) {
                        if ($_GET[$data['name']] == $data['value']) {
                            $data['checked'] = true;
                        }
                    } else {
                        $data['value'] = $_GET[$data['name']];
                    }
                }
                break;
            case 'post':
                if (isset($_POST[$data['name']])) {
                    if (in_array($data['type'], ['radio', 'checkbox'])) {
                        if ($_POST[$data['name']] == $data['value']) {
                            $data['checked'] = true;
                        }
                    } else {
                        $data['value'] = $_POST[$data['name']];
                    }
                }
                break;
        }

        if ($data['error']) {
            $data['class'][] = 'error';
        }
        if ($data['class']) {
            $data['class'] = implode(' ', (is_array($data['class']) ? $data['class'] : [$data['class']]));
        }
        if ($data['data']) {
            foreach ($data['data'] as $key => $value) {
                $data['data-' . $key] = $value;
            }
        }

        $exclude = array_merge($exclude, ['data', 'method', 'option', 'selected', 'error']);
        foreach ($data as $key => $value) {
            if (in_array($key, $exclude) || is_array($value)) {
                continue;
            }

            if (is_bool($value) && $value) {
                $attr[] = $key;
            } elseif (!is_bool($value) && !is_null($value)) {
                $attr[] = $key . '=\"' . $value . '\"';
            }
        }

        return implode(' ', $attr);
    }

    /**
     * @param string $name
     * @param array  $option
     * @param array  $data
     *
     * @return string
     */
    public static function select($name, array $option = [], array $data = [])
    {
        return static::render(array_merge($data, ['name' => $name, 'type' => 'select', 'option' => $option]));
    }
}
