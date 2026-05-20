from fastapi import APIRouter, WebSocket, FastAPI
from typing import List 
from datetime import datetime


router = APIRouter()

class ConnectionManager: 

    def __init__(self): 

        self.active: List[WebSocket] = [] 

 

    async def connect(self, ws: WebSocket): 

        await ws.accept() 

        self.active.append(ws) 

 

    def disconnect(self, ws: WebSocket): 

        self.active.remove(ws) 

 

    async def broadcast(self, message: dict): 

        import json 

        dead = [] 

        for ws in self.active: 

            try: 

                await ws.send_text(json.dumps(message)) 

            except: 

                dead.append(ws) 

        for ws in dead: 

            self.active.remove(ws) 

 

manager = ConnectionManager() 

@router.websocket('/ws')
async def websocket_endpoint(ws: WebSocket): 
    await manager.connect(ws) 
    try: 
        while True: 
            await ws.receive()  # держим соединение 
    except: 
        manager.disconnect(ws) 
