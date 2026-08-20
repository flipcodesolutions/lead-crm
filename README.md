# Laravel 12 Odoo-Inspired Lead CRM

## 1. CRM Workflow

Lead → Lead Assignment → Follow-up / Activities → Qualification → Opportunity → Sales Pipeline → Quotation → Won / Lost

Roles:
- Admin
- Manager
- Telecaller
- Salesperson

---

## 2. Laravel Project Structure

```text
lead-crm/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── Admin/
│   │       │   ├── DashboardController.php
│   │       │   ├── UserController.php
│   │       │   ├── RoleController.php
│   │       │   ├── LeadSourceController.php
│   │       │   ├── LeadStatusController.php
│   │       │   ├── LeadStageController.php
│   │       │   └── ServiceController.php
│   │       ├── LeadController.php
│   │       ├── LeadAssignmentController.php
│   │       ├── FollowUpController.php
│   │       ├── ActivityController.php
│   │       ├── OpportunityController.php
│   │       ├── QuotationController.php
│   │       └── ReportController.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Lead.php
│   │   ├── LeadSource.php
│   │   ├── LeadStatus.php
│   │   ├── LeadStage.php
│   │   ├── LeadAssignment.php
│   │   ├── FollowUp.php
│   │   ├── Activity.php
│   │   ├── LeadNote.php
│   │   ├── Opportunity.php
│   │   ├── Service.php
│   │   ├── Quotation.php
│   │   └── QuotationItem.php
│   └── Services/
│       ├── LeadService.php
│       ├── OpportunityService.php
│       └── QuotationService.php
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   └── views/
│       ├── layouts/
│       ├── dashboard/
│       ├── leads/
│       ├── followups/
│       ├── opportunities/
│       ├── quotations/
│       ├── users/
│       ├── reports/
│       └── settings/
├── routes/
│   ├── web.php
│   └── auth.php
└── public/
```

---

## 3. Database Tables

### Authentication
- users
- roles

### Lead Master
- leads
- lead_sources
- lead_statuses
- lead_stages

### Lead Management
- lead_assignments
- follow_ups
- activities
- lead_notes

### Opportunity
- opportunities

### Sales
- services
- quotations
- quotation_items

### Communication
- emails
- notifications

### System
- settings

---

## 4. users

```text
id
role_id
name
email
phone
password
status
created_at
updated_at
```

## 5. roles

```text
id
name
description
created_at
updated_at
```

Default roles:
1. Admin
2. Manager
3. Telecaller
4. Sales

---

## 6. lead_sources

```text
id
name
status
created_at
updated_at
```

Examples:
- Website
- Facebook
- Google
- Referral
- Phone
- Walk-in
- IndiaMART
- Justdial
- Other

---

## 7. lead_statuses

```text
id
name
status
created_at
updated_at
```

Examples:
- New
- Contacted
- Interested
- Not Interested
- Qualified
- Unqualified
- Converted
- Lost

---

## 8. lead_stages

```text
id
name
sort_order
status
created_at
updated_at
```

Examples:
- New
- Contacted
- Qualification
- Proposal
- Negotiation
- Won
- Lost

---

## 9. leads

```text
id
lead_number
name
company_name
email
phone
alternate_phone
address
city
state
country
source_id
status_id
stage_id
assigned_to
created_by
expected_value
description
next_follow_up_at
converted_at
lost_reason
status
created_at
updated_at
```

---

## 10. lead_assignments

```text
id
lead_id
assigned_by
assigned_to
assigned_at
remarks
created_at
updated_at
```

This preserves assignment history.

---

## 11. follow_ups

```text
id
lead_id
user_id
type
follow_up_date
follow_up_time
subject
description
result
next_follow_up_date
next_follow_up_time
status
created_at
updated_at
```

Types:
- Call
- Meeting
- Email
- WhatsApp
- Other

---

## 12. activities

```text
id
lead_id
user_id
type
title
description
due_date
completed_at
status
created_at
updated_at
```

Examples:
- Call customer
- Send quotation
- Schedule meeting
- Send proposal
- Follow up

---

## 13. lead_notes

```text
id
lead_id
user_id
note
created_at
updated_at
```

---

## 14. opportunities

```text
id
lead_id
opportunity_number
name
customer_name
company_name
expected_revenue
probability
expected_closing_date
stage_id
assigned_to
description
status
won_at
lost_at
lost_reason
created_at
updated_at
```

---

## 15. services

```text
id
name
description
price
tax_percentage
status
created_at
updated_at
```

---

## 16. quotations

```text
id
quotation_number
opportunity_id
customer_name
customer_email
customer_phone
quotation_date
valid_until
subtotal
tax_amount
discount_amount
total_amount
notes
status
created_by
created_at
updated_at
```

Statuses:
- Draft
- Sent
- Accepted
- Rejected
- Expired

---

## 17. quotation_items

```text
id
quotation_id
service_id
description
quantity
price
discount
tax_percentage
total
created_at
updated_at
```

---

## 18. emails

```text
id
lead_id
user_id
to_email
subject
message
sent_at
status
created_at
updated_at
```

---

## 19. notifications

```text
id
user_id
title
message
type
read_at
created_at
updated_at
```

---

## 20. settings

```text
id
key
value
created_at
updated_at
```

---

# 21. Basic Lead Model

```php
class Lead extends Model
{
    protected $fillable = [
        'lead_number',
        'name',
        'company_name',
        'email',
        'phone',
        'source_id',
        'status_id',
        'stage_id',
        'assigned_to',
        'created_by',
        'expected_value',
        'description',
    ];

    public function source()
    {
        return $this->belongsTo(LeadSource::class, 'source_id');
    }

    public function status()
    {
        return $this->belongsTo(LeadStatus::class, 'status_id');
    }

    public function stage()
    {
        return $this->belongsTo(LeadStage::class, 'stage_id');
    }

    public function followUps()
    {
        return $this->hasMany(FollowUp::class);
    }

    public function notes()
    {
        return $this->hasMany(LeadNote::class);
    }

    public function assignments()
    {
        return $this->hasMany(LeadAssignment::class);
    }

    public function opportunity()
    {
        return $this->hasOne(Opportunity::class);
    }
}
```

---

# 22. Basic Lead Controller

```php
class LeadController extends Controller
{
    public function index()
    {
        $leads = Lead::with([
            'source',
            'status',
            'stage'
        ])->latest()->paginate(20);

        return view('leads.index', compact('leads'));
    }

    public function create()
    {
        $sources = LeadSource::where('status', 1)->get();
        $statuses = LeadStatus::where('status', 1)->get();
        $stages = LeadStage::where('status', 1)
            ->orderBy('sort_order')
            ->get();

        return view('leads.create', compact(
            'sources',
            'statuses',
            'stages'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'phone' => 'required',
            'email' => 'nullable|email',
        ]);

        Lead::create([
            'lead_number' => 'LD-' . time(),
            'name' => $request->name,
            'company_name' => $request->company_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'source_id' => $request->source_id,
            'status_id' => $request->status_id,
            'stage_id' => $request->stage_id,
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route('leads.index')
            ->with('success', 'Lead created successfully.');
    }
}
```

---

# 23. Routes

```php
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [
        DashboardController::class,
        'index'
    ])->name('dashboard');

    Route::resource('leads', LeadController::class);

    Route::resource('follow-ups', FollowUpController::class);

    Route::resource('opportunities', OpportunityController::class);

    Route::resource('quotations', QuotationController::class);
});
```

Admin routes:

```php
Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->group(function () {

        Route::resource('users', UserController::class);

        Route::resource(
            'lead-sources',
            LeadSourceController::class
        );

        Route::resource(
            'lead-statuses',
            LeadStatusController::class
        );

        Route::resource(
            'lead-stages',
            LeadStageController::class
        );

        Route::resource(
            'services',
            ServiceController::class
        );
    });
```

---

# 24. Lead Detail Page

Recommended sections:

1. Lead header
2. Customer information
3. Status and stage
4. Assignment
5. Timeline
6. Follow-ups
7. Activities
8. Notes
9. Opportunity
10. Quotation

Actions:

```text
Edit
Assign
Reassign
Add Follow-up
Add Note
Add Activity
Qualify
Convert to Opportunity
Create Quotation
Mark Won
Mark Lost
```

---

# 25. Dashboard

Display:

- Total Leads
- New Leads
- Qualified Leads
- Opportunities
- Won Deals
- Lost Deals
- Pipeline Value
- Today's Follow-ups
- Overdue Follow-ups
- Salesperson Performance
- Lead Source Performance
- Conversion Rate

---

# 26. Role-wise Modules

## Admin

- Dashboard
- Users
- Roles
- Lead Sources
- Lead Statuses
- Lead Stages
- Services
- All Leads
- Opportunities
- Quotations
- Reports
- Settings

## Manager

- Dashboard
- Leads
- Assign Leads
- Follow-ups
- Opportunities
- Quotations
- Team
- Reports

## Telecaller

- Dashboard
- My Leads
- My Follow-ups
- Activities
- Notes

## Salesperson

- Dashboard
- My Leads
- My Opportunities
- Follow-ups
- Quotations
- Customers

---

# 27. Development Phases

## Phase 1 — Authentication
- Users
- Roles
- Login
- Logout
- Dashboard
- Role access

## Phase 2 — Lead Management
- Lead sources
- Lead statuses
- Lead stages
- Create lead
- Edit lead
- Delete lead
- View lead
- Search
- Filter

## Phase 3 — Assignment
- Assign lead
- Reassign lead
- Assignment history
- My leads
- Team leads

## Phase 4 — Follow-ups
- Create follow-up
- Edit follow-up
- Complete follow-up
- Next follow-up
- Follow-up history
- Today's follow-ups
- Overdue follow-ups

## Phase 5 — Qualification
- Qualify lead
- Disqualify lead
- Convert lead
- Lost lead
- Lost reason

## Phase 6 — Opportunity
- Create opportunity
- Pipeline
- Stages
- Expected revenue
- Probability
- Expected closing date
- Won
- Lost

## Phase 7 — Quotation
- Services
- Quotations
- Quotation items
- Tax
- Discount
- PDF
- Email
- Accept / Reject

## Phase 8 — Reports
- Lead report
- Source report
- Salesperson report
- Conversion report
- Won / Lost report
- Revenue report
- Follow-up report

---

# 28. Simple Architecture

For fresher-friendly development:

```text
Route
  ↓
Controller
  ↓
Model
  ↓
Database
```

For complex operations:

```text
Route
  ↓
Controller
  ↓
  
Model
  ↓
Database
```

Avoid adding unnecessary repositories, DTOs, interfaces, actions, and complex patterns in the first version.

---

# 29. Core Relationship

```text
users
  │
  ├───────────────┐
  │               │
  ↓               ↓
leads        lead_assignments
  │
  ├── follow_ups
  ├── activities
  ├── lead_notes
  │
  ↓
opportunities
  │
  ↓
quotations
  │
  ↓
quotation_items
 ``

Master tables:

```text
lead_sources  ──→ leads
lead_statuses ──→ leads
lead_stages   ──→ leads
users         ──→ leads
```

---

## Recommended Stack

- Laravel 12
- PHP 8.4+
- MySQL 8
- Blade
- Bootstrap 5
- laravel sanctum for authentication and authorization
- Laravel Mail for emails
- DomPDF for quotation PDFs
- Laravel Telescope for debugging

The first version should focus on a clean MVC implementation and avoid unnecessary architectural complexity.
