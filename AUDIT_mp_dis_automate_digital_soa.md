# Deep Audit Report: `/mp_dis/automate/digital/soa?date=02/06/2026`

**Audited By:** Claude Code
**Date:** 2026-03-17
**Endpoint:** `GET /mp_dis/automate/digital/soa?date=02/06/2026`
**Branch:** `claude/audit-mp-dis-endpoint-ac7OB`

---

## 1. Route Resolution

**File:** `application/config/routes.php:60`

```php
$route['mp_dis/(.+)'] = '$1';
```

The prefix `mp_dis/` is a transparent pass-through. The actual resolved path is:

```
automate/digital/soa?date=02/06/2026
```

Which maps (via HMVC module routing) to:

- **Module:** `automate`
- **Controller:** `Digital` (`application/modules/automate/controllers/Digital.php`)
- **Method:** `soa()`

---

## 2. Entry Point – `Digital::soa()`

**File:** `Digital.php:33–35`

```php
public function soa(){
    $this->_interface_soa(false);
}
```

Calls `_interface_soa(false)` — the `false` flag means the output is **streamed as a browser download** (not saved server-side).

---

## 3. Authentication & Authorization

### Finding: **NO authentication guard on this endpoint**

The `Digital` controller's constructor (`Digital.php:12–17`) only:
- Sets `$this->DATE_NOW`
- Loads `Corepass_model`
- Loads `download_file` library

There is **no call** to `$this->auth->check_session()` or any session check anywhere in `Digital.php`. The autoloaded `Auth` library (`application/config/autoload.php:61`) is available globally but is **never invoked** in this controller.

Comparing to other controllers (e.g., `Admin.php`, `Filter.php`) which call `$this->auth->check_session()` explicitly, this controller has **no equivalent protection**.

**Risk: CRITICAL — The endpoint is publicly accessible with no login required.**

---

## 4. Request Handling – `_interface_soa()`

**File:** `Digital.php:190–220`

```php
private function _interface_soa($serverDl = true){
    $date = new DateTime();
    $previousDate = $date->modify("-1 days")->format('m/d/yy');
        if(isset($_GET['date'])) $previousDate = $_GET['date'];
    $where = " AND TO_CHAR(cs.START_DATE, 'mm/dd/yyyy') ='".$previousDate."'";
        if(isset($_GET['month'])){
            $previousDate = $_GET['month'];
            $where = " AND TO_CHAR(cs.START_DATE, 'mm/yyyy') ='".$previousDate."'";
        }
    $result = $this->Corepass_model->getDigitalSOAOrder($where);
    ...
}
```

### Parameter: `?date=02/06/2026`

- The raw value of `$_GET['date']` is taken **without any sanitization, validation, or escaping**.
- It is directly interpolated into the SQL `$where` string.
- This `$where` string is passed verbatim into `getDigitalSOAOrder($where)`.

**Risk: CRITICAL — SQL Injection via the `date` parameter.**

---

## 5. Database Query – `Corepass_model::getDigitalSOAOrder()`

**File:** `application/models/Corepass_model.php:1177–1313`

```php
public function getDigitalSOAOrder($where = ''){
    $result = $this->oracle_db->query("SELECT ...
        FROM ...
        WHERE ...
        ".$where."        // <-- raw user input injected here
    )
    ...
}
```

The `$where` clause is built by direct string concatenation of unvalidated user input into an Oracle SQL query.

**Target database:** Oracle (confirmed by `TO_CHAR`, `oracle_db` connection, Oracle-specific table names like `os_currentstep`, `e_wf_process_state`).

### SQL Injection Proof-of-Concept

A request such as:
```
GET /mp_dis/automate/digital/soa?date=02/06/2026' OR '1'='1
```
would produce:
```sql
AND TO_CHAR(cs.START_DATE, 'mm/dd/yyyy') ='02/06/2026' OR '1'='1'
```
This bypasses the date filter and returns **all records** from the database.

More destructive payloads (stacked queries, UNION-based extraction, etc.) are possible depending on Oracle DB permissions.

**Risk: CRITICAL — Unauthenticated Oracle SQL Injection.**

---

## 6. Secondary Query – `getDigitalSOAOrderBillable()`

**File:** `Corepass_model.php:1321–1407`
**Called from:** `Digital.php:241`

```php
$whereD = 'AND ECD.N_ACCOUNTINGDOCUMENT = '.$temp_row->SOA_NUMBER
        .' AND TCO.N_CREDITORDER = '.$temp_row->ORDER_ID
        .' AND EA.SERVICE_ID = '.$temp_row->SERVICE_ID;
$resultDetail = $this->Corepass_model->getDigitalSOAOrderBillable($whereD);
```

While `SOA_NUMBER`, `ORDER_ID`, and `SERVICE_ID` come from the database result (not directly from user input), if the primary SQL injection is exploited to return attacker-controlled data, these fields could themselves contain injected SQL.

**Risk: HIGH — Second-order SQL Injection potential.**

---

## 7. Data Exposure

If successfully queried (authenticated or via SQL injection), this endpoint returns a CSV file containing:

| Field | Description |
|---|---|
| `TIN` | Tax Identification Number (fiscal identifier) |
| `LegalName` | Company legal name |
| `SOA_NUMBER` | Statement of Account number |
| `ORDER_ID` | Credit order ID |
| `ORDER_DATE` | Order creation date |
| `DELIVERED_DATE` | Credit delivery date |
| `AMOUNT` | Gross billable amount (with VAT) |
| `DISCOUNT` | Total discount |
| `TOTAL_AMOUNT` | Net billable amount |
| `CUSTOMER_TYPE` | Client classification |
| `SERVICE_ID` | Service product type |
| `ACCOUNT_MANAGER` | Account manager's full name |
| `PO` | Purchase order reference |
| `CP_ID` | Company ID (prefixed with 'Z') |
| `DUE_DATE` | Payment due date |
| Detail rows: billable items, VAT amounts, credit values | |

**Risk: HIGH — Sensitive financial and PII data exposed.**

---

## 8. `?month=` Parameter – Same Vulnerability

**File:** `Digital.php:198–201`

```php
if(isset($_GET['month'])){
    $previousDate = $_GET['month'];
    $where = " AND TO_CHAR(cs.START_DATE, 'mm/yyyy') ='".$previousDate."'";
}
```

The `month` parameter has the exact same SQL injection vulnerability as `date`. No sanitization.

**Risk: CRITICAL — Same as `date` parameter.**

---

## 9. Output – File Download

**File:** `application/libraries/Download_file.php:594–663`

When `$serverDl = false` (triggered by the `soa()` public method):
- Calls `_callDownload()` which streams a CSV file directly to the browser.
- Filename format: `DO_MMDDYYYY_001.csv` (e.g., `DO_03172026_001.csv`).
- No Content-Security-Policy or other download headers inspected.

The `navLocation` is hardcoded to `"C:/xampp/htdocs/nav_interface/"` — indicating a Windows dev path is in the production library (dead code, but suggests environment inconsistency).

---

## 10. Business Logic Issues

### 10a. Discount Calculation Double-Deduction (`_detail_result`)

**File:** `Digital.php:267`

```php
$X_NET_BILLABLE -= ($temp_row->BILLABLE_AMOUNT + $temp_row->BILLABLE_AMOUNT);
// deducts TWICE the rebate amount
```

For category IDs 600, 356, 355 (rebate/discount billables), the net billable amount is reduced by **2x the rebate amount** instead of 1x. This may be intentional (to account for tax) but is not documented and diverges from the `_remittance_detail_result` logic which deducts only once.

**Risk: MEDIUM — Potential financial calculation error.**

### 10b. `$return_detail` Null Safety Missing

**File:** `Digital.php:243`

```php
$newRow->nav_detail = $return_detail['nav_detail'];
$newRow->DISCOUNT = $return_detail['TOTAL_DISCOUNT'];
```

In `_interface_soa_result()`, if `$return_detail` is empty (returns `[]`), accessing `$return_detail['nav_detail']` will cause a PHP error (undefined index). Compare with the `_interface_si_result()` method (line 98) which properly checks `if($return_detail)` first.

**Risk: LOW — Unhandled PHP error when no billable items exist for an SOA order.**

### 10c. `SI_NUMBER` Field Missing from `soa()` Output

The `_interface_si_result()` includes `SI_NUMBER` in detail rows, but `_interface_soa_result()` does not set `$newRow->SI_NUMBER` even though the data is available from the query. This leads to an undefined property when `_cic_soa_remittance()` is used for SOA vs SI output.

---

## 11. Additional Code Quality Issues

| Issue | Location | Severity |
|---|---|---|
| Commented-out debug code left in production (`log_message`, `print_r`, `die()`) | `Digital.php:83–96, 205–217` | LOW |
| `$export` parameter declared but never used in `_interface_soa_result()` | `Digital.php:221` | LOW |
| Windows dev path hardcoded in `Download_file.php` | `Download_file.php:10` | LOW |
| Stale copy `Digital - Copy.php` exists in production controllers directory | `automate/controllers/` | LOW |
| `cptestquery()` method name suggests test/debug code still active | `Corepass_model.php:101` | LOW |

---

## 12. Summary of Findings

| # | Severity | Finding |
|---|---|---|
| 1 | **CRITICAL** | No authentication — endpoint is publicly accessible |
| 2 | **CRITICAL** | SQL Injection via unvalidated `?date=` parameter |
| 3 | **CRITICAL** | SQL Injection via unvalidated `?month=` parameter |
| 4 | **HIGH** | Second-order SQL Injection potential via DB-sourced WHERE clause |
| 5 | **HIGH** | Sensitive financial/PII data (TIN, legal names, amounts, PO numbers) exposed |
| 6 | **MEDIUM** | Incorrect discount double-deduction in `_detail_result()` (categories 600/356/355) |
| 7 | **LOW** | Null-safety missing on `$return_detail` in `_interface_soa_result()` |
| 8 | **LOW** | Dead/debug code in production (`Digital - Copy.php`, `cptestquery`, commented logs) |

---

## 13. Recommendations

1. **Add session authentication guard** to `Digital::__construct()`:
   ```php
   if(!$this->auth->check_session()) redirect('login');
   ```

2. **Sanitize and validate all query parameters** before use in SQL. Use parameterized queries or at minimum enforce strict date format validation (regex `^[0-9]{2}/[0-9]{2}/[0-9]{4}$`) and escape the value.

3. **Use prepared statements** (bind parameters) for Oracle queries via CodeIgniter's query binding:
   ```php
   $this->oracle_db->query("... WHERE TO_CHAR(cs.START_DATE, 'mm/dd/yyyy') = ?", [$date]);
   ```

4. **Add null check** on `$return_detail` in `_interface_soa_result()` consistent with `_interface_si_result()`.

5. **Remove dead files**: `Digital - Copy.php`, commented-out debug blocks, `cptestquery` method.

6. **Fix discount deduction logic** in `_detail_result()` line 267 — clarify if 2x deduction is intentional and document it.

---

*End of Audit Report*
