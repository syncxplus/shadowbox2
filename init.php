<?php

use DB\Jig;

require_once __DIR__ . '/bootstrap.php';

const USER = 'www-data';
const GROUP = 'www-data';

if (!is_dir(JIG_DIR)) {
    mkdir(JIG_DIR, Base::MODE, true);
}

$db = new Jig(JIG_DIR,Jig::FORMAT_JSON);

$user = new Jig\Mapper($db,'users');

$user->load(['@name=?', 'user']);

if ($user->dry()) {
    $user['name'] = 'user';
    $user['password'] = '123456';
    $user->save();
}

if (!is_dir(dirname(SS_CONFIG))) {
    mkdir(dirname(SS_CONFIG), Base::MODE, true);
}

if (!is_file(SS_CONFIG)) {
    $keys = ['keys' => []];
    $compatible = dirname(SS_CONFIG) . '/shadowbox_config.json';
    if (is_file($compatible)) {
        try {
            $accessKeys = json_decode(file_get_contents($compatible), true, 512, JSON_THROW_ON_ERROR);
            foreach ($accessKeys['accessKeys'] as $accessKey) {
                $keys['keys'][] = [
                    'id' => (string) $accessKey['id'],
                    'port' => (int) $accessKey['port'],
                    'cipher' => $accessKey['encryptionMethod'],
                    'secret' => $accessKey['password'],
                    'method' => $accessKey['encryptionMethod'],
                    'password' => $accessKey['password'],
                    'rate' => $accessKey['rate'] ?: 0,
                    'name' => $accessKey['name'] ?: '',
                    'accessUrl' => $accessKey['accessUrl'] ?: '',
                ];
            }
            rename($compatible, "$compatible.old");
        } catch (Exception $e) {
            $logger = new Log(date('Y-m-d.\l\o\g'));
            $logger->write("Init from compatible $compatible error: " . $e->getTraceAsString());
        }
    } else {
        $f3 = Base::instance();
        $f3->config(CONFIG_DIR . 'system.ini');
        $keys['keys'][] = [
            'id' => '0',
            'port' => $f3->get('PORT_DEFAULT'),
            'cipher' => 'chacha20-ietf-poly1305',
            'secret' => 'shadowbox',
            'method' => 'chacha20-ietf-poly1305',
            'password' => 'shadowbox',
            'rate' => 0,
            'name' => '',
            'accessUrl' => '',
        ];
    }
    yaml_emit_file(SS_CONFIG, $keys);
    chown(SS_CONFIG, USER);
    chgrp(SS_CONFIG, GROUP);
}
