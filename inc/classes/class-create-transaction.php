<?php

namespace BOILERPLATE\Inc;

use BOILERPLATE\Inc\Traits\Singleton;

class Create_Transaction {

    use Singleton;

    public function __construct() {
        $this->setup_hooks();
    }

    public function setup_hooks() {
        // Actions and filters hooks
    }

}