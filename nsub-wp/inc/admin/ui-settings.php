<?php

$pages = array(
    'nsub_page_main'    => array(
        'page_title'    => __('NSub', NSUB_DOMAIN),
        'menu_slug' => 'nsub_config',
        'icon_url' => 'dashicons-unlock',
        'sections'        => array(
            'section-one'    => array(
                'title'            => __('NSub Config', NSUB_DOMAIN),
                'fields'        => array(
                    'owner_wallet'        => array(
                        'title'            => __('Owner Wallet', NSUB_DOMAIN),
                        'text'            => __('The NEAR wallet address which receive funds from users, testnet only for now '),
                        'value'       => '',
                        'attributes' => [
                            'required' => 'required'
                        ]
                    ),
                    'unlock_title' => [
                        'title' => __('Unlock button title', NSUB_DOMAIN),
                        'value' => 'Pay to unlock',
                        'text' => __('Unlock to view button title, HTML allowed'),
                        'attributes' => [
                            'required' => 'required'
                        ]
                    ],
                    'unlock_title' => [
                        'title' => __('Unlock button title', NSUB_DOMAIN),
                        'value' => 'Pay to unlock',
                        'text' => __('Unlock to view button title, HTML allowed'),
                        'attributes' => [
                            'required' => 'required'
                        ]
                    ]

                ),
            ),
        ),

        'subpages' => [
            'nsub_page_donate' => [
                'page_title' => __("Config Donate", NSUB_DOMAIN),
                'menu_slug' => 'nsub_donate',
                'sections' => [
                    's1' => [
                        'title' => ' ', 
                        'custom' => true ,
                        'callback' => nsub_donate_html_page($args = '')
                    ]
                ]
            ],
            'nsub_page_advanced' => [
                'menu_slug' => 'nsub_advanced',
                'page_title' => __('Advanced', NSUB_DOMAIN),
                'sections' => [
                    'section-1' => [
                        'title' => 'Login/logout button',
                        'fields' => [
                            'login_title' => [
                                'title' => __('Login button title', NSUB_DOMAIN),
                                'value' => 'Login with NEAR',
                                'text' => __('HTML allowed', NSUB_DOMAIN),
                                'attributes' => [
                                    'required' => 'required'
                                ]
                            ],
                            'logout_title' => [
                                'title' => __('Logout button title', NSUB_DOMAIN),
                                'value' => 'Logout',
                                'text' => __('HTML allowed', NSUB_DOMAIN),
                                'attributes' => [
                                    'required' => 'required'
                                ]
                            ]
                        ]
                    ],
                    'section-2' => [
                        'title' => ' ',
                        'custom' => true ,
                        'callback' => nsub_login_html_page($args = '')
                    ]
                ]
            ]

        ],

    ),
);
$option_page = new RationalOptionPages($pages);

function nsub_donate_html_page($args){
        ob_start();
?>
    <table class="form-table" role="presentation">
        <tbody>
            <tr>
                <th scope="row"><label for="donate_button_shortcode">Donate button shortcode</label></th>
                <td>
                    <p style="font-family:'Courier New', Courier, monospace; font-weight:bold">[nsub_donate label="Donate NEAR" title="donate" amount="0.1" receiver="" ]</p>
                    <p class="help"><b>label</b>: Label </p>
                    <p class="help"><b>title</b>: Button title </p>
                    <p class="help"><b>amount</b>: Amount in NEAR. Set empty to show input field instead of just donate button with fixed value </p>
                    <p class="help"><b>receiver</b>: Custom NEAR wallet receiver, if empty, donation will be transfer to the owner wallet </p>
                    <p class="help"><b>thankyou</b>: URL of thank you page for redirect to after donation, if empty, will be redirected to the current page </p>
                    <p class="help"><b>*</b>: All params can be set empty, so  you can just use <b>[nsub_donate]</b> </p>
                </td>
            </tr>
        </tbody>
    </table>
<?php
   $s= ob_get_clean();
   return $s;
}

function nsub_login_html_page($args){
    ob_start();
?>
<table class="form-table" role="presentation">
    <tbody>
        <tr>
            <th scope="row"><label for="login_button_shortcode">Login shortcode</label></th>
            <td>
                <p style="font-family:'Courier New', Courier, monospace; font-weight:bold">[nsub_login show_logout="true"]</p>
                <p class="help"><b>show_logout</b>: Show logout button  </p>
                <p class="help"> All params can be set empty, so  you can just use <b>[nsub_login]</b> </p>
            </td>
        </tr>
    </tbody>
</table>
<?php
$s= ob_get_clean();
return $s;
}