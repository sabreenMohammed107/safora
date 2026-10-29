# SAFORA — دليل المشروع (النسخة الأولية HTML)

> النسخة الحالية **نموذج أولي يعمل في المتصفح** (HTML + Alpine.js + Tailwind CDN) والبيانات محفوظة في `localStorage`.
> هذه الملفات تشرح ما تم بناؤه وكيف ننقله إلى **Laravel + MySQL** على مراحل.
>
> 🌐 **متطلب أساسي: المشروع ثنائي اللغة (عربي + إنجليزي)** — الواجهة والبيانات والطباعة. التفاصيل في `06_I18N.md`.

## الملفات
| الملف | المحتوى |
|---|---|
| `01_ADMIN.md` | كل شاشات وخصائص لوحة الأدمن |
| `02_CLIENT_PORTAL.md` | كل شاشات وخصائص بوابة العميل |
| `03_BUSINESS_RULES.md` | القواعد: الخطط والأسعار، الفروع، الصلاحيات، حالات النماذج |
| `04_DATA_MODEL.md` | البيانات الحالية ← جداول Laravel المقترحة (HACCP و ISO منفصلين) |
| `05_SPRINTS.md` | خطة السبرنتات (الهاسب أولاً كاملاً ثم ISO) |
| `06_I18N.md` | اللغتان العربية والإنجليزية: الواجهة، البيانات، الطباعة، الترجمة المطلوبة |

## صفحات النموذج الأولي
| الصفحة | الدور | في القائمة؟ |
|---|---|---|
| `0_HACCPCenter.html` | مركز قيادة الهاسب (PRPs ونماذجها) | ✅ |
| `1_Dashboard.html` | مركز قيادة ISO 22000 (الإجراءات P-01…P-28) | ✅ |
| `2_FormBuilder.html` | استوديو النماذج (`?std=haccp` أو `?std=iso`) | ✅ |
| `5_HACCPPlans.html` | قوالب خطط الهاسب (13 قسم) | ✅ |
| `6_TraceabilityRecall.html` | التتبع والاستدعاء | ✅ |
| `9_CriticalLimits.html` | الحدود الحرجة والحرارة | ✅ |
| `10_CCPLibrary.html` | نقاط التحكم CCP / OPRP | ✅ |
| `7_SectorManagement.html` / `8_CoreSOPs.html` | الملفات الكاملة: القطاعات والإجراءات الأساسية | ✅ |
| `11_ClientCenter.html` | مركز العملاء (أدمن) + بوابة العميل | ✅ |
| `3_MonthlyGridLog.html` / `4_IncidentsCAPA.html` | السجل الشهري / البلاغات وCAPA | ❌ مخفية |

**التشغيل:** ضع كل الملفات في فولدر واحد وافتح `1_Dashboard.html`. بوابة العميل: `11_ClientCenter.html#portal`.

## حسابات التجربة (كلمة المرور `Safora@2026`)
| الدور | البريد |
|---|---|
| مدير الشركة | `ops@alreaya-hospital.example.com` |
| مسؤول فرع جدة | `jeddah@alreaya-hospital.example.com` |
| مستخدم فرع جدة | `qa.jeddah@alreaya-hospital.example.com` |

## مفاتيح التخزين الحالية (localStorage)
| المفتاح | المحتوى |
|---|---|
| `safora.clientCenter.v1` | الخطط، التصنيفات، العملاء (الفروع، المستخدمون، السجلات…) |
| `safora.haccp.v1` | برامج PRPs + نماذج الهاسب |
| `safora.iso.forms.v1` | نماذج ISO |
| `efcos.criticalLimits.v1` / `safora.controlPoints.v2` | مكتبة الحدود / نقاط التحكم |
| `safora.portalSession` / `safora.admin.profile` | جلسة العميل / ملف الأدمن |
