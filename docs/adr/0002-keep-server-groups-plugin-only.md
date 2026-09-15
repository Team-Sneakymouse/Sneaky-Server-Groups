# Keep Server Groups plugin-only

Sneaky Server Groups is implemented as a standalone plugin beside the panel source, without modifying Pelican panel or Wings code. The App Server List grouping can be extended through Pelican and Filament plugin hooks, while tab removal/default-tab behavior lacks a clean extension point and should not justify a maintained fork.
