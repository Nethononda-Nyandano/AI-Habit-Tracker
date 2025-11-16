document.addEventListener("DOMContentLoaded", () => {
    const API_URL = "habitAPI.php";
    const AI_URL = "../api/v1/aiSuggestion.php"; 
    const userId = document.querySelector("#userId").value;

    const habitsSection = document.querySelector("#habits");
    const viewSection = document.getElementById("view-habit");
    const editSection = document.getElementById("edit-habit");
    const deleteModal = document.getElementById("deleteModal");
    const cancelDelete = document.getElementById("cancelDelete");
    const confirmDelete = document.getElementById("confirmDelete");
    const habitForm = document.getElementById("habit-form");

    let habitToDelete = null;
    const synth = window.speechSynthesis;
    let viewUtterance = null;
    let editUtterance = null;

    /* ---------- GLOBAL SECTION HANDLER ---------- */
    window.showSection = function(sectionId) {
        document.querySelectorAll(".content").forEach(sec => sec.classList.add("hidden"));
        const section = document.getElementById(sectionId);
        if(section) section.classList.remove("hidden");
        localStorage.setItem("lastSection", sectionId);
    };

    const lastSection = localStorage.getItem("lastSection") || "home";
    if(lastSection) document.getElementById(lastSection)?.classList.remove("hidden");

    /* ---------- CRUD OPERATIONS ---------- */
    async function getHabits() {
        try {
            const response = await fetch(`${API_URL}?user_id=${userId}`);
            const habits = await response.json();
            displayHabits(habits);
            restoreHabitState();
        } catch(error) {
            console.error("Error fetching habits:", error);
        }
    }

    async function deleteHabit(habitId) {
        try {
            const response = await fetch(`${API_URL}?user_id=${userId}&habit_id=${habitId}`, { method: "DELETE" });
            if(response.ok) getHabits();
        } catch(error) {
            console.error("Error deleting habit:", error);
        }
    }

    async function saveHabit(payload, habitId = null) {
        try {
            const method = habitId ? "PUT" : "POST";
            if(habitId) payload.habitId = habitId;
            await fetch(API_URL, {
                method,
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload)
            });
            getHabits();
            showSection("habits");
            habitForm.reset();
            delete habitForm.dataset.habitId;
        } catch(err) { console.error(err); }
    }

    /* ---------- DISPLAY HABITS ---------- */
    function displayHabits(habits) {
        if(!habitsSection) return;

        if(!habits || habits.length === 0) {
            habitsSection.innerHTML = `
                <div class="flex flex-col items-center justify-center h-64 text-black bg-white">
                    <h3 class="text-2xl font-medium mb-6 text-start">Habits</h3>
                    <p class="text-gray-700 text-lg mb-4">You don't have any habits yet</p>
                    <button onclick="showSection('add-habit')"
                        class="bg-black hover:bg-gray-800 text-white px-4 py-2 rounded-md flex items-center gap-2">
                        Add Habit
                    </button>
                </div>`;
            return;
        }

        habitsSection.innerHTML = `
            <div class="justify-between flex">
                <h3 class="text-2xl font-medium mb-6 text-start">Habits</h3>
                <button onclick="showSection('add-habit')"
                    class="bg-black hover:bg-gray-800 text-white px-4 py-2 rounded-md flex items-center gap-2 mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Add Habit
                </button>
            </div>
            <div id="habits-list" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4"></div>`;

        const habitsList = document.querySelector("#habits-list");

        habits.forEach(habit => {
            const habitElement = document.createElement("div");
            habitElement.className = "habit-card bg-white border border-black rounded-md p-5 flex flex-col justify-between transition hover:shadow-xl relative";
            habitElement.dataset.habit = JSON.stringify(habit);

            habitElement.innerHTML = `
                <div class="pin absolute -top-3 -right-3 w-6 h-6 rounded-full bg-red-600 shadow-lg transform rotate-60"></div>
                <div>
                    <h3 class="text-xl font-semibold text-black mb-2">${habit.habit_name}</h3>
                    <p class="text-sm text-gray-700 mb-4">${habit.description}</p>
                    <p class="text-xs text-gray-500 italic">
                        Frequency: <span class="font-semibold text-black">${habit.frequency}</span>
                    </p>
                </div>
                <div class="flex justify-between items-center mt-4 border-t border-gray-400 pt-3">
                    <p class="text-xs text-gray-500 italic">Created</p>
                    <div class="flex items-center gap-3">
                        <!-- VIEW BUTTON -->
                        <button class="view-btn text-blue-600 hover:text-blue-800" title="View Habit">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </button>

                        <!-- PROGRESS BUTTON -->
                        <button class="progress-btn text-indigo-600 hover:text-indigo-800" title="Progress">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <rect x="3" y="3" width="4" height="18" rx="1"></rect>
                                <rect x="9" y="8" width="4" height="13" rx="1"></rect>
                                <rect x="15" y="13" width="4" height="8" rx="1"></rect>
                            </svg>
                        </button>

                        <!-- EDIT BUTTON -->
                        <button class="edit-btn text-green-600 hover:text-green-800" title="Edit Habit">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 3.487a2.25 2.25 0 013.182 3.182l-10.5 10.5a2.25 2.25 0 01-1.064.59l-4.5 1.125a.75.75 0 01-.927-.927l1.125-4.5a2.25 2.25 0 01.59-1.064l10.5-10.5z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 7.5L16.5 4.5"/>
                            </svg>
                        </button>

                        <!-- DELETE BUTTON -->
                        <button class="delete-btn text-red-600 hover:text-red-800" title="Delete Habit">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                            </svg>
                        </button>
                    </div>
                </div>`;
            habitsList.appendChild(habitElement);
        });

        attachHabitEventListeners();
    }

    /* ---------- EVENT LISTENERS ---------- */
    function attachHabitEventListeners() {
        const viewButtons = document.querySelectorAll(".view-btn");
        const editButtons = document.querySelectorAll(".edit-btn");
        const deleteButtons = document.querySelectorAll(".delete-btn");

       viewButtons.forEach((btn) => {
            btn.addEventListener("click", async () => {
                const habitData = JSON.parse(btn.closest(".habit-card").dataset.habit);

                localStorage.setItem("lastSection", "view-habit");
                localStorage.setItem("lastHabitData", JSON.stringify(habitData));

                viewSection.innerHTML = `
                    <button onclick="showSection('habits')" 
                        class="bg-black hover:bg-gray-800 text-white px-4 py-2 rounded-md flex items-center gap-2 mb-4">
                        ← Back
                    </button>
                    <div class="grid md:grid-cols-2 grid-cols-1 gap-8 items-start p-6 bg-white text-black rounded-sm shadow-xl">
                        <div class="border border-black rounded-md p-6">
                            <h3 class="text-3xl font-bold mb-4">${habitData.habit_name}</h3>
                            <p class="mb-3">${habitData.description}</p>
                            <p class="text-sm italic mb-2">Frequency: ${habitData.frequency}</p>
                            <p class="text-xs text-gray-600">Created on: ${habitData.created_at || "N/A"}</p>
                        </div>
                        <div class="border border-black bg-gray-100 rounded-md p-6 flex flex-col justify-between">
                            <div>
                                <h4 class="text-2xl font-semibold mb-4 flex items-center gap-2">
                                    <img src="../assets/images/AI.png" alt="AI Icon" class="w-12 h-12">
                                    AI Suggestion
                                </h4>
                                <div id="aiSuggestions" class="text-gray-800 text-lg italic">
                                    <p>Loading personalized suggestion...</p>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-3 mt-6">
                                <button id="refreshSuggestion" class="bg-white text-black px-4 py-2 rounded-xl font-semibold hover:bg-gray-200 transition-all">Refresh</button>
                                <button id="readSuggestion" class="bg-green-500 text-white px-4 py-2 rounded-xl font-semibold hover:bg-green-600 transition-all">▶ Read</button>
                                <button id="pauseSuggestion" class="bg-red-500 text-white px-4 py-2 rounded-xl font-semibold hover:bg-red-600 transition-all">⏸ Pause</button>
                            </div>
                        </div>
                    </div>`;

                const aiBox = document.getElementById("aiSuggestions");
                const refreshBtn = document.getElementById("refreshSuggestion");
                const readBtn = document.getElementById("readSuggestion");
                const pauseBtn = document.getElementById("pauseSuggestion");

                async function getAISuggestion(habitName, frequency, description) {
                    aiBox.innerHTML = `<p class="italic text-gray-400">Thinking...</p>`;
                    try {
                        const res = await fetch(AI_URL, {
                            method: "POST",
                            headers: { "Content-Type": "application/json" },
                            body: JSON.stringify({ habitName, frequency, description }),
                        });
                        const data = await res.json();
                        const suggestion = data.suggestion || "No suggestion available.";
                        aiBox.innerHTML = `<p>${suggestion}</p>`;
                        readBtn.dataset.text = suggestion;
                        localStorage.setItem("lastAISuggestion", suggestion);
                    } catch (error) {
                        aiBox.innerHTML = `<p class="text-red-500">Couldn't fetch AI suggestion.</p>`;
                    }
                }

                const synth = window.speechSynthesis;
                let utterance = null;
                readBtn.addEventListener("click", () => {
                    const text = readBtn.dataset.text || aiBox.innerText;
                    if (!text) return;
                    if (synth.speaking) synth.cancel();
                    utterance = new SpeechSynthesisUtterance(text);
                    utterance.lang = "en-US";
                    utterance.rate = 1;
                    utterance.pitch = 1;
                    utterance.onstart = () => (readBtn.textContent = "🔊 Speaking...");
                    utterance.onend = () => (readBtn.textContent = "▶ Read");
                    synth.speak(utterance);
                });
                pauseBtn.addEventListener("click", () => {
                    if (synth.speaking && !synth.paused) synth.pause();
                    else if (synth.paused) synth.resume();
                });
                refreshBtn.addEventListener("click", () =>
                    getAISuggestion(habitData.habit_name, habitData.frequency, habitData.description)
                );

                const lastAISuggestion = localStorage.getItem("lastAISuggestion");
                if (lastAISuggestion) aiBox.innerHTML = `<p>${lastAISuggestion}</p>`;

                getAISuggestion(habitData.habit_name, habitData.frequency, habitData.description);
                showSection("view-habit");
            });
        });

        /* ---- EDIT ---- */
        editButtons.forEach(btn => {
    btn.addEventListener("click", () => {
        const habitData = JSON.parse(btn.closest(".habit-card").dataset.habit);
        showSection("edit-habit");

        // Prefill form fields
        habitForm.habitName.value = habitData.habit_name;
        habitForm.habitDescription.value = habitData.description;
        habitForm.habitFrequency.value = habitData.frequency;
        habitForm.dataset.habitId = habitData.habit_id;

        // AI Suggestion Controls
        const editAISuggestion = document.getElementById("editAISuggestions");
        const editReadBtn = document.getElementById("editRead");
        const editPauseBtn = document.getElementById("editPause");
        const editRefreshBtn = document.getElementById("editRefresh");
        const saveEditBtn = document.getElementById("saveEditHabit"); // ✅ Button to save changes

        // ========== AI Suggestion Function ==========
        async function getEditAISuggestion(name, freq, desc) {
            editAISuggestion.innerHTML = `<p class="italic text-gray-400">Thinking...</p>`;
            try {
                const res = await fetch(AI_URL, {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ habitName: name, frequency: freq, description: desc })
                });
                const data = await res.json();
                const suggestion = data.suggestion || "No suggestion available.";
                editAISuggestion.innerHTML = `<p>${suggestion}</p>`;
                editReadBtn.dataset.text = suggestion;
            } catch (e) {
                editAISuggestion.innerHTML = `<p class="text-red-500">Couldn't fetch AI suggestion.</p>`;
            }
        }

        // ========== AI Button Events ==========
        editRefreshBtn.addEventListener("click", () =>
            getEditAISuggestion(
                habitData.habit_name,
                habitData.frequency,
                habitData.description
            )
        );

        editReadBtn.addEventListener("click", () => {
            const text = editReadBtn.dataset.text || editAISuggestion.innerText;
            if (!text) return;
            if (synth.speaking) synth.cancel();
            editUtterance = new SpeechSynthesisUtterance(text);
            synth.speak(editUtterance);
        });

        editPauseBtn.addEventListener("click", () => {
            if (synth.speaking && !synth.paused) synth.pause();
            else if (synth.paused) synth.resume();
        });

        // ========== SAVE CHANGES ==========
        saveEditBtn.addEventListener("click", async (e) => {
            e.preventDefault();
            const habitId = habitForm.dataset.habitId;
            const updatedData = {
                habit_id: habitId,
                habit_name: habitForm.habitName.value.trim(),
                description: habitForm.habitDescription.value.trim(),
                frequency: habitForm.habitFrequency.value.trim(),
            };

            try {
                const res = await fetch("habit-edit.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(updatedData),
                });

                const result = await res.json();
                if (result.success) {
                    alert("✅ Habit updated successfully!");
                    showSection("dashboard"); // Go back to dashboard
                    loadHabits(); // Optional: refresh habit list
                } else {
                    alert("❌ Failed to update habit: " + (result.message || "Unknown error"));
                }
            } catch (error) {
                console.error("Error updating habit:", error);
                alert("⚠️ Something went wrong while saving changes.");
            }
        });

        // Initial AI suggestion
        getEditAISuggestion(habitData.habit_name, habitData.frequency, habitData.description);
    });
});


        /* ---- DELETE ---- */
        deleteButtons.forEach(btn => {
            btn.addEventListener("click", () => {
                habitToDelete = btn.closest(".habit-card");
                deleteModal.classList.remove("hidden");
            });
        });
    }

    /* ---------- DELETE MODAL ---------- */
    cancelDelete.addEventListener("click", () => {
        habitToDelete = null;
        deleteModal.classList.add("hidden");
    });

    confirmDelete.addEventListener("click", () => {
        if(!habitToDelete) return;
        const habitId = JSON.parse(habitToDelete.dataset.habit).habit_id;
        deleteHabit(habitId);
        deleteModal.classList.add("hidden");
    });

    /* ---------- FORM SUBMIT FOR ADD/EDIT ---------- */
    habitForm.addEventListener("submit", e => {
        e.preventDefault();
        const payload = {
            userId,
            habitName: habitForm.habitName.value,
            habitDescription: habitForm.habitDescription.value,
            habitFrequency: habitForm.habitFrequency.value
        };
        const habitId = habitForm.dataset.habitId || null;
        saveHabit(payload, habitId);
    });

    /* ---------- RESTORE LAST STATE ---------- */
    function restoreHabitState() {
        const lastSection = localStorage.getItem("lastSection");
        const lastHabitData = localStorage.getItem("lastHabitData");
        if(lastSection === "view-habit" && lastHabitData) {
            document.querySelectorAll(".view-btn")[0]?.click();
        }
    }

    /* ---------- INIT ---------- */
    getHabits();
});
