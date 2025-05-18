<?php
// fetchMarketplaceItems.php

require_once 'dbCon.php'; // Database connection
require_once 'activity_store.php'; // Function to get marketplace data

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ensure user is logged in
    session_start();
    $userId = $_SESSION['id'] ?? null;
    if (!$userId) {
        echo 'User not logged in!';
        exit;
    }

    // Get selected category from POST data
    $selectedCategory = $_POST['category'] ?? '';

    // Get marketplace items for the logged-in user and selected category
    $marketplaceItems = getMarketplace($pdo, $userId, $selectedCategory);

    // Output the items as HTML
    foreach ($marketplaceItems as $item): ?>
        <div class="item-card" onclick="openTradeModal(<?= $item['id'] ?>, <?= $item['participant_id'] ?>, '<?= htmlspecialchars($item['item_name'], ENT_QUOTES) ?>', event)">
            <?php if (!empty($item['images'])): ?>
                <img src="data:image/jpeg;base64,<?= $item['images'][0] ?>" alt="Item image" style="width: 100%; height: 150px; object-fit: cover; border-radius: 5px;">
            <?php else: ?>
                <div style="width: 100%; height: 150px; background-color: #eee; border-radius: 5px; display: flex; align-items: center; justify-content: center;">No Image</div>
            <?php endif; ?>
            <h3 style="margin: 10px 0 5px;"><?= htmlspecialchars($item['item_name']) ?></h3>
            <p style="margin: 0; color: blueviolet;">₱ <?= htmlspecialchars($item['price']) ?></p>
            <p style="margin: 0; color: red;"> <?= htmlspecialchars($item['location']) ?></p>
            <p style="margin: 0; font-size: small; color: gray;">By: <?= htmlspecialchars($item['firstName']) . ' ' . htmlspecialchars($item['lastName']) ?></p>
        </div>
    <?php endforeach;
    exit;
}
?>
