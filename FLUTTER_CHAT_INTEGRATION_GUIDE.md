# 🎙️ Flutter Agora Chat & Video Integration Guide (100% Agora Cloud)

This guide provides the **exact implementation for your Flutter Team** to run **100% Agora Real-Time Chat, Audio, and Video** with **Zero Server Daemons and NO Pusher / Reverb required**.

---

## 🗺️ The Complete 5-Step Execution Order (What to call in order)

```
[Step 1: App Launch]  ➡️  POST /api/login                                 (Get Bearer Token)
         ⬇️
[Step 2: Background]  ➡️  GET /api/common/chat/agora-token                (Connect to Agora Cloud SDK)
         ⬇️
[Step 3: Contacts UI] ➡️  GET /api/common/chat/contacts                   (Display Paginated Contacts)
         ⬇️
[Step 4: Tap Contact] ➡️  POST /api/common/chat/conversations             (Creates/Opens Room -> Returns ID)
         ⬇️
[Step 5: Chat Screen] ➡️  A. GET /api/common/chat/conversations/{id}/messages (Load Message History)
                      ➡️  B. POST /api/common/chat/conversations/{id}/messages (Send New Message)
                      ➡️  C. Agora Chat SDK                               (Instant Live Push via Cloud)
```

---

## 📦 1. Required Flutter Dependencies

Add these packages to `pubspec.yaml`:

```yaml
dependencies:
  flutter:
    sdk: flutter
  http: ^1.2.0                  # For Laravel REST API
  agora_chat_sdk: ^1.3.0        # Official Agora Chat SDK for Real-Time Text & Media Messaging
  agora_rtc_engine: ^6.3.0      # Official Agora Engine for Live Audio & Video Calls
  shared_preferences: ^2.2.2    # To store token
  file_picker: ^8.0.0           # For sending attachments/PDFs
```

Run in terminal:
```bash
flutter pub get
```

---

## ⚙️ 2. Production Constants (`constants.dart`)

Create `lib/core/constants.dart`:

```dart
class AppConstants {
  // Your Laravel Production Backend
  static const String baseUrl = "https://arihanthjewellers.in/api";

  // Your Agora App ID from .env
  static const String agoraAppId = "77479c7b51d14a55bdf6603ca4135096";
}
```

---

## 🌐 3. Laravel API Service (`api_service.dart`)

Create `lib/services/api_service.dart`:

```dart
import 'dart:convert';
import 'package:http/http.dart' as http;
import '../core/constants.dart';

class ApiService {
  final String token;

  ApiService({required this.token});

  Map<String, String> get _headers => {
    "Authorization": "Bearer $token",
    "Accept": "application/json",
    "Content-Type": "application/json",
  };

  // 1. Universal Login
  static Future<Map<String, dynamic>> login(String email, String password) async {
    final response = await http.post(
      Uri.parse("${AppConstants.baseUrl}/login"),
      headers: {"Accept": "application/json", "Content-Type": "application/json"},
      body: jsonEncode({"email": email, "password": password}),
    );
    return jsonDecode(response.body);
  }

  // 2. Get Agora User Token & App ID
  Future<Map<String, dynamic>> getAgoraToken({String? channelName}) async {
    final response = await http.post(
      Uri.parse("${AppConstants.baseUrl}/common/chat/agora-token"),
      headers: _headers,
      body: jsonEncode({
        if (channelName != null) "channel_name": channelName,
      }),
    );
    return jsonDecode(response.body);
  }

  // 3. Get Eligible Contacts (with Pagination & Filter Tabs)
  Future<Map<String, dynamic>> getContacts({int page = 1, String type = 'all', String search = ''}) async {
    final uri = Uri.parse("${AppConstants.baseUrl}/common/chat/contacts").replace(
      queryParameters: {
        'page': page.toString(),
        'per_page': '20',
        'type': type,
        if (search.isNotEmpty) 'search': search,
      },
    );
    final response = await http.get(uri, headers: _headers);
    return jsonDecode(response.body);
  }

  // 3.5. Get Login-Wise Paginated Recent Chats (Chat Inbox Screen)
  Future<Map<String, dynamic>> getRecentChats({int page = 1, int perPage = 15, String search = '', String type = 'all', bool unreadOnly = false}) async {
    final uri = Uri.parse("${AppConstants.baseUrl}/common/chat/recent-chats").replace(
      queryParameters: {
        'page': page.toString(),
        'per_page': perPage.toString(),
        'type': type,
        if (unreadOnly) 'unread_only': 'true',
        if (search.isNotEmpty) 'search': search,
      },
    );
    final response = await http.get(uri, headers: _headers);
    return jsonDecode(response.body);
  }

  // 4. Start or Open 1-to-1 Conversation
  Future<Map<String, dynamic>> startConversation(int recipientId, String recipientType) async {
    final response = await http.post(
      Uri.parse("${AppConstants.baseUrl}/common/chat/conversations"),
      headers: _headers,
      body: jsonEncode({"recipient_id": recipientId, "recipient_type": recipientType}),
    );
    return jsonDecode(response.body);
  }

  // 5. Fetch Previous Messages History from Database
  Future<Map<String, dynamic>> getMessageHistory(int conversationId, {int page = 1}) async {
    final response = await http.get(
      Uri.parse("${AppConstants.baseUrl}/common/chat/conversations/$conversationId/messages?page=$page"),
      headers: _headers,
    );
    return jsonDecode(response.body);
  }

  // 6. Save Sent Message to Database
  Future<Map<String, dynamic>> saveMessageToDb(int conversationId, String text) async {
    final response = await http.post(
      Uri.parse("${AppConstants.baseUrl}/common/chat/conversations/$conversationId/messages"),
      headers: _headers,
      body: jsonEncode({"body": text}),
    );
    return jsonDecode(response.body);
  }
}
```

---

## 🎙️ 4. Agora Chat Manager (`agora_chat_service.dart`)

Create `lib/services/agora_chat_service.dart`:

```dart
import 'package:agora_chat_sdk/agora_chat_sdk.dart';
import '../core/constants.dart';

class AgoraChatService {
  static final AgoraChatService _instance = AgoraChatService._internal();
  factory AgoraChatService() => _instance;
  AgoraChatService._internal();

  bool _isInitialized = false;

  // Initialize Agora Chat SDK
  Future<void> initAgoraChat() async {
    if (_isInitialized) return;

    AgoraChatOptions options = AgoraChatOptions(
      appKey: "${AppConstants.agoraAppId}#chat",
      autoLogin: false,
      debugModel: true,
    );

    await AgoraChatClient.getInstance.init(options);
    _isInitialized = true;
    print("🟢 Agora Chat SDK Initialized!");
  }

  // Login using token received from /api/common/chat/agora-token
  Future<bool> loginToAgora(String agoraUserId, String agoraToken) async {
    try {
      await AgoraChatClient.getInstance.loginWithAgoraToken(
        agoraUserId,
        agoraToken,
      );
      print("🟢 Logged into Agora Chat as: $agoraUserId");
      return true;
    } on AgoraChatError catch (e) {
      print("🔴 Agora Login Error: ${e.code} - ${e.description}");
      return false;
    }
  }

  // Register Global Real-Time Incoming Message Listener
  void registerMessageListener(Function(AgoraChatMessage message) onMessageReceived) {
    AgoraChatClient.getInstance.chatManager.addEventHandler(
      "GLOBAL_CHAT_HANDLER",
      AgoraChatEventHandler(
        onMessagesReceived: (List<AgoraChatMessage> messages) {
          for (var message in messages) {
            print("📩 New live message from Agora: ${message.body}");
            onMessageReceived(message);
          }
        },
      ),
    );
  }

  // Send a Text Message directly through Agora Cloud
  Future<AgoraChatMessage> sendTextMessage({
    required String recipientAgoraId, // e.g. "key_user_10", "buyer_1"
    required String content,
  }) async {
    final message = AgoraChatMessage.createTextSendMessage(
      targetId: recipientAgoraId,
      content: content,
      chatType: AgoraChatType.Chat, // 1-to-1 Chat
    );

    await AgoraChatClient.getInstance.chatManager.sendMessage(message);
    print("📤 Message sent live through Agora Cloud to: $recipientAgoraId");
    return message;
  }

  // Logout from Agora on App Logout
  Future<void> logout() async {
    await AgoraChatClient.getInstance.logout(true);
  }
}
```

---

## 💬 5. Complete Flutter Chat Screen (`chat_screen.dart`)

Create `lib/screens/chat_screen.dart`:

```dart
import 'package:flutter/material.dart';
import 'package:agora_chat_sdk/agora_chat_sdk.dart';
import '../services/api_service.dart';
import '../services/agora_chat_service.dart';

class ChatScreen extends StatefulWidget {
  final int conversationId;
  final String partnerName;
  final String partnerAgoraId; // e.g. "key_user_10"
  final String userToken;

  const ChatScreen({
    Key? key,
    required this.conversationId,
    required this.partnerName,
    required this.partnerAgoraId,
    required this.userToken,
  }) : super(key: key);

  @override
  State<ChatScreen> createState() => _ChatScreenState();
}

class _ChatScreenState extends State<ChatScreen> {
  late ApiService _apiService;
  final AgoraChatService _agoraService = AgoraChatService();
  final TextEditingController _textController = TextEditingController();
  final List<Map<String, dynamic>> _messages = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _apiService = ApiService(token: widget.userToken);

    // 1. Fetch previous messages from database
    _loadHistory();

    // 2. Listen for live incoming messages from Agora Cloud
    _listenForLiveAgoraMessages();
  }

  void _loadHistory() async {
    final res = await _apiService.getMessageHistory(widget.conversationId);
    if (res['success'] == true) {
      setState(() {
        _messages.addAll(List<Map<String, dynamic>>.from(res['data']));
        _isLoading = false;
      });
    }
  }

  void _listenForLiveAgoraMessages() {
    _agoraService.registerMessageListener((agoraMessage) {
      // Check if message is from the person we are chatting with
      if (agoraMessage.from == widget.partnerAgoraId) {
        String body = "";
        if (agoraMessage.body is AgoraChatTextMessageBody) {
          body = (agoraMessage.body as AgoraChatTextMessageBody).content;
        }

        setState(() {
          _messages.insert(0, {
            'is_mine': false,
            'body': body,
            'created_at': DateTime.now().toIso8601String(),
          });
        });
      }
    });
  }

  void _sendMessage() async {
    final text = _textController.text.trim();
    if (text.isEmpty) return;
    _textController.clear();

    // 1. Display immediately in UI
    setState(() {
      _messages.insert(0, {
        'is_mine': true,
        'body': text,
        'created_at': DateTime.now().toIso8601String(),
      });
    });

    // 2. Send LIVE instantly via Agora Cloud (0.01 seconds)
    await _agoraService.sendTextMessage(
      recipientAgoraId: widget.partnerAgoraId,
      content: text,
    );

    // 3. Save to Laravel Database
    await _apiService.saveMessageToDb(widget.conversationId, text);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(widget.partnerName),
        backgroundColor: Colors.amber[800],
      ),
      body: Column(
        children: [
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator())
                : ListView.builder(
                    reverse: true, // Newest at bottom
                    itemCount: _messages.length,
                    itemBuilder: (context, index) {
                      final msg = _messages[index];
                      final isMine = msg['is_mine'] == true;
                      return Align(
                        alignment: isMine ? Alignment.centerRight : Alignment.centerLeft,
                        child: Container(
                          margin: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            color: isMine ? Colors.amber[700] : Colors.grey[200],
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: Text(
                            msg['body'] ?? '',
                            style: TextStyle(
                              color: isMine ? Colors.white : Colors.black87,
                            ),
                          ),
                        ),
                      );
                    },
                  ),
          ),
          Container(
            padding: const EdgeInsets.all(8),
            color: Colors.white,
            child: Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _textController,
                    decoration: const InputDecoration(
                      hintText: "Type a message...",
                      border: InputBorder.none,
                    ),
                  ),
                ),
                IconButton(
                  icon: const Icon(Icons.send, color: Colors.amber),
                  onPressed: _sendMessage,
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
```

---

## 🚀 6. Complete App Startup Flow in Flutter

Tell your Flutter developer to follow this startup sequence:

```
1. User Logs in:
   final loginRes = await ApiService.login(email, password);
   final token = loginRes['token'];

2. Fetch Agora Token from Laravel:
   final agoraRes = await apiService.getAgoraToken();
   final agoraUserId = agoraRes['data']['agora_user_id']; // e.g. "buyer_1"
   final chatToken = agoraRes['data']['chat_user_token'];

3. Login to Agora Cloud:
   await AgoraChatService().initAgoraChat();
   await AgoraChatService().loginToAgora(agoraUserId, chatToken);

4. User browses Contacts:
   GET /common/chat/contacts 
   -> Contact card includes: id: 10, type: "key_user", agora_user_id: "key_user_10"

5. User Taps Contact:
   POST /common/chat/conversations 
   -> Returns conversation id: 1

6. Open ChatScreen:
   Pass conversationId (1) and partnerAgoraId ("key_user_10")
   -> Chat is 100% LIVE on Agora Cloud! Zero server daemons needed!
```

---

## 🔔 7. Push Notification Direct Quick-Reply (Android RemoteInput & iOS Interactive Notification)

Users can reply directly from the push notification banner or lock screen without opening the full Flutter app.

### 7.1 Payload Generated by Backend FCM / APNs
When a new message arrives, the backend sends this enriched data payload:
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

### 7.2 Existing Endpoint Used for Reply (Zero New APIs)
The inline reply action button submits directly to your **existing send message API**:
- **1-on-1 Conversation Reply:** `POST /api/common/chat/conversations/{id}/messages`
- **Group Chat Reply:** `POST /api/common/chat/groups/{id}/messages`

**Body (JSON):**
```json
{
  "body": "I am working on it now!"
}
```
*(Also accepts `"reply"` or `"input"`)*

### 7.3 Flutter Implementation (`flutter_local_notifications`)

```dart
import 'dart:convert';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

// 1. Setup notification details with RemoteInput (Inline Text Field)
const AndroidNotificationDetails androidDetails = AndroidNotificationDetails(
  'chat_messages',
  'Chat Messages',
  channelDescription: 'Direct chat and message alerts',
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
      allowGeneratedReplies: true,
    ),
  ],
);

const DarwinNotificationDetails iosDetails = DarwinNotificationDetails(
  categoryIdentifier: 'CHAT_MESSAGE_REPLY',
);

const NotificationDetails notificationDetails = NotificationDetails(
  android: androidDetails,
  iOS: iosDetails,
);

// 2. Background Entrypoint for Notification Action Taps & Replies
@pragma('vm:entry-point')
void onNotificationActionTapped(NotificationResponse response) async {
  if (response.actionId == 'REPLY_ACTION') {
    final String? typedReply = response.input; // Text entered by user in notification shade
    final String? conversationId = response.payload; // Conversation ID passed in payload

    if (typedReply != null && typedReply.trim().isNotEmpty && conversationId != null) {
      final prefs = await SharedPreferences.getInstance();
      final String? token = prefs.getString('auth_token');

      if (token != null) {
        // Calls the EXISTING message API (No new API required)
        final res = await http.post(
          Uri.parse('https://arihanthjewellers.in/api/common/chat/conversations/$conversationId/messages'),
          headers: {
            'Authorization': 'Bearer $token',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
          },
          body: jsonEncode({
            'body': typedReply.trim(),
          }),
        );

        if (res.statusCode == 201) {
          print('Inline reply sent successfully via existing API!');
        }
      }
    }
  }
}
```

