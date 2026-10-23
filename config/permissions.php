<?php

/*
 | Permission modules shown in the Roles & Permissions matrix.
 | Permission keys are "<module>:<action>", e.g. "products:view" (see Permissions::total()).
 */
return [
    'actions' => [
        'view' => 'View',
        'create' => 'Create',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'export' => 'Export',
    ],

    'modules' => [
        'dashboard' => ['label' => 'Dashboard', 'icon' => 'home', 'actions' => ['view']],
        'products' => ['label' => 'Products', 'icon' => 'box', 'actions' => ['view', 'create', 'edit', 'delete', 'export']],
        'categories' => ['label' => 'Categories', 'icon' => 'layers', 'actions' => ['view', 'create', 'edit', 'delete']],
        'brands' => ['label' => 'Brands', 'icon' => 'award', 'actions' => ['view', 'create', 'edit', 'delete']],
        'orders' => ['label' => 'Orders', 'icon' => 'cart', 'actions' => ['view', 'create', 'edit', 'delete', 'export']],
        'customers' => ['label' => 'Customers', 'icon' => 'users', 'actions' => ['view', 'create', 'edit', 'delete', 'export']],
        'coupons' => ['label' => 'Coupons', 'icon' => 'ticket', 'actions' => ['view', 'create', 'edit', 'delete']],
        'flash' => ['label' => 'Flash Deals', 'icon' => 'zap', 'actions' => ['view', 'create', 'edit', 'delete']],
        'banners' => ['label' => 'Banners & Sliders', 'icon' => 'image', 'actions' => ['view', 'create', 'edit', 'delete']],
        'pages' => ['label' => 'Pages', 'icon' => 'file', 'actions' => ['view', 'create', 'edit', 'delete']],
        'faqs' => ['label' => 'FAQs', 'icon' => 'help', 'actions' => ['view', 'create', 'edit', 'delete']],
        'messages' => ['label' => 'Messages', 'icon' => 'mail', 'actions' => ['view', 'edit', 'delete']],
        'reports' => ['label' => 'Reports', 'icon' => 'chart', 'actions' => ['view', 'export']],
        'expenses' => ['label' => 'Expenses', 'icon' => 'wallet', 'actions' => ['view', 'create', 'edit', 'delete', 'export']],
        'settings' => ['label' => 'Site Settings', 'icon' => 'settings', 'actions' => ['view', 'edit']],
        'roles' => ['label' => 'Roles & Permissions', 'icon' => 'shield', 'actions' => ['view', 'create', 'edit', 'delete']],
        'staff' => ['label' => 'Staff Members', 'icon' => 'users', 'actions' => ['view', 'create', 'edit', 'delete']],
    ],

    'presets' => [
        'Manager' => [
            'color' => '#0B3A8C',
            'description' => 'Manage catalog, sales and content. No access control.',
            'except' => ['roles', 'staff'],
            'only_view' => ['settings'],
        ],
        'Order Manager' => [
            'color' => '#0891B2',
            'description' => 'Process orders, view customers and reply to messages.',
            'permissions' => ['dashboard:view', 'orders:view', 'orders:create', 'orders:edit', 'orders:export', 'customers:view', 'customers:export', 'coupons:view', 'messages:view', 'messages:edit'],
        ],
        'Content Editor' => [
            'color' => '#7C3AED',
            'description' => 'Create and edit products, banners, pages and FAQs.',
            'permissions' => ['dashboard:view', 'products:view', 'products:create', 'products:edit', 'categories:view', 'categories:create', 'categories:edit', 'brands:view', 'brands:create', 'brands:edit', 'banners:view', 'banners:create', 'banners:edit', 'pages:view', 'pages:create', 'pages:edit', 'faqs:view', 'faqs:create', 'faqs:edit'],
        ],
        'Support Agent' => [
            'color' => '#0A8F6B',
            'description' => 'Handle customer orders and messages only.',
            'permissions' => ['dashboard:view', 'orders:view', 'orders:edit', 'customers:view', 'messages:view', 'messages:edit', 'faqs:view'],
        ],
    ],
];
