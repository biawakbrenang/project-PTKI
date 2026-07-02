<?php
/**
 * Auth guard component.
 *
 * The visual top header was intentionally removed so the application uses a
 * focused sidebar-only shell. Existing pages may still include this file; keep
 * it as a lightweight guard for backward compatibility.
 */

require_once __DIR__ . '/../auth/check_auth.php';

requireLogin();

?>
