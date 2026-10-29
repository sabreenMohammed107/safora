<?php

/*
| Keys mirror the permission values: "clients.view" → admin.clients.view (the translator reads dots as nesting).
*/

return [
    'admin_groups' => [
        'clients' => 'Clients',
        'catalog' => 'Plans & categories',
        'content' => 'HACCP & ISO content',
        'administration' => 'Administration',
    ],

    'admin' => [
        'clients' => [
            'view' => 'View clients and their profiles',
            'approve' => 'Approve and reject registrations',
            'suspend' => 'Suspend and reactivate clients',
            'impersonate' => 'Log in as a client',
            'documents' => 'Download client documents',
            'accounts' => 'Manage client branches, users and passwords',
            'sectors' => 'Grant and revoke client food sectors',
        ],
        'plans' => ['manage' => 'Manage plans and pricing'],
        'categories' => ['manage' => 'Manage sector categories'],
        'haccp' => ['manage' => 'Manage HACCP libraries'],
        'iso' => ['manage' => 'Manage ISO procedures and forms'],
        'staff' => ['manage' => 'Manage staff'],
        'roles' => ['manage' => 'Manage roles and permissions'],
    ],

    'client_groups' => [
        'records' => 'Records',
        'modules' => 'Plan modules',
        'products' => 'Products',
        'administration' => 'Company administration',
    ],

    'client' => [
        'records' => [
            'view' => 'View records',
            'create' => 'Add records',
            'edit' => 'Edit records',
            'delete' => 'Delete records',
        ],
        'prp' => [
            'access' => 'PRP programs and forms',
            'customize' => 'Customize PRP texts for the facility',
        ],
        'haccp_plans' => ['manage' => 'Fill in HACCP plans'],
        'control_points' => ['monitor' => 'Monitor CCP / OPRP control points'],
        'critical_limits' => ['record' => 'Record critical-limit readings'],
        'traceability' => ['manage' => 'Traceability & recall'],
        'iso' => ['access' => 'ISO procedures and forms'],
        'products' => ['manage' => 'Manage branch products and links'],
        'product_requests' => ['create' => 'Request new products'],
        'branches' => ['manage' => 'Manage branches'],
        'users' => ['manage' => 'Manage users'],
        'roles' => ['manage' => 'Manage roles and permissions'],
        'subscription' => ['view' => 'View subscription and billing'],
    ],
];
