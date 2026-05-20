# database.py — подключение к MySQL (aiomysql)
#
# aiomysql — асинхронный драйвер.
# await — не блокирует event loop при запросе к БД.
# Обычный mysql.connector заблокировал бы, как time.sleep.

import aiomysql

DB_CONFIG = {
    'host': '127.0.0.1',
    'port': 3306,
    'user': 'boardy',          # ← ваш пользователь БД
    'password': '250177',  # ← ваш пароль
    'db': 'boardy',              # ← ваша база данных
    'charset': 'utf8mb4',      # ← полный Unicode, включая эмодзи
}

async def get_db():
    return await aiomysql.connect(**DB_CONFIG)
