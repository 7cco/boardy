from fastapi import FastAPI, Request
from datetime import datetime
import aiomysql, json, asyncio
import redis.asyncio as aioredis 
from routers import comments, ws
from fastapi.middleware.cors import CORSMiddleware
from routers.ws import manager
from contextlib import asynccontextmanager 
from database import get_db, db_query, db_query_one, db_insert, db_execute


async def redis_subscriber():
    redis = await aioredis.from_url('redis://redis:6379')
    pubsub = redis.pubsub()
    await pubsub.subscribe('new_post', 'user.renamed', 'post_deleted', 'post_updated')
    print("✅ Redis subscribed to channels")

    async for message in pubsub.listen():
        if message['type'] != 'message':
            continue
        print(f"📡 Redis message received: {message['channel']}")
        channel = message['channel'].decode()
        data = json.loads(message['data'])
        
        if channel == 'new_post':
            print(f"📤 Broadcasting new_post: {data}")  # ← ДОБАВИТЬ
            await manager.broadcast({'type': 'new_post', 'post': data})
        elif channel == 'user.renamed':
            await db_execute(
                'UPDATE comments SET author_name=%s WHERE author_id=%s',
                data['new_name'], data['id']
            )
            await manager.broadcast({
                'type': 'user_renamed',
                'user_id': data['id'],
                'new_name': data['new_name']
            })
        elif channel == 'post_deleted':
            print(f"🗑️ Broadcasting post_deleted: {data}")
            await manager.broadcast({
                'type': 'post_deleted',
                'post_id': data['id']
            })
        elif channel == 'post_updated':
            print(f"✏️ Broadcasting post_updated: {data}")
            await manager.broadcast({
                'type': 'post_updated',
                'post': data
            })

@asynccontextmanager
async def lifespan(app: FastAPI):
    task = asyncio.create_task(redis_subscriber())
    try:
        yield
    finally:
        task.cancel()
        try:
            await task
        except asyncio.CancelledError:
            pass

app = FastAPI(title='Boardy API', version='0.2.0', lifespan=lifespan)

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

app.include_router(comments.router)
app.include_router(ws.router)

DB_CONFIG = {
	'host': 'mysql',
	'port': 3306,
	'user': 'boardy',
	'password': '250177',
	'db': 'boardy_api',
	'charset': 'utf8mb4',
}

async def get_db():
	return await aiomysql.connect(**DB_CONFIG)

@app.get('/api/status')
async def status():
	return {'status': 'ok', 'time': str(datetime.now())}

@app.get('/api/messages')
async def get_messages():
	conn = await get_db()
	async with conn.cursor(aiomysql.DictCursor) as cur:
		await cur.execute(
			'SELECT posts.body AS message, users.name, '
			'posts.created_at FROM posts '
			'JOIN users ON posts.author_id = users.id '
			'ORDER BY posts.created_at DESC'
		)
		messages = await cur.fetchall()
	conn.close()

	for m in messages:
		m['created_at'] = str(m['created_at'])
	return {'messages': messages, 'count': len(messages)}

@app.get('/api/users')
async def get_users():
	conn = await get_db()
	async with conn.cursor(aiomysql.DictCursor) as cur:
		await cur.execute(
			'SELECT id, name, email, created_at FROM users'
		)
		users = await cur.fetchall()
	conn.close()
	for u in users:
		u['created_at'] = str(u['created_at'])
	return {'users': users, 'count': len(users)}
