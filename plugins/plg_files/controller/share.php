<?php

function share_plg_files($pPlugin, $pView, $pController) {
	global $plg_files_parameter;
	
	if(!empty($plg_files_parameter)) {
		$return['parameter'] = $plg_files_parameter;
		
		if(!empty($plg_files_parameter['allowDeeper']) && $plg_files_parameter['allowDeeper'] === TRUE) {
			$folder = 'home' . '/' . $pController;
			
			$return['views'][] = rtrim( $folder, '/' ) . '/';
		} else {
			$folder = 'home' . '/' . $pController;
			
			$return['views'][] = rtrim( $folder, '/' );
		}
		
		return $return;
	}
	
	return array("parameter"=>array('test', 'test2', 'test3'));
}

?>