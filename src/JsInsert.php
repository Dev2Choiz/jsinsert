<?php

namespace JsInsert;

class JsInsert
{
    /** @var  array $scripts */
    public static $scripts = [];
    /** @var  array $variable */
    public static $variables = [];
    /** @var  array $cssVariables */
    public static $cssVariables = [];

    public function __construct()
    {
        self::reset();
    }

    public static function reset($scripts = true, $cssVariables = true, $jsVariables = true)
    {
        $cssVariables && self::$cssVariables = [];
        $jsVariables  && self::$variables    = [];
        $scripts      && self::$scripts      = [];
    }

    /**
     * @param string $script
     */
    public static function addScript($script)
    {
        self::$scripts [] = $script;
    }

    /**
     * @param string $type
     * @param string $variableName
     * @param string $value
     * @param bool $withRoot
     */
    public static function addVariable($type, $variableName, $value, $withRoot = true)
    {
        self::$variables [] = array(
            'type'  => $type,
            'name'  => $variableName,
            'value' => $value,
            'withRoot' => $withRoot,
        );
    }

    /**
     * @param string $variableName
     * @param string $value
     */
    public static function addCssVariable($variableName, $value)
    {
        self::$cssVariables [] = array(
            'name'  => $variableName,
            'value' => $value,
        );
    }

    /**
     * @return string
     */
    public static function renderView()
    {
        // variables css
        $view = '';
        foreach (self::$cssVariables as $variable) {
            $view .= "\t--{$variable['name']}: {$variable['value']};\n";
        }
        $view = "<style>\nbody {\n" . $view . "}\n</style>\n";

        // variables js
        $view .= "<script>\n";
        $view .= self::declareNodes(self::$variables) . "\n";
        foreach (self::$variables as $variable) {
            $value = $variable['value'];
            if (0 === strcasecmp('string', $variable['type'])) {
                $value = '"' . $value . '"';
            }
            $view .= ($variable['withRoot'] ? '' : 'var ')
                     . $variable['name'] . '=' . $value . ";\n";
        }
        // scripts js
        $view .= implode("\n", self::$scripts);
        $view .= "\n</script>\n";
        return   $view;
    }

    /**
     * @param array $variables
     * @return string
     */
    public static function declareNodes(array $variables)
    {
        $declarations = [];
        foreach ($variables as $variable) {
            $name = $variable['name'];
            if (1 === count(explode('.', $name))) {
                continue;
            }
            $declarations = array_merge(self::prepareAllParents($name), $declarations);
        }

        $declarations = array_unique($declarations);
        sort($declarations, SORT_STRING);
        $view = '';
        foreach ($declarations as $declaration) {
            $view .= $declaration . "={};\n";
        }
        return   $view;
    }

    /**
     * @param string $variable
     * @return array
     */
    public static function prepareAllParents($variable)
    {
        $nodes = explode('.', $variable);
        $node = array_pop($nodes);

        if (0 === count($nodes)) {
            return [$node];
        } else {
            return array_merge(
                [implode('.', $nodes)],
                self::prepareAllParents(implode('.', $nodes))
            );
        }
    }
}
