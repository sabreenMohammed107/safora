<?php

return [
    'must_change_password' => 'Your current password is temporary. Choose a new password to continue.',

    'errors' => [
        'last_branch' => 'The last active branch cannot be deactivated.',
        'last_owner' => 'At least one active company manager must remain.',
        'branch_limit' => 'The plan’s branch limit (:max) has been reached.',
        'user_limit' => 'The plan’s user limit (:max) has been reached.',
    ],

    'branch' => [
        'heading' => 'Branches',
        'intro' => 'Add, edit or deactivate your facility’s branches. Deactivating a branch keeps its records.',
        'new' => 'New branch',
        'active' => 'Branch active',
        'deactivate_hint' => 'Deactivating blocks its users from signing in and keeps all its records. The last active branch cannot be deactivated.',
        'cost_note' => 'Activating this branch adds :amount SAR to the subscription (:cycle, VAT included).',
        'included_note' => 'Within the branches included in the plan — no extra cost.',
        'active_branches' => 'Active branches',
        'included_count' => 'Included in the plan: :count',
        'next_branch' => 'Next branch cost',
        'vat_included' => 'VAT included',
        'fields' => [
            'name' => 'Branch name',
            'city' => 'City',
            'address' => 'Address',
            'manager' => 'Branch manager',
        ],
    ],

    'password' => [
        'reset' => 'Reset password',
        'confirm_reset' => 'Create a new temporary password for “:name”? Their current password stops working immediately.',
        'temporary' => 'Temporary',
        'pending_change' => 'Must change the password at next sign-in',
        'generate_hint' => 'Leave empty to generate a temporary password. It must be changed at first sign-in.',
        'admin_edit_hint' => 'Leave empty to keep it. A new password is temporary and must be changed at sign-in.',
        'required_temporary_hint' => 'A temporary password the user must change at first sign-in.',
        'change_heading' => 'Change password',
        'current' => 'Current password',
        'new' => 'New password',
        'update' => 'Update password',
        'other_devices' => 'Other devices will be signed out.',
    ],

    'credentials' => [
        'title' => 'Sign-in details',
        'shown_once' => 'Shown only once — copy it now',
        'copy' => 'Copy message',
        'copied' => 'Copied',
        'message' => "Hello :name,\nYour SAFORA account for :company is ready.\n\nSign in at: :url\nEmail: :email\nTemporary password: :password\n\nYou will be asked to choose a new password when you first sign in.",
    ],

    'profile' => [
        'title' => 'My profile',
        'details' => 'Personal details',
    ],

    'flash' => [
        'branch_created' => 'Branch “:name” added.',
        'branch_updated' => 'Branch “:name” saved.',
        'password_reset' => 'A temporary password was created for “:name”.',
        'password_changed' => 'Your password has been changed.',
        'profile_saved' => 'Your details have been saved.',
    ],
];
