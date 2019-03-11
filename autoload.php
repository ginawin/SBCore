<?php
// Load constants, config file
require_once __DIR__.'/core/App.php';
App::loadFile(__DIR__.'/vendor/valitron/src/Valitron/Validator.php');
App::loadDir(__DIR__.'/libs/');
echo 123;


