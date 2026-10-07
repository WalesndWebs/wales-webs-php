<?php
declare(strict_types=1);

const REQUEST_STATUSES = ['new', 'reviewing', 'needs_info', 'proposal_sent', 'accepted', 'in_progress', 'completed', 'closed'];
const CONTACT_STATUSES = ['new', 'reviewing', 'replied', 'closed'];
const SERVICE_LABELS = ['digital_presence' => 'Digital Presence', 'business_systems' => 'Business Systems', 'automation_ai' => 'Automation & AI', 'digital_growth' => 'Digital Growth', 'custom_web_app' => 'Custom Web App / Portal', 'other' => 'Other / Let’s discuss'];

function published_posts(): array {
    return all_rows('blog_posts', 'status = ?', ['published'], 'published_at DESC, created_at DESC');
}
function contact_page(string $method): void {
    $errors = []; $values = [];
    $receipt = isset($_GET['received']) ? ($_SESSION['contact_receipt'] ?? null) : null;
    if ($method === 'POST') {
        $receipt = null;
        $values = [
            'full_name' => text_input($_POST, 'full_name', 2, 120, $errors, 'Your name'),
            'email' => text_input($_POST, 'email', 3, 254, $errors, 'Email'),
            'phone_number' => text_input($_POST, 'phone_number', 0, 40, $errors, 'Phone number'),
            'service' => text_input($_POST, 'service', 1, 60, $errors, 'Service'),
            'budget' => text_input($_POST, 'budget', 0, 120, $errors, 'Project scope'),
            'message' => text_input($_POST, 'message', 10, 5000, $errors, 'Message'),
        ];
        if (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
        if (!isset(SERVICE_LABELS[$values['service']])) $errors[] = 'Choose one of the listed services.';
        if (empty($_POST['privacy_accepted'])) $errors[] = 'Please accept the privacy notice.';
        if (limited('contact', 5)) { http_response_code(429); $errors[] = 'Too many submissions. Please wait 15 minutes before trying again.'; }
        if (!$errors) {
            $reference = 'IN-' . strtoupper(bin2hex(random_bytes(5)));
            save_row('contact_inquiries', [...$values, 'reference' => $reference, 'privacy_accepted' => true]);
            $_SESSION['contact_receipt'] = ['reference' => $reference];
            redirect('/contact?received=1');
        }
        if (http_response_code() !== 429) http_response_code(422);
    }
    render('contact', compact('errors', 'values', 'receipt') + ['pageTitle' => 'Contact Us | Wales & Webs', 'pageDescription' => 'Tell Wale what you want to build. We review your inquiry privately and reply personally.']);
}
function request_input(array $source, array &$errors): array {
    $data = [];
    foreach ([
        'full_name' => [2, 120, 'Full name'], 'phone_number' => [5, 40, 'Phone number'],
        'business_name' => [2, 160, 'Business name'], 'business_type' => [2, 120, 'Business type'],
        'business_description' => [10, 3000, 'Business description'], 'industry' => [2, 120, 'Industry'],
        'location' => [2, 200, 'Location'], 'public_contact' => [0, 1000, 'Public contact'],
        'brand_colors' => [0, 300, 'Brand colours'], 'preferred_timeline' => [2, 120, 'Timeline'],
        'additional_details' => [0, 3000, 'Additional details'],
    ] as $key => [$min, $max, $label]) $data[$key] = text_input($source, $key, $min, $max, $errors, $label);
    $data['email'] = text_input($source, 'email', 0, 254, $errors, 'Email') ?: null;
    if ($data['email'] && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid optional email address.';
    $data['service_type'] = text_input($source, 'service_type', 1, 60, $errors, 'Support type');
    if (!in_array($data['service_type'], ['business_profile', 'digital_presence', 'business_blog'], true)) $errors[] = 'Choose a listed support type.';
    $data['target_date'] = date_input($source['target_date'] ?? null, $errors, 'Target date');
    $data['asset_links'] = links_input($source['asset_links'] ?? '', $errors, 'Asset links');
    $data['website_references'] = links_input($source['website_references'] ?? '', $errors, 'Website references');
    $data['privacy_accepted'] = !empty($source['privacy_accepted']);
    if (!$data['privacy_accepted']) $errors[] = 'Please accept the privacy notice.';
    return $data;
}
function request_page(string $method): void {
    $errors = []; $values = ['service_type' => 'business_profile'];
    $receipt = isset($_GET['received']) ? ($_SESSION['request_receipt'] ?? null) : null;
    if ($method === 'POST') {
        $receipt = null; $values = request_input($_POST, $errors);
        if (limited('usabime', 5)) { http_response_code(429); $errors[] = 'Too many submissions. Please wait 15 minutes before trying again.'; }
        if (!$errors) {
            $reference = 'US-' . strtoupper(bin2hex(random_bytes(5)));
            save_row('usabime_requests', [...$values, 'reference' => $reference]);
            $_SESSION['request_receipt'] = ['reference' => $reference, 'name' => $values['full_name']];
            redirect('/usabime/request?received=1');
        }
        if (http_response_code() !== 429) http_response_code(422);
    }
    render('request', compact('errors', 'values', 'receipt') + ['pageTitle' => 'Start your Usabime request | Wales & Webs']);
}
function login_page(string $method): void {
    header('X-Robots-Tag: noindex, nofollow');
    $next = safe_next(is_string($_POST['next'] ?? null) ? $_POST['next'] : '/usabime/admin');
    $errors = [];
    if ($method === 'POST') {
        $expected = setting('blog_admin_password');
        $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
        if (!$expected) { http_response_code(503); $errors[] = 'The admin password has not been configured.'; }
        elseif (limited('admin-login', 10)) { http_response_code(429); $errors[] = 'Too many sign-in attempts. Please wait 15 minutes.'; }
        elseif (!hash_equals($expected, $password)) { http_response_code(401); $errors[] = 'Access could not be verified. Check your password and try again.'; }
        else {
            session_regenerate_id(true);
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
            $_SESSION['admin_fingerprint'] = hash('sha256', $expected);
            $_SESSION['admin_seen'] = time();
            redirect($next);
        }
    }
    render('login', ['admin' => true, 'pageTitle' => 'Private sign in', 'next' => $next, 'errors' => $errors]);
}
function contact_admin_page(string $method): void {
    $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT) ?: null;
    require_admin('/admin/contact' . ($id ? '?id=' . $id : ''));
    $errors = []; $feedback = isset($_GET['saved']) ? 'Inquiry review saved. No email was sent.' : '';
    $inquiry = $id ? find_row('contact_inquiries', $id) : null;
    if ($id && !$inquiry) { http_response_code(404); render('error', ['admin' => true, 'pageTitle' => 'Inquiry not found', 'message' => 'This inquiry does not exist.']); return; }
    if ($method === 'POST' && $inquiry) {
        $status = text_input($_POST, 'status', 1, 40, $errors, 'Status');
        $notes = text_input($_POST, 'admin_notes', 0, 5000, $errors, 'Internal notes');
        if (!in_array($status, CONTACT_STATUSES, true)) $errors[] = 'Choose a listed status.';
        if (!$errors) { save_row('contact_inquiries', ['status' => $status, 'admin_notes' => $notes], $id); redirect('/admin/contact?id=' . $id . '&saved=1'); }
        $inquiry['status'] = $status; $inquiry['admin_notes'] = $notes;
        http_response_code(422);
    }
    $inquiries = all_rows('contact_inquiries');
    if (!$inquiry && $inquiries) $inquiry = $inquiries[0];
    render('contact-admin', compact('inquiries', 'inquiry', 'errors', 'feedback') + ['admin' => true, 'pageTitle' => 'Contact inbox | Wales & Webs']);
}
function blank_proposal(array $request): array {
    return ['title' => mb_substr('Project proposal for ' . $request['business_name'], 0, 180), 'introduction' => '', 'scope' => '', 'currency' => '₦', 'line_items' => [['description' => '', 'quantity' => 1, 'unitPrice' => '']], 'payment_terms' => '', 'timeline' => '', 'valid_until' => null, 'terms' => '', 'status' => 'draft'];
}
function proposal_input(array $source, array &$errors): array {
    $data = [];
    foreach (['title' => [2, 180], 'introduction' => [0, 3000], 'scope' => [0, 5000], 'currency' => [1, 24], 'payment_terms' => [0, 2000], 'timeline' => [0, 500], 'terms' => [0, 5000], 'status' => [1, 20]] as $key => [$min, $max]) {
        $data[$key] = text_input($source, $key, $min, $max, $errors, ucfirst(str_replace('_', ' ', $key)));
    }
    if (!in_array($data['status'], ['draft', 'sent', 'accepted', 'declined'], true)) $errors[] = 'Choose a listed proposal status.';
    $data['valid_until'] = date_input($source['valid_until'] ?? null, $errors, 'Proposal expiry');
    $lines = is_array($source['lines'] ?? null) ? array_values($source['lines']) : [];
    $data['line_items'] = [];
    if (count($lines) < 1 || count($lines) > 40) $errors[] = 'Add between 1 and 40 proposal items.';
    foreach (array_slice($lines, 0, 40) as $index => $line) {
        if (!is_array($line)) { $errors[] = 'Invalid proposal line.'; continue; }
        $description = text_input($line, 'description', 1, 300, $errors, 'Item ' . ($index + 1) . ' description');
        $price = text_input($line, 'unitPrice', 1, 13, $errors, 'Unit price');
        if (!preg_match('/^[0-9]{1,10}(\.[0-9]{1,2})?$/', $price)) $errors[] = 'Each unit price must be a non-negative decimal, with no more than two decimal places and ten digits before the decimal.';
        $quantity = filter_var($line['quantity'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 10000]]);
        if ($quantity === false) $errors[] = 'Each quantity must be a whole number between 1 and 10,000.';
        $data['line_items'][] = ['description' => $description, 'quantity' => $quantity ?: 1, 'unitPrice' => $price];
    }
    return $data;
}
function request_admin_page(string $method): void {
    $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT) ?: null;
    require_admin('/usabime/admin' . ($id ? '?id=' . $id : ''));
    $errors = []; $feedback = '';
    $requests = all_rows('usabime_requests');
    $request = $id ? find_row('usabime_requests', $id) : ($requests[0] ?? null);
    if ($id && !$request) { http_response_code(404); render('error', ['admin' => true, 'pageTitle' => 'Request not found', 'message' => 'This request does not exist.']); return; }
    $id = $request ? (int) $request['id'] : null;
    $saved = $id ? (all_rows('usabime_proposals', 'request_id = ?', [$id])[0] ?? null) : null;
    $proposal = $saved ?? ($request ? blank_proposal($request) : null);
    if ($method === 'POST' && $request) {
        $action = $_POST['action'] ?? '';
        if ($action === 'review') {
            $status = text_input($_POST, 'status', 1, 40, $errors, 'Request status');
            $notes = text_input($_POST, 'admin_notes', 0, 5000, $errors, 'Internal notes');
            if (!in_array($status, REQUEST_STATUSES, true)) $errors[] = 'Choose a listed request status.';
            if (!$errors) { save_row('usabime_requests', ['status' => $status, 'admin_notes' => $notes], $id); redirect('/usabime/admin?id=' . $id . '&saved=review'); }
            $request['status'] = $status; $request['admin_notes'] = $notes;
        } elseif (in_array($action, ['proposal', 'print'], true)) {
            $proposal = proposal_input($_POST, $errors);
            if (!$errors) {
                db()->beginTransaction();
                query('SELECT id FROM usabime_requests WHERE id = ? FOR UPDATE', [$id]);
                $existing = all_rows('usabime_proposals', 'request_id = ?', [$id])[0] ?? null;
                save_row('usabime_proposals', [...$proposal, 'request_id' => $id], $existing ? (int) $existing['id'] : null);
                $status = match ($proposal['status']) { 'sent' => 'proposal_sent', 'accepted' => 'accepted', 'declined' => 'closed', default => null };
                if ($status) save_row('usabime_requests', ['status' => $status], $id);
                db()->commit();
                redirect('/usabime/admin?id=' . $id . '&saved=proposal' . ($action === 'print' ? '&print=1' : ''));
            }
        } else $errors[] = 'Choose a valid action.';
        http_response_code(422);
    }
    if (isset($_GET['saved'])) $feedback = $_GET['saved'] === 'review' ? 'Request review saved.' : 'Proposal saved. Sending remains manual.';
    $printNow = isset($_GET['print']) && $saved !== null;
    render('request-admin', compact('requests', 'request', 'proposal', 'saved', 'errors', 'feedback', 'printNow') + ['admin' => true, 'pageTitle' => 'Usabime request desk | Wales & Webs']);
}
function unique_slug(string $title, ?int $id): string {
    $base = substr(trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($title)), '-'), 0, 80) ?: 'wales-webs-story';
    $slug = $base; $suffix = 2;
    while ($row = query('SELECT id FROM blog_posts WHERE slug = ?', [$slug])->fetch()) {
        if ($id && (int) $row['id'] === $id) break;
        $slug = $base . '-' . $suffix++;
    }
    return $slug;
}
function blog_admin_page(string $method): void {
    $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT) ?: null;
    require_admin('/journal/studio' . ($id ? '?id=' . $id : ''));
    $errors = []; $feedback = isset($_GET['saved']) ? 'Story saved.' : (isset($_GET['deleted']) ? 'Story deleted.' : '');
    $post = $id ? find_row('blog_posts', $id) : null;
    if ($id && !$post) { http_response_code(404); render('error', ['admin' => true, 'pageTitle' => 'Story not found', 'message' => 'This story does not exist.']); return; }
    if ($method === 'POST') {
        if (($_POST['action'] ?? '') === 'delete' && $id) {
            query('DELETE FROM blog_posts WHERE id = ?', [$id]); redirect('/journal/studio?deleted=1');
        }
        $input = [];
        foreach (['title' => [2, 180], 'client_name' => [2, 160], 'category' => [2, 120], 'excerpt' => [10, 500], 'body' => [30, 20000]] as $key => [$min, $max]) $input[$key] = text_input($_POST, $key, $min, $max, $errors, ucfirst(str_replace('_', ' ', $key)));
        $serviceText = text_input($_POST, 'services', 0, 2000, $errors, 'Services');
        $input['services'] = array_values(array_filter(array_map('trim', explode(',', $serviceText))));
        if (count($input['services']) > 20 || array_filter($input['services'], fn($s) => mb_strlen($s) > 120)) $errors[] = 'Enter at most 20 service labels, each up to 120 characters.';
        $input['accent'] = text_input($_POST, 'accent', 1, 20, $errors, 'Accent');
        $input['status'] = text_input($_POST, 'status', 1, 20, $errors, 'Status');
        if (!in_array($input['accent'], ['green', 'amber', 'violet', 'pink'], true)) $errors[] = 'Choose a listed accent.';
        if (!in_array($input['status'], ['draft', 'published'], true)) $errors[] = 'Choose draft or published.';
        $input['featured'] = !empty($_POST['featured']);
        if (!$errors) {
            $input['slug'] = unique_slug($input['title'], $id);
            $input['published_at'] = $input['status'] === 'published' ? ($post['published_at'] ?? gmdate('Y-m-d H:i:s')) : null;
            $id = save_row('blog_posts', $input, $id);
            redirect('/journal/studio?id=' . $id . '&saved=1');
        }
        $post = $input + ['id' => $id, 'slug' => $post['slug'] ?? ''];
        http_response_code(422);
    }
    $posts = all_rows('blog_posts', '', [], 'updated_at DESC');
    render('blog-admin', compact('posts', 'post', 'errors', 'feedback') + ['admin' => true, 'pageTitle' => 'Journal studio | Wales & Webs']);
}