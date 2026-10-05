# Controllers

HTTP entry points currently live in `public/` so the project remains easy to
run in XAMPP. New request orchestration belongs here as modules are migrated:
validate input, call a service, choose a view, and redirect with flash state.

Do not place SQL or HTML templates in controllers.
