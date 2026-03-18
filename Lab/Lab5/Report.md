# 1) Установка certbot
![alt text](screenshots/01-certbot-installed.jpg)

# 2) Получение сертификата
![alt text](screenshots/02-certbot-success.jpg)

# 3) Проверка в браузере
![alt text](screenshots/03-browser-lock.jpg)

# 4) Информация о сертификате
![alt text](screenshots/04-certificate-info.jpg)

# 5) Редирект
![alt text](screenshots/05-redirect.jpg)

## 301 Moved Permanently
## Location: https://student.terramorf.ai-info.ru./

# 6) Конфиг после certbot
![alt text](screenshots/06-nginx-ssl-config.jpg)

## listen 443 ssl; # managed by Certbot
## ssl_certificate /etc/letsencrypt/live/student.terramorf.ai-info.ru/fullchain.pem; # managed by 
## ssl_certificate_key /etc/letsencrypt/live/student.terramorf.ai-info.ru/privkey.pem; # managed by Certbot

# 7) Сертификат для api-поддомена
![alt text](screenshots/07-api-certbot.jpg)

# 8) Проверка обоих доменов
![alt text](screenshots/08-both-https.jpg)

# 9) TLS handshake
![alt text](screenshots/09-tls-handshake.jpg)

## **Версия TLS:** TLSv1.3
## **Алгоритм шифрования:** TLS_AES_256_GCM_SHA384
## **Subject:** CN=student.terramorf.ai-info.ru
## **Issuer:** C=US, O=Let's Encrypt, CN=E7
## **Срок действия:** 18 марта 2026 - 16 июня 2026

# 10) Цепочка доверия
![alt text](screenshots/10-chain.jpg)

## **Корневой CA (Root)**
###   Subject: C=US, O=Internet Security Research Group, CN=ISRG Root X1
###  
### ↓ подписывает
###
## **Промежуточный CA (Intermediate)**
###  Subject: C=US, O=Let's Encrypt, CN=E7
###   Issuer:  C=US, O=Internet Security Research Group, CN=ISRG Root 
###  
### ↓ подписывает
###
## **Сертификат сайта (Leaf/End-entity)**
###   Subject: CN=student.terramorf.ai-info.ru
###   Issuer:  C=US, O=Let's Encrypt, CN=E7

# 11) Сравнение сертификатов
![alt text](screenshots/11-compare-certs.jpg)

## **Общее:**
### 1) Срок действия
### 2) Дата выдачи
### 3) Дата истечения
### 4) Доменная зона
### 5) Центр сертификации
###
## **Различия:**
### 1) Common Name (CN)
### 2) Время выдачи
### 3) Время истечения

# 12) HSTS
![alt text](screenshots/12-hsts.jpg)

## **HSTS** — это политика безопасности, которая предписывает браузеру взаимодействовать с сайтом только через зашифрованное HTTPS-соединение, блокируя любые незащищённые HTTP-запросы. Это защищает пользователей от атак понижения протокола (SSL stripping) и перехвата сессионных cookies, так как браузер автоматически заменяет http:// на https:// ещё до отправки данных.

# 13) Кэширование и gzip
![alt text](screenshots/13-cache-gzip.jpg)

# 14) Автообновление
![alt text](screenshots/14-renew.jpg)

# 15) Pull Request
![alt text](screenshots/15-pull-request.jpg)