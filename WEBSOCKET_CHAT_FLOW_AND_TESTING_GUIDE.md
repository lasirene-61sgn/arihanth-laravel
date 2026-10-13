# 📬 Complete Guide: Testing Real-Time WebSockets in Postman & Frontend Connection

This guide provides an **exact step-by-step walkthrough** to test the entire real-time chat flow (Login ➡️ Connect WebSocket ➡️ Authorize Room ➡️ Send Message ➡️ Receive Live Event) **100% inside Postman**, with **no HTML or frontend files required**.

It also includes the exact code snippets your **Frontend Team (Web & Mobile)** needs to connect to the WebSocket server using their Bearer token.

---

## 🏗️ Architecture: How WebSockets Work with REST API

In modern scalable apps, messages are **sent via HTTP REST API** and **received instantly via WebSockets**:

```
[Postman Tab 1: HTTP API]                [Laravel Backend]               [Postman Tab 2: WebSocket]
       (Sender)                                                                  (Receiver)
          |                                      |                                    |
          |-- 1. POST /login ------------------->|                                    |
          |   (Get Bearer Token)                 |                                    |
          |                                      |<-- 2. Connects ws://localhost:8080-|
          |                                      |    (Gets socket_id)                |
          |                                      |                                    |
          |                                      |<-- 3. POST /broadcasting/auth -----|
          |                                      |    (Authorizes room with Token)    |
          |                                      |                                    |
          |                                      |<-- 4. pusher:subscribe -----------|
          |                                      |    (Listens to private room)       |
          |                                      |                                    |
          |-- 5. POST /messages (HTTP) --------->|                                    |
          |   {"body": "Hello!"}                 |-- Saves to MySQL                   |
          |                                      |-- Broadcasts Event via Reverb ---->|
          |<-- 6. 201 Created (HTTP Response) ---|                                    | 7. LIVE MESSAGE POPS UP!
          |                                      |                                    |    event: "message.sent"
```

---

## ⚡ Prerequisites: Ensure Backend Servers Are Running

Make sure both services are running in your terminal:

```bash
# Terminal 1: Laravel Web API Server (Port 8000)
php artisan serve

# Terminal 2: Reverb WebSocket Server (Port 8080)
php artisan reverb:start --debug
```

---

## 🚀 Complete Step-by-Step Walkthrough in Postman

---

### Step 1: Login in Postman (Get Bearer Token)

First, authenticate any user (Super Admin, Buyer, Craftsman, or Key User) to obtain a Sanctum Bearer token.

1. In Postman, create an **HTTP Request** (Tab 1):
   - **Method:** `POST`
   - **URL:** `http://127.0.0.1:8000/api/login`
2. **Headers:**
   - `Content-Type`: `application/json`
   - `Accept`: `application/json`
3. **Body (raw JSON):**
   ```json
   {
       "email": "SU0001",
       "password": "password123"
   }
   ```
   *(You can pass user code `SU0001`, mobile number, or email address)*
4. Click **Send**.
5. **Response (200 OK):**
   ```json
   {
       "success": true,
       "message": "Login successful",
       "role": "super_admin",
       "token": "1|abc123yourgeneratedsanctumtoken...",
       "user": {
           "id": 1,
           "full_name": "Super Admin"
       }
   }
   ```
6. **Copy the `token` value** (e.g. `1|abc123yourgeneratedsanctumtoken...`). You will use this token in all subsequent steps.

---

### Step 2: Open a WebSocket Tab in Postman & Connect

Postman has a dedicated native WebSocket client.

1. In Postman, click **New** (top left) ➡️ Select **WebSocket Request** (or click the **`+`** icon and choose **WebSocket**).
2. Set the WebSocket URL:
   ```
   ws://127.0.0.1:8080/app/lffoipjc57ebkzslnfcc?protocol=7&client=js&version=8.4.0-reverb&flash=false
   ```
   *(Note: `lffoipjc57ebkzslnfcc` is your `REVERB_APP_KEY` from `.env`)*
3. Click the blue **Connect** button on the right.
4. Look at the **Messages** pane below. You will immediately see an incoming green frame:
   ```json
   {
       "event": "pusher:connection_established",
       "data": "{\"socket_id\":\"284910293.1092837465\",\"activity_timeout\":30}"
   }
   ```
5. **Copy the `socket_id` string** (for example: `284910293.1092837465`).

---

### Step 3: Authorize the Private Room (HTTP Request)

Because chat conversations and groups are private, Laravel requires an authorization signature before a socket is allowed to listen to that channel.

1. Open a new **HTTP Request** tab in Postman:
   - **Method:** `POST`
   - **URL:** `http://127.0.0.1:8000/api/broadcasting/auth`
2. **Headers:**
   - `Authorization`: `Bearer 1|abc123yourgeneratedsanctumtoken...` *(From Step 1)*
   - `Content-Type`: `application/json`
   - `Accept`: `application/json`
3. **Body (raw JSON):**
   ```json
   {
       "socket_id": "284910293.1092837465",
       "channel_name": "private-conversation.1"
   }
   ```
   *(For group chats, use `"channel_name": "private-group.1"`)*
4. Click **Send**.
5. **Response (200 OK):**
   ```json
   {
       "auth": "lffoipjc57ebkzslnfcc:3a1b4c7d9e2f8a1b..."
   }
   ```
6. **Copy the entire `"auth"` string value** (`lffoipjc57ebkzslnfcc:...`).

---

### Step 4: Subscribe to the Room in the WebSocket Tab

1. Switch back to your **Postman WebSocket Tab** (from Step 2).
2. In the bottom message input area:
   - Select **JSON** format.
   - Paste the following subscribe message:
     ```json
     {
         "event": "pusher:subscribe",
         "data": {
             "channel": "private-conversation.1",
             "auth": "lffoipjc57ebkzslnfcc:3a1b4c7d9e2f8a1b..."
         }
     }
     ```
     *(Insert the exact `"auth"` string from Step 3)*
3. Click the blue **Send** button.
4. In the **Messages** pane, you will instantly receive:
   ```json
   {
       "event": "pusher_internal:subscription_succeeded",
       "channel": "private-conversation.1",
       "data": "{}"
   }
   ```
   > 🟢 **You are now subscribed and actively listening to live messages on Conversation #1!** Keep this WebSocket tab open.

---

### Step 5: Send a Message via REST API (HTTP Request)

Now simulate sending a message from any user.

1. Open an **HTTP Request** tab in Postman:
   - **Method:** `POST`
   - **URL:** `http://127.0.0.1:8000/api/common/chat/conversations/1/messages`
2. **Headers:**
   - `Authorization`: `Bearer 1|abc123yourgeneratedsanctumtoken...`
   - `Content-Type`: `application/json`
   - `Accept`: `application/json`
3. **Body (raw JSON):**
   ```json
   {
       "body": "Hello Arun! Testing live WebSocket delivery from Postman."
   }
   ```
4. Click **Send**.
5. **Response (201 Created):**
   ```json
   {
       "success": true,
       "message": "Message sent successfully.",
       "data": {
           "id": 209,
           "conversation_id": 1,
           "chat_group_id": null,
           "sender_id": 1,
           "sender_type": "App\\Models\\ProcessOwner",
           "body": "Hello Arun! Testing live WebSocket delivery from Postman.",
           "created_at": "2026-10-13T13:15:00.000000Z"
       }
   }
   ```

---

### Step 6: Watch the Message Arrive Live in the WebSocket Tab!

Immediately switch back to your **Postman WebSocket Tab**.

In the **Messages** pane, you will see a new green incoming frame appear **in real time**:

```json
{
    "event": "message.sent",
    "channel": "private-conversation.1",
    "data": {
        "id": 209,
        "conversation_id": 1,
        "chat_group_id": null,
        "sender_id": 1,
        "sender_name": "Super Admin",
        "sender_code": "SU0001",
        "sender_avatar": null,
        "body": "Hello Arun! Testing live WebSocket delivery from Postman.",
        "attachments": [],
        "created_at": "2026-10-13T13:15:00.000000Z"
    }
}
```

> 🎉 **Full Cycle Verified!**
> 1. Message sent via Postman HTTP API.
> 2. Saved in database.
> 3. Dispatched to Reverb WebSocket server.
> 4. Received live in Postman WebSocket client without refreshing or polling.

---

## 👥 Testing Group Chats in Postman

Testing group chats follows the exact same pattern:

1. **Authorize Channel (HTTP):**
   - **URL:** `http://127.0.0.1:8000/api/broadcasting/auth`
   - **Body:**
     ```json
     {
         "socket_id": "284910293.1092837465",
         "channel_name": "private-group.5"
     }
     ```
2. **Subscribe (WebSocket Tab):**
   ```json
   {
       "event": "pusher:subscribe",
       "data": {
           "channel": "private-group.5",
           "auth": "lffoipjc57ebkzslnfcc:..."
       }
   }
   ```
3. **Send Group Message (HTTP):**
   - **URL:** `http://127.0.0.1:8000/api/common/chat/groups/5/messages`
   - **Body:**
     ```json
     {
         "body": "Hello group members! Live update test."
     }
     ```
4. **Result:** The incoming frame appears in the WebSocket tab under channel `private-group.5`.

---

## 💻 How Frontend Connects (Give This To Your Frontend Team)

Your frontend team (Web / iOS / Android) does **not** need to do manual Pusher protocol handshakes. Standard libraries handle the handshake and authorization automatically.

### 1. Web Frontend (Vue, React, Next.js, Vanilla JS)

Install `laravel-echo` and `pusher-js`:
```bash
npm install --save laravel-echo pusher-js
```

Initialize Echo with the user's Sanctum Bearer token:
```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const token = "1|abc123yourgeneratedsanctumtoken..."; // Token from /api/login

const echo = new Echo({
    broadcaster: 'reverb',
    key: 'lffoipjc57ebkzslnfcc',
    wsHost: window.location.hostname || 'localhost',
    wsPort: 8080,
    wssPort: 8080,
    forceTLS: false,
    enabledTransports: ['ws', 'wss'],
    authEndpoint: 'http://127.0.0.1:8000/api/broadcasting/auth',
    auth: {
        headers: {
            Authorization: `Bearer ${token}`,
            Accept: 'application/json'
        }
    }
});

// Listen to 1-to-1 conversation messages
const conversationId = 1;
echo.private(`conversation.${conversationId}`)
    .listen('.message.sent', (e) => {
        console.log('New message received in real-time:', e);
        // Append e.body, e.sender_name, e.attachments to chat UI
    });

// Listen to Group Chat messages
const groupId = 5;
echo.private(`group.${groupId}`)
    .listen('.message.sent', (e) => {
        console.log('New group message received:', e);
    });
```

---

### 2. Mobile Frontend (iOS Swift / Android Kotlin)

Mobile apps can connect in two ways:

#### Option A: Using Reverb / Pusher Swift SDK
```swift
import PusherSwift

let options = PusherClientOptions(
    authMethod: .endpoint(authEndpoint: "http://127.0.0.1:8000/api/broadcasting/auth"),
    host: .host("127.0.0.1"),
    port: 8080,
    useTLS: false
)

let pusher = Pusher(key: "lffoipjc57ebkzslnfcc", options: options)
pusher.connect()

let channel = pusher.subscribe("private-conversation.1")
channel.bind(eventName: "message.sent") { (event: PusherEvent) in
    if let data = event.data {
        print("Received message: \(data)")
    }
}
```

#### Option B: Using Agora Chat SDK (Cloud Socket - No VPS daemon needed)
1. Mobile calls `GET /api/common/chat/agora-token` with Bearer token.
2. Response returns:
   ```json
   {
       "app_id": "8947f63f53834167a80b85ea56b2f679",
       "user_id": "buyer_1",
       "chat_token": "007eJxTY..."
   }
   ```
3. Mobile initializes Agora Chat SDK:
   ```swift
   AgoraChatClient.shared().login(withUsername: "buyer_1", agoraToken: "007eJxTY...") { error in
       if error == nil {
           print("Agora Chat connected!")
       }
   }
   ```

---

## 📋 Quick Postman Reference Summary

| Step | Postman Tab | Method / Type | Endpoint / URL | Key Data |
| :--- | :--- | :--- | :--- | :--- |
| **1. Login** | HTTP | `POST` | `/api/login` | Email/Code + Password ➡️ Returns Bearer token |
| **2. Connect Socket** | WebSocket | `WebSocket` | `ws://127.0.0.1:8080/app/lffoipjc57ebkzslnfcc?protocol=7&client=js&version=8.4.0-reverb&flash=false` | Establishes connection ➡️ Returns `socket_id` |
| **3. Authorize Room** | HTTP | `POST` | `/api/broadcasting/auth` | Bearer Token + `socket_id` + `channel_name` ➡️ Returns `auth` string |
| **4. Subscribe** | WebSocket | `Send JSON` | *WebSocket message box* | Send `pusher:subscribe` with `auth` string |
| **5. Send Message** | HTTP | `POST` | `/api/common/chat/conversations/{id}/messages` | Bearer Token + `{"body": "Hello"}` |
| **6. Receive Message**| WebSocket | *Incoming Frame* | *Messages Pane* | Incoming frame: `{"event": "message.sent", "data": {...}}` |
