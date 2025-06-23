        document.addEventListener('DOMContentLoaded', function() {
            // Filters toggle
            const filterToggle = document.getElementById('filter-toggle-btn');
            const filtersContainer = document.getElementById('filters-container');
            
            filterToggle.addEventListener('click', function() {
                filtersContainer.classList.toggle('active');
            });
            
            // Sort dropdown
            const sortDropdown = document.getElementById('sort-dropdown');
            const sortOptions = document.querySelectorAll('.sort-option');
            
            sortDropdown.addEventListener('click', function() {
                sortDropdown.classList.toggle('active');
            });
            
            sortOptions.forEach(option => {
                option.addEventListener('click', function() {
                    // Remove active class from all options
                    sortOptions.forEach(opt => opt.classList.remove('active'));
                    
                    // Add active class to clicked option
                    this.classList.add('active');
                    
                    // Update the sort button text
                    const sortButton = document.querySelector('.sort-button');
                    sortButton.innerHTML = `Sort By: <strong>${this.textContent}</strong>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>`;
                    
                    // Close the dropdown
                    sortDropdown.classList.remove('active');
                });
            });
            
            // Close sort dropdown when clicking outside
            document.addEventListener('click', function(event) {
                if (!sortDropdown.contains(event.target)) {
                    sortDropdown.classList.remove('active');
                }
            });
            
            // Car like functionality
            const likeButtons = document.querySelectorAll('.car-like');
            
            likeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    button.classList.toggle('active');
                });
            });
            
            
            // Mobile menu
            const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
            const navMenu = document.querySelector('.nav-menu');
            
            mobileMenuBtn.addEventListener('click', function() {
                navMenu.style.display = navMenu.style.display === 'flex' ? 'none' : 'flex';
            });
        });
         // Avatar dropdown functionality
            const avatarBtn = document.querySelector('.avatar-btn');
            const avatarDropdown = document.querySelector('.avatar-dropdown');
            
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