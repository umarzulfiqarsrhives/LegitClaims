<?php
return [
    'cacheTimestamp' => 1510895380,
    'database' => [
        'driver' => 'pdo_mysql',
        'dbname' => 'marketing_old',
        'user' => 'root',
        'password' => 'root',
        'host' => 'localhost',
        'port' => ''
    ],
    'useCache' => true,
    'recordsPerPage' => 200,
    'recordsPerPageSmall' => 100,
    'applicationName' => 'Legit Claims',
    'version' => '4.8.2',
    'timeZone' => 'Europe/London',
    'dateFormat' => 'MM/DD/YYYY',
    'timeFormat' => 'hh:mm a',
    'weekStart' => 0,
    'thousandSeparator' => ',',
    'decimalMark' => '.',
    'exportDelimiter' => ';',
    'currencyList' => [
        0 => 'USD'
    ],
    'defaultCurrency' => 'USD',
    'baseCurrency' => 'USD',
    'currencyRates' => [
        
    ],
    'outboundEmailIsShared' => true,
    'outboundEmailFromName' => 'Legit Claims',
    'outboundEmailFromAddress' => 'shehryar.shaukut@legitclaims.co.uk',
    'smtpServer' => 'smtp.mailgun.org',
    'smtpPort' => 587,
    'smtpAuth' => true,
    'smtpSecurity' => 'TLS',
    'smtpUsername' => 'shehryar.shaukut@legitclaims.co.uk',
    'smtpPassword' => 'legitclaims@123',
    'languageList' => [
        0 => 'en_GB',
        1 => 'en_US',
        2 => 'es_MX',
        3 => 'cs_CZ',
        4 => 'da_DK',
        5 => 'de_DE',
        6 => 'es_ES',
        7 => 'fr_FR',
        8 => 'id_ID',
        9 => 'it_IT',
        10 => 'nb_NO',
        11 => 'nl_NL',
        12 => 'tr_TR',
        13 => 'sr_RS',
        14 => 'ro_RO',
        15 => 'ru_RU',
        16 => 'pl_PL',
        17 => 'pt_BR',
        18 => 'uk_UA',
        19 => 'vi_VN',
        20 => 'zh_CN'
    ],
    'language' => 'en_US',
    'logger' => [
        'path' => 'data/logs/espo.log',
        'level' => 'WARNING',
        'rotation' => true,
        'maxFileNumber' => 30
    ],
    'authenticationMethod' => 'Espo',
    'globalSearchEntityList' => [
        0 => 'Account',
        1 => 'Contact',
        2 => 'Lead',
        3 => 'Opportunity'
    ],
    'tabList' => [
        0 => 'Account',
        1 => 'Contact',
        2 => 'Lead',
        3 => 'Opportunity',
        4 => 'Quote',
        5 => 'Product',
        6 => 'Email',
        7 => 'Campaign',
        8 => 'Meeting',
        9 => 'Call',
        10 => 'Task',
        11 => 'Stream',
        12 => 'Report',
        13 => 'KnowledgeBaseArticle',
        14 => 'Document',
        15 => 'TargetList',
        16 => 'Calendar'
    ],
    'quickCreateList' => [
        0 => 'Account',
        1 => 'Contact',
        2 => 'Lead',
        3 => 'Opportunity',
        4 => 'Meeting',
        5 => 'Call',
        6 => 'Task',
        7 => 'Case',
        8 => 'Email'
    ],
    'exportDisabled' => false,
    'assignmentEmailNotifications' => false,
    'assignmentEmailNotificationsEntityList' => [
        0 => 'Lead',
        1 => 'Opportunity',
        2 => 'Task',
        3 => 'Case'
    ],
    'assignmentNotificationsEntityList' => [
        0 => 'Meeting',
        1 => 'Call',
        2 => 'Task',
        3 => 'Email'
    ],
    'portalStreamEmailNotifications' => true,
    'streamEmailNotificationsEntityList' => [
        0 => 'Case'
    ],
    'emailMessageMaxSize' => 10,
    'notificationsCheckInterval' => 10,
    'disabledCountQueryEntityList' => [
        0 => 'Email'
    ],
    'maxEmailAccountCount' => 2,
    'followCreatedEntities' => false,
    'b2cMode' => false,
    'restrictedMode' => false,
    'theme' => 'VioletVertical',
    'massEmailMaxPerHourCount' => 100,
    'personalEmailMaxPortionSize' => 10,
    'inboundEmailMaxPortionSize' => 20,
    'authTokenLifetime' => 0,
    'authTokenMaxIdleTime' => 120,
    'userNameRegularExpression' => '[^a-z0-9\\-@_\\.\\s]',
    'addressFormat' => 1,
    'displayListViewRecordCount' => true,
    'dashboardLayout' => [
        0 => (object) [
            'name' => 'My Espo',
            'layout' => [
                0 => (object) [
                    'id' => 'default-activities',
                    'name' => 'Activities',
                    'x' => 2,
                    'y' => 2,
                    'width' => 2,
                    'height' => 2
                ],
                1 => (object) [
                    'id' => 'default-stream',
                    'name' => 'Stream',
                    'x' => 0,
                    'y' => 0,
                    'width' => 2,
                    'height' => 4
                ],
                2 => (object) [
                    'id' => 'default-tasks',
                    'name' => 'Tasks',
                    'x' => 2,
                    'y' => 0,
                    'width' => 2,
                    'height' => 2
                ]
            ]
        ]
    ],
    'calendarEntityList' => [
        0 => 'Meeting',
        1 => 'Call',
        2 => 'Task'
    ],
    'activitiesEntityList' => [
        0 => 'Meeting',
        1 => 'Call'
    ],
    'historyEntityList' => [
        0 => 'Meeting',
        1 => 'Call',
        2 => 'Email'
    ],
    'lastViewedCount' => 20,
    'cleanupJobPeriod' => '1 month',
    'cleanupActionHistoryPeriod' => '15 days',
    'cleanupAuthTokenPeriod' => '1 month',
    'currencyFormat' => 1,
    'currencyDecimalPlaces' => NULL,
    'aclStrictMode' => true,
    'isInstalled' => true,
    'siteUrl' => 'http://18.220.103.184/marketing',
    'passwordSalt' => 'e9244c4d3dadce18',
    'cryptKey' => 'e209926ecbfd173470d70a791b8a0d85',
    'defaultPermissions' => [
        'user' => 33,
        'group' => 33
    ],
    'massEmailDisableMandatoryOptOutLink' => false,
    'userThemesDisabled' => false,
    'avatarsDisabled' => false,
    'dashletsOptions' => (object) [
        
    ],
    'integrations' => (object) [
        'MailChimp' => true
    ]
];
?>