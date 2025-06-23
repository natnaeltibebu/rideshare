// Logout function
function logout() {
    alert('Logout functionality would be implemented here');
}

// FAQ, mobile menu, and car functionality
document.addEventListener('DOMContentLoaded', function() {
    // Addis Ababa locations for autocomplete
    const addisLocations = [
        'Bole', 'Kazanchis', 'Piassa', 'Merkato', 'Mexico Square', 'Megenagna', 
        'Sarbet', 'Gerji', 'Ayat', 'Jemo', 'CMC', 'Summit', 'Lebu', 'Kaliti', 
        'Shiro Meda', 'Arat Kilo', 'Lideta', 'Kirkos', 'Meskel Square', 'Sidist Kilo', 
        'Lamberet', 'Hayat', 'Haile Garment', 'Wello Sefer', 'Kera', 'Gofa Condominium',
        'Bole Airport', 'Bole Bulbula', 'Yeka Abado', 'Haya Hulet', 'Kolfe', 'Tor Hailoch',
        'Addis Ababa University', 'Gurdshola', 'Tulu Dimtu', 'Kality', 'Alem Bank'
    ];

    // Location autocomplete functionality
    const homeLocationInput = document.getElementById('home-location');
    const homeLocationResults = document.getElementById('home-location-results');
    
    if (homeLocationInput && homeLocationResults) {
        homeLocationInput.addEventListener('input', function() {
            const value = this.value.toLowerCase();
            homeLocationResults.innerHTML = '';
            
            if (value.length < 2) {
                homeLocationResults.classList.remove('active');
                return;
            }
            
            const filteredLocations = addisLocations.filter(loc => 
                loc.toLowerCase().includes(value)
            );
            
            if (filteredLocations.length > 0) {
                filteredLocations.forEach(location => {
                    const item = document.createElement('div');
                    item.className = 'autocomplete-item';
                    item.textContent = location + ', Addis Ababa';
                    
                    item.addEventListener('click', function() {
                        homeLocationInput.value = this.textContent;
                        homeLocationResults.classList.remove('active');
                    });
                    
                    homeLocationResults.appendChild(item);
                });
                
                homeLocationResults.classList.add('active');
            } else {
                homeLocationResults.classList.remove('active');
            }
        });
        
        // Close location autocomplete when clicking outside
        document.addEventListener('click', function(e) {
            if (!homeLocationInput.contains(e.target) && !homeLocationResults.contains(e.target)) {
                homeLocationResults.classList.remove('active');
            }
        });
    }

    // FAQ functionality
    const faqItems = document.querySelectorAll('.faq-item');
    
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        
        question.addEventListener('click', () => {
            // Close all other items
            faqItems.forEach(otherItem => {
                if (otherItem !== item) {
                    otherItem.classList.remove('active');
                }
            });
            
            // Toggle current item
            item.classList.toggle('active');
        });
    });
    
    // Mobile menu functionality - Fixed version
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const navMenu = document.querySelector('.nav-menu');
    
    if (mobileMenuBtn && navMenu) {
        mobileMenuBtn.addEventListener('click', (e) => {
            e.preventDefault();
            navMenu.classList.toggle('mobile-open');
        });
        
        // Handle window resize to reset mobile menu state
        window.addEventListener('resize', () => {
            if (window.innerWidth > 767) {
                navMenu.classList.remove('mobile-open');
            }
        });
    }

    // Car like functionality
    const likeButtons = document.querySelectorAll('.car-like');
    
    likeButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.stopPropagation(); // Prevent card click
            this.classList.toggle('active');
        });
    });

    // Make car cards clickable
    const carCards = document.querySelectorAll('.car-card');
    
    carCards.forEach(card => {
        card.addEventListener('click', function(e) {
            // Don't trigger card click if clicking on like button
            if (e.target.closest('.car-like')) {
                return;
            }
            
            const carId = this.dataset.carId;
            if (carId) {
                alert(`Would navigate to car details page for car ID: ${carId}`);
            }
        });
    });

    // Filter functionality
    const filterButtons = document.querySelectorAll('.filter-btn');
    const carCardsForFilter = document.querySelectorAll('.car-card');

    filterButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Remove active class from all buttons
            filterButtons.forEach(btn => btn.classList.remove('active'));
            // Add active class to clicked button
            button.classList.add('active');

            const filter = button.dataset.filter;

            carCardsForFilter.forEach(card => {
                if (filter === 'all') {
                    card.style.display = 'flex';
                } else if (filter === 'electric' && card.dataset.fuel === 'electric') {
                    card.style.display = 'flex';
                } else if (filter === 'suv' && card.dataset.type === 'suv') {
                    card.style.display = 'flex';
                } else if (filter === 'sedan' && (card.dataset.type === 'sedan' || card.querySelector('.car-title').textContent.toLowerCase().includes('sedan'))) {
                    card.style.display = 'flex';
                } else if (filter === 'luxury' && (card.dataset.type === 'luxury' || card.querySelector('.car-title').textContent.toLowerCase().includes('bmw') || card.querySelector('.car-title').textContent.toLowerCase().includes('mercedes') || card.querySelector('.car-title').textContent.toLowerCase().includes('audi'))) {
                    card.style.display = 'flex';
                } else if (filter === 'economy' && (card.dataset.type === 'economy' || card.querySelector('.car-title').textContent.toLowerCase().includes('honda') || card.querySelector('.car-title').textContent.toLowerCase().includes('toyota') || card.querySelector('.car-title').textContent.toLowerCase().includes('corolla'))) {
                    card.style.display = 'flex';
                } else if (filter === 'convertible' && card.dataset.type === 'convertible') {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });

    // Date validation for search form
    const pickupDateInput = document.querySelector('input[name="pickup_date"]');
    const returnDateInput = document.querySelector('input[name="return_date"]');

    if (pickupDateInput && returnDateInput) {
        pickupDateInput.addEventListener('change', function() {
            returnDateInput.min = this.value;
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
        
        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!avatarDropdown.contains(e.target)) {
                avatarDropdown.classList.remove('active');
            }
        });
    }
});
