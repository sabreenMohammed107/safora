<?php

return [
    'main_branch' => 'Main branch',

    'roles' => [
        'owner' => 'Company manager',
        'member' => 'Employee',
    ],

    'default_roles' => [
        'branch_manager' => 'Branch manager',
        'branch_user' => 'Branch user',
    ],

    'team' => [
        'title' => 'Team & roles',
        'heading' => 'Team & roles',
        'intro' => 'Add your facility’s employees and give each a branch and a role. Available permissions follow your subscription plan.',
        'available_permissions' => ':count permission available|:count permissions available',
        'members' => 'Users',
        'roles' => 'Roles',
        'roles_hint' => 'Permissions outside your current plan do not apply until you upgrade.',
        'new_member' => 'New user',
        'new_role' => 'New role',
        'default_role' => 'Default',
        'role_intro' => 'Choose what holders of this role can do. Locked items are outside your plan or not among your own permissions.',
        'outside_plan' => 'Not available on your current plan',
        'access' => 'Account type & permissions',
        'permissions_count' => ':count permission|:count permissions',
        'members_count' => ':count user|:count users',
        'columns' => ['role' => 'Role'],
        'fields' => [
            'role_name' => 'Role name',
            'description' => 'Short description',
        ],
        'type_hint' => [
            'owner' => 'Every branch and every permission the plan allows.',
            'member' => 'One branch and the permissions of the chosen role.',
        ],
        'errors' => [
            'owner_only' => 'Only a company manager can appoint another company manager.',
            'self_lockout' => 'You cannot deactivate your own account or change its type.',
            'last_owner' => 'At least one active company manager must remain.',
        ],
        'flash' => [
            'member_created' => '“:name” added.',
            'member_updated' => '“:name” saved.',
            'role_created' => 'Role “:name” created.',
            'role_updated' => 'Role “:name” saved.',
            'role_deleted' => 'Role “:name” deleted.',
            'role_in_use' => 'A role assigned to users cannot be deleted.',
        ],
    ],

    'nav' => [
        'label' => 'Client portal menu',
        'locked' => 'Not included in your current plan',
        'home' => 'Home',
        'products' => 'Products',
        'haccp_pack' => 'HACCP pack',
        'iso_pack' => 'ISO pack',
        'prp' => 'PRP programs',
        'haccp_plans' => 'HACCP plans',
        'control_points' => 'Control points',
        'critical_limits' => 'Critical limits',
        'traceability' => 'Traceability & recall',
        'iso_procedures' => 'Procedures & forms',
        'iso_items' => 'ISO items',
        'product_requests' => 'Product requests',
        'team' => 'Team & roles',
        'branches' => 'Branches',
        'account' => 'My account',
    ],

    'branches' => [
        'label' => 'Branch',
        'all' => 'All branches',
        'all_compare' => 'All branches (view & compare)',
    ],

    'auth' => [
        'title' => 'Client portal sign in',
        'heading' => 'Sign in',
        'intro' => 'Use the sign-in details you received from the SAFORA team.',
        'aside_title' => 'Client portal',
        'aside_text' => 'Everything your facility needs to manage food safety under ISO 22000:2018 and SFDA requirements, in one place.',
        'features' => [
            'limits' => ['title' => 'Critical limits & control points', 'text' => 'Matched to your sectors and products'],
            'records' => ['title' => 'Daily records', 'text' => 'Fast Excel-style or form entry'],
            'plans' => ['title' => 'HACCP plans, traceability & recall', 'text' => 'Ready to apply and audit'],
        ],
        'forgot' => 'Forgot your password? Contact the SAFORA team or your company manager to reset it.',
        'new_company' => 'New facility?',
        'register_now' => 'Register now',
        'signed_out' => 'You have been signed out.',
    ],

    'register' => [
        'title' => 'Register a facility',
        'heading' => 'Register a new facility',
        'intro' => 'Fill in the details and attach your commercial registration. The SAFORA team reviews the request and activates the subscription.',
        'aside_title' => 'How registration works',
        'steps' => [
            'submit' => 'Send your facility details and documents and choose a sign-in password.',
            'review' => 'The SAFORA team reviews the request and sets your plan.',
            'activate' => 'Sign in with your email and password once approved.',
        ],
        'company_section' => 'Facility details',
        'login_section' => 'Sign-in details (company manager)',
        'password_hint' => 'At least 8 characters, including letters and numbers.',
        'documents' => 'Commercial registration / VAT certificate',
        'documents_hint' => 'PDF or image · up to :max files · :size MB each.',
        'submit' => 'Submit registration',
        'have_account' => 'Already registered?',
        'submitted' => 'Request :code received. You will be notified once the SAFORA team has reviewed it; you can sign in to follow its status.',
    ],

    'blocked' => [
        'reason' => 'Reason',
        'pending' => ['title' => 'Your account is awaiting approval', 'text' => 'The SAFORA team is reviewing your registration; you will receive your subscription details once it is approved.'],
        'rejected' => ['title' => 'Registration rejected', 'text' => 'Please contact the SAFORA team for details.'],
        'suspended' => ['title' => 'Account temporarily suspended', 'text' => 'Contact the SAFORA team to reactivate it.'],
        'no_plan' => ['title' => 'No subscription plan set', 'text' => 'Please contact the SAFORA team.'],
        'expired' => ['title' => 'Subscription expired', 'text' => 'Renew with the SAFORA team to continue. Your data is kept.'],
        'user_inactive' => ['title' => 'Your user account is disabled', 'text' => 'Ask your company manager to reactivate it.'],
        'no_branch' => ['title' => 'No active branch', 'text' => 'There is no active branch linked to your account. Contact your company manager.'],
    ],

    'impersonation' => [
        'banner' => 'You are viewing the client portal as a system administrator (impersonation).',
        'leave' => 'Back to admin console',
    ],

    'home' => [
        'welcome' => 'Welcome, :name',
        'plan' => 'Plan',
        'valid_until' => 'Valid until',
        'days_left' => ':count day left|:count days left',
        'branches_count' => ':count branch available|:count branches available',
        'compare_heading' => 'Branch comparison',
        'compare_hint' => '“All branches” is for viewing and comparing only — open a branch to enter records.',
        'open_branch' => 'Open branch',
        'modules_heading' => 'Included in your plan',
    ],
];
