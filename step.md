# 🚀 Lead CRM — Client Demonstration Script & Walkthrough Guide

This document is your step-by-step presentation guide for demonstrating the **Odoo-Inspired Lead & Sales CRM** to stakeholders and clients. Follow this workflow to showcase the complete business value from initial lead capture to signed contract and revenue realization.

---

## 📋 Pre-Demo Setup & Access

### 1. Start the Local Server
Make sure the Laravel server is running in your terminal:
```bash
php artisan serve
```
Open your browser at: **[http://127.0.0.1:8000](http://127.0.0.1:8000)**

### 2. Available Demo Accounts
The login page includes **1-Click Demo Login Badges** to switch roles instantly:

| Role | Email | Password | Primary Purpose in Demo |
| :--- | :--- | :--- | :--- |
| **👑 Admin** | `admin@crm.com` | `password123` | Full visibility, Masters configuration & System branding |
| **👔 Manager** | `manager@crm.com` | `password123` | Team oversight, Lead assignment, Kanban & Executive Reports |
| **📞 Telecaller** | `telecaller@crm.com` | `password123` | Lead qualification, Discovery calls, Follow-up scheduling |
| **💼 Sales Rep** | `sales@crm.com` | `password123` | Opportunity management, Quotation generation, Closing deals |

---

## 🎯 Step-by-Step Client Presentation Flow

```
  [1. Executive Dashboard]
             │
  [2. Lead Intake & Capture]
             │
  [3. Lead Detail Hub & Follow-ups]
             │
  [4. Qualification & Opportunity Conversion]
             │
  [5. Sales Pipeline Drag-and-Drop Kanban]
             │
  [6. Quotation Engine & PDF Invoice]
             │
  [7. Deal Won & Status Synchronization]
             │
  [8. Executive Reports & CSV Export]
             │
  [9. Admin Masters & Branding]
```

---

### Step 1: Executive Dashboard (The Command Center)
**Goal:** Show the client how leadership gains immediate visibility over pipeline health and revenue.

1. **Log in as Admin** (`admin@crm.com`).
2. **Present the KPI Cards**:
   - **Total Leads & Qualified Leads**: Highlights top-of-funnel volume.
   - **Pipeline Value**: Total open opportunities in negotiation.
   - **Won Revenue**: Closed deals revenue.
   - **Conversion Rate**: Percentage of leads converted into won business.
   - **Today's & Overdue Follow-ups**: Ensures zero customer inquiries slip through the cracks.
3. **Interactive Charts**:
   - **6-Month Trend Graph**: Shows lead volume trajectory.
   - **Stage Distribution Chart**: Identifies pipeline bottlenecks.
4. **Urgent Follow-ups Widget**:
   - Point out the **"Today's Action Required"** queue.
   - Click the green **"Complete"** button on any follow-up to show how agents log call results in 5 seconds.
5. **Sales Leaderboard**:
   - Demonstrates individual rep performance (Assigned Leads, Won Deals, Closed Revenue).

> 💡 **Talking Point:** *"The dashboard gives sales leaders a 360-degree real-time view of business performance, while keeping frontline reps focused on their highest-priority calls for the day."*

---

### Step 2: Inbound Lead Intake & Management
**Goal:** Demonstrate how easy it is to capture leads and filter large datasets.

1. In the sidebar, click **"Leads"**.
2. Point out the **Search & Filter Bar**:
   - Filter by **Source** (Website, Google Ads, IndiaMART, Referral, etc.).
   - Filter by **Stage** or **Assigned Rep**.
   - Live keyword search across Name, Company, Email, and Phone.
3. Click the **"+ Create Lead"** button:
   - Fill in:
     - **Contact Name**: `Sarah Jenkins`
     - **Company**: `Apex Global Logistics`
     - **Phone**: `+1 (555) 789-0123`
     - **Email**: `sarah@apexgl.com`
     - **Source**: `Website`
     - **Expected Deal Value**: `$5,500`
     - **Description**: `Inquired about multi-branch CRM deployment with VoIP integration.`
4. Click **"Save Lead"**.
5. Show how the CRM automatically generates a unique identifier: **`LD-YYYYMMDD-XXXX`**.

> 💡 **Talking Point:** *"Every inquiry receives an audit-ready lead number and can be auto-assigned or manually routed to the best-suited specialist."*

---

### Step 2.5: Bulk Lead Import & Dynamic Column Mapping (Meta / Facebook Ads / IndiaMART)
**Goal:** Show how Admin and Managers can upload thousands of leads in seconds with custom column matching and zero data loss.

1. Click **"Import Leads"** in the sidebar (or click the **"Bulk Import"** button on the Leads page).
2. Showcase the **Step 1 Upload Wizard**:
   - Drag & drop any CSV file (e.g. Meta Ads, IndiaMART, Justdial, or Excel dump).
   - Configure **Assignment Rules**: Choose *Round-Robin* (auto-distribute equally across sales reps) or assign to a specific rep.
   - Configure **Duplicate Prevention**: Choose *Skip Duplicates* or *Update Existing Records*.
   - **Auto-Capture Custom Questions**: Toggle on *Auto-compile extra campaign questions into description* (e.g. `approximate_area`, `when_are_you_planning`, `inbox_url`).
3. Click **"Next: Map Columns"** $\to$ Showcase the **Interactive Mapping Screen**:
   - Show how the CRM automatically matches columns (`full_name` $\to$ Name, `phone` $\to$ Phone, `city` $\to$ City, `platform` $\to$ Source).
   - Show sample preview rows for verification.
   - Click **"Complete & Import Leads"**:
     - All leads are imported, formatted with Indian mobile numbers (`+91`), assigned to reps, and ready in the pipeline!

> 💡 **Talking Point:** *"Marketing teams frequently run lead ads across Meta, Instagram, and trade portals. With this dynamic mapping tool, they can import any campaign dump without altering the Excel file structure."*

---

### Step 3: Odoo-Style Lead Detail Hub
**Goal:** Showcase the modern master-detail interface where all communication, tasks, and history live in one place.

1. Open any Lead detail page (e.g., the newly created lead or pre-seeded **Zenith Logistics**).
2. Highlight the **Stage Breadcrumb Tracker** at the top:
   - Click on stages (`New` $\to$ `Contacted` $\to$ `Qualification` $\to$ `Proposal`) to show instant 1-click stage progression.
3. Demonstrate the **Action Toolbar**:
   - **"Assign / Reassign"**: Select a sales rep and add transfer remarks. Show the **Assignment History** tab to prove the audit trail.
   - **"Schedule Follow-up"**: Schedule a Call or Meeting with date/time picker.
   - **"Add Activity"**: Create task checklist items (e.g., *"Send technical spec"*).
   - **"Post Internal Note"**: Log internal chatter without sending external emails.
4. Show the **Tabbed Layout**:
   - **Overview**: Complete customer profile & financial details.
   - **Follow-ups Timeline**: Logged interactions and upcoming calls.
   - **Activities**: Pending checklist items with 1-click completion.
   - **Internal Notes**: Team discussion stream with timestamps.
   - **Quotations**: All commercial proposals linked to this customer.

> 💡 **Talking Point:** *"Your sales reps never have to switch between 5 different tools. Calling logs, notes, quotes, and task history are unified on one screen."*

---

### Step 4: Lead Qualification & Opportunity Conversion
**Goal:** Show the transition from an unqualified contact to an active sales deal.

1. On the Lead detail page, click the green **"Qualify Lead"** button.
   - Notice the status updates immediately to `Qualified`.
2. Click the **"Convert to Opportunity"** button:
   - A modal opens pre-filled with deal name, expected revenue, and estimated close date.
   - Set **Probability**: `75%`.
   - Click **"Convert to Opportunity"**.
3. Show that the lead is now officially an **Opportunity (`OPP-YYYYMMDD-XXXX`)** and the system redirects to the opportunity page.

> 💡 **Talking Point:** *"This creates a seamless handoff from marketing/telecalling teams directly into the active sales pipeline."*

---

### Step 5: Sales Pipeline Drag-and-Drop Kanban Board
**Goal:** WOW the client with the visual, interactive sales pipeline.

1. In the sidebar, click **"Opportunities"**.
2. Highlight the **Kanban Board**:
   - Each column represents a pipeline stage: **Qualification $\to$ Proposal $\to$ Negotiation $\to$ Won $\to$ Lost**.
   - Each column header shows **Deal Count** and **Total Expected Revenue**.
3. **Live Drag-and-Drop Interaction**:
   - Pick an opportunity card (e.g., *BioHealth Labs* or *Sterling Wealth*).
   - Drag it from **Proposal** to **Negotiation** (or **Negotiation** to **Won**).
   - Notice the AJAX background update, stage sum recalculation, and success notification.
4. Click the **List View toggle button** (top right) to show that users can also work in tabular data-grid mode if preferred.

> 💡 **Talking Point:** *"Sales teams love visual pipelines. Dragging deals across columns gives reps an intuitive tactile feel while keeping forecasted revenue accurate."*

---

### Step 6: Dynamic Quotation Builder & PDF Generation
**Goal:** Show how reps generate professional branded quotes with automatic tax/discount calculations and export to PDF.

1. On any Opportunity or from the sidebar, go to **"Quotations" $\to$ "+ Create Quotation"**.
2. Select Customer / Opportunity:
   - Pick `BioHealth Labs` or `TechCorp`.
3. **Dynamic Line Items**:
   - Click **"+ Add Item Line"**.
   - In the **Service / Product** dropdown, select `CRM Cloud Enterprise License (Annual)`.
   - Notice the **Unit Price ($1,200)** and **Tax Rate (18%)** auto-fill automatically!
   - Change Quantity to `2`.
   - Add another line: `Telephony & WhatsApp Integration Module`.
   - Apply an overall discount of `$150.00`.
   - **Show the Live Calculation**: Subtotal, Tax Amount, Discount, and Grand Total update instantaneously without reloading the page.
4. Click **"Save Quotation"**.
5. On the Quotation detail page:
   - Click **"Download PDF"**: Displays a clean, professional DomPDF quotation ready for client signing.
   - Click **"Print"**: Opens a printer-friendly layout.
   - Click **"Send via Email"**: Simulates email dispatch and logs the communication in the CRM.

> 💡 **Talking Point:** *"No more manual Excel errors. Product pricing, taxes, and multi-tier discounts are calculated in real time with 1-click PDF export."*

---

### Step 7: Closing the Deal (Won / Lost Lifecycle)
**Goal:** Show how accepting a quotation updates everything across the system.

1. On the Quotation detail page, click **"Mark as Accepted"**.
2. Notice:
   - Quotation status changes to **Accepted** (green badge).
   - The linked Opportunity automatically converts to **Won**!
   - The linked Lead is marked as **Converted / Won**.
   - The executive dashboard won revenue metrics increase automatically.
3. *(Alternative flow)*: If a client declines, show the **"Mark Lost"** button with a mandatory **Lost Reason** dropdown to support future sales win/loss analysis.

---

### Step 8: Follow-up Center & Call Summary Emailing
**Goal:** Show the daily workflow for telecallers and sales reps, including automatically emailing call discussion notes to the client.

1. Click **"Follow-ups"** in the sidebar.
2. Walk through the smart tabs:
   - **Today (Due Now)**: Calls scheduled for today.
   - **Overdue**: Past-due calls highlighted in red badges.
   - **Upcoming**: Scheduled future meetings.
   - **Completed**: Historical logs.
3. Click **"Complete"** on any follow-up call:
   - Enter call talk outcome: *"Discussed custom enterprise requirements with client, agreed on pricing package and onboarding timeline."*
   - Set next follow-up date (optional): *Next Monday at 10:00 AM*.
   - **Toggle On**: `[x] Send Talk Summary in Email to Client`.
   - Confirm/edit the client's email address.
   - Click **"Save & Complete"**:
     - The CRM marks the call completed.
     - Automatically schedules the next touchpoint.
     - **Immediately delivers a formatted Call Summary HTML Email** to the client detailing the discussion points and next steps!
4. Show the **"Email Summary"** button available on any completed call in both the Follow-ups list and the Lead detail timeline to resend or forward conversation notes anytime.

> 💡 **Talking Point:** *"Clients appreciate transparency. Sending an instant post-call summary email keeps both the customer and the sales team aligned on what was agreed over the phone."*

---

### Step 9: Analytics, Conversion Reports & CSV Export
**Goal:** Show how management measures ROI and export reports.

1. In the sidebar, click **"Reports"**.
2. Showcase the **Date Range Filter**:
   - Filter by custom date ranges (This Month, Last Quarter, Year-to-Date).
3. Review:
   - **Lead Sources Breakdown**: Percentage share of leads acquired per marketing channel (Website, Google Ads, IndiaMART, Referrals).
   - **Pipeline Funnel Distribution**: Conversion velocity through stages.
   - **Sales Representative Leaderboard**: Assigned leads, opportunities created, won deals, and won revenue.
4. Click **"Export Leads CSV"** to demonstrate instant download for Excel/PowerBI reporting.

---

### Step 10: Admin Control & System Customization
**Goal:** Show how easily the system can be customized for any business or industry.

1. In the sidebar under **Administration**:
   - **User Management**: Add new team members, assign roles, activate/deactivate accounts.
   - **Role Management**: Configure Admin, Manager, Telecaller, and Salesperson roles.
   - **Lead Sources Master**: Add custom lead sources (e.g., *Trade Show*, *Instagram*).
   - **Pipeline Stages Master**: Reorder or add custom sales stages.
   - **Product & Service Catalog**: Manage item names, base prices, and default GST/VAT percentages.
   - **CRM Branding Settings**: Customize Company Name, Address, Email, Phone, Currency Symbol (`$`, `₹`, `€`, `£`), and Default Terms & Conditions.

---

## 💡 Quick Q&A Cheat Sheet for the Client

| Client Question | Recommended Answer |
| :--- | :--- |
| **"Can we customize the currency to our country?"** | *"Yes! In Admin $\to$ Settings, you can set the currency symbol to $, ₹, €, £, or any symbol, and it reflects everywhere."* |
| **"Can our telecallers only see their own assigned leads?"** | *"Yes. Role-based scoping is built-in. Telecallers and Sales Reps only see leads assigned to them, while Managers and Admins have full organization visibility."* |
| **"Can we change the stages in the sales pipeline?"** | *"Yes! You can add, edit, and reorder stages in Admin $\to$ Pipeline Stages, and the Kanban board updates dynamically."* |
| **"Can we export our customer database?"** | *"Yes. There is a 1-click CSV Export button in the Reports section for all lead and deal data."* |
| **"Is the system mobile friendly?"** | *"Yes, the layout uses responsive Bootstrap 5 with responsive navigation and modal forms for on-the-go sales reps."* |

---

## 🏁 Demo Wrap-Up Checklist

- [x] Executive Dashboard KPIs & Visual Charts
- [x] Lead Capture with Automated Numbering (`LD-XXXX`)
- [x] Odoo-Style Lead Hub with Breadcrumbs & Action Bar
- [x] Follow-up & Activity Scheduling with Auto-Reschedule
- [x] Lead Qualification & Opportunity Conversion (`OPP-XXXX`)
- [x] Sales Pipeline Drag-and-Drop Kanban Board
- [x] Reactive Quotation Builder with Service Auto-fill & Taxes
- [x] Branded DomPDF Invoice Download & Print View
- [x] Deal Won / Lost Lifecycle & Status Sync
- [x] Analytics, Sales Leaderboard & CSV Export
- [x] Admin Master Data & Branding Customization
