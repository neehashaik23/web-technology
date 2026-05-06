<?php include 'db.php'; ?>

<!DOCTYPE html>
<html>
<head>
<title>View Products</title>
</head>
<body>

<h2>Product List</h2>

<?php
$result = $conn->query("SELECT * FROM products");

if($result->num_rows > 0){
    while($row = $result->fetch_assoc()){
        echo "<h3>".$row['name']."</h3>";
        echo "<p>Price: ₹".$row['price']."</p>";
        echo "<img src='".$row['image']."' width='150'><br><br>";
    }
} else {
    echo "No products found";
}
?>

</body>
</html>