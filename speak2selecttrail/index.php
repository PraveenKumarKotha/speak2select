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
    <div>Practice Mode</div>
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
window.addEventListener("load", function () {
    document.querySelectorAll(".level-btn").forEach(btn => {
        btn.addEventListener("click", function () {
            startPractice(this.dataset.level, this);
        });
    });
});
</script>

<script>
/*
====================================
 Speak2Select™ – Practice Module
 Developed by Praveen Kumar Kotha
 Year: 2026
 This is an original learning interface.
====================================
*/
console.log(
  "%cSpeak2Select™ – Practice Module",
  "color:#2563eb;font-size:15px;font-weight:bold"
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
/* ================= QUESTIONS INLINE ================= */

window.SMALL_QUESTIONS = [
    "I like apples",
    "She is happy",
    "This is my book",
    "Open the door",
    "He runs fast"
];

window.MEDIUM_QUESTIONS = [
    "Practice makes a person perfect",
    "She is preparing for the interview",
    "He completed the task successfully",
    "Learning English improves confidence",
    "Consistency leads to success",
];

window.LARGE_QUESTIONS = [
    "Confidence improves when you practice speaking English daily",
    "Effective communication skills are essential for professional success",
    "Consistent practice helps you overcome fear while speaking",
    "Listening carefully helps improve pronunciation and clarity",
    "Success depends on discipline dedication and continuous effort",
];
</script>
<script>
// Disable right click
document.addEventListener("contextmenu", function(e){
    e.preventDefault();
});

// Disable inspect shortcuts
document.addEventListener("keydown", function(e){
    if (
        e.key === "F12" ||
        (e.ctrlKey && e.shiftKey && ["I","C","J"].includes(e.key)) ||
        (e.ctrlKey && e.key === "U")
    ) {
        e.preventDefault();
        alert("Inspect is disabled");
    }
});
</script>
<script>
document.addEventListener("copy", e => e.preventDefault());
</script>

</body>
</html>
