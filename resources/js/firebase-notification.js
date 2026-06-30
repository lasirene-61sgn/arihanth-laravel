import { initializeApp } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-app.js";
import { getMessaging, getToken, onMessage } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-messaging.js";

// Your web app's Firebase configuration
// TODO: Replace with actual config from user
const firebaseConfig = {
  apiKey: "YOUR_API_KEY",
  authDomain: "YOUR_PROJECT_ID.firebaseapp.com",
  projectId: "YOUR_PROJECT_ID",
  storageBucket: "YOUR_PROJECT_ID.appspot.com",
  messagingSenderId: "YOUR_SENDER_ID",
  appId: "YOUR_APP_ID"
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);
const messaging = getMessaging(app);

export function requestPermissionAndSaveToken() {
  console.log('Requesting permission...');
  Notification.requestPermission().then((permission) => {
    if (permission === 'granted') {
      console.log('Notification permission granted.');

      // Get Token
      getToken(messaging, {
        vapidKey: 'YOUR_VAPID_KEY' // TODO: Replace with actual VAPID key
      }).then((currentToken) => {
        if (currentToken) {
          console.log('FCM Token:', currentToken);
          saveTokenToDatabase(currentToken);
        } else {
          console.log('No registration token available. Request permission to generate one.');
        }
      }).catch((err) => {
        console.log('An error occurred while retrieving token. ', err);
      });

    } else {
      console.log('Unable to get permission to notify.');
    }
  });
}

function saveTokenToDatabase(token) {
  fetch('/craftsman/save-token', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    body: JSON.stringify({ token: token })
  })
    .then(response => response.json())
    .then(data => {
      console.log('Token saved to server:', data);
    })
    .catch((error) => {
      console.error('Error saving token to server:', error);
    });
}

// Handle incoming messages
onMessage(messaging, (payload) => {
  console.log('Message received. ', payload);
  const notificationTitle = payload.notification.title;
  const notificationOptions = {
    body: payload.notification.body,
    icon: '/favicon.ico' // Verify path
  };

  new Notification(notificationTitle, notificationOptions);

  // Optional: Refresh the page or update UI if it's an allocation
  if (window.location.href.includes('work-order')) {
    // You might want to show a toast or refresh the list
    alert(notificationTitle + ": " + notificationOptions.body);
    window.location.reload();
  }
});