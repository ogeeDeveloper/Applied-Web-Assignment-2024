<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitfdc0219b5082446e7bd455682e154adf
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'PHPMailer\\PHPMailer\\' => 20,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'PHPMailer\\PHPMailer\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpmailer/phpmailer/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitfdc0219b5082446e7bd455682e154adf::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitfdc0219b5082446e7bd455682e154adf::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitfdc0219b5082446e7bd455682e154adf::$classMap;

        }, null, ClassLoader::class);
    }
}
