<?php

return [

    'title' => '',
    'title_prefix' => '',
    'title_postfix' => ' | PWRTALLER',

    'use_ico_only' => true,
    'use_full_favicon' => false,

    'google_fonts' => [
        'allowed' => true,
    ],

    'logo' => '<b>PWR</b>TALLER',
    'logo_img' => 'img/logopowercars.webp',
    'logo_img_class' => 'brand-image ',
    'logo_img_xl' => null,
    'logo_img_xl_class' => 'brand-image-xs',
    'logo_img_alt' => 'PWRTALLER',

    'auth_logo' => [
        'enabled' => false,
        'img' => [
            'path' => 'img/logopowercars.webp',
            'alt' => 'PWRTALLER',
            'class' => '',
            'width' => 50,
            'height' => 50,
        ],
    ],

    'preloader' => [
        'enabled' => true,
        'img' => [
            'path' => 'img/logopowercars.webp',
            'alt' => 'PWRTALLER',
            'effect' => 'animation__shake',
            'width' => 60,
            'height' => 60,
        ],
    ],

    'usermenu_enabled' => true,
    'usermenu_header' => true,
    'usermenu_header_class' => 'bg-dark',
    'usermenu_image' => true,
    'usermenu_desc' => true,
    'usermenu_profile_url' => true,

    'layout_topnav' => null,
    'layout_boxed' => null,
    'layout_fixed_sidebar' => true,
    'layout_fixed_navbar' => true,
    'layout_fixed_footer' => true,
    'layout_dark_mode' => true,  // Mantener el modo oscuro desactivado por defecto

    'classes_auth_card' => 'card-outline card-orange',
    'classes_auth_header' => '',
    'classes_auth_body' => '',
    'classes_auth_footer' => '',
    'classes_auth_icon' => '',
    'classes_auth_btn' => 'btn-flat btn-primary',

    'classes_body' => '',
    'classes_brand' => 'bg-dark',
    'classes_brand_text' => 'text-white',
    'classes_content_wrapper' => '',
    'classes_content_header' => '',
    'classes_content' => '',
    'classes_sidebar' => 'sidebar-dark-orange elevation-1',
    'classes_sidebar_nav' => '',
    'classes_topnav' => 'navbar-dark navbar-light',
    'classes_topnav_nav' => 'navbar-expand',
    'classes_topnav_container' => 'container',

    'sidebar_mini' => true,
    'sidebar_collapse' => true,
    'sidebar_collapse_auto_size' => false,
    'sidebar_collapse_remember' => false,
    'sidebar_collapse_remember_no_transition' => true,
    'sidebar_scrollbar_theme' => 'os-theme-light',
    'sidebar_scrollbar_auto_hide' => 'l',
    'sidebar_nav_accordion' => true,
    'sidebar_nav_animation_speed' => 300,

    'right_sidebar' => true,
    'right_sidebar_icon' => 'fas fa-comments',
    'right_sidebar_theme' => 'dark elevation-1',
    'right_sidebar_slide' => true,
    'right_sidebar_push' => true,
    'right_sidebar_scrollbar_theme' => 'dark',
    'right_sidebar_scrollbar_auto_hide' => '0',

    'use_route_url' => false,
    'dashboard_url' => 'dashboard',
    'logout_url' => 'logout',
    'login_url' => 'login',
    'register_url' => 'register',
    'password_reset_url' => 'password/reset',
    'password_email_url' => 'password/email',
    'profile_url' => true,

    'enabled_laravel_mix' => false,
    'laravel_mix_css_path' => 'css/app.css',
    'laravel_mix_js_path' => 'js/app.js',

    'menu' => [
        [
            'type'         => 'navbar-search',
            'text'         => 'buscar',
            'topnav_right' => false,
            'icon'         => 'fas fa-fw fa-search',
        ],
        [
            'type'         => 'fullscreen-widget',
            'topnav_right' => false,
            'icon'         => 'fas fa-fw fa-expand-arrows-alt',
        ],

        [
            'text' => 'Dashboard',
            'route' => 'dashboard',
            'icon' => 'fas fa-fw fa-home',
        ],
        [
            'text' => 'Crear OT',
            //'topnav' => true,
            'route' => 'work-orders.create-step-one',
            'icon' => 'fas fa-fw fa-plus-circle',
            'role' => 'Ejecutivo',
        ],
        [
            'text' => 'Mis OT',
            'url'  => 'executive-work-orders',
            'icon' => 'fas fa-fw fa-clipboard-list',
            'role' => 'Ejecutivo',
        ],
        [
            'text' => 'Cotizaciones',
            'route'  => 'work-orders.quotations',
            'icon' => 'fas fa-fw fa-list',
            'role' => 'Ejecutivo',
        ],
        [
            'text' => 'Agendadas',
            'url'  => 'work-orders/scheduled',
            'icon' => 'fas fa-fw fa-calendar',
            'role' => 'Ejecutivo',
        ],
        [
            'text' => 'Mis OT',
            'route' => 'leader-work-orders.index',
            'icon' => 'fas fa-fw fa-tasks',
            'role' => 'Líder',
        ],
        [
            'text' => 'Mis OT',
            'route' => 'mechanic-work-orders.index',
            'icon' => 'fas fa-fw fa-user-cog',
            'role' => 'Mecánico',
        ],
        [
            'text' => 'ÓT de Bodega',
            'route' => 'warehouse-work-orders.index',
            'icon' => 'fas fa-fw fa-boxes',
            'role' => 'Bodeguero',
        ],
        [
            'text' => 'Resumen por Montos',
            'route' => 'manager-work-orders.summary',
            'icon' => 'fas fa-fw fa-chart-bar',
            'role' => 'Manager',
        ],
        [
            'text' => 'Resumen de Estadisticas',
            'route' => 'manager-work-orders.stats',
            'icon' => 'fas fa-fw fa-dollar-sign',
            'role' => 'Manager',
        ],
        [
            'text' => 'Lista de OT',
            'route' => 'manager-work-orders.index',
            'icon' => 'fas fa-fw fa-list',
            'role' => 'Manager',
        ],
        [
            'text'        => 'Usuarios',
            'icon'        => 'fas fa-fw fa-users',
            'submenu'      => [
                [
                    'text' => 'Todos',
                    'route' => 'users.index',
                    'icon' => 'fas fa-fw fa-users',
                    'active' => ['users', 'users/*'],
                ],
                [
                    'text' => 'Roles',
                    'route' => 'users.roles.index',
                    'icon' => 'fas fa-fw fa-user-tag',
                    'active' => ['users/roles/*'],
                ],
                [
                    'text' => 'Permisos',
                    'route' => 'users.permissions.index',
                    'icon' => 'fas fa-fw fa-user-shield',
                    'active' => ['users/permissions/*'],
                ],
            ],
            'role' => 'Administrador',
        ],
        [
            'text'        => 'Grupos de Clientes',
            'route'       => 'client-groups.index',
            'icon'        => 'fas fa-fw fa-users-cog',
            'role' => 'Administrador',
        ],
        [
            'text'        => 'Clientes',
            'route'       => 'clients.index',
            'icon'        => 'fas fa-fw fa-user-friends',
            'role' => 'Administrador',
        ],
        [
            'text'        => 'Servicios',
            'route'       => 'services.index',
            'icon'        => 'fas fa-fw fa-concierge-bell',
            'role' => 'Administrador',
        ],
        [
            'text'        => 'Incidentes',
            'route'       => 'incidents.index',
            'icon'        => 'fas fa-fw fa-exclamation-triangle',
            'role' => 'Administrador',
        ],
        [
            'text'        => 'Productos',
            'route'       => 'products.index',
            'icon'        => 'fas fa-fw fa-box-open',
            'role' => 'Administrador',
        ],
        [
            'text'        => 'Reportes',
            'route'       => 'reports.index',
            'icon'        => 'fas fa-fw fa-chart-line',
            'role' => 'Administrador',
        ],
        [
            'text'        => 'Marcas',
            'route'       => 'brands.index',
            'icon'        => 'fas fa-fw fa-car',
            'role' => 'Administrador',
        ],
        [
            'text'        => 'Vehículos',
            'route'       => 'vehicles.index',
            'icon'        => 'fas fa-fw fa-car-side',
            'role' => 'Administrador',
        ],
        [
            'header' => 'Configuración de la Cuenta',
        ],
        [
            'text' => 'Perfil',
            'url'  => 'profile',
            'icon' => 'fas fa-fw fa-user',
        ],
        [
            'text' => 'Configuraciones',
            'url'  => 'settings',
            'icon' => 'fas fa-fw fa-cogs',
            'role' => 'Administrador',
        ],
    ],

    'filters' => [
        JeroenNoten\LaravelAdminLte\Menu\Filters\GateFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\HrefFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\SearchFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ActiveFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ClassesFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\LangFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\DataFilter::class,
    ],

    'plugins' => [
        'Datatables' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css',
                ],
            ],
        ],
        'Select2' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.css',
                ],
            ],
        ],
        'Chartjs' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.0/Chart.bundle.min.js',
                ],
            ],
        ],
        'Sweetalert2' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.jsdelivr.net/npm/sweetalert2@8',
                ],
            ],
        ],
        'Pace' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/themes/blue/pace-theme-center-radar.min.css',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/pace.min.js',
                ],
            ],
        ],
    ],

    'iframe' => [
        'default_tab' => [
            'url' => null,
            'title' => null,
        ],
        'buttons' => [
            'close' => true,
            'close_all' => true,
            'close_all_other' => true,
            'scroll_left' => true,
            'scroll_right' => true,
            'fullscreen' => true,
        ],
        'options' => [
            'loading_screen' => 1000,
            'auto_show_new_tab' => true,
            'use_navbar_items' => true,
        ],
    ],

    'livewire' => false,
];
