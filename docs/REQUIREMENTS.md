# Minori-kun EC Marketplace — Product and System Requirements

## 1. Document Control

| Field | Value |
|---|---|
| Project | `みのりくん` (Minori-kun) agricultural and food EC marketplace |
| Document version | 0.3.57 |
| Status | Latest workbook-aligned implementation baseline |
| Last updated | 2026-09-18 (Asia/Tokyo) |
| Target launch | First week of November 2026 |
| Development completion target | 2026-10-16 |
| Development estimate | 280 hours / 35 working days |
| Estimate exclusions | Integration testing, bug fixing, evidence preparation, user manuals, and production-release preparation |
| Customer UI | Japanese, JPY, Asia/Tokyo |
| Narrative language | English; exact Japanese UI wording is retained where specified |

### 1.1 Authority and source

This baseline was rebuilt from `C:\EC\docs\みのりくん_EC画面要件定義書.xlsx` after reading all 96 sheets. The workbook was treated as read-only and, after relocation, verified with SHA-256 `aa6f6813288f3306ead838276ec3476056dc701e1ae84049afe84bcd1369b3b3`. The user's later explicit decisions override conflicting workbook copy only where this document calls out that refinement.

### 1.2 Change history

| Version | Date | Summary |
|---|---|---|
| 0.1.0 | 2026-08-09 | Initial consolidated requirements. |
| 0.2.0 | 2026-08-10 | Earlier workbook-aligned baseline. |
| 0.3.0 | 2026-08-28 | Rebased to the current 47-screen workbook, PAY.JP Platform Marketplace API v1, the 15-screen Producer flow, the 11-screen Admin flow, and the current delivery estimate. Obsolete payment, internal Producer-approval, standalone bank-registration, payout-batch, and superseded screen structures were retired. |
| 0.3.1 | 2026-09-02 | Applied the later confirmed P07 selling-information decision: the Producer explicitly selects `単一商品` or `種類あり`, every product is represented by at least one sellable option, and price, stock, and Producer-funded discount are authoritative per sellable option. The discount remains separate from the Company's fixed 10% commission. |
| 0.3.2 | 2026-09-07 | User-confirmed Producer email-first registration: six-digit email OTP, restricted verified-registration session, automatic transition to registration details, and account creation only after details and terms acceptance. Supersedes the workbook's initial-registration link flow only; Buyer verification, P12 email changes, and password reset are unchanged. Figma review variants remain separate pending visual approval; Excel is unchanged. |
| 0.3.3 | 2026-09-08 | Applied the user-approved Producer registration visual refinement: a 35/65 desktop shell, retained three-step progress, clearer shop/contact labels, a required horizontal circular-photo uploader, compact verified-email row, and consistent required-field indicators. Business flow and validation policies are unchanged. |
| 0.3.4 | 2026-09-09 | Confirmed the Producer password policy and exact Japanese guidance, and simplified the approved P02-2 empty-state copy while preserving the registration flow, required fields, and actions. |
| 0.3.5 | 2026-09-09 | Clarified that required fields use a trailing muted-red asterisk and removed the redundant explanatory asterisk legend from P02-2. |
| 0.3.6 | 2026-09-09 | Moved the verified Producer email from the P02-2 header into the Producer-information form, directly below the profile-photo uploader, using the normal field rhythm with a disabled verified treatment. |
| 0.3.7 | 2026-09-09 | Consolidated the P02-2 shop-profile-photo empty state into one circular picker with an integrated camera affordance, removing the separate upload button while preserving the required-photo behavior. |
| 0.3.8 | 2026-09-09 | Removed the campaign banner from the approved B01 Buyer Home / Product List UI so category filters and products begin directly below the header; the disposition of the now-unconsumed A06 banner-management capability remains TBD-004. |
| 0.3.9 | 2026-09-09 | User-confirmed Buyer email-first registration: B14-1 collects email only, B15 verifies a six-digit OTP before account creation, and B14-2 collects registration details under a restricted verified-registration session. Initial-registration OTP success transitions directly to details; B18 email-change verification remains a separate link-based flow. The first approved empty-state frame is B14-1. |
| 0.3.10 | 2026-09-09 | User-confirmed a three-minute expiry for each Buyer initial-registration OTP issued through B15. Other Buyer OTP limits and all unresolved Producer OTP/session numeric policies remain TBD-001. |
| 0.3.11 | 2026-09-10 | User-confirmed removal of customer-facing product variations/options. P07 now captures one product-level price, stock quantity, and optional Producer-funded discount without `単一商品` / `種類あり` controls. Each product retains exactly one internal default sellable option so pricing, stock, discount, cart, and order snapshots remain authoritative without exposing a variation selector. |
| 0.3.12 | 2026-09-10 | User-confirmed that Producers may manually change fulfillment status forward or backward among `受付` (Received), `対応中` (Processing), and `発送済み` (Shipped). P09 uses a dropdown plus explicit confirmation rather than a one-way next-state action. Cancellation and refund remain system-controlled. |
| 0.3.13 | 2026-09-10 | User-confirmed responsive P10 payout navigation. Desktop retains the same-page master-detail interaction. Mobile presents a compact payout overview/history list and opens the selected payout in a dedicated detail subview within the same logical P10 feature, with explicit Back navigation. This subview is not a new standalone inventory screen. |
| 0.3.14 | 2026-09-10 | User-approved P12 mobile account-information form includes the existing shop profile photo as an editable profile field alongside the Producer/farm name, contact name, phone, and login-email change flow. Photo upload remains subject to TBD-003; no format, size, or crop limits are invented. |
| 0.3.15 | 2026-09-11 | User-confirmed post-registration shop-profile-photo management moves from P12 to the P11 Account Settings hub. The circular avatar with an integrated camera affordance is the only picker control; after local selection, P11 shows an explicit confirmation modal and uploads/saves only after confirmation. P02 initial registration photo remains required. |
| 0.3.16 | 2026-09-11 | User-confirmed the B05 mobile purchase-action layout: quantity remains in scrollable product content, while a separate white sticky action tray sits immediately above the bottom navigation with outlined `今すぐ購入` on the left and a wider filled `カートに追加` on the right. The tray is not part of the bottom navigation, and implementation must reserve sufficient content-bottom space so it does not obscure product content. |
| 0.3.17 | 2026-09-11 | Refined the confirmed B05 mobile sticky purchase tray so `今すぐ購入` and `カートに追加` use equal widths and heights. Visual priority remains communicated by outlined secondary versus filled primary styling rather than unequal geometry. |
| 0.3.18 | 2026-09-11 | User-confirmed the B06 mobile multi-Producer cart presentation: keep a flat product-card list, identify each line with `販売元：{ショップ名}`, and do not wrap lines in bulky seller-group cards. Each line has an inline decrement/value/increment quantity stepper and no customer-facing variation text. Producer grouping remains authoritative for payment and sub-order processing. |
| 0.3.19 | 2026-09-11 | Refined the confirmed B06 seller identity treatment: replace the formal `販売元：` prefix with a compact storefront icon followed directly by the public shop name on each cart line. The flat-list, quantity-stepper, and underlying Producer-grouping rules remain unchanged. |
| 0.3.20 | 2026-09-11 | Clarified B08 as `お届け先情報の変更` (Change Delivery Information): the checkout delivery entity includes recipient name, delivery phone, postal code, prefecture, municipality, and street/building details. The primary action uses the current-order-specific label `このお届け先を使用`; the optional `基本のお届け先として保存する` checkbox also updates the Buyer profile default delivery information. |
| 0.3.21 | 2026-09-11 | User-confirmed that the current A03 design is the approved Buyer Management scope. Renamed A03 from Buyer / Inquiry Management to `購入者管理`, removed inquiry handling from the Admin Portal and A03 requirements, and retained the current read-only Buyer list/detail pattern. The operational destination for B20/B21 support inquiries is now explicitly unresolved under TBD-005 rather than being silently assigned to A03. |
| 0.3.22 | 2026-09-14 | User-confirmed removal of the Admin-facing category `表示順` (Display Order) column and create/edit field from A05 Category Management. A05 retains category list, create/edit, product-count, status, enable/disable, and safe result handling; no manual category-order control is exposed in A05. Product-image ordering in P07 and automatic banner ordering in A06 are unchanged. |
| 0.3.23 | 2026-09-14 | User-confirmed replacement of A05 category enable/disable management with separate inline edit and permanent-delete actions. Each category row exposes pencil and trash icon buttons. Delete is allowed only when no product references the category: an in-use category opens a blocking alert with the affected product count and `商品を確認`, while an unused category opens an irreversible-action confirmation before deletion. Create/edit drawers contain category-name editing only; A05 lifecycle-status controls are removed. |
| 0.3.24 | 2026-09-14 | User-confirmed replacement of the A05 category edit drawer with a compact centered input dialog because editing changes only the category name. The dialog contains the category-name input, Cancel, and Save. Category creation remains in the existing create drawer, and permanent deletion remains a separate row action and dialog flow. |
| 0.3.25 | 2026-09-14 | User-confirmed replacement of the remaining A05 category-create drawer with the same compact centered input-dialog pattern used for editing. Create now contains the category-name input, Cancel, and Add; permanent deletion remains a separate row action and dialog flow. |
| 0.3.26 | 2026-09-14 | User-confirmed replacement of the A07 default split-pane product list/detail presentation with a conventional full-width product table. The default state shows search and key filters above a table with product ID, product, Producer, category, price, stock, publication state, promotion, and compact row actions; it does not reserve a persistent right-side detail panel. |
| 0.3.27 | 2026-09-15 | User-confirmed simplified A11 Log Monitoring: keyword search, one date-range filter, and four columns (time, description, actor, result). Row selection opens a compact read-only detail dialog. Main-view severity/type/result filters and row-action icons are removed. |
| 0.3.28 | 2026-09-15 | User-confirmed shipping-inclusive Producer product pricing. P07 mobile Create labels the price as tax/shipping inclusive and places persistent guidance plus a worked example below the input. The product discount applies to the entire entered price, including the embedded shipping amount. |
| 0.3.29 | 2026-09-15 | User-confirmed P04 mobile simplification: remove the standalone link-error design state and change the declined-state action to 再申請する (Reapply). Link failures remain recoverable within the existing application screen. |
| 0.3.30 | 2026-09-15 | User-confirmed P13 mobile protected bank edit opens with existing account values prefilled instead of empty placeholders. Figma uses synthetic sample values consistent with the masked overview. |
| 0.3.31 | 2026-09-15 | Applied the approved shipping-inclusive price label, helper, and example to P07 mobile Edit as well as Create, preserving existing product values. |
| 0.3.32 | 2026-09-15 | User-confirmed compact P04 mobile In Review card: remove the hidden-action gap and redundant status line, place the last-update time 16px after the explanation, and size the card to its content. No manual refresh action is added. |
| 0.3.33 | 2026-09-15 | Applied the compact In Review presentation to desktop P04: retain the upper-right status badge, remove the duplicate status row and unused refresh layers, shorten the card, and clarify automatic updates and selling availability. |
| 0.3.34 | 2026-09-15 | User-requested informational payment-brand logos in desktop/mobile P04 In Review: Visa, Mastercard, JCB, American Express, Diners Club, Discover, and Apple Pay. Label them as PAY.JP-supported methods with an availability qualifier; logos do not represent the Tenant's approval or change the confirmed eligibility gate. |
| 0.3.35 | 2026-09-15 | Added the user-requested Buyer cancellation note to both P08 mobile table-scroll states, below filters and above the order list. It communicates the existing BR-010 30-minute window without adding a fulfillment hold. |
| 0.3.36 | 2026-09-15 | Added a B12 cancellation-completed design state beside the existing confirmation screen. It separates cancelled order status from refund processing, preserves purchase snapshots, removes the cancellation deadline/action, and offers return to order history. |
| 0.3.37 | 2026-09-15 | Added user-requested Other (`その他`) card to B02 Buyer Category, in the lower-right position of the existing two-column grid. |
| 0.3.38 | 2026-09-16 | User confirmed that a calendar-year filter is required on both Buyer Purchase History (B11) and Producer Order List (P08). Producer P08 mobile is updated first; Buyer B11 follows in the Buyer phase. |
| 0.3.39 | 2026-09-16 | User approved the mobile order-period filter pattern for both B11 and P08: one filter sheet provides All time, last 30 days, last 90 days, last 12 months, calendar-year selection, and Custom date range. Start and End date fields appear only after Custom date range is selected. Producer P08 visual states are updated first; no prototype wiring is required. |
| 0.3.40 | 2026-09-17 | User-confirmed Buyer-to-Producer inquiry entry in B12 Order Detail. Replace the generic bottom inquiry link with a full-width secondary button directly below the purchased-lines card, labelled `この注文について生産者に問い合わせる`, and carry the order and Producer context into B20. |
| 0.3.41 | 2026-09-17 | User-requested industry-standard refinement of B12 Producer contact: replace the detached outlined button with a compact, divider-separated navigation row inside the purchased-lines card. Use the concise label `生産者に問い合わせる` and a trailing chevron; retain the order/Producer context and B20 destination. |
| 0.3.42 | 2026-09-17 | User-confirmed the B20 order-linked Producer-inquiry form variant. Show the selected Producer and owned order as read-only context, then collect only an inquiry topic and message, display a warning not to enter card/payment data, and provide `生産者に送信する`. This is a visual form state only: no prototype wiring, chat, inbox, attachment flow, or response-channel behavior is added. |
| 0.3.43 | 2026-09-17 | Refined B20 from marketplace research: present a compact order summary with product thumbnail, product, Producer, and order number; replace the preselected dropdown with four unselected radio-style issue choices; keep one message textarea; demote the payment-data warning to inline helper text; and use the concise primary action `問い合わせを送信`. Prototype wiring remains excluded. |
| 0.3.44 | 2026-09-17 | Added a B11 multi-product order-history presentation. A Producer order card may list multiple different purchased products from the same Producer, showing each product thumbnail, name, quantity, and line amount before the order total. The existing single-product presentation remains valid for orders containing one product. |
| 0.3.45 | 2026-09-17 | Standardized the B11 order-history cards around a compact product-preview pattern: every order shows product imagery, name, and quantity; multi-product orders show up to two product rows followed by `ほかN点` when more products remain. B11 shows only the order total, while B12 remains the complete purchased-product record. |
| 0.3.46 | 2026-09-17 | Added the B12 multi-product Order Detail visual state corresponding to the B11 sample order. It shows every purchased-product snapshot with thumbnail, quantity, and purchase-time price or discount, while retaining separate order/payment/refund states, the order total, Producer inquiry row, delivery snapshot, and cancellation eligibility. No business behavior or screen inventory changed. |
| 0.3.47 | 2026-09-17 | Removed the redundant normal-state `支払：完了` badge from B12 Order Detail. Because an order is created and exposed only after authoritative payment success, the normal paid state is implicit; B12 continues to show the operational order state and any refund state. Payment remains an independent backend state and no unverified browser return may create an order. |
| 0.3.48 | 2026-09-17 | Updated the B20 order-linked Producer inquiry summary for multi-product orders. The compact read-only context now previews up to two purchased products from the selected Producer order with thumbnail, name, and quantity, followed by the Producer and owned order number; additional products use `ほかN点`. The inquiry fields and no-prototype scope are unchanged. |
| 0.3.49 | 2026-09-17 | Refined the B20 multi-product order summary so each thumbnail and its matching product name/quantity are grouped in one distinct horizontal row. Producer and order-number metadata now sits below the product rows, eliminating ambiguous image-to-title association without changing form behavior. |
| 0.3.50 | 2026-09-18 | Added the user-confirmed mobile P02-2 note that the registered shop profile photo is displayed to Buyers as the shop image in product-list experiences. Upload policy remains governed by TBD-003. |
| 0.3.51 | 2026-09-18 | Confirmed stacked vertical mobile presentations for P06 Product List and P08 Order List. Mobile must not require horizontal table swiping; the previously documented horizontal-table wording is superseded. |
| 0.3.52 | 2026-09-18 | Replaced the single shipping-inclusive Producer price-entry rule with separate tax-inclusive product price and three manually entered regional delivery fees for Honshu/Shikoku/Kyushu, Hokkaido, and Okinawa. Create and Edit use the same fields; package size, weight, and carrier inputs are excluded. |
| 0.3.53 | 2026-09-18 | Confirmed seller-by-seller cart checkout. B06 groups lines by shop, shows each shop's item count and subtotal, and provides a checkout action for that shop only. B07–B10 and payment creation operate on the selected Producer group; other shops remain in the cart. |
| 0.3.54 | 2026-09-18 | Added the system-controlled `注文確定` (Order Confirmed) presentation in P08 after the 30-minute Buyer cancellation window expires. It is not a Producer-selectable fulfillment value; P09 continues to manage fulfillment separately. |
| 0.3.55 | 2026-09-18 | Added a completed-order PDF receipt download to B12. The receipt is generated for the selected order only and the approved presentation shows the total as tax included without a separate tax-breakdown line; statutory invoice/receipt scope remains TBD-007. |
| 0.3.56 | 2026-09-18 | Simplified the general BVI-contact variant of B20 to two required inputs only: subject and inquiry content. Authenticated identity is derived server-side; name, email, and order-reference fields are not shown in this variant. |
| 0.3.57 | 2026-09-18 | Added an idempotent Buyer order-accepted email dispatched only after authoritative PAY.JP payment success and order creation. The email contains safe order-identification and next-step information without card credentials or internal settlement data. |

## 2. Requirement Conventions

- `[CONFIRMED]` identifies a binding requirement sourced from the approved workbook or a later explicit user decision.
- `[PROPOSED]` identifies a reversible implementation default that is not a business approval.
- `[TBD]` identifies an unresolved decision and must not be implemented as confirmed behavior.
- `[OUT-OF-SCOPE]` identifies behavior intentionally excluded from the current release.
- Requirement prefixes are `BR`, `FR-B/P/A`, `SCR-B/P/A`, `INT`, `API`, `DATA`, `SEC`, `NFR`, `AT`, and `TBD`.
- Order, Producer sub-order, payment, refund, fulfillment, screening, product, and payout states are separate state machines.

## 3. System Overview

| No. | Topic | Requirement | System handling | Note |
|---:|---|---|---|---|
| 1 | Service model | Company-operated marketplace where Producers sell agricultural and food products to Buyers online. | Buyer, Producer, and Admin areas are provided. | — |
| 2 | Seller and payment model | Each Producer is the seller and a PAY.JP Tenant. The Company operates the platform, while PAY.JP manages Producer sales allocation, the Company fee, and payouts. | Selling eligibility is based on PAY.JP screening; there is no internal Company approval. | — |
| 3 | Buyer purchase | Buyers may keep products from multiple Producers in one cart, then check out one Producer group at a time using PAY.JP. | Each checkout processes one Producer payment and creates one Buyer-visible order for that selected Producer; other Producer groups remain in the cart. | — |
| 4 | Platform operations | Admin oversees Buyer accounts, Producer status, categories, banners, products, orders/refunds, sales and Company commission summaries, PAY.JP payout status, and system/security logs. | PAY.JP makes screening decisions and pays Producers directly; Admin monitors status and handles internal operations. Buyer inquiry handling is not part of the approved Admin Portal scope. | — |

## 4. Actors and Permissions

| Actor | Main role | Data visible | Constraints |
|---|---|---|---|
| Buyer | Search products, manage cart, complete payment, and view or update own orders/profile. | Public catalog and own account/order data. | — |
| Producer | Manage own products, orders, delivery, sales, settlements, and payouts. | Own products, order lines, sales, settlements, and payouts. | — |
| Admin | Oversee Buyer accounts, Producer operations, categories, banners, products, orders/refunds, sales and Company commission, PAY.JP payout status, and system/security logs. | Authorized platform information, Company commission and payout status, and system/security logs. | Buyer inquiry handling is outside the approved Admin Portal scope. Never display card data; bank information is masked. |

- **BR-001 `[CONFIRMED]`** The service is a Company-operated marketplace in which Producers sell agricultural and food products to Buyers.
- **BR-002 `[CONFIRMED]`** Buyer, Producer, and Admin identities and authorization scopes are separate.
- **BR-003 `[CONFIRMED]`** Every Producer query and mutation must enforce ownership server-side.
- **BR-004 `[CONFIRMED]`** Buyers must never see internal Producer settlement, payout, or Company-commission information.
- **BR-005 `[CONFIRMED]`** Admin uses one accountable Admin role for the current release; there is no Admin Account Settings or Admin Password Reset screen.

## 5. Marketplace Business Rules

- **BR-006 `[CONFIRMED]` — Company commission:** For each Producer payment in PAY.JP, the system automatically separates Producer sales and the Company's 10% commission. Owner: System / PAY.JP.
- **BR-007 `[CONFIRMED]` — Screening and selling eligibility:** After Producer registration, the system issues a one-time PAY.JP screening form URL. Product registration and sales begin only after PAY.JP approves both Visa and Mastercard. Owner: Producer / System / PAY.JP. Note: No internal Company approval. Product management and selling remain unavailable before eligibility.
- **BR-008 `[CONFIRMED]` — Delivery and order status:** Each Producer ships their own products and manually updates fulfillment status from the Producer Portal. Producers may change fulfillment forward or backward among Received, Processing, and Shipped. Order receipt, the automatic `注文確定` transition after the 30-minute cancellation window, cancellation, and refund remain system-controlled and separate from Producer fulfillment. Owner: Producer / System.
- **BR-009 `[CONFIRMED]` — Settlement and payout:** PAY.JP applies fees/adjustments to Producer sales and pays each Producer directly. Use month-end close, payout at the end of the following month, and a ¥1,000 minimum. Owner: PAY.JP / System. Note: Carry amounts below the minimum forward. Producer transfer fee is ¥250 including tax.
- **BR-010 `[CONFIRMED]` — Order cancellation and refund:** A Buyer may cancel an order only within 30 minutes after completion. After confirmation, refund the order's Producer PAY.JP payment exactly once; after the deadline, the system automatically marks the order `注文確定` and guides later issues to Contact. Owner: Buyer / System / PAY.JP. Note: Prevent duplicate refunds and keep refund/settlement effects auditable.
- **BR-011 `[CONFIRMED]`** Each Producer is the seller and a PAY.JP Tenant. The Company operates the platform.
- **BR-012 `[CONFIRMED]`** One cart may contain multiple Producers, but checkout, payment, and Buyer order creation occur for one selected Producer group at a time. A Buyer order never combines different Producers; it may contain multiple products from the same Producer.
- **BR-013 `[CONFIRMED]`** PAY.JP screening—not an internal Company approval—determines selling eligibility. Both Visa and Mastercard must be `passed` before selling functions become available.
- **BR-014 `[CONFIRMED]`** The Company's 10% commission and any Producer-funded customer promotion are separate calculations and labels.
- **BR-015 `[CONFIRMED]`** A payment or hosted-form browser return is never authoritative proof of success; authoritative PAY.JP API or webhook information controls state.
- **BR-016 `[CONFIRMED]`** Product edits never change purchase-time OrderItem, Producer, price, promotion, or delivery snapshots.
- **BR-017 `[CONFIRMED]`** Admin may monitor PAY.JP screening and support application issues but cannot create Tenants manually or override card-brand results.
- **BR-018 `[CONFIRMED]`** Sensitive administrative and financial actions require a safe audit/log event without raw card, credential, or bank values.
- **BR-019 `[CONFIRMED]` — Regional delivery fee:** The Producer enters one non-negative JPY delivery fee for each of three zones: Honshu/Shikoku/Kyushu, Hokkaido, and Okinawa. The server selects the fee from the order's delivery prefecture and calculates the Buyer-facing tax-and-shipping-inclusive amount without a separate Buyer shipping-fee line. The previously confirmed Producer-funded percentage discount applies to the complete product amount including the applicable regional delivery fee; it remains separate from the Company's fixed 10% commission. No carrier API, package-size, weight, or carrier-selection input is required for the current release. Pre-address price presentation remains governed by TBD-006, and multi-quantity/multi-product aggregation remains governed by TBD-009.
- **BR-020 `[CONFIRMED]` — Order-accepted email:** After authoritative payment success and committed order creation, send the Buyer one order-accepted email for that order. Dispatch only after commit, deduplicate retries/events, and include no card credentials or internal Producer settlement/commission data. Exact subject and body copy remain governed by TBD-010.

## 6. End-to-End Flows

### 6.1 Buyer purchase

Product List → Product Detail → Cart → Select one Producer group → Order Confirmation → PAY.JP payment for that Producer → Server Verification → Order Complete

**Owner:** Buyer / System / PAY.JP. **Note:** Present one order to the Buyer.

#### 6.1.1 Buyer registration

Email Entry (B14-1) → Six-digit Email OTP (B15) → Verified Registration Details and Terms (B14-2) → Account Creation → Intended Destination or Home

**Owner:** Buyer / System. **Note:** No Buyer account exists before final submission. Initial-registration OTP verification and authenticated B18 email-change verification are distinct flows.

### 6.2 Producer operation

Email Entry (P02-1) → Email OTP (P03) → Verified Registration Details and Terms (P02-2) → Account Creation → PAY.JP Tenant Creation → Hosted Application → PAY.JP Screening → Visa and Mastercard Passed → Product, Order, Delivery, and Payout Management

**Owner:** Producer / System / PAY.JP. **Note:** Generate the application URL on action and reissue it from the same screen when expired or correction is required.

### 6.3 Admin activities

• Support Buyer inquiries and Producer/PAY.JP screening operations
• Manage categories and the Home recommendation banner
• Monitor products, orders, refunds, and chargebacks
• Review sales, Company commission, and PAY.JP payout status
• Monitor failed logins, unauthorized access attempts, payment callback anomalies, and system errors

**Owner:** Admin. **Note:** PAY.JP makes screening decisions and pays Producers; Admin monitors status. Logs are read-only and exclude sensitive values.

The Producer onboarding sequence follows section 6.2. P02-1 and P02-2 are variants of P02, not additional logical screens; the 47-screen inventory is retained. Initial bank information is collected by the PAY.JP-hosted application, not the local registration screen.

### 6.4 Producer registration access and transition contract

- **[CONFIRMED; FR-P-002, FR-P-003, SEC-013]** Email entry creates a temporary registration attempt, not a Producer account. OTP verification proves access to that mailbox only, not identity, selling eligibility, or PAY.JP approval.
- Successful server verification rotates the restricted registration session and permits only registration details and completion. Navigate directly to P02-2 in the same tab without another email link, success-button click, or artificial timer. Show persistent `メールアドレスを確認しました` (Email address verified) feedback and the read-only verified email on P02-2. A transient verification/loading state must prevent duplicate submissions and must not claim success before the server accepts the OTP.
- With a valid verified session, refresh resumes P02-2. An unverified pending attempt opening P02-2 returns to P03; no valid attempt returns to P02-1. An expired verified session explains the expiry and requires re-verification; retain only safe profile input transiently where possible, never persist passwords. Changing email invalidates the previous verification and requires a new OTP before continuing.
- Server checks protect details data endpoints and final registration submission. A Vue route guard is navigation assistance only; loading a static SPA page is not authorization. No temporary registration session may access Producer operational APIs.
- Final submission validates all details and consent, creates the account using the server-verified email, records terms version/time, and consumes the registration grant atomically. Duplicate/replayed/concurrent submissions must not create multiple accounts. Only then establish a fresh authenticated Producer session and open P04. PAY.JP provisioning remains idempotent and selling remains gated by BR-007/BR-013.
- Numeric OTP/session expiry and rate-limit policies remain TBD-001. Required error/recovery states are implementation requirements even where their Figma variants are deferred; the current design batch covers the normal sequential flow only.

### 6.5 Buyer registration access and transition contract

- **[CONFIRMED; FR-B-014, FR-B-015, SEC-014]** B14-1 collects only the intended Buyer login email and creates a temporary Buyer-registration attempt, not a Buyer account. It sends a six-digit OTP and opens B15.
- B15 verifies the purpose-bound OTP server-side. Successful verification rotates the restricted registration session and navigates directly to B14-2 in the same tab without a separate success screen, email link, success-button click, or artificial timer. Wrong, expired, replayed, or rate-limited codes remain on B15 with inline recovery, resend, and Change email actions.
- B14-2 is available only under a matching verified, unexpired, unconsumed Buyer-registration session. It displays the server-verified email read-only and collects name, phonetic name, phone, one default delivery address, password/confirmation, and Buyer Terms consent. A client-supplied replacement email is never accepted at final submission.
- Refresh with a valid verified session resumes B14-2. An unverified attempt opening B14-2 returns to B15; no valid attempt returns to B14-1. Changing email invalidates the prior verification and requires a new OTP. Passwords are never persisted in the temporary registration record.
- Final submission validates all details and consent, creates the Buyer account and default address atomically using the server-verified email, records terms version/time, consumes the registration grant exactly once, and establishes a fresh authenticated Buyer session. Duplicate, replayed, or concurrent submissions must not create multiple accounts.
- Each Buyer initial-registration OTP expires three minutes after issuance. Resending invalidates the prior usable OTP and starts a new three-minute validity period for the replacement. The authenticated B18 email-change flow remains link-based and keeps the existing email active until the new address is verified; it must not reuse the pre-account registration grant. Buyer attempt/resend/session limits and unresolved Producer numeric policies remain TBD-001.

## 7. Screen Inventory and Estimate

| No. | Screen ID | Area | Screen name | Japanese screen name | Summary | Hours | Days |
|---:|---|---|---|---|---|---:|---:|
| 1 | `B01` | Buyer | Home / Product List | `商品一覧（ホーム）` | Entry point for discovering published products and opening Product Detail or Cart. | 8 | 1 |
| 2 | `B02` | Buyer | Category | `カテゴリ` | Browse products by category. | 2 | 0.25 |
| 3 | `B03` | Buyer | Category Product List | `カテゴリ別商品一覧` | Show only products in the selected category. | 2 | 0.25 |
| 4 | `B04` | Buyer | Search | `検索` | Search products by keyword and show matching results or a no-results recovery message on the same screen. | 8 | 1 |
| 5 | `B05` | Buyer | Product Detail | `商品詳細` | Review product details and the current product price/stock before purchase. | 24 | 3 |
| 6 | `B06` | Buyer | Cart | `カート` | Review/edit products grouped by shop, show each shop subtotal, and start checkout for one selected shop while retaining other shop groups. | 32 | 4 |
| 7 | `B07` | Buyer | Order Confirmation | `注文内容の確認` | Confirm the selected shop's products, delivery address, total, and proceed to its payment. | 10 | 1.25 |
| 8 | `B08` | Buyer | Checkout Delivery Address Edit | `チェックアウト配送先編集` | Edit the delivery address for the current order and optionally save it as the default address. | 4 | 0.5 |
| 9 | `B09` | Buyer | PAY.JP Payment Processing | `PAY.JP決済処理` | Safely process PAY.JP payments by Producer and authoritative server-side verification. | 48 | 6 |
| 10 | `B10` | Buyer | Order Complete | `注文完了` | Show an authoritatively paid order result and dispatch one idempotent order-accepted email after commit. | 2 | 0.25 |
| 11 | `B11` | Buyer | Order History | `注文履歴` | Review the Buyer's own orders. | 4 | 0.5 |
| 12 | `B12` | Buyer | Order Detail | `注文詳細` | Review purchase-time order and delivery snapshots and current states, contact the relevant Producer, and download the completed order's receipt PDF. | 4 | 0.5 |
| 13 | `B13` | Buyer | Login | `ログイン` | Sign in to a Buyer account. | 4 | 0.5 |
| 14 | `B14` | Buyer | Member Registration | `新規会員登録` | Enter email first; after B15 OTP verification, complete Buyer details/password/terms and create the account with one default delivery address. | 4 | 0.5 |
| 15 | `B15` | Buyer | Email Verification | `メール確認` | Verify a six-digit OTP before initial Buyer registration details while preserving the separate B18 email-change link flow. | 4 | 0.5 |
| 16 | `B16` | Buyer | Password Reset | `パスワード再設定` | Reset a password without revealing account existence. | 4 | 0.5 |
| 17 | `B17` | Buyer | My Page | `マイページ` | Provide the Buyer account hub, including logout confirmation. | 2 | 0.25 |
| 18 | `B18` | Buyer | Member Information Update | `会員情報の変更` | Update the Buyer's own profile and default delivery address. | 4 | 0.5 |
| 19 | `B19` | Buyer | Password Change | `パスワード変更` | Allow a signed-in Buyer to change password. | 4 | 0.5 |
| 20 | `B20` | Buyer | Contact | `お問い合わせ` | Send a two-field general BVI-support inquiry or an order-linked Producer inquiry. | 1 | 0.125 |
| 21 | `B21` | Buyer | Contact Complete | `お問い合わせ完了` | Confirm inquiry receipt without duplication. | 1 | 0.125 |
| 22 | `P01` | Producer | Producer Login | `生産者ログイン` | Sign in with a Producer account separate from Buyer. | 2 | 0.25 |
| 23 | `P02` | Producer | Producer Registration | `生産者登録` | Enter email first; after OTP verification, complete the minimum profile/password, public shop photo, and Producer Terms consent; mobile explains where the photo appears to Buyers. | 4 | 0.5 |
| 24 | `P03` | Producer | Email Verification | `メール確認` | Verify a six-digit email OTP before Producer registration details; preserve the separate P12 email-change link flow. | 2 | 0.25 |
| 25 | `P04` | Producer | PAY.JP Application / Screening Status | `PAY.JP申請・審査状況` | Generate the PAY.JP application URL, open the hosted form, and handle screening status, expiry, correction, and reissue on one screen. | 8 | 1 |
| 26 | `P05` | Producer | Producer Dashboard | `生産者ダッシュボード` | Dedicated landing page shown after login. Displays read-only summary cards for the Producer's products, orders requiring action, sales, and payout alerts, with links to the related Producer screens. | 2 | 0.25 |
| 27 | `P06` | Producer | Producer Product List | `生産者商品一覧` | Create, edit, publish, or unpublish own products; mobile uses a vertical list without horizontal table scrolling. | 4 | 0.5 |
| 28 | `P07` | Producer | Product Create / Edit | `商品作成・編集` | Set product information, category, images, base price, stock, promotion, three regional delivery fees, and publication state on one screen. | 10 | 1.25 |
| 29 | `P08` | Producer | Producer Order List | `生産者注文一覧` | Process own Producer orders in a vertical mobile list and distinguish the system-controlled `注文確定` state from fulfillment. | 4 | 0.5 |
| 30 | `P09` | Producer | Producer Order Detail | `生産者注文詳細` | Review own lines and minimum delivery information, then update order status. | 4 | 0.5 |
| 31 | `P10` | Producer | Payout | `振込` | Review period sales, the Company's 10% commission, refunds and adjustments, payout history, and detailed payout breakdown in one read-only feature with responsive master-detail presentation. | 6 | 0.75 |
| 32 | `P11` | Producer | Producer Account Settings | `生産者アカウント設定` | Open account-related settings for Producer Account Information, Payout Bank Account Information, Password Change, and Logout. | 1 | 0.125 |
| 33 | `P12` | Producer | Producer Account Information | `生産者アカウント情報` | Review and update the Producer's basic profile and contact information on the same screen. | 3 | 0.375 |
| 34 | `P13` | Producer | Payout Bank Account Information | `振込口座情報` | Display saved bank account information only in masked form with its bank status. After re-authentication, update bank information on the same screen and send the changes to the PAY.JP Tenant API. | 5 | 0.625 |
| 35 | `P14` | Producer | Producer Password Change | `生産者パスワード変更` | Allow a signed-in Producer to change password. | 2 | 0.25 |
| 36 | `P15` | Producer | Producer Password Reset | `生産者パスワード再設定` | Recover Producer access without revealing account state. | 2 | 0.25 |
| 37 | `A01` | Admin | Admin Login | `管理者ログイン` | Sign in with an Admin account separate from Buyer and Producer accounts. | 2 | 0.25 |
| 38 | `A02` | Admin | Admin Dashboard | `管理ダッシュボード` | Summarize PAY.JP screening, payment exceptions, product/banner, order/refund, and payout states needing action. | 4 | 0.5 |
| 39 | `A03` | Admin | Buyer Management | `購入者管理` | Find Buyers and review their minimum account and usage information through the approved list and detail drawer. | 4 | 0.5 |
| 40 | `A04` | Admin | Producer Management | `生産者管理` | Search and filter Producers, then review the selected Producer account, PAY.JP Tenant information, card-brand screening, selling eligibility, application/support history, and operational status. Suspend or reactivate with an audit trail. | 6 | 0.75 |
| 41 | `A05` | Admin | Category Management | `カテゴリ管理` | Create, edit, enable, or disable shared categories used by Buyers and Producers. | 3 | 0.375 |
| 42 | `A06` | Admin | Banner Management | `バナー管理` | Review and control the Today's Recommendations banner automatically selected from the top three selling products. Producers cannot control it. | 3 | 0.375 |
| 43 | `A07` | Admin | Product Management | `商品管理` | Search and filter products across Producers, then review the selected product details, stock, publication status, and promotions. Unpublish or request correction with a reason when needed and record the action in the audit history. | 5 | 0.625 |
| 44 | `A08` | Admin | Order Management | `注文管理` | Search and filter Buyer orders and Producer sub-orders, then review the selected order details, payment, and Producer lines. Process refund/chargeback settlement effects idempotently and record the action in the audit history. | 6 | 0.75 |
| 45 | `A09` | Admin | Sales / Commission Summary | `売上・会社手数料サマリー` | View total sales, the 10% Company commission received, pending commission, refund reductions, and expected payout by period and Producer. | 4 | 0.5 |
| 46 | `A10` | Admin | Payout Monitor | `振込状況確認` | Monitor scheduled, paid, failed, and held Producer payouts and check whether the system-calculated amount matches the actual PAY.JP payout amount. | 4 | 0.5 |
| 47 | `A11` | Admin | Log Monitoring | `ログ監視` | Monitor failed logins, suspicious or unauthorized page/API access, payment callback anomalies, system errors, and sensitive operation results. Logs are read-only and must not contain passwords, card data, secrets, or unmasked bank information. | 4 | 0.5 |
|  |  |  | **Total** |  |  | **280** | **35** |

## 8. Functional Requirements

### 8.1 Buyer

- **FR-B-001 `[CONFIRMED]`** The system shall provide `B01` Home / Product List to entry point for discovering published products and opening Product Detail or Cart.
- **FR-B-002 `[CONFIRMED]`** The system shall provide `B02` Category to choose a shared category.
- **FR-B-003 `[CONFIRMED]`** The system shall provide `B03` Category Product List to show only products in the selected category.
- **FR-B-004 `[CONFIRMED]`** The system shall provide `B04` Search to search products by keyword and show matching results or a no-results recovery message.
- **FR-B-005 `[CONFIRMED]`** The system shall provide `B05` Product Detail to review product details and current product price/stock before purchase.
- **FR-B-006 `[CONFIRMED]`** The system shall provide `B06` Cart to review and edit seller-grouped cart items, show each shop subtotal, and begin checkout for one selected Producer group at a time, or show an empty-cart recovery state when no items exist.
- **FR-B-007 `[CONFIRMED]`** The system shall provide `B07` Order Confirmation to confirm the selected Producer's products, delivery address, total, and proceed to that Producer's payment.
- **FR-B-008 `[CONFIRMED]`** The system shall provide `B08` Checkout Delivery Address Edit to edit the delivery address for the current order and optionally save it as the default address.
- **FR-B-009 `[CONFIRMED]`** The system shall provide `B09` PAY.JP Payment Processing to safely process the selected Producer's PAY.JP payment, 3D Secure authentication, and authoritative server-side result.
- **FR-B-010 `[CONFIRMED]`** The system shall provide `B10` Order Complete to show an authoritatively paid order result and trigger one idempotent order-accepted email after commit.
- **FR-B-011 `[CONFIRMED]`** The system shall provide `B11` Order History to review the Buyer's own orders.
- **FR-B-012 `[CONFIRMED]`** The system shall provide `B12` Order Detail to review purchase-time order and delivery snapshots and current states, and to download a PDF receipt for a completed selected order.
- **FR-B-013 `[CONFIRMED]`** The system shall provide `B13` Login to sign in to a Buyer account.
- **FR-B-014 `[CONFIRMED]`** The system shall provide `B14` Member Registration with email-only entry first (B14-1), followed only after B15 OTP verification by Buyer details, password, default delivery address, and terms consent (B14-2). Create the account only upon successful final submission under a valid verified-registration session.
- **FR-B-015 `[CONFIRMED]`** The system shall provide `B15` Email Verification using a six-digit OTP for initial Buyer registration and retain the separate expiring-link verification behavior for authenticated B18 email changes.
- **FR-B-016 `[CONFIRMED]`** The system shall provide `B16` Password Reset to reset a password without revealing account existence.
- **FR-B-017 `[CONFIRMED]`** The system shall provide `B17` My Page to provide the Buyer account hub.
- **FR-B-018 `[CONFIRMED]`** The system shall provide `B18` Member Information Update to update the Buyer profile and default delivery address.
- **FR-B-019 `[CONFIRMED]`** The system shall provide `B19` Password Change to allow a signed-in Buyer to change password.
- **FR-B-020 `[CONFIRMED]`** The system shall provide `B20` Contact with a two-field general BVI-support form and an order-linked Producer-inquiry variant.
- **FR-B-021 `[CONFIRMED]`** The system shall provide `B21` Contact Complete to confirm inquiry receipt without duplication.

### 8.2 Producer

- **FR-P-001 `[CONFIRMED]`** The system shall provide `P01` Producer Login to sign in with a Producer account separate from Buyer.
- **FR-P-002 `[CONFIRMED]`** The system shall provide `P02` Producer Registration with email entry first (P02-1), followed only after P03 OTP verification by minimum profile, password, and Producer Terms acceptance (P02-2). Create the account only upon successful final submission under a valid verified-registration session, and tell mobile applicants that the shop photo appears to Buyers as the shop image in product lists.
- **FR-P-003 `[CONFIRMED]`** The system shall provide `P03` Email Verification to confirm ownership of a new or changed Producer login email.
- **FR-P-004 `[CONFIRMED]`** The system shall provide `P04` PAY.JP Application / Screening Status to complete the PAY.JP hosted application and track card-brand screening before selling.
- **FR-P-005 `[CONFIRMED]`** The system shall provide `P05` Producer Dashboard to provide the Producer's landing page and own operational summaries.
- **FR-P-006 `[CONFIRMED]`** The system shall provide `P06` Producer Product List to manage only the signed-in Producer's products using a stacked vertical mobile list without horizontal table scrolling.
- **FR-P-007 `[CONFIRMED]`** The system shall provide `P07` Product Create / Edit to create or edit complete product selling information and three regional delivery fees on one screen.
- **FR-P-008 `[CONFIRMED]`** The system shall provide `P08` Producer Order List to process Producer orders containing only the Producer's products using a stacked vertical mobile list and system-controlled `注文確定` presentation.
- **FR-P-009 `[CONFIRMED]`** The system shall provide `P09` Producer Order Detail to review own order lines and minimum delivery information, then manually update fulfillment.
- **FR-P-010 `[CONFIRMED]`** The system shall provide `P10` Payout to review own sales, settlement calculation, payout history, and documents in one read-only feature. Desktop shall use same-page master-detail presentation; mobile shall use an overview/history view that navigates to a selected-payout detail subview within P10.
- **FR-P-011 `[CONFIRMED]`** The system shall provide `P11` Producer Account Settings to provide one hub for Producer account-related settings, confirmed shop-profile-photo updates, and logout.
- **FR-P-012 `[CONFIRMED]`** The system shall provide `P12` Producer Account Information to review and update the Producer's internal profile, contact information, and login email on one screen.
- **FR-P-013 `[CONFIRMED]`** The system shall provide `P13` Payout Bank Account Information to review masked payout-bank data and update it securely on the same screen.
- **FR-P-014 `[CONFIRMED]`** The system shall provide `P14` Producer Password Change to change the Producer password securely from Account Settings.
- **FR-P-015 `[CONFIRMED]`** The system shall provide `P15` Producer Password Reset to recover Producer access without revealing account state.

### 8.3 Admin

- **FR-A-001 `[CONFIRMED]`** The system shall provide `A01` Admin Login to sign in to the protected Admin Portal with the authorized Admin account.
- **FR-A-002 `[CONFIRMED]`** The system shall provide `A02` Admin Dashboard to show the Admin's operational overview and direct links to items requiring attention.
- **FR-A-003 `[CONFIRMED]`** The system shall provide `A03` Buyer Management to search Buyers and review their minimum account and usage information through a read-only list and detail drawer.
- **FR-A-004 `[CONFIRMED]`** The system shall provide `A04` Producer Management to monitor Producer accounts, PAY.JP Tenant status, selling eligibility, and operational state.
- **FR-A-005 `[CONFIRMED]`** The system shall provide `A05` Category Management to maintain the shared product categories used by Buyers and Producers.
- **FR-A-006 `[CONFIRMED]`** The system shall provide `A06` Banner Management to review and control the Home Today's Recommendations banner.
- **FR-A-007 `[CONFIRMED]`** The system shall provide `A07` Product Management to search, review, and moderate products and Producer-created promotions.
- **FR-A-008 `[CONFIRMED]`** The system shall provide `A08` Order Management to search orders, review Buyer and Producer order detail, and handle refund/chargeback exceptions.
- **FR-A-009 `[CONFIRMED]`** The system shall provide `A09` Sales / Commission Summary to show total sales and the Company's 10% commission received or pending.
- **FR-A-010 `[CONFIRMED]`** The system shall provide `A10` Payout Monitor to monitor PAY.JP's direct Producer payouts and compare them with system-calculated amounts.
- **FR-A-011 `[CONFIRMED]`** The system shall provide `A11` Log Monitoring to monitor security, system, payment-callback, and sensitive-operation events safely.

## 9. Buyer Screen Specifications

### SCR-B-001 — B01 Home / Product List / `商品一覧（ホーム）`

- **Status:** `[CONFIRMED]`
- **Actor:** Guest / Buyer
- **Purpose:** Entry point for discovering published products and opening Product Detail or Cart.
- **Main entry:** App launch / Home navigation
- **Summary:** Home / Product List is the Buyer entry page for discovering published products. Buyers can browse product cards, use category quick filters, open the Search page, open Cart, or move to Product Detail. The approved B01 UI does not show a campaign banner; category filters and the product grid begin directly below the header. A purchasable in-stock product can be added directly from the list. Producer/Farm name is not shown on list cards in V1.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `B01-01` | No campaign banner / `バナー非表示` | Do not show a campaign or recommendation banner on B01. Begin the category quick filters and product list directly below the header. | B01にはキャンペーン／おすすめバナーを表示せず、ヘッダー直下からカテゴリ簡易絞り込みと商品一覧を表示する。 | — | Remain on B01; no banner destination exists on this screen. |
| `B01-02` | Search / `検索` | Show a search icon in the header. | ヘッダーに検索アイコンを表示する。 | Tap Search. | Open Search page. |
| `B01-03` | Category quick filter / `カテゴリ簡易絞り込み` | Show shared Admin-managed categories as quick filters; Producers cannot create categories. | 管理者が用意した共通カテゴリを簡易絞り込みとして表示する。生産者独自カテゴリは使用しない。 | Select a category. | Filter products on Home and retain the selected state. |
| `B01-04` | Product card / `商品カード` | Show product image, product name, price, promotion display, stock/sold-out state, and cart action. Producer/Farm name is not shown on the list card in V1. | 商品画像、商品名、価格、プロモーション表示、在庫／売り切れ状態、カート操作を表示する。V1の商品一覧カードには生産者名／農園名を表示しない。 | Tap the card body. | Open Product Detail page. |
| `B01-05` | Add to Cart / `カートに追加` | Show Add to Cart for a purchasable in-stock product. | 購入可能で在庫のある商品には追加ボタンを表示する。 | Tap Add to Cart. | Add quantity 1 and increase the cart count without opening Product Detail. |
| `B01-06` | Purchase availability / `購入可否` | Show a clear unavailable state when the product is unpublished, out of stock, or otherwise not purchasable. | 商品が非公開、在庫切れ、またはその他の理由で購入不可の場合、明確な購入不可状態を表示する。 | Review or tap the product card. | Do not add the product; open Product Detail only when a safe explanatory state is required. |
| `B01-07` | Multi-producer cart / `複数生産者カート` | Allow products from different Producers in one cart. | 生産者が異なる商品も同じカートに保持できる。 | Add another Producer's product. | Keep existing items and add the new item. |
| `B01-08` | Header / bottom navigation / `ヘッダー／下部ナビゲーション` | Home has no Back action. Show header search/cart access and bottom navigation: Home / Category / Cart / My Page. | ホームには戻る操作を表示しない。ヘッダーに検索／カート導線を表示し、下部ナビは「ホーム／カテゴリ／カート／マイページ」とする。 | Tap a navigation item. | Open the selected root page. |

- **Acceptance:** All `B01-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

### SCR-B-002 — B02 Category / `カテゴリ`

- **Status:** `[CONFIRMED]`
- **Actor:** Guest / Buyer
- **Purpose:** Choose a shared category.
- **Main entry:** Category bottom tab
- **Summary:** Category is the Buyer page for choosing a shared Admin-managed product category. Buyers open it from the bottom navigation, review available category cards such as All, Vegetables, Fruit, Rice/Grains, and Sets, then select a category to open the Category Product List page filtered by that category. Producers cannot create or control Buyer-facing categories.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `B02-01` | Category cards / `カテゴリカード` | Show enabled Admin-managed categories such as All, Vegetables, Fruit, Rice/Grains, Sets, and Other. The B02 design includes Other in the lower-right grid cell, with helper text Other products. | すべて／野菜／果物／米・穀物／セット／その他等の有効な管理者管理カテゴリを表示する。「その他」カードの補足は「その他の商品」とする。 | Tap a category card. | Open Category Product List page filtered by the selected category. |
| `B02-02` | Header / bottom navigation / `ヘッダー／下部ナビゲーション` | Show page title, search/cart access, and bottom navigation: Home / Category / Cart / My Page. | 画面名、検索／カート導線、下部ナビ「ホーム／カテゴリ／カート／マイページ」を表示する。 | Tap a navigation item. | Open the selected root page. |

- **Acceptance:** All `B02-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

### SCR-B-003 — B03 Category Product List / `カテゴリ別商品一覧`

- **Status:** `[CONFIRMED]`
- **Actor:** Guest / Buyer
- **Purpose:** Show only products in the selected category.
- **Main entry:** Category page
- **Summary:** Category Product List shows products filtered by the category selected on the Category page. Buyers can confirm the selected category name, browse matching product cards, open Product Detail page, or add purchasable in-stock products to the same cart. This page does not show the Home banner, but it follows the same product card, price, promotion, stock, and multi-producer cart behavior as Home / Product List.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `B03-01` | Selected category / `選択カテゴリ` | Show the selected category name and matching products. Do not show the Home banner. | 選択中カテゴリ名と該当商品を表示する。ホームのバナーは表示しない。 | Tap a product card. | Open Product Detail page. |
| `B03-02` | Cart addition / `カート追加` | Use the same price, promotion, stock, and multi-producer cart rules as Home / Product List. | 商品一覧（ホーム）と同じ価格、プロモーション、在庫、複数生産者カートの規則を使用する。 | Tap the cart action. | Add to the same cart and update the count. |
| `B03-03` | Header / bottom navigation / `ヘッダー／下部ナビゲーション` | Show page title, Back action, search/cart access, and bottom navigation: Home / Category / Cart / My Page. | 画面名、戻る操作、検索／カート導線、下部ナビ「ホーム／カテゴリ／カート／マイページ」を表示する。 | Tap Back or a navigation item. | Return to Category page or open the selected root page. |

- **Acceptance:** All `B03-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

### SCR-B-004 — B04 Search / `検索`

- **Status:** `[CONFIRMED]`
- **Actor:** Guest / Buyer
- **Purpose:** Search products by keyword and show matching results or a no-results recovery message.
- **Main entry:** Header Search
- **Summary:** Search page lets Guests and Buyers search published products by keyword. It opens from the header search action, keeps the entered keyword and result count, shows matching product cards, and keeps the same product-card, price, promotion, stock, and multi-producer cart rules as the other Buyer product-list pages. When no products match, the same Search page shows a clear no-results message and lets the user edit the keyword or return to Home.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `B04-01` | Search field / `検索欄` | Show the query and result count. | 入力済みキーワードと検索結果件数を表示する。 | Enter a keyword and search. | Trim whitespace and run the search. |
| `B04-02` | Results / `検索結果` | Show matching published product cards. | 一致した公開商品カードを表示する。 | Tap a product card or cart action. | Open Product Detail page or add to Cart. |
| `B04-03` | Header / navigation / `ヘッダー／ナビゲーション` | Show title, Back, search/cart, or bottom navigation as appropriate. | 画面名、戻る、検索・カート導線、または画面用途に応じた下部ナビを表示する。 | Tap Back or a navigation item. | Open the prior or selected root page. Home alone has no Back action. |
| `B04-04` | No-results state / `0件表示` | Show the search field, zero count, and no-match message. | 検索欄、0件、該当商品がないことを示すメッセージを表示する。 | Edit the query. | Search again on the same screen. |
| `B04-05` | Return Home / `ホームへ戻る` | Show Return to Home when there are no results. | 0件時にホームへ戻る操作を表示する。 | Tap the button. | Open Home page. |

- **Acceptance:** All `B04-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

### SCR-B-005 — B05 Product Detail / `商品詳細`

- **Status:** `[CONFIRMED]`
- **Actor:** Guest / Buyer
- **Purpose:** Review product details and current product price/stock before purchase.
- **Main entry:** Product List / Search / Category
- **Summary:** The Product Detail page is opened from a product list, category list, or search result. It presents the selected product images, name, public producer name, price and promotion, description, stock, and quantity controls. Buyers can add the product to the cart or proceed toward checkout. Price, stock, and quantity rules are checked before the action, and the page provides clear feedback when the product cannot be purchased. Company commission and internal settlement information are not shown to Buyers.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `B05-01` | Product information / `商品情報` | Show images, name, Producer/farm name, description, and promotion. Public Producer data is name only. | 画像、商品名、生産者名、説明、プロモーションを表示する。公開生産者情報は名称のみ。 | Review images and description. | Remain on Product Detail. |
| `B05-02` | Price and stock / `価格・在庫` | Show the product's current price, Producer-funded discount, and stock availability without a variation selector. | バリエーション選択を表示せず、商品の現在価格、生産者負担割引、在庫状況を表示する。 | Review the selling information. | Keep the displayed price and availability synchronized with the product's single internal sellable option. |
| `B05-03` | Quantity / cart / `数量／カート` | Show quantity, Add to Cart, and Buy Now. On mobile, keep quantity in the scrollable product content and show the two purchase actions at equal width and height in a separate white sticky tray immediately above the bottom navigation. Place outlined `今すぐ購入` on the left and filled `カートに追加` on the right; styling, not geometry, communicates priority. The tray is not part of the navigation, and the content area must reserve enough bottom space to remain unobscured. | 数量とカート追加、今すぐ購入を表示する。モバイルでは数量をスクロール可能な商品内容内に置き、下部ナビゲーション直上の独立した白い固定アクショントレイに、同じ幅と高さで左側へアウトラインの「今すぐ購入」、右側へ塗りの「カートに追加」を表示する。優先度はサイズ差ではなくスタイルで示す。トレイは下部ナビゲーションには含めず、商品内容が隠れない下部余白を確保する。 | Choose quantity and act. | After stock validation, add to Cart or proceed toward Checkout. |
| `B05-04` | Header / navigation / `ヘッダー／ナビゲーション` | Show title, Back, search/cart, or bottom navigation as appropriate. | 画面名、戻る、検索・カートまたは下部ナビを画面用途に応じて表示する。 | Tap Back or a navigation item. | Open the prior or selected root screen. Home alone has no Back action. |

- **Acceptance:** All `B05-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

### SCR-B-006 — B06 Cart / `カート`

- **Status:** `[CONFIRMED]`
- **Actor:** Guest / Buyer
- **Purpose:** Review and edit cart items, or show an empty-cart recovery state when no items exist.
- **Main entry:** Cart icon / product addition
- **Summary:** The Cart lets Buyers review and adjust products from multiple Producers while presenting compact shop groups. Each group shows that shop's products, total quantity, tax-and-shipping-inclusive subtotal, and its own checkout action. Checkout begins for one selected Producer only; products from other shops remain in the cart. An empty cart returns to Home.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `B06-01` | Seller-grouped cart lines / `ショップ別商品明細` | Group cart lines by public shop. Within each compact shop section, show every product, tax-and-shipping-inclusive price, remove action, and inline decrement/value/increment quantity stepper. A shop may contain multiple different products. Do not show customer-facing variation text. | 公開ショップごとに商品をまとめ、各ショップ内に商品、税込・送料込み価格、削除操作、減算／数量／加算の数量ステッパーを表示する。同一ショップの複数商品を同じセクションに表示する。 | Change quantity or remove an item. | Revalidate stock, regional delivery fee, price, and promotion; update only the affected shop subtotal and cart totals. |
| `B06-02` | Promotion / `プロモーション` | Show regular price, promotional price, and rate per eligible line; never show commission. | 対象行に通常価格、割引後価格、割引率を表示する。会社手数料は表示しない。 | Review pricing. | Calculate totals using product-level promotions. |
| `B06-03` | Seller subtotal and checkout / `ショップ小計・ショップ別購入` | For each shop, show its item count and subtotal as `ショップ小計（N点・税込・送料込み）` plus one checkout action for that shop. Do not show one combined checkout action for all Producers. | 各ショップに「ショップ小計（N点・税込・送料込み）」と、そのショップ専用の購入手続き操作を表示する。全生産者をまとめた購入ボタンは表示しない。 | Start checkout for one shop. | Open B13 if logged out; otherwise open B07 containing only the selected Producer's current cart lines. Leave other shop groups in B06. |
| `B06-04` | Header / navigation / `ヘッダー／ナビゲーション` | Show title, Back, search/cart, or bottom navigation as appropriate. | 画面名、戻る、検索・カートまたは下部ナビを画面用途に応じて表示する。 | Tap Back or a navigation item. | Open the prior or selected root screen. Home alone has no Back action. |
| `B06-05` | Empty-cart state / `空カート状態` | Show an empty-cart message and Return to Product List action. | 空カートメッセージと商品一覧へ戻る操作を表示する。 | Tap Return to Product List. | Open Home (B01). |

- **Acceptance:** All `B06-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

### SCR-B-007 — B07 Order Confirmation / `注文内容の確認`

- **Status:** `[CONFIRMED]`
- **Actor:** Buyer
- **Purpose:** Confirm order details, delivery address, totals, and proceed to payment.
- **Main entry:** Cart (B06) / after login
- **Summary:** Order Confirmation shows only the selected Producer group's current cart lines, delivery address, promotions, regional-delivery-inclusive total, and public shop name for final review, then starts that Producer's PAY.JP payment without a local payment-method selector.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `B07-01` | Delivery address / `お届け先` | Show the single registered address and a Change action. | 登録済みの1件の住所を表示し、変更操作を表示する。 | Tap Change. | Open Delivery Address Change (B08). |
| `B07-02` | Selected-shop order summary / `選択ショップの注文明細` | Show only the selected Producer's shop and products, including multiple different products from that shop, quantities, promotions, and one tax-and-shipping-inclusive total. | 選択した1ショップの商品、数量、プロモーション、税込・送料込み合計を表示する。同一ショップの複数商品を表示できる。 | Review the order. | Revalidate the selected shop's server-authoritative price, stock, promotion, delivery zone, and regional fee. |
| `B07-03` | Proceed to PAY.JP Payment / `PAY.JP決済へ進む` | Show Proceed to Payment; do not show a local payment-method selector. | 決済画面へ進むボタンを表示し、ローカルの決済方法選択は置かない。 | Tap once. | Initialize PAY.JP payment processing and open B09. |
| `B07-04` | Delivery address / `配送先住所` | Show the default delivery address or current order address snapshot. | 基本配送先住所または今回の注文用住所を表示する。 | Tap Edit delivery address. | Open B08 or equivalent checkout address edit presentation. |
| `B07-05` | Header / navigation / `ヘッダー／ナビゲーション` | Show title, Back, search/cart, or bottom navigation as appropriate. | 画面名、戻る、検索・カートまたは下部ナビを画面用途に応じて表示する。 | Tap Back or a navigation item. | Open the prior or selected root screen. Home alone has no Back action. |

- **Acceptance:** All `B07-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

### SCR-B-008 — B08 Checkout Delivery Information Edit / `お届け先情報の変更`

- **Status:** `[CONFIRMED]`
- **Actor:** Buyer
- **Purpose:** Edit the delivery address for the current order and optionally save it as the default address.
- **Main entry:** Order Confirmation (B07)
- **Summary:** This checkout-time screen edits the delivery address snapshot for the current order and can optionally update the Buyer default address before returning to Order Confirmation.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `B08-01` | Delivery information form / `お届け先情報入力フォーム` | Show recipient name, delivery phone, postal code, prefecture, municipality, and street/building fields under delivery information rather than labeling the whole entity merely as an address. | `宛名`、`電話番号`、`郵便番号`、`都道府県`、`市区町村`、`番地・建物名`を表示し、主操作を`このお届け先を使用`とする。 | Edit fields and use this delivery information. | Update the current order delivery-address snapshot and return to B07. |
| `B08-02` | Save as default delivery information / `基本のお届け先として保存` | Show the optional `基本のお届け先として保存する` checkbox. | 任意の「基本のお届け先として保存する」チェックボックスを表示する。 | Select and save. | If selected, update the Buyer profile default delivery information as well. |
| `B08-03` | Cancel / return / `キャンセル／戻る` | Show Cancel or Back action. | キャンセルまたは戻る操作を表示する。 | Cancel editing. | Return to Order Confirmation (B07) without changing the current order address. |

- **Acceptance:** All `B08-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

### SCR-B-009 — B09 PAY.JP Payment Processing / `PAY.JP決済処理`

- **Status:** `[CONFIRMED]`
- **Actor:** Buyer
- **Purpose:** Safely process PAY.JP payments by Producer, 3D Secure authentication, and authoritative server-side results.
- **Main entry:** Order Confirmation (B07) / PAY.JP authentication return
- **Summary:** The system processes only the Producer group selected in B06/B07 and creates one uniquely referenced payment for that Producer's PAY.JP Tenant. When required, the Buyer completes 3D Secure authentication. A browser return alone never marks the order paid; payment state is determined only from authoritative PAY.JP API or webhook results.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `B09-01` | Payment initiation / `決済開始` | Show payment-processing guidance for the selected shop order. | 選択したショップ注文の決済処理中案内を表示する。 | Confirm the order and start payment. | Revalidate the selected Producer's price, regional delivery fee, promotion, and stock server-side and create one uniquely referenced payment for that PAY.JP Tenant. Never store card numbers or security codes. |
| `B09-02` | 3D Secure authentication / `3Dセキュア認証` | Show authentication guidance when required. | 認証が必要な場合は認証案内を表示する。 | Complete authentication with the card issuer. | Follow the PAY.JP-directed authentication flow and resume server-side verification after return. Never treat the browser return alone as success. |
| `B09-03` | Result verification / `決済結果確認` | Show Verifying, Completed, Failed, Cancelled, or Pending. | 確認中、完了、失敗、取消、処理保留を表示する。 | Check status or retry safely. | Update each payment only from authoritative PAY.JP API or webhook information and prevent duplicate execution of the same attempt. |
| `B09-04` | Completion and recovery / `完了・回復` | Show the selected shop order result and the next available action. | 選択したショップ注文の結果と次の操作を表示する。 | Open completion or follow the recovery guidance. | Open B10 only after authoritative success for the selected Producer payment and committed order creation. For failure, cancellation, or pending states, do not mark the order paid; prevent duplicate charges and provide safe recovery or return to B07/B06. |

- **Acceptance:** All `B09-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

### SCR-B-010 — B10 Order Complete / `注文完了`

- **Status:** `[CONFIRMED]`
- **Actor:** Buyer
- **Purpose:** Show an authoritatively paid order result.
- **Main entry:** PAY.JP Payment Processing (B09)
- **Summary:** Order Complete shows the selected shop's order number and paid amount only after authoritative payment confirmation, with safe links to Order Detail or Home and no duplicate processing on refresh. After the order commits, the system dispatches one order-accepted email.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `B10-01` | Completion details / `完了情報` | Show completion message, order number, and paid amount. | 完了メッセージ、注文番号、支払金額を表示する。 | Choose Order Detail or Home. | Open B12 or B01. |
| `B10-02` | Safe refresh / `再表示` | Allow safe redisplay of the same result. | 同じ注文結果を再表示できる。 | Refresh. | Do not re-run payment or order creation. |
| `B10-03` | Order-accepted email / `注文受付メール` | No additional completion-screen control is required. | 画面上に追加操作は表示しない。 | Complete payment successfully. | After authoritative payment success and committed order creation, dispatch one idempotent order-accepted email. The proposed minimum content is order number, shop, purchased-product summary, total, and Order Detail route; exact Japanese subject/body copy remains TBD-010. Exclude card credentials, internal commission, settlement, and payout data. |

- **Acceptance:** All `B10-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

### SCR-B-011 — B11 Order History / `注文履歴`

- **Status:** `[CONFIRMED]`
- **Actor:** Buyer
- **Purpose:** Review the Buyer's own orders.
- **Main entry:** My Page (B17)
- **Summary:** Order History, accessed from My Page, lists the signed-in Buyer’s own orders chronologically with key order and payment states, and opens Order Detail for review.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `B11-01` | Order list and period filter / `注文一覧・期間絞り込み` | Show order number, date, amount, order state, and payment state. Filter the signed-in Buyer's purchase history by All time, last 30 days, last 90 days, last 12 months, calendar year, or Custom date range. Show Start and End date fields only when Custom date range is selected. | 注文番号、注文日、金額、注文状態、支払状態を表示する。ログイン中の購入者の購入履歴を、すべての期間、過去30日、過去90日、過去12か月、暦年、または指定期間で絞り込めるようにする。指定期間を選択した場合のみ開始日と終了日を表示する。 | Select a period, enter a valid custom range when applicable, or tap an order row. | Refresh the Buyer's own orders for the applied period, or open Order Detail (B12). |
| `B11-02` | Header / navigation / `ヘッダー／ナビゲーション` | Show title, Back, search/cart, or bottom navigation as appropriate. | 画面名、戻る、検索・カートまたは下部ナビを画面用途に応じて表示する。 | Tap Back or a navigation item. | Open the prior or selected root screen. Home alone has no Back action. |
| `B11-03` | Product preview in Producer order / `生産者注文の商品プレビュー` | Identify the Producer on the order card. Every order card shows product thumbnail, name, and quantity. Show one row for a single-product order and up to two rows for a multi-product order; when additional products remain, summarize them as `ほかN点`. Show only the order total on B11 and do not show per-line prices. Do not split products from the same Producer order into separate history cards. | 注文カードに生産者名を表示し、すべての注文で商品画像・商品名・数量を表示する。1商品は1行、複数商品は最大2行まで表示し、残りは「ほかN点」で示す。B11では商品別金額を表示せず注文合計のみを表示する。同一生産者への1注文を別カードに分割しない。 | Review the product preview or tap the order card. | Open B12 with the complete owned order and all purchased-product snapshots. |

- **Acceptance:** All `B11-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

### SCR-B-012 — B12 Order Detail / `注文詳細`

- **Status:** `[CONFIRMED]`
- **Actor:** Buyer
- **Purpose:** Review purchase-time order and delivery snapshots and current states.
- **Main entry:** Order History (B11)
- **Summary:** Order Detail presents the Buyer’s purchase-time line and delivery snapshots together with current order, payment, and refund states, plus an order-linked route to contact the relevant Producer through the platform. Within 30 minutes after completion, the Buyer may cancel through a confirmation dialog.

- **[CONFIRMED; B12-01, B12-06, AT-B-012] Cancellation result presentation:** After authoritative cancellation success, show `注文：キャンセル済み` and a confirmation `注文をキャンセルしました`. Preserve purchased-item, total, and delivery snapshots. Remove the cancellation deadline and cancellation action; offer `注文履歴に戻る` to B11. Display refund status independently; the example state uses `返金：処理中` with the refund amount and `反映時期はカード会社により異なります。`. Do not show refund completed based only on cancellation confirmation or prototype navigation. This is a state within B12, not a new logical screen. Verify the success-only transition, preservation of snapshots, no repeat cancellation action, and return navigation under AT-B-012.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `B12-01` | Order and refund states / `注文・返金状態` | Show the operational order state and refund state separately. Do not repeat a normal `支払：完了` badge because B12 exists only for an order created after authoritative payment success. Payment remains an independent backend state and must not be inferred from a browser return. | 注文状態と返金状態を分けて表示する。B12は権威ある決済成功後に作成された注文のみを表示するため、通常の「支払：完了」は重複表示しない。 | Review states. | Display the history without modification. |
| `B12-02` | Purchased lines / `購入明細` | Show purchase-time product, Producer, price, promotion, and quantity snapshots. | 購入時の商品名、生産者、価格、割引、数量を表示する。 | Review lines. | Keep history unchanged after catalog edits. |
| `B12-03` | Delivery address snapshot / `配送先スナップショット` | Show the delivery address used for this order at purchase time. | 購入時点でこの注文に使用した配送先住所を表示する。 | Review delivery destination. | May differ from the current Buyer default address. |
| `B12-04` | Contact Producer / `生産者への問い合わせ` | At the bottom of the purchased-lines card, show one divider-separated navigation row with a concise label and trailing chevron. The entire row is the touch target and carries the owned order and relevant Producer context. Do not expose private Buyer or Producer contact details. | `生産者に問い合わせる` と右向きシェブロンを購入明細カード内の最下部に表示する。 | Tap the Producer inquiry row. | Open Contact (B20) with the owned order and Producer context. The operational delivery and response channel remains governed by TBD-005. |
| `B12-05` | Header / navigation / `ヘッダー／ナビゲーション` | Show title, Back, search/cart, or bottom navigation as appropriate. | 画面名、戻る、検索・カートまたは下部ナビを画面用途に応じて表示する。 | Tap Back or a navigation item. | Open the prior or selected root screen. Home alone has no Back action. |
| `B12-06` | Order cancellation / `注文キャンセル` | Show cancellation and a confirmation dialog only within 30 minutes after order completion; hide the action after the deadline. | 注文完了後30分以内の場合のみキャンセル操作と確認ダイアログを表示する。期限後はキャンセル操作を表示しない。 | Review and confirm cancellation. | Refund the order's Producer PAY.JP payment exactly once and update order, payment, and refund states. Repeated actions must not create another refund; after the deadline, mark the order `注文確定` and guide later issues to Contact (B20). |
| `B12-07` | PDF receipt / `領収書PDF` | For a completed order, show `領収書をダウンロード`. Generate a PDF for the selected owned order only, containing its order reference, purchase date, shop, purchased-product snapshots, and total. The approved presentation labels the total `税込` and does not show a separate tax-rate or tax-amount line. Do not expose receipt download before completion. | 完了した注文に「領収書をダウンロード」を表示する。選択した1注文のみの領収書PDFを生成し、注文番号、購入日、ショップ、購入商品、合計（税込）を表示する。税率・税額の独立行は表示しない。 | Download the receipt. | Re-authorize Buyer ownership and completed state on every request, generate the receipt from immutable order snapshots, and return a PDF without mutating the order. Statutory qualified-invoice scope remains TBD-007. |

- **Acceptance:** All `B12-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

### SCR-B-013 — B13 Login / `ログイン`

- **Status:** `[CONFIRMED]`
- **Actor:** Guest
- **Purpose:** Sign in to a Buyer account.
- **Main entry:** Checkout / My Page / direct access
- **Summary:** Login authenticates a Buyer and returns to the intended destination or Home, with links to registration and password reset; this screen has no bottom navigation.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `B13-01` | Credentials / `ログイン情報` | Show email, password, and Login. | メールアドレス、パスワード、ログインボタンを表示する。 | Enter credentials and submit. | After success, open the intended destination or Home. |
| `B13-02` | Supporting links / `補助導線` | Show Registration and Password Reset links. | 新規会員登録とパスワード再設定を表示する。 | Tap a link. | Open B14 or B16. |

- **Acceptance:** All `B13-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

### SCR-B-014 — B14 Member Registration / `新規会員登録`

- **Status:** `[CONFIRMED]`
- **Actor:** Guest
- **Purpose:** Verify the intended login email before collecting details, then create a Buyer account with one default delivery address.
- **Main entry:** Login (B13)
- **Summary:** B14-1 collects email only and sends a six-digit OTP through B15. B14-2 requires a server-verified registration session, displays the verified email read-only, and collects the remaining Buyer account/default-address details, password, and terms consent. No Buyer account exists before final submission; the flow has no bottom navigation.
- **B14-1 approved presentation (2026-09-09):** Use the existing Buyer mobile shell, `新規会員登録` header, compact three-step progress `① メール入力 › ② メール確認 › ③ 情報入力`, existing centered Minori-kun logo, one required `メールアドレス` field, `認証コードを送信` primary action, and an existing-Buyer `ログイン` link. Do not add a separate success screen after OTP verification.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `B14-01` | Email entry / `メール入力` | B14-1 shows email only, required indication, three-step progress, Send verification code, and Login. | メールアドレス／認証コードを送信／ログイン。 | Enter email and request a code. | Validate and normalize email, respond neutrally where account enumeration is possible, create/update a temporary registration attempt, send a six-digit OTP, and open B15. Do not create an account. |
| `B14-02` | Verified member information / `確認済み会員情報` | B14-2 shows the verified email read-only, then name, phonetic name, phone, postal code, and address. | 確認済みメールアドレスを読み取り専用で表示し、氏名、フリガナ、電話番号、郵便番号、住所を入力する。 | Complete required fields after verification. | Require the server-verified registration session and validate formats. Create one default delivery address only with final account creation. |
| `B14-03` | Password / consent / `パスワード／同意` | Show password, confirmation, Buyer Terms consent, and the final Register action on B14-2. | パスワード、確認、利用規約同意、登録ボタンを表示する。 | Consent and register. | Atomically create the verified-email account and default address, record terms version/time, consume the grant once, establish a fresh authenticated session, and open the intended destination or Home. |
| `B14-04` | Existing Buyer / `既存会員` | Show a Login link on B14-1/B14-2 as appropriate. | ログインへ戻るリンクを表示する。 | Tap the link. | Open Login (B13). |

- **Acceptance:** All `B14-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

### SCR-B-015 — B15 Email Verification / `メール確認`

- **Status:** `[CONFIRMED]`
- **Actor:** Buyer Applicant / authenticated Buyer
- **Purpose:** Confirm email ownership before initial account creation or after an authenticated email-address change.
- **Main entry:** B14-1 after requesting an OTP / B18 verification email link
- **Summary:** Initial registration uses a six-digit email OTP before B14-2 and account creation. B18 email changes retain their separate expiring one-time link flow and keep the previous login email active until verification.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `B15-01` | Initial-registration OTP / `登録メール確認` | Show destination email, one six-digit code input, Verify and continue, verifying, invalid/expired code, attempt-limit, and retry states. | 確認コード（6桁）／確認して次へ進む。 | Submit the registration OTP. | Verify the purpose-bound challenge server-side. Success issues restricted registration permission and automatically opens B14-2; it does not create an account. |
| `B15-02` | Registration resend/change / `再送・メール変更` | Show Resend code and Change email with delivery/retry feedback. | 確認コードを再送する／メールアドレスを変更する。 | Request another code or return to B14-1. | Each code expires three minutes after issuance. Invalidate the prior usable challenge when resending and start a new three-minute validity period. Apply the remaining request, resend, and verification limits. Changing email invalidates verification. Remaining numeric policies: TBD-001. |
| `B15-03` | Authenticated email-change result / `メール変更確認結果` | For B18 only, show success, expired, invalid, or already-used link state. | B18のメール変更では、成功、期限切れ、無効、使用済み状態を表示する。 | Open the verification link. | Replace the login email only for a valid authenticated-account change; otherwise retain the previous email. This path never grants B14-2 access. |
| `B15-04` | Navigation / `遷移` | Registration OTP success transitions automatically to B14-2; B18 email-change states show My Page/recovery guidance as appropriate. | 登録確認後はB14-2へ自動遷移し、メール変更ではマイページまたは再試行導線を表示する。 | Continue or recover. | Preserve the originating intent without creating duplicate accounts or applying an unverified email change. |

- **Acceptance:** All `B15-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

### SCR-B-016 — B16 Password Reset / `パスワード再設定`

- **Status:** `[CONFIRMED]`
- **Actor:** Guest / Buyer
- **Purpose:** Reset a password without revealing account existence.
- **Main entry:** Login (B13)
- **Summary:** Password Reset accepts a neutral email request and, for a valid one-time token, saves a new password without revealing whether an account exists, then returns to Login.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `B16-01` | Reset request / `再設定依頼` | Show email input and Send. | メールアドレス入力と送信を表示する。 | Submit. | Always show the same neutral receipt message. |
| `B16-02` | New password / `新パスワード` | Show new password and confirmation for a valid token. | 有効なトークンで新パスワードと確認を表示する。 | Save. | Use the token once before expiry and return to B13. |

- **Acceptance:** All `B16-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

### SCR-B-017 — B17 My Page / `マイページ`

- **Status:** `[CONFIRMED]`
- **Actor:** Buyer
- **Purpose:** Provide the Buyer account hub.
- **Main entry:** My Page bottom tab
- **Summary:** My Page is the Buyer account hub under the bottom navigation, linking to Order History, profile and password updates, Contact, and logout while keeping internal settlement data out of view.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `B17-01` | Member summary / `会員表示` | Show minimal identity only; no points, ranks, coupons, or favorites. | 氏名または会員識別子を最小限表示する。ポイント、ランク、クーポン、お気に入りは表示しない。 | Review the summary. | Display only. |
| `B17-02` | Menu / `メニュー` | Show Order History, Member Update, Password Change, Contact, and Logout. | 注文履歴、会員情報変更、パスワード変更、お問い合わせ、ログアウトを表示する。 | Select a menu item. | Open B11, B18, B19, B20, or show logout confirmation. |
| `B17-03` | Header / navigation / `ヘッダー／ナビゲーション` | Show title, Back, search/cart, or bottom navigation as appropriate. | 画面名、戻る、検索・カートまたは下部ナビを画面用途に応じて表示する。 | Tap Back or a navigation item. | Open the prior or selected root screen. Home alone has no Back action. |
| `B17-04` | Logout confirmation / `ログアウト確認` | Show confirmation before logout. | ログアウト前に確認を表示する。 | Confirm or cancel. | Confirm signs out and returns to Home/Login; cancel stays on My Page. |

- **Acceptance:** All `B17-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

### SCR-B-018 — B18 Member Information Update / `会員情報の変更`

- **Status:** `[CONFIRMED]`
- **Actor:** Buyer
- **Purpose:** Update the Buyer profile and default delivery address.
- **Main entry:** My Page (B17)
- **Summary:** Member Information Update lets an authenticated Buyer update only their own profile and default delivery address; a changed email remains pending until B15 verification.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `B18-01` | Profile fields / `登録情報` | Show name, phonetic name, email, phone, postal code, and address. | 氏名、フリガナ、メール、電話、郵便番号、住所を表示する。 | Edit and save. | Validate and update the authenticated Buyer. If email changes, store it as pending and send verification to the new email. |
| `B18-02` | Header / navigation / `ヘッダー／ナビゲーション` | Show title, Back, search/cart, or bottom navigation as appropriate. | 画面名、戻る、検索・カートまたは下部ナビを画面用途に応じて表示する。 | Tap Back or a navigation item. | Open the prior or selected root screen. Home alone has no Back action. |

- **Acceptance:** All `B18-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

### SCR-B-019 — B19 Password Change / `パスワード変更`

- **Status:** `[CONFIRMED]`
- **Actor:** Buyer
- **Purpose:** Allow a signed-in Buyer to change password.
- **Main entry:** My Page (B17)
- **Summary:** Password Change verifies the current password and policy, updates the signed-in Buyer’s password, and invalidates other sessions while keeping the current session active.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `B19-01` | Password fields / `パスワード入力` | Show current, new, and confirmation password fields. | 現在、新規、確認パスワードを表示する。 | Enter and save. | Verify current password and policy, update password, and invalidate other sessions except the current session. |
| `B19-02` | Header / navigation / `ヘッダー／ナビゲーション` | Show title, Back, search/cart, or bottom navigation as appropriate. | 画面名、戻る、検索・カートまたは下部ナビを画面用途に応じて表示する。 | Tap Back or a navigation item. | Open the prior or selected root screen. Home alone has no Back action. |

- **Acceptance:** All `B19-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

### SCR-B-020 — B20 Contact / `お問い合わせ`

- **Status:** `[CONFIRMED]`
- **Actor:** Buyer
- **Purpose:** Send a support inquiry.
- **Main entry:** My Page / Order Detail
- **Summary:** Contact supports both a simplified general BVI-support variant and an order-linked Producer-inquiry variant. The general variant derives Buyer identity from the authenticated session and collects only subject and inquiry content. The Producer variant derives identity and order context from the authenticated owned order and collects only the inquiry topic and message without requesting card data.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `B20-01` | General BVI inquiry / `BVIへのお問い合わせ` | When entered from My Page, show only required Subject and Inquiry Content fields. Do not show name, email, order-summary, or order-number inputs; derive the authenticated Buyer identity server-side. | マイページから開いた場合は、必須の「件名」と「お問い合わせ内容」のみを表示する。氏名、メールアドレス、注文情報、注文番号の入力欄は表示しない。 | Enter subject and inquiry content, then submit. | Validate the two fields, attach the authenticated Buyer identity server-side, and create one BVI-support inquiry without accepting a client-supplied identity. Delivery and response handling remain governed by TBD-005. |
| `B20-02` | Header / navigation / `ヘッダー／ナビゲーション` | Show title, Back, search/cart, or bottom navigation as appropriate. | 画面名、戻る、検索・カートまたは下部ナビを画面用途に応じて表示する。 | Tap Back or a navigation item. | Open the prior or selected root screen. Home alone has no Back action. |
| `B20-03` | Order-linked Producer inquiry / `生産者へのお問い合わせ` | When entered from B12, show a compact read-only summary of the selected Producer order. Preview up to two purchased products with thumbnail, name, and quantity; when additional products remain, summarize them as `ほかN点`. Then show the selected Producer and owned order number. Require one initially unselected radio-style issue choice: `商品について`, `配送・到着予定について`, `商品の不備・不足について`, or `その他`. Show one required message textarea, the inline helper `カード番号などの決済情報は入力しないでください。`, and the primary action `問い合わせを送信`. Do not repeat Buyer name/email fields in this variant. | 選択した生産者注文の商品を最大2件まで、商品画像・商品名・数量付きでコンパクトな変更不可の注文情報として表示し、残りは「ほかN点」で示す。続けて生産者名と注文番号を表示する。未選択状態の「商品について」「配送・到着予定について」「商品の不備・不足について」「その他」から1件を選択し、必須の「お問い合わせ内容」、決済情報を入力しない補足文、「問い合わせを送信」を表示する。 | Select one issue, enter a message, and submit. | Validate authenticated ownership and create one Producer-directed inquiry carrying the immutable order/Producer context. Delivery and response handling remain governed by TBD-005. |

- **Acceptance:** All `B20-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

### SCR-B-021 — B21 Contact Complete / `お問い合わせ完了`

- **Status:** `[CONFIRMED]`
- **Actor:** Buyer
- **Purpose:** Confirm inquiry receipt without duplication.
- **Main entry:** Contact (B20)
- **Summary:** Contact Complete confirms receipt with a reference number, provides a return to My Page, and prevents duplicate submission when the page is refreshed.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `B21-01` | Receipt / `受付結果` | Show receipt message and reference. | 受付メッセージと受付番号を表示する。 | Return to My Page. | Open B17; refresh does not resubmit. |

- **Acceptance:** All `B21-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

## 10. Producer Screen Specifications

### SCR-P-001 — P01 Producer Login / `生産者ログイン`

- **Status:** `[CONFIRMED]`
- **Actor:** Producer
- **Purpose:** Sign in with a Producer account separate from Buyer.
- **Main entry:** Producer Portal entry
- **Summary:** Producer Login authenticates the Producer and routes the account according to email verification and PAY.JP selling eligibility without exposing sensitive account state.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `P01-01` | Credentials / `ログイン情報` | Email address, password, and Login button. | メールアドレス、パスワード、ログインボタンを表示する。 | Enter credentials and sign in. | Authenticate with throttling and neutral errors; never disclose whether an account exists. |
| `P01-02` | Post-login routing / `ログイン後の遷移` | Show the appropriate next page for the account state. | アカウント状態に応じた次画面を表示する。 | Continue after successful authentication. | New registration accounts already have verified email. Open P04 while PAY.JP eligibility is incomplete, or P05 when both Visa and Mastercard are passed. Temporary registration attempts are not login accounts; resume them under section 6.4. |
| `P01-03` | Account actions / `アカウント操作` | Show Producer Registration and Password Reset links. | 生産者登録とパスワード再設定のリンクを表示する。 | Select an action. | Open P02 or P15. |

- **Acceptance:** All `P01-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

### SCR-P-002 — P02 Producer Registration / `生産者登録`

- **Status:** `[CONFIRMED]`
- **Actor:** Producer Applicant
- **Purpose:** Create a Producer account with the minimum internal profile and accept the Producer Terms.
- **Main entry:** Producer Login (P01)
- **Summary:** P02-1 collects email only and sends a six-digit email OTP through P03. P02-2 requires a server-verified registration session, shows persistent verification success and read-only email, and collects the minimum marketplace profile, password, and Producer Terms consent. No Producer account exists before final submission; PAY.JP's full business and bank form is not duplicated here.
- **P02-2 presentation (user-approved refinement, updated 2026-09-09):** Show compact three-step registration progress with email entry and verification completed and details current; it describes account registration only, not PAY.JP eligibility. Use `アカウントを作成` (Create your account) as the heading. Keep the header limited to the progress indicator and heading. Under `生産者情報` (Producer information), place the verified email directly below the shop-profile-photo uploader and above the shop/farm-name field. Present `メールアドレス` (Email address) as a normal external field label and show the server-bound email in a full-width 48 px disabled/read-only input-style row with `確認済み` (Verified) on the right; the email is not a required editable field and therefore has no asterisk. Group password fields under `パスワード設定` (Password setup); retain password confirmation and use eye-icon visibility controls with accessible Show/Hide names. Use `アカウントを作成する` (Create account) for the final action. Keep the empty state concise: omit the introductory form subtitle, input placeholders, post-creation helper, and existing-account prompt; retain a standalone `ログイン` (Login) link.
- **Producer registration desktop shell (user-approved, 2026-09-08):** P02-1, P03, and P02-2 use a 1280×800 desktop frame with a 448 px branding panel and 832 px form panel. Preserve the existing branding content and green visual identity. P02-2 uses a centered 640 px form column; P02-1 and P03 retain their existing narrower form/card content. Keep the user-visible progress model at three steps across all three states.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `P02-01` | Account / `アカウント` | P02-1: email only and Send code. P02-2: read-only verified email, password, and password confirmation. | メールアドレス／確認コードを送信する。確認後はメールアドレスを読み取り専用で表示し、パスワードと確認用パスワードを入力する。 | Request OTP first; enter credentials after verification. | Validate email format and use neutral duplicate-account responses. Do not collect profile/password before verification. Validate password policy at final submission and take email from the verified server record, never a client-supplied replacement. |
| `P02-02` | Minimum profile / `最小プロフィール` | Shop/farm name, contact-person name, and phone number. | ショップ名・農園名、担当者名、電話番号を入力する。 | Enter the marketplace profile. | Store the internal Producer profile; do not collect PAY.JP business documents or bank details. |
| `P02-03` | Producer Terms / `生産者利用規約` | Show and link the current Producer Terms. The terms include the Company's 10% commission, but the registration checkbox uses a concise terms-consent label rather than displaying the percentage inline. | 現行の生産者利用規約へのリンクと同意欄を表示する。規約には会社手数料10%を含めるが、登録チェックボックスには割合を直接表示せず、簡潔な規約同意文言を使用する。 | Review and accept the terms. | Require consent and record the accepted version and timestamp. |
| `P02-04` | Register / `登録` | Show the Register action and Login link on P02-2. | 登録ボタンとログインリンクを表示する。 | Submit registration or return to Login. | Require a valid verified-registration session, validated profile/password, and terms consent; atomically create an email-verified account and consume the grant. Establish a fresh authenticated Producer session and open P04; do not send another verification email. Retain only safe input on validation failure. |
| `P02-05` | Verified entry and recovery / `確認済み状態・復帰` | Persistent success feedback, read-only verified email, and actionable session-expiry recovery. | メールアドレスを確認しました。 | Continue with details; reverify if the session expires. | Apply section 6.4 and SEC-013 on refresh, direct URL/API requests, expiry, and email changes. No additional success confirmation click is required. |
| `P02-06` | Required shop profile photo / `ショッププロフィール写真` | Required single-photo upload above the Producer/farm name in P02-2. This is a public shop profile photo, not an identity document. On mobile, show a short note directly with the photo field explaining its Buyer-facing use. | ショッププロフィール写真 *。`登録した写真は、購入者向けの商品一覧にショップ画像として表示されます。` | Activate the circular photo control to select a photo; preview, replace, or remove the selection. | Require a successfully uploaded, server-validated photo before account creation. Missing, removed, uploading, or failed uploads must not satisfy the requirement; show an actionable field error/retry. Bind temporary uploads to the verified registration session and associate only its own valid photo with the created Producer profile. Expose the stored photo only as the public shop image in Buyer product-list experiences. Allowed formats, size, and image-processing policy remain TBD-003. |

- **Acceptance:** All `P02-*` rows and section 6.4 are enforced together; pre-account access is scoped to the temporary registration session, not an assumed authenticated Producer. Navigation remains within the final 47-screen inventory.
- **P02-06 presentation (user-confirmed, updated 2026-09-09):** In desktop P02-2, show `ショッププロフィール写真 *` (Shop profile photo, required) as the field label above one 112 px circular photo-picker control. The entire circle is one interaction target; its empty state contains the existing neutral image placeholder and one 32 px green camera badge at the bottom-right to communicate the upload action. The badge is part of the same control, not a second focusable action. Remove the separate outlined `写真を追加` (Add photo) button and any duplicate action link. When a photo is selected, it fills the same circular bounds and the retained camera badge indicates replacement. Use accessible names equivalent to `ショッププロフィール写真を追加` (Add shop profile photo) and `ショッププロフィール写真を変更` (Change shop profile photo), and support conventional keyboard activation. The circular display does not determine the stored image format or crop-processing policy; those remain TBD-003.
- **P02-06 mobile visibility note (user-confirmed, 2026-09-18):** Mobile P02-2 places `登録した写真は、購入者向けの商品一覧にショップ画像として表示されます。` directly below the photo picker. This is informational text, not a separate checkbox or consent action. Desktop is unchanged by this mobile-only presentation refinement.
- **P02-2 required-field convention (user-confirmed, updated 2026-09-09):** Mark every required input and consent label consistently with a trailing muted-red asterisk (`*`). Apply this to shop profile photo, shop/farm name, contact-person name, phone number, password, password confirmation, and Producer Terms consent. Do not show an explanatory legend such as `* は必須項目です`, and do not mix `（必須）` or `· 必須` with the asterisk convention.
- **P02-01 password policy and guidance (user-confirmed, 2026-09-09; resolves TBD-002):** Producer passwords are 8–64 characters and must contain at least one ASCII uppercase letter (`A-Z`), one ASCII lowercase letter (`a-z`), one ASCII digit (`0-9`), and one non-alphanumeric, non-whitespace special character. Preserve the exact submitted value without trimming, and require confirmation to match it exactly. Apply the same policy to P02-01, P14-01, and P15-02. Retain eye-icon visibility controls. Show one muted 12 px line directly below `パスワード設定`: `8〜64文字で、大文字・小文字・数字・記号をそれぞれ1文字以上含めてください。` Do not add a rule card, checklist, strength meter, or reserved empty-state error space.
- **P02-01 password validation copy:** Show field-specific messages only after validation: length `8〜64文字で入力してください。`; composition `大文字・小文字・数字・記号をそれぞれ1文字以上含めてください。`; confirmation mismatch `パスワードが一致しません。`. Never include the password value in logs, analytics, URLs, or generic error messages.
- **P02 photo coverage under AT-P-002:** Test missing/removed photo, upload failure/retry, successful preview/replacement, server rejection of invalid or another session's upload, and successful account creation with its validated shop photo. Apply format/size tests after TBD-003 approval. The current design update covers the initial required-upload state; selected, uploading, and error variants remain to be drawn.

### SCR-P-003 — P03 Email Verification / `メール確認`

- **Status:** `[CONFIRMED]`
- **Actor:** Producer Applicant / Producer
- **Purpose:** Confirm ownership of a new or changed Producer login email.
- **Main entry:** P02-1 after requesting an OTP; existing P12 email-change verification link for that separate flow.
- **Summary:** Initial registration uses a six-digit email OTP before account creation. P12 email changes retain their existing expiring one-time link mechanism and keep the old login email active until verification. This change does not convert Buyer verification or password reset to OTP.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `P03-01` | Verification / `メール確認` | Registration: destination email, single six-digit code input, Verify and continue, verifying, invalid/expired code, attempt-limit, and retry states. P12: retain success/expired/used/invalid-link states. | 確認コードを入力／確認コード（6桁）／確認して次へ進む。 | Submit the registration OTP; for P12 open its verification link. | Verify the purpose-bound, unexpired, unused challenge server-side. Initial OTP success issues restricted registration permission and automatically opens P02-2, not P04. P12 verification returns to P12. |
| `P03-02` | Resend / `再送` | Resend and Change email for incomplete registration verification; show delivery/retry feedback. | 確認コードを再送する／メールアドレスを変更する。 | Request another code or return to P02-1 to change email. | Invalidate the previous usable challenge and apply request, resend, and verification limits. Registration sends a fresh expiring OTP; P12 retains its one-time link. Email change invalidates prior verification. Numeric policies: TBD-001. |
| `P03-03` | Email-change protection / `メール変更保護` | Show that the previous login email remains active until confirmation. | 確認完了までは旧ログインメールが有効であることを表示する。 | Verify the new email or cancel the change. | Do not replace the login email until verification succeeds; cancellation keeps the original email. |

- **Acceptance:** All `P03-*` rows, section 6.4, and SEC-013 are enforced together. Initial registration is scoped to its temporary session; P12 email changes remain scoped to the existing account. Navigation remains within the final 47-screen inventory.

### SCR-P-004 — P04 PAY.JP Application / Screening Status / `PAY.JP申請・審査状況`

- **Status:** `[CONFIRMED]`
- **Actor:** Verified Producer Applicant
- **Purpose:** Complete the PAY.JP hosted application and track card-brand screening before selling.
- **Main entry:** Completed Producer Registration (P02-2) / Producer Login (P01)
- **Summary:** This screen creates the PAY.JP Tenant when required, issues and opens PAY.JP's five-minute one-use Hosted Form URL, and displays authoritative card-brand screening results; a browser return alone never proves approval.
- **Primary Japanese page title:** `販売開始の準備` (Preparation to Start Selling). The logical screen name remains PAY.JP Application / Screening Status.
- **[CONFIRMED; P04-04, BR-007, AT-P-004] Payment logos:** Desktop/mobile In Review includes the user-supplied seven-brand strip under `PAY.JPが対応する決済方法` (Payment methods supported by PAY.JP), with `利用可否は審査結果・設定により異なります。` (Availability depends on screening results and configuration). This is provider-level information, not a declaration that all methods are enabled for this marketplace or Producer. Do not give Apple Pay a fabricated card-brand screening result or change the Visa/Mastercard eligibility rule. Provider source: https://help.pay.jp/ja/articles/3438175 .
- **[CONFIRMED; P04-01, P04-04, AT-P-004] In Review mobile layout:** Use a content-height card with 16px spacing between the explanation, last-update time, and availability notice. Keep the review badge but omit the redundant `申請状況：審査中` line and any hidden-action space. The top edge aligns with other P04 state cards; their heights need not match. No new action or state transition is introduced.
- **[CONFIRMED; P04-02, P04-03, P04-05] Mobile state refinement:** Keep Not Applied, In Review, and Declined. Remove the standalone Link Error screen; show any link-generation/opening failure as recoverable guidance in the current screen. The declined-state primary action is `再申請する` (Reapply), with `PAY.JPの申請フォームを開き、内容を確認・修正して再申請してください。` beneath it. Request a fresh hosted application URL for reapplication where permitted by PAY.JP; do not fabricate approval or override provider restrictions. This change updates the mobile design only.
- **Pre-eligibility shell:** Do not show a sidebar. Show a full-width authenticated header with `みのりくん`, `生産者ポータル`, the Producer/farm name, and an avatar account trigger. Selecting the avatar or farm name opens an account dropdown containing `プロフィール` (Profile) and `ログアウト` (Logout).

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `P04-01` | Overall eligibility / `全体資格` | Show Not Started, Application Required, or In Review while the Producer is not yet eligible to sell. | 販売資格取得前は、未開始、申請必要、審査中を表示する。 | Review the current state. | Enable selling only when both Visa and Mastercard are passed; no internal Company approval is added. As soon as both brands are passed, redirect the current authenticated session to P05. On every later login or authenticated entry, open P05 directly. Do not render a standalone Eligible-complete P04 state or keep `販売開始の準備` in the full sidebar. |
| `P04-02` | Hosted application / `Hosted申請` | Show `Start Application` or the state-appropriate continuation action. | 状態に応じて「申請を開始する」または申請続行の操作を表示する。 | Generate and open the application. | Create the Tenant when absent, call POST /v1/tenants/:id/application_urls, append the configured return_to, and open the returned URL without constructing its host. |
| `P04-03` | Application URL / `申請URL` | Do not expose the five-minute/one-use URL detail in the normal UI. Generate a fresh URL when the Producer acts and show only safe retry guidance if the link is invalid or expired. | 通常画面では5分間・1回限りという技術的なURL条件を表示しない。生産者の操作時に新しいURLを発行し、無効・期限切れの場合は安全な再試行案内のみ表示する。 | Open immediately or request reissue. | Treat access, expiry, or a newer issued URL as invalidating the prior URL; safely retry generation on failure. |
| `P04-04` | Brand screening / `ブランド別審査` | Show passed, in_review, or declined for each card brand and any available date. Do not show a manual screening-status refresh button. | 各ブランドのpassed、in_review、declinedと利用可能日を表示する。審査状況の手動更新ボタンは表示しない。 | Review the status and return later when it is still in progress. | Load the latest authoritative state when the page opens and update from PAY.JP API/webhook data; the Hosted Form browser return changes no approval state. |
| `P04-05` | Correction and support / `修正・サポート` | Show reissue/support guidance when more information is requested or the process cannot continue. | 追加情報が必要な場合や進行不能時に再発行・サポート導線を表示する。 | Reissue the form or contact support. | Do not invent a correction reason that PAY.JP does not provide and do not expose sensitive review details. |
| `P04-06` | Restricted access / `利用制限` | Before eligibility, do not show a sidebar. Show the account dropdown from the header with only `プロフィール` and `ログアウト`; keep the selling-availability explanation in the main P04 content. | 販売資格取得前はサイドバーを表示しない。ヘッダーのアカウントメニューには「プロフィール」と「ログアウト」のみを表示し、販売機能が審査後に利用可能になる案内はP04のメインコンテンツ内に表示する。 | Select Profile to open the signed-in Producer's P12 information, or select Logout and confirm before ending the session. | Permit P04 and P12 before eligibility; deny every selling, order, payout, bank, password, and full account-settings destination through both navigation and direct requests. Immediately after eligibility is confirmed, and on every later authenticated entry, open P05 with the full Producer sidebar; the same header account dropdown remains available. |

- **Acceptance:** All `P04-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

### SCR-P-005 — P05 Producer Dashboard / `生産者ダッシュボード`

- **Status:** `[CONFIRMED]`
- **Actor:** Eligible Producer
- **Purpose:** Provide the Producer's landing page and own operational summaries.
- **Main entry:** Producer Login (P01)
- **Summary:** The Dashboard is the eligible Producer's landing page, showing read-only summaries of own products, orders requiring action, sales, and payout alerts with direct links to the relevant Producer screens.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `P05-01` | Summary cards / `サマリーカード` | Show own product counts, Received/Processing orders, period sales, payout alerts, and a short list of orders requiring action. | 自分の商品数、受付・対応中注文、期間売上、振込注意事項、対応が必要な注文の一覧を表示する。 | Select a summary card, `注文一覧を見る`, or an individual order row. | Product, order-summary, and sales/payout cards open P06, P08, and P10 respectively. `注文一覧を見る` opens P08; an individual order row opens its P09 Order Detail. Do not show a second button that duplicates the P08 order-list action. Use only the signed-in Producer's data. |
| `P05-02` | Primary navigation / `主要ナビゲーション` | Show Dashboard, Product Management, Order Management, and Sales/Payout in the operational sidebar. Do not repeat the signed-in account or farm identity in a sidebar footer. | 運用サイドバーにはダッシュボード、商品管理、注文管理、売上・振込を表示し、ログイン中のアカウント名・農園名をフッターで重複表示しない。 | Select an operational destination, or use the header account dropdown for account actions. | Open P05, P06, P08, or P10 from the sidebar. The header account dropdown opens P11 or Logout; account identity and account actions remain in the header rather than the sidebar footer. |
| `P05-03` | Eligibility and ownership / `資格・所有権` | Show a safe restriction notice when selling eligibility is lost or another Producer identifier is requested. | 販売資格喪失時や他生産者ID指定時に安全な制限案内を表示する。 | Return to the permitted destination. | Block new selling actions and deny cross-Producer access server-side while preserving required own order/payout review. |

- **Acceptance:** All `P05-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

### SCR-P-006 — P06 Producer Product List / `生産者商品一覧`

- **Status:** `[CONFIRMED]`
- **Actor:** Eligible Producer
- **Purpose:** Manage only the signed-in Producer's products.
- **Main entry:** Producer Dashboard (P05)
- **Summary:** Producer Product List shows only the signed-in Producer's products and provides filters, create/edit navigation, and immediate publish or unpublish actions after validation. Mobile uses a stacked vertical list/card presentation and never requires horizontal table swiping.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `P06-01` | Product list / `商品一覧` | Show image, name, category, tax-inclusive base product price, stock, promotion, and Published/Unpublished state. Desktop may use a table; mobile uses vertically stacked full-width rows/cards with no horizontal scrolling. | 画像、商品名、カテゴリ、税込の商品価格、在庫、プロモーション、公開・非公開状態を表示する。モバイルでは横スクロールを使用せず、縦に並ぶ全幅の行／カードで表示する。 | Select a product. | Open P07 with the Producer-owned product only. |
| `P06-02` | Filters and empty state / `絞り込み・空状態` | Filter by keyword, category, publication state, and stock state; show a recovery action when empty. | キーワード、カテゴリ、公開状態、在庫状態で絞り込み、0件時は回復導線を表示する。 | Change filters or create a product. | Refresh only the Producer's list or open P07 for creation. |
| `P06-03` | Publication / `公開操作` | Show Publish/Unpublish for valid own products. | 入力済みの自分の商品に公開・非公開を表示する。 | Change publication state. | Validate ownership, eligibility, required product data, price, and stock before updating. |
| `P06-04` | Access control / `アクセス制御` | Show a generic error for another Producer's product identifier or concurrent update. | 他生産者の商品IDまたは競合更新時に一般的なエラーを表示する。 | Reload or return to the list. | Deny cross-Producer access server-side and require re-edit using current values after a conflict. |

- **Acceptance:** All `P06-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

### SCR-P-007 — P07 Product Create / Edit / `商品作成・編集`

- **Status:** `[CONFIRMED]`
- **Actor:** Eligible Producer
- **Purpose:** Create or edit complete product selling information on one screen.
- **Main entry:** Producer Product List (P06)
- **Summary:** Product Create / Edit combines product information, Admin-created category, images, tax-inclusive base product price, stock, Producer-funded promotion, three regional delivery fees, and publication state in one owned-product form while preserving historical OrderItem snapshots. The UI does not expose product types, variations, option rows, package size, weight, or carrier selection.

- **[CONFIRMED; P07-03, P07-04, P07-07, BR-019, AT-P-007] Regional delivery fees:** Both mobile Create and Edit show `商品価格（税込）` as the product amount before delivery, followed by required non-negative JPY fee inputs for `本州・四国・九州`, `北海道`, and `沖縄`. Show a read-only Buyer-price preview that adds the selected region's delivery fee to the base product price, applies any Producer-funded percentage discount to that complete amount, and labels the result tax/shipping inclusive. This preserves the confirmed full-price promotion basis while making the regional fee visible to the Producer. Do not add package size, weight, carrier, automatic carrier quotation, or carrier-selection inputs. Edit preloads the saved product price, stock, discount, and three delivery fees.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `P07-01` | Core information / `基本情報` | Product name, description, and one enabled Admin-created category; Producers cannot create categories. | 商品名、説明、有効な管理者作成カテゴリを入力する。生産者はカテゴリを作成できない。 | Enter or edit values. | Validate required and safe text while retaining valid input on error. |
| `P07-02` | Images / `画像` | Multiple product images, display order, and a per-image remove action using an X icon; do not show alternative-text inputs or an overflow menu. | 複数の商品画像と表示順を設定し、各画像には削除用の×アイコンを表示する。代替テキスト入力およびオーバーフローメニューは表示しない。 | Add, reorder, or remove images. | Validate file type, size, and safety; reject only the affected image. |
| `P07-03` | Selling information / `販売情報` | Show one required tax-inclusive base product price before delivery and one required stock quantity. Do not show `単一商品`, `種類あり`, a type name, option rows, or add/delete variation controls. The system maintains exactly one internal default sellable option for the product. | 必須の「商品価格（税込）」と在庫数を1つずつ表示する。「単一商品」「種類あり」、種類名、選択肢行、バリエーション追加・削除操作は表示しない。 | Enter or edit price and stock. | Reject missing or negative values, preserve other valid input, use the internal default sellable option as the authoritative base price/stock record, and block publication until errors are corrected. |
| `P07-04` | Product discount / `商品割引` | Set one optional percentage discount. Do not show a target selector or start/end dates. Blank or zero means no discount; the Producer bears the discount. The percentage applies to the complete amount formed by the base product price plus the applicable regional delivery fee. | 任意の割引率を1つ設定する。対象選択および開始日・終了日は表示しない。未入力または0は割引なしとし、商品価格と該当地域の送料を合算した金額に割引を適用する。 | Enter, update, or clear the discount percentage. | Accept only a valid percentage, calculate it from the authoritative base product price plus the applicable regional delivery fee, and keep the Producer-funded discount separate from the Company's fixed 10% commission. |
| `P07-05` | Save and publication / `保存・公開` | Show Save Draft, Publish, or Unpublish. | 下書き保存、公開、非公開を表示する。 | Save the complete form. | Verify eligibility and ownership; save current catalog data without altering existing OrderItem snapshots, then return to P06. |
| `P07-06` | Concurrency and ownership / `競合・所有権` | Show a safe error for stale data or another Producer's product ID. | 古いデータまたは他生産者の商品IDに安全なエラーを表示する。 | Reload or cancel. | Deny cross-Producer access and require re-edit from the current version after a conflict. |
| `P07-07` | Regional delivery fees / `地域別送料` | Show three required JPY inputs: Honshu/Shikoku/Kyushu, Hokkaido, and Okinawa, plus a read-only one-unit tax-and-shipping-inclusive Buyer-price preview. Do not request municipality, package size, weight, or carrier. | 必須の「本州・四国・九州」「北海道」「沖縄」の送料入力と、変更不可の1点分の購入者向け税込・送料込み価格プレビューを表示する。市区町村、荷物サイズ、重量、配送業者は入力しない。 | Enter or edit each regional fee. | Validate non-negative whole JPY amounts, store all three fees with the product, and select the applicable fee from the order delivery prefecture. Guest/pre-address presentation remains TBD-006; multi-item aggregation remains TBD-009. |

- **Acceptance:** All `P07-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

### SCR-P-008 — P08 Producer Order List / `生産者注文一覧`

- **Status:** `[CONFIRMED]`
- **Actor:** Producer
- **Purpose:** Process Producer sub-orders containing only the Producer's products.
- **Main entry:** Producer Dashboard (P05)
- **Summary:** Producer Order List shows only the signed-in Producer's orders, supports operational filtering, and links to manual fulfillment handling without exposing other Producers' order lines. Mobile uses stacked vertical order cards with no horizontal table swiping.

- **[CONFIRMED; P08-01, P08-03, BR-010, AT-P-008] Mobile cancellation note:** Below the filters and above the vertical order list, show a persistent pale notice reading `購入者は注文完了後30分以内であれば、注文をキャンセルできます。` (Buyers can cancel within 30 minutes after order completion). This explains the existing cancellation rule and introduces no Producer cancellation action, countdown, or mandatory fulfillment delay.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `P08-01` | Producer order list / `生産者注文一覧` | Show order reference, order date, own items/quantities, and operational status. Desktop may use a table; mobile uses vertically stacked full-width order cards with no horizontal scrolling. During the cancellation window show the received state; when 30 minutes elapse without cancellation and fulfillment has not advanced, show the system-controlled `注文確定`. | 注文番号、注文日、自分の商品・数量、対応状態を表示する。モバイルでは横スクロールを使用せず、縦に並ぶ全幅の注文カードで表示する。キャンセル可能期間終了後、未キャンセルかつ配送対応が進んでいない場合はシステム管理の「注文確定」を表示する。 | Select a row. | Open P09 only when the order belongs to the signed-in Producer. |
| `P08-02` | Filters / `絞り込み` | Filter by fulfillment state and by order period: All time, last 30 days, last 90 days, last 12 months, calendar year, or Custom date range. Show Start and End date fields only when Custom date range is selected. | 配送対応状態、およびすべての期間、過去30日、過去90日、過去12か月、暦年、または指定期間で絞り込む。指定期間を選択した場合のみ開始日と終了日を表示する。 | Select state and period filters, enter a valid custom range when applicable, then apply or reset. | Refresh the Producer's own sub-orders for the applied filters; show a clear empty state when none match. |
| `P08-03` | Status ownership / `状態の管理主体` | Show Producer-editable fulfillment and system-controlled order states distinctly using text, not color alone. `注文確定` is system-controlled and is not a P09 dropdown option. | 生産者更新の配送対応状態と、システム管理の注文状態を文字で区別して表示する。「注文確定」はシステム管理とし、P09の選択肢には含めない。 | Review the state. | Allow manual forward or backward fulfillment changes among Received, Processing, and Shipped; automatically present `注文確定` after the 30-minute cancellation deadline when applicable; cancellation/refund remains system-controlled. |
| `P08-04` | Access control / `アクセス制御` | Show a generic error for another Producer's sub-order ID. | 他生産者のサブ注文IDには一般的なエラーを表示する。 | Return to the list. | Deny cross-Producer access server-side. |

- **Acceptance:** All `P08-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

### SCR-P-009 — P09 Producer Order Detail / `生産者注文詳細`

- **Status:** `[CONFIRMED]`
- **Actor:** Producer
- **Purpose:** Review own order lines and minimum delivery information, then manually update fulfillment.
- **Main entry:** Producer Order List (P08)
- **Summary:** Producer Order Detail displays only the Producer's purchase-time item snapshots and the recipient information required for delivery; the Producer is responsible for shipment and manually updates the allowed fulfillment state.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `P09-01` | Owned order lines / `自分の注文明細` | Show purchase-time product, quantity, unit price, and promotion for the Producer's lines only. | 自分の明細だけについて購入時の商品、数量、単価、プロモーションを表示する。 | Review the items. | Use immutable OrderItem snapshots and hide other Producers' items and internal payment/commission data. |
| `P09-02` | Delivery information / `配送情報` | Show only recipient name, delivery address, and phone required for fulfillment. | 配送に必要な受取人名、配送先住所、電話番号だけを表示する。 | Use the information for delivery. | Prohibit unrelated use, bulk export, or display beyond the owning Producer. |
| `P09-03` | Manual fulfillment status / `手動配送状態` | Show the current state and a dropdown containing Received, Processing, and Shipped. | 現在状態と、受付・対応中・発送済みを選択できるドロップダウンを表示する。 | Select a different Producer-controlled state and confirm the update. | Permit manual forward or backward changes among Received, Processing, and Shipped; reflect the result to the Buyer order view and audit the change. Cancellation and refund remain system-controlled and are not selectable. |
| `P09-04` | Blocked transition / `更新不可` | Show a safe message when cancellation/refund is active, the transition is invalid, or ownership fails. | キャンセル・返金処理中、無効遷移、所有権不一致時に安全な案内を表示する。 | Return or reload. | Do not update the state and deny cross-Producer access server-side. |

- **Acceptance:** All `P09-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

### SCR-P-010 — P10 Payout / `振込`

- **Status:** `[CONFIRMED]`
- **Actor:** Producer
- **Purpose:** Review own sales, settlement calculation, payout history, and documents in one read-only responsive feature.
- **Main entry:** Producer Dashboard (P05)
- **Summary:** Payout combines period sales, detailed calculation, payout history, status, and statement/invoice downloads using PAY.JP Term/Statement/Balance data reconciled with internal order and refund records. Desktop updates the detail region when a history row is selected. Mobile keeps the overview/list concise and opens the selected payout in a detail subview within the same logical P10 feature; Back returns to the overview without losing list context.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `P10-01` | Period summary / `期間サマリー` | Show gross sales, Producer-funded promotion impact, refunds/cancellations, Company's 10% commission, PAY.JP fees, other adjustments, and net payout. | 総売上、生産者負担プロモーション、返金・取消、会社手数料10％、PAY.JP手数料、その他調整、振込額を表示する。 | Select a period. | Recalculate the read-only view from the Producer's reconciled data and clearly distinguish provisional from finalized amounts. |
| `P10-02` | Payout history / `振込履歴` | Show period, net amount, due/paid date, status, carry-forward amount, and payout reference. | 期間、振込額、予定・実行日、状態、繰越額、振込参照番号を表示する。 | Select a history row. | On desktop, update the detail region on the same page. On mobile, navigate to the selected payout's detail subview within P10 and provide explicit Back navigation that preserves overview/list context. Do not create a separate settlement module or inventory screen. |
| `P10-03` | Detailed breakdown / `詳細内訳` | Show the components and related own orders/refunds supporting the selected payout. | 選択した振込を構成する項目と関連する自分の注文・返金を表示する。 | Review details. | Trace values to the Producer's records without providing any edit action. |
| `P10-04` | Statements and invoices / `明細・適格請求書` | Show available PAY.JP statement, balance, and qualified-invoice documents. | 利用可能なPAY.JP Statement、Balance、適格請求書を表示する。 | Request a download. | Generate a temporary authorized PAY.JP download URL on demand; do not persist it as a permanent public link. |
| `P10-05` | Payout state and bank / `振込状態・口座` | Show payout state and masked destination account with a link to P13. | 振込状態、マスク済み振込先口座、P13へのリンクを表示する。 | Review or open bank information. | Reflect PAY.JP minimum/carry-forward and failure/hold information while keeping the financial calculation read-only. |
| `P10-06` | Ownership and integrity / `所有権・整合性` | Show a generic error for another Producer's payout identifier or unavailable authoritative data. | 他生産者の振込IDまたは正式データ取得不可時に一般的なエラーを表示する。 | Return or retry later. | Deny cross-Producer access and never allow editing commission, refunds, adjustments, net payout, or payout state. |

- **Acceptance:** All `P10-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

### SCR-P-011 — P11 Producer Account Settings / `生産者アカウント設定`

- **Status:** `[CONFIRMED]`
- **Actor:** Producer
- **Purpose:** Provide one hub for Producer account-related settings, confirmed shop-profile-photo updates, and logout.
- **Main entry:** Header account dropdown after selling eligibility
- **Summary:** Producer Account Settings is the account hub for the signed-in Producer's shop-profile photo, profile/contact information, payout bank information, password change, and logout. The avatar itself is the photo picker; a selected candidate is previewed locally and must be confirmed before upload. Logout is a confirmation action rather than a separate screen.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `P11-01` | Account menu / `アカウントメニュー` | Show Producer Account Information, Payout Bank Account Information, and Password Change. | 生産者アカウント情報、振込口座情報、パスワード変更を表示する。 | Select a menu item. | Open P12, P13, or P14. |
| `P11-02` | Account summary / `アカウント概要` | Show minimal Producer identity and masked login email. | 生産者の最小限の本人情報とマスク済みログインメールを表示する。 | Review the summary. | Display only the signed-in Producer's information. |
| `P11-03` | Logout confirmation / `ログアウト確認` | Show a confirmation dialog when Logout is selected. | ログアウト選択時に確認ダイアログを表示する。 | Confirm or cancel. | Confirm ends the Producer session and opens P01; cancel stays on P11. No Logout detail sheet is created. |
| `P11-04` | Shop profile photo / `ショッププロフィール写真` | Show the current shop-profile photo as a circular avatar with one integrated camera/edit affordance; do not add a separate upload button. | 現在のショッププロフィール写真を円形で表示し、同一コントロール内にカメラ／編集アイコンを表示する。 | Activate the avatar, select a local photo, then confirm or cancel the preview. | Selection opens the platform file picker and creates a local candidate preview only. Show `プロフィール写真を変更` with `この写真をプロフィールに設定しますか？`; `変更する` starts upload and persists the new photo only after server success, while `キャンセル` discards the candidate and stays on P11. Keep the previous photo on cancellation or upload failure and provide retry feedback. Apply format, size, crop, and processing rules only after TBD-003 is resolved. |

- **Acceptance:** All `P11-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

### SCR-P-012 — P12 Producer Account Information / `生産者アカウント情報`

- **Status:** `[CONFIRMED]`
- **Actor:** Producer
- **Purpose:** Review and update the Producer's internal profile, contact information, and login email on one screen.
- **Main entry:** Header Profile action from P04 before selling eligibility / Producer Account Settings (P11) after eligibility
- **Summary:** Producer Account Information displays and edits the Producer/farm name, representative/contact information, phone, and login email on one screen; changing the login email reuses P03 verification while the old email remains active. Shop-profile-photo management belongs only to P11 after registration. Before eligibility, the header Profile action opens P12 directly. After eligibility, the header account dropdown opens P11 and P12 is reached from that account hub.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `P12-01` | Producer and contact information / `生産者情報` | Producer/farm name, representative/contact name, and phone number. | 生産者・農園名、代表者・担当者名、電話番号を表示する。 | Review or edit permitted fields. | Validate required formats and save only to the signed-in Producer's internal profile. Shop-profile-photo controls are not shown on P12. |
| `P12-02` | Login email / `ログインメール` | Show the current login email and any pending unverified replacement. | 現在のログインメールと未確認の変更候補を表示する。 | Enter a new email or cancel a pending change. | Check uniqueness safely, send P03 verification, and keep the old login email active until verification succeeds. |
| `P12-03` | Save and feedback / `保存・結果` | Show Save and validation feedback on the same page. | 同一画面に保存と入力結果を表示する。 | Save changes. | Retain safe valid input on error, record an audited profile-change event, and remain on P12 after completion. |
| `P12-04` | Access control / `アクセス制御` | Show a generic error for another Producer's identifier. | 他生産者IDには一般的なエラーを表示する。 | Return to P11. | Deny cross-Producer read and update server-side. |

- **Acceptance:** All `P12-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

### SCR-P-013 — P13 Payout Bank Account Information / `振込口座情報`

- **Status:** `[CONFIRMED]`
- **Actor:** Producer
- **Purpose:** Review masked payout-bank data and update it securely on the same screen.
- **Main entry:** Producer Account Settings (P11) / Payout (P10)
- **Summary:** Payout Bank Account Information shows the PAY.JP payout destination only in masked form and, after re-authentication, edits it on the same screen through the PAY.JP Tenant API without logging raw bank values.

- **[CONFIRMED; P13-02, P13-03, AT-P-013] Prefilled mobile edit:** After successful re-authentication, populate the protected edit form with the current account's available bank name, branch, account type, account number, and holder name. Use normal input-value text styling rather than placeholder styling. Show `登録済みの口座情報を表示しています。変更する項目を編集してください。` (Existing account information is shown; edit the fields you want to change). Preserve untouched values and keep the ordinary overview masked. Never reconstruct full values from masked data; if a provider field is unavailable, retain the existing value through a supported unchanged-field flow or request replacement explicitly. Figma example account number 1234821 and holder ヤマダ タロウ are synthetic, not recovered bank data. Verify ownership, re-authentication, prefill, unchanged-field handling, cancellation, and failure preservation under AT-P-013.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `P13-01` | Masked account and status / `マスク済み口座・状態` | Show masked bank, branch, account type, account number, holder name, and PAY.JP bank status. | 銀行、支店、口座種別、口座番号、名義、PAY.JP口座状態をマスク表示する。 | Review the account. | Retrieve only the owning Tenant's data and never show raw stored account values in ordinary view. |
| `P13-02` | Start edit / `変更開始` | Show Change Bank Account. | 口座変更を表示する。 | Begin the change. | Require recent re-authentication before revealing editable bank fields on the same screen. |
| `P13-03` | Bank update / `口座更新` | Show the bank fields accepted by the PAY.JP Tenant API only during the protected edit state. | 保護された編集状態の間だけPAY.JP Tenant API対応口座項目を表示する。 | Enter, confirm, and submit new values. | Validate locally, send directly to PAY.JP, avoid raw-value logs/local persistence, and refresh masked data/status after success. |
| `P13-04` | Failure and audit / `失敗・監査` | Show safe retry/support guidance for authentication, validation, API, or bank-status failure. | 認証、入力、API、口座状態エラー時に安全な再試行・サポート案内を表示する。 | Correct, retry, or cancel. | Keep the previous account unchanged on failure and record an audit event containing no raw bank values. |
| `P13-05` | Access control / `アクセス制御` | Show a generic error for another Producer/Tenant identifier. | 他生産者・Tenant IDには一般的なエラーを表示する。 | Return to P11. | Deny cross-Producer access server-side. |

- **Acceptance:** All `P13-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

### SCR-P-014 — P14 Producer Password Change / `生産者パスワード変更`

- **Status:** `[CONFIRMED]`
- **Actor:** Signed-in Producer
- **Purpose:** Change the Producer password securely from Account Settings.
- **Main entry:** Producer Account Settings (P11)
- **Summary:** Producer Password Change verifies the current password, validates and confirms the new password, protects the session, and returns to Account Settings after a successful change.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `P14-01` | Password form / `パスワードフォーム` | Current password, new password, and confirmation. | 現在のパスワード、新パスワード、確認用パスワードを表示する。 | Enter and submit the values. | Verify the current password and password policy; retain no password values after validation failure. |
| `P14-02` | Completion and sessions / `完了・セッション` | Show success or a safe authentication/validation error. | 成功または安全な認証・入力エラーを表示する。 | Return to Account Settings or retry. | Update the password securely, invalidate other affected sessions, preserve the current protected session as defined, and return to P11. |

- **Acceptance:** All `P14-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

### SCR-P-015 — P15 Producer Password Reset / `生産者パスワード再設定`

- **Status:** `[CONFIRMED]`
- **Actor:** Producer Applicant / Producer
- **Purpose:** Recover Producer access without revealing account state.
- **Main entry:** Producer Login (P01) / reset email link
- **Summary:** Producer Password Reset combines the reset request and valid-token password entry in one specification using neutral responses, rate limits, and an expiring one-use token.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `P15-01` | Reset request / `再設定依頼` | Email input and Send Reset Link action. | メール入力と再設定リンク送信を表示する。 | Submit an email address. | Always show a neutral response, apply rate limits, and send a link only when permitted without exposing account or screening state. |
| `P15-02` | New password / `新パスワード` | For a valid token, show new password and confirmation. | 有効トークンの場合に新パスワードと確認入力を表示する。 | Enter and save the new password. | Validate policy, consume the one-use token, update securely, invalidate affected sessions, and open P01. |
| `P15-03` | Invalid token / `無効トークン` | Show expired, used, or invalid-link guidance. | 期限切れ、使用済み、不正リンクの案内を表示する。 | Request a new link. | Do not reset the password or reveal account state; return to the request state. |

- **Acceptance:** All `P15-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

## 11. Admin Screen Specifications

### SCR-A-001 — A01 Admin Login / `管理者ログイン`

- **Status:** `[CONFIRMED]`
- **Actor:** Admin
- **Purpose:** Sign in to the protected Admin Portal with the authorized Admin account.
- **Main entry:** Admin Portal entry
- **Summary:** Admin Login authenticates the single authorized Admin account separately from Buyer and Producer accounts, applies safe login protection, and opens the Admin Dashboard without revealing account details on failure.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `A01-01` | Credentials / `ログイン情報` | Admin email address, password, and Login button. | 管理者メールアドレス、パスワード、ログインボタンを表示する。 | Enter credentials and sign in. | Authenticate securely; never disclose whether the Admin account exists. |
| `A01-02` | Login protection / `ログイン保護` | Show a neutral error when authentication fails. | 認証失敗時は中立的なエラーを表示する。 | Correct the input or retry later. | Rate-limit repeated failures, record a safe security event for A11, and never retain password values. |
| `A01-03` | Successful login / `ログイン成功` | Show no financial or personal data before authentication. | 認証前は金融情報・個人情報を表示しない。 | Continue after success. | Create the protected Admin session and open A02. |

- **Acceptance:** All `A01-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

### SCR-A-002 — A02 Admin Dashboard / `管理ダッシュボード`

- **Status:** `[CONFIRMED]`
- **Actor:** Admin
- **Purpose:** Show the Admin's operational overview and direct links to items requiring attention.
- **Main entry:** Admin Login (A01)
- **Summary:** The Admin Dashboard is the Admin Portal landing page, showing compact read-only summaries for Producer screening, product and order issues, Company commission, PAY.JP payouts, and important system/security events with links to the responsible screens.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `A02-01` | Producer status / `生産者状況` | Show Producers with incomplete PAY.JP applications, screening in progress, or lost selling eligibility. | PAY.JP申請未完了、審査中、販売資格喪失の生産者件数を表示する。 | Select a count or alert. | Open A04 with the matching filter; PAY.JP, not Admin, makes screening decisions. |
| `A02-02` | Product and order alerts / `商品・注文注意` | Show problematic products, refund/chargeback cases, and orders requiring Admin support. | 問題商品、返金・チャージバック、管理者支援が必要な注文を表示する。 | Select an alert. | Open filtered A07 or A08. |
| `A02-03` | Sales and commission / `売上・会社手数料` | Show current-period total sales and the 10% Company commission received or pending. | 当期の総売上と、会社が受取済み・未受取の10％手数料を表示する。 | Select the summary. | Open A09 for the period and Producer breakdown. |
| `A02-04` | Payout status / `振込状況` | Show scheduled, paid, failed, and held Producer payout counts. | 生産者振込の予定、完了、失敗、保留件数を表示する。 | Select a status. | Open A10 with the matching filter. |
| `A02-05` | Security alerts and navigation / `セキュリティ注意・ナビゲーション` | Show important failed-login, unauthorized-access, callback, or system-error alerts plus Admin menu links. | 重要なログイン失敗、不正アクセス、コールバック、システムエラーの注意と管理メニューを表示する。 | Select an alert or menu item. | Open A11 or the selected A03–A10 screen. |

- **Acceptance:** All `A02-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

### SCR-A-003 — A03 Buyer Management / `購入者管理`

- **Status:** `[CONFIRMED]`
- **Actor:** Admin
- **Purpose:** Search Buyers and review minimum account and usage information.
- **Main entry:** Admin Dashboard (A02) / Admin menu
- **Summary:** Buyer Management uses the approved Buyer list and right-side detail drawer. It exposes only masked identity, account state, registration/login dates, and aggregate/latest-order usage information. It does not provide inquiry handling or account-state mutation controls.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `A03-01` | Buyer search and list / `購入者検索・一覧` | Search by member ID, masked name, or masked email. Show member ID, masked Buyer name/email, account state, registration date, and order count. | 会員ID、マスク済み氏名・メールで検索し、会員ID、購入者名・メール、アカウント状態、登録日、注文数を表示する。 | Search and select a Buyer row. | Return only the minimum Buyer account and usage data needed by the approved screen. |
| `A03-02` | Buyer detail drawer / `購入者詳細` | Show masked name/email, member ID, account state, registration date, last-login date, total order count, and latest-order date in a right-side drawer. | マスク済み氏名・メール、会員ID、アカウント状態、登録日、最終ログイン日、注文数、最終注文日を右側ドロワーに表示する。 | Select a row to open the drawer; close it to return to the unchanged list. | Display read-only information only. Do not expose card data, Producer finance, delivery-address detail, or unrelated Buyer data. |
| `A03-03` | Privacy, unavailable, and error states / `プライバシー・利用不可・エラー` | Mask personal data and show a generic unavailable or denial state when the Buyer cannot be loaded or the request is outside the permitted scope. | 個人情報をマスクし、購入者を取得できない場合または許可範囲外の場合は一般的な利用不可・拒否状態を表示する。 | Retry, close the drawer, or return to the list. | Enforce authorization server-side and record only a safe operation event for A11. |

- **Acceptance:** All `A03-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

### SCR-A-004 — A04 Producer Management / `生産者管理`

- **Status:** `[CONFIRMED]`
- **Actor:** Admin
- **Purpose:** Monitor Producer accounts, PAY.JP Tenant status, selling eligibility, and operational state.
- **Main entry:** Admin Dashboard (A02) / Admin menu
- **Summary:** Producer Management combines Producer search and detail review. Admin monitors the system-created PAY.JP Tenant and card-brand screening, supports application issues, and may suspend or reactivate marketplace operations, but does not manually create Tenants or approve PAY.JP screening.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `A04-01` | Producer list and filters / `生産者一覧・絞り込み` | Show Producer/farm name, account state, PAY.JP application state, Visa/Mastercard results, selling eligibility, and last update. | 生産者・農園名、アカウント状態、PAY.JP申請状態、Visa・Mastercard結果、販売資格、更新日時を表示する。 | Search, filter, and select a Producer. | Open the selected Producer detail without exposing raw bank data. |
| `A04-02` | Account and Tenant detail / `アカウント・Tenant詳細` | Show internal profile, safe contact data, PAY.JP Tenant reference, application state, and operational history. | 社内プロフィール、安全な連絡先、PAY.JP Tenant参照、申請状態、運用履歴を表示する。 | Review the selected Producer. | Read the current internal and authoritative PAY.JP status; never offer manual Tenant creation. |
| `A04-03` | Brand screening and support / `ブランド別審査・支援` | Show passed, in_review, or declined by card brand and safe application/support guidance. | カードブランド別のpassed、in_review、declinedと安全な申請・支援案内を表示する。 | Review the state or assist with reissue/support guidance. | Selling is enabled only when Visa and Mastercard are passed; Admin cannot override the result. |
| `A04-04` | Suspend or reactivate / `停止・再開` | Show current operational state, reason input, and confirmation. | 現在の運用状態、理由入力、確認を表示する。 | Enter a reason and confirm the state change. | Block or restore new selling actions while preserving existing order/refund/payout access, and write a safe event to A11. |
| `A04-05` | Related operations / `関連業務` | Show links to the Producer's products, sub-orders, sales/commission, and payouts. | 対象生産者の商品、生産者別注文、売上・会社手数料、振込へのリンクを表示する。 | Select a related area. | Open A07, A08, A09, or A10 with the Producer filter. |

- **Acceptance:** All `A04-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

### SCR-A-005 — A05 Category Management / `カテゴリ管理`

- **Status:** `[CONFIRMED]`
- **Actor:** Admin
- **Purpose:** Maintain the shared product categories used by Buyers and Producers.
- **Main entry:** Admin menu / Product Management (A07)
- **Summary:** Category Management keeps one Admin-controlled category master for Buyer browsing and Producer product selection, combining list, create, edit, and guarded permanent deletion without allowing Producers to create private categories. Each row exposes separate pencil (edit) and trash (delete) icon actions. A05 does not expose category lifecycle-status controls or a manual category display-order column or field.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `A05-01` | Category list / `カテゴリ一覧` | Show category name, total linked-product count, and an `操作` column containing separate pencil (edit) and trash (delete) icon buttons. Do not show lifecycle status or a manual display-order column. | カテゴリ名、商品数、操作（編集・削除）を表示し、利用状態および手動の「表示順」列は表示しない。 | Select edit or delete for one category. | Use the same category master in Buyer and Producer screens; icon-only actions require Japanese tooltips and accessible names. |
| `A05-02` | Create or edit / `作成・編集` | Both creation and editing open compact centered input dialogs containing only the category-name field and their respective Cancel plus Add or Save actions. Neither flow shows deletion, lifecycle-status controls, or a manual display-order field. | 作成・編集はいずれもコンパクトな中央入力ダイアログを表示する。カテゴリ名と、キャンセルおよび追加または保存の各操作のみを含み、削除、利用状態、手動の「表示順」は表示しない。 | Enter or change the category name and confirm the applicable action. | Validate required and duplicate names before creating or saving. |
| `A05-03` | Guarded permanent deletion / `削除` | Open a blocking alert when any product references the category; show the affected count and `商品を確認`. When no product references it, open a confirmation dialog that states the deletion cannot be undone. | 商品が紐づく場合は件数と「商品を確認」を含む削除不可ダイアログを表示する。商品が紐づかない場合は、元に戻せないことを明示した削除確認ダイアログを表示する。 | Review linked products, close the alert, cancel deletion, or confirm `削除する` for an unused category. | Re-check references server-side at confirmation time. Permanently delete only when the authoritative linked-product count is zero; otherwise return the in-use result without changing the category. |
| `A05-04` | Operation result / `処理結果` | Show create/edit/delete success, validation, concurrent-change, or in-use guidance. | 作成・編集・削除の成功、入力、競合更新、利用中の案内を表示する。 | Correct, close, or reload when needed. | Apply the latest safe state and write each category create, edit, or delete as a safe A11 audit event. |

- **Acceptance:** All `A05-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

### SCR-A-006 — A06 Banner Management / `バナー管理`

- **Status:** `[CONFIRMED]`
- **Actor:** Admin
- **Purpose:** Review and control the Home Today's Recommendations banner.
- **Main entry:** Admin Dashboard (A02) / Admin menu
- **Summary:** Banner Management shows the three eligible top-selling products automatically selected for the Home Today's Recommendations banner. Admin controls banner visibility and reviews the selected products; Producers cannot control the banner.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `A06-01` | Automatic selection / `自動選定` | Show the current three automatically selected eligible top-selling products and their order. | 販売可能な売上上位3商品と表示順を表示する。 | Review the selection. | Select only published, purchasable products; automatically replace an ineligible item with the next eligible product. |
| `A06-02` | Banner visibility / `バナー表示` | Show Visible/Hidden state and confirmation. | 表示・非表示状態と確認を表示する。 | Show or hide the banner. | Update Home visibility without giving Producers any banner controls. |
| `A06-03` | Preview and product link / `プレビュー・商品リンク` | Show the Buyer-facing title, image, product name, price, promotion, and link. | 購入者向けタイトル、画像、商品名、価格、プロモーション、リンクを表示する。 | Preview or select a product. | Open the Buyer preview or A07 product detail using the current safe product data. |
| `A06-04` | Unavailable selection / `選定不足` | Show a clear state when fewer than three eligible products exist. | 販売可能商品が3件未満の場合に明確な状態を表示する。 | Review products or keep the available set. | Never display unpublished, out-of-stock, or blocked products; write visibility changes as safe A11 events. |

- **Acceptance:** All `A06-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

### SCR-A-007 — A07 Product Management / `商品管理`

- **Status:** `[CONFIRMED]`
- **Actor:** Admin
- **Purpose:** Search, review, and moderate products and Producer-created promotions.
- **Main entry:** Admin Dashboard (A02) / Admin menu / Banner Management (A06)
- **Summary:** Product Management opens with a conventional full-width cross-Producer product table rather than a persistent split-pane detail panel. Product detail, stock, publication history, promotion review, and moderation remain part of logical screen A07 and are shown only after the Admin selects a product. Valid Producer products publish without routine Admin approval; Admin acts only when a listing or promotion requires correction.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `A07-01` | Product list and filters / `商品一覧・絞り込み` | Show a full-width table with product ID, product name, Producer, category, price, stock, publication state, promotion, problem state, and a compact row-action control. Search and key filters appear above the table; do not reserve a persistent right-side detail panel in the default state. | 商品ID、商品名、生産者、カテゴリ、価格、在庫、公開状態、プロモーション、問題状態、コンパクトな行操作を含む全幅テーブルを表示する。検索と主要フィルターはテーブル上部に配置し、初期状態では右側に商品詳細領域を常設しない。 | Search, filter, select a product row, or open its compact row action. | Open the selected product in a separate detail state within logical screen A07; icon-only row actions require Japanese tooltips and accessible names. |
| `A07-02` | Product detail / `商品詳細` | Show description, images, category, authoritative price/stock, publication history, and Producer without variation controls. | 説明、画像、カテゴリ、正式な価格・在庫、公開履歴、生産者を表示し、バリエーション操作は表示しない。 | Review the selected product. | Read current product data while preserving purchase-time OrderItem snapshots. |
| `A07-03` | Promotion detail / `プロモーション詳細` | Show regular price, promotion rate/period, promotional price, and Producer. | 通常価格、率・期間、割引価格、生産者を表示する。 | Review a promotion. | Keep the Producer-funded promotion separate from the Company's 10% commission. |
| `A07-04` | Moderation / `モデレーション` | Show Unpublish, Request Correction, Republish, and reason confirmation. | 非公開、修正依頼、再公開、理由確認を表示する。 | Enter a reason and confirm the permitted action. | Update the listing, show the safe reason to the Producer, and write a safe A11 event; do not add routine prepublication approval. |
| `A07-05` | Problem promotion / `問題プロモーション` | Show Disable Promotion with reason when the promotion is invalid or harmful. | 不正・不適切な場合に理由付きプロモーション停止を表示する。 | Enter a reason and disable it. | Stop the Buyer discount without changing historical OrderItem promotion snapshots. |
| `A07-06` | Errors and related links / `エラー・関連リンク` | Show concurrent-change, unavailable-product, or permission guidance plus category/Producer links. | 競合更新、利用不可、権限案内とカテゴリ・生産者リンクを表示する。 | Reload or open the related record. | Use current values and open A05 or A04 without exposing another role's protected data. |

- **Acceptance:** All `A07-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

### SCR-A-008 — A08 Order Management / `注文管理`

- **Status:** `[CONFIRMED]`
- **Actor:** Admin
- **Purpose:** Search orders, review Buyer and Producer order detail, and handle refund/chargeback exceptions.
- **Main entry:** Admin Dashboard (A02) / Buyer Support (A03) / Admin menu
- **Summary:** Order Management combines order search, Buyer-order detail, Producer sub-orders, payment/refund/fulfillment history, and authorized refund or chargeback handling. Producers remain responsible for fulfillment, while Admin handles support and financial exceptions without manually overwriting provider-confirmed payment state.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `A08-01` | Order list and filters / `注文一覧・絞り込み` | Show order reference, Buyer, Producers, total, order/payment/refund states, fulfillment summary, and exception indicator. | 注文番号、購入者、生産者、合計、注文・決済・返金状態、対応概要、例外を表示する。 | Search, filter, and select an order. | Open the selected order detail on this screen. |
| `A08-02` | Order and sub-order detail / `注文・生産者別詳細` | Show purchase-time items, Producer grouping, prices/promotions, delivery information needed for support, and totals. | 購入時商品、生産者別グループ、価格・プロモーション、支援に必要な配送情報、合計を表示する。 | Review the Buyer order or a Producer sub-order. | Keep other Producer data separated and never change purchase-time snapshots. |
| `A08-03` | State and history / `状態・履歴` | Show order, authoritative payment, refund, and Producer fulfillment states separately with timestamps. | 注文、正式な決済、返金、生産者対応の各状態と日時を分けて表示する。 | Review the history. | Never treat the browser return as payment proof and never manually mark a payment Paid. |
| `A08-04` | Refund or chargeback / `返金・チャージバック` | Show eligible amount/items, prior refunds, reason, confirmation, and expected commission/payout effect. | 対象金額・商品、返金済み額、理由、確認、会社手数料・振込への想定影響を表示する。 | Enter the permitted amount/reason and confirm once. | Validate amount and state, call the approved PAY.JP process, prevent duplicate effect, and add rather than overwrite finalized financial history. |
| `A08-05` | Post-payout effect / `振込後の影響` | Show when a refund or chargeback affects an already paid Producer payout. | 返金・チャージバックが振込済み生産者へ影響する場合を表示する。 | Review the resulting adjustment. | Apply the effect to a future payout or hold according to the recorded rule; do not rewrite Paid history. |
| `A08-06` | Privacy, errors, and links / `プライバシー・エラー・リンク` | Mask unnecessary Buyer data and show safe mismatch/concurrent-operation guidance. | 不要な購入者情報をマスクし、不一致・競合操作の安全な案内を表示する。 | Reload, return, or open the related Producer/sales/payout record. | Enforce authorization and open A04, A09, or A10 with a safe reference; write sensitive operation results to A11. |

- **Acceptance:** All `A08-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

### SCR-A-009 — A09 Sales / Commission Summary / `売上・会社手数料サマリー`

- **Status:** `[CONFIRMED]`
- **Actor:** Admin
- **Purpose:** Show total sales and the Company's 10% commission received or pending.
- **Main entry:** Admin Dashboard (A02) / Admin menu / Order Management (A08)
- **Summary:** Sales / Commission Summary provides period and Producer views of sales, Producer-funded promotion impact, refunds, the separate 10% Company commission, PAY.JP fees, pending commission, and expected Producer payout. All figures are read-only and traceable to purchase-time order records.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `A09-01` | Summary cards / `サマリーカード` | Show gross sales, promotion reduction, refunds, Company commission received, pending commission, and expected Producer payout for the selected period. | 選択期間の総売上、プロモーション減額、返金、会社手数料受取済み・未受取、生産者振込予定額を表示する。 | Change period or select a card. | Recalculate from authoritative order/payment/refund records without editing source values. |
| `A09-02` | Producer breakdown / `生産者別内訳` | Show each Producer's sales, promotion impact, refunds, 10% Company commission, PAY.JP fees, and expected payout. | 生産者ごとの売上、プロモーション影響、返金、会社手数料10％、PAY.JP手数料、振込予定額を表示する。 | Search, filter, and select a Producer. | Display only the selected period and link to the Producer's source orders. |
| `A09-03` | Commission detail / `会社手数料詳細` | Show the sales amount after promotion/refund used for the 10% commission and the resulting commission amount. | プロモーション・返金後の手数料対象売上と10％の会社手数料額を表示する。 | Review source lines. | Keep the Company commission separate from the Buyer promotion and PAY.JP fee. |
| `A09-04` | Received and pending / `受取済み・未受取` | Show commission received by the Company, pending, reduced by refund, or otherwise adjusted. | 会社手数料の受取済み、未受取、返金減額、その他調整を表示する。 | Filter by status. | Use PAY.JP and internal status without allowing manual amount edits. |
| `A09-05` | Difference or missing data / `差異・不足データ` | Show a clear exception when order, payment, refund, fee, or payout data does not match or is unavailable. | 注文、決済、返金、手数料、振込データが一致しない・不足する場合を明確に表示する。 | Open the source order or payout. | Do not finalize the displayed result silently; open A08 or A10 and record the exception safely. |

- **Acceptance:** All `A09-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

### SCR-A-010 — A10 Payout Monitor / `振込状況確認`

- **Status:** `[CONFIRMED]`
- **Actor:** Admin
- **Purpose:** Monitor PAY.JP's direct Producer payouts and compare them with system-calculated amounts.
- **Main entry:** Admin Dashboard (A02) / Admin menu / Sales Summary (A09)
- **Summary:** Payout Monitor is a read-only view of scheduled, paid, failed, and held Producer payouts. It combines the payout list and detail, compares the system-calculated amount with PAY.JP's actual amount, and shows safe follow-up information without allowing Admin to execute or alter payouts.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `A10-01` | Payout list and filters / `振込一覧・絞り込み` | Show Producer, period, expected amount, PAY.JP amount, scheduled/paid date, status, and exception indicator. | 生産者、期間、予定額、PAY.JP実績額、予定・完了日、状態、例外を表示する。 | Search, filter, and select a payout. | Open the selected payout detail on this screen. |
| `A10-02` | Payout detail / `振込詳細` | Show source sales, promotion impact, refunds, Company commission, PAY.JP fees, adjustments, and final amount. | 元売上、プロモーション影響、返金、会社手数料、PAY.JP手数料、調整、最終額を表示する。 | Review the breakdown. | Use internal purchase-time records and PAY.JP status; all values remain read-only. |
| `A10-03` | Amount check / `金額確認` | Show whether the system-calculated amount matches the PAY.JP payout amount. | システム計算額とPAY.JP振込額が一致するか表示する。 | Review a difference. | Show the differing components and source references without manually changing either amount. |
| `A10-04` | Failed or held / `失敗・保留` | Show a safe failure/hold reason, prior status, and related Producer/bank-status link; bank data remains masked. | 安全な失敗・保留理由、直前状態、生産者・口座状態へのリンクを表示し、口座はマスクする。 | Open the related Producer or wait for the next authoritative update. | Open A04 when support is needed and never retry or mark Paid from this screen. |
| `A10-05` | Provider update and security / `提供元更新・安全性` | Show the latest authoritative PAY.JP update time and safe unavailable/delayed guidance. | 最新のPAY.JP正式更新日時と利用不可・遅延の安全な案内を表示する。 | Refresh or return later. | Process repeated/out-of-order provider events idempotently and write only safe status events to A11. |

- **Acceptance:** All `A10-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

### SCR-A-011 — A11 Log Monitoring / `ログ監視`

- **Status:** `[CONFIRMED]`
- **Actor:** Admin
- **Purpose:** Monitor security, system, payment-callback, and sensitive-operation events safely.
- **Main entry:** Admin Dashboard (A02) / Admin menu / alert links
- **Summary:** Log Monitoring provides one read-only view for failed logins, suspicious or unauthorized access, payment callback anomalies, system errors, and sensitive operation results. It supports investigation without allowing log modification or recording passwords, card data, secrets, raw bank data, or unnecessary personal information.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `A11-01` | Event list and filters / `イベント一覧・絞り込み` | Show a simple four-column table: time, plain-language event description, safe actor, and result. Provide one keyword search and one date-range filter. Omit severity/type/result filters and the action column from the main view. | 日時、内容、実行者、結果の4列を表示する。キーワード検索と期間指定のみを設け、重要度・種別・結果の個別フィルターと操作列は表示しない。 | Search, change the date range, or select a row. | Open a compact read-only dialog with the event description, time, actor, result, safe target/reference, and reason or previous/new state when applicable. Close returns to the same list context. Return only authorized, masked log information. |
| `A11-02` | Security events / `セキュリティイベント` | Show repeated login failures, suspected brute-force activity, unauthorized page/API access, and cross-account/cross-Producer attempts. | 連続ログイン失敗、総当たり疑い、権限のない画面・APIアクセス、他アカウント・他生産者へのアクセス試行を表示する。 | Select an event. | Open safe details and related account reference without exposing credentials or secrets. |
| `A11-03` | Payment and system events / `決済・システムイベント` | Show forged/replayed/mismatched callback attempts, delayed or unknown provider events, integration errors, and system failures. | 偽造・再送・金額不一致のコールバック試行、遅延・不明な提供元イベント、連携エラー、システム障害を表示する。 | Review event metadata and related reference. | Show verification/result information without logging payment credentials or full payload secrets. |
| `A11-04` | Sensitive operation results / `重要操作結果` | Show safe results for Producer suspension, product moderation, refunds/chargebacks, and payout-status changes. | 生産者停止、商品モデレーション、返金・チャージバック、振込状態変更の安全な結果を表示する。 | Review the operation event. | Keep time, target, previous/new safe state, result, and reason where applicable; never store raw sensitive values. |
| `A11-05` | Read-only and unavailable state / `参照専用・利用不可` | Show that logs cannot be edited or deleted from this screen and display safe guidance when the log service is unavailable. | この画面からログを編集・削除できないことと、ログサービス利用不可時の安全な案内を表示する。 | Refresh or return later. | Prevent modification, restrict access, and never fabricate missing events. |

- **Acceptance:** All `A11-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

## 12. Integration and Logical API Requirements

- **INT-001 `[CONFIRMED]` — PAY.JP Platform:** Marketplace capabilities use PAY.JP Platform API v1. Platform functionality must not depend on API v2.
- **INT-002 `[CONFIRMED]` — Tenant onboarding:** Create a Tenant automatically when required and generate the hosted application URL with `POST /v1/tenants/:id/application_urls`.
- **INT-003 `[CONFIRMED]` — Hosted URL:** Append the configured `return_to`, open only the returned URL, and generate a new URL after access, expiry, replacement, or safe retry. Do not construct the provider host locally.
- **INT-004 `[CONFIRMED]` — Screening authority:** Update overall and card-brand screening only from authoritative PAY.JP API/webhook data.
- **INT-005 `[CONFIRMED]` — Payment creation:** Revalidate the selected Producer group's base prices, promotions, regional delivery fees, stock, and total server-side, then create one uniquely referenced payment for that Producer's Tenant. Do not include other cart groups in the attempt.
- **INT-006 `[CONFIRMED]` — 3D Secure:** Follow the PAY.JP-directed issuer-authentication flow and resume authoritative verification after browser return.
- **INT-007 `[CONFIRMED]` — Payment/refund idempotency:** Repeated, delayed, or out-of-order payment/refund events must not create duplicate charges, paid orders, refunds, commission effects, or payout effects.
- **INT-008 `[CONFIRMED]` — Direct payout:** Read PAY.JP payout, Term, Statement, Balance, fee, adjustment, and document information as required by P10, A09, and A10; Producers and Admin cannot edit provider-confirmed payout values.
- **INT-009 `[CONFIRMED]` — Bank update:** Send protected bank changes through the PAY.JP Tenant API after re-authentication; do not persist or log raw bank values locally.
- **INT-010 `[CONFIRMED]` — Email:** Registration verification, email-change verification, and password reset use rate-limited, expiring, one-use challenges and neutral request responses where account enumeration is possible. Buyer and Producer initial registration use six-digit email OTPs bound respectively to DATA-014/SEC-014 and DATA-013/SEC-013. Authenticated B18/P12 email changes retain their separate one-time-link flows; password-reset mechanisms are unchanged. Transactional order-accepted email is dispatched only after authoritative payment success and committed order creation, with an idempotency key tied to the order and notification type.

| API ID | Logical interface group | Key controls |
|---|---|---|
| `API-001` | Authentication and email verification | Role separation, neutral recovery, one-use tokens, session protection |
| `API-002` | Public catalog, category, search, and product detail | Published products only; safe public Producer subset |
| `API-003` | Cart and checkout | Seller-grouped cart; one selected Producer checkout; server-authoritative price, regional fee, stock, promotion, address, and totals |
| `API-004` | Buyer order, payment, cancellation, and refund | Buyer ownership; authoritative PAY.JP status; exactly-once effects |
| `API-005` | Producer onboarding and screening | Producer ownership; Tenant auto-creation; Visa/Mastercard eligibility gate |
| `API-006` | Producer catalog and fulfillment | Own data only; immutable purchase snapshots |
| `API-007` | Producer payout and bank information | Read-only finance; masked bank data; recent re-authentication for update |
| `API-008` | Admin operations | Single Admin role; monitored screening; reasoned sensitive operations |
| `API-009` | Monitoring and audit events | Read-only safe logs without secrets or raw sensitive values |

## 13. Logical Data Requirements

- **DATA-001 `[CONFIRMED]` — Buyer:** Identity, verified/pending email, default address, session state.
- **DATA-002 `[CONFIRMED]` — Producer and PAY.JP Tenant:** Internal Producer profile, Tenant reference, operational state, and selling eligibility. The Producer profile includes the required shop profile image reference established through P02-06; it is public marketplace data used as the shop image in Buyer product-list experiences. After registration, replacement is available only through P11-04 and the stored reference changes only after explicit preview confirmation and successful server upload. Cancellation or upload failure preserves the previous reference. Temporary upload ownership follows SEC-013; image policy and abandoned-upload retention require TBD-003 approval.
- **DATA-003 `[CONFIRMED]` — Screening result:** Per-card-brand status and available authoritative timestamps.
- **DATA-004 `[CONFIRMED]` — Category, banner, product, sellable option, stock, promotion, and regional delivery fees:** Every product has exactly one internal default sellable option. Tax-inclusive base product price, stock, and Producer-funded discount are authoritative on that option. The product also stores non-negative JPY delivery fees for Honshu/Shikoku/Kyushu, Hokkaido, and Okinawa. Product-variation, package-size, weight, carrier, and carrier-quotation controls are not exposed in the current release. Publication and promotion remain distinct from the Company's fixed 10% commission.
- **DATA-005 `[CONFIRMED]` — Cart:** Buyer-scoped lines grouped by Producer with server-repriced authoritative values, applicable regional delivery fee, shop item count, shop subtotal, and selected-Producer checkout context. Pre-address price presentation follows TBD-006 and quantity/multi-product fee aggregation follows TBD-009.
- **DATA-006 `[CONFIRMED]` — Buyer order and Producer fulfillment record:** Each checkout creates one Buyer order for one Producer, with that Producer's fulfillment record and immutable product, price, promotion, regional-fee, total, and delivery-address snapshots. An order may contain multiple products from that same Producer but never products from different Producers.
- **DATA-007 `[CONFIRMED]` — Payment attempt:** Tenant, unique reference, 3D Secure and authoritative state; no raw card data.
- **DATA-008 `[CONFIRMED]` — Refund/chargeback:** Original payment, amount, state, reason, idempotency reference, commission/payout effect.
- **DATA-009 `[CONFIRMED]` — Payout and statement:** Period components, expected/provider amount, carry-forward, status, dates, references.
- **DATA-010 `[CONFIRMED]` — Producer bank destination:** Provider-managed values; masked ordinary display; raw values excluded from logs and audit.
- **DATA-011 `[CONFIRMED]` — Inquiry:** Authenticated Buyer reference, destination type (BVI support or Producer-directed), required subject/content for general BVI support, required issue/content for Producer-directed contact, optional owned-order/Producer context only for the order-linked variant, and status. Do not trust client-supplied Buyer identity.
- **DATA-012 `[CONFIRMED]` — Audit/security event:** Actor, time, target, safe previous/new state, result, reason, safe network metadata.
- **DATA-013 `[CONFIRMED]` — Temporary Producer registration:** Server-owned attempt/session identifier, purpose (`producer_registration`), normalized email, protected OTP verifier, challenge expiry/use state, attempt/resend counters, verification timestamp, separate verified-grant expiry, and consumption state. This is not a Producer account; do not store plaintext OTPs or passwords in the temporary record. Retention is TBD-001.
- **DATA-014 `[CONFIRMED]` — Temporary Buyer registration:** Server-owned attempt/session identifier, purpose (`buyer_registration`), normalized email, protected OTP verifier, challenge expiry/use state, attempt/resend counters, verification timestamp, separate verified-grant expiry, intended destination, and consumption state. Each OTP challenge expires three minutes after issuance; a replacement challenge receives its own new three-minute expiry. This is not a Buyer account; do not store plaintext OTPs or passwords in the temporary record. Retention and remaining numeric policies are TBD-001.
- **DATA-015 `[CONFIRMED]` — Receipt document:** Generated read-only PDF bound to one completed Buyer-owned order and its immutable purchase, regional-delivery-fee, shop, total, and order-date snapshots. The current presentation shows `税込` without a separate tax-rate or tax-amount line; qualified-invoice applicability and statutory fields remain TBD-007.

## 14. Security and Privacy Requirements

- **SEC-001 `[CONFIRMED]`** Enforce role and object ownership server-side for every protected request.
- **SEC-002 `[CONFIRMED]`** A Producer can access only their own products, sub-orders, sales, payout, and Tenant/bank data.
- **SEC-003 `[CONFIRMED]`** A Buyer can access only their own account, address, inquiry, and orders.
- **SEC-004 `[CONFIRMED]`** Never store card numbers, security codes, or raw payment credentials.
- **SEC-005 `[CONFIRMED]`** Do not place passwords, card data, secrets, raw bank values, or unnecessary personal information in logs, analytics, URLs, or generic errors.
- **SEC-006 `[CONFIRMED]`** Mask bank information in ordinary Producer/Admin UI and exclude raw before/after values from audit events.
- **SEC-007 `[CONFIRMED]`** Require recent re-authentication before P13 bank-account editing.
- **SEC-008 `[CONFIRMED]`** Process payment, refund, screening, and payout provider events idempotently and reject mismatched or unknown references.
- **SEC-009 `[CONFIRMED]`** Protect registration verification, email-change verification, and reset tokens with expiry, one-use enforcement, invalidation, and rate limits.
- **SEC-010 `[CONFIRMED]`** Use neutral authentication/recovery errors where responses could reveal account or application state.
- **SEC-011 `[CONFIRMED]`** Audit sensitive Admin and Producer operations using safe metadata and preserve finalized financial history.
- **SEC-012 `[CONFIRMED]`** Log Monitoring is read-only; application users cannot edit or delete security events from A11.
- **SEC-013 `[CONFIRMED]` — Pre-account registration boundary:** Bind the temporary registration session to an unpredictable opaque identifier in a Secure, HttpOnly, appropriately SameSite cookie; protect mutations against CSRF. Generate OTPs securely, protect the stored verifier against offline guessing, exclude codes/grants from logs and URLs, and enforce expiry, purpose binding, bounded attempts, request/resend limits, and atomic single use. Rotate the session identifier on successful OTP verification; the verified grant has its own expiry independent of the consumed OTP. Server-side checks on details data and final submit require a matching verified, unexpired, unconsumed session; client booleans and route guards are never proof. Email changes invalidate prior verification. Atomically consume the grant with account creation, enforce email uniqueness and retry safety, and establish a fresh authenticated session only after completion. The temporary session grants no Producer operational access. Numeric policies and retention: TBD-001.
- **SEC-014 `[CONFIRMED]` — Buyer pre-account registration boundary:** Apply SEC-013's cookie, CSRF, secure OTP-verifier, expiry, purpose-binding, bounded-attempt, session-rotation, single-use, server-authorization, email-change invalidation, uniqueness, and atomic-consumption controls independently to `buyer_registration`. The temporary session grants no authenticated Buyer access and cannot be exchanged with a Producer attempt or B18 email-change token. Numeric policies and retention: TBD-001.

## 15. Non-Functional Requirements

- **NFR-001 `[CONFIRMED]`** Customer-facing UI is Japanese, currency is JPY, and business dates/times use Asia/Tokyo.
- **NFR-002 `[CONFIRMED]`** Buyer screens are mobile-first; Producer and Admin screens support desktop operation.
- **NFR-003 `[CONFIRMED]`** Loading, empty, validation, failure, retry, and unavailable states must be safe and actionable.
- **NFR-004 `[CONFIRMED]`** Status must be communicated with text and not by color alone.
- **NFR-005 `[CONFIRMED]`** Financial and screening displays clearly distinguish provisional, pending, finalized, failed, held, and unavailable information where applicable.
- **NFR-006 `[CONFIRMED]`** Repeated user actions and provider events must not duplicate accounts, orders, charges, refunds, inquiries, financial effects, or sensitive operations.

## 16. State Models

- **Producer registration attempt (no account yet):** `Email Entered → OTP Pending → Email Verified / Details Permitted → Consumed on Account Creation`; expired attempts require recovery/re-verification, and changing email invalidates verification. OTP expiry and verified-grant expiry are separate.
- **Buyer registration attempt (no account yet):** `Email Entered → OTP Pending → Email Verified / Details Permitted → Consumed on Account Creation`; success transitions directly from B15 to B14-2. B18 email changes remain a separate authenticated-account state transition.
- **Producer onboarding (account exists):** `Application Required → In Review → Eligible to Sell`; the account email is verified at creation. An authoritative status requiring attention remains non-eligible until Visa and Mastercard are passed.
- **Card brand screening:** `in_review → passed | declined`; status is stored per brand.
- **Payment:** `Verifying → Completed | Failed | Cancelled | Pending`; partial success never makes the overall Buyer order Paid and enters safe recovery/refund handling.
- **Order confirmation:** `Received / cancellation window → Order Confirmed` automatically at 30 minutes when no cancellation succeeded; `Cancelled` and refund states are system-controlled. `注文確定` is an order state, not a Producer-selectable fulfillment value.
- **Producer fulfillment:** Producers may manually change forward or backward among `Received`, `Processing`, and `Shipped`; this state machine remains separate from automatic order confirmation, cancellation, and refund.
- **Product:** Producer-controlled draft/publication state after selling eligibility; Admin moderation may unpublish or request correction with a reason.
- **Payout:** `Scheduled | Paid | Failed | Held | Carried Forward`; provider-confirmed history is read-only.

## 17. Open Decision Registry

The approved workbook contains no cells marked TBD, requiring review, or pending decision. New implementation discoveries must be recorded here before an agent assumes a business, legal, payment, payout, privacy, or operational rule not present in this baseline.

| ID | Status | Decision required | Affected requirements / handling |
|---|---|---|---|
| TBD-001 | `[PARTIALLY RESOLVED]` | Buyer initial-registration OTP lifetime is confirmed at three minutes. Still approve Buyer attempt cap, resend cooldown/request limits, verified-registration session lifetime, and abandoned-attempt retention, plus all corresponding Producer OTP/session numeric policies. | FR-B-014/015, FR-P-002/003, DATA-013/014, SEC-013/014, AT-B-014/015, AT-P-002/003. Do not invent the remaining values in designs or implementation. |
| TBD-002 | `[CONFIRMED]` | User-confirmed 2026-09-09: Producer passwords are 8–64 characters with at least one ASCII uppercase letter, ASCII lowercase letter, ASCII digit, and non-alphanumeric non-whitespace special character; confirmation must match and password values are not trimmed. Exact default-state guidance: `8〜64文字で、大文字・小文字・数字・記号をそれぞれ1文字以上含めてください。` | Apply consistently to P02-01, P14-01, P15-02, and AT-P-002. This is the approved project policy even though contemporary NIST/OWASP guidance generally favors longer passwords without mandatory character-class composition. |

**TBD-003 `[TBD]` — Shop profile photo upload policy:** Approve allowed image formats, maximum file size, dimensions/crop/processing rules, and abandoned-upload retention before implementing those values. Affects P02-06, P11-04, DATA-002, SEC-013, AT-P-002, and AT-P-011. Required initial upload and confirmed post-registration replacement behavior are approved; do not invent numeric limits in the design.

**TBD-004 `[TBD]` — A06 after B01 banner removal:** The user-confirmed B01 UI no longer displays the campaign banner, while the workbook-aligned A06 Banner Management capability remains documented. Confirm whether A06 and its dashboard/navigation references should be removed from V1, repurposed for another placement, or retained for a later banner surface. Do not implement a hidden B01 banner or invent another consumer before this is resolved.

**TBD-005 `[TBD]` — B20/B21 inquiry handling and response channel:** A03 is confirmed as Buyer Management only and no longer handles inquiries in the Admin Portal. B12 now provides a confirmed order-linked Producer inquiry entry and passes the owned order and Producer context into B20 without exposing private contact details. Confirm the operational delivery and response channel for both general support inquiries and Producer-directed inquiries (for example, mediated email or a future inbox). Until resolved, do not add inquiry queues, response controls, or inquiry counts to A03, and do not invent a Producer inbox or direct contact-data exchange.

**TBD-006 `[TBD]` — Buyer price before a delivery prefecture is known:** Regional fees and server-side prefecture mapping are confirmed, and the Buyer UI must continue to present one tax-and-shipping-inclusive amount without a separate shipping line. Confirm which single amount is displayed before the Buyer has a verified delivery prefecture, such as for a guest or an account without a saved address. Do not silently assume the lowest zone, invent a municipality-level rate, or expose a misleading final price. Checkout must always recalculate from the selected delivery prefecture before payment.

**TBD-007 `[TBD]` — Receipt and qualified-invoice scope:** The approved B12 design downloads a PDF for one selected completed order and displays `税込` without a separate tax-rate or tax-amount line. Confirm whether this artifact is an ordinary receipt only or must satisfy Japanese qualified-invoice or other statutory tax-document requirements, and approve any required issuer, registration-number, tax-rate, or tax-amount fields before claiming compliance. Do not add unapproved tax-detail rows to the current UI.

**TBD-008 `[TBD]` — Receipt availability and order completion trigger:** The receipt action is confirmed for a completed order, but the rule that moves a shipped order into the Buyer-visible completed state is not yet specified. Confirm whether completion is automatic, Buyer-confirmed, provider/carrier-confirmed, or Admin-controlled. Until resolved, do not equate payment success, the 30-minute `注文確定` state, or Producer `発送済み` with receipt eligibility.

**TBD-009 `[TBD]` — Regional delivery-fee aggregation:** Product-level regional fee entry and prefecture-zone selection are confirmed, but the calculation for quantity greater than one or multiple different products from the same Producer is not. Confirm whether the fee applies per unit, per product line, once per Producer order, or by another explicit combination rule. Until resolved, P07 may preview one unit for each region, but checkout and implementation must not invent a multi-item fee formula.

**TBD-010 `[TBD]` — Order-accepted email content:** Sending one idempotent Buyer email after authoritative payment success and committed order creation is confirmed. Approve the exact Japanese subject, body copy, sender/reply handling, and whether the message includes the proposed order number, shop, product summary, total, and B12 link. Until resolved, do not treat the proposed wording or field set as final; never include card credentials or internal commission, settlement, or payout data.

## 18. Acceptance Tests

### 18.1 Buyer screens

- **AT-B-001:** Verify `B01` satisfies every `B01-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.
- **AT-B-002:** Verify `B02` satisfies every `B02-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.
- **AT-B-003:** Verify `B03` satisfies every `B03-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.
- **AT-B-004:** Verify `B04` satisfies every `B04-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.
- **AT-B-005:** Verify `B05` satisfies every `B05-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.
- **AT-B-006:** Verify every `B06-*` row, authenticated/guest cart scope, and safe error/retry behavior. Group lines by Producer shop, show all products from the same shop together, show each shop's item count and `ショップ小計（N点・税込・送料込み）`, and expose one checkout action per shop with no combined all-shop checkout. Starting checkout sends only the selected shop's current lines to B07 and leaves every other shop group unchanged in B06. Quantity/removal changes revalidate stock, price, promotion, and regional fee and update only the affected shop subtotal.
- **AT-B-007:** Verify every `B07-*` row and authenticated Buyer scope. Display only the selected Producer's products, including multiple products from that same Producer, and exclude every other cart group. Recalculate the applicable regional fee and final tax-and-shipping-inclusive total from the authoritative delivery prefecture before allowing payment; preserve the other Producer groups in B06.
- **AT-B-008:** Verify every `B08-*` row and authenticated Buyer ownership. The screen is titled `お届け先情報の変更`, exposes `宛名`, `電話番号`, `郵便番号`, `都道府県`, `市区町村`, and `番地・建物名`, and uses `このお届け先を使用` as its primary action. Saving always updates only the current checkout/order delivery snapshot; it also updates the Buyer profile default delivery information only when `基本のお届け先として保存する` is selected. Back or cancellation changes neither value, and previously completed order snapshots remain unchanged.
- **AT-B-009:** Verify every `B09-*` row, authenticated Buyer scope, and safe error/retry behavior. One attempt charges only the selected Producer's authoritative total through that Producer's PAY.JP Tenant. Other cart groups are excluded. Repeated, delayed, pending, failed, cancelled, mismatched, or browser-return-only events cannot create duplicate charges or a paid order.
- **AT-B-010:** Verify every `B10-*` row and authenticated Buyer ownership. B10 appears only after authoritative payment success and committed creation of the selected Producer order. Dispatch one order-accepted email after commit; retries, refreshes, and repeated provider events send no duplicate. Failed or pending payment sends no accepted email. The message contains no card credentials, internal commission, settlement, or payout data. Verify the final subject/body and allowed fields after TBD-010 approval.
- **AT-B-011:** Verify `B11` satisfies every `B11-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.
- **AT-B-012:** Verify every `B12-*` row, authenticated Buyer ownership, and safe error/retry behavior. For a completed owned order, `領収書をダウンロード` returns one PDF for that selected order only, built from immutable order/shop/product/price/regional-fee/date snapshots. The current approved presentation shows the total as `税込` and no separate tax-rate or tax-amount line. Deny another Buyer's order and any ineligible state, and do not mutate the order. Apply TBD-007 statutory fields and TBD-008 completion trigger only after approval.
- **AT-B-013:** Verify `B13` satisfies every `B13-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.
- **AT-B-014:** Verify every `B14-*` row and section 6.5. Email entry creates no Buyer account; B14-2 rejects absent, forged, unverified, expired, consumed, cross-role, or another browser's session and ignores client email tampering. Valid final submission uses the server-verified email, creates one account and one default address atomically, records terms version/time, consumes the grant once, establishes a fresh authenticated session, and preserves the intended destination. Concurrent/retried submission creates at most one account.
- **AT-B-015:** Verify every `B15-*` row, DATA-014, and SEC-014. Test acceptance immediately before the three-minute expiry and rejection at/after expiry; use a controlled server clock rather than client time. Test valid six-digit registration OTP, wrong/expired/replayed codes, remaining attempt limits, resend invalidation with a fresh three-minute expiry, changed email, delivery failure, CSRF, purpose/session mismatch, and concurrent verification. Only server success automatically routes to B14-2 without creating an account or showing a separate success screen. Regress B18 link verification so the old login email remains active until valid confirmation and its token cannot grant registration-details access.
- **AT-B-016:** Verify `B16` satisfies every `B16-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.
- **AT-B-017:** Verify `B17` satisfies every `B17-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.
- **AT-B-018:** Verify `B18` satisfies every `B18-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.
- **AT-B-019:** Verify `B19` satisfies every `B19-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.
- **AT-B-020:** Verify every `B20-*` row, authenticated Buyer scope, and safe error/retry behavior. My Page entry shows only required `件名` and `お問い合わせ内容`; it shows no name, email, order-number, or order-summary input and binds identity server-side. B12 entry retains the separate owned-order Producer inquiry variant with its read-only order summary, required issue choice, and required content. Retried submission creates at most one inquiry.
- **AT-B-021:** Verify `B21` satisfies every `B21-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.

### 18.2 Producer screens

- **AT-P-001:** Verify `P01` satisfies every `P01-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.
- **AT-P-002:** Verify every `P02-*` row and section 6.4. Email entry creates no Producer account; details display persistent verified feedback/read-only email. Reject direct details API access and final submission with absent, forged, unverified, expired, consumed, or another browser's session, including client email tampering. Test valid refresh, expiry/reverification, safe-value preservation without password persistence, required terms/version/time, and invalid fields. On mobile, place `登録した写真は、購入者向けの商品一覧にショップ画像として表示されます。` directly with the required photo field and verify that the stored image is the public Buyer product-list shop image. Test the confirmed password boundaries at 7, 8, 64, and 65 characters; each required ASCII character category; whitespace that must not satisfy the special-character requirement; exact confirmation matching; no trimming; and the approved Japanese validation messages. Concurrent/retried completion creates at most one account using the server-verified email, consumes the grant atomically, creates a fresh authenticated session, and routes to P04 without re-verification. Temporary sessions cannot access Producer operations.
- **AT-P-003:** Verify every `P03-*` row, DATA-013, and SEC-013. Test valid six-digit registration OTP, wrong/expired/replayed codes, attempt limits, resend invalidation, email changes, delivery failures, CSRF, session/purpose mismatch, and concurrent verification. Only server success automatically routes to P02-2, never P04; failed checks stay recoverable. Consuming/expiring the OTP must not revoke an otherwise valid verified grant. Test configured policies after TBD-001 approval. Regress P12 link verification/old-email protection, Buyer verification, and password reset without converting them to this signup OTP flow.
- **AT-P-004:** Verify `P04` satisfies every `P04-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.
- **AT-P-005:** Verify `P05` satisfies every `P05-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.
- **AT-P-006:** Verify every `P06-*` row and Producer ownership. Desktop may use the approved table, but mobile renders full-width vertically stacked product rows/cards with no horizontal table scroll or swipe. Product selection, create, publish, unpublish, filters, empty state, and safe retry remain usable at the supported mobile viewport.
- **AT-P-007:** Verify every `P07-*` row and the authenticated Producer ownership boundary. Create and edit show no product-type selector, variation name, option row, add/delete variation control, package-size, weight, carrier, or carrier-quotation input. Validate one required non-negative tax-inclusive base price, one required non-negative stock quantity, one optional valid percentage discount, and three required non-negative whole-JPY regional fees for Honshu/Shikoku/Kyushu, Hokkaido, and Okinawa. Blank or zero discount means no discount. For each one-unit preview zone, add its fee to the base price and then apply the Producer-funded percentage discount to the complete amount, keeping that discount separate from the Company's fixed 10% commission. Edit preloads all saved values; errors preserve valid input; publication remains blocked until required data is valid. Pre-address Buyer price behavior remains TBD-006, and multi-item fee aggregation remains TBD-009.
- **AT-P-008:** Verify every `P08-*` row and Producer ownership. Mobile uses full-width vertically stacked order cards with no horizontal table scroll or swipe and keeps the 30-minute cancellation note below filters and above the list. During the cancellation window show the received state; after 30 minutes without successful cancellation, present system-controlled `注文確定` when applicable. Distinguish system order state from Producer fulfillment using text, not color alone, and deny another Producer's order.
- **AT-P-009:** Verify every `P09-*` row and Producer ownership. The manual fulfillment dropdown contains only Received, Processing, and Shipped and permits the approved forward or backward updates. `注文確定`, cancellation, and refund are system-controlled, never selectable, and are displayed separately where relevant. Invalid, blocked, cancelled/refunding, or cross-Producer updates make no state change and produce a safe response.
- **AT-P-010:** Verify every `P10-*` row, authenticated Producer ownership, read-only financial integrity, and safe error/retry behavior. On desktop, selecting a payout-history row updates the detail region on the same page. On mobile, the overview remains in place while the selected row opens the P10 detail subview; Back returns to the same overview/list context. Verify that another Producer's payout identifier is denied and that the responsive detail subview does not create an additional inventory route or settlement module.
- **AT-P-011:** Verify every `P11-*` element row, its stated entry and navigation, the authenticated actor scope, and safe error/retry behavior. The circular avatar is the only photo-picker control. A local selection must not upload or replace the stored photo before `変更する` confirmation; `キャンセル`, picker cancellation, validation rejection, and upload failure preserve the previous photo. Successful confirmed upload updates only the signed-in Producer. Apply format/size/crop cases after TBD-003 approval.
- **AT-P-012:** Verify every `P12-*` row, its stated entry and navigation, authenticated Producer ownership, and safe error/retry behavior. Verify contact values load correctly, permitted edits retain safe valid input on failure, and no shop-profile-photo control is shown. A login-email change keeps the old email active until successful P03 verification and must not expose account-existence or cross-Producer information.
- **AT-P-013:** Verify `P13` satisfies every `P13-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.
- **AT-P-014:** Verify `P14` satisfies every `P14-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.
- **AT-P-015:** Verify `P15` satisfies every `P15-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.

### 18.3 Admin screens

- **AT-A-001:** Verify `A01` satisfies every `A01-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.
- **AT-A-002:** Verify `A02` satisfies every `A02-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.
- **AT-A-003:** Verify `A03` satisfies every `A03-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.
- **AT-A-004:** Verify `A04` satisfies every `A04-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.
- **AT-A-005:** Verify `A05` satisfies every `A05-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.
- **AT-A-006:** Verify `A06` satisfies every `A06-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.
- **AT-A-007:** Verify `A07` satisfies every `A07-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.
- **AT-A-008:** Verify `A08` satisfies every `A08-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.
- **AT-A-009:** Verify `A09` satisfies every `A09-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.
- **AT-A-010:** Verify `A10` satisfies every `A10-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.
- **AT-A-011:** Verify every `A11-*` row, authenticated Admin access, and safe error/retry behavior. The main view has only keyword search, date-range filtering, and the four columns 日時 / 内容 / 実行者 / 結果. Selecting a row opens that event's compact read-only dialog; closing preserves search, date range, and pagination. No edit/delete actions or sensitive raw data are exposed.

### 18.4 Cross-domain critical tests

- **AT-X-001:** A mixed-Producer cart is grouped by shop. Starting checkout for one group creates at most one uniquely referenced payment and one Buyer order for that selected Producer's Tenant only; it never combines different Producers, and all unselected shop groups remain in the cart.
- **AT-X-002:** A browser return without authoritative success cannot produce B10 or a Paid order.
- **AT-X-003:** Repeated, delayed, mismatched, forged, or out-of-order provider events produce no duplicate financial effect.
- **AT-X-004:** Cancellation within 30 minutes refunds the order's single Producer payment exactly once. After the deadline the system sets the order state to `注文確定`, B12 hides cancellation, and later issues route to B20; Producer fulfillment remains independent.
- **AT-X-005:** P04 cannot unlock selling until both Visa and Mastercard are `passed`; Admin cannot override the result.
- **AT-X-006:** Before P04 eligibility, no sidebar is rendered. The full-width authenticated header account dropdown exposes only the Producer's own Profile and Logout; Product, Order, Sales/Payout, bank, password, and full Account Settings destinations remain inaccessible by direct URL or identifier manipulation. When Visa and Mastercard both become `passed`, the current authenticated session redirects immediately to P05; every later login also opens P05 with the Producer operational sidebar limited to Dashboard, Product Management, Order Management, and Sales/Payout. The operational sidebar does not repeat a `Logged in` label, account name, or farm name in its footer; identity and account actions remain in the header account dropdown. After eligibility, the header account dropdown exposes Account Settings and Logout, and Account Settings opens P11. No standalone approval-complete P04 state is rendered.
- **AT-X-007:** Producer A cannot access Producer B's product, order, payout, Tenant, or bank identifiers.
- **AT-X-008:** Bank update requires recent re-authentication, leaves the previous account unchanged on failure, and logs no raw values.
- **AT-X-009:** Company commission, Producer-funded promotion, PAY.JP fees, refunds, adjustments, and net payout reconcile as distinct components.
- **AT-X-010:** A11 contains safe events for suspicious access and sensitive operations but no password, card, secret, raw bank, or unnecessary personal values.
- **AT-X-011:** For a one-unit pricing case, regional delivery calculation maps the authoritative delivery prefecture to exactly one of Honshu/Shikoku/Kyushu, Hokkaido, or Okinawa; adds that fee to the base product amount; applies any Producer-funded percentage discount to the complete amount; rounds in JPY according to the existing promotion rule; snapshots the fee and final amount at purchase; and exposes only one tax-and-shipping-inclusive Buyer amount. No municipality, package, weight, carrier, or external quotation data is required. Add quantity and multi-product aggregation cases only after TBD-009 approval.
- **AT-X-012:** Order creation and the order-accepted email are transactionally ordered and idempotent: the email is never dispatched before commit or for a failed/pending payment, and retrying the request, job, webhook, or browser refresh cannot send another accepted email for the same order.

## 19. Traceability

| Functional requirement | Screen requirement | Workbook element IDs | Acceptance test |
|---|---|---|---|
| `FR-B-001` | `SCR-B-001` / `B01` | `B01-01`, `B01-02`, `B01-03`, `B01-04`, `B01-05`, `B01-06`, `B01-07`, `B01-08` | `AT-B-001` |
| `FR-B-002` | `SCR-B-002` / `B02` | `B02-01`, `B02-02` | `AT-B-002` |
| `FR-B-003` | `SCR-B-003` / `B03` | `B03-01`, `B03-02`, `B03-03` | `AT-B-003` |
| `FR-B-004` | `SCR-B-004` / `B04` | `B04-01`, `B04-02`, `B04-03`, `B04-04`, `B04-05` | `AT-B-004` |
| `FR-B-005` | `SCR-B-005` / `B05` | `B05-01`, `B05-02`, `B05-03`, `B05-04` | `AT-B-005` |
| `FR-B-006` | `SCR-B-006` / `B06` | `B06-01`, `B06-02`, `B06-03`, `B06-04`, `B06-05` | `AT-B-006` |
| `FR-B-007` | `SCR-B-007` / `B07` | `B07-01`, `B07-02`, `B07-03`, `B07-04`, `B07-05` | `AT-B-007` |
| `FR-B-008` | `SCR-B-008` / `B08` | `B08-01`, `B08-02`, `B08-03` | `AT-B-008` |
| `FR-B-009` | `SCR-B-009` / `B09` | `B09-01`, `B09-02`, `B09-03`, `B09-04` | `AT-B-009` |
| `FR-B-010` | `SCR-B-010` / `B10` | `B10-01`, `B10-02`, `B10-03` | `AT-B-010` |
| `FR-B-011` | `SCR-B-011` / `B11` | `B11-01`, `B11-02`, `B11-03` | `AT-B-011` |
| `FR-B-012` | `SCR-B-012` / `B12` | `B12-01`, `B12-02`, `B12-03`, `B12-04`, `B12-05`, `B12-06`, `B12-07` | `AT-B-012` |
| `FR-B-013` | `SCR-B-013` / `B13` | `B13-01`, `B13-02` | `AT-B-013` |
| `FR-B-014` | `SCR-B-014` / `B14` | `B14-01`, `B14-02`, `B14-03`, `B14-04` | `AT-B-014` |
| `FR-B-015` | `SCR-B-015` / `B15` | `B15-01`, `B15-02`, `B15-03`, `B15-04` | `AT-B-015` |
| `FR-B-016` | `SCR-B-016` / `B16` | `B16-01`, `B16-02` | `AT-B-016` |
| `FR-B-017` | `SCR-B-017` / `B17` | `B17-01`, `B17-02`, `B17-03`, `B17-04` | `AT-B-017` |
| `FR-B-018` | `SCR-B-018` / `B18` | `B18-01`, `B18-02` | `AT-B-018` |
| `FR-B-019` | `SCR-B-019` / `B19` | `B19-01`, `B19-02` | `AT-B-019` |
| `FR-B-020` | `SCR-B-020` / `B20` | `B20-01`, `B20-02`, `B20-03` | `AT-B-020` |
| `FR-B-021` | `SCR-B-021` / `B21` | `B21-01` | `AT-B-021` |
| `FR-P-001` | `SCR-P-001` / `P01` | `P01-01`, `P01-02`, `P01-03` | `AT-P-001` |
| `FR-P-002` | `SCR-P-002` / `P02` | `P02-01`, `P02-02`, `P02-03`, `P02-04`, `P02-05`, `P02-06` | `AT-P-002` |
| `FR-P-003` | `SCR-P-003` / `P03` | `P03-01`, `P03-02`, `P03-03` | `AT-P-003` |
| `FR-P-004` | `SCR-P-004` / `P04` | `P04-01`, `P04-02`, `P04-03`, `P04-04`, `P04-05`, `P04-06` | `AT-P-004` |
| `FR-P-005` | `SCR-P-005` / `P05` | `P05-01`, `P05-02`, `P05-03` | `AT-P-005` |
| `FR-P-006` | `SCR-P-006` / `P06` | `P06-01`, `P06-02`, `P06-03`, `P06-04` | `AT-P-006` |
| `FR-P-007` | `SCR-P-007` / `P07` | `P07-01`, `P07-02`, `P07-03`, `P07-04`, `P07-05`, `P07-06`, `P07-07` | `AT-P-007` |
| `FR-P-008` | `SCR-P-008` / `P08` | `P08-01`, `P08-02`, `P08-03`, `P08-04` | `AT-P-008` |
| `FR-P-009` | `SCR-P-009` / `P09` | `P09-01`, `P09-02`, `P09-03`, `P09-04` | `AT-P-009` |
| `FR-P-010` | `SCR-P-010` / `P10` | `P10-01`, `P10-02`, `P10-03`, `P10-04`, `P10-05`, `P10-06` | `AT-P-010` |
| `FR-P-011` | `SCR-P-011` / `P11` | `P11-01`, `P11-02`, `P11-03`, `P11-04` | `AT-P-011` |
| `FR-P-012` | `SCR-P-012` / `P12` | `P12-01`, `P12-02`, `P12-03`, `P12-04` | `AT-P-012` |
| `FR-P-013` | `SCR-P-013` / `P13` | `P13-01`, `P13-02`, `P13-03`, `P13-04`, `P13-05` | `AT-P-013` |
| `FR-P-014` | `SCR-P-014` / `P14` | `P14-01`, `P14-02` | `AT-P-014` |
| `FR-P-015` | `SCR-P-015` / `P15` | `P15-01`, `P15-02`, `P15-03` | `AT-P-015` |
| `FR-A-001` | `SCR-A-001` / `A01` | `A01-01`, `A01-02`, `A01-03` | `AT-A-001` |
| `FR-A-002` | `SCR-A-002` / `A02` | `A02-01`, `A02-02`, `A02-03`, `A02-04`, `A02-05` | `AT-A-002` |
| `FR-A-003` | `SCR-A-003` / `A03` | `A03-01`, `A03-02`, `A03-03` | `AT-A-003` |
| `FR-A-004` | `SCR-A-004` / `A04` | `A04-01`, `A04-02`, `A04-03`, `A04-04`, `A04-05` | `AT-A-004` |
| `FR-A-005` | `SCR-A-005` / `A05` | `A05-01`, `A05-02`, `A05-03`, `A05-04` | `AT-A-005` |
| `FR-A-006` | `SCR-A-006` / `A06` | `A06-01`, `A06-02`, `A06-03`, `A06-04` | `AT-A-006` |
| `FR-A-007` | `SCR-A-007` / `A07` | `A07-01`, `A07-02`, `A07-03`, `A07-04`, `A07-05`, `A07-06` | `AT-A-007` |
| `FR-A-008` | `SCR-A-008` / `A08` | `A08-01`, `A08-02`, `A08-03`, `A08-04`, `A08-05`, `A08-06` | `AT-A-008` |
| `FR-A-009` | `SCR-A-009` / `A09` | `A09-01`, `A09-02`, `A09-03`, `A09-04`, `A09-05` | `AT-A-009` |
| `FR-A-010` | `SCR-A-010` / `A10` | `A10-01`, `A10-02`, `A10-03`, `A10-04`, `A10-05` | `AT-A-010` |
| `FR-A-011` | `SCR-A-011` / `A11` | `A11-01`, `A11-02`, `A11-03`, `A11-04`, `A11-05` | `AT-A-011` |

## 20. Explicit Current-Release Exclusions

- **OUT-001 `[OUT-OF-SCOPE]`** Internal Company approval of PAY.JP screening.
- **OUT-002 `[OUT-OF-SCOPE]`** A Producer sidebar and Product, Order, Sales/Payout, bank, password, or full Account Settings navigation/functionality before P04 selling eligibility. The Producer's own P12 Profile and Logout remain available only through the header account dropdown.
- **OUT-003 `[OUT-OF-SCOPE]`** Separate Producer inventory screens or modules for initial bank registration, standalone terms, application submitted, images/variants, payout history/detail, bank update, or Logout. The confirmed responsive mobile detail subview inside logical screen P10 is permitted and does not add a standalone inventory screen.
- **OUT-004 `[OUT-OF-SCOPE]`** Admin Password Reset, Admin Account Settings, standalone Audit History, manual Tenant creation, screening override, or payout execution/editing.
- **OUT-005 `[OUT-OF-SCOPE]`** Any payment method or provider capability not stated in the approved workbook.
