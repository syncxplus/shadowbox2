<?php

namespace api;

use Ramsey\Uuid\Uuid;

class Outline extends Base
{
    private $logger;
    private $sharedPort;
    private $exclusivePort;
    private $exclusiveRate;
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
                    $nextPort = $this->getNextPort();
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
        $this->sharedPort = $f3->get('SHARED_PORT');
        $this->exclusivePort = $f3->get('EXCLUSIVE_PORT');
        $this->exclusiveRate = $f3->get('EXCLUSIVE_RATE');
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
        $id = $nextId;
        $nextId ++;
        if (getenv('EXCLUSIVE') || in_array($rate, $this->exclusiveRate)) {
            $port = $nextPort;
            if ($nextPort < $this->exclusivePort[1]) {
                $nextPort ++;
            } else {
                $nextPort = $this->exclusivePort[0];
            }
        } else {
            $port = $this->sharedPort;
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

    private function getNextPort()
    {
        $ports = array_column($this->data['keys'], 'port');
        rsort($ports);
        return ($ports[0] < $this->exclusivePort[1]) ? ($ports[0] + 1) : $this->exclusivePort[0];
    }
}
