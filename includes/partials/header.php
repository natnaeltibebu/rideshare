<?php
// Get current user if not already available
if (!isset($current_user)) {
    $current_user = get_logged_in_user();
}

// Set default current page if not provided
if (!isset($current_page)) {
    $current_page = 'home';
}
?>

<header>
    <div class="container">
        <nav>
            <a href="<?php echo BASE_URL; ?>" class="logo">
                <div class="logo-icon">R</div>
                <?php echo htmlspecialchars(APP_NAME); ?>
            </a>
            
            <div class="nav-menu">
                <div class="nav-links">
                    <a href="<?php echo BASE_URL; ?>" class="<?php echo $current_page === 'home' ? 'active' : ''; ?>">Home</a>
                    <a href="<?php echo BASE_URL; ?>/browse" class="<?php echo $current_page === 'browse' ? 'active' : ''; ?>">Browse Cars</a>
                    <a href="<?php echo BASE_URL; ?>/host" class="<?php echo $current_page === 'host' ? 'active' : ''; ?>">List Your Car</a>
                </div>
                
                <?php if (is_logged_in()): ?>
                    <div class="avatar-dropdown">
                        <button class="avatar-btn">
                            <div class="avatar"><?php echo strtoupper(substr($current_user['first_name'], 0, 1) . substr($current_user['last_name'], 0, 1)); ?></div>
                            <div class="avatar-info">
                                <div class="avatar-name"><?php echo htmlspecialchars($current_user['first_name'] . ' ' . $current_user['last_name']); ?></div>
                                <div class="avatar-email"><?php echo htmlspecialchars($current_user['email']); ?></div>
                            </div>
                            <svg class="dropdown-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </button>
                        
                        <div class="dropdown-menu">
                            <div class="dropdown-header">
                                <div class="dropdown-name"><?php echo htmlspecialchars($current_user['first_name'] . ' ' . $current_user['last_name']); ?></div>
                                <div class="dropdown-email"><?php echo htmlspecialchars($current_user['email']); ?></div>
                            </div>
                            
                            <div class="dropdown-links">
                                <a href="<?php echo BASE_URL; ?>/dashboard" class="dropdown-link">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                    My Profile
                                </a>
                                <a href="<?php echo BASE_URL; ?>/dashboard?tab=bookings" class="dropdown-link">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 4H8l-7 8 7 8h13a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2z"></path>
                                        <line x1="18" y1="9" x2="12" y2="15"></line>
                                        <line x1="12" y1="9" x2="18" y2="15"></line>
                                    </svg>
                                    My Bookings
                                </a>
                                <a href="#" class="dropdown-link">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                    </svg>
                                    Support
                                </a>
                                <a href="#" class="dropdown-link">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="3"></circle>
                                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                                    </svg>
                                    Settings
                                </a>
                            </div>
                            
                            <div class="dropdown-footer">
                                <button class="logout-btn" onclick="logout()">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                        <polyline points="16 17 21 12 16 7"></polyline>
                                        <line x1="21" y1="12" x2="9" y2="12"></line>
                                    </svg>
                                    Log Out
                                </button>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>/login" class="sign-in-btn">Sign In</a>
                <?php endif; ?>
            </div>
            
            <button class="mobile-menu-btn">☰</button>
        </nav>
    </div>
</header>