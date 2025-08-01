# Simple Telegram Bot (simple balance system)

## Disclaimer: This project was developed as a test assignment for BotHub.

### A few words about the implementation:
- The project follows the MVP pattern, adapted to the Telegram Bot environment (the controller acts as both the controller and the view).
- It uses PDO with prepared statements for MySQL to prevent SQL injection, which is especially important since the bot processes raw user input. While Telegram bots don't operate in a browser context, protecting against SQL injection remains essential when storing or using user-submitted data — so I implemented both safe queries and input validation.
- As per the assignment requirements, I used only the TelegramBot/api wrapper without any additional frameworks.
- If you find any mistakes or areas for improvement, please let me know — I’m happy to fix them.
- I also included optional PostgreSQL support as an alternative implementation.

The requirements didn’t explicitly mention rounding numbers to a certain number of decimal places, so this functionality was not implemented.

![Screenshot](https://i.imgur.com/cnMUZ9a.png)
