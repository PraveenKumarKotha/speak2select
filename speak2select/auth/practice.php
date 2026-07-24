<?php
require_once '../includes/init.php';

if (!defined('APP_ACCESS')) {
    http_response_code(403);
    exit('Forbidden');
}

if (!isset($_SESSION['user_id'], $_SESSION['session_token'])) {
    header("Location: login.php");
    exit();
}

// 🔐 SINGLE-DEVICE CHECK
$stmt = $conn->prepare("SELECT name, session_token FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || $user['session_token'] !== $_SESSION['session_token']) {
    session_destroy();
    header("Location: login.php");
    exit();
}

$username = htmlspecialchars($user['name']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Practice – Speak2Select</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="author" content="Praveen Kumar Kotha – Speak2Select">
<meta name="copyright" content="© 2026 Speak2Select">
<style>
/* 🔒 CSS UNCHANGED */
body {
    font-family: "Segoe UI", Arial, sans-serif;
    background: linear-gradient(135deg, #eef2ff, #f8fafc);
    margin: 0;
    user-select: none;
}

/* Header */
header {
    background: linear-gradient(135deg, #0f172a, #1e293b);
    color: white;
    padding: 16px 22px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* Layout */
.container { padding: 24px; }

.box {
    background: #ffffff;
    padding: 30px;
    max-width: 840px;
    margin: auto;
    border-radius: 18px;
    box-shadow: 0 18px 40px rgba(15, 23, 42, 0.18);
    text-align: center;
}

/* Buttons – Global */
button {
    padding: 14px 26px;
    margin: 10px;
    border: none;
    border-radius: 14px;
    cursor: pointer;
    font-size: 15px;
    font-weight: 600;
    letter-spacing: 0.3px;
    transition: all 0.25s ease;
}

button:hover {
    transform: translateY(-3px);
    box-shadow: 0 14px 26px rgba(0,0,0,0.25);
}

button:active { transform: scale(0.95); }
button:disabled { opacity: 0.5; cursor: not-allowed; }

/* Level buttons */
.level-btn {
    background: linear-gradient(135deg, #fff1e6, #fde7d9);
    color: #7c2d12;
    border: 1px solid #f5c7a6;
}

.level-btn.active {
    background: linear-gradient(135deg, #4f7cff, #2f5bff);
    color: #ffffff;
    border: 1px solid #244bff;
    box-shadow:
        0 0 8px rgba(79,124,255,0.9),
        0 0 18px rgba(79,124,255,0.7),
        0 0 28px rgba(79,124,255,0.5);
}

/* Action buttons */
.listen {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
}
.record {
    background: linear-gradient(135deg, #ef4444, #b91c1c);
    color: white;
}
.next {
    background: linear-gradient(135deg, #8b5cf6, #6d28d9);
    color: white;
}

/* Voice dropdown */
.voice-wrap {
    margin: 20px 0 14px;
}
.voice-wrap label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #334155;
}
.voice-wrap select {
    padding: 12px 16px;
    font-size: 15px;
    border-radius: 12px;
    border: 1px solid #cbd5e1;
    background: #f8fafc;
    cursor: pointer;
}

/* Recording animation */
.recording {
    animation: pulse 1.2s infinite;
}
@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(239,68,68,0.7); }
    70% { box-shadow: 0 0 0 26px rgba(239,68,68,0); }
    100% { box-shadow: 0 0 0 0 rgba(239,68,68,0); }
}

/* Result */
.result-box {
    background: #f1f5f9;
    padding: 22px;
    margin-top: 26px;
    border-radius: 14px;
    text-align: left;
    box-shadow: inset 0 0 0 1px #e2e8f0;
}

.correct { color:#16a34a; font-weight:bold; }
.wrong { color:#dc2626; font-weight:bold; }

.status {
    margin-top: 16px;
    font-weight: 700;
    font-size: 18px;
    color: #2563eb;
}

.perfect-msg {
    margin-top: 14px;
    padding: 14px;
    background: linear-gradient(135deg, #22c55e, #16a34a);
    color: white;
    border-radius: 12px;
    font-size: 18px;
    text-align: center;
    transition: all 0.3s ease;
}

.perfect-msg:hover {
    transform: translateY(-4px);
    box-shadow: 0 14px 28px rgba(22,163,74,0.45);
}

/* Logout */
.logout {
    color: white;
    text-decoration: none;
    background: linear-gradient(135deg, #ef4444, #b91c1c);
    padding: 8px 16px;
    border-radius: 8px;
}

/* Balloons */
.balloon {
    position: fixed;
    bottom: -80px;
    width: 42px;
    height: 62px;
    border-radius: 50%;
    animation: rise 4s linear forwards;
}
@keyframes rise {
    to { transform: translateY(-120vh); opacity: 0; }
}

/* Confetti */
.confetti {
    position: fixed;
    top: -20px;
    width: 10px;
    height: 14px;
    animation: fall 4s linear forwards;
}
@keyframes fall {
    to { transform: translateY(120vh) rotate(720deg); opacity: 0; }
}

/* ================= MOBILE RESPONSIVE ================= */
@media (max-width: 768px) {

    header {
        flex-direction: column;
        gap: 8px;
        text-align: center;
        padding: 14px;
    }

    .container {
        padding: 12px;
    }

    .box {
        padding: 18px;
        border-radius: 14px;
    }

    /* Stack all buttons */
    button {
        width: 100%;
        margin: 6px 0;
        font-size: 14px;
        padding: 12px;
    }

    /* Dropdown full width */
    .voice-wrap select {
        width: 100%;
    }

    .status {
        font-size: 16px;
    }

    .perfect-msg {
        font-size: 16px;
        padding: 12px;
    }

    .result-box {
        padding: 16px;
        font-size: 14px;
    }
}


</style>
</head>

<body>

<header>
    <div><strong>Speak2Select</strong></div>
    <div>Hi, <?= $username ?> | <a href="logout.php" class="logout">Logout</a></div>
</header>

<div class="container">
<div class="box">

<h2>🎧 Listening and Repeating Practice</h2>
<h4>Listen. Speak. Succeed.</h4>
<p>Select → Listen → Record → Auto Feedback → Next</p>

<div id="levels">
    <button class="level-btn" data-level="small">Small</button>
    <button class="level-btn" data-level="medium">Medium</button>
    <button class="level-btn" data-level="large">Large</button>
    <button class="level-btn" data-level="random">Random</button>
</div>

<div class="voice-wrap">
    <label>Voice</label>
    <select id="voiceType">
        <option value="male">Male</option>
        <option value="female">Female</option>
    </select>
</div>

<div>
    <button id="listenBtn" class="listen" onclick="playSentence()" disabled>🔊 Listen</button>
    <button id="recordBtn" class="record" onclick="startRecording()" disabled>🎤 Record</button>
    <button id="nextBtn" class="next" onclick="nextQuestion()" disabled>➡ Next</button>
</div>

<div class="status" id="status"></div>
<div class="result-box" id="result" style="display:none;"></div>
<div id="perfectHolder"></div>

</div>
</div>

<!-- NOTE: we do NOT include the large question files directly here.
     They are loaded dynamically when the user clicks a level. -->

<script>
/* -------------------------
   LAZY-LOADER FOR QUESTIONS
   -------------------------
   This will dynamically load the corresponding questions file
   only when the user clicks a level button. No blocking at page load.
*/
const questionFiles = {
    small: 'questions/small.js',
    medium: 'questions/medium.js',
    large: 'questions/large.js'
};

const loadedFiles = {
    small: false,
    medium: false,
    large: false
};
function ensureLoadedSingle(level, cb) {
    if (loadedFiles[level]) {
        return cb();
    }

    const s = document.createElement('script');

    // 🔥 CACHE BUSTER ADDED HERE
    s.src = questionFiles[level] + '?v=' + Date.now();

    s.onload = () => {
        loadedFiles[level] = true;
        cb();
    };

    s.onerror = () => {
        const st = document.getElementById('status');
        if (st) st.innerText = '⚠ Failed to load question file: ' + questionFiles[level];
        cb();
    };

    document.head.appendChild(s);
}
function ensureLoaded(level, cb) {
    if (level === 'random') {
        // load all three in parallel, then call cb
        const parts = ['small', 'medium', 'large'];
        let remaining = parts.length;
        parts.forEach(l => {
            ensureLoadedSingle(l, () => {
                remaining--;
                if (remaining === 0) cb();
            });
        });
    } else {
        ensureLoadedSingle(level, cb);
    }
}

/* Attach listeners after full page is ready (but we will lazy-load large scripts only on click) */
window.addEventListener("load", function () {
    document.querySelectorAll(".level-btn").forEach(btn => {
        btn.addEventListener("click", function () {
            const lvl = this.dataset.level;
            const st = document.getElementById('status');
            if (st) st.innerText = '⏳ Loading questions...';

            // ensure files loaded, then call original startPractice (unchanged)
            ensureLoaded(lvl, () => {
                if (st) st.innerText = '';
                // If windows variables like window.SMALL_QUESTIONS are not defined, the startPractice
                // will handle empty pool case and show the "Questions not loaded" message.
                startPractice(lvl, this);
                // enable buttons (startPractice will enable them, but ensure)
                const listenBtn = document.getElementById("listenBtn");
                const recordBtn = document.getElementById("recordBtn");
                const nextBtn = document.getElementById("nextBtn");
                if (listenBtn) listenBtn.disabled = false;
                if (nextBtn) nextBtn.disabled = false;
            });
        });
    });
});
</script>
<script>
/*
====================================
 Speak2Select™ – ORIGINAL PLATFORM
 Developed by Praveen Kumar Kotha
 Year: 2026
 Unauthorized copying is prohibited
====================================
*/
console.log(
  "%cSpeak2Select™ – Original Platform",
  "color:#ef4444;font-size:16px;font-weight:bold"
);
</script>
<!-- The rest of your original JS — unchanged -->
<script>
/* ✅ DOM bindings */
const status = document.getElementById("status");
const recordBtn = document.getElementById("recordBtn");
const listenBtn = document.getElementById("listenBtn");
const nextBtn = document.getElementById("nextBtn");
const result = document.getElementById("result");
const perfectHolder = document.getElementById("perfectHolder");
const voiceType = document.getElementById("voiceType");

/* ✅ SINGLE declaration */
let questionPool = [];

let currentSentence = "", currentLevel = "", recognition = null;
let lastSpoken = "", isRecording = false;
let maleVoice = null, femaleVoice = null;

/* ================= CORE ================= */

function stopAllActions(){
    speechSynthesis.cancel();
    if(recognition){
        try { recognition.abort(); } catch(e){}
        recognition = null;
    }
    isRecording = false;
    recordBtn && recordBtn.classList.remove("recording");
    listenBtn && (listenBtn.disabled = false);
    nextBtn && (nextBtn.disabled = false);
}

speechSynthesis.onvoiceschanged = () => {
    const voices = speechSynthesis.getVoices().filter(v =>
        v.lang && v.lang.startsWith && v.lang.startsWith("en")
    );

    // Prefer high-quality voices
    femaleVoice =
        voices.find(v =>
            /female|woman|zira|susan|aria|jenny|emma/i.test(v.name)
        ) ||
        voices.find(v =>
            /google|microsoft/i.test(v.name) && /f/i.test(v.gender || "")
        ) ||
        voices[0];

    maleVoice =
        voices.find(v =>
            /male|man|david|mark|ryan|john|guy/i.test(v.name)
        ) ||
        voices.find(v =>
            /google|microsoft/i.test(v.name)
        ) ||
        voices[0];
};


/* ===== NUMBER NORMALIZATION (five ↔ 5 fix) ===== */
function normalizeNumbers(text) {
    const map = {
        "0":"zero","1":"one","2":"two","3":"three","4":"four",
        "5":"five","6":"six","7":"seven","8":"eight","9":"nine"
    };
    return text.replace(/\b\d\b/g, d => map[d]);
}

function normalize(t){
    if (!t) return "";
    t = normalizeNumbers(t);
    return t.toLowerCase().replace(/[^\w\s]/g,'');
}

function clearResult(){
    if (result) result.style.display = "none";
    if (result) result.innerHTML = "";
    if (perfectHolder) perfectHolder.innerHTML = "";
    lastSpoken = "";
}

/* ================= PRACTICE ================= */

function startPractice(level, btn){
    currentLevel = level;

    document.querySelectorAll('.level-btn')
        .forEach(b => b.classList.remove('active'));
    btn && btn.classList.add('active');

    if(level === 'small'){
        questionPool = window.SMALL_QUESTIONS || [];
    } else if(level === 'medium'){
        questionPool = window.MEDIUM_QUESTIONS || [];
    } else if(level === 'large'){
        questionPool = window.LARGE_QUESTIONS || [];
    } else {
        questionPool = [
            ...(window.SMALL_QUESTIONS || []),
            ...(window.MEDIUM_QUESTIONS || []),
            ...(window.LARGE_QUESTIONS || [])
        ];
    }

    if(!questionPool || questionPool.length === 0){
        if (status) status.innerText = "⚠ Questions not loaded";
        return;
    }

    nextQuestion();
    listenBtn && (listenBtn.disabled = false);
    nextBtn && (nextBtn.disabled = false);
}

function nextQuestion(){
    stopAllActions();

    if (!questionPool || questionPool.length === 0) {
        currentSentence = "";
        if (status) status.innerText = "⚠ Questions not loaded";
        return;
    }

    currentSentence =
        questionPool[Math.floor(Math.random() * questionPool.length)];

    clearResult();
    if (status) status.innerText = "🆕 New sentence loaded";
    if (recordBtn) recordBtn.disabled = true;
}

/* ================= AUDIO ================= */

function playSentence(){
    stopAllActions();
    clearResult();
    if (status) status.innerText = "🔊 Listen Carefully...";

    if (recordBtn) recordBtn.disabled = true;
    if (nextBtn) nextBtn.disabled = true;

    if(!currentSentence) {
        if (status) status.innerText = "⚠ No sentence loaded";
        return;
    }

    const u = new SpeechSynthesisUtterance(currentSentence);
    u.voice = voiceType && voiceType.value === "male" ? maleVoice : femaleVoice;
    u.onend = () => {
    stopAllActions();

    /* 🔓 enable record after listen */
    if (recordBtn) recordBtn.disabled = false;
    };

    speechSynthesis.speak(u);
}

function startRecording(){
    const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
    if(!SR || isRecording) return;

    stopAllActions();
    clearResult();

    isRecording = true;
    if (status) status.innerText = "🎤 Listening...";
    if (listenBtn) listenBtn.disabled = true;
    if (nextBtn) nextBtn.disabled = true;

    recognition = new SR();
    recognition.lang = "en-IN";
    recognition.interimResults = false;

    try {
        recognition.start();
    } catch(e){
        // sometimes start may throw if already started — ignore
    }

    recordBtn && recordBtn.classList.add("recording");

    setTimeout(() => {
        try { recognition && recognition.stop(); } catch(e){}
    }, 5000);

    recognition.onresult = e => {
        lastSpoken = e.results[0][0].transcript;
    };

    recognition.onend = () => {
        stopAllActions();
        showResult(lastSpoken);
    };

    recognition.onerror = (e) => {
        // stop and show error in status
        try { recognition.stop(); } catch(er){}
        stopAllActions();
        if (status) status.innerText = "⚠ Speech recognition error";
    };
}

/* ================= RESULT ================= */

function showResult(spoken){
    const actual = normalize(currentSentence).split(" ").filter(Boolean);
    const said = normalize(spoken || "").split(" ").filter(Boolean);

    let correct = 0;
    let html = `<h3>Actual</h3><p>${currentSentence}</p>
                <h3>You Spoke</h3><p>`;

    actual.forEach((w,i)=>{
        if(said[i] === w){
            correct++;
            html += `<span class="correct">${w}</span> `;
        } else {
            html += `<span class="wrong">${said[i] || "_"}</span> `;
        }
    });

    html += "</p>";
    if (result) result.innerHTML = html;
    if (result) result.style.display = "block";
    setTimeout(scrollToResult, 200);
    if(correct === actual.length){
        if (perfectHolder) perfectHolder.innerHTML =
            `<div class="perfect-msg">👏 Good job! You’re perfect!</div>`;
        celebrate();
    }
}

/* ================= CELEBRATION ================= */

function celebrate(){
    for(let i=0;i<25;i++){
        const b=document.createElement("div");
        b.className="balloon";
        b.style.left=Math.random()*100+"vw";
        b.style.background=`hsl(${Math.random()*360},80%,60%)`;
        document.body.appendChild(b);
        setTimeout(()=>b.remove(),4000);
    }
    for(let i=0;i<40;i++){
        const c=document.createElement("div");
        c.className="confetti";
        c.style.left=Math.random()*100+"vw";
        c.style.background=`hsl(${Math.random()*360},90%,60%)`;
        document.body.appendChild(c);
        setTimeout(()=>c.remove(),4000);
    }
}
function scrollToResult() {
    const res = document.getElementById("result");
    if (res && res.style.display !== "none") {
        res.scrollIntoView({
            behavior: "smooth",
            block: "start"
        });
    }
}
</script>
<script>
/* 🔐 SMART SINGLE-DEVICE SESSION GUARD (LOW LOAD) */
let lastSessionCheck = 0;

function checkSessionSmart() {
    const now = Date.now();

    // prevent frequent checks (min 30 seconds)
    if (now - lastSessionCheck < 30000) return;
    lastSessionCheck = now;

    fetch('../includes/check_session.php')
        .then(res => res.text())
        .then(status => {
            if (status.trim() === 'logout') {
                alert("Your account was logged in on another device.");
                window.location.href = "login.php";
            }
        })
        .catch(() => {}); // fail silently
}

/* Check on user activity */
['click', 'keydown'].forEach(evt => {
    document.addEventListener(evt, checkSessionSmart);
});

/* Check when tab becomes active */
document.addEventListener('visibilitychange', () => {
    if (!document.hidden) {
        checkSessionSmart();
    }
});

/* Safety check every 2 minutes */
setInterval(checkSessionSmart, 120000);
</script>
<script>
/* ===============================
   BASIC DEVTOOLS / COPY DETERRENT
   =============================== */

// Disable right click
document.addEventListener("contextmenu", e => e.preventDefault());

// Block common shortcuts
document.addEventListener("keydown", e => {
    if (
        e.key === "F12" ||
        (e.ctrlKey && e.shiftKey && ["I","C","J"].includes(e.key)) ||
        (e.ctrlKey && e.key === "U")
    ) {
        e.preventDefault();
        alert("⚠️ Source viewing is disabled.");
    }
});

// Detect DevTools open (size trick)
setInterval(() => {
    if (
        window.outerWidth - window.innerWidth > 160 ||
        window.outerHeight - window.innerHeight > 160
    ) {
        document.body.innerHTML =
            "<h2 style='text-align:center;margin-top:20%'>⚠️ DevTools Detected</h2>";
    }
}, 1500);
</script>
</body>
</html>
