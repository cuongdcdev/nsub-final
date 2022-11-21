<?php
add_action('wp_dashboard_setup', function () {
    if( !current_user_can('administrator')) return;
    wp_add_dashboard_widget('custom_help_widget', 'NSub Dashboard', function () {
?>
        <script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script>
        
        <div id="hcg-tabs-1" class="tabs-container">

            <div id="nsub-tabs-nav">
                <a href="#" data-target="tab_1" class="tabs-menu tabs-menu-active">Orders</a>
                <a href="#" data-target="tab_2" class="tabs-menu">Top users</a>
                <a href="#" data-target="tab_3" class="tabs-menu">Top posts</a>
                <a href="#" data-target="tab_4" class="tabs-menu">Top categories</a>
            </div>

            <div class="tabs-content">
                <div id="tab_1" class="tabs-panel" style="display:block">
                    <div class="flex-content"> Numbers of orders per day (last 30 days) </div>
                    <canvas id="chart-dailytxs" width="400" height="400"></canvas>
                    <div id="nsub-sale-stat"></div>

                </div>

                <div id="tab_2" class="tabs-panel">
                    <div class="flex-content">Top 10 users by purchases (last 30 days)</div>
                    <canvas id="chart-topusers" width="400" height="400"></canvas>
                </div>

                <div id="tab_3" class="tabs-panel">
                    <div class="flex-content">Top 10 posts by sold (last 30 days)</div>
                    <canvas id="chart-topposts" width="400" height="400"></canvas>
                </div>

                <div id="tab_4" class="tabs-panel">
                    <div class="flex-content">Top 10 categories by sold (last 30 days)</div>
                    <canvas id="chart-topcats" width="400" height="400"></canvas>
                </div>

            </div>

        </div>

        <script>
            (function($) {
                //tabs 
                $("#nsub-tabs-nav a").click(function(event) {
                    event.preventDefault();
                    $("#nsub-tabs-nav a").removeClass("tabs-menu-active");
                    $(this).addClass("tabs-menu-active");
                    $(".tabs-panel").removeClass("animated-tabs bounceIn").hide();
                    var tab_id = $(this).data("target");
                    $("#" + tab_id).addClass("animated-tabs bounceIn").show();
                });

                // get txs in last 30 days 
                $.ajax({
                    method: "POST",
                    url: "<?php echo admin_url("admin-ajax.php") ?>",
                    data: {
                        action: "nsub_analytics",
                        nsub_txs: "yup"
                    },
                    success: function(data) {

                        new Chart("chart-dailytxs", {
                            type: "line",
                            data: {
                                labels: data.xValues,
                                datasets: [{
                                    fill: false,
                                    lineTension: 0,
                                    backgroundColor: "rgba(0,0,255,1.0)",
                                    borderColor: "rgba(0,0,255,0.1)",
                                    data: data.yValues,
                                    label: "orders",
                                }]
                            },
                            options: {
                                legend: {
                                    display: false
                                },
                                scales: {
                                    yAxes: [{
                                        ticks: {
                                            min: 0,
                                        }
                                    }],
                                }
                            }
                        });

                        $("#nsub-sale-stat").append( "<p><b>Revenue in NEAR: " + parseFloat(data.revNear).toFixed(2) +" NEAR </b></p>" 
                        + "<b><p>NFT used: " + data.revNft + "</b></p>" );

                    },
                    error: function(err) {
                        console.log(err);
                    }
                }); // get txs in last 30 days 

                // get top 10 users last 30 days by purchase times  
                $.ajax({
                    method: "POST",
                    url: "<?php echo admin_url("admin-ajax.php") ?>",
                    data: {
                        action: "nsub_analytics",
                        nsub_top_users: "yup"
                    },
                    success: function(data) {
                        new Chart("chart-topusers", {
                            type: "bar",
                            data: {
                                labels: data.users,
                                datasets: [{
                                    label: "orders",
                                    data: data.txs
                                }]
                            },
                            options: {
                                legend: {
                                    display: false
                                },
                                scales: {
                                    yAxes: [{
                                        ticks: {
                                            min: 0,
                                        }
                                    }],
                                }
                            }
                        });
                    },
                    error: function(err) {
                        console.log(err);
                    }
                }); // get top purchase users last 30 days

                // get top 10 posts last 30 days by purchase times  
                $.ajax({
                    method: "POST",
                    url: "<?php echo admin_url("admin-ajax.php") ?>",
                    data: {
                        action: "nsub_analytics",
                        nsub_top_posts: "yup"
                    },
                    success: function(data) {
                        new Chart("chart-topposts", {
                            type: "bar",
                            data: {
                                labels: data.posts,
                                datasets: [{
                                    label: "number of sold",
                                    data: data.txs
                                }]
                            },
                            options: {
                                legend: {
                                    display: false
                                },
                                scales: {
                                    yAxes: [{
                                        ticks: {
                                            min: 0,
                                        }
                                    }],
                                }
                            }
                        });
                    },
                    error: function(err) {
                        console.log(err);
                    }
                }); // get top purchase posts last 30 days

                // top 10 categories last 30 days 
                $.ajax({
                    method: "POST",
                    url: "<?php echo admin_url("admin-ajax.php") ?>",
                    data: {
                        action: "nsub_analytics",
                        nsub_top_cats: "yup"
                    },
                    success: function(data) {
                        new Chart("chart-topcats", {
                            type: "bar",
                            data: {
                                labels: data.cats,
                                datasets: [{
                                    label: "number of sold",
                                    data: data.txs
                                }]
                            },
                            options: {
                                legend: {
                                    display: false
                                },
                                scales: {
                                    yAxes: [{
                                        ticks: {
                                            min: 0,
                                        }
                                    }],
                                }
                            }
                        });
                    },
                    error: function(err) {
                        console.log(err);
                    }
                }); // get top purchase posts last 30 days
            })(jQuery);
        </script>
        <style>
            .tabs-container {
                width: 100%;
            }

            div#nsub-tabs-nav {
                position: relative;
                display: flex;
                justify-content: flex-start;
            }

            div#nsub-tabs-nav a:nth-child(even) {
                margin: 0 3px;
            }

            a.tabs-menu {
                display: inline-block;
                background-color: #007fe6;
                font-size: 12px;
                color: #fff;
                padding: 5px 10px;
                font-weight: bold;
                text-decoration: none;
                border: solid 1px #007fe6;
                border-bottom: 0;
                border-radius: 3px 3px 0 0;
            }

            a.tabs-menu.tabs-menu-active {
                background-color: #fff;
                border: solid 1px #1179ac;
                color: #6b6b6b;
                border-bottom: 0;
            }

            .tabs-content {
                border: solid 2px #1179ac;
                margin-top: -2px;
                background-color: #fff;
                overflow: hidden;
                line-height: 1.5;
            }

            .tabs-panel {
                display: none;
                min-height: 150px;
                overflow: auto;
                padding: 10px;
                font-size: 14px;
            }

            .animated-tabs {
                -webkit-animation-duration: 1s;
                animation-duration: 1s;
                -webkit-animation-fill-mode: both;
                animation-fill-mode: both;
            }
        </style>

<?php
    });
}); //dashboard setup

//ajax wp_ajax_nsub_analytics  
add_action("wp_ajax_nsub_analytics", function () {
    global $wpdb;
    $table_name = $wpdb->prefix . "nsub_data";
    if (!current_user_can("administrator")) return;

    if (isset($_POST['nsub_txs'])) {
        $xarr = [];
        $yarr = [];
        $revNear = 0; //revenue posts in NEAR  
        $revNft = 0;//revenue in NFT 

        $rs = $wpdb->get_results("SELECT COUNT(post_id) as tx,DATE(created_at) AS tdate
        FROM $table_name 
        WHERE created_at > DATE_ADD(NOW(), INTERVAL -1 MONTH) 
        GROUP BY (tdate)", ARRAY_A);

        $revqrNear = $wpdb->get_results( "SELECT pay
        FROM $table_name 
        WHERE created_at > DATE_ADD(NOW(), INTERVAL -1 MONTH) AND type ='pay'", ARRAY_A );

        $revqrNft = $wpdb->get_results( "SELECT pay
        FROM $table_name 
        WHERE created_at > DATE_ADD(NOW(), INTERVAL -1 MONTH) AND type ='nft'", ARRAY_A );

        foreach( $revqrNear as $r ){
            $revNear += nsub_yocto2near($r['pay']);
        }

        foreach($revqrNear as $r){
            $revNft++;
        }

        foreach ($rs as $r) {
            $xarr[] = $r["tdate"];
            $yarr[] = $r["tx"];
        }
        wp_send_json([
            'xValues' => $xarr,
            'yValues' => $yarr,
            'revNear' => $revNear,
            'revNft' => $revNft
        ], 200);
    }

    if (isset($_POST['nsub_top_users'])) {
        $rs = $wpdb->get_results("SELECT user_id, count(user_id) as times
        FROM $table_name
        WHERE created_at > DATE_ADD(NOW(), INTERVAL -1 MONTH)
        GROUP BY user_id 
        LIMIT 10", ARRAY_A);

        $users = [];
        $txs = [];
        foreach ($rs as $r) {
            $users[] = "User ID: " . $r["user_id"];
            $txs[] = $r["times"];
        };
        wp_send_json([
            'users' => $users,
            'txs' => $txs
        ]);
    }

    if (isset($_POST['nsub_top_posts'])) {
        $rs = $wpdb->get_results("SELECT post_id, count(post_id) as times
        FROM $table_name
        WHERE created_at > DATE_ADD(NOW(), INTERVAL -1 MONTH) AND post_id is not null
        GROUP BY post_id
        LIMIT 10", ARRAY_A);

        $posts = [];
        $txs = [];
        foreach ($rs as $r) {
            $posts[] = "post ID: " . $r["post_id"];
            $txs[] = $r["times"];
        };

        wp_send_json([
            'posts' => $posts,
            'txs' => $txs
        ]);
    }

    if (isset($_POST['nsub_top_cats'])) {
        $rs = $wpdb->get_results("SELECT cat_id, count(cat_id) as times
        FROM $table_name
        WHERE created_at > DATE_ADD(NOW(), INTERVAL -1 MONTH) AND cat_id is not null
        GROUP BY cat_id
        LIMIT 10", ARRAY_A);

        $cats = [];
        $txs = [];
        foreach ($rs as $r) {
            $cats[] = "cat ID: " . $r["cat_id"];
            $txs[] = $r["times"];
        };

        wp_send_json([
            'cats' => $cats,
            'txs' => $txs
        ]);
    }
}); //wp_ajax_nsub_analytics