![alt text](screenshots/01-describe.png)
Сейчас хэш занимает 60 символов, но функция может обновиться, и хэш начнёт занимать больше символов.

![alt text](screenshots/02-nav-guest.png)

![alt text](screenshots/03-nav-logged.png)
Так мы соблюдаем прицип DRY, новая ссылка появится сразу везде

![alt text](screenshots/04-register-layout.png)

![alt text](screenshots/05-login-layout.png)

![alt text](screenshots/06-register-done.png)

![alt text](screenshots/07-hash.png)
Это хеш алгоритма bcrypt, где $2y$ обозначает версию алгоритма, 10$ — коэффициент вычислительной сложности, далее идут соль и итоговый хеш, а увеличение стоимости до 15 сделает процесс проверки пароля в 32 раза медленнее

![alt text](screenshots/08-email-taken.png)
Может существовать 2 пользователя с одним email.

![alt text](screenshots/09-login-done.png)

![alt text](screenshots/10-wrong-password.png)
Защитная от перебора имени пользователя

![alt text](screenshots/11-cookie.png)
В куке хранится идентификатор сессии - случайная строка символов, которую генерирует сервер при успешном входе

![alt text](screenshots/12-cookie-attrs.png)
Если убрать флаг HttpOnly, то JavaScript получит доступ к кукам через document.cookie, что при наличии XSS-уязвимости позволит злоумышленнику украсть session_id и полностью перехватить сессию пользователя.

![alt text](screenshots/13-httponly-check.png)
Благодря флагу HttpOnly

![alt text](screenshots/14-session-file.png)
В файле на сервере хранится полезная информация: id пользователя, имя, все, что мы положим в $_SESSION. А в куке хранится только ID сессии.
Данные так разделяют для безопасности, чтоб нельзя было поменять user_id или попасть в чужой аккаунт. 

![alt text](screenshots/15-redirect.png)

![alt text](screenshots/16-posts-authors.png)
напишите SQL с JOIN, которым вы тянете посты. Почему JOIN, а не два отдельных запроса?

![alt text](screenshots/17-submit-layout.png)

![alt text](screenshots/18-after-logout.png)

![alt text](screenshots/19-cookie-gone.png)
session_destroy() удаляет данные сессии только на сервере, но не трогает куку в браузере, поэтому без setcookie() с прошедшей датой браузер продолжит отправлять старый PHPSESSID, и при следующем session_start() PHP создаст новую сессию (отсюда мгновенное появление новой куки); если выполнить только session_destroy() — на клиенте останется «мёртвая» кука, которая будет создавать пустые сессии, а если только setcookie() — на сервере останется «файл-зомби»

![alt text](screenshots/20-expired.png)
Когда мы удаляем файл сессии, бразуер об этом не знает и продолжает отправлять куку с ID.