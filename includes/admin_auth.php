<?php
function require_admin() {
    require_login();
    $user = get_logged_in_user();
    if (!$user || $user['role'] !== 'admin') {
        if (is_ajax_request()) {
            error_response('Access denied. Admin privileges required.', 403);
        } else {
            redirect('login');
        }
    }
}

function require_admin_or_host() {
    require_login();
    $user = get_logged_in_user();
    if (!$user || !in_array($user['role'], ['admin', 'host'])) {
        if (is_ajax_request()) {
            error_response('Access denied. Admin or Host privileges required.', 403);
        } else {
            redirect('login');
        }
    }
}

function is_ajax_request() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

function get_user_role() {
    $user = get_logged_in_user();
    return $user ? $user['role'] : null;
}
?>