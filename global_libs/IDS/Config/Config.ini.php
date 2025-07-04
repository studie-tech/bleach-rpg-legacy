; <?php die(); ?>

; PHPIDS Config.ini

; General configuration settings


[General]

    ; basic settings - customize to make the PHPIDS work at all
    filter_type     = xml
    
    base_path       = global_libs/IDS/
    use_base_path   = true
    
    filter_path     = default_filter.xml
    tmp_path        = tmp
    scan_keys       = false
    
    ; in case you want to use a different HTMLPurifier source, specify it here
    ; By default, those files are used that are being shipped with PHPIDS
    HTML_Purifier_Path	= vendors/htmlpurifier/HTMLPurifier.auto.php
    HTML_Purifier_Cache = vendors/htmlpurifier/HTMLPurifier/DefinitionCache/Serializer
    
    ; define which fields contain html and need preparation before 
    ; hitting the PHPIDS rules (new in PHPIDS 0.5)
    ;html[]          = POST.__wysiwyg
    
    ; define which fields contain JSON data and should be treated as such 
    ; for fewer false positives (new in PHPIDS 0.5.3)
    ;json[]          = POST.__jsondata

    ; define which fields shouldn't be monitored (a[b]=c should be referenced via a.b)
    exceptions[] = REQUEST.orders
    exceptions[] = POST.orders
    exceptions[] = REQUEST.nindo
    exceptions[] = POST.nindo
    exceptions[] = POST.recaptcha_challenge_field
    exceptions[] = POST.recaptcha_response_field
    exceptions[] = POST.battle_description
    exceptions[] = POST.password
    exceptions[] = POST.password_v
    exceptions[] = REQUEST.sig
    exceptions[] = POST.sig
    exceptions[] = GET.sig 
    exceptions[] = REQUEST.description
    exceptions[] = POST.description
    exceptions[] = REQUEST.effect_1
    exceptions[] = POST.effect_1
    exceptions[] = REQUEST.effect_2
    exceptions[] = POST.effect_2
    exceptions[] = REQUEST.effect_3
    exceptions[] = POST.effect_3
    exceptions[] = REQUEST.effect_4
    exceptions[] = POST.effect_4

    ; you can use regular expressions for wildcard exceptions - example: /.*foo/i

    ; PHPIDS should run with PHP 5.1.2 but this is untested - set 
    ; this value to force compatibilty with minor versions
    min_php_version = 5.1.6

; If you use the PHPIDS logger you can define specific configuration here

[Logging]

    ; file logging
    path            = tmp/phpids_log.txt

    ; email logging

    ; note that enabling safemode you can prevent spam attempts,
    ; see documentation
    recipients[]    = test@test.com.invalid
    subject         = "PHPIDS detected an intrusion attempt!"
    header			= "From: <PHPIDS> info@phpids.org"
    envelope        = ""
    safemode        = true
    urlencode       = true
    allowed_rate    = 15

    ; database logging


; If you would like to use other methods than file caching you can configure them here

[Caching]

    ; caching:      session|file|database|memcached|none
    caching         = file 
    expiration_time = 600

    ; file cache    
    path            = tmp/default_filter.cache

    ; database cache
    wrapper         = "mysql:host=localhost;port=3306;dbname=phpids"   
    user            = phpids_user
    password        = 123456
    table           = cache  

    ; memcached     
    ;host           = localhost
    ;port           = 11211
    ;key_prefix     = PHPIDS
