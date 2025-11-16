const API_KEY ;
const DEMO_MODE = API_KEY === "YOUR_API_KEY_HERE";
if (DEMO_MODE)
    document.getElementById("demoModeIndicator").classList.remove("hidden");

const userName = document.getElementById("user-name")?.value || "User";

// Tabs
const textChatTab = document.getElementById("textChatTab");
const voiceChatTab = document.getElementById("voiceChatTab");
const textChatSection = document.getElementById("textChatSection");
const voiceChatSection = document.getElementById("voiceChatSection");

textChatTab.addEventListener("click", () => {
    textChatSection.classList.remove("hidden");
    voiceChatSection.classList.add("hidden");
    textChatTab.classList.remove("bg-gray-200", "text-gray-800");
    textChatTab.classList.add("bg-black", "text-white");
    voiceChatTab.classList.remove("bg-black", "text-white");
    voiceChatTab.classList.add("bg-gray-200", "text-gray-800");
});

voiceChatTab.addEventListener("click", async () => {
    textChatSection.classList.add("hidden");
    voiceChatSection.classList.remove("hidden");
    voiceChatTab.classList.remove("bg-gray-200", "text-gray-800");
    voiceChatTab.classList.add("bg-black", "text-white");
    textChatTab.classList.remove("bg-black", "text-white");
    textChatTab.classList.add("bg-gray-200", "text-gray-800");

    // Initialize webcam (optional)
    await initWebcam();

    // Auto-greet the user by name
    const greeting = `Hello ${userName}! I'm Habit-Tracker BOT, your friendly AI assistant. Press and hold the microphone button to start talking with me!`;
    addMessage(voiceChatMessages, "bot", greeting);
    speak(greeting);
});

// Text chat
const textSendBtn = document.getElementById("textSendBtn");
const textUserInput = document.getElementById("textUserInput");
const textChatMessages = document.getElementById("textChatMessages");

textSendBtn.addEventListener("click", () => {
    const msg = textUserInput.value.trim();
    if (msg) {
        chatWithAI(textChatMessages, msg);
        textUserInput.value = "";
    }
});

textUserInput.addEventListener("keypress", (e) => {
    if (e.key === "Enter") {
        const msg = textUserInput.value.trim();
        if (msg) {
            chatWithAI(textChatMessages, msg);
            textUserInput.value = "";
        }
    }
});

// Voice chat
const voiceBtn = document.getElementById("voiceBtn");
const voiceChatMessages = document.getElementById("voiceChatMessages");
const voiceStatus = document.getElementById("voiceStatus");
const emotionDisplay = document.getElementById("emotionDisplay");
const emotionText = document.getElementById("emotionText");
const speechVisualization = document.getElementById("speechVisualization");
const camVideo = document.getElementById("userCam");

let recognizing = false;
let recordedTranscript = "";
let selectedVoice = null;

// Speech recognition
const recognition =
    "webkitSpeechRecognition" in window ? new webkitSpeechRecognition() : null;

// Webcam
async function initWebcam() {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
        camVideo.srcObject = stream;
        document.getElementById("webcamContainer")?.classList.remove("hidden");
        return true;
    } catch (error) {
        console.warn("No webcam access:", error);
        return false;
    }
}

// Speech visualization
function addSpeechWord(word) {
    const wordElement = document.createElement("div");
    wordElement.classList.add(
        "absolute",
        "text-xl",
        "font-bold",
        "text-black",
        "opacity-10",
        "whitespace-nowrap",
        "select-none"
    );
    wordElement.textContent = word;

    const left = Math.random() * 80 + 10;
    const rotation = Math.random() * 20 - 10;
    const duration = Math.random() * 4 + 6;

    wordElement.style.left = `${left}%`;
    wordElement.style.transform = `rotate(${rotation}deg)`;
    wordElement.style.animation = `wordFloat ${duration}s linear forwards`;

    speechVisualization.appendChild(wordElement);

    setTimeout(() => {
        wordElement.remove();
    }, duration * 1000);
}

function processSpeechForVisualization(transcript) {
    const words = transcript.split(/\s+/).filter((word) => word.length > 2);
    words.forEach(addSpeechWord);
}

// Voice synthesis
const voiceSelect = document.getElementById("voiceSelect");

function populateVoices() {
    const voices = speechSynthesis.getVoices();
    voiceSelect.innerHTML = voices
        .map((v) => `<option value="${v.name}">${v.name} (${v.lang})</option>`)
        .join("");

    if (!selectedVoice && voices.length > 0) {
        selectedVoice =
            voices.find((v) => v.name.toLowerCase().includes("female")) || voices[0];
        voiceSelect.value = selectedVoice.name;
    }
}

speechSynthesis.onvoiceschanged = populateVoices;
populateVoices();

voiceSelect.addEventListener("change", () => {
    selectedVoice = speechSynthesis.getVoices().find((v) => v.name === voiceSelect.value);
});

// Recognition setup
if (recognition) {
    recognition.lang = "en-US";
    recognition.continuous = true;
    recognition.interimResults = true;

    recognition.onstart = () => {
        recognizing = true;
        recordedTranscript = "";
        voiceStatus.innerHTML =
            '<i class="fas fa-microphone-alt text-black mr-1"></i> Listening (hold R)...';
        voiceBtn.innerHTML =
            '<i class="fas fa-stop-circle"></i> <span class="font-medium">Listening...</span>';
        voiceBtn.classList.add("bg-red-600", "hover:bg-red-700");
        speechVisualization.innerHTML = "";
    };

    recognition.onend = async () => {
        recognizing = false;
        voiceStatus.innerHTML =
            '<i class="fas fa-info-circle mr-1"></i> Hold R to talk again.';
        voiceBtn.innerHTML =
            '<i class="fas fa-microphone"></i> <span class="font-medium">Start Talking</span>';
        voiceBtn.classList.remove("bg-red-600", "hover:bg-red-700");

        if (recordedTranscript.trim() !== "") {
            await chatWithAI(voiceChatMessages, recordedTranscript, true, true);
        }
    };

    recognition.onresult = (event) => {
        let interimTranscript = "";
        for (let i = event.resultIndex; i < event.results.length; i++) {
            const transcript = event.results[i][0].transcript;
            if (event.results[i].isFinal) {
                recordedTranscript += transcript + " ";
                processSpeechForVisualization(transcript);
            } else {
                interimTranscript += transcript;
                processSpeechForVisualization(interimTranscript);
            }
        }
    };
}

// Voice button & keyboard
voiceBtn.addEventListener("mousedown", () => recognition?.start());
voiceBtn.addEventListener("mouseup", () => recognition?.stop());

document.addEventListener("keydown", (e) => {
    if (e.key.toLowerCase() === "r" && !recognizing) {
        e.preventDefault();
        recognition?.start();
    }
});

document.addEventListener("keyup", (e) => {
    if (e.key.toLowerCase() === "r" && recognizing) {
        recognition?.stop();
    }
});

// Chat functions
function addMessage(target, sender, text, hide = false) {
    if (hide) return;

    const msgDiv = document.createElement("div");
    msgDiv.classList.add(sender === "bot" ? "justify-start" : "justify-end", "flex");

    const bubble = document.createElement("div");
    bubble.classList.add(
        "max-w-xs", "md:max-w-md", "px-4", "py-3", "rounded-2xl", "break-words", "shadow-sm"
    );

    if (sender === "bot") {
        bubble.classList.add("bg-gray-100", "text-black", "rounded-bl-none", "border", "border-gray-300");
        bubble.innerHTML = `<div class="flex items-center gap-2 mb-1"><i class="fas fa-robot text-black"></i><span class="text-xs font-semibold">NETDEVBOT</span></div>${text}`;
    } else {
        bubble.classList.add("bg-black", "text-white", "rounded-tr-none");
        bubble.innerHTML = `<div class="flex items-center gap-2 mb-1 justify-end"><span class="text-xs font-semibold text-gray-300">You</span><i class="fas fa-user text-gray-300"></i></div>${text}`;
    }

    msgDiv.appendChild(bubble);
    target.appendChild(msgDiv);
    target.scrollTop = target.scrollHeight;
}

async function chatWithAI(target, message, speakBack = false, hideText = false) {
    addMessage(target, "user", message, hideText);
    const temp = !hideText ? addMessage(target, "bot", "Habit-Tracker BOT is thinking...") : null;

    if (DEMO_MODE) {
        setTimeout(() => {
            if (temp) {
                temp.querySelector("div:last-child").textContent =
                    "This is a simulated response. To enable real AI responses, please add your OpenAI API key.";
            }
            if (speakBack) speak("This is a simulated response. To enable real AI responses, please add your OpenAI API key.");
        }, 1000);
        return;
    }

    try {
        const res = await fetch("https://api.openai.com/v1/chat/completions", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                Authorization: `Bearer ${API_KEY}`,
            },
            body: JSON.stringify({
                model: "gpt-3.5-turbo",
                messages: [
                    { role: "system", content: "You are Habit-Tracker BOT, a friendly AI assistant." },
                    { role: "user", content: message },
                ],
                max_tokens: 250,
            }),
        });

        const data = await res.json();
        const botReply = data?.choices?.[0]?.message?.content?.trim() || "Sorry, I didn't get that. Please try again.";

        if (temp) temp.querySelector("div:last-child").textContent = botReply;
        if (speakBack) speak(botReply);
    } catch (e) {
        if (temp) temp.querySelector("div:last-child").innerHTML =
            '<i class="fas fa-exclamation-triangle mr-1"></i> Network or API error! Please check your connection.';
    }
}

// Speak function
function speak(text) {
    if (!("speechSynthesis" in window)) return;

    const utter = new SpeechSynthesisUtterance(text);
    utter.rate = 1;
    utter.pitch = 1.05;
    if (selectedVoice) utter.voice = selectedVoice;

    speechSynthesis.speak(utter);
}

// Init on page load
window.addEventListener("DOMContentLoaded", () => {
    addMessage(textChatMessages, "bot", "Hello! I'm Habit-Tracker BOT, your friendly AI assistant. How can I help you today?");
    addMessage(voiceChatMessages, "bot", "Hello! I'm Habit-Tracker BOT, your friendly AI assistant. Press and hold the microphone button to start talking with me!");
});

// Word float animation
const style = document.createElement("style");
style.textContent = `
@keyframes wordFloat {
    0% { transform: translateY(100vh) rotate(0deg); opacity: 0; }
    10% { opacity: 0.1; }
    90% { opacity: 0.08; }
    100% { transform: translateY(-100px) rotate(10deg); opacity: 0; }
}`;
document.head.appendChild(style);

