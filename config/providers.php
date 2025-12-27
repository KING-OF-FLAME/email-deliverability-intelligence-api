<?php

/**
 * Email Service Provider detection patterns based on MX records.
 * Keys = Provider Name
 * Values = substrings / domains found in MX hostnames
 */

return [

    /* =========================
     * Business / Enterprise ESPs
     * ========================= */

    'Google Workspace' => [
        'aspmx.l.google.com',
        'alt1.aspmx.l.google.com',
        'googlemail.com',
        // Added these to catch consumer Gmail (gmail-smtp-in.l.google.com)
        'l.google.com',       
        'smtp-in.l.google.com'
    ],

    'Microsoft 365' => [
        'outlook.com',
        'protection.outlook.com',
        'mail.protection.outlook.com'
    ],

    'Zoho Mail' => [
        'zoho.com',
        'zoho.eu',
        'zoho.in'
    ],

    'Proton Mail (Business)' => [
        'protonmail.ch',
        'protonmail.com'
    ],

    'Fastmail' => [
        'fastmail.com',
        'messagingengine.com'
    ],

    'Amazon SES / WorkMail' => [
        'amazonses.com',
        'awsapps.com'
    ],

    'Rackspace Email' => [
        'emailsrvr.com'
    ],

    'GoDaddy Email' => [
        'secureserver.net'
    ],

    'IONOS (1&1)' => [
        '1and1.com',
        'ionos.com',
        'kundenserver.de'
    ],

    'Bluehost / HostGator / EIG' => [
        'bluehost.com',
        'hostgator.com',
        'websitewelcome.com'
    ],

    'DreamHost' => [
        'dreamhost.com'
    ],

    'Namecheap Email' => [
        'privateemail.com',
        'registrar-servers.com'
    ],

    'Yandex Business' => [
        'yandex.net',
        'yandex.com'
    ],

    'Alibaba / Aliyun Mail' => [
        'mxhichina.com',
        'alidns.com'
    ],

    'Tencent Exmail' => [
        'exmail.qq.com'
    ],

    'Mailgun' => [
        'mailgun.org',
        'mailgun.net'
    ],

    'SendGrid' => [
        'sendgrid.net'
    ],

    'Zoho ZeptoMail' => [
        'zeptomail.com'
    ],

    'Guerrilla Mail' => [
        'guerrillamail.com',
        'guerrillamailblock.com',
        'sharklasers.com',
        'm3player.com'
    ],

    'Yahoo / AOL' => [
        'biz.mail.yahoo.com',
        'yahoodns.net', // Catches mta7.am0.yahoodns.net (Consumer Yahoo)
        'aol.com'
    ],

    /* =========================
     * Free / Consumer Providers
     * (used to set business_email = false)
     * ========================= */

    'free_patterns' => [
        'gmail.com',
        'googlemail.com',
        'yahoo.com',
        'yahoo.co.uk',
        'yahoo.fr',
        'hotmail.com',
        'hotmail.co.uk',
        'outlook.com',
        'live.com',
        'aol.com',
        'icloud.com',
        'me.com',
        'mac.com',
        'yandex.com',
        'yandex.ru',
        'mail.ru',
        'proton.me',
        'protonmail.com',
        'gmx.com',
        'gmx.net',
        'gmx.de',
        'web.de',
        'zoho.com', // free Zoho mailboxes
        'test.com',
        'example.com'
    ]
];