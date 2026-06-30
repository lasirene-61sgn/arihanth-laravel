<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Consultation - {{ config('app.name') }}</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Agora SDK -->
    <script src="https://download.agora.io/sdk/release/AgoraRTC_N-4.20.0.js"></script>
    
    <style>
        :root {
            --bg-dark: #121417;
            --bg-card: #1A1D24;
            --primary: #6366F1;
            --danger: #EF4444;
            --success: #10B981;
            --text-light: #F9FAFB;
            --text-gray: #9CA3AF;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { 
            height: 100%; 
            overflow: hidden; 
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-dark);
            color: var(--text-light);
        }

        .meeting-wrapper {
            position: relative;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Top Header */
        .meeting-header {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            padding: 20px 30px;
            background: linear-gradient(to bottom, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0) 100%);
            z-index: 10;
            display: flex;
            justify-content: space-between;
            align-items: center;
            pointer-events: none; /* Let clicks pass through to video */
        }
        
        .meeting-header * { pointer-events: auto; }

        .meeting-title {
            font-size: 1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .meeting-title i { color: var(--primary); }

        .participant-info {
            font-size: 0.85rem;
            color: var(--text-gray);
            background: rgba(0,0,0,0.5);
            padding: 6px 12px;
            border-radius: 20px;
            backdrop-filter: blur(5px);
        }

        /* Video Area */
        .video-grid {
            flex: 1;
            position: relative;
            background-color: #000;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* Remote Video (Full Screen by default if 1-on-1) */
        .remote-player-wrapper {
            width: 100%;
            height: 100%;
            position: relative;
            background-color: var(--bg-dark);
        }

        .remote-player-wrapper :first-child {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Local Video (PiP) */
        .local-player-wrapper {
            position: absolute;
            bottom: 100px;
            right: 30px;
            width: 240px;
            height: 180px;
            background-color: var(--bg-card);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
            border: 1px solid rgba(255,255,255,0.1);
            z-index: 20;
            transition: all 0.3s ease;
        }

        .local-player-wrapper :first-child {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Controls */
        .controls-container {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(26, 29, 36, 0.85);
            backdrop-filter: blur(10px);
            padding: 12px 24px;
            border-radius: 40px;
            display: flex;
            gap: 15px;
            align-items: center;
            border: 1px solid rgba(255,255,255,0.05);
            z-index: 30;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }

        .btn-control {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            border: none;
            background: rgba(255,255,255,0.05);
            color: var(--text-light);
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            font-size: 1.1rem;
            transition: all 0.2s ease;
        }

        .btn-control:hover {
            background: rgba(255,255,255,0.1);
            transform: translateY(-2px);
        }

        .btn-control.active {
            background: var(--primary);
            color: white;
        }

        .btn-control.muted {
            background: var(--danger);
            color: white;
        }

        .btn-control.leave {
            background: var(--danger);
            color: white;
            width: 60px;
            border-radius: 30px;
        }

        .btn-control.leave:hover {
            background: #DC2626;
        }

        /* Name Label */
        .video-label {
            position: absolute;
            bottom: 15px;
            left: 15px;
            background: rgba(0,0,0,0.6);
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 500;
            backdrop-filter: blur(5px);
        }

        /* Loading Overlay */
        .loading-state {
            position: absolute;
            inset: 0;
            background: var(--bg-dark);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 100;
            gap: 15px;
        }

        .loader {
            width: 35px;
            height: 35px;
            border: 3px solid rgba(255,255,255,0.1);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        /* Responsive */
        @media (max-width: 768px) {
            .local-player-wrapper {
                width: 100px;
                height: 140px; /* Portrait for mobile */
                bottom: calc(90px + env(safe-area-inset-bottom, 0px));
                right: 15px;
                border-radius: 12px;
            }
            .controls-container {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                transform: none;
                width: 100%;
                background: #1A1D24; /* Solid dark background */
                border-radius: 24px 24px 0 0; /* Top rounded corners */
                padding: 15px 20px;
                padding-bottom: calc(15px + env(safe-area-inset-bottom, 0px));
                justify-content: space-around;
                border-left: none;
                border-right: none;
                border-bottom: none;
                box-shadow: 0 -10px 20px rgba(0,0,0,0.5);
                display: flex;
            }
            .btn-control {
                width: 45px;
                height: 45px;
                font-size: 1.1rem;
            }
            .btn-control.leave {
                width: 55px;
            }
        }
    </style>
</head>
<body>

<div class="meeting-wrapper">
    <!-- Header -->
    <div class="meeting-header">
        <div class="meeting-title">
            <i class="bi bi-camera-video-fill"></i>
            <span>Live Consultation</span>
        </div>
        <div class="participant-info" id="participant-count">
            1 Person in call
        </div>
    </div>

    <!-- Video Grid -->
    <div class="video-grid" id="meet">
        <!-- Remote video will be placed here and take full screen -->
        <div class="remote-player-wrapper" id="remote-container">
            <!-- Placeholder for when waiting for remote user -->
            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: var(--text-gray);">
                <i class="bi bi-person-fill" style="font-size: 4rem; opacity: 0.3;"></i>
                <p style="margin-top: 15px; font-size: 0.9rem;">Waiting for the other person to join...</p>
            </div>
        </div>

        <!-- Local Video (PiP) -->
        <div class="local-player-wrapper" id="local-container">
            <!-- Local video will be played here -->
        </div>
    </div>

    <!-- Controls -->
    <div class="controls-container">
        <button id="mute-audio" class="btn-control" title="Toggle Microphone">
            <i class="bi bi-mic-fill"></i>
        </button>
        <button id="mute-video" class="btn-control" title="Toggle Camera">
            <i class="bi bi-camera-video-fill"></i>
        </button>
        <button id="share-screen" class="btn-control" title="Share Screen">
            <i class="bi bi-display"></i>
        </button>
        <button id="leave-btn" class="btn-control leave" title="Leave Call">
            <i class="bi bi-telephone-x-fill"></i>
        </button>
    </div>
</div>

<!-- Loading Overlay -->
<div class="loading-state" id="loadingOverlay">
    <div class="loader"></div>
    <p style="color: var(--text-gray); font-size: 0.9rem;">Connecting to secure room...</p>
</div>

<script>
    (function() {
        const client = AgoraRTC.createClient({ mode: "rtc", codec: "vp8" });
        
        const appId = "{{ $appId }}";
        const channel = "{{ $meeting->room_id }}";
        const token = "{{ $agoraToken }}";
        const uid = {{ auth()->id() }};

        let localTracks = {
            videoTrack: null,
            audioTrack: null
        };
        let screenTrack = null;

        async function joinRoom() {
            if (!appId || !token) {
                document.getElementById('loadingOverlay').innerHTML = '<p style="color:#EF4444;">Configuration Error: Missing App ID or Token.</p>';
                return;
            }

            try {
                await client.join(appId, channel, token, uid);
                document.getElementById('loadingOverlay').style.display = 'none';

                // Create local tracks
                const [audioTrack, videoTrack] = await AgoraRTC.createMicrophoneAndCameraTracks()
                    .catch(e => {
                        alert("Camera/Mic permission denied!");
                        throw e;
                    });
                
                localTracks.audioTrack = audioTrack;
                localTracks.videoTrack = videoTrack;

                // Play local video in PiP
                const localContainer = document.getElementById('local-container');
                videoTrack.play(localContainer);
                
                // Add label to local PiP
                const label = document.createElement('div');
                label.className = "video-label";
                const isHost = uid == "{{ $meeting->host_id }}";
                label.innerText = isHost ? "Host (You)" : "Participant (You)";
                localContainer.appendChild(label);
                
                await client.publish([audioTrack, videoTrack]);

                // Controls
                const micBtn = document.getElementById('mute-audio');
                const camBtn = document.getElementById('mute-video');
                const screenBtn = document.getElementById('share-screen');

                micBtn.addEventListener('click', async () => {
                    const isMuted = localTracks.audioTrack.muted;
                    await localTracks.audioTrack.setMuted(!isMuted);
                    micBtn.classList.toggle('muted', !isMuted);
                    micBtn.innerHTML = !isMuted ? '<i class="bi bi-mic-mute-fill"></i>' : '<i class="bi bi-mic-fill"></i>';
                });

                camBtn.addEventListener('click', async () => {
                    const isMuted = localTracks.videoTrack.muted;
                    await localTracks.videoTrack.setMuted(!isMuted);
                    camBtn.classList.toggle('muted', !isMuted);
                    camBtn.innerHTML = !isMuted ? '<i class="bi bi-camera-video-off-fill"></i>' : '<i class="bi bi-camera-video-fill"></i>';
                });

                screenBtn.addEventListener('click', async () => {
                    if (!screenTrack) {
                        try {
                            screenTrack = await AgoraRTC.createScreenVideoTrack();
                            await client.unpublish(localTracks.videoTrack);
                            await client.publish(screenTrack);
                            
                            screenBtn.classList.add('active');
                            
                            // Play screen in the main large container
                            const remoteContainer = document.getElementById('remote-container');
                            remoteContainer.innerHTML = ''; // Clear placeholder or remote video
                            screenTrack.play(remoteContainer);
                            
                            screenTrack.on("track-ended", () => {
                                stopScreenShare();
                            });
                        } catch (e) {
                            console.error("Screen share failed:", e);
                        }
                    } else {
                        stopScreenShare();
                    }
                });

                async function stopScreenShare() {
                    if (screenTrack) {
                        await client.unpublish(screenTrack);
                        screenTrack.close();
                        screenTrack = null;
                    }
                    await client.publish(localTracks.videoTrack);
                    screenBtn.classList.remove('active');
                    
                    // Reset main container to wait for remote or just show black
                    const remoteContainer = document.getElementById('remote-container');
                    remoteContainer.innerHTML = `
                        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: var(--text-gray);">
                            <i class="bi bi-person-fill" style="font-size: 4rem; opacity: 0.3;"></i>
                            <p style="margin-top: 15px; font-size: 0.9rem;">Waiting for the other person to join...</p>
                        </div>`;
                }

                // Handle remote users
                client.on("user-published", async (user, mediaType) => {
                    await client.subscribe(user, mediaType);
                    
                    if (mediaType === "video") {
                        const remoteContainer = document.getElementById('remote-container');
                        remoteContainer.innerHTML = ''; // Clear placeholder
                        
                        user.videoTrack.play(remoteContainer);
                        
                        // Add label
                        const label = document.createElement('div');
                        label.className = "video-label";
                        const isRemoteHost = user.uid == "{{ $meeting->host_id }}";
                        label.innerText = isRemoteHost ? "Host" : "Participant";
                        remoteContainer.appendChild(label);
                        
                        document.getElementById('participant-count').innerText = "2 People in call";
                    }
                    
                    if (mediaType === "audio") {
                        user.audioTrack.play();
                    }
                });

                client.on("user-unpublished", (user) => {
                    const remoteContainer = document.getElementById('remote-container');
                    remoteContainer.innerHTML = `
                        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: var(--text-gray);">
                            <i class="bi bi-person-fill" style="font-size: 4rem; opacity: 0.3;"></i>
                            <p style="margin-top: 15px; font-size: 0.9rem;">The other person left the call.</p>
                        </div>`;
                    document.getElementById('participant-count').innerText = "1 Person in call";
                });

            } catch (error) {
                console.error("Agora Error:", error);
                document.getElementById('loadingOverlay').innerHTML = `<p style="color:#EF4444;">Error: ${error.message}</p>`;
            }
        }

        joinRoom();

        async function leave() {
            for (let trackName in localTracks) {
                let track = localTracks[trackName];
                if (track) { track.stop(); track.close(); }
            }
            if (screenTrack) { screenTrack.stop(); screenTrack.close(); }
            await client.leave();
        }

        document.getElementById('leave-btn').addEventListener('click', async (e) => {
            e.preventDefault();
            await leave();
            window.history.back();
        });
    })();
</script>
</body>
</html>
