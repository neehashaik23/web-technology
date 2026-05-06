<?php include 'db.php'; ?>

<!DOCTYPE html>
<html>
<head>
<title>Add Product</title>
</head>
<body>

<h2>Add Product</h2>

<form method="POST">

Name: <input type="text" name="name" required><br><br>

Price: <input type="number" name="price" required><br><br>

Image URL: <input type="text" name="image" required><br><br>

<button type="submit" name="submit">Add Product</button>

</form>

<?php
if(isset($_POST['submit'])){
    $name = $_POST['name'];
    $price = $_POST['price'];
    $image = $_POST['image'];

    $sql = "INSERT INTO products (name, price, image)
            VALUES ('$name','$price','$image')";

    if($conn->query($sql)){
        echo "<p style='color:green;'>Product Added Successfully!</p>";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

</body>
</html>