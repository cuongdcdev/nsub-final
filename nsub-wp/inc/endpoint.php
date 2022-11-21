<?php

//login or create new users with NEAR 
add_action("wp_ajax_nopriv_login_with_near", "login_with_near");
function login_with_near(){
    if (!is_user_logged_in() && wp_verify_nonce($_REQUEST['nonce'], "near-login") == 1) {
        $uname = strip_tags(trim($_POST["wallet"]));
        $loggedUser = false;

        if (username_exists($uname)) {
            $loggedUser = get_user_by("login", $uname);
        } else {
            //create new user + login 
            $newuser = [
                'user_login' => $uname,
                'user_pass' => md5(rand()),
                'role' => get_option('default_role')
            ];
            $newuserId = wp_insert_user($newuser);
            $loggedUser = get_user_by("ID", $newuserId);
        }

        if ($loggedUser) {
            //login user 
            wp_clear_auth_cookie();
            wp_set_current_user($loggedUser->ID);
            wp_set_auth_cookie($loggedUser->ID);

            //return json 
            header("content-type:application-json");
            echo json_encode([
                "status" => "success",
                "message" => __( sprintf("Success logged in with wallet %s", $uname), NSUB_DOMAIN),
                "nonce" => wp_create_nonce("near-logout")
            ]);
            die;
        }

        header("content-type:application-json");
        echo json_encode([
            "status" => "error",
            "message" => __( sprintf("Something wrong during login %s, plz try again", $uname), NSUB_DOMAIN),
        ]);
        die;
    }
}

//logout with NEAR 
add_action("wp_ajax_logout_with_near", function () {
    if (is_user_logged_in() && wp_verify_nonce($_REQUEST['nonce'], "near-logout")) {
        wp_clear_auth_cookie();
        wp_set_current_user(0);
        wp_logout();

        header("content-type:application-json");
        echo json_encode([
            "status" => "success",
            "message" => __("User logged out!", NSUB_DOMAIN)
        ]);
        die;
    }
});

//handler content after paid
add_action("wp_ajax_nsub_after_paid", function () {
    if (wp_verify_nonce($_REQUEST['nonce'], "nsub-content")) {
        $txhash = trim(strip_tags($_POST["txhash"]));
        $pid = intval(trim($_POST['pid']));
        $user = wp_get_current_user();

        // //current user is admin, unlock content  
        // if(current_user_can("administrator")){
        //     wp_send_json([
        //         "status" => "success",
        //         "type" => "pay",
        //         "message" => nsub_get_unlocked_content($pid)
        //     ], 200);
        // }

        //save to db 
        if (isset($txhash)) {
            $stt = nsub_get_payment_result($txhash);
            $postConfig = nsub_get_post_config($pid);
            $nsubPayLog = !empty($postConfig["inherit_cat"]) ?  nsub_db_get_row(null, intval($user->ID),$postConfig["inherit_cat"]) :  nsub_db_get_row($pid, intval($user->ID));
            $expiredDate = intval($postConfig["expired_at"]) > 0 ? date('Y-m-d H:i:s', strtotime("+" . intval($postConfig["expired_at"]) . " day")) : "";
            //check pay amount in NEAR  
            if (
                empty($stt) || ($stt["sender"] != $user->user_login) || ($stt["receiver"] != nsub_get_receiver()) ||
                abs( floatval($postConfig["amount"])- nsub_yocto2near( $stt["received_amount"]) / ( (100-floatval($stt['tax']))/100)) > 0.001
            ) {
                wp_send_json([
                    "status" => "error",
                    "type" => "pay",
                    "message" => __("Something wrong!!", NSUB_DOMAIN)
                ], 400);
            }

            if (empty($nsubPayLog)) {
                //save to db meta table 
                if( !empty($postConfig["inherit_cat"]) ){
                    nsub_db_insert(null, $user->ID, $txhash, $expiredDate, "pay", $stt["received_amount"], $stt, $postConfig["inherit_cat"]);
                }else{
                    nsub_db_insert($pid, $user->ID, $txhash, $expiredDate, "pay", $stt["received_amount"], $stt);
                }

                //return unlocked content 
                wp_send_json([
                    "status" => "success",
                    "type" => "pay",
                    "message" => nsub_get_unlocked_content($pid)
                ], 200);
            } else {
                //check if content expired & user just did renew 
                if ($nsubPayLog["txhash"] != $txhash) {
                    global $wpdb;
                    $table_name = $wpdb->prefix . "nsub_data";
                    $wpdb->update($table_name, [
                        "pay" => $stt["received_amount"],
                        "expired_at" => $expiredDate,
                        "txhash" => $txhash,
                        "type" => "pay",
                        "data" => json_encode($stt),

                    ], [
                        "txhash" => $nsubPayLog["txhash"],
                        "user_id" => $user->ID,
                        "post_id" => $pid
                    ]);
                    $nsubPayLog["expired_at"] = $expiredDate;
                }

                //check if content expired 
                $istokenstillvalid = intval($postConfig["expired_at"]) > 0 && strtotime($nsubPayLog["expired_at"]) > time();

                if ((intval($postConfig["expired_at"]) == 0) || $istokenstillvalid) {
                    wp_send_json([
                        "status" => "success",
                        "type" => "pay",
                        "message" => nsub_get_unlocked_content($pid)
                    ], 200);
                } else {
                    wp_send_json([
                        "status" => "error",
                        "type" => "pay",
                        "message" => __("Expired, please renew" , NSUB_DOMAIN)
                    ], 400);
                }
            }
        }
    } else {
        wp_send_json([
            "status" => "error",
            "type" => "pay",
            "message" => __("Something wrong", NSUB_DOMAIN)
        ], 400);
    }
}); //handler content after paid   

//get unlocked content for post
add_action("wp_ajax_nsub_get_content", function () {
    $user = wp_get_current_user();
    $pid = intval(trim($_POST['pid']));

    //current user is admin, unlock content  
    if (current_user_can("administrator")) {
        wp_send_json([
            "status" => "success",
            "message" => nsub_get_unlocked_content($pid)
        ], 200);
    }

    if (wp_verify_nonce($_REQUEST['nonce'], "nsub-content")) {
        // $stt =  json_decode(nsub_get_payment_result($txhash) , true );
        $postConfig = nsub_get_post_config($pid);
        $expiredDate = intval($postConfig["expired_at"]) > 0 ? date('Y-m-d H:i:s', strtotime("+" . intval($postConfig["expired_at"]) . " day")) : "";
        
        if( !empty( $postConfig["inherit_cat"] ) ){
            //query from first category of post instead;
            $cats = get_the_terms( $pid , "category");
            $firstCat = $cats[0];
            $nsubPayLog = nsub_db_get_row("", $user->ID , $firstCat->term_id);
        }else{
            $nsubPayLog = nsub_db_get_row($pid, $user->ID);
        }
        //no lock
        if (!$postConfig["lock"]) {
            wp_send_json([
                "status" => "success",
                "type" => $postConfig["type"],
                "message" => nsub_get_unlocked_content($pid)
            ], 200);
        }
        
        //post type require hold NFT 
        if ($postConfig["type"] == "nft" && nsub_is_owner_have_nft($postConfig["nft_id"], $user->user_login)) {
            if (empty($nsubPayLog)) {
                nsub_db_insert($pid, $user->ID, "", $expiredDate, "nft", $postConfig["nft_id"], "");
                $nsubPayLog = [
                    "post_id" => $pid,
                    "user_id" => $user->ID,
                    "type" => "nft",
                    "pay" => $postConfig["nft_id"],
                    "expired_at" => $expiredDate
                ];
            }

            //Post config changed to another NFT, update db again 
            if (!empty($nsubPayLog["pay"]) && ($nsubPayLog["pay"] != $postConfig["nft_id"])) {
                global $wpdb;
                $table_name = $wpdb->prefix . "nsub_data";
                $wpdb->update($table_name, [
                    "pay" => $postConfig["nft_id"],
                    "expired_at" => $expiredDate,
                    "type" => "nft"
                ], [
                    "user_id" => $user->ID,
                    "post_id" => $pid
                ]);

                $nsubPayLog["expired_at"] = $expiredDate;
            }

            //check if content expired 
            $isnftdatestillvalid = (intval($postConfig["expired_at"]) > 0) && (strtotime($nsubPayLog["expired_at"]) > time());
            if ((intval($postConfig["expired_at"]) == 0) || $isnftdatestillvalid) {
                wp_send_json([
                    "status" => "success",
                    "type" => "nft",
                    "message" => nsub_get_unlocked_content($pid)
                ], 200);
            } else {
                wp_send_json([
                    "status" => "error",
                    "type" => $postConfig["type"],
                    "message" => __(sprintf("NFT %s expired at %s" , $postConfig["nft_id"], $nsubPayLog["expired_at"]),NSUB_DOMAIN)
                ], 400);
            }
        } //nft 

        if ($postConfig["type"] == "pay") {
            //check if content expired 
            $istokenstillvalid = @intval($postConfig["expired_at"]) > 0 && @strtotime($nsubPayLog["expired_at"]) > time();
            if ( !empty($nsubPayLog) && (intval($postConfig["expired_at"]) == 0) || $istokenstillvalid) {
                wp_send_json([
                    "status" => "success",
                    "type" => $postConfig["type"],
                    "message" => nsub_get_unlocked_content($pid)
                ], 200);
            } else {
                wp_send_json([
                    "status" => "error",
                    "type" => $postConfig["type"],
                    "message" => __("Expired or not pay yet", NSUB_DOMAIN)
                ], 400);
            }
        } //pay 

        $addmsg = $postConfig["type"] == "nft" ? " | Require NFT " . $postConfig["nft_id"] : " ";
        wp_send_json([
            "status" => "error",
            "type" => $postConfig["type"],
            "message" => __("Failed to get content", NSUB_DOMAIN) . $addmsg
        ], 400);
    } else {
        wp_send_json([
            "status" => "error",
            "message" => __("Nonce failed", NSUB_DOMAIN)
        ], 400);
    }
}); //get unlocked content   


add_action("wp_ajax_nsub_get_nft_info" , function(){
    wp_send_json([
        "status" => "success", 
        "message" => nsub_get_nft_info($_POST["nft_id"])
    ]);
});
