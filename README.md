# BSIS-3B Blood Bank

## Progress Report - 1

### 1. Project Status

- **System Name:** Regional Blood Bank Donor Inventory and Distribution System (BSIS-3B Blood Bank)
- **Date:** November 16, 2026
- **Current Completion:** 25%
- **Current Stage:** Integration, UI polish, and bug fixing

### 2. Latest System Changes

| Module / Feature | Type | Short Summary of Change |
|---|---|---|
| Password Recovery | Update | Uses Laravel's email reset link instead of SMS verification codes. |
| Account Settings | New / Update | Lets users save a mobile contact number without phone verification. |
| Phone Number Saving | Fix | Accepted both `09XXXXXXXXX` and `+639XXXXXXXXX`, normalized values, and fixed the browser validation rejection. |
| Local Development Environment | Fix | Restarted the stopped Laravel development processes after Nginx returned 502. |
| Authentication Tests and Documentation | Update | Added email password-reset coverage and recorded current behavior, verification, and remaining tasks. |

### 3. Explanation of Each Change

#### Email-Based Password Recovery

1. Password recovery asks for the email address associated with the account.
2. Laravel sends a password reset link using the configured mail transport.
3. The link contains a time-limited, one-time reset token.
4. A successful reset hashes the new password and rotates the remember token.
5. Unknown email addresses receive the same confirmation response to avoid account discovery.
6. Feature tests verify notification delivery and successful token use.

#### Account Settings

1. A **Settings** link is available from the dashboard navigation.
2. The settings route requires an authenticated user.
3. The profile form supports updates to the user's name, email address, and mobile number.
4. New registrations require a unique mobile contact number.
5. Existing users can add or change their mobile contact number from Settings.
6. The mobile number is contact information and is not used for password recovery.
7. Password changes require the current password and a confirmed new password of at least eight characters.
8. Passwords are hashed, and the remember token is rotated after a password change.
9. Tests cover profile updates, duplicate numbers, password changes, and guest access.
10. All signed-in users can use Settings; patient and administrator roles are not yet separated.

#### Phone Number Saving Fix

1. The settings form previously accepted only the international `+639...` phone format.
2. Entering a valid local number such as `09302324013` caused the browser to block submission.
3. The phone input pattern now accepts both `09XXXXXXXXX` and `+639XXXXXXXXX`.
4. Server-side normalization converts local-format numbers before validation and saving.
5. Both formats are stored consistently as `+639...` numbers.
6. Registration and Settings use the same normalization.
7. The database uniqueness check therefore applies to one canonical value per phone number.
8. Feature tests confirm that local-format numbers save in normalized form and duplicate numbers are rejected.

#### Local Development Server and Database

1. The browser displayed an Nginx **502 Bad Gateway** page.
2. A request to the configured local PHP port could not connect while the Laravel server was stopped.
3. Nginx itself was running, but the application development process was not.
4. The project's configured `composer run dev` command was started.
5. It launched Laravel's server, the queue listener, and Vite.
6. The project domain then redirected from `/` to `/login` as expected.
7. The `/login` endpoint returned HTTP 200 after startup.
8. This was an environment/server availability issue, not an application validation error.
9. The development processes are currently running in the local session.
10. If they stop, run `composer run dev` again from the project directory.

#### Authentication Tests and Documentation

1. Registration tests confirm that a unique phone number is required.
2. Recovery tests verify email notification behavior and token-based password reset.
3. Settings tests verify number normalization, uniqueness, password changes, and guest access.

### 4. Remaining Tasks Before Final Submission

The target dates below are suggested and can be adjusted to match the project schedule.

| Remaining Task | Responsible | Target Completion |
|---|---|---|
| Configure and verify the production email transport for password recovery. | Project developer / team (to be assigned) | November 20, 2026 |
| Revoke or invalidate existing sessions after a password reset. | Project developer / team (to be assigned) | November 21, 2026 |
| Define patient and administrator roles and add role-specific settings if required. | Project developer / team (to be assigned) | November 24, 2026 |
| Complete final end-to-end regression tests and review production security configuration. | Project developer / team (to be assigned) | November 27, 2026 |

### 5. GitHub Repository Status

Changes are in the local working tree and may not appear in GitHub until they are committed and pushed.

### 6. GitHub Repository Link

[Regional Blood Bank Donor Inventory and Distribution System](https://github.com/c20245492palada-beep/Regional-Blood-Bank-Donor-Inventory-Distribution--System-exam)

---

## How to Recover a Password

1. Select **Forgot Password?** on the login page.
2. Enter the email address associated with the account.
3. Open the password reset link sent to that email address.
4. Choose and confirm a new password.

## How to Use Account Settings

After signing in, select **Settings** from the dashboard.

- **Profile information**: update the name, email address, or mobile contact number. Email addresses and phone numbers must be unique.
- **Change password**: enter the current password, new password, and confirmation. The new password must be at least eight characters.

## Database and Tests

Run pending migrations before using the feature in a new environment:

```bash
php artisan migrate
```

For an existing MySQL database that still has the old SMS recovery schema, remove it in phpMyAdmin:

```sql
DROP TABLE IF EXISTS `phone_verification_codes`;
ALTER TABLE `users` DROP COLUMN `phone_verified_at`;
```

The `phone_number` column remains as contact information.

Run the test suite with:

```bash
php artisan test --compact
```

## Security Limitations

- Password reset rotates the account's `remember_token`, but does not automatically revoke every active browser session.
- Password recovery depends on the configured email transport being able to deliver reset links.
- Use HTTPS in production, set the correct production `APP_URL`, and do not leave `APP_DEBUG` enabled.
- The app does not currently have separate patient and administrator roles.
