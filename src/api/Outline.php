<?php

namespace api;

use Ramsey\Uuid\Uuid;

class Outline extends Base
{
    private $logger;
    private $data;

    function manipulate(\Base $f3)
    {
        $this->logger->write($f3->get('VERB') . '' . $f3->get('URI'));
        switch ($f3->get('VERB')) {
            case 'HEAD':
            case 'GET':
                echo json_encode(array_merge(['accessKeys' => $this->data['keys']], ['status' => true]));
                break;
            case 'POST':
                $rate = $f3->get('PARAMS.rate') ?: 0;
                $count = $f3->get('PARAMS.count') ?: 1;
                $newUsers = ['keys' => []];
                if (intval($count) > 0) {
                    $nextId = $this->getNextId();
                    $nextPort = $this->getNextPort($rate);
                    for ($i = 0 ; $i < $count; $i ++) {
                        $user = $this->createUser($nextId, $nextPort, $rate);
                        $this->data['keys'][] = $user;
                        $newUsers['keys'][] = $user;
                    }
                    $this->updateConfig();
                }
                echo json_encode(array_merge(['accessKeys' => $newUsers['keys']], ['status' => true]));
                break;
            case 'DELETE':
                $body = $f3->get('BODY');
                $this->logger->write("DELETE $body");
                $items = json_decode($body, true);
                if ($items) {
                    $items = array_column($items, 'id');
                    $keys = $this->data['keys'];
                    $idx = [];
                    foreach ($keys as $i => $key) {
                        if (in_array($key['id'], $items) !== false) {
                            $idx[] = $i;
                            $this->logger->write($key['id'] . ' will be deleted');
                        }
                    }
                    foreach ($idx as $id) {
                        unset($this->data['keys'][$id]);
                    }
                    $this->data['keys'] = array_values($this->data['keys']);
                    $this->updateConfig();
                }
                echo json_encode(['status' => true]);
                break;
        }
    }

    function __construct()
    {
        $f3 = \Base::instance();
        $this->logger = $f3->get('LOGGER');
        $this->data = yaml_parse_file(SS_CONFIG);
    }

    private function updateConfig()
    {
        if (yaml_emit_file(SS_CONFIG, $this->data)) {
            system('pkill -SIGHUP -f outline', $code);
            return $code === 0;
        } else {
            return false;
        }
    }

    private function createUser(&$nextId, &$nextPort, $rate)
    {
        $f3 = \Base::instance();
        $id = $nextId;
        $nextId ++;
        $port = $nextPort;
        if (getenv('EXCLUSIVE') || $rate == 0) {
            $nextPort ++;
            $start = $f3->get('PORT_LIMIT_' . $rate) ?: $f3->get('PORT_DEFAULT');
            $limit = $start + $f3->get('PORT_RANGE');
            if ($nextPort >= $limit) {
                $nextPort = $start;
            }
        }
        $random = substr(Uuid::uuid1()->toString(), 0, 8);
        return [
            'id' => (string) $id,
            'port' => $port,
            'cipher' => 'chacha20-ietf-poly1305',
            'secret' => "pwd-$random",
            'method' => 'chacha20-ietf-poly1305',
            'password' => "pwd-$random",
            'rate' => $rate,
            'name' => '',
            'accessUrl' => '',
        ];
    }

    private function getNextId()
    {
        $keys = $this->data['keys'];
        $maxId = $keys[array_key_last($keys)]['id'];
        return (intval($maxId) + 1);
    }

    private function getNextPort($rate)
    {
        $f3 = \Base::instance();
        $start = $f3->get('PORT_LIMIT_' . $rate) ?: $f3->get('PORT_DEFAULT');
        $limit = $start + $f3->get('PORT_RANGE');
        $port = 0;
        foreach ($this->data['keys'] as $key) {
            if ($key['port'] > $start && $key['port'] < $limit) {
                $port = $key['port'];
            }
        }
        if ($port == 0) {
            return $start;
        } else {
            $port ++;
            return $port < $limit ? $port : $start;
        }
    }
}
