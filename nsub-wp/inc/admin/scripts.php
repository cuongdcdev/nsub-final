<?php
add_action("admin_footer", function () {
?>
    <style>
        .nft-img-wrap {
            max-width: 80%;
            text-align: center;
            border: 1px solid gray;
            margin: 5px;
            border-radius: 5px;
        }

        .nft-img-wrap img {
            max-width: 80%;
            padding: 10px;
            margin: 0 auto;
            max-height: 100px;
        }
    </style>

    <script>
        (function($) {
            console.log("admin script");
            function nsub_show_nft(nftid, idNftField){
                if(!nftid) return;
                
                $.ajax({
                        method: "POST",
                        url: "<?= admin_url("admin-ajax.php") ?>",
                        data: {
                            action: "nsub_get_nft_info",
                            nft_id: nftid.trim().includes("paras") ?  nftid.trim() + ":1" : nftid.trim() //if is paras NFT contract, auto append 1 to show NFT image
                        },
                        success: function(data) {
                            $(".nft-img-wrap").remove();
                            if (!data.message) {
                                $(idNftField).parent().append("<div class='nft-img-wrap'>NFT not found</div>");
                                return;
                            }
                            console.log(data.message);
                            var r = (data.message.metadata);
                            var medialink =  r.media.includes("https://") ? r.media : "https://ipfs.fleek.co/ipfs/" + r.media;
                            $(idNftField).parent().append(
                                "<div class='nft-img-wrap'><img src=" + medialink + "/><p style='text-align:center'>" + r.title + "</p></div>"
                            );
                        },
                        error: function(err) {
                            console.log(err);
                        }
                    })
            }
            <?php
            $screen = get_current_screen();
            if ($screen->parent_base == 'edit' && isset($_GET["post"]) ) :
            ?>
                $("#nsub-nft-collection-id").on("change", function(e) {
                    console.log("changed", $(this).val());
                    var nftid = $(this).val();
                   nsub_show_nft(nftid, "#nsub-nft-collection-id");
                });
                if(  $("#nsub-nft-collection-id").val().length > 0 ){
                    nsub_show_nft($("#nsub-nft-collection-id").val(), "#nsub-nft-collection-id");
                };
            <?php endif; ?>

            <?php if (isset($_GET["taxonomy"]) && $_GET["taxonomy"] == "category" && isset($_GET["tag_ID"])) : ?>
                $("#nsub-nft-id").on("change", function(e) {
                    console.log("changed", $(this).val());
                    var nftid = $(this).val();
                   nsub_show_nft(nftid, "#nsub-nft-id");
                });
                if(  $("#nsub-nft-id").val().length > 0 ){
                    nsub_show_nft($("#nsub-nft-id").val(), "#nsub-nft-id");
                };
            <?php endif ?>
        })(jQuery)
    </script>)
<?php
});
