## Таска 1

```sql
-- users(id, first_name, last_name, birthday)
-- books (id, name, author)
-- user_books (id, user_id, book_id, get_date, return_date)


select u.id                                                  as user_id,
       concat_ws(' ', trim(u.first_name), trim(u.last_name)) as fullname, -- не сказано наллабл или нет, но предположим, что нет
       min(b.author)                                         as author,   -- автор один, но убирает варнинг из-за груп бая т.к. агрегация
       group_concat(
           distinct trim(b.name) order by b.name separator ', '
    )       as books_ordered
from users u
         inner join user_books ub on u.id = ub.user_id
         inner join books b on ub.book_id = b.id
where u.birthday between curdate() - interval 17 year and curdate() - interval 7 year
        and (
            (ub.return_date is not null and ub.return_date <= ub.get_date + interval 14 day) -- в задаче не сказано, но на всякий, если посетитель не вернул
            or (ub.return_date is null and curdate() <= ub.get_date + interval 14 day) -- тоже по факту не просрочили
        )
group by u.id
having count(ub.id) = 2 and count(distinct b.author) = 1;

-- еще индексы можно добавить на [get_date, return_date] в user_books и [user_id, book_id] из-за джоинов
```

## Таска 2

### Стек

- PHP 8.2
- Laravel 12
- MySQL 8 основная база
- Redis кеш, очереди
- supervisor для очередей

В тексте задания нет ограничений по стеку или каких-либо функциональных ограничений (напр нельзя использовать бд или кеш), поэтому сделал как считаю лучшим вариантом

### Архитектура

#### Данные

| Таблица          | Назначение                                          |
|------------------|-----------------------------------------------------|
| `users`          | Пользователи (для кого-то же нужно выдавать токены) |
| `currencies`     | Доступные монеты                                    |
| `exchange_rates` | Курсы валют                                         |
| `settings`       | Настройки (такие как комиссия)                      |

Остальные таблицы являются стандартными для Laravel приложения

#### Таблица `currencies`

| Столбец  | Назначение                        |
|----------|-----------------------------------|
| `id`     | Уникальный идентификатор монеты   |
| `symbol` | Символ валюты (USD, ETH, BTC, пр) |

Индексы: `unique symbol`

#### Таблица `exchange_rates`

| Столбец                   | Назначение                             |
|---------------------------|----------------------------------------|
| `id`                      | Уникальный идентификатор пары конверта |
| `from_currency_id`        | fk на базовую валюту                   |
| `to_currency_id`          | fk на котируемую валюту                |
| `rate`                    | Курс (varchar)                         |
| `created_at`/`updated_at` | Таймстампы                             |

Индексы: `unique [from_currency_id, to_currency_id]`

#### Фоновая работа

Супервизор слушает одну очередь в редисе (`default`), при желании можно распараллелить на две разные очереди

#### `UpdateCurrenciesTableJob`

Запускается каждый час, обновляет список актуальных монет из источников

#### `UpdateExchangeRatesJob`

Запускается каждую минуту, обновляет валютные курсы

#### Структура

```text
/app - Папка с приложением
    /Console
        /Commands - Консольные команды
    /DataProviders - Поставщики данных (курсов валют)
        CoinCapDataProvider.php - Обёртка над coincap
    /Dto
        /Convert - DTO для работы с конвертацией
        /Currency - DTO для работы с валютами
        /ExchangeRate - DTO для работы с курсами валют
    /Enums - Всё что может быть потенциально магическим упаковано в енумки
        MethodEnum.php - перечисление API методов (convert, rates) с логикой валидации
        ResponseStatusEnum.php - статус в респонсе бека (success, error)
        SettingEnum.php - доступные настройки
    /Exceptions - Исключения
        MethodNotFoundException.php - Выбрасывается когда прилетает запрос с недопустимым методом
        PairNotFoundException.php - Выбрасывается при попытке конвертировать пару, которая несуществует
        RestMethodIsNotAllowedForApiMethodException.php - Выбрасывается при неправильном REST методе по отношению к методу API
        SettingIsNotSetException.php - Выбрасывается при получении настройки, которая  ещё не установлена
    /Helpers - хелперы для мелочёвки
    /Http
        /Controllers - Контроллеры
            HealthCheckController.php - Контроллер для хелсчека, тут используется только для докера
            /Api
                AbstractApiController.php - Родитель для всех API контроллеров, содержит шаблоны респонсов API
                /V1
                    ApiController.php - Основной контроллер (он тут один)
        /Middleware
            ApiTokenAuthMiddleware.php - Переопределение стандартной авторизации ларавел
        /Requests
            BaseApiRequest.php - Валидация запросов
        /Resources
            ConvertResource.php - Обёртка над респонсом конверта в ресурс
    /Interfaces
        /DataProviders - Интерфейс (один) для поставщиков данных
        /Pipelines - Интерфейс (один) для пайплайнов обработки (для фоллбека на другого поставщика)
        /Repositories - Интерфейсы репозиториев
        /Services - Интерфейсы сервисов
    /Jobs
        UpdateCurrenciesTableJob.php - Обновление списка монет в очереди
        UpdateExchangeRatesJob.php - Обновление обменных курсов в очереди
    /Models - Eloquent модели
    /Pipelines - Пайплайны
    /Providers - Стандартные сервис-провайдеры ларавел, тут нет смысла разделять
    /Repositories - Репозитории
        /Cached - Реализация кэширования для репозиториев
    /Rules - Правила валидации
    /Services 
        ConvertService.php - Сервис для обработки конвертации
        ExchangeRateService.php - Сервис для работы с курсами
    /UseCases - бизнес логика, тут она очень маленькая, можно было и без неё, просто в контроллере сервис дёргать
    /ValueObjects - VO для инкапсуляции и валидации данных
```

#### Поставщики данных

в `config/data-providers.php` можно настроить список поставщиков данных, можно несколько использовать, тут он один (
coincap), в задании их два было, но один из них не отвечает

в `app/Pipelines/DataProviderPipeline.php` пример пайплайна как это работает

используется повсеместно в джобах: `UpdateCurrenciesTableJob`, `UpdateExchangeRatesJob`

### Деплой

```shell
cp .env.example .env
docker-compose up --build
```

#### Прогнать миграции

```shell
docker ps
# берём id app контейнера
docker exec %id% php /app/artisan migrate --force
```

#### Засидить настройки

```shell
docker exec %id% php /app/artisan db:seed "SettingSeeder"
```

#### Принудительно обновить таблицу монет

```shell
docker exec %id% php /app/artisan currencies:force-update
```

#### Получение Bearer токена для авторизации

```shell
docker exec %id% php /app/artisan bearer-token:generate
# Пример ответа: 
# Bearer token: dQeS5XmYpFBsrKZhI3ZCa3eUEentrRKfMbn8XuXI_cRTAk_0E8VlSSseI7SI2Mhk
```

### Логи

```text
/storage/logs - папка с логами
    laravel.log - General лог приложения
    uncaught.log - Крит лог с экспешенами, которые не были отловлены в рантайме
    controllers.log - Лог с ошибками, перехваченными в контроллерах, тут всё ловится на верхнем уровне
    jobs.log - Лог с ошибками, перехваченными в джобах
    queue.log - Лог работы очередей из супервизора
    php-fpm.log - Лог php-fpm из супервизора (stdout)
    php-fpm-error.log - Лог php-fpm из супервизора (stderr)
    laravel-scheduler.log - Лог ларавельского крона (stdout)
    laravel-scheduler-error.log - Лог ларавельского крона (stderr)
```

### Примеры ответов API

#### rates: Получение всех курсов с учетом комиссии = 2% (GET запрос)

Время запроса: 197 мс на холодную, 24 мс с кэшем

```shell
curl -X GET "http://127.0.0.1/api/v1?method=rates&currency=USDT" \
     -H "Authorization: Bearer dQeS5XmYpFBsrKZhI3ZCa3eUEentrRKfMbn8XuXI_cRTAk_0E8VlSSseI7SI2Mhk"
# {
#     "status": "success",
#     "code": 200,
#     "data": {
#         "BTC": "0.0000090372",
#         "ETH": "0.0002458098",
#         "BNB": "0.0010110954",
#         "BCH": "0.0018535338",
#         "SOL": "0.0049514563",
#         "LTC": "0.0097031988",
#         "XRP": "0.35981418",
#         "GBP": "0.75582",
#         "CHF": "0.806208",
#         "EUR": "0.864654",
#         "USD": "1.02",
#         "USDC": "1.02",
#         "USDT": "1.02",
#         "SGD": "1.3",
#         "CAD": "1.41",
#         "AUD": "1.53",
#         "BGN": "1.68",
#         "NZD": "1.74",
#         "TRX": "3.03",
#         "ILS": "3.35",
#         "PLN": "3.68",
#         "QAR": "3.69",
#         "AED": "3.71",
#         "SAR": "3.79",
#         "MYR": "4.25",
#         "RON": "4.37",
#         "DOGE": "4.46",
#         "POL": "4.57",
#         "BRL": "5.39",
#         "DKK": "6.42",
#         "HRK": "6.48",
#         "CNY": "7.19",
#         "HKD": "7.86",
#         "SEK": "9.52",
#         "NOK": "10.1",
#         "ZAR": "17.45",
#         "MXN": "18.54",
#         "CZK": "20.93",
#         "THB": "32.71",
#         "UAH": "41.76",
#         "TRY": "41.97",
#         "EGP": "48.33",
#         "PHP": "58.93",
#         "INR": "89.62",
#         "RSD": "101.22",
#         "JPY": "148.34",
#         "PKR": "286.78",
#         "HUF": "330.9",
#         "CLP": "955.94",
#         "ARS": "1433.92",
#         "KRW": "1433.92",
#         "NGN": "1433.92",
#         "IDR": "8603.5"
#     }
# }
```

#### convert: Запрос на обмен валюты c учетом комиссии = 2%.

Время ответа: 210 мс на холодную, 26 мс с кэшем

```shell
curl -X POST "http://127.0.0.1/api/v1?method=convert&currency_from=ETH&currency_to=USD&value=10" \
     -H "Authorization: Bearer dQeS5XmYpFBsrKZhI3ZCa3eUEentrRKfMbn8XuXI_cRTAk_0E8VlSSseI7SI2Mhk"
# {
#     "status": "success",
#     "code": 200,
#     "data": {
#         "currency_from": "ETH",
#         "currency_to": "USD",
#         "value": "1",
#         "converted_value": "4216.45",
#         "rate": "4216.41"
#     }
# }
```

### Примеры ошибок

В задании был указан формат ошибок только для 403, для остальных я сделал по примеру

#### Запрос без токена

```shell
curl -X GET "http://127.0.0.1/api/v1?method=rates&currency=USDT"
# {
#     "status": "error",
#     "code": 403,
#     "message": "Invalid token"
# }
```

#### Запрос с невалидной валютой

```shell
curl "http://127.0.0.1/api/v1?method=rates&currency=%D0%B0%D0%B1%D0%B2_2" \
     -H "Authorization: Bearer dQeS5XmYpFBsrKZhI3ZCa3eUEentrRKfMbn8XuXI_cRTAk_0E8VlSSseI7SI2Mhk"
# {
#     "status": "error",
#     "code": 400,
#     "message": "Bad request.",
#     "errors": {
#         "currency": [
#             "The currency must be a valid currency symbol",
#             "The currency абв_2 is not supported."
#         ]
#     }
# }

```

#### Запрос с неподдерживаемой валютой

```shell
curl "http://127.0.0.1/api/v1?method=rates&currency=VERSE" \
     -H "Authorization: Bearer dQeS5XmYpFBsrKZhI3ZCa3eUEentrRKfMbn8XuXI_cRTAk_0E8VlSSseI7SI2Mhk"
# {
#     "status": "error",
#     "code": 400,
#     "message": "Bad request.",
#     "errors": {
#         "currency": [
#             "The currency VERSE is not supported."
#         ]
#     }
# }
```

#### Запрос с невалидной суммой

```shell
curl -X POST "http://127.0.0.1/api/v1?method=convert&currency_from=ETH&currency_to=USD&value=-2" \
     -H "Authorization: Bearer dQeS5XmYpFBsrKZhI3ZCa3eUEentrRKfMbn8XuXI_cRTAk_0E8VlSSseI7SI2Mhk"
# {
#     "status": "error",
#     "code": 400,
#     "message": "Bad request.",
#     "errors": {
#         "value": [
#             "The value is too great or too low"
#         ]
#     }
# }
```

#### Запрос к несуществующему методу API

```shell
curl -X POST "http://127.0.0.1/api/v1?method=earn_money&value=100&currency=USDT" \
     -H "Authorization: Bearer dQeS5XmYpFBsrKZhI3ZCa3eUEentrRKfMbn8XuXI_cRTAk_0E8VlSSseI7SI2Mhk"
# {
#     "status": "error",
#     "code": 404,
#     "message": "Method doesnt exist. Allowed methods are: rates,convert"
# }
```

#### Неверный REST запрос к API методу

```shell
curl -X GET "http://127.0.0.1/api/v1?method=convert&currency_from=ETH&currency_to=USD&value=10" \
     -H "Authorization: Bearer dQeS5XmYpFBsrKZhI3ZCa3eUEentrRKfMbn8XuXI_cRTAk_0E8VlSSseI7SI2Mhk"
# {
#     "status": "error",
#     "code": 400,
#     "message": "REST method GET for convert is not supported. Supported: POST"
# }
```

#### 500

```shell
curl -X POST "http://127.0.0.1/api/v1?method=convert&currency_from=ETH&currency_to=USD&value=10" \
     -H "Authorization: Bearer dQeS5XmYpFBsrKZhI3ZCa3eUEentrRKfMbn8XuXI_cRTAk_0E8VlSSseI7SI2Mhk"
# {
#   "status": "error",
#   "code": 500,
#   "message": "Server error."
# }
```
