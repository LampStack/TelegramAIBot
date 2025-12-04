<?php

include './config.php';
include './api.php';

function LampStack($method,$datas=[]){
    global $apiKey;
    $url = 'https://api.telegram.org/bot' . $apiKey . '/' . $method;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch, CURLOPT_POSTFIELDS,$datas);
    $res = curl_exec($ch);
    if(curl_error($ch)){
        return json_decode(curl_error($ch));
    }else{
        return json_decode($res);
    }
}


$MySQLi = new mysqli('localhost', $DB['username'], $DB['password'], $DB['dbname']);
$MySQLi->query("SET NAMES 'utf8'");
$MySQLi->set_charset('utf8mb4');
if($MySQLi->connect_error) die;


$update = json_decode(file_get_contents('php://input'));

if(isset($update->message)) {
    @$msg = $update->message->text;
    @$chat_id = $update->message->chat->id;
    @$from_id = $update->message->from->id;
    @$chat_type = $update->message->chat->type;
    @$message_id = $update->message->message_id;
    @$name = $update->message->from->first_name;
}

if(isset($update->callback_query)) {
@$callback_query_data = $update->callback_query->data;
@$chat_id = $update->callback_query->message->chat->id;
@$from_id = $update->callback_query->from->id;
@$chat_type = $update->callback_query->message->chat->type;
@$message_id = $update->callback_query->message->message_id;
@$name = $update->callback_query->from->first_name;
}



@$getDB = mysqli_fetch_assoc(mysqli_query($MySQLi, "SELECT * FROM `user` WHERE `id` = '{$from_id}' LIMIT 1"));
if(!$getDB) $MySQLi->query("INSERT INTO `user` (`id`) VALUES ('{$from_id}')");



$start_kbd = json_encode([
    'inline_keyboard' => [
        [['text' => '⚙️ Choose Model', 'callback_data' => 'chooseModel']],
        [['text' => '🔗 our Channel', 'url' => 'https://t.me/osClub'], ['text' => 'Developer 👨🏻‍💻', 'url' => 'https://t.me/LampStack']],
    ]
]);



//                      start the bot                      //
if($msg === '/start'){
    $MySQLi->query("UPDATE `user` SET `step` = NULL WHERE `id` = '{$from_id}' LIMIT 1");
    $current_model = $getDB['model'];
    LampStack('sendMessage', [
        'chat_id' => $from_id,
        'text' => "<b>👋 Welcome to Auddo — your all-in-one AI assistant!</b>\n\n⚡ Ask anything, generate images, translate, analyze, or create content with multiple powerful AI models. 🚀\n\n<b>Type your request or choose an option below to get started! ✨</b>\n\n💡 Current Model : <code>$current_model</code>",
        'parse_mode' => 'HTML',
        'reply_to_message_id' => $message_id,
        'reply_markup' => $start_kbd
    ]);
    $MySQLi->close();
    die;
}



//                      back to main menu                      //
if($callback_query_data === 'backToMainMenu'){
    $MySQLi->query("UPDATE `user` SET `step` = NULL WHERE `id` = '{$from_id}' LIMIT 1");
    $current_model = $getDB['model'];
    LampStack('editMessageText',[
    'chat_id' => $from_id,
    'message_id' => $message_id,
    'text' => "<b>👋 Welcome to Auddo — your all-in-one AI assistant!</b>\n\n⚡ Ask anything, generate images, translate, analyze, or create content with multiple powerful AI models. 🚀\n\n<b>Type your request or choose an option below to get started! ✨</b>\n\n💡 Current Model : <code>$current_model</code>",
    'parse_mode'=>"HTML",
    'reply_markup' => $start_kbd
    ]);
    $MySQLi->close();
    die;
}



//                      choose model                      //
if($callback_query_data == 'chooseModel'){
    $text = "<b>Please choose which type of models you want to view 👇</b>\n\nYou can see the list of <b>Chat Models 💬</b> or <b>Image Generation Models 🌄</b>\n\nOnce you select and <b>activate a model</b>, it will become your default, and all your future messages will be answered using that model ⚡️";

    $buttons[] = [['text' => '💬 Chat Model\'s', 'callback_data' => 'chooseChatModel'], ['text' => 'Image Model\'s 🌄', 'callback_data' => 'chooseImageModel']];
    $buttons[] = [['text' => '🔙', 'callback_data' => 'backToMainMenu']];

    LampStack('editMessageText',[
        'chat_id' => $from_id,
        'message_id' => $message_id,
        'text' => $text,
        'parse_mode' => "HTML",
        'reply_markup' => json_encode([
            'inline_keyboard' => $buttons
        ])
    ]);
    $MySQLi->close();
    die;
}



//                      choose chat model                      //
if($callback_query_data == 'chooseChatModel'){
    
    $text = "🔖  The list of chat model's :\n\n❗️ After selecting any model, the response to all your messages will be processed by that model.\n\nAll models are <b>*Free*</b> and the processing speed is different for each model.";
    $index = 0;
    foreach ($models['chat'] as $model) {
        $buttons[] = [['text' => $model, 'callback_data' => 'selectChatModel_' . $index]];
        $index++;
    }
    $buttons[] = [['text' => '🔙', 'callback_data' => 'backToMainMenu']];

    LampStack('editMessageText',[
        'chat_id' => $from_id,
        'message_id' => $message_id,
        'text' => $text,
        'parse_mode' => "HTML",
        'reply_markup' => json_encode([
            'inline_keyboard' => $buttons
        ])
    ]);

    $MySQLi->close();
    die;
}

if(explode('_', $callback_query_data)[0] == 'selectChatModel'){
    $model_index = explode('_', $callback_query_data)[1];
    $model_name = $models['chat'][$model_index];
    $MySQLi->query("UPDATE `user` SET `model` = '{$model_name}' WHERE `id` = '{$from_id}' LIMIT 1");
    LampStack('answercallbackquery', [
        'callback_query_id' => $update->callback_query->id,
        'text' => "🌱 Default model set to -> $model_name (chat)",
        'show_alert' => false
    ]);
    $current_model = $model_name;
    LampStack('editMessageText',[
    'chat_id' => $from_id,
    'message_id' => $message_id,
    'text' => "<b>👋 Welcome to Auddo — your all-in-one AI assistant!</b>\n\n⚡ Ask anything, generate images, translate, analyze, or create content with multiple powerful AI models. 🚀\n\n<b>Type your request or choose an option below to get started! ✨</b>\n\n💡 Current Model : <code>$current_model</code>",
    'parse_mode'=>"HTML",
    'reply_markup' => $start_kbd
    ]);
    $MySQLi->close();
    die;
}



//                      choose image model                      //
if($callback_query_data == 'chooseImageModel'){
    
    $text = "🔖  The list of text-to-image model's :\n\n❗️ After selecting any model, the response to all your messages will be processed by that model.\n\nAll models are <b>*Free*</b> and the processing speed is different for each model.";
    $index = 0;
    foreach ($models['text_to_image'] as $model) {
        $buttons[] = [['text' => $model, 'callback_data' => 'selectImageModel_' . $index]];
        $index++;
    }
    $buttons[] = [['text' => '🔙', 'callback_data' => 'backToMainMenu']];

    LampStack('editMessageText',[
        'chat_id' => $from_id,
        'message_id' => $message_id,
        'text' => $text,
        'parse_mode' => "HTML",
        'reply_markup' => json_encode([
            'inline_keyboard' => $buttons
        ])
    ]);

    $MySQLi->close();
    die;
}

if(explode('_', $callback_query_data)[0] == 'selectImageModel'){
    $model_index = explode('_', $callback_query_data)[1];
    $model_name = $models['text_to_image'][$model_index];
    $MySQLi->query("UPDATE `user` SET `model` = '{$model_name}' WHERE `id` = '{$from_id}' LIMIT 1");
    LampStack('answercallbackquery', [
        'callback_query_id' => $update->callback_query->id,
        'text' => "🌱 Default model set to -> $model_name (text-to-image)",
        'show_alert' => false
    ]);
    $current_model = $model_name;
    LampStack('editMessageText',[
    'chat_id' => $from_id,
    'message_id' => $message_id,
    'text' => "<b>👋 Welcome to Auddo — your all-in-one AI assistant!</b>\n\n⚡ Ask anything, generate images, translate, analyze, or create content with multiple powerful AI models. 🚀\n\n<b>Type your request or choose an option below to get started! ✨</b>\n\n💡 Current Model : <code>$current_model</code>",
    'parse_mode'=>"HTML",
    'reply_markup' => $start_kbd
    ]);
    $MySQLi->close();
    die;
}
















//                      answer the query                      //
if($msg){

    $bytez = new BytezAPI($bytez_api_key);

    $current_model = $getDB['model'];

    if(in_array($current_model, $models['chat'])){
        $chatResponse = $bytez->chat(
            $current_model,
            [["role" => "user", "content" => $msg]]
        );
        $result = json_decode($chatResponse, true)['output']['content'];
        LampStack('sendMessage', [
            'chat_id' => $from_id,
            'text' => $result,
            'parse_mode' => 'MarkDown',
            'reply_to_message_id' => $message_id,
        ]);
    } else if(in_array($current_model, $models['text_to_image'])){
        $first_message_id = LampStack('sendMessage', [
            'chat_id' => $from_id,
            'text' => "<b>♻️ Creating image, please wait ...</b>",
            'parse_mode' => 'HTML',
            'reply_to_message_id' => $message_id,
        ])->result->message_id;
        $oldtime = microtime(true);
        $imageResponse = $bytez->text_to_image(
            $current_model,
            $msg
        );
        $result = json_decode($imageResponse, true)['output'];
        $TimeSpend = round(((microtime(true) - $oldtime)), 3);
        LampStack('sendPhoto', [
            'chat_id' => $from_id,
            'photo' => $result,
            'caption' => "Image created in $TimeSpend seconds",
            'parse_mode' => 'MarkDown',
            'reply_to_message_id' => $message_id,
        ]);
        LampStack('deleteMessage', [
            'chat_id' => $from_id,
            'message_id' => $first_message_id,
        ]);
    }

    $MySQLi->close();
    die;
}






































$MySQLi->close();
die;