<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xml:lang="fr" xmlns="http://www.w3.org/1999/xhtml">
<script type="text/javascript" src="//data.bordeaux-metropole.fr/api/cub.xjs?key=QHUHHRI7HD"></script>

	<script type="text/javascript">		

		var SAEIV = {
			// Lignes
			wpsLines: null,
			
			// ArrÃªts
			wpsStops: null,
			
			// TronÃ§ons
			wpsSections: null,
			
			// VÃ©hicules
			wfsVehicles: null,
			
			// Horaires Ã  l'arrÃªt
			wpsStopTimes: null,
			
			// Correspondances Ã  l'arrÃªt
			wpsStopCorres: null,
			corresData: null,
			
			// Stats
			statRemaining: null,
			statInterval: null,
			
			init: function() {
				// Panel latÃ©ral
				$('#right_panel').enhanceWithin().panel({
					close: function() {
						$(this).panel('open');
					}
				})
				$('#right_panel').panel('open');
				
				
				// Ã‰vÃ©nements sur les contrÃ´les
				$('#cboLines').change(SAEIV.onSelectLine);
				$('#panWay').change(SAEIV.onSelectWay);
				
				// Spits
				SAEIV.spitSet = new CUB.Layer.SpitSet({
					spitDistance: 200
				});
				
				// Lignes
				SAEIV.wpsLines = new CUB.Layer.Processing('Lignes de bus', '//data.bordeaux-metropole.fr/wps?key=QHUHHRI7HD', {
					process: 'SV_LIGNE_A'
				});
				
				// TronÃ§ons
				SAEIV.wpsSections = new CUB.Layer.Processing('', '//data.bordeaux-metropole.fr/wps?key=QHUHHRI7HD', {
					process: 'saeiv_troncons_sens',
					style: new CUB.Style({ // Style par dÃ©faut
						color: new CUB.Color('#0011ee'),
					outlineWidth: 3
					})
				});
				
				// ArrÃªts
				SAEIV.wpsStops = new CUB.Layer.Processing('', '//data.bordeaux-metropole.fr/wps?key=QHUHHRI7HD', {
					process: 'saeiv_arrets_sens',
					style: new CUB.Style({ // Style par dÃ©faut
						color: new CUB.Color('#ff1111')
					}),
					selectable: true,
					onEntityRender: function(entity, style) {
						if(entity.attributes.TYPE == 'DEVIE')
							style.color = style.outlineColor = new CUB.Color('#FFa050');
					},
					onSelect: SAEIV.onSelectStop
				});
				
				// Horaires Ã  un arrÃªt
				SAEIV.wpsStopTimes = new CUB.Layer.Processing('', '//data.bordeaux-metropole.fr/wps?key=QHUHHRI7HD', {
					process: 'SV_HORAI_A'
				});
				
				// Correspondances
				SAEIV.wpsStopCorres = new CUB.Layer.Processing('', '//data.bordeaux-metropole.fr/wps?key=QHUHHRI7HD', {
					process: 'saeiv_correspondances'
				});
				
				SAEIV.reset();
				SAEIV.loadLines();
			},
			
			// Chargement des lignes
			loadLines: function() {
				
				SAEIV.wpsLines.execute({
					filter: '<Filter><PropertyIsEqualTo><PropertyName>ACTIVE</PropertyName><Literal>1</Literal></PropertyIsEqualTo></Filter>'
				}, SAEIV.onLinesLoad);
			},
			
			showSpit: function(spit_attributes, position) {
				// On supprime tous les Spits de la carte (le cas Ã©chÃ©ant)
				SAEIV.spitSet.removeAll();

				// On crÃ©e un Spit Ã  la position de l'entitÃ©, et on le
				// renseigne avec les attributs
				var spit = SAEIV.spitSet.createSpit(position);
				spit.attributes = spit_attributes;

				// NÃ©cessaire pour que le Spit s'affiche correctement
				SAEIV.spitSet.redraw();
			},
			
			updateStats: function() {
				var count = SAEIV.wfsVehicles.getEntities().length;
				$('#stats').html(count + ' vÃ©hicules sur le sens');
				
				if(SAEIV.statInterval)
					clearInterval(SAEIV.statInterval);
				
				SAEIV.statRemaining = 10;
				$('#reload > span').html(SAEIV.statRemaining + 's');
				SAEIV.statInterval = setInterval(function() {
					--SAEIV.statRemaining;
					$('#reload > span').html(SAEIV.statRemaining + 's');
					
					if(!SAEIV.statRemaining)
					{
						clearInterval(SAEIV.statInterval);
						SAEIV.statInterval = null;
					}
				}, 1000);
			},
			
			getVehicleType: function(type) {
				switch(type)
				{
					case 'BUS':
						return 'Bus';
						
					case 'NAVETTE':
						return 'BatCUB';
						
					case 'TRAM_LONG':
						return 'Tramway long';
						
					case 'TRAM_COURT':
						return 'Tramway court';
						
					default: 
						return 'VÃ©hicule';
				}
			},
			
			// Masquage
			reset: function() {
				$('#legend').hide();
				$('#panWay').hide();
				
				if(SAEIV.wfsVehicles)
					SAEIV.wfsVehicles.removeAll();
				SAEIV.wpsSections.removeAll();
				SAEIV.wpsStops.removeAll();
				
				if(SAEIV.statInterval)
				{
					clearInterval(SAEIV.statInterval);
					SAEIV.statInterval = null;
				}
			},
			
			// Ã‰vÃ©nements
			// RÃ©ponse au chargement des lignes
			onLinesLoad: function(response) {
				$('#cboLines option[value]').remove();
					
				var lines = [];
				for(var i in response.result)
					lines.push({ GID: response.result[i].attributes.GID, LIBELLE: response.result[i].attributes.LIBELLE });
				
				for(var i in lines)
					$('#cboLines').append('<option value="' + lines[i].GID + '">' + lines[i].LIBELLE + '</option>');
				$('#cboLines').selectmenu('refresh');
								
			},
			
			// SÃ©lection d'une ligne
			onSelectLine: function() {
				// Suppression de tout ce qui peut Ãªtre affichÃ©
				SAEIV.reset();
				
				// Affichage aller / retour
				$('#panWay').show();
				$('#legend').show(500);
				$('#optWaySingle').trigger('click');
				$('input[name="optWay"]').checkboxradio('refresh');
			},
			
			// SÃ©lection d'un sens
			onSelectWay: function() {
				var sens = 'ALLER';
				if($('input[name="optWay"]:checked').val() === 'return')
					sens = 'RETOUR';
				
				// On charge les arrÃªts de la ligne
				SAEIV.wpsStops.execute({
					GID: $('#cboLines').val(),
					SENS: sens
				}, null, function() { alert('Ã‰chec du chargement des arrÃªts'); });
				
				// On charge les tronÃ§ons de la ligne
				SAEIV.wpsSections.execute({
					GID: $('#cboLines').val(),
					SENS: sens
				}, null, function() { alert('Ã‰chec du chargement des tronÃ§ons'); });
				
				// Les vÃ©hicules
				if(SAEIV.wfsVehicles)
					SAEIV.wfsVehicles.destroy();
				
				SAEIV.wfsVehicles = new CUB.Layer.Dynamic('', '//data.bordeaux-metropole.fr/wfs?key=QHUHHRI7HD', {
					layerName: 'SV_VEHIC_P',
					loadAllAtOnce: true,
					wfsFilter: '<AND><PropertyIsEqualTo><PropertyName>RS_SV_LIGNE_A</PropertyName><Literal>' + $('#cboLines').val() + '</Literal></PropertyIsEqualTo>' + 
						'<PropertyIsEqualTo><PropertyName>SENS</PropertyName><Literal>' + sens + '</Literal></PropertyIsEqualTo></AND>',
					refreshInterval: 10000,
					selectable: true,
					style: new CUB.Style({ // Style par dÃ©faut
						symbol: 'https://data.bordeaux-metropole.fr/dev/exemples/samples/saeiv/saeiv_bus_ok.png',
						symbolRotation: '${GEOM_O}',				
						symbolRotationIsGeo: true,
						opacity: 100,
						size: 12
					}),
					onLoadEnd: function() {
						$('#reload').show(500);
						SAEIV.updateStats();
					},
					onEntityRender: function(entity, style) {
						if(entity.attributes.BLOQUE == 1 || entity.attributes.NEUTRALISE == 1)
						{
							style.symbolRotation = 90;
							style.size = 14;
							style.symbol = 'https://data.bordeaux-metropole.fr/dev/exemples/samples/saeiv/saeiv_bus_bloque.png';
						}
						else if(entity.attributes.ETAT == 'AVANCE')
							style.symbol = 'https://data.bordeaux-metropole.fr/dev/exemples/samples/saeiv/saeiv_bus_avance.png';
						else if(entity.attributes.ETAT == 'RETARD')
							style.symbol = 'https://data.bordeaux-metropole.fr/dev/exemples/samples/saeiv/saeiv_bus_retard.png';
						
						// A l'heure : on ne fait rien (symbole par dÃ©faut)
					},
					onSelect: SAEIV.onSelectVehicle
				});
			},
			
			// SÃ©lection d'un arrÃªt
			onSelectStop: function(entity) {
				SAEIV.corresData = null;
				SAEIV.wpsStopCorres.execute({
					GID: entity.attributes.GID
				}, function(reponse) {
					SAEIV.corresData = reponse.result;
				});
				
				// On charge les horaires de l'arrÃªt
				SAEIV.wpsStopTimes.execute({
					filter: '<Filter><PropertyIsEqualTo ><PropertyName>RS_SV_ARRET_P</PropertyName><Literal>' + entity.attributes.GID + '</Literal></PropertyIsEqualTo></Filter>',
					propertyname: ['HOR_REAL', 'HOR_APP', 'RS_SV_COURS_A', 'ETAT', 'TYPE']
				}, function(response) {
					var horaires = [];
					
					// On cherche les horaires pour notre chemin
					$(response.result).each(function(idx, rec) {
						var hor = Date.fromString(rec.attributes.HOR_REAL || rec.attributes.HOR_APP); // On
																										// prend
																										// le
																										// thÃ©orique
																										// si
																										// l'horaire
																										// estimÃ©e
																										// n'existe
																										// pas
						
						if(hor >= (new Date()).setMinutes((new Date()).getMinutes() - 5) &&
							hor <= (new Date()).setMinutes((new Date()).getMinutes() + 30))
							horaires.push(rec);
					});
					
					// Tri par horaires
					horaires.sort(function(a, b) { 
						return Date.fromString(a.attributes.HOR_APP) - Date.fromString(b.attributes.HOR_APP)
					});
					
					// Construction du rÃ©sultat
					var res = '';
					var cnt = 0;
					for(var i in horaires)
					{
						if(horaires[i].attributes.ETAT != 'NON_REALISE' && horaires[i].attributes.ETAT != 'NON_RENSEIGNE')
							continue;
						
						var hor;
						var hor_theo = false;
						if(horaires[i].attributes.HOR_REAL)
							hor = Date.fromString(horaires[i].attributes.HOR_REAL)
						else
						{
							hor = Date.fromString(horaires[i].attributes.HOR_APP);
							hor_theo = true;
						}
						
						var remaining = parseInt((hor - new Date()) / 1000);
						
						if(remaining < 60)
							remaining = '< 1min';
						else
							remaining = parseInt(remaining / 60) + 'min';
						
						var time = hor.getHours() + ':' + (hor.getMinutes() + '').padleft(2) + ':' + 
							(hor.getSeconds() + '').padleft(2) + (hor_theo ? '*' : '') + ' (' +
							remaining + ')';
						/*
						 * if(horaires[i].attributes.TYPE == 'DEVIATION') time + '<span
						 * color="#EE8000">' + time + '</span>';
						 */
							
						res += '<div><img src="https://data.bordeaux-metropole.fr/dev/exemples/samples/saeiv/lignes/' + $('#cboLines').val() + '.png" class="line_ico"/> ' + time + '</div>';
						cnt++;
						
						if(cnt > 2)
							break;
					}
					
					SAEIV.showSpit({
						title: '<img src="https://data.bordeaux-metropole.fr/dev/exemples/samples/saeiv/lignes/' + $('#cboLines').val() + '.png" class="line_ico"/> ArrÃªt ' + entity.attributes.LIBELLE, // + '
																																																			// ' +
																																																			// entity.attributes.GID,
						content: (res ? '<h3>Prochains passages :</h3>' + res + '<br/>* Horaires thÃ©oriques' : 'Pas de passages prÃ©vus prochainement pour cette ligne' ) + '<br/><div id="corres">Chargement des correspondances...</div>'
					}, entity.geometry().toPosition());
					
					// Chargement des correspondances
					var fn = function() {
						if(SAEIV.corresData)
							SAEIV.onCorresLoad();
						else
							setTimeout(fn, 500);
					}
					fn();
				});
			},
			
			onCorresLoad: function() {
				var lines = []; // Liste des GID des lignes
				var res = '';
				lines[$('#cboLines').val()] = true; // On remet pas la ligne
													// elle-mÃªme
				
				for(var i in SAEIV.corresData)
				{
					var corres = SAEIV.corresData[i];
					if(lines[corres.attributes.RS_SV_LIGNE_A] /* DÃ©jÃ  listÃ© */)
						continue;
					
					lines[corres.attributes.RS_SV_LIGNE_A] = true;
					
					var hor_theo = corres.attributes.HOR_REAL ? false : true; // True
																				// si
																				// on a
																				// juste
																				// l'horaire
																				// thÃ©orique
					var hor = Date.fromString(hor_theo ? corres.attributes.HOR_APP : corres.attributes.HOR_REAL);
					
					var remaining = parseInt((hor - new Date()) / 1000);
					
					if(remaining < 60)
						remaining = '< 1min';
					else
						remaining = parseInt(remaining / 60) + 'min';
				
					res += '<div><img src="https://data.bordeaux-metropole.fr/dev/exemples/samples/saeiv/lignes/' + corres.attributes.RS_SV_LIGNE_A + '.png" class="line_ico"/> ' +
						'ArrÃªt ' + corres.attributes.ARRET_LIBELLE + ' (Ã  ' + corres.attributes.DIST + 'm) : ' + hor.getHours() + ':' + (hor.getMinutes() + '').padleft(2) + ':' + 
						(hor.getSeconds() + '').padleft(2) + (hor_theo ? '*' : '') + ' (' + remaining + ')</div>';
				}
				
				if(res)
					res = '<h3>Correspondances sur cet arrÃªt :</h3>' + res;
				else
					res = '<b>Pas de correspondances Ã  cet arrÃªt</b>';

				$('#corres').html(res);
			},
			
			// SÃ©lection d'un vÃ©hicule
			onSelectVehicle: function(entity) {
				// Ã‰tat
				var etat = '';
				if(entity.attributes.ETAT == 'AVANCE')
					etat = 'En avance';
				else if(entity.attributes.ETAT == 'RETARD')
					etat = 'En retard';
				else if(entity.attributes.ETAT == 'HEURE')
					etat = 'Ã€ l\'heure';
				else
					etat = 'Inconnu';
				
				// Calcul du retard
				var retard = Math.abs(entity.attributes.RETARD);
				var str_retard = (retard % 60 + '').padleft(2) + 's';
				
				if(retard >= 60)
					str_retard = (parseInt(retard / 60) + 'm') + str_retard;
				
				if(entity.attributes.RETARD < 0)
					str_retard = '-' + str_retard;
				
				// IcÃ´nes
				var icons = '';
				
				if(entity.attributes.SAE == 1)
					icons += '<img class="bus_icon" src="https://data.bordeaux-metropole.fr/dev/exemples/samples/saeiv/bus_sae.png" title="VÃ©hicule Ã©quipÃ© de SAE"/>';
				if(entity.attributes.PMR == 1)
					icons += '<img class="bus_icon" src="https://data.bordeaux-metropole.fr/dev/exemples/samples/saeiv/bus_pmr.png" title="AccÃ¨s PMR"/>';
				if(entity.attributes.ARRET == 1)
					icons += '<img class="bus_icon" src="https://data.bordeaux-metropole.fr/dev/exemples/samples/saeiv/bus_stop.png" title="VÃ©hicule arrÃªtÃ© Ã  un arrÃªt"/>';
				if(entity.attributes.BLOQUE == 1 || entity.attributes.NEUTRALISE == 1)
					icons += '<img class="bus_icon" src="shttps://data.bordeaux-metropole.fr/dev/exemples/samples/saeiv/bus_warning.png" title="VÃ©hicule bloquÃ© ou neutralisÃ©"/>';
				
				SAEIV.showSpit({ 
					title: SAEIV.getVehicleType(entity.attributes.TYPE),
					content: '<table><tr><td><b>Destination :</b></td><td>' + entity.attributes.TERMINUS + '</td></tr>' +
						'<tr><td>Ã‰tat :</td><td>' + etat + '</td></tr>' +
						'<tr><td>Retard :</td><td>' + str_retard + '</td></tr>' +
						'<tr><td>Vitesse :</td><td>' + entity.attributes.VITESSE + ' km/h</td></tr></table>' +
						icons + '<div style="clear: both"></div>'
				}, entity.geometry().toPosition());
			}
		};
		
		// Cette fonction est dÃ©clenchÃ©e une fois le navigateur prÃªt
		CUB.ready(function() {
			// Initialise l'API
			CUB.init('map', {
				hidejQuery: true
			});
			new CUB.Panel.Zoom();
			
			setTimeout(function() {
				CUB.moveToExtent(new CUB.Extent(1389619.63, 4186106.38, 1448886.26, 4209627.83));
				CUB.resize();
			}, 1500);
			
			SAEIV.init();			
		});	
		-->
	</script>
	
	</html>