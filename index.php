<?php

require 'vendor/autoload.php';

use TelegramBot\Api\BotApi;
use Core\Database;
use Models\User;
use Controllers\TelegramController;

$config = require 'config.php';
$bot = new BotApi($config['botToken']);

$dbInstance = Database::getInstance($config['db']);
$pdo = $dbInstance->getConnection();
$userModel = new User($pdo, $config['db']['driver']);
$telegramController = new TelegramController($bot, $userModel);

while (true) {
    $telegramController->handleUpdates();
    sleep(1);
}
