/**
 * app.js - API Directory Logic
 */

// --- CONFIGURATION ---
const API_URL = 'https://randomuser.me/api/?results=20&nat=us,gb,ca&seed=assignment'; 
// Fetches 20 results, limiting to English-speaking nationalities for cleaner data.
// The 'seed' ensures the same "random" users appear every time you refresh (good for testing).

// --- DOM ELEMENTS ---
const elements = {
    loading: document.getElementById('loading-state'),
    error: document.getElementById('error-state'),
    errorMsg: document.getElementById('error-message'),
    grid: document.getElementById('user-grid'),
    search: document.getElementById('search-box'),
    count: document.getElementById('member-count'),
    noResults: document.getElementById('no-results')
};

// --- APPLICATION STATE ---
let allUsers = []; // Stores the master list of fetched users

/**
 * Requirement: Working fetch() call with Error Handling
 * Main initialization function to retrieve data from the API
 */
async function initializeDirectory() {
    try {
        // 1. Show Loading, Hide Grid/Error
        showState('loading');

        // 2. Perform Fetch Request
        const response = await fetch(API_URL);

        // 3. Handle basic HTTP errors (e.g., 404 or 500)
        if (!response.ok) {
            throw new Error(`Profile API communication failure (Status: ${response.status})`);
        }

        // 4. Parse the JSON data
        const data = await response.json();
        allUsers = data.results; // RandomUser API wraps results in a 'results' array

        // 5. Successful: Render the initial grid
        renderUserGrid(allUsers);
        
        // 6. Enable the search box now that we have data
        elements.search.removeAttribute('disabled');
        showState('grid');

    } catch (error) {
        // Requirement: Error Handling implementation
        console.error("Critical API Error:", error);
        elements.errorMsg.textContent = `Technical Details: ${error.message}. Please try refreshing the directory.`;
        showState('error');
    }
}

/**
 * Requirement: Create a brand-new card UI/Bootstrap card grid
 * Takes an array of user objects and maps them into dynamic HTML cards
 */
function renderUserGrid(usersToRender) {
    // A. Clear existing grid contents and manage state
    elements.grid.innerHTML = '';
    
    // BONUS Requirement: Card Count Badge
    elements.count.textContent = usersToRender.length;

    // B. Handle Case: Search returned 0 matches
    if (usersToRender.length === 0) {
        elements.grid.classList.add('d-none');
        elements.noResults.classList.remove('d-none');
        return;
    }

    // C. Normal Case: Hide "No Results", Show Grid
    elements.noResults.classList.add('d-none');
    elements.grid.classList.remove('d-none');

    // D. Loop through users and generate Bootstrap Cards
    // Requirement: attractive CSS, clean ui, real people images
    usersToRender.forEach(user => {
        // Construct the full name
        const fullName = `${user.name.first} ${user.name.last}`;
        
        // Construct a clean location string
        const location = `${user.location.city}, ${user.location.country}`;

        // Create the card column HTML string
        const cardHtml = `
            <div class="col">
                <div class="card user-card shadow-sm">
                    <!-- Top design element (grey background wrapper) -->
                    <div class="card-img-wrapper">
                        <!-- Requirement: attractive CSS/real people images structure -->
                        <div class="avatar-circle">
                            <!-- 'user.picture.large' comes from the API -->
                            <img src="${user.picture.large}" class="avatar-img" alt="${fullName}">
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class="user-name text-capitalize">${fullName}</h5>
                        <!-- Simulating a job title for a clean UI aesthetic -->
                        <p class="user-title">Team Member</p>
                        
                        <!-- Contact/Location Info Section -->
                        <div class="user-info-section pt-3 border-top">
                            <p class="user-info-text">
                                <i class="bi bi-envelope-fill text-muted"></i>
                                <span class="text-truncate" title="${user.email}">${user.email}</span>
                            </p>
                            <p class="user-info-text text-capitalize">
                                <i class="bi bi-geo-alt-fill text-muted"></i>
                                ${location}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Inject the generated string efficiently into the grid container
        elements.grid.insertAdjacentHTML('beforeend', cardHtml);
    });
}

/**
 * BONUS Requirement: Search or Filter Functionality
 * Listens for typing in the search box and filters the cached user list
 */
elements.search.addEventListener('input', (event) => {
   
    const query = event.target.value.toLowerCase().trim();
    
    
    const filteredResults = allUsers.filter(user => {
        const firstName = user.name.first.toLowerCase();
        const lastName = user.name.last.toLowerCase();
        const email = user.email.toLowerCase();
        const country = user.location.country.toLowerCase();

        return firstName.includes(query) || 
               lastName.includes(query) || 
               email.includes(query) || 
               country.includes(query);
    });
    
    
    renderUserGrid(filteredResults);
});


function showState(state) {
    
    const states = [elements.loading, elements.error, elements.grid];
    
    
    states.forEach(el => el.classList.add('d-none'));

    
    switch(state) {
        case 'loading': elements.loading.classList.remove('d-none'); break;
        case 'error':   elements.error.classList.remove('d-none'); break;
        case 'grid':    elements.grid.classList.remove('d-none'); break;
    }
}


document.addEventListener('DOMContentLoaded', initializeDirectory);