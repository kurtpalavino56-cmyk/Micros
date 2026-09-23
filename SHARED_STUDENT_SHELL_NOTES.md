# Shared Student Shell Migration

All active student-facing pages now use the centralized `components.student-navigation` include, which delegates the authenticated topbar to one shared source. Pages with the standard dashboard shell use the shared `components.student-sidebar` and `student-sidebar-layout` contract. Dashboard and inbox were migrated from bespoke navigation markup to that shared sidebar. Analytics now also loads the shared responsive layer.

The course player remains intentionally specialized: its course/module navigation is the primary navigation while learning, so it keeps the course-specific layout but uses the centralized authenticated topbar and responsive component.

Validation completed: strict Blade reference check passed with zero missing references; student layout and main-tag heuristics are balanced; no bespoke student topbar/sidebar markup remains in active student templates.
