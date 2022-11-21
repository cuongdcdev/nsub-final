console.log("nsub_ajax ready,loading~");
// console.log(nSubContract);

window.initNSub = async function () {
    window.$ = jQuery;
    window.walletConn = nSubContract.wallet;
    const urlParams = new URLSearchParams(window.location.search);
    const txhash = urlParams.get("transactionHashes");

    console.log("[initNSub] wallet conn", walletConn);

    //HANDLE LOGIN LOGOUT 
    if (!nsubObject.isSignedIn && walletConn.walletSelector.isSignedIn()) {
        console.log("Loginingggg with NEAR~~~~");
        $.ajax({
            method: "POST",
            url: nsubObject.ajaxUrl,
            data: {
                action: "login_with_near",
                nonce: nsubObject.nonce,
                wallet: walletConn.accountId
            },
            success: function (data) {
                var rurl = window.location.href;
                if( window.location.href.indexOf("wp-login") > 0 || window.location.href.indexOf("wp-admin") > 0 ){
                    rurl = window.location.origin;
                }
                window.location.replace(rurl);
                console.log("success!", data);
            },
            error: function (err) {
                console.log("errr");
            }
        });
    }

    $(".nsub-logout-btn").on("click", function (e) {
        e.preventDefault();
        logoutWithNear();
    });

    function logoutWithNear() {
        var walletConn = nSubContract.wallet;
        if (!walletConn.walletSelector.isSignedIn()) return;
        $.ajax({
            method: "POST",
            url: nsubObject.ajaxUrl,
            data: {
                action: "logout_with_near",
                nonce: nsubObject.nonce,
            },
            success: function (data) {
                if (data.status == "success") {
                    walletConn.signOut();
                    window.location.replace(window.location.origin + window.location.pathname);
                } else {
                    console.log("Errr logout", data);
                }
            }
        });

    }//logout with NEAR 

    $(".nsub-login-btn").on("click", (e) => {
        e.preventDefault();
        walletConn.signIn();
     });

    if (window.isSignedIn) {
        signedInFlow();
    } else {
        signedOutFlow();
    }

    $("#wp-admin-bar-logout").on("click", function(e){
        e.preventDefault();
        // var logouturl = $(this).find("a").attr("href");
        logoutWithNear();
    });

    // Display the signed-out-flow container
    function signedOutFlow() {
        $('#signed-in-flow').hide();
        $('#signed-out-flow').show();
    }

    // Displaying the signed in flow container and fill in account-specific data
    function signedInFlow() {
        $('#signed-out-flow').hide();
        $('#signed-in-flow').show();
    }
    //END HANDLE LOGIN LOGOUT 


    // HANDLE CONTENT 

    if (txhash !== null && urlParams.get("type") == "pay" && nsubObject.pid ) {
        // Get result from the txhash
        console.log("get locked content after paid");
        let txrs = await nSubContract.wallet.getTransactionResult(txhash);
        try {
            let jsonrs = JSON.parse(txrs);
            if (jsonrs.sender == walletConn.accountId) {
                $.ajax({
                    url: nsubObject.ajaxUrl,
                    method: "POST",
                    data: {
                        action: "nsub_after_paid",
                        txhash: txhash,
                        nonce: nsubObject.nonce_content ? nsubObject.nonce_content : false,
                        pid: nsubObject.pid,
                        wallet: walletConn.accountId
                    },
                    success: function (data) {
                        console.log("success payment: ", data);
                        $("#nsub_content_wrap").html("");
                        $("#nsub_content_wrap").append(data.message);
                    },
                    error: function (err) {
                        console.log("error during payment", err.responseJSON);
                        if( nsubObject.postConfig.type  == "pay"){
                            $("#nsub-pay-wrap").show();
                        }
                        $("#nsub_content_wrap").append("<p style='color:red'>" + err.responseJSON.message + "</p>");

                    }
                });
            }
        } catch (e) {
            console.log("Get TX result err ", e);
        }
    }else if ( nsubObject.isSignedIn && $("#nsub_content_wrap").length > 0 && nsubObject.pid ) {
        console.log("get locked content");
        $.ajax({
            url: nsubObject.ajaxUrl,
            method: "POST",
            data: {
                action: "nsub_get_content",
                txhash: txhash,
                nonce: nsubObject.nonce_content ? nsubObject.nonce_content : false,
                pid: nsubObject.pid,
                wallet: walletConn.accountId
            },
            success: function (data) {
                console.log("success get unlocked: ", data);
                $("#nsub_content_wrap").html("");
                $("#nsub_content_wrap").append( data.message);
            },
            error: function (err) {
                console.log("error during get unlocked content ", err.responseJSON);
                if( nsubObject.postConfig.type  == "pay"){
                    $("#nsub-pay-wrap").show();
                }
                $("#nsub_content_wrap").append("<p style='color:red'>" + err.responseJSON.message + "</p>");
                
            }
        });
    }

    //script on single post only
    if( nsubObject.pid ){
        //pay to unlock
        if( nsubObject.postConfig.type == "pay"){
            var urlcb = (new URL(window.location.href));
            urlcb.searchParams.append("type" , "pay" );
            $("#paytounlock").on("submit" , async (e) =>{
                e.preventDefault();
                if( !window.isSignedIn ){
                    walletConn.signIn();
                    return;
                }
                await nSubContract.pay(nsubObject.postConfig.amount, nsubObject.postConfig.owner_address, urlcb.href );
            } );
        }

        if( !nsubObject.isSignedIn && nsubObject.postConfig.type == "pay"){
            $("#nsub-pay-wrap").show();
        }

        
    
    }//run on single post

    //donate feature 
    async function nsub_donate(amount, receiver, redirecturl){
        if( !window.isSignedIn ){
            walletConn.signIn();
            return;
        }
        await nSubContract.donate(amount, receiver, redirecturl);
    }

    $(".nsub-donate-form .nsub-donate-btn").on("click" ,async function(){
        var amount = parseFloat($(this).parent().find("input[name=donate-amount]").val()) > 0 ? ($(this).parent().find("input[name=donate-amount]").val()) : ($(this).attr("data-amount").trim()) ;
        var receiver = $(this).attr("data-receiver").trim();
        var redirecturl = $(this).attr("data-thankyou") ?  $(this).attr("data-thankyou").trim() : "" ; 
        if( amount ){
            nsub_donate(amount, receiver, redirecturl); 
        }
        console.log("amount: " + amount );
    });

//END HANDLE CONTENT


}// init Nsub
