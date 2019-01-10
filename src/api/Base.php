<?php

namespace api;

use DB\Jig;

abstract class Base
{
    function beforeRoute()
    {
        $jig = new Jig(JIG_DIR,Jig::FORMAT_JSON);
        $user = new Jig\Mapper($jig,'users');
        $auth = new \Auth($user, array('id'=>'name', 'pw'=>'password'));
        if (!$auth->basic()) {
            die();
        }
    }
}
