<?php 

$name = $_POST['name'];

?>

<html>
	<form action="xss.php" method="post">
		<input name="name" type="text" value="<?php echo $name; ?>"></input>
	</form>
</html>