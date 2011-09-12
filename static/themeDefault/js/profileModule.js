var current_city;
var current_country;
var exec_url;

var country_list = {};
var city_list = {};

var countryDiv;
var cityDiv;

function profileModule_cityInit(countrydivid,citydivid,cityid,url){
	exec_url = url;
	countryDiv = document.getElementById(countrydivid);
	cityDiv = document.getElementById(citydivid);
	$.post(exec_url, {
		"jquery": "ProfileModule",
		"action":"init",
		"city_id" : cityid	
	},
	function(data){
		current_city = data.city_id;
		current_country = data.country_id;
		country_list = data.country_list;
		city_list[data.country_id] = data.city_list;
		profileModule_redrawCountryCity();
	}, "json");
}

function profileModule_redrawCountryCity(){
	var options = '';
	for(var i in country_list){
		if(country_list[i].id == current_country)
			options += '<option selected="selected">'+country_list[i].name+'</option>';
		else
			options += '<option>'+country_list[i].name+'</option>';
	}
	countryDiv.innerHTML = '<select  onchange="profileModule_countryChange(this)">'+options+'</select>';
	cityDiv.innerHTML = '';
	var cityInput = document.createElement('INPUT');
	cityInput.type = 'hidden';
	cityInput.value = city_list[current_country][current_city].id
	cityDiv.appendChild(cityInput)
	
	var citySelect = document.createElement('SELECT');
	citySelect.name = 'city_id';
	cityDiv.appendChild(citySelect)
	var co = 0;
	for(var k in city_list[current_country]){
		var opt = document.createElement('OPTION');
		opt.value = city_list[current_country][k].id;
		opt.innerHTML = city_list[current_country][k].name;
		co++;
		citySelect.appendChild(opt);
		if(city_list[current_country][k].id == current_city)
			citySelect.selectedIndex = co-1;
		

		
	}
	
}

function profileModule_countryChange(obj){
	for(var i in country_list)
		if(country_list[i].name == obj.value){
			$.post(exec_url, {
				"jquery": "ProfileModule",
				"action":"getCityList",
				"country_id" : country_list[i].id
			},
			function(data){
				current_country = data.country_id;
				current_city = data.city_id;
				city_list[data.country_id] = data.city_list;
				profileModule_redrawCountryCity();
			}, "json");
		}
		
}


////////////////////
function profileModule_addFriend(id,exec_url){
	$.post(exec_url, {
		"jquery": "ProfileModule",
		"action":"addFriend",
		"id" : id	
	},
	function(data){
		document.location.reload();
	}, "json");
}

function profileModule_removeFriend(id,exec_url){
	$.post(exec_url, {
		"jquery": "ProfileModule",
		"action":"removeFriend",
		"id" : id	
	},
	function(data){
		document.location.reload();
	}, "json");
}
