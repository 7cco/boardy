![alt text](screenshots/01-php-version.png)

![alt text](screenshots/02-php-form.png)

![alt text](screenshots/03-php-messages.png)

![alt text](screenshots/04-nginx-php.png)
## fastcgi_pass
это директива nginx для передачи запросов любому FastCGI-серверу, а fcgiwrap - это конкретный сервис-обёртка, позволяющая запускать обычные CGI-скрипты через протокол FastCGI.

## PHP-FPM
быстрее, потому что использует постоянные рабочие процессы, которые не нужно создавать заново для каждого запроса, в отличие от CGI, где на каждый запрос создается новый процесс.

![alt text](screenshots/05-shared-nothing.png)
Счётчик не растёт, потому что переменные не сохраняются между запросами, которые обрабатываются изолировано.
## shared nothing
это архитектурный подход, при котором каждый запрос обрабатывается независимо в собственном процессе с изолированной памятью

![alt text](screenshots/06-php-slow.png)
Около 4 секунд
4 воркера (PID: 903, 904, 1761, 1762).
10 запросов разделились на 3 "волны" по 4 воркера:
1-я волна: 4 запроса (18:39:29)
2-я волна: 4 запроса (18:39:31)
3-я волна: 2 запроса (18:39:33)

![alt text](screenshots/07-api-status.png)

![alt text](screenshots/08-api-messages.png)

![alt text](screenshots/09-counter.png)
Uvicorn не уничтожает состояние после запроса, поэтому счётчик растёт

![alt text](screenshots/10-async-slow.png)
Запросы выполнялись параллельно, поэтому всё заняло даже меньше 2 сек

![alt text](screenshots/11-blocking.png)
Они отличаются использованием time.sleep, которая блокирует параллельность, поэтому и длится дольше

![alt text](screenshots/12-swagger.png)
docs из следующей практики

![alt text](screenshots/13-systemd.png)

![alt text](screenshots/14-nginx-api.png)
proxy_pass пересылает запросы по HTTP, а fastcgi_pass использует протокол FastCGI.
PHP традиционно работает через PHP-FPM (FastCGI Process Manager), который использует протокол FastCGI
Python-приложение работает как независимый HTTP-сервер и общается с nginx по стандартному HTTP-протоколу

![alt text](screenshots/15-compare.png)
HTML - для людей: готов к отображению в браузере,
содержит разметку, стили, структуру,
человек видит красивую таблицу

JSON - для программ (API):машиночитаемый формат,
легко парсить кодом, только данные без оформления, используется в API для интеграций

![alt text](screenshots/16-processes.png)

![alt text](screenshots/17-pull-request.png)