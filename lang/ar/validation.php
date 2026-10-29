<?php

/*
| Arabic messages for the validation rules the application uses. Arabic is also the
| fallback locale, so add a message here whenever a new rule is introduced.
*/

return [
    'array' => 'حقل :attribute يجب أن يكون قائمة.',
    'between' => [
        'numeric' => 'حقل :attribute يجب أن يكون بين :min و :max.',
    ],
    'boolean' => 'قيمة :attribute غير صحيحة.',
    'confirmed' => 'تأكيد :attribute غير مطابق.',
    'date' => 'حقل :attribute ليس تاريخاً صحيحاً.',
    'email' => 'حقل :attribute يجب أن يكون بريداً إلكترونياً صحيحاً.',
    'enum' => 'القيمة المختارة في :attribute غير صحيحة.',
    'exists' => 'القيمة المختارة في :attribute غير صحيحة.',
    'file' => 'حقل :attribute يجب أن يكون ملفاً.',
    'max' => [
        'array' => 'حقل :attribute يجب ألا يزيد عن :max عناصر.',
        'file' => 'حجم :attribute يجب ألا يزيد عن :max كيلوبايت.',
        'numeric' => 'حقل :attribute يجب ألا يزيد عن :max.',
        'string' => 'حقل :attribute يجب ألا يزيد عن :max حرفاً.',
    ],
    'in' => 'القيمة المختارة في :attribute غير مسموحة.',
    'mimes' => 'حقل :attribute يجب أن يكون ملفاً من نوع: :values.',
    'min' => [
        'numeric' => 'حقل :attribute يجب ألا يقل عن :min.',
        'string' => 'حقل :attribute يجب ألا يقل عن :min أحرف.',
    ],
    'numeric' => 'حقل :attribute يجب أن يكون رقماً.',
    'password' => [
        'letters' => 'حقل :attribute يجب أن يحتوي على حرف واحد على الأقل.',
        'mixed' => 'حقل :attribute يجب أن يحتوي على حرف كبير وحرف صغير على الأقل.',
        'numbers' => 'حقل :attribute يجب أن يحتوي على رقم واحد على الأقل.',
        'symbols' => 'حقل :attribute يجب أن يحتوي على رمز واحد على الأقل.',
        'uncompromised' => 'كلمة المرور هذه ظهرت في تسريب بيانات، اختر كلمة مرور أخرى.',
    ],
    'required' => 'حقل :attribute مطلوب.',
    'string' => 'حقل :attribute يجب أن يكون نصاً.',
    'unique' => ':attribute مستخدم مسبقاً.',

    'attributes' => [
        'company' => 'اسم المنشأة',
        'sector_category_id' => 'التصنيف',
        'contact_name' => 'اسم المسؤول',
        'phone' => 'الجوال',
        'email' => 'البريد الإلكتروني',
        'password' => 'كلمة المرور',
        'cr_number' => 'السجل التجاري',
        'documents' => 'المستندات',
        'documents.*' => 'المستند',
        'plan_id' => 'الخطة',
        'cycle' => 'دورة الفوترة',
        'start_date' => 'تاريخ البدء',
        'discount' => 'الخصم',
        'reason' => 'السبب',
        'name' => 'الاسم',
        'title' => 'المسمى الوظيفي',
        'description' => 'الوصف',
        'locale' => 'اللغة',
        'role' => 'نوع الحساب',
        'branch_id' => 'الفرع',
        'client_role_id' => 'الدور',
        'admin_role_id' => 'الدور',
        'is_super_admin' => 'المدير العام',
        'active' => 'حالة الحساب',
        'permissions' => 'الصلاحيات',
        'permissions.*' => 'الصلاحية',
    ],
];
