<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xml:lang="fr" xmlns="http://www.w3.org/1999/xhtml">
<?php
session_start();
if (! isset($_SESSION['username'])) {
    header("Location: http://localhost/phpProject/src/php/login.html");
}

?>

<head>
<script
	src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
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
	margin: 7px 5px 0 0;
}

form.head>div, form.head>input {
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
		
		var panel, wpsRecherche, wpsLocalisation, wfsLayer, place;
		var resultats = [];
		
		CUB.ready(function() {
			CUB.init();
			CUB.enable();
			place = new CUB.Layer.Dynamic();
			
			// Contenu HTML du Panel
			var content = '<div class="container">'
				+ 'Adresse sur la M&#233;tropole : <br/>'
				+ '<form onsubmit="rechercherVoies(); return false;" class="head">'
				+ '<input type="text" id="input"/>'
				+ '<input type="button" id="btn" class="btn" value="Rechercher" onclick="rechercherVoies()"/>'
				+ '<div class="loading" id="loading"></div>'
				+ '<div>Rechercher dans <select id="restriction">'
				+ '<option value="">Toute la M&#233;tropole</option>'
				+ '<option value="33003">Ambar&#232;s-et-Lagrave</option>'
				+ '<option value="33004">Amb&#232;s</option>'
				+ '<option value="33013">Artigues-Pr&#232;s-Bordeaux</option>'
				+ '<option value="33032">Bassens</option>'
				+ '<option value="33039">B&#232;gles</option>'
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
				+ '<option value="33281">M&#233;rignac</option>'
				+ '<option value="33312">Parempuyre</option>'
				+ '<option value="33318">Pessac</option>'
				+ '<option value="33376">Saint-Aubin-de-M&#233;doc</option>'
				+ '<option value="33434">Saint-Louis-de-Montferrand</option>'
				+ '<option value="33449">Saint-M&#233;dard-en-Jalles</option>'
				+ '<option value="33487">Saint-Vincent-de-Paul</option>'
				+ '<option value="33519">Le Taillan-M&#233;doc</option>'
				+ '<option value="33522">Talence</option>'
				+ '<option value="33550">Villenave-d\'Ornon</option>'
				+ '</select></div>'
				+ '</form>'
				+ '<select id="voies_result" size="10" style="width: 400px"></select><br/>'
				+ '<div id="results"></div>'
				+ '<input type="button" id="button" value="Cadrer sur" onclick="localiser()"/><br/>'
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
			wpsRecherche = new CUB.Layer.Processing('', '//data.bordeaux-metropole.fr/wps?key=G0NDMI15J2', {
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
			
			// On r&#233;cup&#232;re la liste d&#233;roulante
			var input = document.getElementById('input');
			
			// Param&#232;tres à passer au serveur WPS (ici "input")
			var params = { 
				input: input.value,
				commune: $('#restriction').val()
			};
			
			// Lancement du traitement
			wpsRecherche.execute(params, function(res) { // Fonction appel&#233;e d&#232;s le traitement termin&#233;
				setLoading(false);
				
				// Mise à jour de la liste avec les r&#233;sultats
				var list = document.getElementById('voies_result');
				list.options.length = 0;
				
				if(res.result)
				{
					// Nombre de r&#233;sultats trouv&#233;s
					document.getElementById('results').innerHTML = 'R&#233;sultats trouv&#233;s : ' + res.result.length + ' en ' + ((new Date() - start) / 1000) + 's';
				
					for(var i in res.result)
					{
						var libelle = (res.result[i].attributes.NUMERO + res.result[i].attributes.REP + ' ' + res.result[i].attributes.NOM_VOIE).trim();
						list.options[list.options.length] = new Option(libelle + ' ' + res.result[i].attributes.CODE_POSTAL + ' ' + res.result[i].attributes.COMMUNE + ' (pertinence : ' + 
								res.result[i].attributes.PERTINENCE + '%)', resultats.length /* Indice dans le tableau */, false, false);
								
						resultats.push(res.result[i]);
					}
				}else
					document.getElementById('results').innerHTML = 'Aucun r&#233;sultat trouv&#233;';
			}, function() {
				setLoading(false);
				document.getElementById('results').innerHTML = 'Erreur inconnue';
			});
		}
		
		function localiser()
		{
			// On r&#233;cup&#232;re la liste des r&#233;sultats
			var list = document.getElementById('voies_result');
			
			// Aucune rue s&#233;lectionn&#233;e ?
			if(list.selectedIndex < 0)
				return;
			
			var resultat = resultats[list.options[list.selectedIndex].value];

			 $.ajax({
				  type: "POST",
				  url: "recordAction.php",
				  data: { string: "CHOSE ADDRESS " + resultat.attributes.NUMERO + resultat.attributes.REP + ' ' + resultat.attributes.NOM_VOIE }
				});  

			CUB.moveToExtent(resultat.getExtent());
			CUB.zoomMinus(resultat.getExtent().height(), resultat.getExtent().width())

			place.destroy();
			place = new CUB.Layer.Dynamic(
					'',
					'http://data.bordeaux-metropole.fr/wfs?key=G0NDMI15J2',
					{
						layerName : 'TB_ARRET_P',
						label : '${RESEAU} : ${LIGNEDES}',
						size : 25,
						style : new CUB.Style({
							color : new CUB.Color('#4E32A7')
						}),
						wfsFilter : '<PropertyIsEqualTo><PropertyName>VILLE</PropertyName><Literal>'+resultat.attributes.COMMUNE.toUpperCase()+'</Literal></PropertyIsEqualTo>'
						, 
						selectable : true
					});

			// &#233;v&#232;nement d&#233;clench&#233; à la s&#233;lection d'un objet (entit&#233;)
			place.onSelect = function(entity) 
			{
				$.ajax({
					  type: "POST",
					  url: "recordAction.php",
					  data: { string: "CHOSE " + entity.attributes.RESEAU + ' ' +  entity.attributes.LIGNEDES}
					}); 
				alert('Ligne : ' + entity.attributes.LIGNEDES + '\nAdresse : ' + entity.attributes.ADRESSE + '\nType : ' + entity.attributes.RESEAU);
			}
		}
		
	</script>
</head>
<body>


</body>
</html>