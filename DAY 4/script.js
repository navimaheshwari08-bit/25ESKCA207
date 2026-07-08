// Selecting elements out of the DOM smoothly
const academyForm = document.getElementById('academyForm');
const userNameInput = document.getElementById('userName');
const userEmailInput = document.getElementById('userEmail');
const feedbackBox = document.getElementById('form-feedback');

// Handle form submission event
academyForm.addEventListener('submit', function(event) {
    // Stop the page from reloading completely
    event.preventDefault();

    // Clean up inputs to prevent blank space entries
    const nameValue = userNameInput.value.trim();
    const emailValue = userEmailInput.value.trim();

    // Reset our custom feedback element look
    feedbackBox.style.display = "none";
    feedbackBox.className = "feedback-box";

    // Requirement 2b & 3: Custom Field Validation Lookups
    if (nameValue.length < 3) {
        showFeedback("Hey there! Please make sure your name is at least 3 characters long.", "error");
        return;
    }

    if (!emailValue.includes('@') || !emailValue.includes('.')) {
        showFeedback("Oops! That looks like an incomplete email format. Let's fix that.", "error");
        return;
    }

    // Requirement 2c: Dynamic personalized greeting execution
    showFeedback(`Welcome to the future of tech, ${nameValue}! Your request is confirmed. Check your inbox soon!`, "success");
    
    // Completely clear the inputs out upon a successful run
    academyForm.reset();
});

// Helper utility to update text UI dynamically
function showFeedback(message, type) {
    feedbackBox.innerText = message;
    feedbackBox.style.display = "block";
    
    if (type === "success") {
        feedbackBox.classList.add("feedback-success");
    } else {
        feedbackBox.classList.add("feedback-error");
    }
}