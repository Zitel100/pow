<?php
session_start();

// ============================================
// CONFIGURATION - Change these as needed
// ============================================
$ADMIN_TOKEN = "MySecretToken2026!"; // Must match your Railway environment variable
$POW_SERVER_URL = "https://pow-production-9e6e.up.railway.app"; // Your Railway app URL
$REDIRECT_URL = "/target-page.php"; // Where to redirect after successful verification
// ============================================

// Handle POST verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nonce'])) {
    $nonce = $_POST['nonce'];
    $challenge = $_SESSION['current_challenge'] ?? '';
    
    if (empty($challenge) || empty($nonce)) {
        http_response_code(403);
        echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
        <html><head>
        <title>403 Forbidden</title>
        </head><body>
        <h1>Forbidden</h1>
        <p>You don\'t have permission to access this resource.</p>
        <p>Additionally, a 403 Forbidden error was encountered while trying to use an ErrorDocument to handle the request.</p>
        </body></html>';
        exit;
    }
    
    // Call the Verify endpoint
    $verify_url = $POW_SERVER_URL . "/Verify?challenge=" . urlencode($challenge) . "&nonce=" . urlencode($nonce);
    
    $ch = curl_init($verify_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer " . $ADMIN_TOKEN,
        "User-Agent: Mozilla/5.0 (compatible; PoW-Verifier/1.0)",
        "Accept: application/json",
    ]);
    
    $response = curl_exec($ch);
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_status === 200) {
        $_SESSION['session_verified'] = true;
        header('Location: ' . $REDIRECT_URL);
        exit;
    } else {
        http_response_code(403);
        echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
        <html><head>
        <title>403 Forbidden</title>
        </head><body>
        <h1>Forbidden</h1>
        <p>You don\'t have permission to access this resource.</p>
        <p>Additionally, a 403 Forbidden error was encountered while trying to use an ErrorDocument to handle the request.</p>
        </body></html>';
        exit;
    }
}

// Fetch challenge from PoW server
$api_url = $POW_SERVER_URL . "/GetChallenges?difficultyLevel=2";

$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer " . $ADMIN_TOKEN,
    "User-Agent: Mozilla/5.0 (compatible; PoW-Challenge/1.0)",
    "Accept: application/json",
]);

$response = curl_exec($ch);
$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$challenge_token = '';

if ($http_status === 200) {
    $challenge_list = json_decode($response, true);
    if (is_array($challenge_list) && !empty($challenge_list)) {
        $challenge_token = $challenge_list[0];
        $_SESSION['current_challenge'] = $challenge_token;
    }
}

// Fallback challenge if API fails
if (empty($challenge_token)) {
    $fallback_challenge = [
        'N' => 4096,
        'r' => 8,
        'p' => 1,
        'klen' => 32,
        'i' => base64_encode(random_bytes(16)),
        'd' => '00',
        'dl' => 2
    ];
    $challenge_token = base64_encode(json_encode($fallback_challenge));
    $_SESSION['current_challenge'] = $challenge_token;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Verify your session</title>
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        body{min-height:100vh;display:flex;align-items:center;justify-content:center;background:#fff;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;padding:24px}
        .mh8u3v1h9{width:100%;max-width:400px;background:#fff;border-radius:12px;border:1px solid #e5e7eb;border-top:3px solid #4F6EF7}
        .m2obgm5mq05{padding:32px 28px 26px}
        .fog36t{width:42px;height:42px;border-radius:50%;background:#f3f4f6;color:#4F6EF7;display:flex;align-items:center;justify-content:center;margin-bottom:20px}
        .fog36t svg{width:20px;height:20px}
        h1{font-size:20px;font-weight:700;color:#111827;margin-bottom:6px;letter-spacing:-.025em}
        .b2klj2hlka{font-size:14px;color:#6b7280;line-height:1.55;margin-bottom:24px}
        .iamyhr7{width:100%;padding:13px;border:none;border-radius:8px;background:#4F6EF7;color:#fff;font-size:14px;font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:10px;text-decoration:none;transition:opacity .15s,transform .1s}
        .iamyhr7:hover:not(:disabled){opacity:.9}
        .iamyhr7:active:not(:disabled){transform:scale(.99)}
        .iamyhr7:disabled{background:#e5e7eb;color:#9ca3af;cursor:not-allowed;opacity:1}
        .bqkt59d6{width:15px;height:15px;border:2px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;animation:s5d730humyp .65s linear infinite;display:none}
        .iamyhr7.otxi4z .bqkt59d6{display:inline-block;border-color:#d1d5db;border-top-color:#9ca3af}
        @keyframes s5d730humyp{to{transform:rotate(360deg)}}
        .b4qn8zq0g{margin-top:16px;text-align:center;font-size:11px;color:#9ca3af}
        
        /* PoW Bot Deterrent CSS */
        .pow-bot-deterrent {
            background-color: #f3f4f6;
            border: 1px solid #9359fa;
            border-radius: 1em;
            font-size: 0.9em;
            padding: 0.8em;
            padding-top: 0.5em;
            border-bottom: 2px solid #452775;
            margin-bottom: 1em;
            min-width: 20em;
        }
        
        .pow-bot-deterrent-link {
            color: #452775;
            font-weight: bold;
            text-decoration: underline;
            font-size: 1.2em;
            font-family: monospace;
        }
        .pow-bot-deterrent-link:hover,
        .pow-bot-deterrent-link:active,
        .pow-bot-deterrent-link:visited {
            color: #452775;
        }
        
        .pow-bot-deterrent-row {
            display: inline-flex;
            flex-direction: row;
            align-content: center;
            width: 100%;
            justify-content: space-between;
        }
        
        .pow-bot-deterrent-icon-container {
            margin-left: 0.5em;
            margin-top: 0.2em;
            margin-bottom: -0.5em;
            margin-right: 0.2em;
        }
        
        .pow-bot-deterrent-best-hash {
            font-family: monospace;
            background: #585a29;
            color: #f6ff72;
            transition: background 0.5s ease-in-out, color 0.5s ease-in-out;
            padding: 0.2em 0.8em;
            padding-bottom: 0.3em;
            margin-left: -0.5em;
            border-radius: 0.5em;
            font-size: 0.7em;
            font-weight: bolder;
            display: block;
            float: right;
        }
        
        .pow-bot-deterrent-best-hash-done {
            background: #3b6262;
            color: #53f65d;
        }
        
        .pow-bot-deterrent-description {
            margin-top: 0.5em;
            font-size: 0.85em;
            color: #6b7280;
        }
        
        .pow-bot-deterrent-progress-bar-container {
            border-radius: 1em;
            background: #444;
            height: 0.8em;
            margin-top: 0.5em;
            border: 1px solid #727630;
            box-sizing: content-box;
            overflow: hidden;
        }
        
        .pow-bot-deterrent-progress-bar {
            background: #f6ff72;
            height: 0.8em;
            width: 0%;
            border-radius: 1em;
            transition: width 0.5s ease-in-out;
        }
        
        .pow-bot-deterrent-icon {
            height: 3em;
        }
        
        .pow-bot-deterrent-hidden {
            display: none;
        }
        
        .pow-checkmark-icon-checkmark {
            fill:none;
            stroke: #31bd82;
            stroke-width: 6em;
            stroke-dasharray: 60em;
            stroke-dashoffset: 74em;
            stroke-linecap: round;
            stroke-linejoin: round;
            animation: 0.8s normal forwards ease-in-out pow-draw-checkmark;
            animation-play-state: paused;
        }
        
        .pow-checkmark-icon-border {
            fill:none;
            stroke: #aaa;
            stroke-width: 3em;
            stroke-dasharray: 110em;
            stroke-dashoffset: 110em;
            stroke-linecap: round;
            stroke-linejoin: round;
            animation: 0.8s normal forwards ease-in-out pow-draw-checkmark-border;
            animation-play-state: paused;
        }
        
        .pow-gears-icon-gear-large {
            fill: #9359fa;
            animation: 4s linear infinite pow-spinning-gears-large;
            animation-play-state: running;
        }
        .pow-gears-icon-gear-small {
            fill: #9359fa;
            animation: 4s linear infinite pow-spinning-gears-small;
            animation-play-state: running;
        }
        
        @keyframes pow-draw-checkmark-border {
            0% { stroke-dashoffset: 110em; }
            100% { stroke-dashoffset: 10em; }
        }
        
        @keyframes pow-draw-checkmark {
            0% { stroke-dashoffset: 74em; }
            100% { stroke-dashoffset: 120em; }
        }
        
        @keyframes pow-spinning-gears-small {
            0% { transform: translate(161px, 161px) rotate(0deg) translate(-161px,-161px); }
            100% { transform: translate(161px, 161px) rotate(360deg) translate(-161px,-161px); }
        }
        
        @keyframes pow-spinning-gears-large {
            0% { transform: translate(73px, 73px) rotate(360deg) translate(-73px,-73px); }
            100% { transform: translate(73px, 73px) rotate(0deg) translate(-73px,-73px); }
        }
        
        .verification-status {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 12px;
            margin-bottom: 16px;
            background: #f9fafb;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            min-height: 48px;
        }
        
        .verification-status .spinner {
            width: 20px;
            height: 20px;
            border: 2px solid #e5e7eb;
            border-top-color: #4F6EF7;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            display: none;
        }
        
        .verification-status .spinner.active {
            display: block;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .verification-status .status-text {
            font-size: 14px;
            color: #374151;
        }
        
        .verification-status .status-text.success {
            color: #10b981;
        }
        
        .verification-status .status-text.error {
            color: #ef4444;
        }
        
        .verification-status .status-text.loading {
            color: #4F6EF7;
        }
    </style>
</head>
<body>
<div class="mh8u3v1h9">
    <form method="POST" action="/" id="challengeForm" class="m2obgm5mq05">
        <div class="fog36t">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                <path d="M9 12l2 2 4-4"/>
            </svg>
        </div>
        <h1>Verify your session</h1>
        <p class="b2klj2hlka">Please verify before continuing.</p>
        
        <input type="hidden" name="challenge" value="<?php echo htmlspecialchars($challenge_token, ENT_QUOTES, 'UTF-8'); ?>" />
        <input type="hidden" name="nonce" id="nonce_input" value="" />
        
        <div class="pow-bot-deterrent" 
             id="powContainer"
             data-pow-bot-deterrent-static-assets-cross-origin-url="<?php echo $POW_SERVER_URL; ?>/pow-bot-deterrent-static/"
             data-pow-bot-deterrent-challenge="<?php echo htmlspecialchars($challenge_token, ENT_QUOTES, 'UTF-8'); ?>"
             data-pow-bot-deterrent-callback="myPowCallback">
            <div class="pow-bot-deterrent-row">
                <div>
                    <a class="pow-bot-deterrent-link" href="https://git.sequentialread.com/forest/pow-bot-deterrent" target="_blank">💥PoW! <span>Bot Deterrent</span></a>
                    <div class="pow-bot-deterrent-description">Creating <a href="https://en.wikipedia.org/wiki/Proof_of_work" target="_blank">Proof of Work</a>. Privacy-respecting anti-spam measure.</div>
                </div>
                <div class="pow-bot-deterrent-icon-container">
                    <svg xmlns="http://www.w3.org/2000/svg" xml:space="preserve" version="1.1" viewBox="-30 -5 250 223" class="pow-gears-icon pow-bot-deterrent-icon">
                        <path class="pow-gears-icon-gear-large" d="M113.595,133.642l-5.932-13.169c5.655-4.151,10.512-9.315,14.307-15.209l13.507,5.118c2.583,0.979,5.469-0.322,6.447-2.904	l4.964-13.103c0.47-1.24,0.428-2.616-0.117-3.825c-0.545-1.209-1.547-2.152-2.788-2.622l-13.507-5.118	c1.064-6.93,0.848-14.014-0.637-20.871l13.169-5.932c1.209-0.545,2.152-1.547,2.622-2.788c0.47-1.24,0.428-2.616-0.117-3.825	l-5.755-12.775c-1.134-2.518-4.096-3.638-6.612-2.505l-13.169,5.932c-4.151-5.655-9.315-10.512-15.209-14.307l5.118-13.507	c0.978-2.582-0.322-5.469-2.904-6.447L93.88,0.82c-1.239-0.469-2.615-0.428-3.825,0.117c-1.209,0.545-2.152,1.547-2.622,2.788	l-5.117,13.506c-6.937-1.07-14.033-0.849-20.872,0.636L55.513,4.699c-0.545-1.209-1.547-2.152-2.788-2.622	c-1.239-0.469-2.616-0.428-3.825,0.117L36.124,7.949c-2.518,1.134-3.639,4.094-2.505,6.612l5.932,13.169	c-5.655,4.151-10.512,9.315-14.307,15.209l-13.507-5.118c-1.239-0.469-2.615-0.427-3.825,0.117	c-1.209,0.545-2.152,1.547-2.622,2.788L0.326,53.828c-0.978,2.582,0.322,5.469,2.904,6.447l13.507,5.118	c-1.064,6.929-0.848,14.015,0.637,20.871L4.204,92.196c-1.209,0.545-2.152,1.547-2.622,2.788c-0.47,1.24-0.428,2.616,0.117,3.825	l5.755,12.775c0.544,1.209,1.547,2.152,2.787,2.622c1.241,0.47,2.616,0.429,3.825-0.117l13.169-5.932	c4.151,5.656,9.314,10.512,15.209,14.307l-5.118,13.507c-0.978,2.582,0.322,5.469,2.904,6.447l13.103,4.964	c0.571,0.216,1.172,0.324,1.771,0.324c0.701,0,1.402-0.147,2.054-0.441c1.209-0.545,2.152-1.547,2.622-2.788l5.117-13.506	c6.937,1.069,14.034,0.849,20.872-0.636l5.931,13.168c0.545,1.209,1.547,2.152,2.788,2.622c1.24,0.47,2.617,0.429,3.825-0.117	l12.775-5.754C113.607,139.12,114.729,136.16,113.595,133.642z M105.309,86.113c-4.963,13.1-17.706,21.901-31.709,21.901	c-4.096,0-8.135-0.744-12.005-2.21c-8.468-3.208-15.18-9.522-18.899-17.779c-3.719-8.256-4-17.467-0.792-25.935	c4.963-13.1,17.706-21.901,31.709-21.901c4.096,0,8.135,0.744,12.005,2.21c8.468,3.208,15.18,9.522,18.899,17.778	C108.237,68.434,108.518,77.645,105.309,86.113z"/>
                        <path class="pow-gears-icon-gear-small" d="M216.478,154.389c-0.896-0.977-2.145-1.558-3.469-1.615l-9.418-0.404	c-0.867-4.445-2.433-8.736-4.633-12.697l6.945-6.374c2.035-1.867,2.17-5.03,0.303-7.064l-6.896-7.514	c-0.896-0.977-2.145-1.558-3.47-1.615c-1.322-0.049-2.618,0.416-3.595,1.312l-6.944,6.374c-3.759-2.531-7.9-4.458-12.254-5.702	l0.404-9.418c0.118-2.759-2.023-5.091-4.782-5.209l-10.189-0.437c-2.745-0.104-5.091,2.023-5.209,4.781l-0.404,9.418	c-4.444,0.867-8.735,2.433-12.697,4.632l-6.374-6.945c-0.896-0.977-2.145-1.558-3.469-1.615c-1.324-0.054-2.618,0.416-3.595,1.312	l-7.514,6.896c-2.035,1.867-2.17,5.03-0.303,7.064l6.374,6.945c-2.531,3.759-4.458,7.899-5.702,12.254l-9.417-0.404	c-2.747-0.111-5.092,2.022-5.21,4.781l-0.437,10.189c-0.057,1.325,0.415,2.618,1.312,3.595c0.896,0.977,2.145,1.558,3.47,1.615	l9.417,0.403c0.867,4.445,2.433,8.736,4.632,12.698l-6.944,6.374c-0.977,0.896-1.558,2.145-1.615,3.469	c-0.057,1.325,0.415,2.618,1.312,3.595l6.896,7.514c0.896,0.977,2.145,1.558,3.47,1.615c1.319,0.053,2.618-0.416,3.595-1.312	l6.944-6.374c3.759,2.531,7.9,4.458,12.254,5.702l-0.404,9.418c-0.118,2.759,2.022,5.091,4.781,5.209l10.189,0.437	c0.072,0.003,0.143,0.004,0.214,0.004c1.25,0,2.457-0.468,3.381-1.316c0.977-0.896,1.558-2.145,1.615-3.469l0.404-9.418	c4.444-0.867,8.735-2.433,12.697-4.632l6.374,6.945c0.896,0.977,2.145,1.558,3.469,1.615c1.33,0.058,2.619-0.416,3.595-1.312	l7.514-6.896c2.035-1.867,2.17-5.03,0.303-7.064l-6.374-6.945c2.531-3.759,4.458-7.899,5.702-12.254l9.417,0.404	c2.756,0.106,5.091-2.022,5.21-4.781l0.437-10.189C217.847,156.659,217.375,155.366,216.478,154.389z M160.157,183.953	c-12.844-0.55-22.846-11.448-22.295-24.292c0.536-12.514,10.759-22.317,23.273-22.317c0.338,0,0.678,0.007,1.019,0.022	c12.844,0.551,22.846,11.448,22.295,24.292C183.898,174.511,173.106,184.497,160.157,183.953z"/>
                    </svg>
                    <svg xmlns="http://www.w3.org/2000/svg" xml:space="preserve" version="1.1" viewBox="0 0 512 512" class="pow-checkmark-icon pow-bot-deterrent-icon pow-bot-deterrent-hidden">
                        <polyline class="pow-checkmark-icon-checkmark" points="444,110 206,343 120,252"/>
                        <polyline class="pow-checkmark-icon-border" points="240,130 30,130 30,470 370,470 370,350"/>
                    </svg>
                </div>
            </div>
            <div class="pow-bot-deterrent-best-hash" id="powBestHash">loading...</div>
            <div class="pow-bot-deterrent-progress-bar-container">
                <div class="pow-bot-deterrent-progress-bar" id="powProgressBar" style="width:0%"></div>
            </div>
        </div>
        
        <div class="verification-status" id="verificationStatus">
            <div class="spinner active" id="statusSpinner"></div>
            <span class="status-text loading" id="statusText">Verifying your session...</span>
        </div>

        <button type="submit" class="iamyhr7" id="submitBtn" disabled>
            <span class="bqkt59d6" id="spinner"></span>
            <span id="jqcmz5x3h16zxx">Complete Verification</span>
        </button>
        <div class="b4qn8zq0g">Session ref · <span id="an9fh0klqcjrgft">---</span></div>
    </form>
</div>

<!-- Load the PoW Bot Deterrent library from the same Railway server -->
<script src="<?php echo $POW_SERVER_URL; ?>/pow-bot-deterrent-static/pow-bot-deterrent.js"></script>

<script>
(function(){
    var btn = document.getElementById("submitBtn"),
        txt = document.getElementById("jqcmz5x3h16zxx"),
        ref = document.getElementById("an9fh0klqcjrgft"),
        nonceInput = document.getElementById("nonce_input");
    
    var statusSpinner = document.getElementById("statusSpinner");
    var statusText = document.getElementById("statusText");
    var powProgressBar = document.getElementById("powProgressBar");
    var powBestHash = document.getElementById("powBestHash");
    
    if(ref) {
        ref.textContent = Math.random().toString(36).slice(2,10).toUpperCase();
    }
    
    if(!btn || !txt || !nonceInput) {
        console.error("Required elements not found");
        return;
    }
    
    // Callback function that the PoW library will call when solved
    window.myPowCallback = function(nonce) {
        console.log("myPowCallback called with nonce:", nonce);
        
        if (nonceInput) {
            nonceInput.value = nonce;
        }
        
        if (statusSpinner) {
            statusSpinner.className = "spinner";
        }
        if (statusText) {
            statusText.className = "status-text success";
            statusText.textContent = "✅ Verification complete!";
        }
        if (powBestHash) {
            powBestHash.textContent = nonce + " ✅";
            powBestHash.className = "pow-bot-deterrent-best-hash pow-bot-deterrent-best-hash-done";
        }
        if (powProgressBar) {
            powProgressBar.style.width = "100%";
        }
        
        if (btn) {
            btn.disabled = false;
            btn.style.background = "#10b981";
        }
        if (txt) {
            txt.textContent = "Verification Complete";
        }
        
        console.log("Proof of Work completed with nonce:", nonce);
        
        setTimeout(function() {
            console.log("Auto-submitting form with nonce:", nonceInput ? nonceInput.value : 'unknown');
            var form = document.getElementById('challengeForm');
            if (form) {
                form.submit();
            }
        }, 400);
    };
    
    // Wait for the library to load, then initialize it
    var loadCheckInterval = setInterval(function() {
        if (typeof window.powBotDeterrentInit !== 'undefined') {
            clearInterval(loadCheckInterval);
            console.log("PoW Bot Deterrent loaded successfully");
            
            if (statusText) {
                statusText.textContent = "Starting PoW...";
            }
            
            if (window.powBotDeterrentInitDone) {
                window.powBotDeterrentReset();
            }
            window.powBotDeterrentInit();
            
            if (typeof window.powBotDeterrentTrigger === 'function') {
                console.log("Triggering PoW manually");
                window.powBotDeterrentTrigger();
            } else {
                setTimeout(function() {
                    if (typeof window.powBotDeterrentTrigger === 'function') {
                        console.log("Triggering PoW manually (delayed)");
                        window.powBotDeterrentTrigger();
                    }
                }, 1000);
            }
        }
    }, 500);
    
    // Fallback: If library doesn't load within 5 seconds, use simplified PoW
    setTimeout(function() {
        if (typeof window.powBotDeterrentInit === 'undefined') {
            clearInterval(loadCheckInterval);
            console.warn("PoW Bot Deterrent not loaded - using fallback");
            if (statusText) {
                statusText.textContent = "Using fallback verification...";
            }
            solveFallbackPoW();
        }
    }, 5000);
    
    function solveFallbackPoW() {
        var challenge = "<?php echo htmlspecialchars($challenge_token, ENT_QUOTES, 'UTF-8'); ?>";
        if (!challenge || challenge === '') {
            if (statusText) {
                statusText.textContent = "❌ No challenge available";
            }
            if (powBestHash) {
                powBestHash.textContent = "ERROR";
            }
            return;
        }
        
        if (statusText) {
            statusText.textContent = "Calculating proof of work...";
        }
        if (powBestHash) {
            powBestHash.textContent = "Working...";
        }
        
        setTimeout(function() {
            try {
                var nonce = 0;
                var maxNonce = 1000000;
                var bestHash = "ffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffff";
                
                while (nonce < maxNonce) {
                    var message = challenge + nonce;
                    var hash = simpleHash(message);
                    
                    if (hash < bestHash) {
                        bestHash = hash;
                        if (powBestHash) {
                            powBestHash.textContent = bestHash.substring(0, 8) + "...";
                        }
                    }
                    
                    var leadingZeros = 0;
                    for (var i = 0; i < hash.length; i++) {
                        if (hash[i] === '0') {
                            leadingZeros++;
                        } else {
                            break;
                        }
                    }
                    
                    if (leadingZeros >= 2) {
                        if (powBestHash) {
                            powBestHash.textContent = hash.substring(0, 8) + " ✅";
                        }
                        window.myPowCallback(nonce.toString(16));
                        return;
                    }
                    nonce++;
                    
                    if (nonce % 1000 === 0) {
                        var progress = Math.min((nonce / 100000) * 100, 99);
                        if (powProgressBar) {
                            powProgressBar.style.width = progress + "%";
                        }
                        if (statusText) {
                            statusText.textContent = "Calculating proof of work... " + Math.round(progress) + "%";
                        }
                    }
                }
                
                window.myPowCallback(Math.floor(Math.random() * 1000).toString(16));
            } catch (error) {
                console.error("Fallback verification failed:", error);
                if (statusText) {
                    statusText.textContent = "❌ Verification failed";
                }
                if (powBestHash) {
                    powBestHash.textContent = "ERROR";
                }
            }
        }, 50);
    }
    
    function simpleHash(message) {
        var hash = 0;
        for (var i = 0; i < message.length; i++) {
            var char = message.charCodeAt(i);
            hash = ((hash << 5) - hash) + char;
            hash = hash & hash;
        }
        return Math.abs(hash).toString(16).padStart(64, '0');
    }
})();
</script>
</body>
</html>
