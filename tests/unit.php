<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';
require dirname(__DIR__) . '/app/handlers.php';
function expect(bool $condition, string $name): void { if (!$condition) throw new RuntimeException('FAIL: ' . $name); }
expect(cents('1250.50') * 2 + cents('99.99') === 260099, 'Exact decimal proposal arithmetic');
expect(format_money(260099) === '2,600.99', 'Money formatting');
expect(format_money(0) === '0.00', 'Zero formatting');
expect(format_money(400000000000000000) === '4,000,000,000,000,000.00', 'Large totals without float precision loss');
expect(e('<script>') === '&lt;script&gt;', 'HTML escaping');
expect(safe_next('//example.com') === '/usabime/admin', 'No external login redirect');
expect(safe_next('/admin/contact?id=22') === '/admin/contact?id=22', 'Safe private redirect');
$errors = [];
expect(date_input('2026-02-30', $errors, 'date') === null && count($errors) === 1, 'Invalid calendar date');
$errors = [];
expect(date_input('2028-02-29', $errors, 'date') === '2028-02-29' && !$errors, 'Leap-year calendar date');
$errors = [];
links_input("https://example.com\njavascript:alert(1)", $errors, 'links');
expect(count($errors) === 1, 'Unsafe links rejected');
$valid = ['title' => 'A project proposal', 'currency' => 'pounds', 'status' => 'draft', 'lines' => [['description' => 'Design', 'quantity' => '2', 'unitPrice' => '1250.50'], ['description' => 'Setup', 'quantity' => '1', 'unitPrice' => '99.99']]];
$errors = []; $proposal = proposal_input($valid, $errors);
expect(!$errors && $proposal['currency'] === 'pounds', 'Free-text currency and proposal input');
$valid['lines'][0]['unitPrice'] = '12.345'; $errors = []; proposal_input($valid, $errors);
expect(count($errors) > 0, 'Too many decimal places rejected');
$valid['lines'][0]['unitPrice'] = '-5'; $errors = []; proposal_input($valid, $errors);
expect(count($errors) > 0, 'Negative price rejected');
$valid['lines'][0]['unitPrice'] = '1'; $valid['lines'][0]['quantity'] = '0'; $errors = []; proposal_input($valid, $errors);
expect(count($errors) > 0, 'Zero quantity rejected');
echo "PHP validation, escaping, redirect safety and exact-money checks passed.\n";