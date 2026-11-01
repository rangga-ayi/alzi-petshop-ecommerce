<?php
session_start();
include '../php/php.php';

// Periksa apakah user sudah login
if (!isset($_SESSION['userid'])) {
    header("Location: ../login/login_form.php");
    exit;
}

if ($_SESSION['level'] != "admin") {
    header("Location: ../login/login_form.php");
    exit;
}
// edit.php
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM cart WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    if (isset($_POST['update'])) {
        $user_name = trim($_POST['user_name']);
        $product_name = trim($_POST['product_name']);
        $quantity = intval($_POST['quantity']);
        $price = intval(preg_replace('/[^0-9]/', '', $_POST['price']));

        $stmt_up = $conn->prepare("UPDATE cart SET user_name = ?, product_name = ?, quantity = ?, price = ? WHERE id = ?");
        $stmt_up->bind_param("ssiii", $user_name, $product_name, $quantity, $price, $id);
        if ($stmt_up->execute()) {
            $stmt_up->close();
            header("Location: cart.php");
            exit;
        } else {
            echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($conn->error) . "</div>";
            $stmt_up->close();
        }
    }
} else {
    header("Location: cart.php");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Item</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Edit Item</h2>
        <form action="" method="POST">
            <div class="mb-3">
                <label for="user_name" class="form-label">User Name</label>
                <input type="text" class="form-control" id="user_name" name="user_name" value="<?php echo $row['user_name']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="product_name" class="form-label">Product Name</label>
                <input type="text" class="form-control" id="product_name" name="product_name" value="<?php echo $row['product_name']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="quantity" class="form-label">Quantity</label>
                <input type="number" class="form-control" id="quantity" name="quantity" value="<?php echo $row['quantity']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="price" class="form-label">Price</label>
                <input type="number" class="form-control" id="price" name="price" value="<?php echo $row['price']; ?>" step="0.01" required>
            </div>
            <button type="submit" name="update" class="btn btn-primary">Update</button>
        </form>
    </div>
</body>
</html>