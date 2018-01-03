<?php
// $url will contain the API endpoint

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xml:lang="fr" xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<title>Carte simple - API CUB</title>
	<script type="text/javascript" src="http://data.bordeaux-metropole.fr/api/cub.xjs?key=QHUHHRI7HD"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
	
		<script>
		if(typeof String.prototype.trim !== 'function') {
		  String.prototype.trim = function() {
			return this.replace(/^\s+|\s+$/g, ''); 
		  }
		}
</script>
		<script type="text/javascript">
		<!--
		var draw;
		var entity; // Entité en cours d'édition
		var type ;// Type de dessin courant
		
		CUB.ready(function() {
			CUB.init();
			type = CUB.Entity.geomType.POINT;
			// On crée une couche dynamique qui va contenir nos dessins
			draw = CUB.Layer.Dynamic('zone_carte', null, {
				selectable: false,
				style: new CUB.Style({
					color: new CUB.Color('#99FF99'),
					opacity: 50,
					outlineColor: new CUB.Color('#00FF00'),
					outlineOpacity: 80,
					outlineWidth: 2,
					size: 6
				})
			});
			
			
			
			
			// On active l'outil point
			enablePoint();
			
			// Compatible ordinateurs et mobiles
			CUB.events.onTouchStart = CUB.events.onClick = function(point) 
			{
				if(!type)
					return;
							
				switch(type)
				{
					case CUB.Entity.geomType.POINT:
						entity = draw.createEntity(type);
						entity.geometry(new CUB.Geometry.Point(point));
						alert(point);
						break;
						
					case CUB.Entity.geomType.LINE:
						if(!entity) // Pas d'entité courante
						{
							// On en crée une
							entity = draw.createEntity(CUB.Entity.geomType.LINE);
							entity.geometry(new CUB.Geometry.Line([point])); // On crée la ligne (avec un seul point)
						} else {
							// On récupère la géométrie de l'entité courante
							var geom = entity.geometry();
							
							// On lui ajoute le point de clic
							geom.addVertices([point]);
							
							// On remplace la géométrie de l'objet courant
							entity.geometry(geom);
						}
						break;
						
					case CUB.Entity.geomType.POLYGON:
						if(!entity) // Pas d'entité courante
						{
							// On en crée une
							entity = draw.createEntity(CUB.Entity.geomType.POLYGON);
							entity.geometry(new CUB.Geometry.Polygon([point])); // On crée le polygone (avec un seul point)
						} else {
							// On récupère la géométrie de l'entité courante
							var geom = entity.geometry();
							
							// On lui ajoute le point de clic
							geom.addVertices([point]);
							
							// On remplace la géométrie de l'objet courant
							entity.geometry(geom);
						}
						break;
				}
			}
		});
		
		// Fonctions de dessin
		function enablePoint() 
		{
			// On désactive le déplacement panoramique
			CUB.disablePan();
			
			newObject();
			type = CUB.Entity.geomType.POINT;
		}
		function enableLine() 
		{
			// On désactive le déplacement panoramique
			CUB.disablePan();

			newObject();
			type = CUB.Entity.geomType.LINE;
		}
		
		function enablePolygon() 
		{
			// On désactive le déplacement panoramique
			CUB.disablePan();

			newObject();
			type = CUB.Entity.geomType.POLYGON;
		}
		
		function disableAll() 
		{
			// On active le déplacement panoramique
			CUB.enablePan();
			
			type = null;
		}
		
		function eraseAll() 
		{
			// On supprime toutes les entités de la couche de dessin
			draw.removeAll();
			
			newObject();
		}
		
		function newObject()
		{
			entity = null; // Force la recréation d'une entité au prochain clic
		}
		//-->
	</script>
</head>
	<body>
	<div id="zone_carte" style="width: 500px; height: 500px; border: 1px solid black"></div>
	<div id="right_panel" ></div>
	</body>
</html>