UPSKILL Stacking Framework Form Redesign v11

Purpose
-------
Redesigns the admin stacking-framework form so the completion rule is explicit and aligned with the institutional framework model.

Key changes
------------
1. Adds Completion Model:
   - Complete all required microcredentials
   - Complete a minimum number of required microcredentials (N-of-M)
2. The old ambiguous Required Microcredential Count field is now only used for N-of-M.
3. In All Required mode, the system automatically sets required_count to the number of microcredentials marked Required. A lower legacy required_count cannot make a 2-of-2 framework appear 100% after only one completion.
4. Adds Cumulative Learning Outcomes, one outcome per line.
5. Clarifies academic-recognition fields with labels/help text.
6. Clarifies that the approving academic unit is an academic unit, not a role such as Dean.
7. Keeps the existing modal/shared authenticated navbar/sidebar and the v10 Activate workflow.
8. Student stacking progress now uses completion_mode when calculating the target.
9. Adds regression tests for All Required vs Minimum Required.

Important behavior
------------------
- All Required + 2 required microcredentials = 2 of 2 target. One completion = 50%; recognition is not eligible.
- Minimum Required + 3 required microcredentials + minimum 2 = 2 of 3 target. Two completions = 100%.
- Optional microcredentials do not count toward the required target.
- Sequence Required still enforces the configured order among required microcredentials.
- Academic credit is never created automatically by reaching 100%; the recognition workflow remains separate.

Migration
---------
Run:
  php artisan migrate

This adds stacking_frameworks.completion_mode with a default of all_required. Existing frameworks therefore become safe All Required configurations. If an existing framework was intentionally N-of-M, edit it and select the Minimum Required completion model.

Then clear caches:
  php artisan optimize:clear

Tests
-----
Run locally with the PHP extensions required by your Laravel/Pest setup:
  php artisan test --filter=StackingFrameworkAdministrationTest
  php artisan test --filter=StackingProgressionTest

The development verification environment used for this patch does not have PHP DOMDocument enabled, so full Artisan/Pest execution could not be completed there. PHP syntax checks passed for all modified PHP files.
