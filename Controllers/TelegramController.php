<?php

namespace Controllers;

use TelegramBot\Api\BotApi;
use Models\User;

class TelegramController {

    protected $bot;
    protected $userModel;
    protected $updateOffset = 0;

    public function __construct(BotApi $bot, User $userModel) {
        $this->bot = $bot;
        $this->userModel = $userModel;
    }
    public function handleUpdates(): void {

        try {
            $updates = $this->bot->getUpdates($this->updateOffset, 100, 30);
        } catch (\TelegramBot\Api\HttpException $e) {
            sleep(1);
            return;
        }

        if (!empty($updates)) {

            $maxUpdateId = $this->updateOffset;

            foreach ($updates as $update) {

                $updateId = $update->getUpdateId();

                if ($updateId >= $maxUpdateId) {
                    $maxUpdateId = $updateId + 1;
                }
                $message = $update->getMessage();
                if (!$message) continue;

                $chatId = $message->getChat()->getId();
                $telegramId = $message->getFrom()->getId();
                $text = trim($message->getText());

                if (strpos($text, '/start') === 0) {
                    $this->userModel->findOrCreate($telegramId);
                    $this->bot->sendMessage($chatId, "Добро пожаловать! Ваш счет: 0.00$");
                    continue;
                }

                if (empty($text)) {
                    $this->bot->sendMessage($chatId, "Сообщение не может быть пустым. Пожалуйста, отправьте число.");
                    continue;
                }
                $normalizedText = str_replace(',', '.', $text);

                if (!is_numeric($normalizedText)) {
                    $this->bot->sendMessage($chatId, "Пожалуйста, отправьте число для изменения баланса (например, 10 или -5.50).");
                    continue;
                }
                
                $amount = (float)$normalizedText;
                $result = $this->userModel->applyTransaction($telegramId, $amount);

                if (isset($result['error'])) {
                    $this->bot->sendMessage($chatId, $result['error']);
                } else {
                    $this->bot->sendMessage($chatId, "Операция выполнена. Ваш новый баланс: " . number_format($result['balance'], 2));
                }
            }
            $this->updateOffset = $maxUpdateId;
        }
    }
}
