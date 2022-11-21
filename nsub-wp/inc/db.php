<?php 

function nsub_init_db(){
    global $wpdb;
    $table_name = $wpdb->prefix . "nsub_data"; 
    $charset_collate = $wpdb->get_charset_collate();
 
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
        `post_id` bigint(20) unsigned DEFAULT NULL,
        `cat_id` bigint(20) unsigned DEFAULT NULL,
        `user_id` bigint(20) unsigned DEFAULT NULL,
        `txhash` varchar(125)  DEFAULT NULL,
        `type` varchar(25)  DEFAULT NULL,
        `data` text ,
        `pay` text ,
        `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
        `expired_at` timestamp NULL DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) $charset_collate;";
 
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

function nsub_db_insert($pid = null, $uid, $txhash,  $expired_at = null, $type="pay", $pay ="", $data = "", $catid=null   ){
    global $wpdb;
    $table_name = $wpdb->prefix . "nsub_data";
    $params = [
        "post_id" => $pid, 
        "user_id" => $uid,
        "txhash" => $txhash,
        "type" => $type,
        "pay" => $pay,
        "data" => json_encode($data),
        "expired_at" => $expired_at,
        "cat_id" => $catid
    ];
    $wpdb->insert($table_name, $params);
    // error_log("NEW QUERY: " . $wpdb->last_query);

}

/**
 * get row by postId or categoryId and User Id 
 * @return assoc array | null 
 */
function nsub_db_get_row($pid, $uid, $catid=""){
    global $wpdb;
    $tbname = $wpdb->prefix. "nsub_data";
    if( !empty($catid) ){
        return $wpdb->get_row("SELECT * FROM $tbname WHERE user_id={$uid} AND cat_id={$catid} LIMIT 1", ARRAY_A);
    }else{
        return $wpdb->get_row("SELECT * FROM $tbname WHERE user_id={$uid} AND post_id={$pid} LIMIT 1", ARRAY_A);
    }
}

function nsub_db_get_row_by_txhash($txhash){
    global $wpdb;
    $tbname = $wpdb->prefix. "nsub_data";
    return $wpdb->get_row("SELECT * FROM $tbname WHERE txhash='{$txhash}' LIMIT 1 ", ARRAY_A);
}

// function nsub_db_get_row_by_pid_uid_cid($pid, $uid,$cid){
//     global $wpdb;
//     $tbname = $wpdb->prefix. "nsub_data";
//     if(!empty($cid)){
//         return $wpdb->get_row("SELECT * FROM $tbname WHERE cat_id={$pid} AND user_id={$uid} LIMIT 1 ", ARRAY_A);
//     }else{
//         return $wpdb->get_row("SELECT * FROM $tbname WHERE post_id={$pid} AND user_id={$uid} LIMIT 1 ", ARRAY_A);
//     }
// }


/**
 * Delete row by postId and UserId
 */
function nsub_db_del_row($pid,$uid){
    global $wpdb;
    $tbname = $wpdb->prefix . "nsub_data";
    return $wpdb->delete( $tbname, [
        "post_id" => $pid,
        "user_id" => $uid
    ]);
}