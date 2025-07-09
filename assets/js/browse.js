// Logout function
function logout() {
    fetch(window.BASE_URL + '/api/auth.php?action=logout', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        window.location.href = window.BASE_URL + '/';
    })
    .catch(error => {
        console.error('Error:', error);
        window.location.href = window.BASE_URL + '/';
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Filters toggle
    const filterToggle = document.getElementById('filter-toggle-btn');
    const filtersContainer = document.getElementById('filters-container');
    
    if (filterToggle && filtersContainer) {
        filterToggle.addEventListener('click', function() {
            filtersContainer.classList.toggle('active');
        });
    }
    
    // Car like functionality
    const likeButtons = document.querySelectorAll('.car-like');
    
    likeButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.stopPropagation();
            this.classList.toggle('active');
        });
    });
    
    // Make car cards clickable
    const carCards = document.querySelectorAll('.car-card');
    
    carCards.forEach(card => {
        card.addEventListener('click', function(e) {
            if (e.target.closest('.car-like')) {
                return;
            }
            
            const carId = this.dataset.carId;
            if (carId) {
                window.location.href = window.BASE_URL + '/details?id=' + carId;
            }
        });
    });
    
    // Mobile menu functionality
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const navMenu = document.querySelector('.nav-menu');
    
    if (mobileMenuBtn && navMenu) {
        mobileMenuBtn.addEventListener('click', (e) => {
            e.preventDefault();
            navMenu.classList.toggle('mobile-open');
        });
        
        window.addEventListener('resize', () => {
            if (window.innerWidth > 767) {
                navMenu.classList.remove('mobile-open');
            }
        });
    }
    
    // Avatar dropdown functionality
    const avatarBtn = document.querySelector('.avatar-btn');
    const avatarDropdown = document.querySelector('.avatar-dropdown');
    
    if (avatarBtn && avatarDropdown) {
        avatarBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            avatarDropdown.classList.toggle('active');
        });
        
        document.addEventListener('click', (e) => {
            if (!avatarDropdown.contains(e.target)) {
                avatarDropdown.classList.remove('active');
            }
        });
    }
    
    // Reset filters functionality
    const resetBtn = document.querySelector('.reset-btn');
    if (resetBtn) {
        resetBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = window.BASE_URL + '/browse';
        });
    }
});