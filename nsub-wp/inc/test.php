<?php 

add_action("1template_redirect", function () {
    // $cats = get_the_terms(16 , "category");
    // if($cats){
    //     foreach($cats as $c){
    //         // var_dump($c->term_id);
    //         $locktype =  get_term_meta( $c->term_id, "nsub-lock-type",true );
    //         if( !$locktype || $locktype == "off" ){
    //             // no lock 
    //             return;
    //         }
    //         //lock 
    //         var_dump("nsub lock type:  " . $locktype) ;
    //         switch($locktype){
    //             case "nft":
    //                 //query nft info from user's NEAR wallet  
    //             break;

    //             case "pay-near":
    //                 //query near paid by txhash from WP user meta
    //             break;
    //         }
    //         die;
    //     }
    // }
//     $pid = '16';
//    var_dump(nsub_get_post_config($pid) ) ;

nsub_is_owner_have_nft("testnet-cert.certynetwork.testnet::198" , "ahihi1323.testnet");
});//template redirect for TEST
