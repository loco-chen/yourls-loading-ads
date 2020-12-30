<?php
/*
Plugin Name: Loading Ads
Plugin URI: https://github.com/loco-chen/yourls-loading-ads
Description: This plugin enables the feature of show ad for your short URLs!
Version: 1.0
Author: Loco
Author URI: https://github.com/loco-chen
*/

// No direct call
if( !defined( 'YOURLS_ABSPATH' ) ) die();

//key
define( 'KEY', 'jh$uI@HU.136#' );
$VER_HASH = array();
$VER_HASH[] = md5( date( "YmdHi", time() ).KEY );
$VER_HASH[] = md5( date( "YmdHi", time()-30 ).KEY );
define( 'VER_HASH', $VER_HASH );

// Hook our custom function into the 'pre_redirect' event
yourls_add_action( 'pre_redirect', 'loading_ads' );

// Custom function that will be triggered when the event occurs
function loading_ads( $args ) {	
	$loco_loading_array = json_decode(yourls_get_option('LocoLoadingAds'), true);
	$locoLoadingAdsSettings = yourls_get_option('LocoLoadingAdsSettings');
	if ($loco_loading_array === false || $locoLoadingAdsSettings === false ) {
		yourls_add_option('LocoLoadingAds', 'null');
		$loco_loading_array = json_decode(yourls_get_option('LocoLoadingAds'), true);
		if ($loco_loading_array === false) {
			die("Unable to properly enable adshow due to an apparent problem with the database.");
		}
	}

	$VER_HASH = VER_HASH[0];
	$loco_loading_fullurl = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	$loco_loading_urlpath = parse_url( $loco_loading_fullurl, PHP_URL_PATH );
	$loco_loading_pathFragments = explode( '/', $loco_loading_urlpath );
	$loco_loading_short = end( $loco_loading_pathFragments );
	
	if( array_key_exists( $loco_loading_short, $loco_loading_array ) && !empty($locoLoadingAdsSettings) ){
		if( isset( $_POST[ 'hash' ] ) && in_array($_POST[ 'hash' ], VER_HASH) ){ //Check if password is submited, and if it matches the DB
			$url = $args[ 0 ];
			header("Location: $url"); //Redirects client
			die();
		} else {
			//Displays main "Insert Password" area
			echo <<<PWP
<!DOCTYPE html>
<html>
<head>
<title>loading...</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no">
<meta name="renderer" content="webkit">
<meta http-equiv="x-ua-compatible" content="IE=edge,chrome=1">
<style>
body{font-family:"Helvetica Neue",Helvetica,Arial,sans-serif;font-size:16px;line-height:1.42857143;color:#333;background-color:#fff;margin:0}
*{-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box}
.box{left:50%;top:30%;width:300px;margin-left:-150px;height:200px;bottom:10px;padding:10px;word-break:break-all;position:fixed}
.logo{text-align:center;margin-bottom:20px}
.tip{text-align:center;margin-bottom:15px;letter-spacing:1px}
.progress{height:20px;margin-bottom:20px;overflow:hidden;background-color:#f5f5f5;border-radius:4px;-webkit-box-shadow:inset 0 1px 2px rgba(0,0,0,.1);box-shadow:inset 0 1px 2px rgba(0,0,0,.1);height:8px}
.progress-bar{float:left;width:0%;height:100%;font-size:12px;line-height:20px;color:#fff;text-align:center;background-color:#337ab7;-webkit-box-shadow:inset 0 -1px 0 rgba(0,0,0,.15);box-shadow:inset 0 -1px 0 rgba(0,0,0,.15);-webkit-transition:width .6s ease;-o-transition:width .6s ease;transition:width .6s ease}
.progress-bar-success{background-color:#5cb85c}
</style>
</head>
<body>
<div class="box">
	<div class="logo">
	
	$locoLoadingAdsSettings

	</div>
	<div class="tip">
		<small>
		<div>
			正在加载数据，长时间未响应请刷新！
		</div>
		</small>
	</div>
	<div class="progress">
		<div id="progress-bar" class="progress-bar progress-bar-success" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="width:0%">
		</div>
	</div>
	<form name="myForm" id="myForm" action="" method="POST">
		<input type="hidden" name="hash" value="$VER_HASH">
	</form>
</div>
<script>
function progress(p){document.getElementById("progress-bar").style.width=p+"%"}
setTimeout(function(){progress("5");},100);
window.onload = function() {
    //setTimeout(function(){progress("5");setTimeout(function(){progress("60");document.getElementById("myForm").submit();setTimeout(function(){progress("95");},800);},1200);},800);
   setTimeout(function(){progress("10");setTimeout(function(){progress("20");setTimeout(function(){progress("30");setTimeout(function(){progress("40");setTimeout(function(){progress("50");setTimeout(function(){progress("60");setTimeout(function(){progress("80");document.getElementById("myForm").submit();setTimeout(function(){progress("95");},800);},800);},800);},800);},800);},800);},800);},800);
}
</script>
</body>
</html>
PWP;
			die();
		}
	}
}

// Register plugin page in admin page
yourls_add_action( 'plugins_loaded', 'loco_loading_display_panel' );
function loco_loading_display_panel() {
	yourls_register_plugin_page( 'loco_loading', 'Loading Ads', 'loco_loading_display_page' );
}

// Function which will draw the admin page
function loco_loading_display_page() {
	
	if ( isset( $_POST[ 'LocoLoadingAds' ] ) ){
		
		$loco_loading_array =  json_decode(yourls_get_option('LocoLoadingAds'), true);
		if( array_key_exists( $_POST[ 'LocoLoadingAds' ], $loco_loading_array ) ){
			unset($loco_loading_array[ $_POST[ 'LocoLoadingAds' ] ]);
		}else{
			$loco_loading_array[$_POST[ 'LocoLoadingAds' ]] = 'enable';
		}
		yourls_update_option( 'LocoLoadingAds', json_encode( $loco_loading_array ) );

	} else {
		if( isset( $_POST['loco_Loading_Ads_Settings'] ) ) {
			yourls_update_option( 'LocoLoadingAdsSettings', $_POST['loco_Loading_Ads_Settings']);
			echo "<p style='color: green'>Success!</p>";
		}
		loco_loading_process_display();
	}
}



// Add our QR Code Button to the Admin interface
yourls_add_filter( 'table_add_row_cell_array', 'loco_loading_ads_checkbox' );
function loco_loading_ads_checkbox($cells, $keyword, $url, $title, $ip, $clicks, $timestamp ){
	
	$cells['keyword']['template'] = '<input type="checkbox" class="loco_loading_ads_checkbox" title="Loading Ads" value="%keyword%"%checked%> <a href="%shorturl%">%keyword_html%</a>';
	
	$cells['keyword']['keyword'] = $keyword;
	$cells['keyword']['checked'] = loco_loading_ads_checked( $keyword );

  return $cells;
}
function loco_loading_ads_checked($keyword){
	
		$loco_loading_array =  json_decode(yourls_get_option('LocoLoadingAds'), true);
		
		$checked = '';
		if( array_key_exists( $keyword, $loco_loading_array ) ){
			$checked = " checked";
		}
		
		return $checked;
}

yourls_add_action( 'html_footer', 'loco_loading_ads_submit' );
function loco_loading_ads_submit() {
	
	echo <<<TB
	<script type="text/javascript">
	$(".loco_loading_ads_checkbox").change(function() {
　　	$.post("./plugins.php?page=loco_loading",{LocoLoadingAds:$(this).attr('value')},function(result){
		});
	});
	</script>
TB;
	
}


//Display Form
function loco_loading_process_display() {

    $loco_Loading_Ads_Settings = yourls_get_option('LocoLoadingAdsSettings');

    echo <<<HTML
        <main>
            <h2>Loading Ads Settings</h2>
            <form method="post">
            <p>
                <textarea name="loco_Loading_Ads_Settings">$loco_Loading_Ads_Settings</textarea>
            </p>
            <p><input type="submit" value="Save" class="button" /></p>
            </form>
        </main>
HTML;
}
?>