<?php

/*
| Keys mirror the permission values: "clients.view" → admin.clients.view (the translator reads dots as nesting).
*/

return [
    'admin_groups' => [
        'clients' => 'العملاء',
        'catalog' => 'الخطط والتصنيفات',
        'content' => 'محتوى الهاسب و ISO',
        'administration' => 'الإدارة',
    ],

    'admin' => [
        'clients' => [
            'view' => 'عرض العملاء وملفاتهم',
            'approve' => 'اعتماد ورفض طلبات التسجيل',
            'suspend' => 'إيقاف وإعادة تفعيل العملاء',
            'impersonate' => 'الدخول كعميل',
            'documents' => 'تنزيل مستندات العملاء',
            'accounts' => 'إدارة فروع ومستخدمي العملاء وكلمات المرور',
            'sectors' => 'منح وسحب القطاعات الغذائية للعملاء',
        ],
        'plans' => ['manage' => 'إدارة الخطط والأسعار'],
        'categories' => ['manage' => 'إدارة تصنيفات القطاعات'],
        'haccp' => ['manage' => 'إدارة مكتبات الهاسب'],
        'iso' => ['manage' => 'إدارة إجراءات ونماذج ISO'],
        'staff' => ['manage' => 'إدارة فريق العمل'],
        'roles' => ['manage' => 'إدارة الأدوار والصلاحيات'],
    ],

    'client_groups' => [
        'records' => 'السجلات',
        'modules' => 'وحدات الخطة',
        'products' => 'المنتجات',
        'administration' => 'إدارة المنشأة',
    ],

    'client' => [
        'records' => [
            'view' => 'عرض السجلات',
            'create' => 'إضافة السجلات',
            'edit' => 'تعديل السجلات',
            'delete' => 'حذف السجلات',
        ],
        'prp' => [
            'access' => 'برامج PRPs ونماذجها',
            'customize' => 'تخصيص نصوص PRPs للمنشأة',
        ],
        'haccp_plans' => ['manage' => 'تعبئة خطط الهاسب'],
        'control_points' => ['monitor' => 'مراقبة نقاط التحكم CCP / OPRP'],
        'critical_limits' => ['record' => 'تسجيل قراءات الحدود الحرجة'],
        'traceability' => ['manage' => 'التتبع والاستدعاء'],
        'iso' => ['access' => 'إجراءات ونماذج ISO'],
        'products' => ['manage' => 'إدارة منتجات الفرع والربط'],
        'product_requests' => ['create' => 'طلب منتجات جديدة'],
        'branches' => ['manage' => 'إدارة الفروع'],
        'users' => ['manage' => 'إدارة المستخدمين'],
        'roles' => ['manage' => 'إدارة الأدوار والصلاحيات'],
        'subscription' => ['view' => 'عرض الاشتراك والفواتير'],
    ],
];
