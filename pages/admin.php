<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin_auth.php';

// Check admin or host access
require_admin_or_host();

$current_user = get_logged_in_user();
$user_role = $current_user['role'];
$is_admin = ($user_role === 'admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_admin ? 'Admin' : 'Host'; ?> Dashboard | Rideshare</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap">
    <style>
        :root {
            --primary: #4F46E5;
            --primary-light: #EEF2FF;
            --primary-dark: #4338CA;
            --dark: #111827;
            --gray-900: #1F2937;
            --gray-800: #374151;
            --gray-700: #4B5563;
            --gray-500: #6B7280;
            --gray-300: #D1D5DB;
            --gray-200: #E5E7EB;
            --gray-100: #F3F4F6;
            --gray-50: #F9FAFB;
            --white: #FFFFFF;
            --success: #10B981;
            --warning: #FBBF24;
            --error: #EF4444;
            --radius-sm: 6px;
            --radius-md: 10px;
            --radius-lg: 16px;
            --radius-xl: 24px;
            --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            color: var(--gray-900);
            background-color: var(--gray-50);
            line-height: 1.5;
            overflow-x: hidden;
        }

        /* Typography */
        h1, h2, h3, h4, h5, h6 {
            font-weight: 700;
            line-height: 1.2;
            letter-spacing: -0.025em;
        }

        /* Buttons */
        .btn {
            padding: 12px 24px;
            border-radius: var(--radius-md);
            font-weight: 500;
            font-size: 15px;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            position: relative;
            overflow: hidden;
        }

        .btn::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            pointer-events: none;
            background-image: radial-gradient(circle, #fff 10%, transparent 10.01%);
            background-repeat: no-repeat;
            background-position: 50%;
            transform: scale(10, 10);
            opacity: 0;
            transition: transform 0.4s, opacity 0.8s;
        }

        .btn:active::after {
            transform: scale(0, 0);
            opacity: 0.3;
            transition: 0s;
        }

        .btn-primary {
            background-color: var(--primary);
            color: var(--white);
            box-shadow: 0 2px 4px rgba(79, 70, 229, 0.1);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(79, 70, 229, 0.2);
        }
        
        .btn-secondary {
            background-color: var(--white);
            color: var(--gray-800);
            border: 1px solid var(--gray-200);
            box-shadow: var(--shadow-sm);
        }
        
        .btn-secondary:hover {
            background-color: var(--gray-50);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn-sm {
            padding: 8px 16px;
            font-size: 14px;
        }

        .btn-success {
            background-color: var(--success);
            color: var(--white);
        }

        .btn-warning {
            background-color: var(--warning);
            color: var(--white);
        }

        .btn-danger {
            background-color: var(--error);
            color: var(--white);
        }

        /* Admin Layout */
        .admin-layout {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background-color: var(--white);
            border-right: 1px solid var(--gray-200);
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 50;
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--gray-200);
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--dark);
            text-decoration: none;
        }

        .logo-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.25rem;
        }

        .sidebar-nav {
            flex: 1;
            padding: 1.5rem 0;
        }

        .nav-section {
            margin-bottom: 2rem;
        }

        .nav-section-title {
            padding: 0 1.5rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--gray-500);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .nav-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            color: var(--gray-700);
            text-decoration: none;
            transition: var(--transition);
            font-size: 0.9375rem;
            font-weight: 500;
            gap: 12px;
        }

        .nav-item:hover {
            background-color: var(--gray-50);
            color: var(--primary);
        }

        .nav-item.active {
            background-color: var(--primary-light);
            color: var(--primary);
            border-right: 3px solid var(--primary);
        }

        .nav-icon {
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sidebar-footer {
            padding: 1.5rem;
            border-top: 1px solid var(--gray-200);
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0.75rem;
            border-radius: var(--radius-md);
            transition: var(--transition);
            cursor: pointer;
        }

        .user-profile:hover {
            background-color: var(--gray-50);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .user-info {
            flex: 1;
        }

        .user-name {
            font-weight: 600;
            font-size: 0.875rem;
            color: var(--gray-900);
        }

        .user-role {
            font-size: 0.75rem;
            color: var(--gray-500);
            text-transform: capitalize;
        }

        /* Main Content */
        .main-content {
            margin-left: 280px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .top-bar {
            background-color: var(--white);
            border-bottom: 1px solid var(--gray-200);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 40;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--gray-900);
        }

        .top-bar-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .notification-btn {
            position: relative;
            padding: 0.5rem;
            background-color: var(--gray-100);
            border-radius: 50%;
            border: none;
            cursor: pointer;
            transition: var(--transition);
        }

        .notification-btn:hover {
            background-color: var(--gray-200);
        }

        .notification-badge {
            position: absolute;
            top: 0;
            right: 0;
            width: 8px;
            height: 8px;
            background-color: var(--error);
            border-radius: 50%;
        }

        .content-area {
            padding: 2rem;
            flex: 1;
        }

        /* Dashboard Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background-color: var(--white);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-100);
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .stat-title {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--gray-700);
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .stat-icon.primary {
            background-color: var(--primary);
        }

        .stat-icon.success {
            background-color: var(--success);
        }

        .stat-icon.warning {
            background-color: var(--warning);
        }

        .stat-icon.error {
            background-color: var(--error);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 800;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
        }

        .stat-change {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            font-size: 0.875rem;
        }

        .stat-change.positive {
            color: var(--success);
        }

        .stat-change.negative {
            color: var(--error);
        }

        /* Tables */
        .table-container {
            background-color: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-100);
            overflow: hidden;
        }

        .table-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--gray-900);
        }

        .table-actions {
            display: flex;
            gap: 0.75rem;
        }

        .search-input {
            padding: 0.5rem 1rem;
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-md);
            font-size: 0.875rem;
            color: var(--gray-900);
            background-color: var(--gray-50);
            transition: var(--transition);
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary);
            background-color: var(--white);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th {
            text-align: left;
            padding: 1rem 1.5rem;
            font-weight: 600;
            font-size: 0.875rem;
            color: var(--gray-700);
            background-color: var(--gray-50);
            border-bottom: 1px solid var(--gray-200);
        }

        .data-table td {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--gray-100);
            font-size: 0.875rem;
            color: var(--gray-900);
        }

        .data-table tr:hover {
            background-color: var(--gray-50);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 100px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-badge.active {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .status-badge.pending {
            background-color: rgba(251, 191, 36, 0.1);
            color: var(--warning);
        }

        .status-badge.inactive {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--error);
        }

        .status-badge.confirmed {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .status-badge.completed {
            background-color: rgba(79, 70, 229, 0.1);
            color: var(--primary);
        }

        .status-badge.cancelled {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--error);
        }

        .status-badge.maintenance {
            background-color: rgba(251, 191, 36, 0.1);
            color: var(--warning);
        }

        .avatar-sm {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.75rem;
        }

        /* Charts Container */
        .charts-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .chart-container {
            background-color: var(--white);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-100);
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .chart-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--gray-900);
        }

        .chart-placeholder {
            height: 300px;
            background: linear-gradient(135deg, var(--primary-light), var(--gray-50));
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray-500);
            font-weight: 500;
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .quick-action-btn {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem;
            background-color: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-lg);
            text-decoration: none;
            color: var(--gray-700);
            transition: var(--transition);
            box-shadow: var(--shadow-sm);
        }

        .quick-action-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
            border-color: var(--primary);
            color: var(--primary);
        }

        .quick-action-icon {
            width: 40px;
            height: 40px;
            background-color: var(--primary-light);
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
        }

        /* Recent Activity */
        .activity-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid var(--gray-100);
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: var(--primary-light);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            flex-shrink: 0;
        }

        .activity-content {
            flex: 1;
        }

        .activity-title {
            font-weight: 500;
            color: var(--gray-900);
            font-size: 0.875rem;
        }

        .activity-time {
            font-size: 0.75rem;
            color: var(--gray-500);
        }

        /* Content Sections */
        .content-section {
            display: none;
        }

        .content-section.active {
            display: block;
        }

        /* Loading */
        .loading {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            color: var(--gray-500);
        }

        .loading-spinner {
            width: 20px;
            height: 20px;
            border: 2px solid var(--gray-200);
            border-top: 2px solid var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 0.5rem;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Mobile Menu Toggle */
        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--gray-700);
        }

        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .modal-content {
            background-color: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-xl);
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            margin: 0;
            color: var(--gray-900);
        }

        .modal-close {
            background: none;
            border: none;
            cursor: pointer;
            color: var(--gray-500);
            padding: 0.5rem;
            border-radius: var(--radius-sm);
            transition: var(--transition);
        }

        .modal-close:hover {
            background-color: var(--gray-100);
            color: var(--gray-700);
        }

        .modal-body {
            padding: 1.5rem;
        }

        .contact-info,
        .booking-details {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .contact-item,
        .detail-row {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .contact-item strong,
        .detail-row strong {
            color: var(--gray-700);
            font-size: 0.875rem;
        }

        .contact-item a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }

        .contact-item a:hover {
            text-decoration: underline;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--gray-700);
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-md);
            font-size: 0.875rem;
            color: var(--gray-900);
            background-color: var(--white);
            transition: var(--transition);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .form-actions {
            display: flex;
            gap: 0.75rem;
            justify-content: flex-end;
            margin-top: 1.5rem;
            padding-top: 1rem;
            border-top: 1px solid var(--gray-200);
        }

        /* Notification Styles */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: var(--white);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-lg);
            border-left: 4px solid;
            z-index: 1001;
            min-width: 300px;
            animation: slideInRight 0.3s ease;
        }

        .notification-success {
            border-left-color: var(--success);
        }

        .notification-error {
            border-left-color: var(--error);
        }

        .notification-info {
            border-left-color: var(--primary);
        }

        .notification-warning {
            border-left-color: var(--warning);
        }

        .notification-content {
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .notification-close {
            background: none;
            border: none;
            cursor: pointer;
            color: var(--gray-500);
            padding: 0.25rem;
            border-radius: var(--radius-sm);
            transition: var(--transition);
        }

        .notification-close:hover {
            background-color: var(--gray-100);
            color: var(--gray-700);
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .mobile-menu-toggle {
                display: block;
            }

            .charts-grid {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .content-area {
                padding: 1rem;
            }

            .top-bar {
                padding: 1rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .quick-actions {
                grid-template-columns: 1fr;
            }

            .table-actions {
                flex-direction: column;
                gap: 0.5rem;
            }

            .search-input {
                width: 100%;
            }

            .modal-content {
                max-width: 95%;
            }
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <a href="<?php echo BASE_URL; ?>" class="sidebar-logo">
                    <div class="logo-icon">R</div>
                    Rideshare <?php echo ucfirst($user_role); ?>
                </a>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">Main</div>
                    <a href="#" class="nav-item active" data-section="dashboard">
                        <div class="nav-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="7" height="7"></rect>
                                <rect x="14" y="3" width="7" height="7"></rect>
                                <rect x="14" y="14" width="7" height="7"></rect>
                                <rect x="3" y="14" width="7" height="7"></rect>
                            </svg>
                        </div>
                        Dashboard
                    </a>
                    <?php if ($is_admin): ?>
                    <a href="#" class="nav-item" data-section="analytics">
                        <div class="nav-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="18" y1="20" x2="18" y2="10"></line>
                                <line x1="12" y1="20" x2="12" y2="4"></line>
                                <line x1="6" y1="20" x2="6" y2="14"></line>
                            </svg>
                        </div>
                        Analytics
                    </a>
                    <?php endif; ?>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Management</div>
                    <a href="#" class="nav-item" data-section="cars">
                        <div class="nav-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9L18 10.5V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v5c0 .4.2.8.5 1l4 3.5H8a2 2 0 0 0 2 2h1v-2a2 2 0 0 0-2-2"></path>
                                <circle cx="7" cy="17" r="2"></circle>
                                <path d="M9 17h6"></path>
                                <circle cx="17" cy="17" r="2"></circle>
                            </svg>
                        </div>
                        <?php echo $is_admin ? 'All Cars' : 'My Cars'; ?>
                    </a>
                    <?php if ($is_admin): ?>
                    <a href="#" class="nav-item" data-section="users">
                        <div class="nav-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                        </div>
                        Users
                    </a>
                    <?php endif; ?>
                    <a href="#" class="nav-item" data-section="bookings">
                        <div class="nav-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                        </div>
                        <?php echo $is_admin ? 'All Bookings' : 'My Bookings'; ?>
                    </a>
                </div>
            </nav>

            <div class="sidebar-footer">
                <div class="user-profile">
                    <div class="user-avatar"><?php echo strtoupper(substr($current_user['first_name'], 0, 1) . substr($current_user['last_name'], 0, 1)); ?></div>
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($current_user['first_name'] . ' ' . $current_user['last_name']); ?></div>
                        <div class="user-role"><?php echo htmlspecialchars($current_user['role']); ?></div>
                    </div>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Bar -->
            <header class="top-bar">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <button class="mobile-menu-toggle" id="mobile-menu-toggle">☰</button>
                    <h1 class="page-title" id="page-title">Dashboard</h1>
                </div>
                
                <div class="top-bar-actions">
                    <button class="notification-btn">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                        </svg>
                        <div class="notification-badge"></div>
                    </button>
                    <a href="<?php echo BASE_URL; ?>" class="btn btn-secondary btn-sm">View Site</a>
                    <button onclick="logout()" class="btn btn-danger btn-sm">Logout</button>
                </div>
            </header>

            <!-- Content Area -->
            <div class="content-area">
                <!-- Dashboard Section -->
                <div class="content-section active" id="dashboard">
                    <!-- Stats Grid -->
                    <div class="stats-grid" id="stats-grid">
                        <div class="loading">
                            <div class="loading-spinner"></div>
                            Loading statistics...
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="quick-actions">
                        <a href="<?php echo BASE_URL; ?>/host" class="quick-action-btn">
                            <div class="quick-action-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                            </div>
                            <div>
                                <div style="font-weight: 600;">Add New Car</div>
                                <div style="font-size: 0.75rem; color: var(--gray-500);">List a new vehicle</div>
                            </div>
                        </a>

                        <?php if ($is_admin): ?>
                        <a href="#" class="quick-action-btn" onclick="showAddUserModal()">
                            <div class="quick-action-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <line x1="19" y1="8" x2="19" y2="14"></line>
                                    <line x1="22" y1="11" x2="16" y2="11"></line>
                                </svg>
                            </div>
                            <div>
                                <div style="font-weight: 600;">Add User</div>
                                <div style="font-size: 0.75rem; color: var(--gray-500);">Create new account</div>
                            </div>
                        </a>
                        <?php endif; ?>

                        <a href="#" class="quick-action-btn">
                            <div class="quick-action-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                    <line x1="16" y1="13" x2="8" y2="13"></line>
                                    <line x1="16" y1="17" x2="8" y2="17"></line>
                                    <polyline points="10 9 9 9 8 9"></polyline>
                                </svg>
                            </div>
                            <div>
                                <div style="font-weight: 600;">Generate Report</div>
                                <div style="font-size: 0.75rem; color: var(--gray-500);">Export analytics</div>
                            </div>
                        </a>
                    </div>

                    <!-- Charts -->
                    <div class="charts-grid">
                        <div class="chart-container">
                            <div class="chart-header">
                                <h3 class="chart-title">Revenue Analytics</h3>
                                <select class="btn btn-secondary btn-sm" style="border: 1px solid var(--gray-200);">
                                    <option>Last 30 days</option>
                                    <option>Last 90 days</option>
                                    <option>Last year</option>
                                </select>
                            </div>
                            <div class="chart-placeholder">
                                Revenue Chart Placeholder
                            </div>
                        </div>

                        <div class="chart-container">
                            <div class="chart-header">
                                <h3 class="chart-title">Recent Activity</h3>
                            </div>
                            <div style="max-height: 300px; overflow-y: auto;" id="recent-activity">
                                <div class="loading">
                                    <div class="loading-spinner"></div>
                                    Loading activities...
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Bookings Table -->
                    <div class="table-container">
                        <div class="table-header">
                            <h3 class="table-title">Recent Bookings</h3>
                            <div class="table-actions">
                                <input type="text" class="search-input" placeholder="Search bookings..." id="dashboard-bookings-search">
                                <button class="btn btn-secondary btn-sm">Export</button>
                            </div>
                        </div>
                        <div id="dashboard-bookings-table">
                            <div class="loading">
                                <div class="loading-spinner"></div>
                                Loading bookings...
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cars Section -->
                <div class="content-section" id="cars">
                    <div class="table-container">
                        <div class="table-header">
                            <h3 class="table-title"><?php echo $is_admin ? 'All Cars' : 'My Cars'; ?> Management</h3>
                            <div class="table-actions">
                                <input type="text" class="search-input" placeholder="Search cars..." id="cars-search">
                                <button class="btn btn-secondary btn-sm">Filter</button>
                                <a href="<?php echo BASE_URL; ?>/host" class="btn btn-primary btn-sm">Add Car</a>
                            </div>
                        </div>
                        <div id="cars-table">
                            <div class="loading">
                                <div class="loading-spinner"></div>
                                Loading cars...
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($is_admin): ?>
                <!-- Users Section -->
                <div class="content-section" id="users">
                    <div class="table-container">
                        <div class="table-header">
                            <h3 class="table-title">Users Management</h3>
                            <div class="table-actions">
                                <input type="text" class="search-input" placeholder="Search users..." id="users-search">
                                <button class="btn btn-secondary btn-sm">Filter</button>
                                <button class="btn btn-primary btn-sm" onclick="showAddUserModal()">Add User</button>
                            </div>
                        </div>
                        <div id="users-table">
                            <div class="loading">
                                <div class="loading-spinner"></div>
                                Loading users...
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Bookings Section -->
                <div class="content-section" id="bookings">
                    <div class="table-container">
                        <div class="table-header">
                            <h3 class="table-title"><?php echo $is_admin ? 'All Bookings' : 'My Bookings'; ?> Management</h3>
                            <div class="table-actions">
                                <input type="text" class="search-input" placeholder="Search bookings..." id="bookings-search">
                                <select class="btn btn-secondary btn-sm" style="border: 1px solid var(--gray-200);" id="bookings-status-filter">
                                    <option value="">All Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="confirmed">Confirmed</option>
                                    <option value="active">Active</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                                <button class="btn btn-secondary btn-sm">Export</button>
                            </div>
                        </div>
                        <div id="bookings-table">
                            <div class="loading">
                                <div class="loading-spinner"></div>
                                Loading bookings...
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($is_admin): ?>
                <!-- Analytics Section -->
                <div class="content-section" id="analytics">
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-header">
                                <div class="stat-title">Monthly Revenue</div>
                                <div class="stat-icon success">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="18" y1="20" x2="18" y2="10"></line>
                                        <line x1="12" y1="20" x2="12" y2="4"></line>
                                        <line x1="6" y1="20" x2="6" y2="14"></line>
                                    </svg>
                                </div>
                            </div>
                            <div class="stat-value">$42,580</div>
                            <div class="stat-change positive">
                                +15% from last month
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-header">
                                <div class="stat-title">Conversion Rate</div>
                                <div class="stat-icon primary">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                                        <polyline points="17 6 23 6 23 12"></polyline>
                                    </svg>
                                </div>
                            </div>
                            <div class="stat-value">24.3%</div>
                            <div class="stat-change positive">
                                +2.1% from last month
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-header">
                                <div class="stat-title">Avg. Booking Value</div>
                                <div class="stat-icon warning">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="12" y1="1" x2="12" y2="23"></line>
                                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="stat-value">$82</div>
                            <div class="stat-change positive">
                                +$7 from last month
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-header">
                                <div class="stat-title">Customer Satisfaction</div>
                                <div class="stat-icon success">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                    </svg>
                                </div>
                            </div>
                            <div class="stat-value">4.8</div>
                            <div class="stat-change positive">
                                +0.2 from last month
                            </div>
                        </div>
                    </div>

                    <div class="chart-container" style="margin-bottom: 2rem;">
                        <div class="chart-header">
                            <h3 class="chart-title">Revenue Trends</h3>
                            <select class="btn btn-secondary btn-sm" style="border: 1px solid var(--gray-200);">
                                <option>Last 6 months</option>
                                <option>Last year</option>
                                <option>All time</option>
                            </select>
                        </div>
                        <div class="chart-placeholder" style="height: 400px;">
                            Revenue Trends Chart Placeholder
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                        <div class="chart-container">
                            <div class="chart-header">
                                <h3 class="chart-title">Booking Sources</h3>
                            </div>
                            <div class="chart-placeholder">
                                Pie Chart Placeholder
                            </div>
                        </div>

                        <div class="chart-container">
                            <div class="chart-header">
                                <h3 class="chart-title">Popular Car Types</h3>
                            </div>
                            <div class="chart-placeholder">
                                Bar Chart Placeholder
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        const API_BASE = '<?php echo BASE_URL; ?>/api';
        const USER_ROLE = '<?php echo $user_role; ?>';
        const IS_ADMIN = <?php echo $is_admin ? 'true' : 'false'; ?>;
        const CURRENT_USER_ID = <?php echo $_SESSION['user_id']; ?>;

        // Global variables
        let currentSection = 'dashboard';
        let dashboardData = {};
        let searchTimeouts = {};

        // Logout function
        function logout() {
            fetch(`${API_BASE}/auth.php?action=logout`, {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                window.location.href = '<?php echo BASE_URL; ?>/';
            })
            .catch(error => {
                console.error('Error:', error);
                window.location.href = '<?php echo BASE_URL; ?>/';
            });
        }

        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            // Navigation functionality
            setupNavigation();
            
            // Mobile menu
            setupMobileMenu();
            
            // Load initial data
            loadDashboardStats();
            loadRecentActivity();
            loadDashboardBookings();
            
            // Setup search handlers
            setupSearchHandlers();
        });

        function setupNavigation() {
            const navItems = document.querySelectorAll('.nav-item');
            const contentSections = document.querySelectorAll('.content-section');
            const pageTitle = document.getElementById('page-title');

            navItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Remove active class from all nav items
                    navItems.forEach(nav => nav.classList.remove('active'));
                    
                    // Add active class to clicked item
                    this.classList.add('active');
                    
                    // Hide all content sections
                    contentSections.forEach(section => section.classList.remove('active'));
                    
                    // Show target section
                    const targetSection = this.dataset.section;
                    document.getElementById(targetSection).classList.add('active');
                    
                    // Update page title
                    const sectionTitles = {
                        'dashboard': 'Dashboard',
                        'analytics': 'Analytics',
                        'cars': IS_ADMIN ? 'All Cars Management' : 'My Cars Management',
                        'users': 'Users Management',
                        'bookings': IS_ADMIN ? 'All Bookings Management' : 'My Bookings Management'
                    };
                    pageTitle.textContent = sectionTitles[targetSection] || 'Dashboard';
                    
                    currentSection = targetSection;
                    
                    // Load section data
                    loadSectionData(targetSection);
                });
            });
        }

        function setupMobileMenu() {
            const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
            const sidebar = document.getElementById('sidebar');

            mobileMenuToggle.addEventListener('click', function() {
                sidebar.classList.toggle('open');
            });

            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(e) {
                if (window.innerWidth <= 1024) {
                    if (!sidebar.contains(e.target) && !mobileMenuToggle.contains(e.target)) {
                        sidebar.classList.remove('open');
                    }
                }
            });

            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 1024) {
                    sidebar.classList.remove('open');
                }
            });
        }

        function setupSearchHandlers() {
            // Setup search with debouncing
            const searchInputs = {
                'cars-search': () => loadCars(),
                'users-search': () => loadUsers(),
                'bookings-search': () => loadBookings(),
                'dashboard-bookings-search': () => loadDashboardBookings()
            };

            Object.keys(searchInputs).forEach(inputId => {
                const input = document.getElementById(inputId);
                if (input) {
                    input.addEventListener('input', function() {
                        clearTimeout(searchTimeouts[inputId]);
                        searchTimeouts[inputId] = setTimeout(searchInputs[inputId], 300);
                    });
                }
            });

            // Status filter for bookings
            const statusFilter = document.getElementById('bookings-status-filter');
            if (statusFilter) {
                statusFilter.addEventListener('change', loadBookings);
            }
        }

        function loadSectionData(section) {
            switch(section) {
                case 'cars':
                    loadCars();
                    break;
                case 'users':
                    if (IS_ADMIN) loadUsers();
                    break;
                case 'bookings':
                    loadBookings();
                    break;
                case 'dashboard':
                    // Already loaded on page load
                    break;
            }
        }

        // API call functions
        async function apiCall(endpoint, options = {}) {
            try {
                const response = await fetch(`${API_BASE}/${endpoint}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        ...options.headers
                    },
                    ...options
                });
                
                const data = await response.json();
                
                if (!response.ok) {
                    throw new Error(data.error || 'Request failed');
                }
                
                return data;
            } catch (error) {
                console.error('API Error:', error);
                throw error;
            }
        }

        async function loadDashboardStats() {
            try {
                const response = await apiCall('admin.php?action=dashboard-stats');
                const stats = response.data;
                
                renderStats(stats);
            } catch (error) {
                document.getElementById('stats-grid').innerHTML = `
                    <div style="grid-column: 1 / -1; text-align: center; color: var(--error);">
                        Failed to load statistics: ${error.message}
                    </div>
                `;
            }
        }

        function renderStats(stats) {
            const statsGrid = document.getElementById('stats-grid');
            
            if (IS_ADMIN) {
                statsGrid.innerHTML = `
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-title">Total Cars</div>
                            <div class="stat-icon primary">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9L18 10.5V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v5c0 .4.2.8.5 1l4 3.5H8a2 2 0 0 0 2 2h1v-2a2 2 0 0 0-2-2"></path>
                                    <circle cx="7" cy="17" r="2"></circle>
                                    <path d="M9 17h6"></path>
                                    <circle cx="17" cy="17" r="2"></circle>
                                </svg>
                            </div>
                        </div>
                        <div class="stat-value">${stats.total_cars.toLocaleString()}</div>
                        <div class="stat-change ${stats.cars_growth > 0 ? 'positive' : 'negative'}">
                            ${stats.cars_growth > 0 ? '+' : ''}${stats.cars_growth} this month
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-title">Active Users</div>
                            <div class="stat-icon success">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="stat-value">${stats.active_users.toLocaleString()}</div>
                        <div class="stat-change ${stats.users_growth > 0 ? 'positive' : 'negative'}">
                            ${stats.users_growth > 0 ? '+' : ''}${stats.users_growth} this month
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-title">Total Bookings</div>
                            <div class="stat-icon warning">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                    <line x1="16" y1="2" x2="16" y2="6"></line>
                                    <line x1="8" y1="2" x2="8" y2="6"></line>
                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                </svg>
                            </div>
                        </div>
                        <div class="stat-value">${stats.total_bookings.toLocaleString()}</div>
                        <div class="stat-change ${stats.bookings_growth > 0 ? 'positive' : 'negative'}">
                            ${stats.bookings_growth > 0 ? '+' : ''}${stats.bookings_growth} this month
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-title">Revenue</div>
                            <div class="stat-icon error">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="12" y1="1" x2="12" y2="23"></line>
                                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="stat-value">$${stats.total_revenue.toLocaleString()}</div>
                        <div class="stat-change positive">All time</div>
                    </div>
                `;
            } else {
                // Host view
                statsGrid.innerHTML = `
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-title">My Cars</div>
                            <div class="stat-icon primary">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9L18 10.5V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v5c0 .4.2.8.5 1l4 3.5H8a2 2 0 0 0 2 2h1v-2a2 2 0 0 0-2-2"></path>
                                    <circle cx="7" cy="17" r="2"></circle>
                                    <path d="M9 17h6"></path>
                                    <circle cx="17" cy="17" r="2"></circle>
                                </svg>
                            </div>
                        </div>
                        <div class="stat-value">${stats.total_cars}</div>
                        <div class="stat-change positive">Listed cars</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-title">Total Bookings</div>
                            <div class="stat-icon success">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                    <line x1="16" y1="2" x2="16" y2="6"></line>
                                    <line x1="8" y1="2" x2="8" y2="6"></line>
                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                </svg>
                            </div>
                        </div>
                        <div class="stat-value">${stats.total_bookings}</div>
                        <div class="stat-change positive">All time</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-title">My Revenue</div>
                            <div class="stat-icon warning">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="12" y1="1" x2="12" y2="23"></line>
                                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="stat-value">$${stats.total_revenue.toLocaleString()}</div>
                        <div class="stat-change positive">All time</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-title">Avg Rating</div>
                            <div class="stat-icon success">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                </svg>
                            </div>
                        </div>
                        <div class="stat-value">${stats.avg_rating || '4.8'}</div>
                        <div class="stat-change positive">Out of 5.0</div>
                    </div>
                `;
            }
        }

        async function loadRecentActivity() {
            try {
                const response = await apiCall('admin.php?action=recent-activity&limit=10');
                const activities = response.data.activities;
                
                renderRecentActivity(activities);
            } catch (error) {
                document.getElementById('recent-activity').innerHTML = `
                    <div style="text-align: center; color: var(--error); padding: 1rem;">
                        Failed to load activities: ${error.message}
                    </div>
                `;
            }
        }

        function renderRecentActivity(activities) {
            const container = document.getElementById('recent-activity');
            
            if (activities.length === 0) {
                container.innerHTML = `
                    <div style="text-align: center; color: var(--gray-500); padding: 2rem;">
                        No recent activity
                    </div>
                `;
                return;
            }
            
            container.innerHTML = activities.map(activity => {
                const timeAgo = getTimeAgo(activity.timestamp);
                const activityTitle = getActivityTitle(activity);
                const activityIcon = getActivityIcon(activity.type);
                
                return `
                    <div class="activity-item">
                        <div class="activity-icon">
                            ${activityIcon}
                        </div>
                        <div class="activity-content">
                            <div class="activity-title">${activityTitle}</div>
                            <div class="activity-time">${timeAgo}</div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        function getActivityTitle(activity) {
            const name = `${activity.first_name} ${activity.last_name}`;
            switch(activity.type) {
                case 'user_registered':
                    return `${name} registered`;
                case 'car_listed':
                    return `${name} listed ${activity.car_info}`;
                case 'booking_created':
                case 'booking_received':
                    return `${name} booked ${activity.car_info}`;
                default:
                    return `${name} performed an action`;
            }
        }

        function getActivityIcon(type) {
            const icons = {
                user_registered: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><line x1="19" y1="8" x2="19" y2="14"></line><line x1="22" y1="11" x2="16" y2="11"></line></svg>',
                car_listed: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>',
                booking_created: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>',
                booking_received: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>'
            };
            return icons[type] || icons.booking_created;
        }

        function getTimeAgo(timestamp) {
            const now = new Date();
            const time = new Date(timestamp);
            const diffMs = now - time;
            const diffMins = Math.floor(diffMs / 60000);
            const diffHours = Math.floor(diffMs / 3600000);
            const diffDays = Math.floor(diffMs / 86400000);
            
            if (diffMins < 1) return 'Just now';
            if (diffMins < 60) return `${diffMins} minutes ago`;
            if (diffHours < 24) return `${diffHours} hours ago`;
            return `${diffDays} days ago`;
        }

        async function loadDashboardBookings() {
            try {
                const searchTerm = document.getElementById('dashboard-bookings-search')?.value || '';
                const params = new URLSearchParams({
                    search: searchTerm,
                    limit: 5
                });
                
                const response = await apiCall(`admin.php?action=bookings&${params}`);
                const bookings = response.data.bookings;
                
                renderDashboardBookings(bookings);
            } catch (error) {
                document.getElementById('dashboard-bookings-table').innerHTML = `
                    <div style="text-align: center; color: var(--error); padding: 2rem;">
                        Failed to load bookings: ${error.message}
                    </div>
                `;
            }
        }

        function renderDashboardBookings(bookings) {
            const container = document.getElementById('dashboard-bookings-table');
            
            if (bookings.length === 0) {
                container.innerHTML = `
                    <div style="text-align: center; color: var(--gray-500); padding: 2rem;">
                        No bookings found
                    </div>
                `;
                return;
            }
            
            container.innerHTML = `
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Customer</th>
                            <th>Car</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${bookings.map(booking => `
                            <tr>
                                <td>#${booking.id}</td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <div class="avatar-sm">${booking.first_name.charAt(0)}${booking.last_name.charAt(0)}</div>
                                        ${booking.first_name} ${booking.last_name}
                                    </div>
                                </td>
                                <td>${booking.make} ${booking.model}</td>
                                <td>${formatDate(booking.start_date)}</td>
                                <td>$${parseFloat(booking.total_amount).toFixed(2)}</td>
                                <td><span class="status-badge ${booking.status}">${booking.status.charAt(0).toUpperCase() + booking.status.slice(1)}</span></td>
                                <td>
                                    <button class="btn btn-secondary btn-sm" onclick="viewBooking(${booking.id})" title="${IS_ADMIN ? 'View Details' : 'Contact Customer'}">
                                        ${IS_ADMIN ? 
                                            `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                <circle cx="12" cy="12" r="3"></circle>
                                            </svg>` :
                                            `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                            </svg>`
                                        }
                                        ${IS_ADMIN ? 'View' : 'Contact'}
                                    </button>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;
        }

        async function loadCars() {
            try {
                const searchTerm = document.getElementById('cars-search')?.value || '';
                const params = new URLSearchParams({
                    search: searchTerm,
                    page: 1
                });
                
                const response = await apiCall(`admin.php?action=cars&${params}`);
                const cars = response.data.cars;
                
                renderCarsTable(cars);
            } catch (error) {
                document.getElementById('cars-table').innerHTML = `
                    <div style="text-align: center; color: var(--error); padding: 2rem;">
                        Failed to load cars: ${error.message}
                    </div>
                `;
            }
        }

        function renderCarsTable(cars) {
            const container = document.getElementById('cars-table');
            
            if (cars.length === 0) {
                container.innerHTML = `
                    <div style="text-align: center; color: var(--gray-500); padding: 2rem;">
                        No cars found
                    </div>
                `;
                return;
            }
            
            container.innerHTML = `
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Car ID</th>
                            ${IS_ADMIN ? '<th>Owner</th>' : ''}
                            <th>Make & Model</th>
                            <th>Year</th>
                            <th>Price/Day</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${cars.map(car => `
                            <tr>
                                <td>#${car.id}</td>
                                ${IS_ADMIN ? `
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <div class="avatar-sm">${car.first_name.charAt(0)}${car.last_name.charAt(0)}</div>
                                            ${car.first_name} ${car.last_name}
                                        </div>
                                    </td>
                                ` : ''}
                                <td>${car.make} ${car.model}</td>
                                <td>${car.year}</td>
                                <td>$${parseFloat(car.price_per_day).toFixed(2)}</td>
                                <td><span class="status-badge ${car.status}">${car.status.charAt(0).toUpperCase() + car.status.slice(1)}</span></td>
                                <td>
                                    <button class="btn btn-secondary btn-sm" onclick="viewCar(${car.id})" title="Edit Car">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                    </button>
                                    ${(IS_ADMIN || car.user_id == CURRENT_USER_ID) ? `<button class="btn btn-danger btn-sm" onclick="deleteCar(${car.id})" title="Delete Car" style="margin-left: 4px;">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            <line x1="10" y1="11" x2="10" y2="17"></line>
                                            <line x1="14" y1="11" x2="14" y2="17"></line>
                                        </svg>
                                    </button>` : ''}
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;
        }

        async function loadUsers() {
            if (!IS_ADMIN) return;
            
            try {
                const searchTerm = document.getElementById('users-search')?.value || '';
                const params = new URLSearchParams({
                    search: searchTerm,
                    page: 1
                });
                
                const response = await apiCall(`admin.php?action=users&${params}`);
                const users = response.data.users;
                
                renderUsersTable(users);
            } catch (error) {
                document.getElementById('users-table').innerHTML = `
                    <div style="text-align: center; color: var(--error); padding: 2rem;">
                        Failed to load users: ${error.message}
                    </div>
                `;
            }
        }

        function renderUsersTable(users) {
            const container = document.getElementById('users-table');
            
            if (users.length === 0) {
                container.innerHTML = `
                    <div style="text-align: center; color: var(--gray-500); padding: 2rem;">
                        No users found
                    </div>
                `;
                return;
            }
            
            container.innerHTML = `
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${users.map(user => `
                            <tr>
                                <td>#${user.id}</td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <div class="avatar-sm">${user.first_name.charAt(0)}${user.last_name.charAt(0)}</div>
                                        ${user.first_name} ${user.last_name}
                                    </div>
                                </td>
                                <td>${user.email}</td>
                                <td>${user.phone || 'N/A'}</td>
                                <td><span class="status-badge ${user.role === 'admin' ? 'error' : user.role === 'host' ? 'warning' : 'active'}">${user.role.charAt(0).toUpperCase() + user.role.slice(1)}</span></td>
                                <td><span class="status-badge ${user.is_verified ? 'active' : 'inactive'}">${user.is_verified ? 'Verified' : 'Unverified'}</span></td>
                                <td>
                                    <button class="btn btn-secondary btn-sm" onclick="editUser(${user.id})" title="Edit User">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                    </button>
                                    <button class="btn btn-${user.is_verified ? 'warning' : 'success'} btn-sm" onclick="toggleUserStatus(${user.id}, ${user.is_verified})" title="${user.is_verified ? 'Suspend User' : 'Activate User'}" style="margin-left: 4px;">
                                        ${user.is_verified ? 
                                            `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <path d="M16 16l-4-4-4 4"></path>
                                                <path d="M12 8V2"></path>
                                            </svg>` : 
                                            `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                            </svg>`
                                        }
                                    </button>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;
        }

        async function loadBookings() {
            try {
                const searchTerm = document.getElementById('bookings-search')?.value || '';
                const status = document.getElementById('bookings-status-filter')?.value || '';
                const params = new URLSearchParams({
                    search: searchTerm,
                    status: status,
                    page: 1
                });
                
                const response = await apiCall(`admin.php?action=bookings&${params}`);
                const bookings = response.data.bookings;
                
                renderBookingsTable(bookings);
            } catch (error) {
                document.getElementById('bookings-table').innerHTML = `
                    <div style="text-align: center; color: var(--error); padding: 2rem;">
                        Failed to load bookings: ${error.message}
                    </div>
                `;
            }
        }

        function renderBookingsTable(bookings) {
            const container = document.getElementById('bookings-table');
            
            if (bookings.length === 0) {
                container.innerHTML = `
                    <div style="text-align: center; color: var(--gray-500); padding: 2rem;">
                        No bookings found
                    </div>
                `;
                return;
            }
            
            container.innerHTML = `
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Customer</th>
                            <th>Car</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${bookings.map(booking => `
                            <tr>
                                <td>#${booking.id}</td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <div class="avatar-sm">${booking.first_name.charAt(0)}${booking.last_name.charAt(0)}</div>
                                        ${booking.first_name} ${booking.last_name}
                                    </div>
                                </td>
                                <td>${booking.make} ${booking.model}</td>
                                <td>${formatDate(booking.start_date)}</td>
                                <td>${formatDate(booking.end_date)}</td>
                                <td>$${parseFloat(booking.total_amount).toFixed(2)}</td>
                                <td><span class="status-badge ${booking.status}">${booking.status.charAt(0).toUpperCase() + booking.status.slice(1)}</span></td>
                                <td>
                                    <button class="btn btn-secondary btn-sm" onclick="viewBooking(${booking.id})" title="${IS_ADMIN ? 'View Details' : 'Contact Customer'}">
                                        ${IS_ADMIN ? 
                                            `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                <circle cx="12" cy="12" r="3"></circle>
                                            </svg>` :
                                            `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                            </svg>`
                                        }
                                        ${IS_ADMIN ? 'View' : 'Contact'}
                                    </button>
                                    ${booking.status === 'pending' ? `<button class="btn btn-success btn-sm" onclick="updateBookingStatus(${booking.id}, 'confirmed')" title="Approve Booking" style="margin-left: 4px;">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                        </svg>
                                    </button>` : ''}
                                    ${booking.status !== 'cancelled' && booking.status !== 'completed' ? `<button class="btn btn-warning btn-sm" onclick="updateBookingStatus(${booking.id}, 'cancelled')" title="Cancel Booking" style="margin-left: 4px;">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <line x1="15" y1="9" x2="9" y2="15"></line>
                                            <line x1="9" y1="9" x2="15" y2="15"></line>
                                        </svg>
                                    </button>` : ''}
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;
        }

        // Utility functions
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric' 
            });
        }

        // Action functions
        async function updateBookingStatus(bookingId, status) {
            try {
                const formData = new FormData();
                formData.append('booking_id', bookingId);
                formData.append('status', status);
                
                await apiCall('admin.php?action=update-booking-status', {
                    method: 'POST',
                    body: formData
                });
                
                // Reload bookings
                if (currentSection === 'bookings') {
                    loadBookings();
                } else {
                    loadDashboardBookings();
                }
                
                showNotification('Booking status updated successfully', 'success');
            } catch (error) {
                showNotification('Failed to update booking status: ' + error.message, 'error');
            }
        }

        async function viewBooking(bookingId) {
            try {
                const response = await apiCall(`admin.php?action=booking-details&id=${bookingId}`);
                const booking = response.data;
                
                if (IS_ADMIN) {
                    // Show detailed booking information for admin
                    showBookingDetailsModal(booking);
                } else {
                    // Show contact information for host
                    showContactModal(booking);
                }
            } catch (error) {
                showNotification('Failed to load booking details: ' + error.message, 'error');
            }
        }

        function showContactModal(booking) {
            const modal = document.createElement('div');
            modal.className = 'modal-overlay';
            modal.innerHTML = `
                <div class="modal-content" style="max-width: 400px;">
                    <div class="modal-header">
                        <h3>Customer Contact Information</h3>
                        <button class="modal-close" onclick="this.closest('.modal-overlay').remove()">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="contact-info">
                            <div class="contact-item">
                                <strong>Customer:</strong> ${booking.first_name} ${booking.last_name}
                            </div>
                            <div class="contact-item">
                                <strong>Email:</strong> 
                                <a href="mailto:${booking.email}">${booking.email}</a>
                            </div>
                            <div class="contact-item">
                                <strong>Phone:</strong> 
                                <a href="tel:${booking.phone}">${booking.phone}</a>
                            </div>
                            <div class="contact-item">
                                <strong>Car:</strong> ${booking.make} ${booking.model} (${booking.year})
                            </div>
                            <div class="contact-item">
                                <strong>Dates:</strong> ${formatDate(booking.start_date)} - ${formatDate(booking.end_date)}
                            </div>
                            <div class="contact-item">
                                <strong>Status:</strong> 
                                <span class="status-badge ${booking.status}">${booking.status.charAt(0).toUpperCase() + booking.status.slice(1)}</span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        }

        function showBookingDetailsModal(booking) {
            const modal = document.createElement('div');
            modal.className = 'modal-overlay';
            modal.innerHTML = `
                <div class="modal-content" style="max-width: 600px;">
                    <div class="modal-header">
                        <h3>Booking Details #${booking.id}</h3>
                        <button class="modal-close" onclick="this.closest('.modal-overlay').remove()">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="booking-details">
                            <div class="detail-row">
                                <strong>Customer:</strong> ${booking.first_name} ${booking.last_name}
                            </div>
                            <div class="detail-row">
                                <strong>Email:</strong> ${booking.email}
                            </div>
                            <div class="detail-row">
                                <strong>Phone:</strong> ${booking.phone}
                            </div>
                            <div class="detail-row">
                                <strong>Car:</strong> ${booking.make} ${booking.model} (${booking.year})
                            </div>
                            <div class="detail-row">
                                <strong>Rental Period:</strong> ${formatDate(booking.start_date)} - ${formatDate(booking.end_date)}
                            </div>
                            <div class="detail-row">
                                <strong>Total Amount:</strong> $${parseFloat(booking.total_amount).toFixed(2)}
                            </div>
                            <div class="detail-row">
                                <strong>Security Deposit:</strong> $${parseFloat(booking.security_deposit).toFixed(2)}
                            </div>
                            <div class="detail-row">
                                <strong>Pickup Location:</strong> ${booking.pickup_location}
                            </div>
                            <div class="detail-row">
                                <strong>Status:</strong> 
                                <span class="status-badge ${booking.status}">${booking.status.charAt(0).toUpperCase() + booking.status.slice(1)}</span>
                            </div>
                            ${booking.notes ? `<div class="detail-row"><strong>Notes:</strong> ${booking.notes}</div>` : ''}
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        }

        async function viewCar(carId) {
            try {
                const response = await apiCall(`admin.php?action=car-details&id=${carId}`);
                const car = response.data;
                showEditCarModal(car);
            } catch (error) {
                showNotification('Failed to load car details: ' + error.message, 'error');
            }
        }

        function showEditCarModal(car) {
            const modal = document.createElement('div');
            modal.className = 'modal-overlay';
            modal.innerHTML = `
                <div class="modal-content" style="max-width: 700px;">
                    <div class="modal-header">
                        <h3>Edit Car - ${car.make} ${car.model}</h3>
                        <button class="modal-close" onclick="this.closest('.modal-overlay').remove()">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="editCarForm">
                            <input type="hidden" name="car_id" value="${car.id}">
                            
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div class="form-group">
                                    <label>Make:</label>
                                    <input type="text" name="make" value="${car.make}" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Model:</label>
                                    <input type="text" name="model" value="${car.model}" class="form-control" required>
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div class="form-group">
                                    <label>Year:</label>
                                    <input type="number" name="year" value="${car.year}" class="form-control" min="1900" max="2030" required>
                                </div>
                                <div class="form-group">
                                    <label>Vehicle Type:</label>
                                    <select name="vehicle_type" class="form-control" required>
                                        <option value="sedan" ${car.vehicle_type === 'sedan' ? 'selected' : ''}>Sedan</option>
                                        <option value="suv" ${car.vehicle_type === 'suv' ? 'selected' : ''}>SUV</option>
                                        <option value="hatchback" ${car.vehicle_type === 'hatchback' ? 'selected' : ''}>Hatchback</option>
                                        <option value="convertible" ${car.vehicle_type === 'convertible' ? 'selected' : ''}>Convertible</option>
                                        <option value="coupe" ${car.vehicle_type === 'coupe' ? 'selected' : ''}>Coupe</option>
                                        <option value="truck" ${car.vehicle_type === 'truck' ? 'selected' : ''}>Truck</option>
                                        <option value="van" ${car.vehicle_type === 'van' ? 'selected' : ''}>Van</option>
                                    </select>
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div class="form-group">
                                    <label>Transmission:</label>
                                    <select name="transmission" class="form-control" required>
                                        <option value="manual" ${car.transmission === 'manual' ? 'selected' : ''}>Manual</option>
                                        <option value="automatic" ${car.transmission === 'automatic' ? 'selected' : ''}>Automatic</option>
                                        <option value="cvt" ${car.transmission === 'cvt' ? 'selected' : ''}>CVT</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Fuel Type:</label>
                                    <select name="fuel_type" class="form-control" required>
                                        <option value="petrol" ${car.fuel_type === 'petrol' ? 'selected' : ''}>Petrol</option>
                                        <option value="diesel" ${car.fuel_type === 'diesel' ? 'selected' : ''}>Diesel</option>
                                        <option value="electric" ${car.fuel_type === 'electric' ? 'selected' : ''}>Electric</option>
                                        <option value="hybrid" ${car.fuel_type === 'hybrid' ? 'selected' : ''}>Hybrid</option>
                                    </select>
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div class="form-group">
                                    <label>Seating Capacity:</label>
                                    <select name="seating_capacity" class="form-control" required>
                                        <option value="2" ${car.seating_capacity == 2 ? 'selected' : ''}>2 Seats</option>
                                        <option value="4" ${car.seating_capacity == 4 ? 'selected' : ''}>4 Seats</option>
                                        <option value="5" ${car.seating_capacity == 5 ? 'selected' : ''}>5 Seats</option>
                                        <option value="7" ${car.seating_capacity == 7 ? 'selected' : ''}>7 Seats</option>
                                        <option value="8" ${car.seating_capacity == 8 ? 'selected' : ''}>8 Seats</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Status:</label>
                                    <select name="status" class="form-control" required>
                                        <option value="active" ${car.status === 'active' ? 'selected' : ''}>Active</option>
                                        <option value="inactive" ${car.status === 'inactive' ? 'selected' : ''}>Inactive</option>
                                        <option value="maintenance" ${car.status === 'maintenance' ? 'selected' : ''}>Maintenance</option>
                                    </select>
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div class="form-group">
                                    <label>Price per Day ($):</label>
                                    <input type="number" name="price_per_day" value="${car.price_per_day}" class="form-control" min="1" step="0.01" required>
                                </div>
                                <div class="form-group">
                                    <label>Security Deposit ($):</label>
                                    <input type="number" name="security_deposit" value="${car.security_deposit}" class="form-control" min="0" step="0.01" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Pickup Location:</label>
                                <input type="text" name="pickup_location" value="${car.pickup_location}" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label>Description:</label>
                                <textarea name="description" class="form-control" rows="4" style="resize: vertical;">${car.description || ''}</textarea>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div class="form-group">
                                    <label>Available From:</label>
                                    <input type="date" name="available_from" value="${car.available_from ? car.available_from.split(' ')[0] : ''}" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Available To:</label>
                                    <input type="date" name="available_to" value="${car.available_to ? car.available_to.split(' ')[0] : ''}" class="form-control" required>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Update Car</button>
                                <button type="button" class="btn btn-secondary" onclick="this.closest('.modal-overlay').remove()">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
            
            document.getElementById('editCarForm').addEventListener('submit', updateCar);
        }

        async function updateCar(e) {
            e.preventDefault();
            const formData = new FormData(e.target);
            
            try {
                await apiCall('admin.php?action=update-car', {
                    method: 'POST',
                    body: formData
                });
                
                showNotification('Car updated successfully', 'success');
                e.target.closest('.modal-overlay').remove();
                loadCars(); // Reload cars list
            } catch (error) {
                showNotification('Failed to update car: ' + error.message, 'error');
            }
        }

        async function deleteCar(carId) {
            if (confirm('Are you sure you want to delete this car? This action cannot be undone.')) {
                try {
                    const formData = new FormData();
                    formData.append('car_id', carId);
                    
                    await apiCall('admin.php?action=delete-car', {
                        method: 'POST',
                        body: formData
                    });
                    
                    showNotification('Car deleted successfully', 'success');
                    loadCars(); // Reload cars list
                } catch (error) {
                    showNotification('Failed to delete car: ' + error.message, 'error');
                }
            }
        }

        async function editUser(userId) {
            try {
                const response = await apiCall(`admin.php?action=user-details&id=${userId}`);
                const user = response.data;
                showEditUserModal(user);
            } catch (error) {
                showNotification('Failed to load user details: ' + error.message, 'error');
            }
        }

        function showEditUserModal(user) {
            const modal = document.createElement('div');
            modal.className = 'modal-overlay';
            modal.innerHTML = `
                <div class="modal-content" style="max-width: 500px;">
                    <div class="modal-header">
                        <h3>Edit User</h3>
                        <button class="modal-close" onclick="this.closest('.modal-overlay').remove()">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="editUserForm">
                            <input type="hidden" name="user_id" value="${user.id}">
                            <div class="form-group">
                                <label>First Name:</label>
                                <input type="text" name="first_name" value="${user.first_name}" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Last Name:</label>
                                <input type="text" name="last_name" value="${user.last_name}" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Email:</label>
                                <input type="email" name="email" value="${user.email}" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Phone:</label>
                                <input type="text" name="phone" value="${user.phone || ''}" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>Role:</label>
                                <select name="role" class="form-control" required>
                                    <option value="renter" ${user.role === 'renter' ? 'selected' : ''}>Renter</option>
                                    <option value="host" ${user.role === 'host' ? 'selected' : ''}>Host</option>
                                    <option value="admin" ${user.role === 'admin' ? 'selected' : ''}>Admin</option>
                                </select>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Update User</button>
                                <button type="button" class="btn btn-secondary" onclick="this.closest('.modal-overlay').remove()">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
            
            document.getElementById('editUserForm').addEventListener('submit', updateUser);
        }

        async function updateUser(e) {
            e.preventDefault();
            const formData = new FormData(e.target);
            
            try {
                await apiCall('admin.php?action=update-user', {
                    method: 'POST',
                    body: formData
                });
                
                showNotification('User updated successfully', 'success');
                e.target.closest('.modal-overlay').remove();
                loadUsers(); // Reload users list
            } catch (error) {
                showNotification('Failed to update user: ' + error.message, 'error');
            }
        }

        async function toggleUserStatus(userId, currentStatus) {
            const action = currentStatus ? 'suspend' : 'activate';
            if (confirm(`Are you sure you want to ${action} this user?`)) {
                try {
                    const formData = new FormData();
                    formData.append('user_id', userId);
                    formData.append('is_verified', currentStatus ? '0' : '1');
                    
                    await apiCall('admin.php?action=toggle-user-status', {
                        method: 'POST',
                        body: formData
                    });
                    
                    showNotification(`User ${action}d successfully`, 'success');
                    loadUsers(); // Reload users list
                } catch (error) {
                    showNotification(`Failed to ${action} user: ` + error.message, 'error');
                }
            }
        }

        function showAddUserModal() {
            const modal = document.createElement('div');
            modal.className = 'modal-overlay';
            modal.innerHTML = `
                <div class="modal-content" style="max-width: 500px;">
                    <div class="modal-header">
                        <h3>Add New User</h3>
                        <button class="modal-close" onclick="this.closest('.modal-overlay').remove()">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="addUserForm">
                            <div class="form-group">
                                <label>First Name:</label>
                                <input type="text" name="first_name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Last Name:</label>
                                <input type="text" name="last_name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Email:</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Phone:</label>
                                <input type="text" name="phone" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>Password:</label>
                                <input type="password" name="password" class="form-control" required minlength="6">
                            </div>
                            <div class="form-group">
                                <label>Role:</label>
                                <select name="role" class="form-control" required>
                                    <option value="renter">Renter</option>
                                    <option value="host">Host</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Add User</button>
                                <button type="button" class="btn btn-secondary" onclick="this.closest('.modal-overlay').remove()">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
            
            document.getElementById('addUserForm').addEventListener('submit', addUser);
        }

        async function addUser(e) {
            e.preventDefault();
            const formData = new FormData(e.target);
            
            try {
                await apiCall('admin.php?action=add-user', {
                    method: 'POST',
                    body: formData
                });
                
                showNotification('User added successfully', 'success');
                e.target.closest('.modal-overlay').remove();
                loadUsers(); // Reload users list
            } catch (error) {
                showNotification('Failed to add user: ' + error.message, 'error');
            }
        }

        // Notification system
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            notification.innerHTML = `
                <div class="notification-content">
                    <span>${message}</span>
                    <button class="notification-close" onclick="this.parentElement.parentElement.remove()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 5000);
        }
    </script>
</body>
</html>