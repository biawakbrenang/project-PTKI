/**
 * ECO-LEARNING - Global client-side interactions and utility helpers
 */

// 1. Live clock handler (used in header.php)
function updateClock() {
    const element = document.getElementById('liveClock');
    if (element) {
        const now = new Date();
        const hrs = String(now.getHours()).padStart(2, '0');
        const mins = String(now.getMinutes()).padStart(2, '0');
        const secs = String(now.getSeconds()).padStart(2, '0');
        element.textContent = `${hrs}:${mins}:${secs} WIB`;
    }
}

// Start clock loop if element exists or globally
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('liveClock')) {
        setInterval(updateClock, 1000);
        updateClock();
    }
});

// 2. Demo bypass form filler (used in login.php)
function fillDemo(username, password, captchaValue) {
    const usernameInput = document.querySelector('input[name="username"]');
    const passwordInput = document.querySelector('input[name="password"]');
    const captchaInput = document.querySelector('input[name="captcha"]');

    if (usernameInput) usernameInput.value = username;
    if (passwordInput) passwordInput.value = password;
    if (captchaInput) captchaInput.value = captchaValue;
}

// 3. Lecturer Homework Evaluation Modal (used in koreksi.php)
function toggleGradeModal(id, name, score, comment) {
    const modalSubId = document.getElementById('modalSubId');
    const gradeStudentName = document.getElementById('gradeStudentName');
    const modalScore = document.getElementById('modalScore');
    const modalComment = document.getElementById('modalComment');
    const gradeModal = document.getElementById('gradeModal');

    if (modalSubId) modalSubId.value = id;
    if (gradeStudentName) gradeStudentName.textContent = 'Mahasiswa: ' + name;
    if (modalScore) modalScore.value = score !== null && score !== 'null' && score !== '' ? score : '';
    if (modalComment) modalComment.value = comment;
    if (gradeModal) gradeModal.classList.remove('hidden');
}

function closeGradeModal() {
    const gradeModal = document.getElementById('gradeModal');
    if (gradeModal) gradeModal.classList.add('hidden');
}

// 4. Student Submit Homework Modal (used in tugas.php)
function openSubmitModal(id, title) {
    const modalTaskId = document.getElementById('modalTaskId');
    const modalTaskName = document.getElementById('modalTaskName');
    const submitModal = document.getElementById('submitModal');

    if (modalTaskId) modalTaskId.value = id;
    if (modalTaskName) modalTaskName.textContent = 'Tugas: ' + title;
    if (submitModal) submitModal.classList.remove('hidden');
}

function closeSubmitModal() {
    const submitModal = document.getElementById('submitModal');
    if (submitModal) submitModal.classList.add('hidden');
}
