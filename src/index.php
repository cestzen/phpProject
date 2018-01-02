<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xml:lang="fr" xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<title>Carte simple - API CUB</title>
	<script type="text/javascript" src="http://data.bordeaux-metropole.fr/api/cub.xjs?key=QHUHHRI7HD"></script>
	<script type="text/javascript">
		CUB.ready(function() {
			CUB.init('zone_carte')
			var statique = new CUB.Layer.Static('Ma couche statique', 'http://data.bordeaux-metropole.fr/wms?key=QHUHHRI7HD', ['TB_PARCR_P', 'TB_CHEM_L', 'CI_PARK_P']);
			var dynamique = new CUB.Layer.Dynamic('Ma couche dynamique', 'http://data.bordeaux-metropole.fr/wfs?key=QHUHHRI7HD', { layerName: 'TB_STVEL_P' });
		});
		
	</script>
</head>
	<body>
	<div id="zone_carte" style="width: 500px; height: 500px; border: 1px solid black"></div>
	</body>
</html>