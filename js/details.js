       document.addEventListener('DOMContentLoaded', function() {
            // Gallery thumbnails functionality
            const mainImage = document.getElementById('main-image');
            const thumbnails = document.querySelectorAll('.thumbnail');
            
            thumbnails.forEach(thumb => {
                thumb.addEventListener('click', function() {
                    // Update main image
                    mainImage.src = this.dataset.src;
                    
                    // Update active thumbnail
                    thumbnails.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                });
            });
            
            // Like button functionality
            const galleryLike = document.getElementById('gallery-like');
            galleryLike.addEventListener('click', function() {
                this.classList.toggle('active');
            });
            
            // Similar cars like button functionality
            const likeButtons = document.querySelectorAll('.car-like');
            likeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    button.classList.toggle('active');
                });
            });
            
            // Date inputs to calculate total
            const dateInputs = document.querySelectorAll('.date-input');
            dateInputs.forEach(input => {
                input.addEventListener('change', updateBookingSummary);
            });
            
            function updateBookingSummary() {
                // This is a simplified implementation
                // In a real app, you would calculate the actual dates difference
                const dailyRate = 68;
                const days = 3; // Default to 3 days
                const serviceFee = 20;
                const insurance = 15;
                
                const subtotal = dailyRate * days;
                const total = subtotal + serviceFee + insurance;
                
                const summaryItems = document.querySelectorAll('.summary-item');
                summaryItems[0].innerHTML = `<span>$${dailyRate} x ${days} days</span><span>$${subtotal}</span>`;
                summaryItems[3].innerHTML = `<span>Total</span><span>$${total}</span>`;
            }
            
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