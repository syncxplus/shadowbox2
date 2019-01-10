<?php

namespace api;

class Version
{
    function get(\Base $f3)
    {
        echo json_encode([
            'version' => $f3->get('SS_VERSION')
        ]);
    }
}
