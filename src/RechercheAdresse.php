<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xml:lang="fr" xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Exemple API CUB - Localisation sur adresse en WPS</title>
<!-- NE PAS UTILISER LA CLE CI-DESSOUS. Formulaire de demande de cl� : http//data.bordeaux-metropole.fr/key -->
<script type="text/javascript"
	src="//data.bordeaux-metropole.fr/api/cub.xjs?key=QHUHHRI7HD"></script>

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
				+ '<input type="button" value="Cadrer sur" onclick="localiser()"/><br/>'
				+ '</div>';
			
			// Construction du panel de contr�les
			panel = new CUB.Panel({
				width: 450,
				height: 300,
				content: content,
				top: 0,
				left: 0
			});
			
			// Service de recherche de voie
			wpsRecherche = new CUB.Layer.Processing('', '//data.bordeaux-metropole.fr/wps?key=QHUHHRI7HD', 'recherche_voie');
			
			// Service de recherche de cadrage sur une voie
			wpsLocalisation = new CUB.Layer.Processing('', '//data.bordeaux-metropole.fr/wps?key=QHUHHRI7HD', {
				process: 'voie_par_identifiant',
				style: new CUB.Style({
					outlineWidth: 5,
					outlineColor: new CUB.Color('#ff0000')
				})
			});
		});
		
		function rechercherVoies()
		{
			// On r�cup�re la liste d�roulante
			var input = document.getElementById('input');
			
			// Param�tres � passer au serveur WPS (ici "input")
			var params = { 
				input: input.value
			};
			
			// Lancement du traitement
			wpsRecherche.execute(params, function(res) { // Fonction appel�e d�s le traitement termin�
				// Mise � jour de la liste avec les r�sultats
				var list = document.getElementById('voies_result');
				list.options.length = 0;

				if(!res.result)
				{
					document.getElementById('results').innerHTML = '0 r�sultat trouv�';
					return;
				}
			
				// Nombre de r�sultats trouv�s
				document.getElementById('results').innerHTML = 'Resultats : ' + res.result.length;
				
				for(var i in res.result)
				{
					// Type de r�sultat
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
			// On r�cup�re la liste des r�sultats
			var list = document.getElementById('voies_result');
			
			// Aucune rue s�lectionn�e ?
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

			// �v�nement d�clench� � la s�lection d'un objet (entit�)
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
				
			wfsLayer = new CUB.Layer.Dynamic(null, '//data.bordeaux-metropole.fr/wfs?key=QHUHHRI7HD' /* NE PAS UTILISER CETTE CLE */ + filter, {
				layerName: origin,
				attributes: null,
				loadAllAtOnce: true,
				style: new CUB.Style({
					color: new CUB.Color('#FF0000'),
					outlineColor: new CUB.Color('#FF0000'),
					size: 15
				}),
				onLoadEnd: function(entities) {
					// On r�cup�re les entit�s
					entites = this.getEntities();
				
					var point = entites[0].getBarycenter();
					
					// On cadre sur l'objet
					CUB.moveToPoint(point, 5000);
				}
			});
		}
		
		function localiser_voie_wps(ident)
		{			
			// Param�tres � passer au serveur WPS (ici "ident" qui est l'identifiant de la voie)
			var params = { 
				ident: ident
			};
			
			// Lancement du traitement
			wpsLocalisation.execute(params, function(res) {
				// On r�cup�re les entit�s
				entites = this.getEntities();
				
				if(entites.length == 0) // Pas de g�om�trie
				{
					alert('Impossible de se localiser sur la voie : celle-ci n\'a pas de g�om�trie');
					return;
				}
				
				// On prend l'�tendue de la premi�re
				var extent = entites[0].getExtent();
				
				// Et on ajoute les �tendues des suivantes
				for(var i = 1; i < entites.length; ++i)
					extent.add(entites[i].getExtent());
					
				// On cadre sur cette �tendue globale, qui contient toutes les entit�s
				CUB.moveToExtent(extent);
			});
		}
	</script>
</head>
<body>


</body>
</html>
