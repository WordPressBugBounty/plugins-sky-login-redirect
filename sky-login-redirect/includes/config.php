<?php
/**
 * Config
 * PHP version 7
 *
 * @category Config
 * @package  SkyLoginRedirect
 * @author   Utopique <support@utopique.net>
 * @license  GPL https://utopique.net
 * @link     https://utopique.net
 */
if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
/**
 * Plugin configuration
 */
$args = [
    'namespace'     => __NAMESPACE__,
    'textdomain'    => 'sky-login-redirect',
    'version'       => '1.1.2',
    'page_title'    => 'Sky Login Redirect',
    'menu_title'    => 'Sky Login Redirect',
    'slug'          => 'sky-login-redirect',
    'callback'      => 'create_page',
    'icon'          => 'none',
    'position'      => 2,
    'emoji'         => '⚡',
    'freemius'      => 'Sky_Login_Redirect_fs',
    'fs_upgrade'    => \SkyLoginRedirect\Sky_Login_Redirect_fs()->get_upgrade_url(),
    'highest_plan'  => 'agency'
];

// define option_name separately
$args['option_name'] = str_replace('-', '_', $args['slug']) . '_settings';

$menu_icon = [
    'svg' => "data:image/svg+xml,%3Csvg xmlns='http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg' width='1em' height='1em' preserveAspectRatio='xMidYMid meet' viewBox='0 0 48 48'%3E%3Cpath fill='%23FFC107' d='M33 22h-9.4L30 5H19l-6 21h8.6L17 45z'%2F%3E%3C%2Fsvg%3E",
    'parent' => 'dashicons'
];

$menu_icon['style'] = "#toplevel_page_{$args['slug']} .wp-menu-image {
    mask-image: url(\"{$menu_icon['svg']}\");
    mask-repeat: no-repeat;
    mask-position: center;
    mask-size: 22px;
    background-color: #FFC107;
    -webkit-mask-image: url(\"{$menu_icon['svg']}\");
    -webkit-mask-repeat: no-repeat;
    -webkit-mask-position: center;
    -webkit-mask-size: 22px;
}";

$menu_icon['animation'] = "@keyframes shake {
     10%, 90% { transform: translate3d(-1px, 0, 0); }
     20%, 80% { transform: translate3d(2px, 0, 0); }
     30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
     40%, 60% { transform: translate3d(4px, 0, 0); }
}
#toplevel_page_{$args['slug']} .wp-menu-image:hover,
a.toplevel_page_{$args['slug']}:hover div.wp-menu-image {
     animation: shake 0.82s cubic-bezier(.36,.07,.19,.97) both;
     transform: translate3d(0, 0, 0);
     backface-visibility: hidden;
     perspective: 1000px;
}";

$tabs = array(
    'meta' => 'Meta Tags',
    'assets' => 'Assets',
    'analytics' => 'Analytics',
    'cache' => 'Cache',
    'headers' => 'Headers',
    'advanced' => 'Advanced',
    'agency' => 'Agency'
);

$icons = [
    'meta' => '<svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1.625em" height="1.625em" preserveAspectRatio="xMidYMid meet" viewBox="0 0 100 100"><path class="fill-blue" fill="currentColor" fill-rule="evenodd" d="M27.832 0a7.923 7.923 0 0 0-4.889 1.705L7.48 13.922a7.901 7.901 0 0 0-2.443 9.115l7.281 18.31a7.904 7.904 0 0 0 5.301 4.715l57.319 15.362a7.903 7.903 0 0 0 9.675-5.592l8.18-30.53a7.901 7.901 0 0 0-5.588-9.675L29.887.267A7.883 7.883 0 0 0 27.832 0zm31.643 15.049c-1.082.633-2.128 1.478-3.104 2.494a21.867 21.867 0 0 0-2.576 3.299l-4.928-1.319a17.073 17.073 0 0 1 10.608-4.474zm4.351.635l-1.96 7.322l-6.136-1.645a19.363 19.363 0 0 1 2.008-2.504c2.032-2.116 4.163-3.21 6.088-3.173zm1.852.416c1.84.845 3.258 2.93 4.004 5.957c.24.975.401 2.04.486 3.172l-6.47-1.733l1.98-7.396zm-47.416.074a5.532 5.532 0 1 1 .076 11.06a5.532 5.532 0 0 1-.076-11.06zm51.94 1.93a17.083 17.083 0 0 1 6.491 8.875l-4.591-1.231a21.797 21.797 0 0 0-.58-4.144c-.316-1.28-.757-2.461-1.32-3.5zm-22.833 2.978l5.422 1.455a31.389 31.389 0 0 0-2.437 5.875l-6.15-1.65a16.976 16.976 0 0 1 3.165-5.68zm7.313 1.959l6.691 1.793l-1.63 6.092l-7.556-2.024c.66-2.152 1.513-4.131 2.495-5.861zm8.523 2.285l7.031 1.883c-.014 1.989-.264 4.128-.77 6.322l-7.894-2.115l1.633-6.09zm8.92 2.39l5.086 1.362c.376 2.1.363 4.303-.1 6.504L71.3 34.023c.505-2.169.774-4.294.826-6.306zm-28.412.876l6.139 1.646a31.775 31.775 0 0 0-.903 6.287l-5.334-1.43c-.376-2.1-.365-4.302.098-6.503zm7.97 2.137l7.567 2.029l-1.633 6.09l-6.781-1.817a29.66 29.66 0 0 1 .848-6.302zm9.401 2.519l7.906 2.117a29.683 29.683 0 0 1-2.418 5.881l-7.12-1.906l1.632-6.092zm9.73 2.607l5.807 1.555a16.98 16.98 0 0 1-3.164 5.68l-5.002-1.338a31.861 31.861 0 0 0 2.36-5.897zm-26.683 1.34l4.81 1.29c.054 1.617.258 3.147.602 4.544c.27 1.098.635 2.12 1.088 3.045a17.081 17.081 0 0 1-6.5-8.879zM50.869 39l6.26 1.678l-2.041 7.617c-.111-.036-.22-.077-.33-.115c-1.534-1-2.717-2.935-3.375-5.606A19.724 19.724 0 0 1 50.869 39zm8.092 2.168l6.598 1.77a19.718 19.718 0 0 1-2.233 2.837c-1.866 1.944-3.814 3.026-5.611 3.163a17.312 17.312 0 0 1-.797-.15l2.043-7.62zm8.523 2.285l4.475 1.197a17.093 17.093 0 0 1-9.508 4.38a15.447 15.447 0 0 0 2.242-1.94c.997-1.038 1.937-2.263 2.791-3.637zm-46.55 12.218l34.862 41.786c2.281 2.938 6.788 3.408 9.625 1.003c8.22-6.853 16.463-13.685 24.666-20.567l.016-.014l.015-.015c2.725-2.478 2.854-6.93.33-9.591l-.038-.041l-.046-.04a1.336 1.336 0 0 0-.881-.304a1.292 1.292 0 0 0-.755.304c-.313.26-.407.474-.514.666c-.107.193-.19.369-.257.488c-.066.119-.145.176-.018.07l.037-.03c-.287.22-.562.582-.631.994c-.07.41.05.743.15.956c.199.424.331.628.306.536l.016.058l.023.057c.4.992.076 2.354-.775 2.888l-.066.042l-.057.049c-7.956 6.798-16.073 13.43-24.107 20.17c-1 .79-2.688.827-3.503-.076C50.858 84.97 42.434 74.835 34 64.706Z" color="currentColor"/><path fill="currentColor" fill-rule="evenodd" d="m92.114 41.972l-1.254 4.71l.175.354c.396.803.333 1.859-.2 2.468l-.053.06l-.041.066C85.272 58.189 79.74 66.718 74.3 75.31l-.02.034l-.02.037c-.328.622-.859 1.21-1.44 1.562c-.58.353-1.17.485-1.792.333h-.006l-.004-.002c-.76-.176-1.581-.96-2.784-1.622c-12.061-7.708-24.086-15.479-36.122-23.237c-5.12-1.524-10.434-3.106-15.078-4.557l51.13 32.781l.017.01l.016.011c3.139 1.85 7.391.715 9.21-2.434l-.025.043c5.752-8.956 11.559-17.885 17.284-26.87l.018-.026l.015-.029c1.476-2.658.914-6.105-1.303-8.173z" color="currentColor"/></svg>',
    'assets' => '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" focusable="false" width="1.625em" height="1.625em" style="transform: rotate(360deg);" preserveAspectRatio="xMidYMid meet" viewBox="0 0 21 21"><g fill="none" fill-rule="evenodd" stroke="#626262" stroke-linecap="round" stroke-linejoin="round"><path d="M18.5 7.5v-3a2 2 0 0 0-2-2h-3"/><path d="M10.5 12.5v-4"/><path class="fill-pink" d="M12.5 10.5h-4z"/><path class="fill-fuchsia" d="M18.5 13.5v3a2 2 0 0 1-2 2h-3"/><path d="M7.5 2.5h-3a2 2 0 0 0-2 2v3"/><path d="M7.5 18.5h-3a2 2 0 0 1-2-2v-3"/></g></svg>',
    'analytics' => '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" focusable="false" width="1.625em" height="1.625em" style="transform: rotate(360deg);" preserveAspectRatio="xMidYMid meet" viewBox="0 0 32 32"><path d="M4 2H2v26a2 2 0 0 0 2 2h26v-2H4z" fill="#626262"/><path class="fill-olive" d="M30 9h-7v2h3.59L19 18.59l-4.29-4.3a1 1 0 0 0-1.42 0L6 21.59L7.41 23L14 16.41l4.29 4.3a1 1 0 0 0 1.42 0l8.29-8.3V16h2z" fill="#626262"/></svg>',
    'cache' => '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" focusable="false" width="1.625em" height="1.625em" style="transform: rotate(360deg);" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24"><g fill="none" stroke="#626262" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path class="stroke-green"  d="M12 2v4"/><path class="stroke-orange" d="M12 18v4"/><path class="stroke-red" d="M4.93 4.93l2.83 2.83"/><path class="stroke-olive" d="M16.24 16.24l2.83 2.83"/><path class="stroke-red" d="M2 12h4"/><path class="stroke-olive" d="M18 12h4"/><path class="stroke-orange" d="M4.93 19.07l2.83-2.83"/><path class="stroke-green" d="M16.24 7.76l2.83-2.83"/></g></svg>',
    'headers' => '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" focusable="false" width="1.625em" height="1.625em" style="transform: rotate(360deg);" preserveAspectRatio="xMidYMid meet" viewBox="0 0 512 512"><path class="fill-red" d="M63.28 202a15.29 15.29 0 0 1-7.7-2a14.84 14.84 0 0 1-5.52-20.46C69.34 147.36 128 72.25 256 72.25c55.47 0 104.12 14.57 144.53 43.29c33.26 23.57 51.9 50.25 60.78 63.1a14.79 14.79 0 0 1-4 20.79a15.52 15.52 0 0 1-21.24-4C420 172.32 371 102 256 102c-112.25 0-163 64.71-179.53 92.46A15 15 0 0 1 63.28 202z" fill="#626262"/><path  d="M320.49 496a15.31 15.31 0 0 1-3.79-.43c-92.85-23-127.52-115.82-128.93-119.68l-.22-.85c-.76-2.68-19.39-66.33 9.21-103.61c13.11-17 33.05-25.72 59.38-25.72c24.48 0 42.14 7.61 54.28 23.36c10 12.86 14 28.72 17.87 44c8.13 31.82 14 48.53 47.79 50.25c14.84.75 24.59-7.93 30.12-15.32c14.95-20.15 17.55-53 6.28-82C398 228.57 346.61 158 256 158c-38.68 0-74.22 12.43-102.72 35.79c-23.59 19.35-42.28 46.67-51.28 74.75c-16.69 52.28 5.2 134.46 5.41 135.21A14.83 14.83 0 0 1 96.54 422a15.39 15.39 0 0 1-18.74-10.6c-1-3.75-24.38-91.4-5.1-151.82c21-65.47 85.81-131.47 183.33-131.47c45.07 0 87.65 15.32 123.19 44.25c27.52 22.5 50 52.72 61.76 82.93c14.95 38.57 10.94 81.86-10.19 110.14c-14.08 18.86-34.13 28.72-56.34 27.65c-57.86-2.9-68.26-43.29-75.84-72.75c-7.8-30.22-12.79-44.79-42.58-44.79c-16.36 0-27.85 4.5-35 13.82c-9.75 12.75-10.51 32.68-9.43 47.14a152.44 152.44 0 0 0 5.1 29.79c2.38 6 33.37 82 107.59 100.39a14.88 14.88 0 0 1 11 18.11a15.36 15.36 0 0 1-14.8 11.21z" fill="#626262"/><path class="fill-red" d="M201.31 489.14a15.5 15.5 0 0 1-11.16-4.71c-37.16-39-58.18-82.61-66.09-137.14V347c-4.44-36.1 2.06-87.21 33.91-122.35c23.51-25.93 56.56-39.11 98.06-39.11c49.08 0 87.65 22.82 111.7 65.89c17.45 31.29 20.91 62.47 21 63.75a15.07 15.07 0 0 1-13.65 16.4a15.26 15.26 0 0 1-16.79-13.29A154 154 0 0 0 340.43 265c-18.64-32.89-47-49.61-84.51-49.61c-32.4 0-57.75 9.75-75.19 29c-25.14 27.75-30 70.5-26.55 98.78c6.93 48.22 25.46 86.58 58.18 120.86a14.7 14.7 0 0 1-.76 21.11a15.44 15.44 0 0 1-10.29 4z" fill="#626262"/><path class="fill-red" d="M372.5 446.18c-32.5 0-60.13-9-82.24-26.89c-44.42-35.79-49.4-94.08-49.62-96.54a15.27 15.27 0 0 1 30.45-2.36c.11.86 4.55 48.54 38.79 76c20.26 16.18 47.34 22.6 80.71 18.85a15.2 15.2 0 0 1 16.91 13.18a14.92 14.92 0 0 1-13.44 16.5a187 187 0 0 1-21.56 1.26z" fill="#626262"/><path d="M398.18 48.79C385.5 40.54 340.54 16 256 16c-88.74 0-133.81 27.11-143.78 34a11.59 11.59 0 0 0-1.84 1.4a.36.36 0 0 1-.22.1a14.87 14.87 0 0 0-5.09 11.15a15.06 15.06 0 0 0 15.31 14.85a15.56 15.56 0 0 0 8.88-2.79c.43-.32 39.22-28.82 126.77-28.82S382.58 74.29 383 74.5a15.25 15.25 0 0 0 9.21 3a15.06 15.06 0 0 0 15.29-14.89a14.9 14.9 0 0 0-9.32-13.82z" fill="#626262"/></svg>',
    'advanced' => '<svg xmlns="http://www.w3.org/2000/svg" width="1.625em" height="1.625em" preserveAspectRatio="xMidYMid meet" viewBox="0 0 32 32"><path fill="currentColor" d="M16 22a6 6 0 1 1 6-6a5.936 5.936 0 0 1-6 6Zm0-10a3.912 3.912 0 0 0-4 4a3.912 3.912 0 0 0 4 4a3.912 3.912 0 0 0 4-4a3.912 3.912 0 0 0-4-4Z"/><path fill="currentColor" d="m29.305 11.044l-2.36-4.088a1.998 1.998 0 0 0-2.374-.894l-2.434.823a11.042 11.042 0 0 0-1.312-.758l-.503-2.519A2 2 0 0 0 18.36 2h-4.72a2 2 0 0 0-1.962 1.608l-.503 2.519a10.967 10.967 0 0 0-1.327.753l-2.42-.818a1.998 1.998 0 0 0-2.372.894l-2.36 4.088a2 2 0 0 0 .411 2.502l1.931 1.697C5.021 15.495 5 15.745 5 16c0 .258.01.513.028.766l-1.92 1.688a2 2 0 0 0-.413 2.502l2.36 4.088a1.998 1.998 0 0 0 2.374.895l2.434-.824a10.974 10.974 0 0 0 1.312.759l.503 2.518A2 2 0 0 0 13.64 30H18v-2h-4.36l-.71-3.55a9.095 9.095 0 0 1-2.695-1.572l-3.447 1.166l-2.36-4.088l2.725-2.395a8.926 8.926 0 0 1-.007-3.128l-2.718-2.389l2.36-4.088l3.427 1.16A9.03 9.03 0 0 1 12.93 7.55L13.64 4h4.72l.71 3.55a9.098 9.098 0 0 1 2.695 1.572l3.447-1.166l2.36 4.088l-2.798 2.452L26.092 16l2.8-2.454a2 2 0 0 0 .413-2.502Z"/><path class="fill-blue" fill="currentColor" d="m23 26.18l-2.59-2.59L19 25l4 4l7-7l-1.41-1.41L23 26.18z"/></svg>',
    'agency' => '<svg xmlns="http://www.w3.org/2000/svg" width="1.625em" height="1.625em" preserveAspectRatio="xMidYMid meet" viewBox="0 0 32 32"><path class="fill-purple" fill="currentColor" d="M27 20.397v3c-1 0-2-1.5-2-4v-3c-4 5-5 7-5 9A5 5 0 0 0 23.046 30A7.528 7.528 0 0 1 25 26.397A7.528 7.528 0 0 1 26.954 30A5 5 0 0 0 30 25.397c0-2-1.125-3.571-3-5zM17 28H4v-4h13v-2H4a2.002 2.002 0 0 0-2 2v4a2.002 2.002 0 0 0 2 2h13z"/><path fill="currentColor" d="M28 12H7a2.002 2.002 0 0 0-2 2v4a2.002 2.002 0 0 0 2 2h10v-2H7v-4h21l.001 2H30v-2a2.002 2.002 0 0 0-2-2zm-3-2H4a2.002 2.002 0 0 1-2-2V4a2.002 2.002 0 0 1 2-2h21a2.002 2.002 0 0 1 2 2v4a2.002 2.002 0 0 1-2 2zM4 4v4h21V4z"/></svg>',
];

/**
 * Promobox : define features according to plans
 */
$features = [];

$starter = [
    __('Local Google Analytics 4', 'flashspeed'),
    __('Custom code blocks', 'flashspeed'),
];

$growth = [
    __('Caching and Page preloading', 'flashspeed'),
];

$pro = [
    __('Security headers', 'flashspeed'),
    __('Content Security Policy', 'flashspeed'),
    __('Optimize resources', 'flashspeed'),
];

$agency = [
    __('Advanced options for agencies and clients', 'flashspeed'),
    //__('Debugging management', 'flashspeed'),
    //__('Restrict plugin options for clients', 'flashspeed'),
    //__('Core, plugins, themes updates management', 'flashspeed'),
    //__('Email notifications management', 'flashspeed'),
];

/* Free plan or non-paying user */
if (\SkyLoginRedirect\Sky_Login_Redirect_fs()->is_not_paying() || \SkyLoginRedirect\Sky_Login_Redirect_fs()->is_free_plan()) {
    //$features = [...$starter, ...$growth, ...$pro, ...$agency];
    // PHP < 7.4
    $features = array_merge($starter, $growth, $pro, $agency);
}

/* Starter */
if (\SkyLoginRedirect\Sky_Login_Redirect_fs()->is_plan('starter', true)) {
    //$features = [...$growth, ...$pro, ...$agency];
    // PHP < 7.4
    $features = array_merge($growth, $pro, $agency);
}

/* Growth */
if (\SkyLoginRedirect\Sky_Login_Redirect_fs()->is_plan('growth', true)) {
    //$features = [...$pro, ...$agency];
    // PHP < 7.4
    $features = array_merge($pro, $agency);
}

/* Pro */
if (\SkyLoginRedirect\Sky_Login_Redirect_fs()->is_plan('pro', true)) {
    //$features = [...$agency];
    // PHP < 7.4
    $features = $agency;
}

/* common features for all plans */
$all_plans = [
    'Priority premium support'
];
$features = array_merge($features, $all_plans);
