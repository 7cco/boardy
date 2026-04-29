from fastapi import APIRouter, HTTPException, Depends
from pydantic import BaseModel
from database import get_db
from auth import get_current_user
import aiomysql


router = APIRouter()

# Получить все комментарии к посту
@router.get('/posts/{post_id}/comments')
async def get_comments(post_id: int):
    conn = await get_db()
    async with conn.cursor(aiomysql.DictCursor) as cur:
        await cur.execute(
            'SELECT c.id, c.body, c.created_at, '
            'u.name AS author_name '
            'FROM comments c '
            'JOIN users u ON  c.author_id = u.id '
            'WHERE c.post_id = %s '
            'ORDER BY c.created_at',
            (post_id,)
        )
        items = await cur.fetchall()
    conn.close()
    for item in items:
        item['created_at'] = str(item['created_at'])
    return {'items': items, 'count': len(items)}


# Создать комментарий к посту
class CommentCreate(BaseModel):
    body: str

@router.post('/posts/{post_id}/comments', status_code=201)
async def create_comment(post_id: int, data: CommentCreate, user = Depends(get_current_user)):
    if not data.body.strip():
        raise HTTPException(status_code=422, detail='Текст пустой')
    
    author_id = user['user_id']
    
    conn = await get_db()
    async with conn.cursor() as cur:
        # Проверяем что пост существует
        await cur.execute('SELECT id FROM posts WHERE id=%s', (post_id,))
        if not await cur.fetchone():
            conn.close()
            raise HTTPException(status_code=404, detail='Пост не найден')
            
        # Создаём комментарий с реальным author_id
        await cur.execute(
            'INSERT INTO comments (body, post_id, author_id) VALUES (%s, %s, %s)',
            (data.body, post_id, author_id)
        )
        await conn.commit()
        new_id = cur.lastrowid
        conn.close()
        
        return {'id': new_id, 'body': data.body, 'author_id': author_id, 'status': 'created'}

# Обновить комментарий по ID
class CommentUpdate(BaseModel):
    body: str

@router.put('/comments/{comment_id}')
async def update_comment(comment_id: int, data: CommentUpdate):
    if not data.body.strip():
        raise HTTPException(status_code=422, detail='Текст пустой')
    conn = await get_db()
    async with conn.cursor() as cur:
        await cur.execute(
            'UPDATE comments SET body=%s WHERE id=%s',
            (data.body, comment_id)
        )
        if cur.rowcount == 0:
            conn.close()
            raise HTTPException(status_code=404, detail='Не найден')
        await conn.commit()
    conn.close()
    return {'id': comment_id, 'body': data.body, 'status': 'updated'}


# Удалить комментарий по ID
@router.delete('/comments/{comment_id}', status_code=204)
async def delete_comment(comment_id: int):
    conn = await get_db()
    async with conn.cursor() as cur:
        await cur.execute('DELETE FROM comments WHERE id=%s', (comment_id,))
        if cur.rowcount == 0:
            conn.close()
            raise HTTPException(status_code=404, detail='Комментарий не найден')
        await conn.commit()
    conn.close()
