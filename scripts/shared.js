function loadSharedMain() {
	hideLoadingScreen();
	hashChanged(getHash());
	
	$('#menu').html('');
	
	var tile = jQuery('<li/>', {
		id: 'menu-username',
		class: 'menu-tile'
	}).on("click", new Function('logoutShare()')).appendTo('#menu');
	
	jQuery('<a/>', {
		text: 'Von Freigabe abmelden'
	}).appendTo(tile);
}

function logoutShare() {
	setStorage("share","");
	location.href = '';
}
