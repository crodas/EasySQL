<?php
/**
 *  Autoloader function generated by crodas/Autoloader
 *
 *  https://github.com/crodas/Autoloader
 *
 *  This is a generated file, do not modify it.
 */

spl_autoload_register(function ($class) {
    /*
        This array has a map of (class => file)
    */
    static $classes = array (
  'easysql\\compiler\\query' => 
  array (
    0 => '/Compiler/Query.php',
    1 => 'class_exists',
  ),
  'easysql\\compiler\\repository\\method' => 
  array (
    0 => '/Compiler/Repository/Method.php',
    1 => 'class_exists',
  ),
  'base_template_c4adc06fcaba37f452631fc5422ddc1f451c4bce' => 
  array (
    0 => '/Compiler/TemplIates.php',
    1 => 'class_exists',
  ),
  'class_a70c700441f2ca1a7f9cce68047ff33925e70d04' => 
  array (
    0 => '/Compiler/TemplIates.php',
    1 => 'class_exists',
  ),
  'easysql\\compiler\\templates' => 
  array (
    0 => '/Compiler/TemplIates.php',
    1 => 'class_exists',
  ),
  'easysql\\easysql' => 
  array (
    0 => '/EasySQL.php',
    1 => 'class_exists',
  ),
  'easysql\\engine\\base' => 
  array (
    0 => '/Engine/Base.php',
    1 => 'class_exists',
  ),
);

    static $deps    = array (
  'class_a70c700441f2ca1a7f9cce68047ff33925e70d04' => 
  array (
    0 => 'base_template_c4adc06fcaba37f452631fc5422ddc1f451c4bce',
  ),
);

$class = strtolower($class);
if (isset($classes[$class])) {
    if (!empty($deps[$class])) {
        foreach ($deps[$class] as $zclass) {
if (
    ! $classes[$zclass][1]( $zclass, false )
) {
    require __DIR__  . $classes[$zclass][0];
}
        }
    }
if (
    ! $classes[$class][1]( $class, false )
) {
    require __DIR__  . $classes[$class][0];
}
    return true;
}

    /**
     * Autoloader that implements the PSR-0 spec for interoperability between
     * PHP software.
     *
     * kudos to@alganet for this autoloader script.
     * borrowed from https://github.com/Respect/Validation/blob/develop/tests/bootstrap.php
     */
    $fileParts = explode('\\', ltrim($class, '\\'));
    if (false !== strpos(end($fileParts), '_')) {
        array_splice($fileParts, -1, 1, explode('_', current($fileParts)));
    }
    $file = stream_resolve_include_path(implode(DIRECTORY_SEPARATOR, $fileParts) . '.php');
    if ($file) {
        return require $file;
    }

    return false;
} 
);


