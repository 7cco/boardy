# database.py — подключение к MySQL (aiomysql)
#
# aiomysql — асинхронный драйвер.
# await — не блокирует event loop при запросе к БД.
# Обычный mysql.connector заблокировал бы, как time.sleep.

import aiomysql

DB_CONFIG = {
    'host': 'mysql',
    'port': 3306,
    'user': 'boardy',          # ← ваш пользователь БД
    'password': '250177',  # ← ваш пароль
    'db': 'boardy_api',              # ← ваша база данных
    'charset': 'utf8mb4',      # ← полный Unicode, включая эмодзи
}

async def get_db():
    conn = await aiomysql.connect(**DB_CONFIG)
    try:
        async with conn.cursor(aiomysql.DictCursor) as cur:
            yield cur
    finally:
        conn.close()

async def db_query(query, *args):
    async with aiomysql.connect(**DB_CONFIG) as conn:
        async with conn.cursor(aiomysql.DictCursor) as cur:
            await cur.execute(query, args)
            return await cur.fetchall()

async def db_query_one(query, *args):
    res = await db_query(query, *args)
    return res[0] if res else None

async def db_insert(query, *args):
    async with aiomysql.connect(**DB_CONFIG) as conn:
        async with conn.cursor() as cur:
            await cur.execute(query, args)
            await conn.commit()
            return cur.lastrowid

async def db_execute(query, *args):
    async with aiomysql.connect(**DB_CONFIG) as conn:
        async with conn.cursor() as cur:
            await cur.execute(query, args)
            await conn.commit()