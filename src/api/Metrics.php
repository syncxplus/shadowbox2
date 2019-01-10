<?php

namespace api;

class Metrics
{
    function scrape()
    {
        $response = \Web::instance()->request('http://localhost:9090/metrics');
        header('Content-Type: text/plain; version=0.0.4; charset=utf-8');
        echo $response['body'];
    }
}
