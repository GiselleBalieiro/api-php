# api-php

A plain-PHP REST API that powers the users and AI agents of the **AgenT** platform. It also connects those agents to **Telegram** and to the **WhatsApp Cloud API (Meta)**. Incoming messages are forwarded to a RAG service (`ia-rag-api`, private), and its answer is sent back to the chat.

![PHP](https://img.shields.io/badge/PHP-777BB4?logo=php&logoColor=ffffff&style=flat-square)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?logo=mysql&logoColor=ffffff&style=flat-square)
![JWT](https://img.shields.io/badge/JWT-000000?logo=jsonwebtokens&logoColor=ffffff&style=flat-square)
![Heroku](https://img.shields.io/badge/Heroku-430098?logo=heroku&logoColor=ffffff&style=flat-square)

## Features

- **Auth:**
  - register and login with `password_hash` / `password_verify`;
  - stateless sessions with **JWT** (HS256, 1-hour expiry) via `firebase/php-jwt`.
- **Agents CRUD:** create, list, read, update and delete AI agents. Every query is scoped to the authenticated user (`user_id` comes from the token).
- **Telegram bots:**
  - registers a bot's webhook for an agent;
  - answers each incoming message with the agent's AI reply.
- **WhatsApp Cloud API:**
  - webhook verification (`hub.challenge`);
  - receives messages and replies with the AI answer;
  - endpoint to send a message to a number.
- **Database:** MySQL through **PDO** with prepared statements. Credentials come from environment variables (`vlucas/phpdotenv`).
- **CORS** handled for browser clients.

## Endpoints

| Method | Route | Description |
|---|---|---|
| `POST` | `/user.php?register` | Create a user (`name`, `email`, `password`) |
| `POST` | `/user.php` | Login (`email`, `password`) and get a JWT |
| `GET` | `/user.php` | Current user (`Authorization` header with the token) |
| `GET` | `/agent.php` | List the user's agents |
| `GET` | `/agent.php?id={id}` | Get one agent |
| `POST` | `/agent.php` | Create an agent |
| `POST` | `/agent.php?update&id={id}` | Update an agent |
| `DELETE` | `/agent.php?id={id}` | Delete an agent |
| `POST` | `/create_bot.php` | Register a Telegram webhook (`bot_token`, `agent_id`) |
| `POST` | `/telegram.php?agent_id={id}` | Telegram webhook: replies with the agent's AI answer |
| `GET` / `POST` | `/webhook.php` | WhatsApp Cloud API webhook (verification + incoming messages) |
| `POST` | `/enviar.php` | Send a WhatsApp message (`number`, `message`) |

`api-oficial/` holds the same WhatsApp official-API scripts, kept separate.

## Running locally

Requirements: PHP 8+ with the `pdo_mysql` and `curl` extensions, Composer, and a MySQL database with `user` and `agent` tables.

```bash
composer install
```

Create a `.env` file in the project root:

```env
DATABASE_HOST=localhost
DATABASE_PORT=3306
DATABASE=your_database
DATABASE_USERNAME=root
DATABASE_PASSWORD=secret
JWT_SECRET_KEY=change-me

# WhatsApp Cloud API (optional)
WHATSAPP_TOKEN=
PHONE_NUMBER_ID=
VERIFY_TOKEN=
IA_API_URL=
```

Start the built-in server:

```bash
php -S localhost:8000
```

## Deploy

The repo has a `Procfile` and was deployed on Heroku.

## Author

Made by [Giselle Balieiro](https://github.com/GiselleBalieiro).
