<?php

//Load template from specific pages
add_filter('page_template', function ($page_template) {
    if (get_page_template_slug() == 'template-login.php') {
        $page_template = dirname(__FILE__) . 'template/template-login.php';
    }
    return $page_template;
});

/**
 * Add "Custom" template to page attirbute template section.
 */
add_filter('theme_page_templates', function ($post_templates, $wp_theme, $post, $post_type) {
    $post_templates['template-login.php'] = __('NTip Login Page');
    return $post_templates;
}, 10, 4);

//get payment result from NEAR
/**
 * @param string txhash
 * @return array json array assoc | false 
 */
function nsub_get_payment_result($txhash){
    $hash = strip_tags(trim($txhash));                         
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://archival-rpc.testnet.near.org',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => sprintf('{
        "jsonrpc": "2.0",
        "id": "dontcare",
        "method": "tx",
        "params": ["%s", "dontcare"]
        }', $hash),
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
        ),
    ));

    $response = json_decode(curl_exec($curl), true);
    curl_close($curl);
    if( isset($response["error"]) ) return false;
    $arr = $response["result"]["receipts_outcome"];
    $rs = false;
    foreach ($arr as $a) {
        $r = $a["outcome"]["status"]["SuccessValue"];
        if (!empty($r) && !is_null(base64_decode($r))) {
            $rs = base64_decode($r) ;
            break;
        }
    }
    return $rs ? json_decode(json_decode($rs),true) : false;

}//payment result from NEAR 

//handler payment 
// function nsub_handler_payment($txhash)
// {

//     if (empty($txhash)) {
//         wp_send_json([
//             "status" => "error",
//             "message" => "hash must not empty"
//         ], 400);
//     }

//     $current_user = wp_get_current_user();
//     $hash = strip_tags(trim($txhash));
//     $rs = nsub_get_payment_result($hash);

//     if ($current_user->user_login == $_POST["user_wallet"] && $rs["sender"] == $_POST["user_wallet"]) {
//         //save paid post tx to user meta 
//         update_user_meta($current_user->ID, "nsub_post_" . $_POST['pid'], [
//             "txhash" => $txhash,
//             "expired" => "", // false  / timestamp 
//             "txresult" => "", // txhash result from NEAR  
//         ]);

//         //return unlocked content 

//     }

//     echo $response;
// } //handle payment  

/**
 * Get unlocked post content from WP db
 * @param init pid
 * @return string unlocked content 
 */
function  nsub_get_unlocked_content($pid){
    $post_content = get_post_field("post_content", $pid);
    $re = '/\[nsub_lock.*?\](.+?)\[\/nsub_lock\]/s';
    preg_match($re, $post_content, $matches, PREG_OFFSET_CAPTURE, 0);
    $c = $matches[1][0];
    if( sizeof($matches) > 0 ) return apply_filters("the_content", $c);
    return apply_filters("the_content", $post_content); 
}

/**
 * Check if a wallet have an NFT, get result onchain 
 * @return boolean 
 */
function nsub_is_owner_have_nft($nft_id, $owner_id){
    //nft_id: Nft_contract|nft_id -exmp: paras-token-v2.testnet|758:1
    $curl = curl_init();

    $nftAccountId = @explode("|" , $nft_id)[0];
    $item_id = @explode("|" , $nft_id)[1];
    $args = base64_encode('{"account_id": "'.$owner_id.'", "fromIndex": 0 ,"limit": 200}');
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://archival-rpc.testnet.near.org',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => sprintf('{
  "jsonrpc": "2.0",
  "id": "dontcare",
  "method": "query",
  "params": {
    "request_type": "call_function",
    "finality": "final",
    "account_id": "%s",
    "method_name": "nft_tokens_for_owner",
    "args_base64": "%s"
  }
}' , $nftAccountId , $args ),
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
        ),
    ));
    $response = json_decode(curl_exec($curl), true);
    curl_close($curl);

    
    if(isset($response["error"]) ){
        return false;
    }

    $arrtxt = implode(array_map("chr", $response['result']['result']));
    $arrRs = json_decode($arrtxt,true);
    // var_dump("arr RS" , $arrRs); die;
    foreach ($arrRs as $v) {
        // var_dump( "token id: " .$v['token_id'] . " - item id: " . $item_id . "\n" );
        if( $v['token_id'] == $item_id || $v['token_id'] == "#".$item_id || 
            strpos( $v['token_id'] , $item_id ) === 0 //only valid if start with $item_id first, exmp: NFT col: 775 - item: 775:1
        ){
            
            return true;
        }
    }
    return false;
}//nsub_is_owner_have_nft

function nsub_get_nft_info($nft_id){
    //nft_id: Nft_contract|nft_id -exmp: paras-token-v2.testnet|758
    $curl = curl_init();

    $nftAccountId = @explode("|" , $nft_id)[0];
    $item_id = @explode("|" , $nft_id)[1];
    $args = base64_encode('{"token_id":"'.$item_id.'"}');
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://archival-rpc.testnet.near.org',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => sprintf('{
  "jsonrpc": "2.0",
  "id": "dontcare",
  "method": "query",
  "params": {
    "request_type": "call_function",
    "finality": "final",
    "account_id": "%s",
    "method_name": "nft_token",
    "args_base64": "%s"
  }
}' , $nftAccountId , $args ),
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
        ),
    ));
    $response = json_decode(curl_exec($curl), true);
    curl_close($curl);

    if(isset($response["error"]) ){
        return false;
    }

    $arrtxt = implode(array_map("chr", $response['result']['result']));
    $arrRs = json_decode($arrtxt,true);

    return $arrRs;
}//nsub_get_nft_info

//get post content lock config 
function nsub_get_post_config($pid){

    $finalConfig = [
        "type" => "pay", //pay | nft 
        "amount" => "0.1", 
        "nft_id" => "",//Id of NFT 
        "expired_at" => 0,
        "lock" => true, //if this post is locked 
        "inherit_cat" => false, //inherit from category, cat_id or false 
    ];

    //check if post have special settings
    $pconfig = get_post_meta($pid , "nsub_post_config" , true ) ;

    if($pconfig && $pconfig["nsub-lock-type"] == "custom"){

        if( $pconfig["nsub-custom"] == "nft" ){
            //check if user wallet hold NFT 
            $finalConfig["type"] = "nft";
            $finalConfig["nft_id"] =  $pconfig["nsub-nft-collection-id"];
        }

        if ($pconfig["nsub-custom"] == "pay"){
            //check tx if result is paid + save to user meta 
            $finalConfig["type"] = "pay"; 
            $finalConfig["amount"] = $pconfig["nsub-amount-in-near"];
        }
        
        $finalConfig["expired_at"] = $pconfig["nsub-expired-after"];

        // die("save to user meta + unlock hien thi content cho user");
        return $finalConfig;
    }

    if( $pconfig && $pconfig["nsub-lock-type"] == "off" ){
        $finalConfig["lock"] = false;
        // die ("Tra ve luon content vi post lock type == off ");
        return $finalConfig;
    }

    //ko co post config rieng || post config inherit from category => load setting from the first category 
    $cats = get_the_terms( $pid , "category");
    $firstCat = $cats[0];
    $finalConfig["inherit_cat"] = $firstCat->term_id;
    $locktype =  get_term_meta( $firstCat->term_id, "nsub-lock-type",true );

    // no lock
    if( !$locktype || $locktype == "off" ){
        $finalConfig["lock"] = false;
        return $finalConfig;
    }
    //lock 
    switch($locktype){
        case "nft":
            //query nft info from user's NEAR wallet  
            $finalConfig["type"] = "nft";
            $finalConfig["nft_id"] =  get_term_meta(  $firstCat->term_id, "nsub-nft-id",true);
            // die ("category: user need to hold nft");
        break;

        case "pay-near":
            //query near paid by txhash from WP user meta
            $finalConfig["type"] = "pay";
            $finalConfig["amount"] =  (get_term_meta(  $firstCat->term_id, "nsub-amount-near",true));
            $finalConfig["expired_at"] =  intval(get_term_meta(  $firstCat->term_id, "nsub-expired-day",true));
            // die ("category: user need to pay near");
        break;
    }
    $finalConfig["expired_at"] = get_term_meta(  $firstCat->term_id, "nsub-expired-day",true);
    return $finalConfig;
    
}//get post config 

//return owner wallet address 
function nsub_get_receiver(){
    return trim(get_option("nsub_page_main")["owner_wallet"]);
}

function nsub_yocto2near($yoctorNear){
    return floatval($yoctorNear * 10**-24);
}

function nsub_near2yoctor($near){
    return floatval($near * 10**24);
}