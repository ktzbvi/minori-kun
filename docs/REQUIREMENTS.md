# Minori-kun EC Marketplace — Product and System Requirements

## 1. Document Control

| Field | Value |
|---|---|
| Project | `みのりくん` (Minori-kun) agricultural and food EC marketplace |
| Document version | 0.3.1 |
| Status | Latest workbook-aligned implementation baseline |
| Last updated | 2026-09-02 (Asia/Tokyo) |
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
| 3 | Buyer purchase | Buyers browse products and purchase products from multiple Producers through one cart using PAY.JP. | The system processes payment by Producer while presenting one Buyer order. | — |
| 4 | Platform operations | Admin oversees Buyer/inquiry support, Producer status, categories, banners, products, orders/refunds, sales and Company commission summaries, PAY.JP payout status, and system/security logs. | PAY.JP makes screening decisions and pays Producers directly; Admin monitors status and handles internal operations. | — |

## 4. Actors and Permissions

| Actor | Main role | Data visible | Constraints |
|---|---|---|---|
| Buyer | Search products, manage cart, complete payment, and view or update own orders/profile. | Public catalog and own account/order data. | — |
| Producer | Manage own products, orders, delivery, sales, settlements, and payouts. | Own products, order lines, sales, settlements, and payouts. | — |
| Admin | Oversee Buyer/inquiry support, Producer operations, categories, banners, products, orders/refunds, sales and Company commission, PAY.JP payout status, and system/security logs. | Authorized platform information, Company commission and payout status, and system/security logs. | Never display card data; bank information is masked. |

- **BR-001 `[CONFIRMED]`** The service is a Company-operated marketplace in which Producers sell agricultural and food products to Buyers.
- **BR-002 `[CONFIRMED]`** Buyer, Producer, and Admin identities and authorization scopes are separate.
- **BR-003 `[CONFIRMED]`** Every Producer query and mutation must enforce ownership server-side.
- **BR-004 `[CONFIRMED]`** Buyers must never see internal Producer settlement, payout, or Company-commission information.
- **BR-005 `[CONFIRMED]`** Admin uses one accountable Admin role for the current release; there is no Admin Account Settings or Admin Password Reset screen.

## 5. Marketplace Business Rules

- **BR-006 `[CONFIRMED]` — Company commission:** For each Producer payment in PAY.JP, the system automatically separates Producer sales and the Company's 10% commission. Owner: System / PAY.JP.
- **BR-007 `[CONFIRMED]` — Screening and selling eligibility:** After Producer registration, the system issues a one-time PAY.JP screening form URL. Product registration and sales begin only after PAY.JP approves both Visa and Mastercard. Owner: Producer / System / PAY.JP. Note: No internal Company approval. Product management and selling remain unavailable before eligibility.
- **BR-008 `[CONFIRMED]` — Delivery and order status:** Each Producer ships their own products and manually updates order status from the Producer Portal. Owner: Producer. Note: Expected states: Received → Processing → Shipped.
- **BR-009 `[CONFIRMED]` — Settlement and payout:** PAY.JP applies fees/adjustments to Producer sales and pays each Producer directly. Use month-end close, payout at the end of the following month, and a ¥1,000 minimum. Owner: PAY.JP / System. Note: Carry amounts below the minimum forward. Producer transfer fee is ¥250 including tax.
- **BR-010 `[CONFIRMED]` — Order cancellation and refund:** A Buyer may cancel an order only within 30 minutes after completion. After confirmation, refund every underlying Producer PAY.JP payment exactly once; after the deadline, guide the Buyer to Contact. Owner: Buyer / System / PAY.JP. Note: Prevent duplicate refunds and keep refund/settlement effects auditable.
- **BR-011 `[CONFIRMED]`** Each Producer is the seller and a PAY.JP Tenant. The Company operates the platform.
- **BR-012 `[CONFIRMED]`** One Buyer order may contain multiple Producers, while the system processes payment by Producer and exposes one coherent Buyer order.
- **BR-013 `[CONFIRMED]`** PAY.JP screening—not an internal Company approval—determines selling eligibility. Both Visa and Mastercard must be `passed` before selling functions become available.
- **BR-014 `[CONFIRMED]`** The Company's 10% commission and any Producer-funded customer promotion are separate calculations and labels.
- **BR-015 `[CONFIRMED]`** A payment or hosted-form browser return is never authoritative proof of success; authoritative PAY.JP API or webhook information controls state.
- **BR-016 `[CONFIRMED]`** Product edits never change purchase-time OrderItem, Producer, variant, price, promotion, or delivery snapshots.
- **BR-017 `[CONFIRMED]`** Admin may monitor PAY.JP screening and support application issues but cannot create Tenants manually or override card-brand results.
- **BR-018 `[CONFIRMED]`** Sensitive administrative and financial actions require a safe audit/log event without raw card, credential, or bank values.

## 6. End-to-End Flows

### 6.1 Buyer purchase

Product List → Product Detail → Cart → Order Confirmation → PAY.JP payment by Producer → Server Verification → Order Complete

**Owner:** Buyer / System / PAY.JP. **Note:** Present one order to the Buyer.

### 6.2 Producer operation

Registration → Email Verification and Producer Terms Acceptance → PAY.JP Tenant Creation → Hosted Application → PAY.JP Screening → Visa and Mastercard Passed → Product, Order, Delivery, and Payout Management

**Owner:** Producer / System / PAY.JP. **Note:** Generate the application URL on action and reissue it from the same screen when expired or correction is required.

### 6.3 Admin activities

• Support Buyer inquiries and Producer/PAY.JP screening operations
• Manage categories and the Home recommendation banner
• Monitor products, orders, refunds, and chargebacks
• Review sales, Company commission, and PAY.JP payout status
• Monitor failed logins, unauthorized access attempts, payment callback anomalies, and system errors

**Owner:** Admin. **Note:** PAY.JP makes screening decisions and pays Producers; Admin monitors status. Logs are read-only and exclude sensitive values.

The Producer onboarding sequence is `Registration → Email Verification and Producer Terms Acceptance → PAY.JP Tenant Creation → Hosted Application → PAY.JP Screening → Visa and Mastercard Passed → Product, Order, Delivery, and Payout Management`. Initial bank information is collected by the PAY.JP-hosted application, not the local registration screen.

## 7. Screen Inventory and Estimate

| No. | Screen ID | Area | Screen name | Japanese screen name | Summary | Hours | Days |
|---:|---|---|---|---|---|---:|---:|
| 1 | `B01` | Buyer | Home / Product List | `商品一覧（ホーム）` | Entry point for discovering published products and opening Product Detail or Cart. | 8 | 1 |
| 2 | `B02` | Buyer | Category | `カテゴリ` | Browse products by category. | 2 | 0.25 |
| 3 | `B03` | Buyer | Category Product List | `カテゴリ別商品一覧` | Show only products in the selected category. | 2 | 0.25 |
| 4 | `B04` | Buyer | Search | `検索` | Search products by keyword and show matching results or a no-results recovery message on the same screen. | 8 | 1 |
| 5 | `B05` | Buyer | Product Detail | `商品詳細` | Review product details and selected variant price/stock before purchase. | 24 | 3 |
| 6 | `B06` | Buyer | Cart | `カート` | Review/edit cart items and show empty-cart recovery when no items exist. | 32 | 4 |
| 7 | `B07` | Buyer | Order Confirmation | `注文内容の確認` | Confirm order details, delivery address, totals, and proceed to payment. | 10 | 1.25 |
| 8 | `B08` | Buyer | Checkout Delivery Address Edit | `チェックアウト配送先編集` | Edit the delivery address for the current order and optionally save it as the default address. | 4 | 0.5 |
| 9 | `B09` | Buyer | PAY.JP Payment Processing | `PAY.JP決済処理` | Safely process PAY.JP payments by Producer and authoritative server-side verification. | 48 | 6 |
| 10 | `B10` | Buyer | Order Complete | `注文完了` | Show an authoritatively paid order result. | 2 | 0.25 |
| 11 | `B11` | Buyer | Order History | `注文履歴` | Review the Buyer's own orders. | 4 | 0.5 |
| 12 | `B12` | Buyer | Order Detail | `注文詳細` | Review purchase-time order and delivery snapshots and current states. | 4 | 0.5 |
| 13 | `B13` | Buyer | Login | `ログイン` | Sign in to a Buyer account. | 4 | 0.5 |
| 14 | `B14` | Buyer | Member Registration | `新規会員登録` | Create a Buyer account with one default delivery address. | 4 | 0.5 |
| 15 | `B15` | Buyer | Email Verification | `メール確認` | Confirm buyer email ownership after registration or email change. | 4 | 0.5 |
| 16 | `B16` | Buyer | Password Reset | `パスワード再設定` | Reset a password without revealing account existence. | 4 | 0.5 |
| 17 | `B17` | Buyer | My Page | `マイページ` | Provide the Buyer account hub, including logout confirmation. | 2 | 0.25 |
| 18 | `B18` | Buyer | Member Information Update | `会員情報の変更` | Update the Buyer's own profile and default delivery address. | 4 | 0.5 |
| 19 | `B19` | Buyer | Password Change | `パスワード変更` | Allow a signed-in Buyer to change password. | 4 | 0.5 |
| 20 | `B20` | Buyer | Contact | `お問い合わせ` | Send a support inquiry. | 1 | 0.125 |
| 21 | `B21` | Buyer | Contact Complete | `お問い合わせ完了` | Confirm inquiry receipt without duplication. | 1 | 0.125 |
| 22 | `P01` | Producer | Producer Login | `生産者ログイン` | Sign in with a Producer account separate from Buyer. | 2 | 0.25 |
| 23 | `P02` | Producer | Producer Registration | `生産者登録` | Create a Producer account, enter the minimum required profile information, and accept the Producer Terms, including the Company's 10% commission. | 4 | 0.5 |
| 24 | `P03` | Producer | Email Verification | `メール確認` | Confirm ownership of the registered email address. | 2 | 0.25 |
| 25 | `P04` | Producer | PAY.JP Application / Screening Status | `PAY.JP申請・審査状況` | Generate the PAY.JP application URL, open the hosted form, and handle screening status, expiry, correction, and reissue on one screen. | 8 | 1 |
| 26 | `P05` | Producer | Producer Dashboard | `生産者ダッシュボード` | Dedicated landing page shown after login. Displays read-only summary cards for the Producer's products, orders requiring action, sales, and payout alerts, with links to the related Producer screens. | 2 | 0.25 |
| 27 | `P06` | Producer | Producer Product List | `生産者商品一覧` | Create, edit, publish, or unpublish own products. | 4 | 0.5 |
| 28 | `P07` | Producer | Product Create / Edit | `商品作成・編集` | Set product information, category, images, variants, price, stock, promotion, and publication state on one screen. | 10 | 1.25 |
| 29 | `P08` | Producer | Producer Order List | `生産者注文一覧` | Process Producer sub-orders containing own products. | 4 | 0.5 |
| 30 | `P09` | Producer | Producer Order Detail | `生産者注文詳細` | Review own lines and minimum delivery information, then update order status. | 4 | 0.5 |
| 31 | `P10` | Producer | Payout | `振込` | Review period sales, the Company's 10% commission, refunds and adjustments, payout history, and detailed payout breakdown on one read-only page. | 6 | 0.75 |
| 32 | `P11` | Producer | Producer Account Settings | `生産者アカウント設定` | Open account-related settings for Producer Account Information, Payout Bank Account Information, Password Change, and Logout. | 1 | 0.125 |
| 33 | `P12` | Producer | Producer Account Information | `生産者アカウント情報` | Review and update the Producer's basic profile and contact information on the same screen. | 3 | 0.375 |
| 34 | `P13` | Producer | Payout Bank Account Information | `振込口座情報` | Display saved bank account information only in masked form with its bank status. After re-authentication, update bank information on the same screen and send the changes to the PAY.JP Tenant API. | 5 | 0.625 |
| 35 | `P14` | Producer | Producer Password Change | `生産者パスワード変更` | Allow a signed-in Producer to change password. | 2 | 0.25 |
| 36 | `P15` | Producer | Producer Password Reset | `生産者パスワード再設定` | Recover Producer access without revealing account state. | 2 | 0.25 |
| 37 | `A01` | Admin | Admin Login | `管理者ログイン` | Sign in with an Admin account separate from Buyer and Producer accounts. | 2 | 0.25 |
| 38 | `A02` | Admin | Admin Dashboard | `管理ダッシュボード` | Summarize PAY.JP screening, payment exceptions, product/banner, order/refund, and payout states needing action. | 4 | 0.5 |
| 39 | `A03` | Admin | Buyer / Inquiry Management | `購入者・問い合わせ管理` | Find Buyers and inquiries and perform account/support operations using only the minimum required data. | 4 | 0.5 |
| 40 | `A04` | Admin | Producer Management | `生産者管理` | Search and filter Producers, then review the selected Producer account, PAY.JP Tenant information, card-brand screening, selling eligibility, application/support history, and operational status. Suspend or reactivate with an audit trail. | 6 | 0.75 |
| 41 | `A05` | Admin | Category Management | `カテゴリ管理` | Create, edit, enable, or disable shared categories used by Buyers and Producers. | 3 | 0.375 |
| 42 | `A06` | Admin | Banner Management | `バナー管理` | Review and control the Today's Recommendations banner automatically selected from the top three selling products. Producers cannot control it. | 3 | 0.375 |
| 43 | `A07` | Admin | Product Management | `商品管理` | Search and filter products across Producers, then review the selected product details, variants, stock, publication status, and promotions. Unpublish or request correction with a reason when needed and record the action in the audit history. | 5 | 0.625 |
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
- **FR-B-005 `[CONFIRMED]`** The system shall provide `B05` Product Detail to review product details and selected variant price/stock before purchase.
- **FR-B-006 `[CONFIRMED]`** The system shall provide `B06` Cart to review and edit cart items, or show an empty-cart recovery state when no items exist.
- **FR-B-007 `[CONFIRMED]`** The system shall provide `B07` Order Confirmation to confirm order details, delivery address, totals, and proceed to payment.
- **FR-B-008 `[CONFIRMED]`** The system shall provide `B08` Checkout Delivery Address Edit to edit the delivery address for the current order and optionally save it as the default address.
- **FR-B-009 `[CONFIRMED]`** The system shall provide `B09` PAY.JP Payment Processing to safely process PAY.JP payments by Producer, 3D Secure authentication, and authoritative server-side results.
- **FR-B-010 `[CONFIRMED]`** The system shall provide `B10` Order Complete to show an authoritatively paid order result.
- **FR-B-011 `[CONFIRMED]`** The system shall provide `B11` Order History to review the Buyer's own orders.
- **FR-B-012 `[CONFIRMED]`** The system shall provide `B12` Order Detail to review purchase-time order and delivery snapshots and current states.
- **FR-B-013 `[CONFIRMED]`** The system shall provide `B13` Login to sign in to a Buyer account.
- **FR-B-014 `[CONFIRMED]`** The system shall provide `B14` Member Registration to create a Buyer account with one default delivery address.
- **FR-B-015 `[CONFIRMED]`** The system shall provide `B15` Email Verification to confirm buyer email ownership after registration or email address change.
- **FR-B-016 `[CONFIRMED]`** The system shall provide `B16` Password Reset to reset a password without revealing account existence.
- **FR-B-017 `[CONFIRMED]`** The system shall provide `B17` My Page to provide the Buyer account hub.
- **FR-B-018 `[CONFIRMED]`** The system shall provide `B18` Member Information Update to update the Buyer profile and default delivery address.
- **FR-B-019 `[CONFIRMED]`** The system shall provide `B19` Password Change to allow a signed-in Buyer to change password.
- **FR-B-020 `[CONFIRMED]`** The system shall provide `B20` Contact to send a support inquiry.
- **FR-B-021 `[CONFIRMED]`** The system shall provide `B21` Contact Complete to confirm inquiry receipt without duplication.

### 8.2 Producer

- **FR-P-001 `[CONFIRMED]`** The system shall provide `P01` Producer Login to sign in with a Producer account separate from Buyer.
- **FR-P-002 `[CONFIRMED]`** The system shall provide `P02` Producer Registration to create a Producer account with the minimum internal profile and accept the Producer Terms.
- **FR-P-003 `[CONFIRMED]`** The system shall provide `P03` Email Verification to confirm ownership of a new or changed Producer login email.
- **FR-P-004 `[CONFIRMED]`** The system shall provide `P04` PAY.JP Application / Screening Status to complete the PAY.JP hosted application and track card-brand screening before selling.
- **FR-P-005 `[CONFIRMED]`** The system shall provide `P05` Producer Dashboard to provide the Producer's landing page and own operational summaries.
- **FR-P-006 `[CONFIRMED]`** The system shall provide `P06` Producer Product List to manage only the signed-in Producer's products.
- **FR-P-007 `[CONFIRMED]`** The system shall provide `P07` Product Create / Edit to create or edit complete product selling information on one screen.
- **FR-P-008 `[CONFIRMED]`** The system shall provide `P08` Producer Order List to process Producer sub-orders containing only the Producer's products.
- **FR-P-009 `[CONFIRMED]`** The system shall provide `P09` Producer Order Detail to review own order lines and minimum delivery information, then manually update fulfillment.
- **FR-P-010 `[CONFIRMED]`** The system shall provide `P10` Payout to review own sales, settlement calculation, payout history, and documents on one read-only page.
- **FR-P-011 `[CONFIRMED]`** The system shall provide `P11` Producer Account Settings to provide one hub for Producer account-related settings and logout.
- **FR-P-012 `[CONFIRMED]`** The system shall provide `P12` Producer Account Information to review and update the Producer's internal profile, contact information, and login email on one screen.
- **FR-P-013 `[CONFIRMED]`** The system shall provide `P13` Payout Bank Account Information to review masked payout-bank data and update it securely on the same screen.
- **FR-P-014 `[CONFIRMED]`** The system shall provide `P14` Producer Password Change to change the Producer password securely from Account Settings.
- **FR-P-015 `[CONFIRMED]`** The system shall provide `P15` Producer Password Reset to recover Producer access without revealing account state.

### 8.3 Admin

- **FR-A-001 `[CONFIRMED]`** The system shall provide `A01` Admin Login to sign in to the protected Admin Portal with the authorized Admin account.
- **FR-A-002 `[CONFIRMED]`** The system shall provide `A02` Admin Dashboard to show the Admin's operational overview and direct links to items requiring attention.
- **FR-A-003 `[CONFIRMED]`** The system shall provide `A03` Buyer / Inquiry Management to handle Buyer account support and inquiries using only the minimum required data.
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
- **Summary:** Home / Product List is the Buyer entry page for discovering published products. Buyers can browse product cards, use category quick filters, open the Search page, open Cart, or move to Product Detail. The page also includes a system-controlled Banner that automatically recommends the most purchased eligible products for today; Producers cannot control the Banner in V1. A purchasable single-variant product can be added directly from the list, while products that require variant selection open Product Detail first. Producer/Farm name is not shown on list cards in V1.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `B01-01` | Banner / `バナー` | Show an Admin/System-controlled banner with the top purchased eligible products as Today's Recommendation. Producers cannot control the banner in V1. | 管理者／システム管理のバナーとして、購入数の多い対象商品を「本日のおすすめ」として表示する。V1では生産者がバナーを操作しない。 | Tap a recommended product. | Open Product Detail page. |
| `B01-02` | Search / `検索` | Show a search icon in the header. | ヘッダーに検索アイコンを表示する。 | Tap Search. | Open Search page. |
| `B01-03` | Category quick filter / `カテゴリ簡易絞り込み` | Show shared Admin-managed categories as quick filters; Producers cannot create categories. | 管理者が用意した共通カテゴリを簡易絞り込みとして表示する。生産者独自カテゴリは使用しない。 | Select a category. | Filter products on Home and retain the selected state. |
| `B01-04` | Product card / `商品カード` | Show product image, product name, price, promotion display, stock/sold-out state, and cart action. Producer/Farm name is not shown on the list card in V1. | 商品画像、商品名、価格、プロモーション表示、在庫／売り切れ状態、カート操作を表示する。V1の商品一覧カードには生産者名／農園名を表示しない。 | Tap the card body. | Open Product Detail page. |
| `B01-05` | Add to Cart / `カートに追加` | Show Add to Cart for a purchasable single-variant item. | 購入可能な単一バリエーション商品には追加ボタンを表示する。 | Tap Add to Cart. | Add quantity 1 and increase the cart count without opening Product Detail. |
| `B01-06` | Multiple variants / `複数バリエーション` | Indicate when variant selection is required because price or stock differs by variant. | 価格または在庫がバリエーションごとに異なる場合、選択が必要であることを表示する。 | Tap the cart action. | Open Product Detail page and add only after variant selection. |
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
| `B02-01` | Category cards / `カテゴリカード` | Show enabled Admin-managed categories such as All, Vegetables, Fruit, Rice/Grains, and Sets. | すべて／野菜／果物／米・穀物／セット等の有効な管理者管理カテゴリを表示する。 | Tap a category card. | Open Category Product List page filtered by the selected category. |
| `B02-02` | Header / bottom navigation / `ヘッダー／下部ナビゲーション` | Show page title, search/cart access, and bottom navigation: Home / Category / Cart / My Page. | 画面名、検索／カート導線、下部ナビ「ホーム／カテゴリ／カート／マイページ」を表示する。 | Tap a navigation item. | Open the selected root page. |

- **Acceptance:** All `B02-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

### SCR-B-003 — B03 Category Product List / `カテゴリ別商品一覧`

- **Status:** `[CONFIRMED]`
- **Actor:** Guest / Buyer
- **Purpose:** Show only products in the selected category.
- **Main entry:** Category page
- **Summary:** Category Product List shows products filtered by the category selected on the Category page. Buyers can confirm the selected category name, browse matching product cards, open Product Detail page, or add purchasable single-variant products to the same cart. This page does not show the Home banner, but it follows the same product card, price, promotion, stock, variant, and multi-producer cart behavior as Home / Product List.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `B03-01` | Selected category / `選択カテゴリ` | Show the selected category name and matching products. Do not show the Home banner. | 選択中カテゴリ名と該当商品を表示する。ホームのバナーは表示しない。 | Tap a product card. | Open Product Detail page. |
| `B03-02` | Cart addition / `カート追加` | Use the same price, promotion, stock, variant, and multi-producer cart rules as Home / Product List. | 商品一覧（ホーム）と同じ価格、プロモーション、在庫、バリエーション、複数生産者カートの規則を使用する。 | Tap the cart action. | Add to the same cart and update the count. |
| `B03-03` | Header / bottom navigation / `ヘッダー／下部ナビゲーション` | Show page title, Back action, search/cart access, and bottom navigation: Home / Category / Cart / My Page. | 画面名、戻る操作、検索／カート導線、下部ナビ「ホーム／カテゴリ／カート／マイページ」を表示する。 | Tap Back or a navigation item. | Return to Category page or open the selected root page. |

- **Acceptance:** All `B03-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

### SCR-B-004 — B04 Search / `検索`

- **Status:** `[CONFIRMED]`
- **Actor:** Guest / Buyer
- **Purpose:** Search products by keyword and show matching results or a no-results recovery message.
- **Main entry:** Header Search
- **Summary:** Search page lets Guests and Buyers search published products by keyword. It opens from the header search action, keeps the entered keyword and result count, shows matching product cards, and keeps the same product-card, price, promotion, stock, variant, and multi-producer cart rules as the other Buyer product-list pages. When no products match, the same Search page shows a clear no-results message and lets the user edit the keyword or return to Home.

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
- **Purpose:** Review product details and selected variant price/stock before purchase.
- **Main entry:** Product List / Search / Category
- **Summary:** The Product Detail page is opened from a product list, category list, or search result. It presents the selected product images, name, public producer name, price and promotion, description, available variants, stock, and quantity controls. Buyers can review the current selection and add it to the cart or proceed toward checkout. Variant, stock, and quantity rules are checked before the action, and the page provides clear feedback when the product cannot be purchased. Company commission and internal settlement information are not shown to Buyers.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `B05-01` | Product information / `商品情報` | Show images, name, Producer/farm name, description, and promotion. Public Producer data is name only. | 画像、商品名、生産者名、説明、プロモーションを表示する。公開生産者情報は名称のみ。 | Review images and description. | Remain on Product Detail. |
| `B05-02` | Variants / `バリエーション` | Show price and stock for each variant. | 各バリエーションの価格と在庫を表示する。 | Select a variant. | Update displayed price, stock, and availability. |
| `B05-03` | Quantity / cart / `数量／カート` | Show quantity, Add to Cart, and Buy Now. | 数量とカート追加、今すぐ購入を表示する。 | Choose quantity and act. | After stock validation, add to Cart or proceed toward Checkout. |
| `B05-04` | Header / navigation / `ヘッダー／ナビゲーション` | Show title, Back, search/cart, or bottom navigation as appropriate. | 画面名、戻る、検索・カートまたは下部ナビを画面用途に応じて表示する。 | Tap Back or a navigation item. | Open the prior or selected root screen. Home alone has no Back action. |

- **Acceptance:** All `B05-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

### SCR-B-006 — B06 Cart / `カート`

- **Status:** `[CONFIRMED]`
- **Actor:** Guest / Buyer
- **Purpose:** Review and edit cart items, or show an empty-cart recovery state when no items exist.
- **Main entry:** Cart icon / product addition
- **Summary:** The Cart lets Buyers review and adjust items, quantities, promotions, and totals across Producers, then continue to Order Confirmation; an empty cart returns to Home.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `B06-01` | Cart lines / `商品明細` | Group lines by Producer and show product, variant, quantity, and price. | 生産者単位にグループ化し、商品・バリエーション・数量・価格を表示する。 | Change quantity or remove an item. | Revalidate stock/price and update totals. |
| `B06-02` | Promotion / `プロモーション` | Show regular price, promotional price, and rate per eligible line; never show commission. | 対象行に通常価格、割引後価格、割引率を表示する。会社手数料は表示しない。 | Review pricing. | Calculate totals using product-level promotions. |
| `B06-03` | Proceed to Checkout / `注文確認へ` | Show the final total and Review Order button. | 最終合計と注文内容確認ボタンを表示する。 | Tap the button. | Open B13 if logged out; otherwise B07. |
| `B06-04` | Header / navigation / `ヘッダー／ナビゲーション` | Show title, Back, search/cart, or bottom navigation as appropriate. | 画面名、戻る、検索・カートまたは下部ナビを画面用途に応じて表示する。 | Tap Back or a navigation item. | Open the prior or selected root screen. Home alone has no Back action. |
| `B06-05` | Empty-cart state / `空カート状態` | Show an empty-cart message and Return to Product List action. | 空カートメッセージと商品一覧へ戻る操作を表示する。 | Tap Return to Product List. | Open Home (B01). |

- **Acceptance:** All `B06-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

### SCR-B-007 — B07 Order Confirmation / `注文内容の確認`

- **Status:** `[CONFIRMED]`
- **Actor:** Buyer
- **Purpose:** Confirm order details, delivery address, totals, and proceed to payment.
- **Main entry:** Cart (B06) / after login
- **Summary:** Order Confirmation brings together the current order lines, delivery address, promotions, and total for a final review, then starts PAY.JP payment without selecting a payment method locally.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `B07-01` | Delivery address / `お届け先` | Show the single registered address and a Change action. | 登録済みの1件の住所を表示し、変更操作を表示する。 | Tap Change. | Open Delivery Address Change (B08). |
| `B07-02` | Order summary / `注文明細` | Group products by Producer and show variant, quantity, promotion, and totals. | 生産者ごとに商品、バリエーション、数量、プロモーション、合計を表示する。 | Review the order. | Revalidate against server price and stock. |
| `B07-03` | Proceed to PAY.JP Payment / `PAY.JP決済へ進む` | Show Proceed to Payment; do not show a local payment-method selector. | 決済画面へ進むボタンを表示し、ローカルの決済方法選択は置かない。 | Tap once. | Initialize PAY.JP payment processing and open B09. |
| `B07-04` | Delivery address / `配送先住所` | Show the default delivery address or current order address snapshot. | 基本配送先住所または今回の注文用住所を表示する。 | Tap Edit delivery address. | Open B08 or equivalent checkout address edit presentation. |
| `B07-05` | Header / navigation / `ヘッダー／ナビゲーション` | Show title, Back, search/cart, or bottom navigation as appropriate. | 画面名、戻る、検索・カートまたは下部ナビを画面用途に応じて表示する。 | Tap Back or a navigation item. | Open the prior or selected root screen. Home alone has no Back action. |

- **Acceptance:** All `B07-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

### SCR-B-008 — B08 Checkout Delivery Address Edit / `チェックアウト配送先編集`

- **Status:** `[CONFIRMED]`
- **Actor:** Buyer
- **Purpose:** Edit the delivery address for the current order and optionally save it as the default address.
- **Main entry:** Order Confirmation (B07)
- **Summary:** This checkout-time screen edits the delivery address snapshot for the current order and can optionally update the Buyer default address before returning to Order Confirmation.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `B08-01` | Delivery address form / `配送先入力フォーム` | Show recipient, phone, postal code, prefecture/city/address fields. | 宛名、電話番号、郵便番号、都道府県、市区町村、住所を表示する。 | Edit fields and save. | Update the current order delivery address snapshot. |
| `B08-02` | Save as default / `基本住所として保存` | Show optional Save as default delivery address checkbox. | 「基本配送先住所として保存する」チェックボックスを表示する。 | Select and save. | If selected, update the Buyer profile default address as well. |
| `B08-03` | Cancel / return / `キャンセル／戻る` | Show Cancel or Back action. | キャンセルまたは戻る操作を表示する。 | Cancel editing. | Return to Order Confirmation (B07) without changing the current order address. |

- **Acceptance:** All `B08-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

### SCR-B-009 — B09 PAY.JP Payment Processing / `PAY.JP決済処理`

- **Status:** `[CONFIRMED]`
- **Actor:** Buyer
- **Purpose:** Safely process PAY.JP payments by Producer, 3D Secure authentication, and authoritative server-side results.
- **Main entry:** Order Confirmation (B07) / PAY.JP authentication return
- **Summary:** The system processes cart items by Producer and creates a payment for each Producer's PAY.JP Tenant. When required, the Buyer completes 3D Secure authentication. A browser return alone never marks the order paid; the overall payment state is determined only from authoritative PAY.JP API or webhook results.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `B09-01` | Payment initiation / `決済開始` | Show payment-processing guidance. | 決済処理中の案内を表示する。 | Confirm the order and start payment. | Revalidate price and stock server-side, group the cart by Producer, and create a uniquely referenced payment for each PAY.JP Tenant. Never store card numbers or security codes. |
| `B09-02` | 3D Secure authentication / `3Dセキュア認証` | Show authentication guidance when required. | 認証が必要な場合は認証案内を表示する。 | Complete authentication with the card issuer. | Follow the PAY.JP-directed authentication flow and resume server-side verification after return. Never treat the browser return alone as success. |
| `B09-03` | Result verification / `決済結果確認` | Show Verifying, Completed, Failed, Cancelled, or Pending. | 確認中、完了、失敗、取消、処理保留を表示する。 | Check status or retry safely. | Update each payment only from authoritative PAY.JP API or webhook information and prevent duplicate execution of the same attempt. |
| `B09-04` | Completion and recovery / `完了・回復` | Show the overall order result and the next available action. | 注文全体の結果と次の操作を表示する。 | Open completion or follow the recovery guidance. | Open B10 only when every required payment succeeds. For failure, cancellation, partial success, or pending states, do not mark the overall order paid; prevent duplicate charges and provide safe recovery or return to B07/B06. |

- **Acceptance:** All `B09-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

### SCR-B-010 — B10 Order Complete / `注文完了`

- **Status:** `[CONFIRMED]`
- **Actor:** Buyer
- **Purpose:** Show an authoritatively paid order result.
- **Main entry:** PAY.JP Payment Processing (B09)
- **Summary:** Order Complete shows the order number and paid amount only after authoritative payment confirmation, with safe links to Order Detail or Home and no duplicate processing on refresh.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `B10-01` | Completion details / `完了情報` | Show completion message, order number, and paid amount. | 完了メッセージ、注文番号、支払金額を表示する。 | Choose Order Detail or Home. | Open B12 or B01. |
| `B10-02` | Safe refresh / `再表示` | Allow safe redisplay of the same result. | 同じ注文結果を再表示できる。 | Refresh. | Do not re-run payment or order creation. |

- **Acceptance:** All `B10-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

### SCR-B-011 — B11 Order History / `注文履歴`

- **Status:** `[CONFIRMED]`
- **Actor:** Buyer
- **Purpose:** Review the Buyer's own orders.
- **Main entry:** My Page (B17)
- **Summary:** Order History, accessed from My Page, lists the signed-in Buyer’s own orders chronologically with key order and payment states, and opens Order Detail for review.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `B11-01` | Order list / `注文一覧` | Show order number, date, amount, order state, and payment state. | 注文番号、注文日、金額、注文状態、支払状態を表示する。 | Tap an order row. | Open Order Detail (B12). |
| `B11-02` | Header / navigation / `ヘッダー／ナビゲーション` | Show title, Back, search/cart, or bottom navigation as appropriate. | 画面名、戻る、検索・カートまたは下部ナビを画面用途に応じて表示する。 | Tap Back or a navigation item. | Open the prior or selected root screen. Home alone has no Back action. |

- **Acceptance:** All `B11-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

### SCR-B-012 — B12 Order Detail / `注文詳細`

- **Status:** `[CONFIRMED]`
- **Actor:** Buyer
- **Purpose:** Review purchase-time order and delivery snapshots and current states.
- **Main entry:** Order History (B11)
- **Summary:** Order Detail presents the Buyer’s purchase-time line and delivery snapshots together with current order, payment, and refund states, plus a support route for that order. Within 30 minutes after completion, the Buyer may cancel through a confirmation dialog.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `B12-01` | Order states / `注文状態` | Show order, payment, and refund states separately. | 注文、支払、返金の状態を分けて表示する。 | Review states. | Display the history without modification. |
| `B12-02` | Purchased lines / `購入明細` | Show purchase-time product, Producer, variant, price, promotion, and quantity snapshots. | 購入時の商品名、生産者、バリエーション、価格、割引、数量を表示する。 | Review lines. | Keep history unchanged after catalog edits. |
| `B12-03` | Delivery address snapshot / `配送先スナップショット` | Show the delivery address used for this order at purchase time. | 購入時点でこの注文に使用した配送先住所を表示する。 | Review delivery destination. | May differ from the current Buyer default address. |
| `B12-04` | Contact support / `問い合わせ` | Show a support link carrying the order reference. | 注文番号付き問い合わせ導線を表示する。 | Tap Contact. | Open Contact (B20). |
| `B12-05` | Header / navigation / `ヘッダー／ナビゲーション` | Show title, Back, search/cart, or bottom navigation as appropriate. | 画面名、戻る、検索・カートまたは下部ナビを画面用途に応じて表示する。 | Tap Back or a navigation item. | Open the prior or selected root screen. Home alone has no Back action. |
| `B12-06` | Order cancellation / `注文キャンセル` | Show cancellation and a confirmation dialog only within 30 minutes after order completion; hide the action after the deadline. | 注文完了後30分以内の場合のみキャンセル操作と確認ダイアログを表示する。期限後はキャンセル操作を表示しない。 | Review and confirm cancellation. | Refund every underlying Producer PAY.JP payment exactly once and update order, payment, and refund states. Repeated actions must not create another refund; after the deadline, guide the Buyer to Contact (B20). |

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
- **Purpose:** Create a Buyer account with one default delivery address.
- **Main entry:** Login (B13)
- **Summary:** Member Registration creates a Buyer account and one default delivery address, sends email verification, and guides the Buyer to verify before checkout or payment; it has no bottom navigation.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `B14-01` | Member information / `会員情報` | Enter name, phonetic name, email, phone, postal code, and address. | 氏名、フリガナ、メール、電話、郵便番号、住所を入力する。 | Complete required fields. | Validate formats and duplicate email. |
| `B14-02` | Password / consent / `パスワード／同意` | Show password, confirmation, and terms consent. | パスワード、確認、利用規約同意を表示する。 | Consent and register. | Create the account in an unverified-email state, send a verification email, and guide the Buyer to verify before checkout/payment. |
| `B14-03` | Existing Buyer / `既存会員` | Show a Login link. | ログインへ戻るリンクを表示する。 | Tap the link. | Open Login (B13). |

- **Acceptance:** All `B14-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

### SCR-B-015 — B15 Email Verification / `メール確認`

- **Status:** `[CONFIRMED]`
- **Actor:** Guest / Buyer
- **Purpose:** Confirm buyer email ownership after registration or email address change.
- **Main entry:** Verification email link / resend guidance
- **Summary:** Email Verification confirms ownership after registration or an email change using a one-time token, supports controlled resend, and routes the Buyer to the next intended screen.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `B15-01` | Verification result / `確認結果` | Show success, expired, invalid, or already-used verification state. | メール確認の成功、期限切れ、無効、使用済み状態を表示する。 | Review the result. | For a valid token, mark the email as verified. |
| `B15-02` | Resend / `再送` | Show resend verification email action. | 確認メール再送ボタンを表示する。 | Request resend. | Apply cooldown/rate limit and send a new verification email. |
| `B15-03` | Navigation / `遷移` | Show Login, resume checkout, or My Page guidance as appropriate. | ログイン、チェックアウト再開、またはマイページへの導線を表示する。 | Select a destination. | Open the intended destination or Login. |

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
- **Summary:** Contact collects a Buyer support inquiry with optional reference to the Buyer’s own order, validates ownership, and submits one inquiry without requesting card data.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `B20-01` | Inquiry fields / `問い合わせ内容` | Show name/email defaults, subject, body, and optional owned order reference. | 氏名・メール初期値、件名、本文、任意の自分の注文番号を表示する。 | Enter and submit. | Validate content and order ownership, then create one inquiry. |
| `B20-02` | Header / navigation / `ヘッダー／ナビゲーション` | Show title, Back, search/cart, or bottom navigation as appropriate. | 画面名、戻る、検索・カートまたは下部ナビを画面用途に応じて表示する。 | Tap Back or a navigation item. | Open the prior or selected root screen. Home alone has no Back action. |

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
| `P01-02` | Post-login routing / `ログイン後の遷移` | Show the appropriate next page for the account state. | アカウント状態に応じた次画面を表示する。 | Continue after successful authentication. | Open P03 when email is unverified, P04 while PAY.JP eligibility is incomplete, or P05 when both Visa and Mastercard are passed. |
| `P01-03` | Account actions / `アカウント操作` | Show Producer Registration and Password Reset links. | 生産者登録とパスワード再設定のリンクを表示する。 | Select an action. | Open P02 or P15. |

- **Acceptance:** All `P01-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

### SCR-P-002 — P02 Producer Registration / `生産者登録`

- **Status:** `[CONFIRMED]`
- **Actor:** Producer Applicant
- **Purpose:** Create a Producer account with the minimum internal profile and accept the Producer Terms.
- **Main entry:** Producer Login (P01)
- **Summary:** Producer Registration creates an unverified Producer account using only the minimum marketplace profile, records acceptance of the Producer Terms including the Company's 10% commission, and sends email verification; PAY.JP's full business and bank form is not duplicated here.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `P02-01` | Account / `アカウント` | Email address, password, and password confirmation. | メールアドレス、パスワード、確認用パスワードを入力する。 | Enter account credentials. | Validate required fields, format, password policy, and duplicate email without exposing existing account details. |
| `P02-02` | Minimum profile / `最小プロフィール` | Producer/farm name, representative or contact name, and phone number. | 生産者・農園名、代表者または担当者名、電話番号を入力する。 | Enter the marketplace profile. | Store the internal Producer profile; do not collect PAY.JP business documents or bank details. |
| `P02-03` | Producer Terms / `生産者利用規約` | Show and link the current Producer Terms. The terms include the Company's 10% commission, but the registration checkbox uses a concise terms-consent label rather than displaying the percentage inline. | 現行の生産者利用規約へのリンクと同意欄を表示する。規約には会社手数料10%を含めるが、登録チェックボックスには割合を直接表示せず、簡潔な規約同意文言を使用する。 | Review and accept the terms. | Require consent and record the accepted version and timestamp. |
| `P02-04` | Register / `登録` | Show the Register action and Login link. | 登録ボタンとログインリンクを表示する。 | Submit registration or return to Login. | Create an unverified account, send a one-time verification email, and open P03; retain only safe input when validation fails. |

- **Acceptance:** All `P02-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

### SCR-P-003 — P03 Email Verification / `メール確認`

- **Status:** `[CONFIRMED]`
- **Actor:** Producer Applicant / Producer
- **Purpose:** Confirm ownership of a new or changed Producer login email.
- **Main entry:** Verification link from P02 or P12
- **Summary:** Email Verification handles both initial registration and login-email changes with an expiring one-time token; the old login email remains active until a changed email is verified.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `P03-01` | Verification result / `確認結果` | Show success, expired, used, or invalid-link status. | 成功、期限切れ、使用済み、不正リンクの状態を表示する。 | Open the verification link. | Verify a valid one-time token; after initial verification open P04, and after an email change return to P12. |
| `P03-02` | Resend / `再送` | Show Resend when verification is incomplete. | 確認未完了の場合に再送を表示する。 | Request another email. | Invalidate the previous usable token, apply rate limits, and send a new expiring one-time link. |
| `P03-03` | Email-change protection / `メール変更保護` | Show that the previous login email remains active until confirmation. | 確認完了までは旧ログインメールが有効であることを表示する。 | Verify the new email or cancel the change. | Do not replace the login email until verification succeeds; cancellation keeps the original email. |

- **Acceptance:** All `P03-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

### SCR-P-004 — P04 PAY.JP Application / Screening Status / `PAY.JP申請・審査状況`

- **Status:** `[CONFIRMED]`
- **Actor:** Verified Producer Applicant
- **Purpose:** Complete the PAY.JP hosted application and track card-brand screening before selling.
- **Main entry:** Email Verification (P03) / Producer Login (P01)
- **Summary:** This screen creates the PAY.JP Tenant when required, issues and opens PAY.JP's five-minute one-use Hosted Form URL, and displays authoritative card-brand screening results; a browser return alone never proves approval.
- **Primary Japanese page title:** `販売開始の準備` (Preparation to Start Selling). The logical screen name remains PAY.JP Application / Screening Status.
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
- **Summary:** Producer Product List shows only the signed-in Producer's products and provides filters, create/edit navigation, and immediate publish or unpublish actions after validation.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `P06-01` | Product list / `商品一覧` | Show image, name, category, price range, stock, promotion, and Published/Unpublished state. | 画像、商品名、カテゴリ、価格帯、在庫、プロモーション、公開・非公開状態を表示する。 | Select a product. | Open P07 with the Producer-owned product only. |
| `P06-02` | Filters and empty state / `絞り込み・空状態` | Filter by keyword, category, publication state, and stock state; show a recovery action when empty. | キーワード、カテゴリ、公開状態、在庫状態で絞り込み、0件時は回復導線を表示する。 | Change filters or create a product. | Refresh only the Producer's list or open P07 for creation. |
| `P06-03` | Publication / `公開操作` | Show Publish/Unpublish for valid own products. | 入力済みの自分の商品に公開・非公開を表示する。 | Change publication state. | Validate ownership, eligibility, required product data, price, and stock before updating. |
| `P06-04` | Access control / `アクセス制御` | Show a generic error for another Producer's product identifier or concurrent update. | 他生産者の商品IDまたは競合更新時に一般的なエラーを表示する。 | Reload or return to the list. | Deny cross-Producer access server-side and require re-edit using current values after a conflict. |

- **Acceptance:** All `P06-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

### SCR-P-007 — P07 Product Create / Edit / `商品作成・編集`

- **Status:** `[CONFIRMED]`
- **Actor:** Eligible Producer
- **Purpose:** Create or edit complete product selling information on one screen.
- **Main entry:** Producer Product List (P06)
- **Summary:** Product Create / Edit combines product information, Admin-created category, images, variants, price, stock, Producer-funded promotion, and publication state in one owned-product form while preserving historical OrderItem snapshots.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `P07-01` | Core information / `基本情報` | Product name, description, and one enabled Admin-created category; Producers cannot create categories. | 商品名、説明、有効な管理者作成カテゴリを入力する。生産者はカテゴリを作成できない。 | Enter or edit values. | Validate required and safe text while retaining valid input on error. |
| `P07-02` | Images / `画像` | Multiple product images, display order, and a per-image remove action using an X icon; do not show alternative-text inputs or an overflow menu. | 複数の商品画像と表示順を設定し、各画像には削除用の×アイコンを表示する。代替テキスト入力およびオーバーフローメニューは表示しない。 | Add, reorder, or remove images. | Validate file type, size, and safety; reject only the affected image. |
| `P07-03` | Selling information and product type / `販売情報・商品タイプ` | In the same selling-information card, require the Producer to select `単一商品` or `種類あり`. `単一商品` shows one `通常商品` sellable row. `種類あり` shows a required type name, one option row by default, and `＋ 種類を追加` for additional option rows. Every sellable row owns its authoritative price and stock; the first and only row cannot be deleted, while additional rows have a delete action. | 同じ販売情報カード内で「単一商品」または「種類あり」を選択する。「単一商品」は「通常商品」1行を表示する。「種類あり」は必須の種類名、初期表示の選択肢1行、「＋ 種類を追加」を表示する。各販売行に正式な価格と在庫を設定する。最初の1行だけが残る場合は削除不可とし、追加行には削除操作を表示する。 | Select the product type and enter or edit the applicable sellable rows. | Reject missing or negative price/stock and duplicate option names. Preserve valid input when changing or adding rows. Use the selected sellable row's authoritative price and stock and block publication until errors are corrected. |
| `P07-04` | Sellable-option discount / `販売単位の割引` | Set an optional percentage discount independently on each sellable row. For `単一商品`, the `通常商品` row owns the discount. For `種類あり`, each option may have a different discount. Do not show a target selector or start/end dates. Blank or zero means no discount; the Producer bears the discount. | 各販売行に任意の割引率を個別設定する。「単一商品」では「通常商品」行に設定し、「種類あり」では選択肢ごとに異なる割引率を設定できる。対象選択および開始日・終了日は表示しない。未入力または0は割引なしとし、割引は生産者負担とする。 | Enter, update, or clear each sellable row's discount percentage. | Accept only a valid percentage, calculate from that row's authoritative price, and keep every Producer-funded discount separate from the Company's fixed 10% commission. |
| `P07-05` | Save and publication / `保存・公開` | Show Save Draft, Publish, or Unpublish. | 下書き保存、公開、非公開を表示する。 | Save the complete form. | Verify eligibility and ownership; save current catalog data without altering existing OrderItem snapshots, then return to P06. |
| `P07-06` | Concurrency and ownership / `競合・所有権` | Show a safe error for stale data or another Producer's product ID. | 古いデータまたは他生産者の商品IDに安全なエラーを表示する。 | Reload or cancel. | Deny cross-Producer access and require re-edit from the current version after a conflict. |

- **Acceptance:** All `P07-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

### SCR-P-008 — P08 Producer Order List / `生産者注文一覧`

- **Status:** `[CONFIRMED]`
- **Actor:** Producer
- **Purpose:** Process Producer sub-orders containing only the Producer's products.
- **Main entry:** Producer Dashboard (P05)
- **Summary:** Producer Order List shows only the signed-in Producer's sub-orders, supports operational filtering, and links to manual fulfillment handling without exposing other Producers' order lines.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `P08-01` | Sub-order list / `サブ注文一覧` | Show sub-order reference, order date, own items/quantities, and fulfillment state. | サブ注文番号、注文日、自分の商品・数量、配送対応状態を表示する。 | Select a row. | Open P09 only when the sub-order belongs to the signed-in Producer. |
| `P08-02` | Filters / `絞り込み` | Filter by Received, Processing, Shipped, Cancelled, and Refunded system states. | 受付、対応中、発送済み、キャンセル、返金済みで絞り込む。 | Select filters. | Refresh the Producer's own sub-orders; show a clear empty state when none match. |
| `P08-03` | Status ownership / `状態の管理主体` | Show Producer-editable and system-controlled states distinctly. | 生産者更新状態とシステム管理状態を区別して表示する。 | Review the state. | Allow manual progression only through Received → Processing → Shipped; cancellation/refund remains system-controlled. |
| `P08-04` | Access control / `アクセス制御` | Show a generic error for another Producer's sub-order ID. | 他生産者のサブ注文IDには一般的なエラーを表示する。 | Return to the list. | Deny cross-Producer access server-side. |

- **Acceptance:** All `P08-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

### SCR-P-009 — P09 Producer Order Detail / `生産者注文詳細`

- **Status:** `[CONFIRMED]`
- **Actor:** Producer
- **Purpose:** Review own order lines and minimum delivery information, then manually update fulfillment.
- **Main entry:** Producer Order List (P08)
- **Summary:** Producer Order Detail displays only the Producer's purchase-time item snapshots and the recipient information required for delivery; the Producer is responsible for shipment and manually advances the allowed order state.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `P09-01` | Owned order lines / `自分の注文明細` | Show purchase-time product, variant, quantity, unit price, and promotion for the Producer's lines only. | 自分の明細だけについて購入時の商品、バリエーション、数量、単価、プロモーションを表示する。 | Review the items. | Use immutable OrderItem snapshots and hide other Producers' items and internal payment/commission data. |
| `P09-02` | Delivery information / `配送情報` | Show only recipient name, delivery address, and phone required for fulfillment. | 配送に必要な受取人名、配送先住所、電話番号だけを表示する。 | Use the information for delivery. | Prohibit unrelated use, bulk export, or display beyond the owning Producer. |
| `P09-03` | Manual fulfillment status / `手動配送状態` | Show Received, Processing, and Shipped with the current state. | 現在状態と受付、対応中、発送済みを表示する。 | Select the next allowed state and confirm. | Permit only sequential manual updates; reflect the result to the Buyer order view and audit the change. |
| `P09-04` | Blocked transition / `更新不可` | Show a safe message when cancellation/refund is active, the transition is invalid, or ownership fails. | キャンセル・返金処理中、無効遷移、所有権不一致時に安全な案内を表示する。 | Return or reload. | Do not update the state and deny cross-Producer access server-side. |

- **Acceptance:** All `P09-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

### SCR-P-010 — P10 Payout / `振込`

- **Status:** `[CONFIRMED]`
- **Actor:** Producer
- **Purpose:** Review own sales, settlement calculation, payout history, and documents on one read-only page.
- **Main entry:** Producer Dashboard (P05)
- **Summary:** Payout combines period sales, detailed calculation, payout history, status, and statement/invoice downloads in one read-only page using PAY.JP Term/Statement/Balance data reconciled with internal order and refund records.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `P10-01` | Period summary / `期間サマリー` | Show gross sales, Producer-funded promotion impact, refunds/cancellations, Company's 10% commission, PAY.JP fees, other adjustments, and net payout. | 総売上、生産者負担プロモーション、返金・取消、会社手数料10％、PAY.JP手数料、その他調整、振込額を表示する。 | Select a period. | Recalculate the read-only view from the Producer's reconciled data and clearly distinguish provisional from finalized amounts. |
| `P10-02` | Payout history / `振込履歴` | Show period, net amount, due/paid date, status, carry-forward amount, and payout reference. | 期間、振込額、予定・実行日、状態、繰越額、振込参照番号を表示する。 | Select a history row. | Expand the detailed breakdown on the same page; do not open a separate settlement screen. |
| `P10-03` | Detailed breakdown / `詳細内訳` | Show the components and related own orders/refunds supporting the selected payout. | 選択した振込を構成する項目と関連する自分の注文・返金を表示する。 | Review details. | Trace values to the Producer's records without providing any edit action. |
| `P10-04` | Statements and invoices / `明細・適格請求書` | Show available PAY.JP statement, balance, and qualified-invoice documents. | 利用可能なPAY.JP Statement、Balance、適格請求書を表示する。 | Request a download. | Generate a temporary authorized PAY.JP download URL on demand; do not persist it as a permanent public link. |
| `P10-05` | Payout state and bank / `振込状態・口座` | Show payout state and masked destination account with a link to P13. | 振込状態、マスク済み振込先口座、P13へのリンクを表示する。 | Review or open bank information. | Reflect PAY.JP minimum/carry-forward and failure/hold information while keeping the financial calculation read-only. |
| `P10-06` | Ownership and integrity / `所有権・整合性` | Show a generic error for another Producer's payout identifier or unavailable authoritative data. | 他生産者の振込IDまたは正式データ取得不可時に一般的なエラーを表示する。 | Return or retry later. | Deny cross-Producer access and never allow editing commission, refunds, adjustments, net payout, or payout state. |

- **Acceptance:** All `P10-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

### SCR-P-011 — P11 Producer Account Settings / `生産者アカウント設定`

- **Status:** `[CONFIRMED]`
- **Actor:** Producer
- **Purpose:** Provide one hub for Producer account-related settings and logout.
- **Main entry:** Header account dropdown after selling eligibility
- **Summary:** Producer Account Settings is the account hub linking to profile/contact information, payout bank information, password change, and logout; logout is a confirmation action rather than a separate screen.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `P11-01` | Account menu / `アカウントメニュー` | Show Producer Account Information, Payout Bank Account Information, and Password Change. | 生産者アカウント情報、振込口座情報、パスワード変更を表示する。 | Select a menu item. | Open P12, P13, or P14. |
| `P11-02` | Account summary / `アカウント概要` | Show minimal Producer identity and masked login email. | 生産者の最小限の本人情報とマスク済みログインメールを表示する。 | Review the summary. | Display only the signed-in Producer's information. |
| `P11-03` | Logout confirmation / `ログアウト確認` | Show a confirmation dialog when Logout is selected. | ログアウト選択時に確認ダイアログを表示する。 | Confirm or cancel. | Confirm ends the Producer session and opens P01; cancel stays on P11. No Logout detail sheet is created. |

- **Acceptance:** All `P11-*` rows are enforced together, navigation remains within the final 47-screen inventory, and access is scoped to the authenticated actor.

### SCR-P-012 — P12 Producer Account Information / `生産者アカウント情報`

- **Status:** `[CONFIRMED]`
- **Actor:** Producer
- **Purpose:** Review and update the Producer's internal profile, contact information, and login email on one screen.
- **Main entry:** Header Profile action from P04 before selling eligibility / Producer Account Settings (P11) after eligibility
- **Summary:** Producer Account Information displays and edits the Producer/farm name, representative/contact information, phone, and login email on one screen; changing the login email reuses P03 verification while the old email remains active. Before eligibility, the header Profile action opens P12 directly. After eligibility, the header account dropdown opens P11 and P12 is reached from that account hub.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `P12-01` | Profile and contact / `プロフィール・連絡先` | Producer/farm name, representative/contact name, and phone number. | 生産者・農園名、代表者・担当者名、電話番号を表示する。 | Review or edit permitted fields. | Validate required formats and save only to the signed-in Producer's internal profile. |
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

### SCR-A-003 — A03 Buyer / Inquiry Management / `購入者・問い合わせ管理`

- **Status:** `[CONFIRMED]`
- **Actor:** Admin
- **Purpose:** Handle Buyer account support and inquiries using only the minimum required data.
- **Main entry:** Admin Dashboard (A02) / Admin menu
- **Summary:** Buyer / Inquiry Management combines Buyer lookup and inquiry handling, exposing only the minimum account, contact, and owned-order information needed for support while keeping card data, Producer finance, and unrelated Buyer data hidden.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `A03-01` | Buyer search / `購入者検索` | Search by safe identifiers and show masked name, email, account state, and inquiry count. | 安全な識別情報で検索し、氏名・メールのマスク表示、アカウント状態、問い合わせ件数を表示する。 | Search and select a Buyer. | Return only the selected Buyer's minimum support data. |
| `A03-02` | Inquiry list and detail / `問い合わせ一覧・詳細` | Show subject, submitted date, status, message, and safe contact information. | 件名、受付日、状態、本文、安全な連絡先を表示する。 | Open an inquiry and enter a response or internal result. | Save the response/status without exposing unrelated account or payment data. |
| `A03-03` | Buyer support detail / `購入者支援詳細` | Show account state and links to the Buyer's own orders needed for support. | アカウント状態と、支援に必要な本人注文へのリンクを表示する。 | Review the account or select an order. | Open A08 for the selected owned order; do not display Company commission or Producer payout data. |
| `A03-04` | Privacy and errors / `プライバシー・エラー` | Show a generic denial when data or an action is outside the permitted support scope. | 許可範囲外の情報・操作には一般的な拒否を表示する。 | Return to the list or narrow the request. | Enforce authorization server-side and record only a safe operation event for A11. |

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
- **Summary:** Category Management keeps one Admin-controlled category master for Buyer browsing and Producer product selection, combining list, create, edit, display order, and enable/disable operations without allowing Producers to create private categories.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `A05-01` | Category list / `カテゴリ一覧` | Show category name, enabled/disabled state, display order, product count, and updated time. | カテゴリ名、有効・無効、表示順、商品件数、更新日時を表示する。 | Search, reorder, or select a category. | Use the same enabled category master in Buyer and Producer screens. |
| `A05-02` | Create or edit / `作成・編集` | Show category name and display order on the same screen. | 同一画面にカテゴリ名と表示順を表示する。 | Enter values and save. | Validate required and duplicate names before saving. |
| `A05-03` | Enable or disable / `有効・無効` | Show the current state and affected product count. | 現在状態と影響する商品件数を表示する。 | Confirm enable or disable. | Prevent unsafe deletion; disabling stops new selection while preserving existing product history. |
| `A05-04` | Operation result / `処理結果` | Show success, validation, concurrent-change, or in-use guidance. | 成功、入力、競合更新、利用中の案内を表示する。 | Correct or reload when needed. | Apply the latest safe state and write the category change as a safe A11 event. |

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
- **Summary:** Product Management combines the cross-Producer product list, product detail, variants, stock, publication state, promotion review, and moderation. Valid Producer products publish without routine Admin approval; Admin acts only when a listing or promotion requires correction.

| Element ID | Element (EN / JP) | Display or input | Japanese display/input | User action | Processing / destination |
|---|---|---|---|---|---|
| `A07-01` | Product list and filters / `商品一覧・絞り込み` | Show Producer, category, product, price range, stock, publication, promotion, and problem state. | 生産者、カテゴリ、商品、価格帯、在庫、公開、プロモーション、問題状態を表示する。 | Search, filter, and select a product. | Open the selected product detail on this screen. |
| `A07-02` | Product detail / `商品詳細` | Show description, images, category, variants, authoritative price/stock, publication history, and Producer. | 説明、画像、カテゴリ、バリエーション、正式な価格・在庫、公開履歴、生産者を表示する。 | Review the selected product. | Read current product data while preserving purchase-time OrderItem snapshots. |
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
| `A11-01` | Event list and filters / `イベント一覧・絞り込み` | Show time, severity, event type, safe actor/target, result, source IP when permitted, and reference. | 日時、重要度、イベント種別、安全な実行者・対象、結果、許可時の送信元IP、参照番号を表示する。 | Filter by period, severity, type, result, or safe identifier. | Return only authorized, masked log information. |
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
- **INT-005 `[CONFIRMED]` — Payment creation:** Revalidate price and stock server-side, group cart lines by Producer, and create a uniquely referenced payment for each Tenant.
- **INT-006 `[CONFIRMED]` — 3D Secure:** Follow the PAY.JP-directed issuer-authentication flow and resume authoritative verification after browser return.
- **INT-007 `[CONFIRMED]` — Payment/refund idempotency:** Repeated, delayed, or out-of-order payment/refund events must not create duplicate charges, paid orders, refunds, commission effects, or payout effects.
- **INT-008 `[CONFIRMED]` — Direct payout:** Read PAY.JP payout, Term, Statement, Balance, fee, adjustment, and document information as required by P10, A09, and A10; Producers and Admin cannot edit provider-confirmed payout values.
- **INT-009 `[CONFIRMED]` — Bank update:** Send protected bank changes through the PAY.JP Tenant API after re-authentication; do not persist or log raw bank values locally.
- **INT-010 `[CONFIRMED]` — Email:** Registration verification, email-change verification, and password reset use rate-limited, expiring, one-use tokens and neutral request responses where account enumeration is possible.

| API ID | Logical interface group | Key controls |
|---|---|---|
| `API-001` | Authentication and email verification | Role separation, neutral recovery, one-use tokens, session protection |
| `API-002` | Public catalog, category, search, and product detail | Published products only; safe public Producer subset |
| `API-003` | Cart and checkout | Server-authoritative price, stock, promotion, address, and totals |
| `API-004` | Buyer order, payment, cancellation, and refund | Buyer ownership; authoritative PAY.JP status; exactly-once effects |
| `API-005` | Producer onboarding and screening | Producer ownership; Tenant auto-creation; Visa/Mastercard eligibility gate |
| `API-006` | Producer catalog and fulfillment | Own data only; immutable purchase snapshots |
| `API-007` | Producer payout and bank information | Read-only finance; masked bank data; recent re-authentication for update |
| `API-008` | Admin operations | Single Admin role; monitored screening; reasoned sensitive operations |
| `API-009` | Monitoring and audit events | Read-only safe logs without secrets or raw sensitive values |

## 13. Logical Data Requirements

- **DATA-001 `[CONFIRMED]` — Buyer:** Identity, verified/pending email, default address, session state.
- **DATA-002 `[CONFIRMED]` — Producer and PAY.JP Tenant:** Internal Producer profile, Tenant reference, operational state, selling eligibility.
- **DATA-003 `[CONFIRMED]` — Screening result:** Per-card-brand status and available authoritative timestamps.
- **DATA-004 `[CONFIRMED]` — Category, banner, product, sellable option, stock, promotion:** Every product has at least one sellable option. Price, stock, and Producer-funded discount are authoritative per option; publication and promotion remain distinct from the Company's fixed 10% commission.
- **DATA-005 `[CONFIRMED]` — Cart:** Buyer-scoped lines with server-repriced authoritative values.
- **DATA-006 `[CONFIRMED]` — Buyer order and Producer sub-order:** One Buyer order with Producer-grouped fulfillment and immutable purchase snapshots.
- **DATA-007 `[CONFIRMED]` — Payment attempt:** Tenant, unique reference, 3D Secure and authoritative state; no raw card data.
- **DATA-008 `[CONFIRMED]` — Refund/chargeback:** Original payment, amount, state, reason, idempotency reference, commission/payout effect.
- **DATA-009 `[CONFIRMED]` — Payout and statement:** Period components, expected/provider amount, carry-forward, status, dates, references.
- **DATA-010 `[CONFIRMED]` — Producer bank destination:** Provider-managed values; masked ordinary display; raw values excluded from logs and audit.
- **DATA-011 `[CONFIRMED]` — Inquiry:** Buyer-safe contact content, related order reference, status.
- **DATA-012 `[CONFIRMED]` — Audit/security event:** Actor, time, target, safe previous/new state, result, reason, safe network metadata.

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

## 15. Non-Functional Requirements

- **NFR-001 `[CONFIRMED]`** Customer-facing UI is Japanese, currency is JPY, and business dates/times use Asia/Tokyo.
- **NFR-002 `[CONFIRMED]`** Buyer screens are mobile-first; Producer and Admin screens support desktop operation.
- **NFR-003 `[CONFIRMED]`** Loading, empty, validation, failure, retry, and unavailable states must be safe and actionable.
- **NFR-004 `[CONFIRMED]`** Status must be communicated with text and not by color alone.
- **NFR-005 `[CONFIRMED]`** Financial and screening displays clearly distinguish provisional, pending, finalized, failed, held, and unavailable information where applicable.
- **NFR-006 `[CONFIRMED]`** Repeated user actions and provider events must not duplicate accounts, orders, charges, refunds, inquiries, financial effects, or sensitive operations.

## 16. State Models

- **Producer onboarding:** `Unverified → Email Verified → Application Required → In Review → Eligible to Sell`; an authoritative status requiring attention remains non-eligible until Visa and Mastercard are passed.
- **Card brand screening:** `in_review → passed | declined`; status is stored per brand.
- **Payment:** `Verifying → Completed | Failed | Cancelled | Pending`; partial success never makes the overall Buyer order Paid and enters safe recovery/refund handling.
- **Producer fulfillment:** `Received → Processing → Shipped`; cancellation/refund states are system-controlled.
- **Product:** Producer-controlled draft/publication state after selling eligibility; Admin moderation may unpublish or request correction with a reason.
- **Payout:** `Scheduled | Paid | Failed | Held | Carried Forward`; provider-confirmed history is read-only.

## 17. Open Decision Registry

The approved workbook contains no cells marked TBD, requiring review, or pending decision. New implementation discoveries must be recorded here before an agent assumes a business, legal, payment, payout, privacy, or operational rule not present in this baseline.

## 18. Acceptance Tests

### 18.1 Buyer screens

- **AT-B-001:** Verify `B01` satisfies every `B01-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.
- **AT-B-002:** Verify `B02` satisfies every `B02-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.
- **AT-B-003:** Verify `B03` satisfies every `B03-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.
- **AT-B-004:** Verify `B04` satisfies every `B04-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.
- **AT-B-005:** Verify `B05` satisfies every `B05-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.
- **AT-B-006:** Verify `B06` satisfies every `B06-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.
- **AT-B-007:** Verify `B07` satisfies every `B07-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.
- **AT-B-008:** Verify `B08` satisfies every `B08-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.
- **AT-B-009:** Verify `B09` satisfies every `B09-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.
- **AT-B-010:** Verify `B10` satisfies every `B10-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.
- **AT-B-011:** Verify `B11` satisfies every `B11-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.
- **AT-B-012:** Verify `B12` satisfies every `B12-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.
- **AT-B-013:** Verify `B13` satisfies every `B13-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.
- **AT-B-014:** Verify `B14` satisfies every `B14-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.
- **AT-B-015:** Verify `B15` satisfies every `B15-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.
- **AT-B-016:** Verify `B16` satisfies every `B16-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.
- **AT-B-017:** Verify `B17` satisfies every `B17-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.
- **AT-B-018:** Verify `B18` satisfies every `B18-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.
- **AT-B-019:** Verify `B19` satisfies every `B19-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.
- **AT-B-020:** Verify `B20` satisfies every `B20-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.
- **AT-B-021:** Verify `B21` satisfies every `B21-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.

### 18.2 Producer screens

- **AT-P-001:** Verify `P01` satisfies every `P01-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.
- **AT-P-002:** Verify `P02` satisfies every `P02-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.
- **AT-P-003:** Verify `P03` satisfies every `P03-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.
- **AT-P-004:** Verify `P04` satisfies every `P04-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.
- **AT-P-005:** Verify `P05` satisfies every `P05-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.
- **AT-P-006:** Verify `P06` satisfies every `P06-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.
- **AT-P-007:** Verify `P07` satisfies every `P07-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.
- **AT-P-008:** Verify `P08` satisfies every `P08-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.
- **AT-P-009:** Verify `P09` satisfies every `P09-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.
- **AT-P-010:** Verify `P10` satisfies every `P10-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.
- **AT-P-011:** Verify `P11` satisfies every `P11-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.
- **AT-P-012:** Verify `P12` satisfies every `P12-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.
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
- **AT-A-011:** Verify `A11` satisfies every `A11-*` element row, its stated entry and navigation, the authenticated actor scope, and its safe error/retry behavior.

### 18.4 Cross-domain critical tests

- **AT-X-001:** A mixed-Producer cart creates uniquely referenced payments per Tenant while the Buyer sees one order.
- **AT-X-002:** A browser return without authoritative success cannot produce B10 or a Paid order.
- **AT-X-003:** Repeated, delayed, mismatched, forged, or out-of-order provider events produce no duplicate financial effect.
- **AT-X-004:** Cancellation within 30 minutes refunds every underlying Producer payment exactly once; after the deadline B12 hides cancellation and routes to B20.
- **AT-X-005:** P04 cannot unlock selling until both Visa and Mastercard are `passed`; Admin cannot override the result.
- **AT-X-006:** Before P04 eligibility, no sidebar is rendered. The full-width authenticated header account dropdown exposes only the Producer's own Profile and Logout; Product, Order, Sales/Payout, bank, password, and full Account Settings destinations remain inaccessible by direct URL or identifier manipulation. When Visa and Mastercard both become `passed`, the current authenticated session redirects immediately to P05; every later login also opens P05 with the Producer operational sidebar limited to Dashboard, Product Management, Order Management, and Sales/Payout. The operational sidebar does not repeat a `Logged in` label, account name, or farm name in its footer; identity and account actions remain in the header account dropdown. After eligibility, the header account dropdown exposes Account Settings and Logout, and Account Settings opens P11. No standalone approval-complete P04 state is rendered.
- **AT-X-007:** Producer A cannot access Producer B's product, order, payout, Tenant, or bank identifiers.
- **AT-X-008:** Bank update requires recent re-authentication, leaves the previous account unchanged on failure, and logs no raw values.
- **AT-X-009:** Company commission, Producer-funded promotion, PAY.JP fees, refunds, adjustments, and net payout reconcile as distinct components.
- **AT-X-010:** A11 contains safe events for suspicious access and sensitive operations but no password, card, secret, raw bank, or unnecessary personal values.

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
| `FR-B-010` | `SCR-B-010` / `B10` | `B10-01`, `B10-02` | `AT-B-010` |
| `FR-B-011` | `SCR-B-011` / `B11` | `B11-01`, `B11-02` | `AT-B-011` |
| `FR-B-012` | `SCR-B-012` / `B12` | `B12-01`, `B12-02`, `B12-03`, `B12-04`, `B12-05`, `B12-06` | `AT-B-012` |
| `FR-B-013` | `SCR-B-013` / `B13` | `B13-01`, `B13-02` | `AT-B-013` |
| `FR-B-014` | `SCR-B-014` / `B14` | `B14-01`, `B14-02`, `B14-03` | `AT-B-014` |
| `FR-B-015` | `SCR-B-015` / `B15` | `B15-01`, `B15-02`, `B15-03` | `AT-B-015` |
| `FR-B-016` | `SCR-B-016` / `B16` | `B16-01`, `B16-02` | `AT-B-016` |
| `FR-B-017` | `SCR-B-017` / `B17` | `B17-01`, `B17-02`, `B17-03`, `B17-04` | `AT-B-017` |
| `FR-B-018` | `SCR-B-018` / `B18` | `B18-01`, `B18-02` | `AT-B-018` |
| `FR-B-019` | `SCR-B-019` / `B19` | `B19-01`, `B19-02` | `AT-B-019` |
| `FR-B-020` | `SCR-B-020` / `B20` | `B20-01`, `B20-02` | `AT-B-020` |
| `FR-B-021` | `SCR-B-021` / `B21` | `B21-01` | `AT-B-021` |
| `FR-P-001` | `SCR-P-001` / `P01` | `P01-01`, `P01-02`, `P01-03` | `AT-P-001` |
| `FR-P-002` | `SCR-P-002` / `P02` | `P02-01`, `P02-02`, `P02-03`, `P02-04` | `AT-P-002` |
| `FR-P-003` | `SCR-P-003` / `P03` | `P03-01`, `P03-02`, `P03-03` | `AT-P-003` |
| `FR-P-004` | `SCR-P-004` / `P04` | `P04-01`, `P04-02`, `P04-03`, `P04-04`, `P04-05`, `P04-06` | `AT-P-004` |
| `FR-P-005` | `SCR-P-005` / `P05` | `P05-01`, `P05-02`, `P05-03` | `AT-P-005` |
| `FR-P-006` | `SCR-P-006` / `P06` | `P06-01`, `P06-02`, `P06-03`, `P06-04` | `AT-P-006` |
| `FR-P-007` | `SCR-P-007` / `P07` | `P07-01`, `P07-02`, `P07-03`, `P07-04`, `P07-05`, `P07-06` | `AT-P-007` |
| `FR-P-008` | `SCR-P-008` / `P08` | `P08-01`, `P08-02`, `P08-03`, `P08-04` | `AT-P-008` |
| `FR-P-009` | `SCR-P-009` / `P09` | `P09-01`, `P09-02`, `P09-03`, `P09-04` | `AT-P-009` |
| `FR-P-010` | `SCR-P-010` / `P10` | `P10-01`, `P10-02`, `P10-03`, `P10-04`, `P10-05`, `P10-06` | `AT-P-010` |
| `FR-P-011` | `SCR-P-011` / `P11` | `P11-01`, `P11-02`, `P11-03` | `AT-P-011` |
| `FR-P-012` | `SCR-P-012` / `P12` | `P12-01`, `P12-02`, `P12-03`, `P12-04` | `AT-P-012` |
| `FR-P-013` | `SCR-P-013` / `P13` | `P13-01`, `P13-02`, `P13-03`, `P13-04`, `P13-05` | `AT-P-013` |
| `FR-P-014` | `SCR-P-014` / `P14` | `P14-01`, `P14-02` | `AT-P-014` |
| `FR-P-015` | `SCR-P-015` / `P15` | `P15-01`, `P15-02`, `P15-03` | `AT-P-015` |
| `FR-A-001` | `SCR-A-001` / `A01` | `A01-01`, `A01-02`, `A01-03` | `AT-A-001` |
| `FR-A-002` | `SCR-A-002` / `A02` | `A02-01`, `A02-02`, `A02-03`, `A02-04`, `A02-05` | `AT-A-002` |
| `FR-A-003` | `SCR-A-003` / `A03` | `A03-01`, `A03-02`, `A03-03`, `A03-04` | `AT-A-003` |
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
- **OUT-003 `[OUT-OF-SCOPE]`** Separate Producer screens for initial bank registration, standalone terms, application submitted, images/variants, payout history/detail, bank update, or Logout.
- **OUT-004 `[OUT-OF-SCOPE]`** Admin Password Reset, Admin Account Settings, standalone Audit History, manual Tenant creation, screening override, or payout execution/editing.
- **OUT-005 `[OUT-OF-SCOPE]`** Any payment method or provider capability not stated in the approved workbook.
