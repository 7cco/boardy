## Вывод systemctl status nginx (active)
![alt text](screenshots/01-nginx-status.png)

## Дефолтная страница Nginx в браузере
![alt text](screenshots/02-browser-ip.png)

## Выводcurl -v
![alt text](screenshots/03-curl.png)

### GET / HTTP/1.1
### HTTP/1.1 200 OK
### Content-Type: text/html

## Вывод ls -la /var/www/
### До
![alt text](screenshots/04-permission-1.png)
### После
![alt text](screenshots/04-permission-2.png)

### listen 80 default_server
Директива указывает Nginx принимать HTTP-запросы на порту 80
### root /var/www/html
Директива задаёт корневую директорию для хранения файлов сайта
### server_name _
Директива определяет имена доменов, на которые давать ответ, _ используется для обработки любых запросов, не попавших в другие хосты.
### index 
index.html index.htm index.nginx-debian.html - Директива задаёт приоритетный список файлов, которые Nginx будет искать и отдавать при запросе директории

## Созданная зона
![alt text](screenshots/05-dns-zone.png)

## A-запись
![alt text](screenshots/06-a-record.png)

## Вывод ping
![alt text](screenshots/07-ping.png)

## Вывод dig
![alt text](screenshots/08-dig.png)
### QUESTION SECTION
student.terramorf.ai-info.ru.  IN      A
### ANSWER SECTION
student.terramorf.ai-info.ru. 54 IN     A       158.160.31.95
### SERVER
127.0.0.53#53(127.0.0.53) (UDP)

## Вывод dig +trace
![alt text](screenshots/09-dig-trace.png)
Запрос начался с локального резолвера (127.0.0.53), затем прошёл через корневой сервер m.root-servers.net (202.12.27.33) к TLD-серверу .ru f.dns.ripn.net (193.232.156.17), далее к авторитетному серверу ai-info.ru — ns4.netangels.ru (80.87.101.2), который делегировал поддомен terramorf.ai-info.ru на ns1.yandexcloud.net (84.201.185.208), и тот вернул финальную A-запись: student.terramorf.ai-info.ru (158.160.31.95)

## Страница Nginx в браузере
![alt text](screenshots/10-browser-domain.png)