import jwt
from fastapi import Header, HTTPException
import os

# Читаем содержимое файла с ключом
PUBLIC_KEY_PATH = os.environ.get('OAUTH_PUBLIC_KEY', '/app/oauth-public.key')

with open(PUBLIC_KEY_PATH, 'r') as f:
    PUBLIC_KEY = f.read().strip()

async def get_current_user(authorization: str = Header(None)):
    if not authorization or not authorization.startswith('Bearer '):
        raise HTTPException(401, detail='Token required')
    
    token = authorization.split(' ')[1]
    try:
        payload = jwt.decode(
            token, 
            PUBLIC_KEY,
            algorithms=['RS256'],
            options={'verify_aud': False}
        )
        return payload
    except jwt.ExpiredSignatureError:
        raise HTTPException(401, detail='Token expired')
    except jwt.InvalidTokenError as e:
        raise HTTPException(401, detail=f'Invalid token: {e}')