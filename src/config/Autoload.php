<?php

namespace App\config;

use CodeIgniter\Config\BaseConfig;

class Autoload extends BaseConfig{
    public $psr4 = [
        'App' => ROOTPATH . 'src',
    ];
}