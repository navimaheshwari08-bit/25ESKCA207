document.getElementById('registrationForm').addEventListener('submit', function(e) {
    // Prevent page reload
    e.preventDefault();

    // 1. Capture user inputs
    const fullName = document.getElementById('fullName').value;
    const email = document.getElementById('email').value;
    const gender = document.querySelector('name=["gender"]:checked')?.value || 'Not Specified';
    const course = document.getElementById('course').value;
    const address = document.getElementById('address').value;
    const photoInput = document.getElementById('photoUpload');

    // 2. Map captured input values to the Confirmation Card fields
    document.getElementById('confName').innerText = fullName;
    document.getElementById('confEmail').innerText = email;
    document.getElementById('confGender').innerText = gender;
    document.getElementById('confCourse').innerHTML = `<i class="fa-solid fa-graduation-cap me-2"></i>${course}`;
    document.getElementById('confAddress').innerText = address;

    // 3. Setup local Photo preview UI logic if a file exists
    if (photoInput.files && photoInput.files[0]) {
        const reader = new FileReader();
        reader.onload = function(event) {
            document.getElementById('confPhoto').src = event.target.result;
        };
        reader.readAsDataURL(photoInput.files[0]);
    } else {
        // Fallback fallback if no file is provided
        document.getElementById('confPhoto').src = "https://via.placeholder.com/150?text=Student";
    }

    // 4. Toggle visibility using Bootstrap's display utility classes
    document.getElementById('formSection').classList.add('d-none');
    document.getElementById('confirmationSection').classList.remove('d-none');
});

// "Register Another Student" back button utility
document.getElementById('backBtn').addEventListener('click', function() {
    // Reset form inputs
    document.getElementById('registrationForm').reset();
    
    // Switch views back
    document.getElementById('confirmationSection').classList.add('d-none');
    document.getElementById('formSection').classList.remove('d-none');
});