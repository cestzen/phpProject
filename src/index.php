<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xml:lang="fr" xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<title>Carte simple - API CUB</title>
	<script type="text/javascript" src="http://data.bordeaux-metropole.fr/api/cub.xjs?key=ABCDE01234"></script>
	<script type="text/javascript">
		CUB.ready(function() {
			CUB.init('zone_carte')
		});
	</script>
</head>
	<body>
	<div id="zone_carte" style="width: 256px; height: 256px; border: 1px solid black"></div>
	</body>
</html>