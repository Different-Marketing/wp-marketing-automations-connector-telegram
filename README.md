# Autonami Marketing Automations Connector for Telegram

## Описание

Этот плагин добавляет интеграцию Telegram в Autonami Marketing Automations для WordPress. С его помощью вы сможете отправлять сообщения через сервис Telegram в рамках ваших маркетинговых автоматизаций.

## Возможности

- Отправка SMS через Telegram в автоматизациях Autonami
- Поддержка персонализации сообщений с использованием тегов слияния Autonami
- Возможность отправки тестовых сообщений
- Поддержка UTM-меток для отслеживания

## Требования

- WordPress 6.6 или выше
- Autonami Marketing Automations
- Активная учетная запись Telegram

## Установка

1. Загрузите папку `autonami-automations-connectors-telegram` в директорию `/wp-content/plugins/` вашего сайта WordPress.
2. Активируйте плагин через меню 'Плагины' в WordPress.
3. Перейдите в настройки Autonami и подключите ваш аккаунт Telegram.

## Настройка

1. В админ-панели WordPress перейдите в раздел Autonami -> Настройки -> Интеграции.
2. Найдите Telegram в списке интеграций и нажмите "Подключить".
3. Введите ваш логин и пароль от Telegram.
4. Нажмите "Сохранить".

## Использование

После настройки вы сможете использовать действие "Отправить сообщение через Telegram" в ваших автоматизациях Autonami. При создании этого действия вы сможете указать получателя, текст сообщения и другие параметры.

## Поддержка

Если у вас возникли проблемы с использованием плагина, пожалуйста, создайте issue в репозитории GitHub или обратитесь в нашу службу поддержки.

## Лицензия

Этот плагин распространяется под лицензией GPL v2 или более поздней версии.

## Авторы

Разработано командой my.mamatov.club.

## Благодарности

Особая благодарность команде Autonami за создание отличной платформы для маркетинговых автоматизаций.

## Структура файлов плагина


wp-marketing-automations-connector-telegram/
│
├── autonami/
│   ├── class-bwfan-telegram-integration.php
│   └── class-bwfan-telegram-send-message.php
│
├── calls/
│   ├── class-wfco-telegram-send-message.php
│   └── class-wfco-telegram-get-bot-info.php
│
├── includes/
│   ├── class-wfco-telegram-call.php
│   └── class-wfco-telegram-common.php
│
├── views/
│   ├── settings.php
│   └── logo.png
│
├── connector.php
├── README.md
└── wp-marketing-automations-connector-telegram.php


### Описание основных файлов и функций