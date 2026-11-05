---
paths:
  - 'app/Providers/Filament/**,app/Http/Middleware/**,routes/web.php'
---

# Middleware

## Use one branded login page
The canonical authentication screen is the custom `/login` route. Do not enable Filament's built-in `->login()` page; `AuthenticateFilament` must redirect unauthenticated panel requests to the named `login` route.
