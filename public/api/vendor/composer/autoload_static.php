<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitd4e2b66b9cfe4558a9b8018aae8dc3b8
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'Phroute\\Phroute\\' => 16,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Phroute\\Phroute\\' => 
        array (
            0 => __DIR__ . '/..' . '/phroute/phroute/src/Phroute',
        ),
    );

    public static $prefixesPsr0 = array (
        'M' => 
        array (
            'Monolog' => 
            array (
                0 => __DIR__ . '/..' . '/monolog/monolog/src',
            ),
        ),
    );

    public static $classMap = array (
        'DBCon' => __DIR__ . '/..' . '/pekky/DB/DBCon.class.php',
        'EasyPeasyICS' => __DIR__ . '/..' . '/phpmailer/phpmailer/extras/EasyPeasyICS.php',
        'League\\OAuth2\\Client\\Provider\\Google' => __DIR__ . '/..' . '/phpmailer/phpmailer/get_oauth_token.php',
        'Letters' => __DIR__ . '/..' . '/pekky/Filter/Letters.class.php',
        'Names' => __DIR__ . '/..' . '/pekky/Filter/Names.class.php',
        'PHPMailer' => __DIR__ . '/..' . '/phpmailer/phpmailer/class.phpmailer.php',
        'PHPMailerOAuth' => __DIR__ . '/..' . '/phpmailer/phpmailer/class.phpmaileroauth.php',
        'PHPMailerOAuthGoogle' => __DIR__ . '/..' . '/phpmailer/phpmailer/class.phpmaileroauthgoogle.php',
        'POP3' => __DIR__ . '/..' . '/phpmailer/phpmailer/class.pop3.php',
        'Query' => __DIR__ . '/..' . '/pekky/DB/Query.class.php',
        'RegMail' => __DIR__ . '/..' . '/pekky/mail/RegMail.class.php',
        'SMTP' => __DIR__ . '/..' . '/phpmailer/phpmailer/class.smtp.php',
        'ntlm_sasl_client_class' => __DIR__ . '/..' . '/phpmailer/phpmailer/extras/ntlm_sasl_client.php',
        'phpmailerException' => __DIR__ . '/..' . '/phpmailer/phpmailer/class.phpmailer.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitd4e2b66b9cfe4558a9b8018aae8dc3b8::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitd4e2b66b9cfe4558a9b8018aae8dc3b8::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInitd4e2b66b9cfe4558a9b8018aae8dc3b8::$prefixesPsr0;
            $loader->classMap = ComposerStaticInitd4e2b66b9cfe4558a9b8018aae8dc3b8::$classMap;

        }, null, ClassLoader::class);
    }
}
