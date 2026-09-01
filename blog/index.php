<?php

/**
 * Blog entry point
 * Routes /blog/* requests to the main index.php dispatcher
 */

require_once __DIR__ . '/../init.php';

doAction('init');

$emDispatcher = Dispatcher::getInstance();
$emDispatcher->dispatch();

View::output();
