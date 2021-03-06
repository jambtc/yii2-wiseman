<?php
$secrets = require __DIR__ . '/secrets.php';

return [
    'adminEmail' => $secrets['mail_adminusername'],
    'senderEmail' => $secrets['mail_username'],
    'senderName' => $secrets['mail_name'],
    'logoApplicazione' => '/css/images/logo.png',
    'website' => 'www.txlab.it',
    'adminName' => 'txLab',
    'company' => 'txLab.it',
    'supportEmail' => $secrets['mail_support'],
    'encryptionFile' => dirname(__FILE__).'/encrypt.json',
    'icon-framework' => 'fa',  // Font Awesome Icon framework

    /**
     * Set the password reset token expiration time.
     */
    'user.passwordResetTokenExpire' => 3600,
    'user.passwordMinLength' => 8,

    /**
     * Set the list of usernames that we do not want to allow to users to take upon registration or profile change.
     */
    'user.spamNames' => 'admin|superadmin|creator|thecreator|username|administrator|root',


    /**
    * set the domain name to switch from dashboard and pos
    */
    // 'pos.domain' => $secrets['pos.domain'],
    // 'api.domain' => $secrets['api.domain'],

    // openwheater
    'openweather_key' => $secrets['openweather_key'],

    // key for storing data on blochchain
    'secret_hash_key' => $secrets['secret_hash_key'],


    'openweather_key' => $secrets['openweather_key'],
    'giphy_developer_api' => $secrets['giphy_developer_api'],
    'google_dialog_flow' => $secrets['google_dialog_flow'],

    'GOOGLE_CLOUD_PROJECT' => $secrets['GOOGLE_CLOUD_PROJECT'],
    'GOOGLE_APPLICATION_CREDENTIALS' => $secrets['GOOGLE_APPLICATION_CREDENTIALS'],

    'telegram_wiseman_token' => $secrets['telegram_wiseman_token'],


];
