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
            // Form multi-step functionality
            const prevBtn = document.getElementById('prev-step');
            const nextBtn = document.getElementById('next-step');
            const formSteps = document.querySelectorAll('.form-step');
            const progressSteps = document.querySelectorAll('.progress-step');
            const form = document.getElementById('listing-form');
            let currentStep = 0;
            
            // Car make and models data
            const carModels = {
                toyota: ['Camry', 'Corolla', 'RAV4', 'Highlander', 'Tacoma', 'Prius', 'Land Cruiser', 'Fortuner', 'Yaris'],
                honda: ['Accord', 'Civic', 'CR-V', 'Pilot', 'Odyssey', 'Fit', 'HR-V', 'City'],
                bmw: ['3 Series', '5 Series', 'X3', 'X5', '7 Series', 'i4', 'X1', 'X7', '4 Series'],
                mercedes: ['C-Class', 'E-Class', 'S-Class', 'GLC', 'GLE', 'EQS', 'A-Class', 'G-Class', 'GLS'],
                audi: ['A3', 'A4', 'A6', 'Q3', 'Q5', 'e-tron', 'Q7', 'A8', 'Q8'],
                tesla: ['Model 3', 'Model Y', 'Model S', 'Model X', 'Cybertruck'],
                ford: ['F-150', 'Mustang', 'Explorer', 'Escape', 'Bronco', 'Mach-E', 'Ranger', 'Focus'],
                chevrolet: ['Silverado', 'Equinox', 'Tahoe', 'Malibu', 'Suburban', 'Bolt', 'Trax', 'Traverse'],
                hyundai: ['Elantra', 'Sonata', 'Tucson', 'Santa Fe', 'Kona', 'Ioniq', 'Palisade', 'Venue'],
                kia: ['Forte', 'K5', 'Sportage', 'Sorento', 'Telluride', 'EV6', 'Soul', 'Seltos']
            };
            
            // Addis Ababa locations
            const addisLocations = [
                'Bole', 'Kazanchis', 'Piassa', 'Merkato', 'Mexico Square', 'Megenagna', 
                'Sarbet', 'Gerji', 'Ayat', 'Jemo', 'CMC', 'Summit', 'Lebu', 'Kaliti', 
                'Shiro Meda', 'Arat Kilo', 'Lideta', 'Kirkos', 'Meskel Square', 'Sidist Kilo', 
                'Lamberet', 'Hayat', 'Haile Garment', 'Wello Sefer', 'Kera', 'Gofa Condominium',
                'Bole Airport', 'Bole Bulbula', 'Yeka Abado', 'Haya Hulet', 'Kolfe', 'Tor Hailoch',
                'Addis Ababa University', 'Gurdshola', 'Tulu Dimtu', 'Kality', 'Alem Bank'
            ];
            
            // Update steps
            function updateStep() {
                formSteps.forEach((step, index) => {
                    step.classList.toggle('active', index === currentStep);
                });
                
                progressSteps.forEach((step, index) => {
                    step.classList.toggle('active', index === currentStep);
                    step.classList.toggle('completed', index < currentStep);
                });
                
                prevBtn.disabled = currentStep === 0;
                
                if (currentStep === formSteps.length - 1) {
                    nextBtn.textContent = 'Submit Listing';
                    nextBtn.type = 'submit';
                    nextBtn.form = 'listing-form';
                } else {
                    nextBtn.textContent = 'Continue';
                    nextBtn.type = 'button';
                    nextBtn.removeAttribute('form');
                }
                
                // Populate review data if on review step
                if (currentStep === 3) {
                    populateReviewData();
                }
            }
            
            // Populate review data
            function populateReviewData() {
                // Vehicle details
                document.getElementById('review-make').textContent = document.getElementById('make-search').value || '-';
                document.getElementById('review-model').textContent = document.getElementById('model').options[document.getElementById('model').selectedIndex]?.text || '-';
                document.getElementById('review-year').textContent = document.getElementById('year').value || '-';
                document.getElementById('review-vehicle-type').textContent = document.getElementById('vehicle-type').options[document.getElementById('vehicle-type').selectedIndex]?.text || '-';
                document.getElementById('review-transmission').textContent = document.getElementById('transmission').options[document.getElementById('transmission').selectedIndex]?.text || '-';
                document.getElementById('review-fuel-type').textContent = document.getElementById('fuel-type').options[document.getElementById('fuel-type').selectedIndex]?.text || '-';
                document.getElementById('review-seating').textContent = document.getElementById('seating').options[document.getElementById('seating').selectedIndex]?.text || '-';
                
                // Rental information
                const price = document.getElementById('price').value;
                const deposit = document.getElementById('deposit').value;
                document.getElementById('review-price').textContent = price ? '$' + price + ' per day' : '-';
                document.getElementById('review-deposit').textContent = deposit ? '$' + deposit : '-';
                document.getElementById('review-location').textContent = document.getElementById('location').value || '-';
                
                const fromDate = document.getElementById('available-from').value;
                const toDate = document.getElementById('available-to').value;
                if (fromDate && toDate) {
                    const from = new Date(fromDate);
                    const to = new Date(toDate);
                    document.getElementById('review-dates').textContent = 
                        from.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) + 
                        ' to ' + 
                        to.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                } else {
                    document.getElementById('review-dates').textContent = '-';
                }
                
                // Additional details
                document.getElementById('review-description').textContent = document.getElementById('description').value || '-';
                document.getElementById('review-contact').textContent = document.getElementById('contact').value || '-';
                
                // Photos
                const photoPreview = document.getElementById('review-photos');
                photoPreview.innerHTML = '';
                
                const thumbnails = document.querySelectorAll('#photo-preview .thumbnail');
                thumbnails.forEach((thumbnail, index) => {
                    if (index < 3) { // Show first 3 photos in review
                        const img = thumbnail.querySelector('img').cloneNode(true);
                        const div = document.createElement('div');
                        div.className = 'thumbnail';
                        div.appendChild(img);
                        photoPreview.appendChild(div);
                    }
                });
                
                if (thumbnails.length > 3) {
                    const morePhotosDiv = document.createElement('div');
                    morePhotosDiv.className = 'thumbnail';
                    morePhotosDiv.style.display = 'flex';
                    morePhotosDiv.style.alignItems = 'center';
                    morePhotosDiv.style.justifyContent = 'center';
                    morePhotosDiv.style.backgroundColor = 'var(--gray-100)';
                    morePhotosDiv.innerHTML = `<span style="font-weight: 600; color: var(--gray-700);">+${thumbnails.length - 3} more</span>`;
                    photoPreview.appendChild(morePhotosDiv);
                }
            }
            
            // Validate current step
            function validateStep(step) {
                let isValid = true;
                const errorMessages = document.querySelectorAll('.error-message');
                errorMessages.forEach(msg => msg.classList.remove('visible'));
                
                // Clear previous errors
                document.querySelectorAll('.form-input, .form-select, .form-textarea').forEach(input => {
                    input.classList.remove('error');
                });
                
                if (step === 0) {
                    // Validate Vehicle Details
                    const make = document.getElementById('make-search');
                    const model = document.getElementById('model');
                    const year = document.getElementById('year');
                    const vehicleType = document.getElementById('vehicle-type');
                    const transmission = document.getElementById('transmission');
                    const fuelType = document.getElementById('fuel-type');
                    const seating = document.getElementById('seating');
                    
                    if (!make.value.trim()) {
                        document.getElementById('make-error').classList.add('visible');
                        make.classList.add('error');
                        isValid = false;
                    }
                    
                    if (!model.value) {
                        document.getElementById('model-error').classList.add('visible');
                        model.classList.add('error');
                        isValid = false;
                    }
                    
                    if (!year.value) {
                        document.getElementById('year-error').classList.add('visible');
                        year.classList.add('error');
                        isValid = false;
                    }
                    
                    if (!vehicleType.value) {
                        document.getElementById('vehicle-type-error').classList.add('visible');
                        vehicleType.classList.add('error');
                        isValid = false;
                    }
                    
                    if (!transmission.value) {
                        document.getElementById('transmission-error').classList.add('visible');
                        transmission.classList.add('error');
                        isValid = false;
                    }
                    
                    if (!fuelType.value) {
                        document.getElementById('fuel-type-error').classList.add('visible');
                        fuelType.classList.add('error');
                        isValid = false;
                    }
                    
                    if (!seating.value) {
                        document.getElementById('seating-error').classList.add('visible');
                        seating.classList.add('error');
                        isValid = false;
                    }
                } else if (step === 1) {
                    // Validate Rental Information
                    const price = document.getElementById('price');
                    const deposit = document.getElementById('deposit');
                    const location = document.getElementById('location');
                    const availableFrom = document.getElementById('available-from');
                    const availableTo = document.getElementById('available-to');
                    
                    if (!price.value || price.value <= 0) {
                        document.getElementById('price-error').classList.add('visible');
                        price.classList.add('error');
                        isValid = false;
                    }
                    
                    if (!deposit.value || deposit.value < 0) {
                        document.getElementById('deposit-error').classList.add('visible');
                        deposit.classList.add('error');
                        isValid = false;
                    }
                    
                    if (!location.value.trim()) {
                        document.getElementById('location-error').classList.add('visible');
                        location.classList.add('error');
                        isValid = false;
                    }
                    
                    if (!availableFrom.value) {
                        document.getElementById('available-from-error').classList.add('visible');
                        availableFrom.classList.add('error');
                        isValid = false;
                    }
                    
                    if (!availableTo.value) {
                        document.getElementById('available-to-error').classList.add('visible');
                        availableTo.classList.add('error');
                        isValid = false;
                    }
                    
                    if (availableFrom.value && availableTo.value) {
                        const from = new Date(availableFrom.value);
                        const to = new Date(availableTo.value);
                        
                        if (from > to) {
                            document.getElementById('available-to-error').textContent = 'End date must be after start date';
                            document.getElementById('available-to-error').classList.add('visible');
                            availableTo.classList.add('error');
                            isValid = false;
                        }
                    }
                } else if (step === 2) {
                    // Validate Additional Details (made more lenient for testing)
                    const description = document.getElementById('description');
                    const contact = document.getElementById('contact');
                    const terms = document.getElementById('terms');
                    
                    if (!description.value.trim() || description.value.length < 50) {
                        document.getElementById('description-error').classList.add('visible');
                        description.classList.add('error');
                        isValid = false;
                    }
                    
                    if (!contact.value.trim()) {
                        document.getElementById('contact-error').classList.add('visible');
                        contact.classList.add('error');
                        isValid = false;
                    }
                    
                    if (!terms.checked) {
                        document.getElementById('terms-error').classList.add('visible');
                        isValid = false;
                    }
                }
                
                if (!isValid) {
                    // Scroll to first error
                    const firstError = document.querySelector('.error-message.visible');
                    if (firstError) {
                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
                
                return isValid;
            }
            
            // Remove error styling when input changes
            const formInputs = document.querySelectorAll('.form-input, .form-select, .form-textarea');
            formInputs.forEach(input => {
                input.addEventListener('input', function() {
                    this.classList.remove('error');
                    const errorId = this.id + '-error';
                    const errorElement = document.getElementById(errorId);
                    if (errorElement) {
                        errorElement.classList.remove('visible');
                    }
                });
            });
            
            // Next button handler
            nextBtn.addEventListener('click', function(e) {
                e.preventDefault();
                
                console.log('Next button clicked, current step:', currentStep);
                
                if (currentStep < formSteps.length - 1) {
                    // Navigate to next step
                    if (validateStep(currentStep)) {
                        currentStep++;
                        updateStep();
                        window.scrollTo(0, 0);
                    }
                } else {
                    // Final step - submit form
                    console.log('Attempting form submission...');
                    
                    if (!validateStep(currentStep)) {
                        console.log('Validation failed');
                        return false;
                    }
                    
                    // Show loading state
                    nextBtn.disabled = true;
                    nextBtn.innerHTML = '<span style="display: inline-block; width: 20px; height: 20px; border: 2px solid #ffffff; border-radius: 50%; border-top-color: transparent; animation: spin 1s linear infinite;"></span> Submitting...';
                    
                    // Submit the form
                    console.log('Submitting form...');
                    form.submit();
                }
            });
            
            // Previous button handler
            prevBtn.addEventListener('click', function() {
                if (currentStep > 0) {
                    currentStep--;
                    updateStep();
                    window.scrollTo(0, 0);
                }
            });
            
            // Make search autocomplete
            const makeSearch = document.getElementById('make-search');
            const makeResults = document.getElementById('make-results');
            const modelSelect = document.getElementById('model');
            
            makeSearch.addEventListener('input', function() {
                const value = this.value.toLowerCase();
                makeResults.innerHTML = '';
                
                if (value.length < 1) {
                    makeResults.classList.remove('active');
                    return;
                }
                
                const makes = Object.keys(carModels);
                const filteredMakes = makes.filter(make => make.includes(value));
                
                if (filteredMakes.length > 0) {
                    filteredMakes.forEach(make => {
                        const item = document.createElement('div');
                        item.className = 'autocomplete-item';
                        item.textContent = make.charAt(0).toUpperCase() + make.slice(1);
                        
                        item.addEventListener('click', function() {
                            makeSearch.value = this.textContent;
                            makeResults.classList.remove('active');
                            
                            // Populate models
                            modelSelect.innerHTML = '<option value="" disabled>Select model</option>';
                            
                            carModels[make].forEach(model => {
                                const option = document.createElement('option');
                                option.value = model.toLowerCase().replace(' ', '-');
                                option.textContent = model;
                                modelSelect.appendChild(option);
                            });
                            
                            modelSelect.disabled = false;
                            
                            // Clear error if exists
                            makeSearch.classList.remove('error');
                            document.getElementById('make-error').classList.remove('visible');
                        });
                        
                        makeResults.appendChild(item);
                    });
                    
                    makeResults.classList.add('active');
                } else {
                    makeResults.classList.remove('active');
                }
            });
            
            // Close autocomplete when clicking outside
            document.addEventListener('click', function(e) {
                if (!makeSearch.contains(e.target) && !makeResults.contains(e.target)) {
                    makeResults.classList.remove('active');
                }
            });
            
            // Location autocomplete
            const locationInput = document.getElementById('location');
            const locationResults = document.getElementById('location-results');
            
            locationInput.addEventListener('input', function() {
                const value = this.value.toLowerCase();
                locationResults.innerHTML = '';
                
                if (value.length < 2) {
                    locationResults.classList.remove('active');
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
                            locationInput.value = this.textContent;
                            locationResults.classList.remove('active');
                            
                            // Clear error if exists
                            locationInput.classList.remove('error');
                            document.getElementById('location-error').classList.remove('visible');
                        });
                        
                        locationResults.appendChild(item);
                    });
                    
                    locationResults.classList.add('active');
                } else {
                    locationResults.classList.remove('active');
                }
            });
            
            // Close location autocomplete when clicking outside
            document.addEventListener('click', function(e) {
                if (!locationInput.contains(e.target) && !locationResults.contains(e.target)) {
                    locationResults.classList.remove('active');
                }
            });
            
            // Photo upload preview functionality
            // Photo upload preview functionality
const uploadInput = document.getElementById('car-photos');
const photoPreview = document.getElementById('photo-preview');
let selectedFiles = []; // Array to store all selected files

uploadInput.addEventListener('change', function(e) {
    if (e.target.files.length > 0) {
        const newFiles = Array.from(e.target.files); // Convert FileList to Array

        // Filter out duplicates based on file name and size
        newFiles.forEach(file => {
            if (!selectedFiles.some(f => f.name === file.name && f.size === file.size)) {
                // Check file size (max 10MB)
                if (file.size > 10 * 1024 * 1024) {
                    alert(`File "${file.name}" exceeds the 10MB limit.`);
                    return;
                }
                selectedFiles.push(file);
            }
        });

        // Update preview
        photoPreview.innerHTML = ''; // Clear existing previews
        selectedFiles.forEach((file, index) => {
            const thumbnail = document.createElement('div');
            thumbnail.className = 'thumbnail';

            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);

            const removeBtn = document.createElement('div');
            removeBtn.className = 'thumbnail-remove';
            removeBtn.innerHTML = '×';
            removeBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                // Remove file from selectedFiles
                selectedFiles.splice(index, 1);
                // Update preview
                photoPreview.removeChild(thumbnail);
                // Update file input
                updateFileInput();
            });

            thumbnail.appendChild(img);
            thumbnail.appendChild(removeBtn);
            photoPreview.appendChild(thumbnail);
        });

        // Update file input with accumulated files
        updateFileInput();

        // Clear error
        document.getElementById('photos-error').classList.remove('visible');
    }
});

// Function to update file input with accumulated files
function updateFileInput() {
    const dataTransfer = new DataTransfer();
    selectedFiles.forEach(file => dataTransfer.items.add(file));
    uploadInput.files = dataTransfer.files;
}
            
            // Set min date for available from to today
            const today = new Date();
            const dd = String(today.getDate()).padStart(2, '0');
            const mm = String(today.getMonth() + 1).padStart(2, '0');
            const yyyy = today.getFullYear();
            const todayString = yyyy + '-' + mm + '-' + dd;
            
            document.getElementById('available-from').min = todayString;
            
            // Set min date for available to based on available from
            document.getElementById('available-from').addEventListener('change', function() {
                document.getElementById('available-to').min = this.value;
                
                // Clear error if it exists
                this.classList.remove('error');
                document.getElementById('available-from-error').classList.remove('visible');
                
                if (document.getElementById('available-to').value && 
                    new Date(this.value) > new Date(document.getElementById('available-to').value)) {
                    document.getElementById('available-to').value = this.value;
                }
            });
            
            document.getElementById('available-to').addEventListener('change', function() {
                // Clear error if it exists
                this.classList.remove('error');
                document.getElementById('available-to-error').classList.remove('visible');
            });

            // Mobile menu functionality
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
            
            // Initialize
            updateStep();
        });