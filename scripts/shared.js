function loadSharedMain() {
	hideLoadingScreen();
	hashChanged(getHash());
	
	document.querySelector('#menu').innerHTML = '';
	
	var tile = document.createElement('li');
		tile.id = 'menu-username';
		tile.className = 'menu-tile';
		tile.addEventListener('click', new Function('logoutShare()'), false);
	document.querySelector('#menu').appendChild(tile);


	var link = document.createElement('a');
		link.innerHTML = 'Von Freigabe abmelden';
	tile.appendChild(link);
}

function logoutShare() {
	setStorage("share","");
	location.href = '';
}
