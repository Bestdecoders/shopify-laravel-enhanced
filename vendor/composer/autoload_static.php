<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitd176f5332955594a179a79881f0787c8
{
    public static $prefixLengthsPsr4 = array (
        'B' => 
        array (
            'Bestdecoders\\ShopifyLaravelEnhanced\\' => 36,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Bestdecoders\\ShopifyLaravelEnhanced\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitd176f5332955594a179a79881f0787c8::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitd176f5332955594a179a79881f0787c8::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitd176f5332955594a179a79881f0787c8::$classMap;

        }, null, ClassLoader::class);
    }
}
