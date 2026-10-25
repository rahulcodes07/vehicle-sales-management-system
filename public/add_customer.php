<?php
$pageTitle = "Register customer";
require_once __DIR__ . '/../app/Views/layouts/header.php';

$successMsg = '';
$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $name    = trim($_POST['name'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');

    // An empty email is stored as NULL so several customers can leave it blank
    $emailValue = $email === '' ? null : $email;

    if (empty($name) || empty($phone) || empty($address)) {
        $errorMsg = "Enter the customer's name, phone number and address.";
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO Customer (name, phone, email, address) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssss", $name, $phone, $emailValue, $address);

        try {
            mysqli_stmt_execute($stmt);
            $successMsg = "{$name} has been registered.";
        } catch (mysqli_sql_exception $e) {
            $errorMsg = "A customer with this phone number or email address is already registered.";
        }
        mysqli_stmt_close($stmt);
    }
}

$form = $errorMsg !== '' ? $_POST : [];

page_head(
    'Register a customer',
    'Save their contact details so you can record a sale or a policy for them.',
    '<a href="customers.php" class="btn">' . icon('arrow-left', 16) . 'Back to customers</a>'
);

if ($successMsg !== '') echo alert_box('success', $successMsg);
if ($errorMsg !== '') echo alert_box('danger', $errorMsg);
?>

<form method="POST" action="add_customer.php" class="form-card">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

    <div class="form-section">
        <h2>Customer details</h2>
        <p>Fields marked <span class="req">*</span> are required.</p>

        <div class="field">
            <label for="name">Full name <span class="req">*</span></label>
            <input type="text" id="name" name="name" value="<?= e($form['name'] ?? '') ?>" placeholder="For example, Ananya Gupta" required>
        </div>
        <div class="field-grid">
            <div class="field">
                <label for="phone">Mobile number <span class="req">*</span></label>
                <input type="tel" id="phone" name="phone" value="<?= e($form['phone'] ?? '') ?>" placeholder="For example, 9876543210" required>
                <span class="hint">Each customer needs a different number.</span>
            </div>
            <div class="field">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= e($form['email'] ?? '') ?>" placeholder="name@example.com">
            </div>
        </div>
        <div class="field">
            <label for="address">Address <span class="req">*</span></label>
            <textarea id="address" name="address" rows="3" placeholder="House, street, city and pincode" required><?= e($form['address'] ?? '') ?></textarea>
        </div>
    </div>

    <div class="form-actions">
        <a href="customers.php" class="btn">Cancel</a>
        <button type="submit" class="btn btn-primary">Save customer</button>
    </div>
</form>

<?php require_once __DIR__ . '/../app/Views/layouts/footer.php'; ?>