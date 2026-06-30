// Firebase Messaging Service Worker
// This file MUST stay at: /public/firebase-messaging-sw.js (domain root)
// It handles background push notifications when the admin tab is not in focus.

importScripts('https://www.gstatic.com/firebasejs/12.15.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/12.15.0/firebase-messaging-compat.js');

firebase.initializeApp({
    apiKey: "AIzaSyD6y5IeHzbDKJJhBoWRaB8q-GsulGVARS4",
    authDomain: "arihanth-1938c.firebaseapp.com",
    databaseURL: "https://arihanth-1938c-default-rtdb.firebaseio.com",
    projectId: "arihanth-1938c",
    storageBucket: "arihanth-1938c.firebasestorage.app",
    messagingSenderId: "601146486892",
    appId: "1:601146486892:web:47c5b8e9a8d84b491aa849"
});

const messaging = firebase.messaging();

// Handle background messages (tab not focused / browser minimized)
messaging.onBackgroundMessage(function(payload) {
    console.log('[FCM SW] Background message received:', payload);

    const title = payload.notification?.title || 'Arihanth Jewellers';
    const options = {
        body: payload.notification?.body || '',
        icon: '/images/taralogo.png',
        badge: '/images/taralogo.png',
        vibrate: [200, 100, 200],
        data: payload.data || {}
    };

    self.registration.showNotification(title, options);
});

// When admin clicks a notification, navigate to the meeting room
self.addEventListener('notificationclick', function(event) {
    event.notification.close();

    const data = event.notification.data;
    let url = '/admin/meetings';

    if (data && data.room_id) {
        url = '/video-call/' + data.room_id;
    }

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function(clientList) {
            // Focus existing tab if already open
            for (const client of clientList) {
                if (client.url.includes(url) && 'focus' in client) {
                    return client.focus();
                }
            }
            // Otherwise open a new tab
            if (clients.openWindow) {
                return clients.openWindow(url);
            }
        })
    );
});
