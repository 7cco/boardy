![alt text](screenshots/01-directory.jpg)

![alt text](screenshots/02-vhost-config.jpg)
**server_name** указывает имя домена, по которому nginx выбирает соответствующий виртуальный сервер для обработки запроса.

**root** задает корневую директорию на файловой системе, относительно которой располагаются файлы сайта.

**access_log** определяет путь к файлу, в который записывается лог всех обработанных клиентских запросов.

**error_log** задает путь к файлу, куда сохраняются сообщения об ошибках и предупреждениях работы сервера.

**try_files** последовательно проверяет существование указанных файлов или директорий и возвращает первый найденный ресурс или код ответа.

**error_page** настраивает отображение пользовательских страниц при возникновении указанных кодов ошибок HTTP.

![alt text](screenshots/03-landing.png)

![alt text](screenshots/04-form.jpg)

![alt text](screenshots/05-404.jpg)

![alt text](screenshots/06-dns-api.jpg)

![alt text](screenshots/07-dig-api.jpg)

![alt text](screenshots/08-api-config.jpg)

![alt text](screenshots/09-api-browser.jpg)

![alt text](screenshots/10-curl-v.jpg)
**Стартовая строка** GET / HTTP/1.1
**Заголовок** Host: student.terramorf.ai-info.ru
**Стартовая строка ответа** HTTP/1.1 200 OK
**Content-Type** text/html
**Content-Length** 891

![alt text](screenshots/11-vhosts.jpg)
Веб-сервер nginx получает запрос на один IP-адрес, но смотрит на значение заголовка, сравнивает значение Host с директивами server_name в своей конфигурации и выбирает соответствующий блок, третий запрос вернул default_server, потому что не нашёл указаного Host, у меня это boardy-api,
т.к. я его указал таковым

![alt text](screenshots/12-post-405.jpg)
POST /feedback.html HTTP/1.1
Content-Type application/x-www-form-urlencoded
тело запроса name=Ivanov&message=Hello
Потому что Nginx не обрабатывает данные, поэтому выдаёт 405

GET возвращает заголовки и тело ответа, а HEAD только заголовки.
HEAD можно использовать для проверки на существования страницы,получение метаданных, проверка ссылок

![alt text](screenshots/13-logs.jpg)

![alt text](screenshots/14-log-stats.jpg)

![alt text](screenshots/15-pull-request.png)