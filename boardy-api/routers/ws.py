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
        print(f"📢 Attempting to broadcast to {len(self.active)} clients")
        for ws in self.active: 
            try: 
                await ws.send_text(json.dumps(message)) 
                print(f"✅ Sent to client")
            except: 
                print(f"❌ Failed to send:")
                dead.append(ws) 
        for ws in dead: 
            self.active.remove(ws) 
            
manager = ConnectionManager() 

@router.websocket('/ws')
async def websocket_endpoint(ws: WebSocket): 
    print(f"🔌 New WS connection attempt")
    await manager.connect(ws) 
    print(f"✅ WS accepted, total active: {len(manager.active)}")
    try: 
        while True: 
            await ws.receive()
    except: 
        print(f"🔌 WS disconnected:")
        manager.disconnect(ws) 
