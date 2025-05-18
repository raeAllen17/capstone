<?php
session_start(); // Add this line at the very top if it's not already there
require_once 'dbCon.php'; // Database connection
require_once 'activity_store.php'; // Required functions

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['id'] ?? null; // Ensure the user is logged in
    if (!$userId) {
        echo 'User not logged in!';
        exit;
    }

    $selectedCategory = $_POST['category'] ?? ''; // Get the selected category
    $userItems = getUserListing($pdo, $userId, $selectedCategory); // Get the filtered user listings

    // Loop through the user items and output them as HTML
    foreach ($userItems as $item): ?>
        <div class="item-card">
            <?php if (!empty($item['images'])): ?>
                <img src="data:image/jpeg;base64,<?= $item['images'][0] ?>" alt="Item image" style="width: 100%; height: 300px; object-fit: cover; border-radius: 5px;">
            <?php else: ?>
                <div style="width: 100%; height: auto; background-color: #eee; border-radius: 5px; display: flex; align-items: center; justify-content: center;">No Image</div>
            <?php endif; ?>
            <h3 style="margin: 10px 0 5px;"><?= htmlspecialchars($item['item_name']) ?></h3>
            <p style="margin: 0; color: blueviolet;">₱ <?= htmlspecialchars($item['price']) ?></p>
            <p style="margin: 0; color: red;"> <?= htmlspecialchars($item['location']) ?></p>
            <div style="overflow: auto;">
                <p style="margin: 0; color: grey;"> <?= htmlspecialchars($item['description']) ?></p>
            </div>
            <form method="POST" action="joiner_yourListing.php">
                <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                <button type="submit" class="blue_buttons" style="background: linear-gradient(to right, #ff6b6b, #ffa07a);">Delete</button>
            </form>
        </div>
    <?php endforeach;
    exit; // Stop further execution after outputting the HTML
}
?>
