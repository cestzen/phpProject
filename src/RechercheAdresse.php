<?php
// $url will contain the API endpoint

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xml:lang="fr" xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<title>Carte simple - API CUB</title>
	<script type="text/javascript" src="http://data.bordeaux-metropole.fr/api/cub.xjs?key=QHUHHRI7HD"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
	<style type="text/css">
		.container
		{
			background-color: white;
			padding:10px; 
			border: 1px solid #ccc;
		}
		
		#results
		{
			width: 300px; 
			height: 20px;
		}
		
		#voies_result
		{
			font-size: 10px;
			margin-top: 10px;
		}
		
		.btn
		{
			margin: 5px
		}
		
		input
		{
			font-size: 10px;
		}
		
		#input
		{
			width: 200px;
			 margin: 7px 5px 0 0;
		}
		
		form.head > div, form.head > input {
			float: left;
		}
		
		.loading {
			background-image: url('jquery/images/ajax-loader.gif');
			background-size: cover;
			margin-top: 8px;
			height: 16px;
			width: 16px;
			display: none;
		}
	</style>
	
	<script type="text/javascript">
		<!--
		var panel, wpsRecherche, wpsLocalisation, wfsLayer;
		var resultats = [];
		
		CUB.ready(function() {
			CUB.init();
			
			// Contenu HTML du Panel
			var content = '<div class="container">'
				+ 'Adresse sur la Métropole : <br/>'
				+ '<form onsubmit="rechercherVoies(); return false;" class="head">'
				+ '<input type="text" id="input"/>'
				+ '<input type="button" id="btn" class="btn" value="Rechercher" onclick="rechercherVoies()"/>'
				+ '<div class="loading" id="loading"></div>'
				+ '<div>Rechercher dans <select id="restriction">'
				+ '<option value="">Toute la Métropole</option>'
				+ '<option value="33003">Ambarès-et-Lagrave</option>'
				+ '<option value="33004">Ambès</option>'
				+ '<option value="33013">Artigues-Près-Bordeaux</option>'
				+ '<option value="33032">Bassens</option>'
				+ '<option value="33039">Bègles</option>'
				+ '<option value="33056">Blanquefort</option>'
				+ '<option value="33063">Bordeaux</option>'
				+ '<option value="33065">Bouliac</option>'
				+ '<option value="33069">Le Bouscat</option>'
				+ '<option value="33075">Bruges</option>'
				+ '<option value="33096">Carbon-Blanc</option>'
				+ '<option value="33119">Cenon</option>'
				+ '<option value="33162">Eysines</option>'
				+ '<option value="33167">Floirac</option>'
				+ '<option value="33192">Gradignan</option>'
				+ '<option value="33200">Le Haillan</option>'
				+ '<option value="33249">Lormont</option>'
				+ '<option value="33273">Martignas-sur-Jalle</option>'
				+ '<option value="33281">Mérignac</option>'
				+ '<option value="33312">Parempuyre</option>'
				+ '<option value="33318">Pessac</option>'
				+ '<option value="33376">Saint-Aubin-de-Médoc</option>'
				+ '<option value="33434">Saint-Louis-de-Montferrand</option>'
				+ '<option value="33449">Saint-Médard-en-Jalles</option>'
				+ '<option value="33487">Saint-Vincent-de-Paul</option>'
				+ '<option value="33519">Le Taillan-Médoc</option>'
				+ '<option value="33522">Talence</option>'
				+ '<option value="33550">Villenave-d\'Ornon</option>'
				+ '</select></div>'
				+ '</form>'
				+ '<select id="voies_result" size="10" style="width: 400px"></select><br/>'
				+ '<div id="results"></div>'
				+ '<input type="button" value="Cadrer sur" onclick="localiser()"/><br/>'
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
			wpsRecherche = new CUB.Layer.Processing('', '//data.bordeaux-metropole.fr/wps?key=QHUHHRI7HD', {
				process: 'geocodeur',
				style: new CUB.Style({
					color: new CUB.Color('#FF0000')
				})
			});
		});
		
		function setLoading(on)
		{
			var input = document.getElementById('input');
			var btn = document.getElementById('btn');
			var loading = document.getElementById('loading');
			if(on)
			{
				loading.style.display = 'block';
				input.disabled = true;
				btn.disabled = true;
			}else{
				loading.style.display = 'none';
				input.disabled = false;
				btn.disabled = false;
			}
		}
		
		function rechercherVoies()
		{
			var start = new Date();
			setLoading(true);			
			
			// On récupère la liste déroulante
			var input = document.getElementById('input');
			
			// Paramètres à passer au serveur WPS (ici "input")
			var params = { 
				input: input.value,
				commune: $('#restriction').val()
			};
			
			// Lancement du traitement
			wpsRecherche.execute(params, function(res) { // Fonction appelée dès le traitement terminé
				setLoading(false);
				
				// Mise à jour de la liste avec les résultats
				var list = document.getElementById('voies_result');
				list.options.length = 0;
				
				if(res.result)
				{
					// Nombre de résultats trouvés
					document.getElementById('results').innerHTML = 'Résultats trouvés : ' + res.result.length + ' en ' + ((new Date() - start) / 1000) + 's';
				
					for(var i in res.result)
					{
						var libelle = (res.result[i].attributes.NUMERO + res.result[i].attributes.REP + ' ' + res.result[i].attributes.NOM_VOIE).trim();
						list.options[list.options.length] = new Option(libelle + ' ' + res.result[i].attributes.CODE_POSTAL + ' ' + res.result[i].attributes.COMMUNE + ' (pertinence : ' + 
								res.result[i].attributes.PERTINENCE + '%)', resultats.length /* Indice dans le tableau */, false, false);
								
						resultats.push(res.result[i]);
					}
				}else
					document.getElementById('results').innerHTML = 'Aucun résultat trouvé';
			}, function() {
				setLoading(false);
				document.getElementById('results').innerHTML = 'Erreur inconnue';
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
			
			resultat.zoomTo(1000);
		}
		//-->
	</script>
</head>
	<body>
	<div id="zone_carte" style="width: 500px; height: 500px; border: 1px solid black"></div>
	<div id="right_panel" ></div>
	</body>
</html>