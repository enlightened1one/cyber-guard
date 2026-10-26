# Security baseline

This prototype follows a practical security baseline appropriate for a local academic demo.

- Passwords are hashed with `PASSWORD_DEFAULT`.
- All database queries use prepared statements.
- CSRF tokens are generated and validated on mutating requests.
- File uploads are limited and stored outside the public web root.
- Access control is enforced server-side and stays deny-by-default.
- Error pages hide internal stack traces and configuration secrets.
