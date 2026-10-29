# نموذج البيانات: الحالي ← Laravel/MySQL

> **مبدأ:** جداول مشتركة (Core) + جداول **`haccp_*`** + جداول **`iso_*`** منفصلة تماماً.
> الحقول المتغيرة (أعمدة النموذج، بيانات السجل) تُخزن **JSON**.
> 🌐 **كل عمود نصي يظهر للمستخدم = مترجم** (`spatie/laravel-translatable`: JSON `{"ar","en"}`). الأعمدة المكتوبة `name_ar, name_en` أدناه تتحول لعمود واحد `name` مترجم. التفاصيل في `06_I18N.md`.

## 1) Core (مشترك) — Sprint 1–2
| الجدول | أهم الأعمدة |
|---|---|
| `admins` | id, name, title, email, password, locale |
| `plans` | id, code (PLN-HACCP/PLN-ISO), name_ar, name_en, tier, price_monthly, price_annual, included_branches, extra_branch_monthly, extra_branch_annual, max_sites, max_users, active |
| `plan_modules` | plan_id, module_key |
| `sector_categories` | id, name_ar, … |
| `sectors` / `products` | مكتبة القطاعات والمنتجات |
| `clients` | id, code (CL-1001), company, cr_number, contact_name, email, phone, category_id, status (pending/active/suspended/rejected), registered_at |
| `client_sectors` | client_id, sector_id, source (category/granted/added) |
| `subscriptions` | client_id, plan_id, cycle, discount, start_date, end_date |
| `client_branches` | id, client_id, code (BR-01), name, city, address, manager, phone, active |
| `client_users` | id, client_id, branch_id (null للمدير), name, email (unique), role, password, locale (ar/en), active |
| `branch_products` | branch_id, product_id |
| `client_documents` | client_id, type, file_path, … |
| `client_requests` | client_id, branch_id, type (product/sector), payload JSON, status |
| `activity_logs` | client_id, actor, text, created_at |

## 2) HACCP — Sprint 3–9
| الجدول | الأعمدة | المصدر الحالي |
|---|---|---|
| `haccp_program_groups` | key, label, icon | `PRP_GROUPS` |
| `haccp_programs` | id, code (PRP-01), name, group_key, icon, summary, record_name, acceptance, limit_text, steps, evidence, failure, haccp_link, responsible, frequency, status | `safora.haccp.v1.procedures` |
| `haccp_program_customs` | client_id, program_id, fields JSON, status (draft/internally_reviewed), version | `client.prp_custom` |
| `haccp_program_revisions` | client_id, program_id, version, person, status, created_at | `client.prp_history` |
| `haccp_forms` | id, code (PRP-01-F01), eib_code, program_id, title_ar, title_en, freq, status (Active/Draft), layout_type, config JSON | `safora.haccp.v1.forms` |
| `haccp_records` | id, client_id, branch_id, form_code, data JSON, status (draft/submitted), created_by, timestamps | `client.haccp_entries` |
| `haccp_form_headers` | client_id, branch_id, form_code, header JSON | `client.formHeads` |
| `haccp_plan_templates` | id (poultry…), name, doc_no, version, scope, flow JSON, hazards JSON, ccps JSON, oprps JSON, product JSON | `5_HACCPPlans` + `LINK.plans` |
| `haccp_plan_refs` | key (method/decision/prp_matrix/verification), data JSON | ثوابت مشتركة |
| `haccp_control_points` | CCP/OPRP library | `safora.controlPoints.v2` |
| `haccp_critical_limits` | مكتبة الحدود | `efcos.criticalLimits.v1` |
| `haccp_trace_templates` | منتجات التتبع وتبويباتها | `LINK.trace` |
| `haccp_branch_links` | branch_id, type (plan/ctrl/limit/trace), ref_id | `branch.linked` |
| `haccp_seed_marks` | branch_id, form_code | `client.seeded` |

> أكواد سجلات الوحدات الحالية: `H|poultry|hazards` · `C|CL-0005|mon` · `L|…|read` · `T|…|tab` — في Laravel الأفضل `module`, `ref_id`, `section` كأعمدة منفصلة داخل `haccp_records`.

## 3) ISO — Sprint 10–12 (بعد انتهاء الهاسب)
| الجدول | الأعمدة | المصدر |
|---|---|---|
| `iso_procedures` | code (P-01…P-28), title, … | `PROCEDURES` / `1_Dashboard` |
| `iso_forms` | code (P-19-F01), procedure_id, title, freq, status, layout_type, config JSON | `safora.iso.forms.v1` |
| `iso_records` | client_id, branch_id, form_code, data JSON, status | `client.form_entries` |
| `iso_form_headers` | client_id, branch_id, form_code, header JSON | (نفس فكرة الهاسب) |
| `iso_pack_items` + `iso_pack_records` | السياسة، الوثائق، التدريب، الموردون، التدقيق، مراجعة الإدارة، NC/CAPA، الطوارئ | `ISO_PACK` (تجريبي) |

## 4) شكل JSON
**`haccp_forms.config` (eib_log):**
```json
{ "version":"01", "dateLabel":"تاريخ التنفيذ", "rows":30,
  "purpose":"...", "acceptance":"...", "note":"...",
  "columns":[ {"id":"c1","label":"رقم المصيدة","type":"text","required":true},
              {"id":"result","label":"النتيجة","type":"select","options":["مطابق","غير مطابق","لا ينطبق","قيد التحقق"],"required":true},
              {"id":"capa","label":"رقم CAPA","type":"text"} ] }
```
**`haccp_records.data`:** `{ "date":"2026-10-20", "c1":"...", "result":"fail", "capa":"" }` — تُحفظ **قيمة** الخيار وليس نصه.
> عناوين `columns[].label` و `options` تكون `{"ar","en"}` (المثال الكامل في `06_I18N.md`).

## 5) خدمة مشتركة (بدون جداول مشتركة)
`App\Services\Forms\LayoutConverter` = نسخة PHP من `layoutToFields()` → تُستخدم من `HaccpForm` و `IsoForm`.
`App\Services\Forms\FormPrinter` = الطباعة PDF (مثلاً `mpdf` لدعم العربية).
