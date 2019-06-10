
<!DOCTYPE html>
<html>
<head>
	<title>Site Map Tool</title>
	<link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
<form action="single-page.php">
	URL: <input type="text" name="url" value="<?php if(isset($_GET['url'])) {echo $_GET['url'];} ?>">
	Number: <input type="text" name="num" value="<?php if(isset($_GET['num'])) {echo $_GET['num'];} ?>">
	<input type="submit">
</form>
<?php
	if(isset($_GET["url"])) { include 'single-page-scrape.php'; }
?>
</body>
</html>