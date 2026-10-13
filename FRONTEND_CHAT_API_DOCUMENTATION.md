# 📱 Arihanth Chat System - Master Frontend Documentation

This document is the **complete step-by-step implementation roadmap** for your **Frontend Team (Flutter, iOS, Android, and Web)**.

---

## 🗺️ The Complete 5-Step Execution Order (What APIs to call in order)

```
[Step 1: App Launch]  ➡️  POST /api/login                                 (Get Bearer Token)
         ⬇️
[Step 2: Background]  ➡️  GET /api/common/chat/agora-token                (Connect to Agora Cloud)
         ⬇️
[Step 3: Contacts UI] ➡️  GET /api/common/chat/contacts                   (Display Paginated Contacts)
         ⬇️
[Step 4: Tap Contact] ➡️  POST /api/common/chat/conversations             (Creates/Opens Conversation -> Returns ID)
         ⬇️
[Step 5: Chat Screen] ➡️  A. GET /api/common/chat/conversations/{id}/messages (Load Message History)
                      ➡️  B. POST /api/common/chat/conversations/{id}/messages (Send New Message)
                      ➡️  C. Agora Chat SDK / WebSocket                    (Instant Real-Time Push)
```

---

### 📋 Sequential API Summary Table

| Step | User Action | HTTP Method | Endpoint | Purpose |
| :---: | :--- | :---: | :--- | :--- |
| **1** | User Enters Credentials | `POST` | `/api/login` | Log in and get Bearer token |
| **2** | App Background Setup | `GET` | `/api/common/chat/agora-token` | Get Agora User Token & App ID |
| **3** | User opens "Chat Inbox" | `GET` | `/api/common/chat/recent-chats` | Get login-wise paginated recent chats, unread badges & latest messages |
| **4** | User opens "New Chat / Contacts" | `GET` | `/api/common/chat/contacts` | Display contacts list with search & filter tabs |
| **5** | User taps on a contact card | `POST` | `/api/common/chat/conversations` | Open conversation room & get `conversation_id` |
| **6A**| Chat screen opens | `GET` | `/api/common/chat/conversations/{id}/messages`| Load previous chat history |
| **6B**| User taps "Send" | `POST` | `/api/common/chat/conversations/{id}/messages`| Save and broadcast new message |

---

## 🌐 1. Base URL & Authentication

- **Production Base URL:** `https://arihanthjewellers.in/api`
- **Local Base URL:** `http://127.0.0.1:8000/api`

### Common Request Headers:
```http
Authorization: Bearer <SANCTUM_TOKEN>
Accept: application/json
Content-Type: application/json
```
*(When uploading attachments like images/videos, use `multipart/form-data` instead of `application/json`)*

---

## 🔑 2. Authentication API (Universal Login)

Use this single endpoint to log in any role (**Super Admin, Admin, Buyer, Craftsman, Key User, Craftsman Staff**).

### `POST /api/login`
- **Body (JSON):**
  ```json
  {
      "email": "SU0001",
      "password": "your_password",
      "device_type": "ios",
      "fcm_token": "optional_fcm_token_here",
      "voip_token": "optional_ios_voip_token_here"
  }
  ```
  *(Note: `"email"` accepts user code `SU0001`, mobile number, or email address)*
- **Response (200 OK):**
  ```json
  {
      "success": true,
      "message": "Login successful",
      "role": "super_admin",
      "token": "1|abc123yourgeneratedsanctumtoken...",
      "user": {
          "id": 1,
          "full_name": "Super Admin",
          "user_code": "SU0001"
      }
  }
  ```
  > 💡 Save the `"token"` and pass it in `Authorization: Bearer <token>` for all requests and WebSocket auth below.

---

## ⚡ 3. Real-Time WebSockets Architecture (Live Message Delivery)

### How It Works:
- **SENDING a Message:** The sender sends via REST API `POST /api/common/chat/conversations/{id}/messages` (saves to MySQL & handles attachments).
- **RECEIVING a Message:** The receiver connects via **WebSocket** and listens to their private room. When the server gets the message, it pushes it through the WebSocket to the receiver **in 0.01 seconds without refreshing or polling**.

```
[Sender (User A)]                         [Laravel Backend]                       [Receiver (User B)]
        |                                         |                                        |
        |--- 1. POST /messages (HTTP) ----------->|                                        |
        |    {"body": "Hello Arun!"}              |-- Saves to Database                    |
        |<-- 2. 201 Created (HTTP Response) ------|-- Dispatches Broadcast Event --------->|
        |                                         |                                        | 3. Live message pops up
        |                                         |                                        |    Event: "message.sent"
```

---

### 3.1 WebSocket Connection Parameters

| Parameter | Local Development | Production (Hostinger / Live) |
| :--- | :--- | :--- |
| **Broadcaster** | `reverb` | `reverb` (or `pusher`) |
| **Host** | `127.0.0.1` | `arihanthjewellers.in` |
| **Port** | `8080` | `443` |
| **Scheme / TLS** | `ws` (non-TLS) | `wss` (forceTLS: true) |
| **App Key** | `lffoipjc57ebkzslnfcc` | `lffoipjc57ebkzslnfcc` |
| **Auth Endpoint** | `http://127.0.0.1:8000/api/broadcasting/auth` | `https://arihanthjewellers.in/api/broadcasting/auth` |

---

### 3.2 Private Channel Names

The frontend must subscribe to the channel corresponding to the active room:

| Type | Channel Name Format | Example | Who is Allowed to Join |
| :--- | :--- | :--- | :--- |
| **1-to-1 Chat** | `private-conversation.{id}` | `private-conversation.1` | Sender, Receiver & Super Admin |
| **Group Chat** | `private-group.{id}` | `private-group.5` | Group Participants & Super Admin |
| **Personal Alerts** | `private-user.{role}.{user_id}` | `private-user.buyer.1` | Only that specific user |

---

### 3.3 Live Incoming Event Data Specification

When a message is received in real-time, the WebSocket emits the following event:

- **Event Name:** `message.sent` (or `.message.sent` in Laravel Echo)
- **Payload Data (JSON):**
  ```json
  {
      "event": "message.sent",
      "channel": "private-conversation.1",
      "data": {
          "id": 209,
          "conversation_id": 1,
          "chat_group_id": null,
          "sender_id": 1,
          "sender_type": "App\\Models\\ProcessOwner",
          "sender_name": "Super Admin",
          "sender_code": "SU0001",
          "sender_avatar": null,
          "body": "Hello Arun! Testing live delivery.",
          "attachments": [
              {
                  "id": 42,
                  "file_name": "design.png",
                  "file_type": "image",
                  "mime_type": "image/png",
                  "full_url": "https://arihanthjewellers.in/storage/chat_attachments/1/design.png"
              }
          ],
          "created_at": "2026-10-13T13:15:00.000000Z"
      }
  }
  ```

- **Delete Event:** `message.deleted` (broadcasted when a message is deleted, sending `{"message_id": 209}`).

---

### 3.4 Frontend Code Implementation

#### A. Web Frontend (React / Vue / Next.js) using Laravel Echo
```bash
npm install --save laravel-echo pusher-js
```
```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const token = "1|abc123yourtoken..."; // Bearer token from /api/login

const echo = new Echo({
    broadcaster: 'reverb',
    key: 'lffoipjc57ebkzslnfcc',
    wsHost: 'arihanthjewellers.in',
    wsPort: 443,
    wssPort: 443,
    forceTLS: true,
    enabledTransports: ['ws', 'wss'],
    authEndpoint: 'https://arihanthjewellers.in/api/broadcasting/auth',
    auth: {
        headers: {
            Authorization: `Bearer ${token}`,
            Accept: 'application/json'
        }
    }
});

// 1. Listen to 1-to-1 Direct Conversation
const conversationId = 1;
echo.private(`conversation.${conversationId}`)
    .listen('.message.sent', (message) => {
        console.log("New 1-to-1 message arrived:", message);
        // Append to chat window UI
    });

// 2. Listen to Group Chat
const groupId = 5;
echo.private(`group.${groupId}`)
    .listen('.message.sent', (message) => {
        console.log("New group message arrived:", message);
        // Append to group chat UI
    });
```

#### B. iOS Native (Swift) using PusherSwift
```swift
import PusherSwift

let options = PusherClientOptions(
    authMethod: .endpoint(authEndpoint: "https://arihanthjewellers.in/api/broadcasting/auth"),
    host: .host("arihanthjewellers.in"),
    port: 443,
    useTLS: true
)

// Add Authorization Bearer Header
let authHeaders = ["Authorization": "Bearer \(userSanctumToken)"]
options.authMethod = .authRequestBuilder(authRequestBuilder: AuthRequestBuilder(headers: authHeaders))

let pusher = Pusher(key: "lffoipjc57ebkzslnfcc", options: options)
pusher.connect()

// Subscribe to conversation
let channel = pusher.subscribe("private-conversation.1")
channel.bind(eventName: "message.sent") { (event: PusherEvent) in
    if let data = event.data {
        print("Incoming live message: \(data)")
    }
}
```

---

## 🎙️ 4. Agora Chat & Real-Time Token API (Alternative Cloud Socket)

Generates instant Agora tokens for **Chat (V2), RTM, and RTC** for voice, video, or cloud chat.

### `GET /api/common/chat/agora-token`
*(Can also be called with `POST` with optional `{"channel_name": "room1"}`)*
- **Headers:** `Authorization: Bearer <token>`
- **Response (200 OK):**
  ```json
  {
      "success": true,
      "data": {
          "app_id": "77479c7b51d14a55bdf6603ca4135096",
          "user_id": "buyer_1",
          "chat_user_token": "007eJxTYCj0S...",
          "rtm_token": "007eJxTYCj0S...",
          "rtc_token": "007eJxTYCj0S...",
          "channel_name": "general",
          "expire_timestamp": 1728840000,
          "token_builder": "v2"
      }
  }
  ```
  > 💡 Mobile apps pass `user_id` and `chat_user_token` to `AgoraChatClient.shared().login(withUsername: user_id, agoraToken: chat_user_token)`.

---

## 👥 5. Eligible Contacts List API

Returns contacts the user is permitted to chat with or add to groups according to company rules:
- **Buyer** sees: Their own Key Users (`bp_code` matching) + Craftsmen (with chat permissions enabled) + Admins.
- **Craftsman** sees: Their own Staff (`craftsman_id` matching) + Buyers (with chat permissions enabled) + Key Users + Admins.
- **Key User** sees: Their parent Buyer + colleagues under same BP code + Craftsmen (with chat permissions enabled) + Admins.
- **Craftsman Staff** sees: Their parent Craftsman + colleagues + Buyers (with chat permissions enabled) + Admins.
- **Admin / Super Admin** sees: All users across the system.

> 🔒 **Permission Enforcement:**
> Chat access requires the `'chat'` (or `'messages'`) permission assigned by Super Admin in the dashboard.
> - Accounts without chat permissions receive HTTP `403 Forbidden`: `{"success": false, "message": "You do not have chat permissions enabled. Please contact Super Admin."}`.
> - 1-to-1 conversation start (`POST /api/common/chat/conversations`) validates that both the initiating user and target recipient have chat permissions enabled.

### `GET /api/common/chat/contacts`
Returns paginated contacts with category counts so frontend apps can display role filter tabs (e.g., `[All] [Buyers] [Craftsmen] [Key Users] [Staff] [Admins]`).

- **Query Parameters:**
  | Parameter | Type | Default | Description |
  | :--- | :--- | :--- | :--- |
  | `page` | integer | `1` | Page number for pagination |
  | `per_page` | integer | `20` | Contacts per page (max: 100) |
  | `type` | string | `all` | Filter by category: `all`, `buyers`, `craftsmen`, `key_users`, `craftsman_staff`, `admins` |
  | `search` | string | `""` | Search by user name, code, bp_code, email, or mobile |
  | `grouped` | boolean | `false` | Set to `true` to return categorized dictionary format |

- **Response (200 OK):**
  ```json
  {
      "success": true,
      "data": [
          {
              "id": 10,
              "type": "key_user",
              "model_type": "App\\Models\\KeyUser",
              "code": "KU0001",
              "name": "Arun Kumar",
              "label": "KU0001 - Arun Kumar",
              "avatar_url": "https://arihanthjewellers.in/storage/profiles/arun.jpg",
              "email": "arun@example.com",
              "mobile": "9876543210",
              "badge": "Key User"
          },
          {
              "id": 1,
              "type": "buyer",
              "model_type": "App\\Models\\Buyer",
              "code": "BP0001",
              "name": "Rajesh Jewellers",
              "label": "BP0001 - Rajesh Jewellers",
              "avatar_url": "https://arihanthjewellers.in/storage/buyers/rajesh.jpg",
              "email": "buyer@arihanth.com",
              "mobile": "9876543211",
              "badge": "Buyer"
          }
      ],
      "pagination": {
          "current_page": 1,
          "per_page": 20,
          "total": 95,
          "last_page": 5,
          "from": 1,
          "to": 20,
          "has_more": true
      },
      "filter_counts": {
          "all": 95,
          "buyers": 30,
          "craftsmen": 25,
          "key_users": 20,
          "craftsman_staff": 15,
          "admins": 5
      },
      "active_filter": "all"
  }
  ```
  > 💡 **Frontend Tip:** Use `filter_counts` to render tab badges in the UI:  
  > `[ All (95) ]  [ Buyers (30) ]  [ Craftsmen (25) ]  [ Key Users (20) ]  [ Staff (15) ]  [ Admins (5) ]`  
  > When clicking a tab, call: `GET /api/common/chat/contacts?type=buyers&page=1`!

---

## 💬 6. Direct 1-to-1 Conversations (REST)

### 6.1 Unified Login-Wise Recent Chats API (with Pagination)
#### `GET /api/common/chat/recent-chats` *(also supports aliases `/api/common/chat/conversations` and `/api/common/chat/recent-messages`)*

This is the **single unified endpoint** for your Chat Inbox screen. It returns:
1. **Paginated recent chats** with partner info, latest message, and unread counters.
2. **Global unread message counter** (`total_unread_count`) for tab/icon badges.
3. **Current logged-in user summary** with their `agora_user_id`.
4. **Recent activity stream** (`recent_messages` top 10 items) for instant live preview.

- **Query Parameters:**
  | Parameter | Type | Default | Description |
  | :--- | :--- | :--- | :--- |
  | `page` | integer | `1` | Page number for infinite scroll / pagination |
  | `per_page` | integer | `15` | Number of chat threads per page (max: 100) |
  | `search` | string | `""` | Search by contact name, code, email, mobile, or last message |
  | `type` | string | `all` | Filter by contact role: `all`, `buyers`, `craftsmen`, `key_users`, `craftsman_staff`, `admins` |
  | `unread_only` | boolean | `false` | Set `true` to only show conversations with unread messages |

- **Response (200 OK):**
  ```json
  {
      "success": true,
      "total_unread_count": 9,
      "data": [
          {
              "id": 7,
              "conversation_id": 7,
              "partner": {
                  "id": 1,
                  "type": "superadmin",
                  "model_type": "App\\Models\\ProcessOwner",
                  "agora_user_id": "superadmin_1",
                  "code": "SA001",
                  "name": "Super Admin",
                  "label": "SA001 - Super Admin",
                  "avatar_url": null,
                  "email": "udayakirane2002@gmail.com",
                  "mobile": "9361590913",
                  "badge": "Super Admin"
              },
              "recent_message": {
                  "id": 205,
                  "conversation_id": 7,
                  "sender": {
                      "id": 1,
                      "type": "superadmin",
                      "name": "Super Admin",
                      "badge": "Super Admin"
                  },
                  "body": "hellow",
                  "attachments": [],
                  "is_read": false,
                  "created_at": "2026-10-13T07:32:03.000000Z"
              },
              "unread_count": 9,
              "last_message_at": "2026-10-13T07:32:03.000000Z",
              "created_at": "2026-09-07T10:09:39.000000Z"
          },
          {
              "id": 6,
              "conversation_id": 6,
              "partner": {
                  "id": 2,
                  "type": "admin",
                  "model_type": "App\\Models\\ProcessOwner",
                  "agora_user_id": "admin_2",
                  "code": "AD0001",
                  "name": "Admin",
                  "label": "AD0001 - Admin",
                  "avatar_url": null,
                  "email": "admin@gmail.com",
                  "mobile": "7888778878",
                  "badge": "Admin"
              },
              "recent_message": {
                  "id": 191,
                  "conversation_id": 6,
                  "sender": {
                      "id": 1,
                      "type": "buyer",
                      "name": "Babu",
                      "badge": "Buyer"
                  },
                  "body": "hello",
                  "attachments": [],
                  "is_read": true,
                  "created_at": "2026-09-07T12:45:00.000000Z"
              },
              "unread_count": 0,
              "last_message_at": "2026-09-04T09:27:35.000000Z",
              "created_at": "2026-09-04T09:27:35.000000Z"
          }
      ],
      "pagination": {
          "current_page": 1,
          "per_page": 15,
          "total": 2,
          "last_page": 1,
          "from": 1,
          "to": 2,
          "has_more": false
      }
  }
  ```


### 6.2 Start or Open a Conversation (Selected from Contacts List)
#### `POST /api/common/chat/conversations`
When the user picks a person from `/api/common/chat/contacts` (e.g. Arun Kumar with `"id": 10`, `"type": "key_user"`), call this endpoint to start or retrieve their 1-to-1 conversation room.

- **Request Headers:**
  - `Authorization: Bearer <TOKEN>`
  - `Content-Type: application/json`
  - `Accept: application/json`
- **Body (JSON):**
  ```json
  {
      "recipient_id": 10,
      "recipient_type": "key_user",
      "body": "Hi Arun, starting our chat!"
  }
  ```
  *(Note: Accepts `"recipient_id"` or `"target_id"`, and `"recipient_type"` or `"target_type"`. The `"body"` field is optional)*  
  *(Allowed `recipient_type`: `buyer`, `craftsman`, `key_user`, `craftsman_staff`, `admin`, `super_admin`)*

- **Response (200 OK / 201 Created):**
  ```json
  {
      "success": true,
      "message": "Conversation initialized successfully.",
      "data": {
          "id": 1,
          "partner": {
              "id": 10,
              "type": "key_user",
              "model_type": "App\\Models\\KeyUser",
              "code": "KU0001",
              "name": "Arun Kumar",
              "label": "KU0001 - Arun Kumar",
              "avatar_url": "https://arihanthjewellers.in/storage/profiles/arun.jpg",
              "email": "arun@example.com",
              "mobile": "9876543210",
              "badge": "Key User"
          },
          "last_message_at": "2026-10-13T14:45:00.000000Z",
          "initial_message": null
      }
  }
  ```
  > 💡 **Next Step for Frontend:**  
  > 1. Use `data.id` (`1`) to navigate the user to the chat screen.  
  > 2. Fetch message history: `GET /api/common/chat/conversations/1/messages`.  
  > 3. Connect to WebSocket room: `private-conversation.1` to listen for live incoming messages!

### 6.3 Get Message History
#### `GET /api/common/chat/conversations/{id}/messages`
- **Query Parameters:** `?page=1&per_page=30`

### 6.4 Send Message (Triggers Real-Time WebSocket)
#### `POST /api/common/chat/conversations/{id}/messages`
- **Option A: Text Only (raw JSON):**
  ```json
  {
      "body": "Hello Arun! Please check the design quotation."
  }
  ```
- **Option B: Attachments / Multimedia (`multipart/form-data`):**
  - `body`: `"Here are the design photos"`
  - `attachments[]`: Select file 1 (image/png)
  - `attachments[]`: Select file 2 (application/pdf)
- **Response (201 Created):**
  ```json
  {
      "success": true,
      "message": "Message sent successfully.",
      "data": {
          "id": 209,
          "conversation_id": 1,
          "body": "Here are the design photos",
          "attachments": [
              {
                  "id": 42,
                  "file_name": "design.png",
                  "file_type": "image",
                  "full_url": "https://arihanthjewellers.in/storage/chat_attachments/1/design.png"
              }
          ]
      }
  }
  ```

### 6.5 Delete Conversation
#### `DELETE /api/common/chat/conversations/{id}`

---

## 👨‍👩‍👧‍👦 7. Group Chat System

### 7.1 Get My Groups
#### `GET /api/common/chat/groups`

### 7.2 Create Group (Role Restrictions Enforced)
#### `POST /api/common/chat/groups`
- **Body (JSON):**
  ```json
  {
      "name": "Design Review Team",
      "description": "Group for Work Order discussions",
      "participants": [
          { "id": 10, "type": "key_user" },
          { "id": 1, "type": "admin" }
      ]
  }
  ```

### 7.3 Get Group Details & Participants
#### `GET /api/common/chat/groups/{id}`

### 7.4 Add Participants
#### `POST /api/common/chat/groups/{id}/participants`
- **Body (JSON):** `{"participants": [{"id": 12, "type": "key_user"}]}`

### 7.5 Remove Participant
#### `DELETE /api/common/chat/groups/{id}/participants/{participantId}`

### 7.6 Get Group Messages
#### `GET /api/common/chat/groups/{id}/messages`
- **Query Parameters:** `?page=1&per_page=30`

### 7.7 Send Group Message (Triggers WebSocket to `private-group.{id}`)
#### `POST /api/common/chat/groups/{id}/messages`
- **Body:** `{"body": "Meeting starts in 10 minutes."}`

### 7.8 Delete Group
#### `DELETE /api/common/chat/groups/{id}`

---

## 🗑️ 8. Delete Message

### `DELETE /api/common/chat/messages/{id}`
- Users can delete their own messages.
- Super Admin can delete any message.
- Triggers `message.deleted` WebSocket event to remove the message from all participants' screens.

---

## 🤖 9. AI Chatbot Assistant API

Handles automated questions about Work Orders, Purchase Orders, Repairs, or connects users instantly to live video consultations.

### `POST /api/common/chatbot/message`
- **Body (JSON):**
  ```json
  {
      "message": "What is the status of work order WO-1024?"
  }
  ```
- **Response (200 OK):**
  ```json
  {
      "success": true,
      "reply": "Work Order WO-1024 is currently In Progress. Assigned craftsman: Rajesh Craftsman. Expected delivery: 2026-10-20.",
      "quick_replies": [
          "Check PO status",
          "Talk to Support",
          "Start Video Consultation"
      ]
  }
  ```

---

## 👑 10. Super Admin Chat Monitoring APIs

### `GET /api/common/chat/super-admin/overview`
- Returns count of total conversations, groups, messages, and role breakdown.

### `GET /api/common/chat/super-admin/conversations`
- Allows Super Admin to view and manage any conversation across the platform.

---

## 🔔 11. Push Notification Direct Reply Action with Inline Text Input

When a user receives a push notification, they can reply directly from the notification shade (lock screen / notification banner) without opening the app.

### 11.1 FCM & APNs Payload Structure Sent by Server
The server automatically sends the interactive reply action metadata pointing directly to the existing chat message endpoint:
```json
{
  "title": "SA001 - Super Admin",
  "body": "Hello! Please update work order status.",
  "data": {
    "type": "chat_message",
    "message_id": "213",
    "conversation_id": "8",
    "chat_group_id": "",
    "sender_id": "1",
    "sender_type": "App\\Models\\ProcessOwner",
    "sender_name": "Super Admin",
    "sender_code": "SA001",
    "action": "reply",
    "action_type": "inline_reply",
    "allow_input": "true",
    "input_placeholder": "Type a reply...",
    "category": "CHAT_MESSAGE_REPLY",
    "reply_url": "https://arihanthjewellers.in/api/common/chat/conversations/8/messages",
    "reply_method": "POST"
  }
}
```

### 11.2 Existing Endpoint Used for Reply (Zero New APIs)
The reply action button posts directly to your **existing send message API**:

- **1-on-1 Chat Reply:** `POST /api/common/chat/conversations/{id}/messages`
- **Group Chat Reply:** `POST /api/common/chat/groups/{id}/messages`

- **Headers:** `Authorization: Bearer <TOKEN>`, `Content-Type: application/json`
- **Body (JSON):**
  ```json
  {
      "body": "Work order is completed."
  }
  ```
  *(Also accepts `"reply"`, `"input"`, or `"text"` for convenience)*

- **Response (`201 Created`):** Returns the standard created message object and broadcasts to WebSocket / Agora.

### 11.3 Flutter Notification Setup (Android RemoteInput & iOS UNTextInputNotificationAction)

In Flutter (`flutter_local_notifications`):
```dart
// 1. Android Notification Action with Remote Input:
final androidNotificationDetails = AndroidNotificationDetails(
  'chat_messages',
  'Chat Messages',
  importance: Importance.max,
  priority: Priority.high,
  actions: <AndroidNotificationAction>[
    AndroidNotificationAction(
      'REPLY_ACTION',
      'Reply',
      inputs: <AndroidNotificationActionInput>[
        AndroidNotificationActionInput(
          label: 'Type a reply...',
        ),
      ],
    ),
  ],
);

// 2. iOS Category with Text Input Action:
final darwinNotificationDetails = DarwinNotificationDetails(
  categoryIdentifier: 'CHAT_MESSAGE_REPLY',
);

// 3. Background Notification Action Handler:
@pragma('vm:entry-point')
void notificationActionHandler(NotificationResponse response) async {
  if (response.actionId == 'REPLY_ACTION') {
    final String? userReply = response.input; // Text typed by user in notification
    final String? conversationId = response.payload; // Conversation ID

    if (userReply != null && conversationId != null) {
      await http.post(
        Uri.parse('https://arihanthjewellers.in/api/common/chat/conversations/$conversationId/reply'),
        headers: {
          'Authorization': 'Bearer $userSanctumToken',
          'Content-Type': 'application/json',
        },
        body: jsonEncode({'reply': userReply}),
      );
    }
  }
}
```
