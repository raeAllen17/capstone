<?php
session_start();
require 'dbCon.php';

if (!isset($_SESSION['id'])) {
    header("Location: landing_page.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($_POST['list'])){
        $participant_id = $_SESSION['id'];
        $item_name = $_POST['item_name'] ?? '';
        $price = $_POST['price'] ?? 0;
        $location = $_POST['location'] ?? '';
        $condition = $_POST['condition'] ?? '';
        $category = $_POST['category'] ?? '';
        $description = $_POST['description'] ?? '';

        try {

            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                INSERT INTO marketplace (participant_id, item_name, price, location, `condition`, category, description)
                VALUES (:participant_id, :item_name, :price, :location, :condition, :category, :description)
            ");
            $stmt->execute([
                ':participant_id' => $participant_id,
                ':item_name' => $item_name,
                ':price' => $price,
                ':location' => $location,
                ':condition' => $condition,
                ':category' => $category,
                ':description' => $description
            ]);

            $marketplace_id = $pdo->lastInsertId();
            if (!empty($_FILES['images']['tmp_name'][0])) {
                foreach ($_FILES['images']['tmp_name'] as $index => $tmpName) {
                    if (is_uploaded_file($tmpName)) {
                        $imageData = file_get_contents($tmpName);
                        $stmt = $pdo->prepare("INSERT INTO marketplace_images (marketplace_id, image) VALUES (:marketplace_id, :image)");
                        $stmt->bindParam(':marketplace_id', $marketplace_id, PDO::PARAM_INT);
                        $stmt->bindParam(':image', $imageData, PDO::PARAM_LOB);
                        $stmt->execute();
                    }
                }
            }        

            $pdo->commit();
            $_SESSION['success_message'] = "Item listed successfully!";
            header("Location: ../joiner_marketList.php");
            exit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            $_SESSION['error_message'] = $e->getMessage();
            header("Location: joiner_marketList.php");
            exit();
        }
    } else if (isset($_POST['offer'])){
        $trade_from_user_id = $_SESSION['id'];
        $trade_to_user_id = $_POST['trade_to_user_id'] ?? null;
        $trade_to_item_id = $_POST['trade_to_item_id'] ?? null;
        $trade_from_item_id_raw = $_POST['selected_item'] ?? null;

        $isBuy = ($trade_from_item_id_raw === 'buy');
        $trade_from_item_id = $isBuy ? null : $trade_from_item_id_raw;

        // Validate data
        if (!$trade_from_user_id || !$trade_to_user_id || !$trade_to_item_id || (!$isBuy && !$trade_from_item_id)) {
            $_SESSION['error_message'] = "Choose an item to trade.";
            header("Location: ../joiner_marketplace.php");
            exit();
        }

        // Prevent trading with self
        if ($trade_from_user_id == $trade_to_user_id) {
            $_SESSION['error_message'] = "You cannot trade with yourself.";
            header("Location: ../joiner_marketplace.php");
            exit();
        }

        // Check for duplicate pending trade
        $checkSql = "
            SELECT COUNT(*) FROM trades
            WHERE trade_from_user_id = :from_user
              AND trade_to_user_id = :to_user
              AND trade_to_item_id = :to_item
              AND (
                (:from_item IS NULL AND trade_from_item_id IS NULL)
                OR
                (trade_from_item_id = :from_item)
              )
              AND status = 'pending'
        ";

        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->execute([
            ':from_user' => $trade_from_user_id,
            ':from_item' => $trade_from_item_id,
            ':to_user' => $trade_to_user_id,
            ':to_item' => $trade_to_item_id
        ]);

        $exists = $checkStmt->fetchColumn();

        if ($exists > 0) {
            $_SESSION['error_message'] = "You've already made this trade offer.";
            header("Location: ../joiner_marketplace.php");
            exit();
        }

        // Insert new trade
        try {
            $stmt = $pdo->prepare("
                INSERT INTO trades (
                    trade_from_user_id,
                    trade_from_item_id,
                    trade_to_user_id,
                    trade_to_item_id,
                    status,
                    created_at,
                    updated_at
                ) VALUES (
                    :trade_from_user_id,
                    :trade_from_item_id,
                    :trade_to_user_id,
                    :trade_to_item_id,
                    'pending',
                    NOW(),
                    NOW()
                )
            ");

            $stmt->execute([
                ':trade_from_user_id' => $trade_from_user_id,
                ':trade_from_item_id' => $trade_from_item_id,
                ':trade_to_user_id' => $trade_to_user_id,
                ':trade_to_item_id' => $trade_to_item_id
            ]);

            $_SESSION['success_message'] = "Trade offer submitted!";
            header("Location: ../joiner_marketplace.php");
            exit();

        } catch (PDOException $e) {
            $_SESSION['error_message'] = "Error processing trade: " . $e->getMessage();
            header("Location: ../joiner_marketplace.php");
            exit();
        }
    }
} else {
    echo "Session Expired";
}



