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
    const bookingForm = document.querySelector('.booking-form');
    if (bookingForm) {
        bookingForm.addEventListener('submit', handleBookingSubmission);
    }

    async function handleBookingSubmission(e) {
        e.preventDefault(); // Prevent default form submission
        
        const formData = new FormData(e.target);
        const bookingData = {
            car_id: formData.get('car_id'),
            start_date: formData.get('start_date'),
            end_date: formData.get('end_date'),
            pickup_location: formData.get('pickup_location'),
            notes: formData.get('notes')
        };
        
        // Show loading state
        const submitBtn = e.target.querySelector('.book-now-btn');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Booking...';
        submitBtn.disabled = true;
        
        try {
            const response = await fetch(window.BASE_URL + '/api/bookings.php?action=create', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(bookingData)
            });
            
            const result = await response.json();
            
            if (result.status === 'success') {
                showBookingSuccess(result);
            } else {
                showBookingError(result.message);
            }
            
        } catch (error) {
            console.error('Booking error:', error);
            showBookingError('Failed to create booking. Please try again.');
        } finally {
            // Restore button
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        }
    }

    function showBookingSuccess(result) {
        // Get car name from the page
        const carName = document.querySelector('.car-name').textContent.trim();
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        
        // Create success message
        const successDiv = document.createElement('div');
        successDiv.className = 'alert alert-success';
        successDiv.innerHTML = `Booking confirmed for ${carName}, ${startDate}--${endDate}.`;
        
        // Insert after breadcrumb (below navbar)
        const breadcrumb = document.querySelector('.breadcrumb');
        breadcrumb.parentNode.insertBefore(successDiv, breadcrumb.nextSibling);
        
        // Scroll to alert
        successDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
        
        // Reset form
        document.querySelector('.booking-form').reset();
        updateBookingSummary(); // Recalculate default pricing
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (successDiv.parentNode) {
                successDiv.parentNode.removeChild(successDiv);
            }
        }, 5000);
    }

    function showBookingError(message) {
        // Create error message
        const errorDiv = document.createElement('div');
        errorDiv.className = 'alert alert-error';
        errorDiv.innerHTML = `Booking failed: ${message}`;
        
        // Insert after breadcrumb (below navbar)
        const breadcrumb = document.querySelector('.breadcrumb');
        breadcrumb.parentNode.insertBefore(errorDiv, breadcrumb.nextSibling);
        
        // Scroll to alert
        errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (errorDiv.parentNode) {
                errorDiv.parentNode.removeChild(errorDiv);
            }
        }, 5000);
    }

    // Real-time availability checking
    let availabilityTimeout;
    function checkAvailability() {
        const carId = document.querySelector('input[name="car_id"]').value;
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        
        if (!startDate || !endDate || !carId) return;
        
        // Validate that end date is after start date
        if (new Date(endDate) <= new Date(startDate)) {
            const submitBtn = document.querySelector('.book-now-btn');
            const availabilityMsg = document.getElementById('availability-message') || createAvailabilityMessage();
            availabilityMsg.className = 'availability-message unavailable';
            availabilityMsg.textContent = '❌ End date must be after start date';
            submitBtn.disabled = true;
            return;
        }
        
        // Clear previous timeout
        clearTimeout(availabilityTimeout);
        
        // Check availability after 500ms delay (debounce)
        availabilityTimeout = setTimeout(async () => {
            try {
                const response = await fetch(
                    `${window.BASE_URL}/api/cars.php?action=check-availability&car_id=${carId}&start_date=${startDate}&end_date=${endDate}`
                );
                
                const result = await response.json();
                
                const submitBtn = document.querySelector('.book-now-btn');
                const availabilityMsg = document.getElementById('availability-message') || createAvailabilityMessage();
                
                if (result.status === 'success') {
                    if (result.data.available) {
                        availabilityMsg.className = 'availability-message available';
                        availabilityMsg.textContent = '✅ Car is available for these dates';
                        submitBtn.disabled = false;
                    } else {
                        availabilityMsg.className = 'availability-message unavailable';
                        availabilityMsg.textContent = '❌ Car is not available for these dates';
                        submitBtn.disabled = true;
                    }
                } else {
                    availabilityMsg.className = 'availability-message unavailable';
                    availabilityMsg.textContent = '❌ ' + (result.message || 'Unable to check availability');
                    submitBtn.disabled = true;
                }
            } catch (error) {
                console.error('Availability check failed:', error);
                const submitBtn = document.querySelector('.book-now-btn');
                const availabilityMsg = document.getElementById('availability-message') || createAvailabilityMessage();
                availabilityMsg.className = 'availability-message unavailable';
                availabilityMsg.textContent = '❌ Unable to check availability';
                submitBtn.disabled = true;
            }
        }, 500);
    }

    function createAvailabilityMessage() {
        const msg = document.createElement('div');
        msg.id = 'availability-message';
        msg.className = 'availability-message';
        
        // Insert before the booking summary
        const bookingSummary = document.querySelector('.booking-summary');
        bookingSummary.parentNode.insertBefore(msg, bookingSummary);
        
        return msg;
    }

    const { dailyRate, serviceFee, insurance } = window.CAR_DATA;
    
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
    if (galleryLike) {
        galleryLike.addEventListener('click', function() {
            this.classList.toggle('active');
        });
    }
    
    // Similar cars like button functionality
    const likeButtons = document.querySelectorAll('.car-like');
    likeButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            button.classList.toggle('active');
        });
    });
    
    // Date inputs to calculate total
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    
    function updateBookingSummary() {
        const startDate = startDateInput.value;
        const endDate = endDateInput.value;
        
        if (startDate && endDate) {
            const start = new Date(startDate);
            const end = new Date(endDate);
            const timeDiff = end.getTime() - start.getTime();
            const days = Math.ceil(timeDiff / (1000 * 3600 * 24)); // Fixed: removed +1 for correct day calculation
            
            if (days > 0) {
                const subtotal = dailyRate * days;
                const total = subtotal + serviceFee + insurance;
                
                // Update display
                document.getElementById('price-calculation').textContent = '$' + dailyRate.toFixed(2) + ' x ' + days + ' days';
                document.getElementById('subtotal-amount').textContent = '$' + subtotal.toFixed(2);
                document.getElementById('total-amount').textContent = '$' + total.toFixed(2);
                document.getElementById('hidden_total_amount').value = total.toFixed(2);
                
                // Check availability when dates change
                checkAvailability();
            }
        }
    }
    
    // Set min date for end date based on start date
    if (startDateInput) {
        startDateInput.addEventListener('change', function() {
            if (endDateInput) {
                endDateInput.min = this.value;
            }
            updateBookingSummary();
        });
    }
    
    if (endDateInput) {
        endDateInput.addEventListener('change', updateBookingSummary);
    }
    
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
});