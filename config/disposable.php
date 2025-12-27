<?php

/**
 * Expanded list of disposable / temporary email domains.
 * Suitable for API-level checks.
 * For production scale, load from file or DB and update regularly.
 */

return [

    /* =========================
     * Popular Disposable Services
     * ========================= */

    'mailinator.com',
    'guerrillamail.com',
    'guerrillamailblock.com',
    'sharklasers.com',
    'm3player.com', // Added explicitly to fix false negative
    'yopmail.com',
    'yopmail.fr',
    'yopmail.net',
    'temp-mail.org',
    'temp-mail.io',
    'tempmail.com',
    '10minutemail.com',
    '10minutemail.net',
    '10minemail.com',
    'throwawaymail.com',
    'getairmail.com',
    'maildrop.cc',
    'dispostable.com',
    'tempr.email',
    'trashmail.com',
    'trashmail.de',
    'fakeinbox.com',
    'mailnesia.com',
    'mintemail.com',
    'emailondeck.com',
    'mytemp.email',
    'tempinbox.com',
    'anonaddy.com',
    'anonaddy.me',
    'simplelogin.co',
    'simplelogin.com',

    /* =========================
     * Short-lived / Burner Mail
     * ========================= */

    'burnermail.io',
    'spambog.com',
    'spambog.de',
    'spambog.ru',
    'spamgourmet.com',
    'spamgourmet.net',
    'spamgourmet.org',
    'jetable.org',
    'emailfake.com',
    'temporarymail.com',
    'moakt.com',
    'inboxbear.com',
    'dropmail.me',
    'mailpoof.com',
    'mailcatch.com',
    'mailnull.com',

    /* =========================
     * Known API / Automation Abuse
     * ========================= */

    'boximail.com',
    'dayrep.com',
    'einrot.com',
    'fleckens.hu',
    'jourrapide.com',
    'rhyta.com',
    'superrito.com',
    'teleworm.us',

    /* =========================
     * Catch-all disposable TLDs (Note: These need specific logic to work as wildcards)
     * For now, exact matches are safer in this list.
     * ========================= */
     
    // Added common domains for these TLDs if known, otherwise 
    // Reputation.php logic needs update to handle TLD wildcards.
    'xyz', 
    'top'
];