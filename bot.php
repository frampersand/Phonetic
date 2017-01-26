<?php
define('BOT_TOKEN', '/***** TELEGRAM BOT ID *****/');
require_once("telegram.php");
$telegram = new Telegram(BOT_TOKEN);
$result = $telegram->getData();

if (isset($result["inline_query"])) {
	$id = $result["inline_query"]["id"];
	$query = $result["inline_query"]["query"];
	$user = $result["inline_query"]["from"]["id"];
	$url = "http://www.macmillandictionary.com/dictionary/british/".strtolower($query);
	$data = crawl($url);
	$word = ucfirst($query);
	$responses[] = array("type" => "audio", "id" => "0", "audio_url" => $data["talks"], "title" => $word);
	$content = array("inline_query_id" => $result["inline_query"]["id"], "results" => json_encode($responses), "cache_time" => 60);
    $telegram->inlineQuery($content);
} else {
	$text = $result['message']['text'];
	$user = $result['message']['chat']['id'];
	$url = "http://www.macmillandictionary.com/dictionary/british/".strtolower($text);
	$data = crawl($url);
	$response = "<b>".ucfirst($text)."</b> ({$data["type"]})\n{$data["pron"]}";  
	if($data["pron"] == '')
		$response = "I'm sorry, I couldn't find that.";
    $content = array('chat_id' => $result["message"]["chat"]["id"], 'text' => $response, "parse_mode" => 'html');	
	$telegram->sendMessage($content);
}

function crawl($url){
	$contents = file_get_contents($url.$text);
    $contents = str_replace(array("\t","\r","\n"), "", $contents);
    preg_match_all("/<span class=\"PART-OF-SPEECH\"><span class=\"SEP PART-OF-SPEECH-before\"> <\/span>(.*?)<\/span>/i", $contents, $type);
    preg_match_all("/<span class=\"SEP PRON-before\"> \/<\/span>(.*?)<span class=\"SEP PRON-after\">/i", $contents, $pron);
    preg_match_all("/class=\"sound audio_play_button\".*?'pronaudio', '(.*?)'/i", $contents, $talks);
	$data = [
		"type" => $type[1][0],
		"pron" => $pron[1][0],
		"talks" => $talks[1][0]
	];
	return $data;
}
?>