<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xml:lang="fr" xmlns="http://www.w3.org/1999/xhtml">
<head>
<script
	src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<link rel="stylesheet" href="jquery/jquery.mobile.icons-1.4.2.min.css" />
<link rel="stylesheet"
	href="jquery/jquery.mobile.structure-1.4.2.min.css" />
<title>Carte simple</title>
<?php
session_start();
if (! isset($_SESSION['username'])) {
    header("Location: http://localhost/phpProject/src/html/login.html");
}
?>

<script type="text/javascript"
	src="http://data.bordeaux-metropole.fr/api/cub.xjs?key=G0NDMI15J2"></script>

<script type="text/javascript">
	var abc, bikes;
	CUB.ready(function() {
		CUB.init('zone_carte');
		CUB.events.onLayersLoadEnd = null; // Premi�re fois seulement
		abc = new CUB.Layer.Dynamic();
		bikes = new CUB.Layer.Dynamic();
	});

	function mapVcub() {
		$.ajax({
			  type: "POST",
			  url: "recordAction.php",
			  data: { string: "LOOK UP VCUB"}
			}); 
		abc.destroy();
		bikes.destroy();
		abc = new CUB.Layer.Dynamic();
		CUB.ready(function() {
			// Création de la couche

			bikes = new CUB.Layer.Dynamic('',
					'http://data.bordeaux-metropole.fr/wfs?key=G0NDMI15J2', {
						layerName : 'TB_STVEL_P',
						style : new CUB.Style({
							color : new CUB.Color('#6892EB')
						}),
						selectable: true,
						onSelect: function(entity) {
							$.ajax({
								  type: "POST",
								  url: "recordAction.php",
								  data: { string: "LOOK UP VCUBS STATION " + entity.attributes.NOM}
								}); 
							alert("LIGNES CORRESPONDANTS : " + entity.attributes.LIGNCORR);
						}
					});
		});
	};
	function mapTraffic() {
		$.ajax({
			  type: "POST",
			  url: "recordAction.php",
			  data: { string: "LOOK UP TRAFFIC"}
			}); 
		abc.destroy();
		bikes.destroy();
		bikes = new CUB.Layer.Dynamic();
		CUB.ready(function() {
			// Création de la couche

			abc = new CUB.Layer.Dynamic('',
					'http://data.bordeaux-metropole.fr/wfs?key=G0NDMI15J2', {
						layerName : 'CI_TRAFI_L',
						style : new CUB.Style({
							color : new CUB.Color('#FF1515')
						})
					});
		});
	};

	function mapRedraw() {
		var dropdown = document.getElementById("chooseLine");
		var lineName = dropdown.options[dropdown.selectedIndex].value;
		abc.destroy();
		bikes.destroy();
		bikes = new CUB.Layer.Dynamic();
		$.ajax({
			  type: "POST",
			  url: "recordAction.php",
			  data: { string: "LOOK UP LINE " + lineName}
			}); 
		CUB
				.ready(function() {
					// Création de la couche

					abc = new CUB.Layer.Dynamic(
							'',
							'http://data.bordeaux-metropole.fr/wfs?key=G0NDMI15J2',
							{
								layerName : 'TB_CHEM_L',
								style: new CUB.Style({
									outlineColor: new CUB.Color(CUB.Color.colors.BLUE),
									outlineWidth: 2,
									label: '${NUMCOMLI}', // Libell� de l'�tiquette
									labelColor: new CUB.Color(CUB.Color.colors.BLACK), // Couleur du libell� de l'�tiquette
									labelOutlineColor: new CUB.Color(CUB.Color.colors.WHITE), // Couleur de contour du libell� de l'�tiquette
									labelOutlineWidth: 2, // Taille du contour du libell� de l'�tiquette
									labelSize: 13, // Taille du libell� de l'�tiquette
									labelMaxScaledenom: 5000 // Echelle maximum d'affichage des libell�s
								}),
								wfsFilter : '<PropertyIsEqualTo><PropertyName>NUMEXPLO</PropertyName><Literal>'
										+ lineName
										+ '</Literal></PropertyIsEqualTo>',
										selectable: true,
										onSelect: function(entity) {
											bikes.destroy();
											var lineName = entity.attributes.NOMCOMLI;
											$.ajax({
												  type: "POST",
												  url: "recordAction.php",
												  data: { string: "LOOK UP VCUBS OF " + lineName}
												}); 
											lineName = lineName.replace("Tram ", "");
											var filter;
											if(lineName == 'C'){
												filter = '<Or><PropertyIsLike wildCard="*"'
													+' singleChar="." escape="!"><PropertyName>LIGNCORR'
													+'</PropertyName><Literal>*/C/*</Literal></PropertyIsLike>'
													+'<PropertyIsLike wildCard="*" singleChar="." escape="!">'
													+'<PropertyName>LIGNCORR</PropertyName><Literal>'
													+'C/*</Literal></PropertyIsLike><PropertyIsEqualTo>'
													+'<PropertyName>LIGNCORR</PropertyName><Literal>*/C'
													+'</Literal></PropertyIsEqualTo></Or>';
											}else{
												filter = '<PropertyIsLike wildCard="*" singleChar="." escape="!"><PropertyName>LIGNCORR</PropertyName><Literal>'
													+'*'+lineName+'*'
													+'</Literal></PropertyIsLike>';
											}
											bikes = new CUB.Layer.Dynamic('',
													'http://data.bordeaux-metropole.fr/wfs?key=G0NDMI15J2', {
														layerName : 'TB_STVEL_P',
														style : new CUB.Style({
															color : new CUB.Color('#6892EB')
														}),
														wfsFilter : filter,
														selectable: true,
														onSelect: function(entity) {
															$.ajax({
																  type: "POST",
																  url: "recordAction.php",
																  data: { string: "LOOK UP VCUBS STATION " + entity.attributes.NOM}
																}); 
															alert("LIGNES CORRESPONDANTS : " + entity.attributes.LIGNCORR);
														}
													});
										}
							});
					
				});
	};
</script>

<style>
body, html {
	margin: 0;
	padding: 0;
}

#zone_carte {
	position: absolute;
	width: 100%;
	height: 100%;
	display: flex;
	flex-direction: column;
}
</style>
</head>

<body>
	<div id="zone_carte" style="width: 80%; height: 100%; float: left;"></div>
	<div id="sideMenu"
		style="width: 20%; height: 50%; background-color: blue; float: right;">

		<div id="navitia-line-ahah" class="navitia-ahah">
			<div>
				<button name="chooseTraffic" id="chooseTraffic"
					onclick="mapTraffic()">SHOW TRAFFIC</button>
			</div>
			<div>
				<button name="chooseVcub" id="chooseVcub" onclick="mapVcub()">SHOW
					VCUB</button>
			</div>
			<div>
				<a href="/phpProject/src/php/addressSearch.php"><button
						name="chooseVcub" id="chooseVcub" onclick="mapVcub()">SEARCH
						ADDRESS</button></a>
			</div>
			<div class="form-item" id="edit-navitia-line-wrapper">
				<label for="edit-navitia-line">Selectionner votre ligne </label> <select
					name="chooseLine" id="chooseLine" class="form-select"
					onchange="mapRedraw()"><option value="0" selected="selected">Choisissez
						une ligne</option>
					<optgroup label="Tram">
						<option value="59">Tram A</option>
						<option value="60">Tram B</option>
						<option value="61">Tram C</option>
					</optgroup>
					<optgroup label="Navette Fluviale">
						<option value="69">BAT³</option>
					</optgroup>
					<optgroup label="Bus">
						<option value="1">Lianes 1</option>
						<option value="2">Lianes 2</option>
						<option value="3">Lianes 3</option>
						<option value="4">Lianes 4</option>
						<option value="5">Lianes 5 Nord</option>
						<option value="5">Lianes 5 Sud</option>
						<option value="6">Lianes 6</option>
						<option value="7">Lianes 7</option>
						<option value="8">Lianes 8</option>
						<option value="9">Lianes 9</option>
						<option value="10">Lianes 10</option>
						<option value="11">Lianes 11</option>
						<option value="14">Navette Relais Tram C</option>
						<option value="15">Lianes 15</option>
						<option value="16">Lianes 16</option>
						<option value="18">Navette Stade Matmut Atlantique</option>
						<option value="19">Navette Arena</option>
						<option value="20">Ligne 20</option>
						<option value="21">Ligne 21</option>
						<option value="22">Ligne 22</option>
						<option value="23">Ligne 23</option>
						<option value="24">Ligne 24</option>
						<option value="25">Ligne 25</option>
						<option value="26">Ligne 26</option>
						<option value="27">Ligne 27</option>
						<option value="28">Ligne 28</option>
						<option value="29">Ligne 29</option>
						<option value="30">Ligne 30</option>
						<option value="32">Corol 32</option>
						<option value="33">Corol 33</option>
						<option value="34">Corol 34</option>
						<option value="35">Corol 35</option>
						<option value="36">Corol 36</option>
						<option value="37">Corol 37</option>
						<option value="38">Flexo 38 / Resago</option>
						<option value="40">Citéis 40</option>
						<option value="41">Citéis 41</option>
						<option value="42">Citéis 42</option>
						<option value="43">Citéis 43</option>
						<option value="44">Citéis 44</option>
						<option value="45">Citéis 45</option>
						<option value="46">Citéis 46</option>
						<option value="47">Citéis 47</option>
						<option value="48">Flexo 48</option>
						<option value="49">Flexo 49</option>
						<option value="50">Flexo 50</option>
						<option value="51">Flexo 51</option>
						<option value="52">Flexo 52</option>
						<option value="54">Flexo 54</option>
						<option value="55">Flexo 55</option>
						<option value="57">Flexo 57</option>
						<option value="58">Ligne 58</option>
						<option value="62">Ligne 62</option>
						<option value="63">Citéis 63</option>
						<option value="64">Ligne 64</option>
						<option value="66">Navette Travaux Barrière du Médoc</option>
						<option value="67">Ligne 67</option>
						<option value="68">Flexo 68</option>
						<option value="71">Ligne 71</option>
						<option value="72">Citéis 72</option>
						<option value="73">Ligne 73</option>
						<option value="74">Spécifique 74</option>
						<option value="76">Ligne 76</option>
						<option value="77">Spécifique 77</option>
						<option value="78">Spécifique 78</option>
						<option value="79">Spécifique 79</option>
						<option value="80">Ligne 80</option>
						<option value="81">Spécifique 81</option>
						<option value="82">Spécifique 82</option>
						<option value="83">Ligne 83</option>
						<option value="85">Spécifique 85</option>
						<option value="86">Spécifique 86</option>
						<option value="87">Ligne 87</option>
						<option value="88">Spécifique 88</option>
						<option value="89">Citéis 89</option>
						<option value="90">Ligne 90</option>
						<option value="91">Ligne 91</option>
						<option value="92">Ligne 92</option>
						<option value="93">Ligne 93</option>
						<option value="94">Spécifique 94</option>
						<option value="95">Spécifique 95</option>
						<option value="96">Spécifique 96</option>
					</optgroup></select>
			</div>
			<div>
				<a href="/phpProject/src/php/logOff.php"><button>LOG OUT</button></a>
			</div>
		</div>
	</div>
</body>

</html>