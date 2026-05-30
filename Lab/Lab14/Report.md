
![alt text](screenshots/01-passport-install.png)
![alt text](screenshots/02-spa-client.png)
Публичный клиент (SPA в браузере) не может хранить client_secret — он виден в исходном коде. PKCE заменяет secret динамическим code_verifier, который знает только браузер: он хешируется в code_challenge для /authorize, а оригинал отправляется в /token для проверки. Это защищает от атаки перехвата authorization code (злоумышленник, перехвативший code, не сможет обменять его на токен без verifier'а).   

![alt text](screenshots/03-token-ttl.png)
Короткий access (15 мин) минимизирует окно ущерба при утечке — украденный токен быстро протухнет. Refresh длинный (30 дней), но защищён HttpOnly cookie от XSS. Если access будет 24 часа — утечка даст злоумышленнику целые сутки доступа к API от имени пользователя.

![alt text](screenshots/04-pkce-curl-1.png)
![alt text](screenshots/04-pkce-curl-2.png)
Получение authorization code через redirect
Обмен authorization code на access/refresh tokens
Проверка code_verifier на сервере

![alt text](screenshots/05-databases.png)
![alt text](screenshots/06-comments-schema.png)
В коде используется подход "Application-Level Integrity" (целостность на уровне приложения), а не "Database-Level Integrity" (на уровне БД). Если нет FK на уровне БД, вся ответственность ложится на код.

![alt text](screenshots/07-fastapi-db.png)

![alt text](screenshots/08-rs256-success.png)
![alt text](screenshots/09-rs256-fail.png)
RS256 использует асимметричное шифрование: Passport держит приватный ключ, а FastAPI проверяет публичным — ключ не нужно передавать по сети. HS256 требует общий секрет на всех сервисах: его компрометация ломает всю систему, а в микросервисах секрет приходится копировать на каждый узел.

![alt text](screenshots/10-crud-all.png)
author_name — бизнес-данные конкретного запроса, а не идентификационный claim. JWT содержит sub (user_id), который не меняется, но имя пользователя может отличаться от текущего в БД (например, если только что сменили). Если зашить имя в JWT custom claim — придётся перевыпускать токен при каждом rename, что ломает UX (все сессии слетят).

![alt text](screenshots/11-owner-check.png)
В routers/comments.py — строки if existing['author_id'] != user['sub']: raise HTTPException(403, 'Not your comment') в методах update и delete. Если убрать — любой авторизованный пользователь сможет редактировать/удалять чужие комментарии, зная их ID.

![alt text](screenshots/12-cors-config.png)
Браузер блокирует это по спецификации CORS: wildcard с credentials=true создаёт уязвимость, позволяя любому сайту красть куки аутентификации. Если бы пропустил — злоумышленникский сайт мог бы отправить запрос от имени залогиненного пользователя и украсть его сессию/токены (CSRF-подобная атака через CORS).

![alt text](screenshots/13-pkce-utils.png)
Challenge — это публичный хеш, который передаётся открыто в URL при редиректе на /authorize. Verifier — секрет, отправляется только в POST /token по защищённому каналу. Если перепутать — сервер не сможет проверить соответствие: verifier в URL раскроет секрет, а challenge в POST не пройдёт валидацию (PKCE сломается).

![alt text](screenshots/14-login-redirect.png)
![alt text](screenshots/15-login-callback.png)

![alt text](screenshots/16-token-exchange.png)
State защищает от CSRF-атаки на OAuth flow: злоумышленник не может подделать state, сгенерированный приложением. Если убрать проверку — возможна атака authorization code injection: злоумышленник заставляет жертву авторизоваться под своим аккаунтом, а потом подменяет code в callback, вынуждая приложение выдать токены жертве от имени атакующего.

![alt text](screenshots/17-refresh-cookie.png)
Если refresh_token хранится в localStorage, злоумышленник через XSS может выполнить localStorage.getItem('refresh_token'), украсть его и отправлять на свой сервер. Далее он сможет бесконечно получать новые access_token через silent refresh, полностью скомпрометировав аккаунт жертвы в фоновом режиме.
Именно поэтому refresh_token кладут в HttpOnly cookie — JavaScript не может её прочитать, даже если XSS сработала.

![alt text](screenshots/18-silent-refresh.png)

![alt text](screenshots/19-redis-ping.png)

![alt text](screenshots/20-laravel-publish.png)
Redis decouples сервисы: Laravel не знает о существовании FastAPI и не ждёт ответа (fire-and-forget). Http::post() создаёт runtime-зависимость — если FastAPI недоступен, Laravel падает с ошибкой или ждёт таймаута, блокируя запрос пользователя.


![alt text](screenshots/21-subscriber-running.png)

![alt text](screenshots/22-broadcast-flow.png)

![alt text](screenshots/23-user-renamed.png)
Магия в AppServiceProvider::boot() — строка User::observe(UserObserver::class) регистрирует observer на модели. Laravel через Eloquent events (saved, updated и т.д.) автоматически вызывает методы observer'а при изменении модели — это паттерн Observer, встроенный в ORM.

![alt text](screenshots/24-denorm-before.png)
![alt text](screenshots/25-denorm-after.png)
Это модель согласованности, при которой данные временно расходятся между сервисами, но со временем приходят к единому состоянию. Задержка между сменой имени и обновлением comments возникает в интервале: Laravel опубликовал в Redis → сообщение ещё не обработано FastAPI (сетевые задержки, переподключение pub/sub, загрузка FastAPI). В этот момент comments содержат старое имя — система несогласованна, но станет согласованной после обработки события.

![alt text](screenshots/26-two-browsers-post.png)
![alt text](screenshots/27-two-browsers-comment-1.png)
![alt text](screenshots/27-two-browsers-comment-2.png)
![alt text](screenshots/27-two-browsers-comment-3.png)

![alt text](screenshots/28-no-http-callback.png)
нигде в коде нет Http::post()

![alt text](screenshots/29-nginx-no-internal.png)