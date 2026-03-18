# Oracle / OCI8 Tight Coupling Audit

**Project:** DIS — Digital Integrated System
**Framework:** CodeIgniter 3.x (PHP)
**Audit Date:** 2026-03-18
**Branch:** `claude/audit-oracle-coupling-PVP3f`

---

## Executive Summary

The DIS application maintains a **read-only integration with an external Oracle database** (Corepass) in parallel with its primary MySQL (`dis_db`) database. All Oracle access is concentrated in a single model (`Corepass_model.php`), which contains 28 data-access methods and 2,128 lines of raw Oracle SQL.

| Layer | Files Affected | Severity |
|-------|---------------|----------|
| Configuration | 1 | Low — env-var driven, easily swappable |
| Framework drivers (CI built-in) | 6 | N/A — not application code |
| Application model | 1 primary + 6 backup copies | **High** |
| Controllers (indirect, via model) | 9 | Medium |

**Oracle-specific SQL patterns in `Corepass_model.php`:**

| Pattern | Count | ANSI Equivalent |
|---------|-------|-----------------|
| Oracle outer join `col(+)` | 31 | `LEFT OUTER JOIN … ON` |
| `NVL(expr, fallback)` | 1 | `COALESCE(expr, fallback)` |
| `ROWNUM = 1` | 1 | `LIMIT 1` (MySQL/PG) or `FETCH FIRST 1 ROW ONLY` |
| String concat `\|\|` | multiple | `CONCAT()` or `\|\|` (ANSI-ok in PG) |

---

## Layer 1 — Configuration

### `application/config/database.php`

Lines 77–100 define the Oracle connection group; line 103 resets `$active_group` back to `'default'` (MySQL), so the Oracle group is **not** the runtime default — it is loaded on-demand by `Corepass_model`.

```php
// line 77
$active_group = 'oracle';        // temporarily set during definition

// lines 80–100
$db['oracle'] = array(
    'hostname' => getenv('ORDB_HOST').':'.getenv('ORDB_PORT').'/'.getenv('ORDB_DATABASE'),
    'username' => getenv('ORDB_USERNAME') ? getenv('ORDB_USERNAME') : 'PH32_PROD',
    'password' => getenv('ORDB_PASSWORD') ? getenv('ORDB_PASSWORD') : 'PH32_PROD',
    'database' => getenv('ORDB_DATABASE_NAME') ? getenv('ORDB_DATABASE_NAME') : 'COREPASS',
    'dbdriver' => 'oci8',
    'pconnect' => TRUE,
    ...
);

// line 103 — overrides active_group back to MySQL
$active_group = 'default';
```

**Issues:**
- Hardcoded fallback credentials (`PH32_PROD` / `PH32_PROD`) — should be env-only with no fallback.
- `pconnect => TRUE` keeps persistent Oracle connections open even when Oracle is idle.

---

## Layer 2 — Framework OCI8 Drivers (CodeIgniter built-in)

These files ship with CodeIgniter 3 and are **not application code**. They are included here for completeness.

| File | Class | Notes |
|------|-------|-------|
| `system/database/drivers/oci8/oci8_driver.php` | `CI_DB_oci8_driver` | Core driver; uses `OCI_COMMIT_ON_SUCCESS`, `OCI_NO_AUTO_COMMIT`, `OCI_B_CURSOR`; reserves `rownum` as identifier |
| `system/database/drivers/oci8/oci8_result.php` | `CI_DB_oci8_result` | Calls `oci_num_fields()`, `oci_field_name()`, `oci_field_type()`, `oci_field_size()`, `oci_free_statement()` directly |
| `system/database/drivers/oci8/oci8_forge.php` | `CI_DB_oci8_forge` | Oracle DDL (CREATE TABLE, ALTER, DROP) |
| `system/database/drivers/oci8/oci8_utility.php` | `CI_DB_oci8_utility` | Queries `SELECT username FROM dba_users` |
| `system/database/drivers/pdo/subdrivers/pdo_oci_driver.php` | `CI_DB_pdo_oci_driver` | PDO-OCI alternative |
| `system/database/drivers/pdo/subdrivers/pdo_oci_forge.php` | `CI_DB_pdo_oci_forge` | PDO-OCI schema tools |

---

## Layer 3 — Application Model (Primary Concern)

### `application/models/Corepass_model.php` — 2,128 lines

This is the **sole source of Oracle coupling in application business logic**.

#### Constructor (lines 4–12)

```php
public $oracle_db;

public function __construct(){
    parent::__construct();
    if (extension_loaded('oci8')) {
        $this->oracle_db = $this->load->database('oracle', TRUE);
    } else {
        log_message('error', 'Corepass_model: Oracle oci8 extension not loaded.');
        $this->oracle_db = null;
    }
}
```

- Hard dependency on the `oci8` PHP extension.
- Loads the `oracle` named connection group from `database.php`.
- All 28 data methods call `$this->oracle_db->query($sql)` with raw SQL strings.

#### Public Methods (28 data-access functions)

| # | Method | Line | Oracle Patterns |
|---|--------|------|-----------------|
| 1 | `testing()` | 15 | Raw query |
| 2 | `getBankDetailsByTIN($tin)` | 47 | `(+)` outer joins (lines 81–82), `NVL()` (line 18) |
| 3 | `cptestquery($where)` | 101 | Raw query |
| 4 | `getQueryClient($where)` | 151 | `(+)` outer joins (lines 275–276, 292, 296, 304) |
| 5 | `getQueryAddress($addressID)` | 345 | Raw query |
| 6 | `getQueryPeople($peopleID)` | 383 | Raw query |
| 7 | `getBankAccountByCPID($agreement_id)` | 408 | Raw query |
| 8 | `getBankCode($bankAccountNo, $CP_ID, $agreement_id)` | 472 | Raw query |
| 9 | `getQueryService()` | 534 | Raw query |
| 10 | `getQueryPaymentCon($agrID)` | 555 | `(+)` outer joins |
| 11 | `getQueryCompanyInfo($cpID)` | 720 | Raw query |
| 12 | `getQueryDelPoint($agrID)` | 749 | Raw query |
| 13 | `getQueryAgrSpRole($agrID)` | 770 | Raw query |
| 14 | `getQueryAcctPeopleID($acctID)` | 801 | Raw query |
| 15 | `getQueryAcctSpRoleID($acctID)` | 818 | Raw query |
| 16 | `getQueryContactDataID($acctID)` | 852 | Raw query |
| 17 | `getQueryAgreementRole($AGREEMENT_ID)` | 905 | `(+)` outer joins |
| 18 | `getQueryDigitalClients($where)` | 958 | `(+)` outer joins (multiple) |
| 19 | `getQueryAgreementDigitalRole($AGREEMENT_ID)` | 1125 | `(+)` outer joins |
| 20 | `getDigitalSOAOrder($where)` | 1177 | `(+)` outer joins |
| 21 | `getDigitalSOAOrderBillable($where)` | 1322 | `(+)` outer joins |
| 22 | `checkConnect($where)` | 1411 | Raw query |
| 23 | `getQueryMerchantConv($where)` | 1421 | `(+)` outer joins |
| 24 | `getQueryMerConvAgr($where)` | 1463 | `(+)` outer joins, `ROWNUM = 1` (line 1586) |
| 25 | `getQueryMerRemittance($where)` | 1614 | `(+)` outer joins |
| 26 | `getDigitalRemittanceBillable($where)` | 1741 | `(+)` outer joins |
| 27 | `getQueryAgreements($where)` | 1785 | `(+)` outer joins |
| 28 | `getQueryClientV2($where)` | 1911 | `(+)` outer joins |

#### Oracle-Specific SQL Patterns — Annotated Examples

**Oracle outer join `(+)` — 31 instances**
```sql
-- line 81-82 (getBankDetailsByTIN)
AND EFCP.BANKCODETYPE_ID = EBCTP.BANKCODETYPE_ID(+)
AND EBCTP.NAME_ID = EMLS_EBCTP.STRING_ID(+)
-- ANSI equivalent:
-- LEFT JOIN E_BANK_CODE_TYPE EBCTP ON EFCP.BANKCODETYPE_ID = EBCTP.BANKCODETYPE_ID
-- LEFT JOIN E_MULTI_LANGUAGE_STRING EMLS_EBCTP ON EBCTP.NAME_ID = EMLS_EBCTP.STRING_ID
```

**NVL() — 1 instance**
```sql
-- line 18 (getBankDetailsByTIN)
NVL(TOR.Q_ESTIMATEDVOUCHERS, NOT_CONFIRMEDORDER_VOUCHER_QTY.VOUCHER_QUANTITY) VouchersQTY
-- ANSI equivalent:
-- COALESCE(TOR.Q_ESTIMATEDVOUCHERS, NOT_CONFIRMEDORDER_VOUCHER_QTY.VOUCHER_QUANTITY)
```

**ROWNUM — 1 instance**
```sql
-- line 1586 (getQueryMerConvAgr)
AND ROWNUM = 1
-- MySQL/MariaDB equivalent: LIMIT 1
-- PostgreSQL equivalent: FETCH FIRST 1 ROW ONLY
```

**String concatenation `||`**
```sql
-- (present in multiple queries)
EMFTR.A_BASERANGE || ' - ' || EMFTR.N_PERCENTAGE * 100 || ' %'
-- Works in Oracle and PostgreSQL natively; MySQL uses CONCAT()
```

#### Backup / Stale Copies (should be deleted)

All of the following contain identical Oracle coupling and appear to be leftover backup files that should be removed from the repository:

| File | Notes |
|------|-------|
| `application/models/Corepass_model - Copy.php` | Exact copy |
| `application/models/Corepass_model - 11-05-2025.php` | Dated snapshot |
| `application/models/Corepass_model_BACKUPH2H.php` | H2H variant backup |
| `application/models/Corepass_mode_3rd_deploymentl.php` | Deployment snapshot (typo in filename) |
| `application/models/backup/Corepass_model.php` | Backup subdirectory copy |
| `application/backUP/models/Corepass_model.php` | Backup directory copy |

---

## Layer 4 — Controllers (Indirect Oracle Coupling)

These controllers load `Corepass_model` and call its Oracle-backed methods. They have no direct OCI8 code but are tightly coupled to Oracle via the model interface.

| Controller | Module | Oracle Methods Called |
|-----------|--------|----------------------|
| `application/modules/sfchecker/controllers/Sfchecker.php` | sfchecker | `getQueryDelPoint`, `getQueryAgrSpRole`, `getQueryAcctPeopleID`, `getQueryAcctSpRoleID`, `getQueryContactDataID`, `getQueryCompanyInfo` |
| `application/modules/syscore/controllers/Syscore.php` | syscore | Corepass_model general use |
| `application/modules/syscore/controllers/Syspa.php` | syscore | Corepass_model general use |
| `application/modules/syscore/controllers/Autoprocess.php` | syscore | Corepass_model general use |
| `application/modules/syscore/controllers/Offlineprocess.php` | syscore | Corepass_model general use |
| `application/modules/automate/controllers/Digital.php` | automate | Corepass_model general use |
| `application/modules/automate/controllers/Digital - Copy.php` | automate | Backup copy — should be deleted |
| `application/modules/automate/controllers/Navision.php` | automate | Corepass_model general use |
| `application/modules/automate/controllers/Navision_3rd_deployment.php` | automate | Deployment snapshot — should be deleted |
| `application/modules/admin/controllers/Admin.php` | admin | Corepass_model general use |
| `application/modules/pdf_pa/controllers/Pdf_pa_backup.php` | pdf_pa | Backup copy — should be deleted |

---

## Security Notes

A related security audit (`AUDIT_mp_dis_automate_digital_soa.md`) already documents an **unsanitized `$where` parameter** passed directly into Oracle SQL in several `Corepass_model` methods (e.g., `getDigitalSOAOrder`, `getQueryDigitalClients`). The tight coupling to raw SQL amplifies this risk — any controller that forwards user input into these `$where` parameters without sanitization is vulnerable to SQL injection against the Oracle database.

---

## Decoupling Guidance

If migration away from Oracle is required, the work is concentrated in one file:

### SQL Conversion Checklist for `Corepass_model.php`

1. **Oracle outer joins (31 instances)** — Replace all `table.col(+)` with explicit `LEFT OUTER JOIN … ON` clauses. This is the largest effort.
2. **NVL() (1 instance, line 18)** — Replace with `COALESCE()`.
3. **ROWNUM (1 instance, line 1586)** — Replace with `LIMIT 1`.
4. **String concat `||`** — Safe in PostgreSQL as-is; replace with `CONCAT()` for MySQL.
5. **Connection loading** — Replace `$this->load->database('oracle', TRUE)` with the target DB group.
6. **Extension check** — Remove `extension_loaded('oci8')` guard once OCI8 is no longer needed.

### Estimated Scope

| Task | Effort |
|------|--------|
| Convert 31 `(+)` outer joins to ANSI JOIN syntax | High |
| Replace NVL, ROWNUM (2 total) | Trivial |
| Update DB connection loading | Trivial |
| Delete 6+ stale backup model copies | Trivial |
| Delete 3 stale controller backup copies | Trivial |
| Regression-test 9 affected controllers | Medium |

---

## File Inventory

### Application Files With Oracle Coupling

| File | Type | Severity |
|------|------|----------|
| `application/config/database.php` | Config | Low |
| `application/models/Corepass_model.php` | Model | **High** |
| `application/models/Corepass_model - Copy.php` | Stale backup | Cleanup |
| `application/models/Corepass_model - 11-05-2025.php` | Stale backup | Cleanup |
| `application/models/Corepass_model_BACKUPH2H.php` | Stale backup | Cleanup |
| `application/models/Corepass_mode_3rd_deploymentl.php` | Stale backup | Cleanup |
| `application/models/backup/Corepass_model.php` | Stale backup | Cleanup |
| `application/backUP/models/Corepass_model.php` | Stale backup | Cleanup |
| `application/modules/sfchecker/controllers/Sfchecker.php` | Controller | Medium |
| `application/modules/syscore/controllers/Syscore.php` | Controller | Medium |
| `application/modules/syscore/controllers/Syspa.php` | Controller | Medium |
| `application/modules/syscore/controllers/Autoprocess.php` | Controller | Medium |
| `application/modules/syscore/controllers/Offlineprocess.php` | Controller | Medium |
| `application/modules/automate/controllers/Digital.php` | Controller | Medium |
| `application/modules/automate/controllers/Digital - Copy.php` | Stale backup | Cleanup |
| `application/modules/automate/controllers/Navision.php` | Controller | Medium |
| `application/modules/automate/controllers/Navision_3rd_deployment.php` | Stale backup | Cleanup |
| `application/modules/admin/controllers/Admin.php` | Controller | Medium |
| `application/modules/pdf_pa/controllers/Pdf_pa_backup.php` | Stale backup | Cleanup |

### Framework Files (CodeIgniter built-in — not application code)

| File | Notes |
|------|-------|
| `system/database/drivers/oci8/oci8_driver.php` | CI OCI8 driver |
| `system/database/drivers/oci8/oci8_result.php` | OCI8 result handler |
| `system/database/drivers/oci8/oci8_forge.php` | OCI8 DDL |
| `system/database/drivers/oci8/oci8_utility.php` | OCI8 utilities |
| `system/database/drivers/pdo/subdrivers/pdo_oci_driver.php` | PDO-OCI driver |
| `system/database/drivers/pdo/subdrivers/pdo_oci_forge.php` | PDO-OCI DDL |
