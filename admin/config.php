<?php
/* =========================================================
   Vent Studio — admin accounts.
   The OWNER (LT_OWNER) is the only account that can manage
   other admins from the Users page. Admins live in
   data/admins.json (editable in the UI); the list below is the
   first-run seed used only before data/admins.json exists.

   Passwords are stored ONLY as bcrypt hashes. To make one, run:
     php -r "echo password_hash('YOUR_PASSWORD', PASSWORD_BCRYPT), PHP_EOL;"
   ========================================================= */

define('LT_OWNER', 'admin@example.com');

define('LT_ADMINS', [
    'admin@example.com'       => '$2y$10$Fv6vH0C8Zs1/OXjit.ky8OdpVMJKx6/eWDzF/KRIoqoyN0rKEJOdK',
    'staff@example.com' => '$2y$10$7jl4U4DZGivTS.BnwoH6qOMCXi6ibARl9N4wSpYcM4BdXcihrshla',
]);
