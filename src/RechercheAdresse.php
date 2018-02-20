<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xml:lang="fr" xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Exemple API CUB - Localisation sur adresse en WPS</title>
<script type="text/javascript"
	src="//data.bordeaux-metropole.fr/api/cub.xjs?key=G0NDMI15J2"></script>

<style type="text/css">
.container {
	background-color: white;
	padding: 10px;
	border: 1px solid #ccc;
}

#results {
	width: 300px;
	height: 20px;
}

#voies_result {
	font-size: 10px;
	margin-top: 10px;
}

.btn {
	margin: 5px
}

input {
	font-size: 10px;
}

#input {
	width: 200px;
}
</style>

<script type="text/javascript">
		var panel, wpsRecherche, wpsLocalisation, wfsLayer, place;
		var resultats = [];
		
		CUB.ready(function() {
			CUB.init();	

			place = new CUB.Layer.Dynamic();		
			
			// Mire de chargement
			new CUB.Panel.Loading();
			
			// Contenu HTML du Panel
			var content = '<div class="container">'
				+ 'Nom ou partie de nom d\'une voie, un arret TBC ou un equipement public de la CUB : <br/><input type="text" id="input"/>'
				+ '<input type="button" class="btn" value="Rechercher" onclick="rechercherVoies()"/><br/>'
				+ '<select id="voies_result" size="10" style="width: 400px"></select><br/>'
				+ '<div id="results"></div>'
				+ '<input type="button" value="Cadrer sur" onclick="localiser()"/>'
				+ '<a href="/phpProject/src/php/showBusses.html"><input type="button"'
				+ ' value="Retourner aux services" </a><br/>'
				+ '</div>';
			
			// Construction du panel de contrôles
			panel = new CUB.Panel({
				width: 450,
				height: 300,
				content: content,
				top: 0,
				left: 0
			});
			
			// Service de recherche de voie
			wpsRecherche = new CUB.Layer.Processing('', '//data.bordeaux-metropole.fr/wps?key=G0NDMI15J2', 'recherche_voie');
			
			// Service de recherche de cadrage sur une voie
			wpsLocalisation = new CUB.Layer.Processing('', '//data.bordeaux-metropole.fr/wps?key=G0NDMI15J2', {
				process: 'voie_par_identifiant',
				style: new CUB.Style({
					outlineWidth: 5,
					outlineColor: new CUB.Color('#ff0000')
				})
			});
		});
		
		function rechercherVoies()
		{
			// On récupère la liste déroulante
			var input = document.getElementById('input');
			
			// Paramètres à passer au serveur WPS (ici "input")
			var params = { 
				input: input.value
			};
			
			// Lancement du traitement
			wpsRecherche.execute(params, function(res) { // Fonction appelée dès le traitement terminé
				// Mise à jour de la liste avec les résultats
				var list = document.getElementById('voies_result');
				list.options.length = 0;

				if(!res.result)
				{
					document.getElementById('results').innerHTML = '0 résultat trouvé';
					return;
				}
			
				// Nombre de résultats trouvés
				document.getElementById('results').innerHTML = 'Resultats : ' + res.result.length;
				
				for(var i in res.result)
				{
					// Type de résultat
					var type = 'Inconnu';
					switch(res.result[i].attributes.ORIGINE)
					{
						case 'FV_VOIE_A':
							type = 'Nom de voie';
							break;
						case 'TB_ARRET_P':
							type = 'Arret TBC';
							break;
					}				
					if(type != 'Inconnu'){
					list.options[list.options.length] = new Option(res.result[i].attributes.NOM + ' - ' + res.result[i].attributes.COMMUNE + ' (pertinence : ' + 
							res.result[i].attributes.PERTINENCE + '%) - ' + type, resultats.length /* Indice dans le tableau */, false, false);
							
					resultats.push(res.result[i]);
					}
				}
			});
		}
		
		function localiser()
		{
			// On récupère la liste des résultats
			var list = document.getElementById('voies_result');
			
			// Aucune rue sélectionnée ?
			if(list.selectedIndex < 0)
				return;
			
			var resultat = resultats[list.options[list.selectedIndex].value];
			place.destroy();
			place = new CUB.Layer.Dynamic(
					'',
					'http://data.bordeaux-metropole.fr/wfs?key=G0NDMI15J2',
					{
						layerName : 'TB_ARRET_P',
						style : new CUB.Style({
							color : new CUB.Color('#4E32A7')
						}),
						wfsFilter : '<PropertyIsEqualTo><PropertyName>VILLE</PropertyName><Literal>'+resultat.attributes.COMMUNE.toUpperCase()+'</Literal></PropertyIsEqualTo>'
						, 
						selectable : true
					});

			// Évènement déclenché à la sélection d'un objet (entité)
			place.onSelect = function(entity) 
			{
				alert('Ville ' + entity.attributes.VILLE.toLowerCase());
			}
			
			if(wfsLayer)
				wfsLayer.destroy();
			wpsLocalisation.removeAll();
				
			switch(resultat.attributes.ORIGINE)
			{	
				case 'FV_VOIE_A':
					localiser_voie_wps(resultat.attributes.IDENT);
					break;
				case 'TB_ARRET_P':
					localiser_objet_wfs(resultat.attributes.ORIGINE, resultat.attributes.IDENT);
					break;
			}
		}

		function localiser_objet_wfs(origin, ident)
		{
			var filter = '&FILTER=<Filter><PropertyIsEqualTo><PropertyName>IDENT</PropertyName><Literal>' + ident + '</Literal></PropertyIsEqualTo></Filter>';
				
			wfsLayer = new CUB.Layer.Dynamic(null, '//data.bordeaux-metropole.fr/wfs?key=G0NDMI15J2' /* NE PAS UTILISER CETTE CLE */ + filter, {
				layerName: origin,
				attributes: null,
				loadAllAtOnce: true,
				style: new CUB.Style({
					color: new CUB.Color('#FF0000'),
					outlineColor: new CUB.Color('#FF0000'),
					size: 15
				}),
				onLoadEnd: function(entities) {
					// On récupère les entités
					entites = this.getEntities();
				
					var point = entites[0].getBarycenter();
					
					// On cadre sur l'objet
					CUB.moveToPoint(point, 5000);
				}
			});
		}
		
		function localiser_voie_wps(ident)
		{			
			// Paramètres à passer au serveur WPS (ici "ident" qui est l'identifiant de la voie)
			var params = { 
				ident: ident
			};
			
			// Lancement du traitement
			wpsLocalisation.execute(params, function(res) {
				// On récupère les entités
				entites = this.getEntities();
				
				if(entites.length == 0) // Pas de géométrie
				{
					alert('Impossible de se localiser sur la voie : celle-ci n\'a pas de géométrie');
					return;
				}
				
				// On prend l'étendue de la première
				var extent = entites[0].getExtent();
				
				// Et on ajoute les étendues des suivantes
				for(var i = 1; i < entites.length; ++i)
					extent.add(entites[i].getExtent());
					
				// On cadre sur cette étendue globale, qui contient toutes les entités
				CUB.moveToExtent(extent);
			});
		}
	</script>
</head>
<body>


</body>
</html>
