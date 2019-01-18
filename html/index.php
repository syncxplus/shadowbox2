<?php

require_once dirname(__DIR__) . '/bootstrap.php';

call_user_func(function (Base $f3) {
    $f3->config([
        CONFIG_DIR . 'system.ini',
        CONFIG_DIR . 'route.ini',
        SS_DIR . 'config.ini',
    ]);

    $f3->mset([
        'AUTOLOAD' => dirname(__DIR__) . '/src/',
        'LOGS' => LOG_DIR,
    ]);

    if (!is_dir($f3->get('LOGS'))) {
        mkdir($f3->get('LOGS'), Base::MODE, true);
    }

    $f3->set('ONERROR', function (Base $f3) {
        echo json_encode($f3->get('ERROR'), JSON_UNESCAPED_UNICODE);
    });

    $f3->set('LOGGER', new Log(date('Y-m-d.\l\o\g')));

    if (PHP_SAPI != 'cli') {
        $f3->run();
    }
}, Base::instance());
