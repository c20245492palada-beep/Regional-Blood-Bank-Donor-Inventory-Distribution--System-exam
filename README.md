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
| Password Recovery | New / Update | Added phone-only recovery using a six-digit SMS code through Semaphore instead of an email link. |
| Account Settings | New / Update | Added mobile-number enrollment and verification; phone changes require the current password. |
| Phone Number Saving | Fix | Accepted both `09XXXXXXXXX` and `+639XXXXXXXXX`, normalized values, and fixed the browser validation rejection. |
| Local Development Environment | Fix | Restarted the stopped Laravel development processes after Nginx returned 502. |
| Authentication Tests and Documentation | Update | Expanded OTP regression tests and recorded current behavior, verification, and remaining tasks. |

### 3. Explanation of Each Change

#### Phone-Based Password Recovery

1. Password recovery now asks for a phone number instead of an email address.
2. The form accepts Philippine mobile numbers entered as `09XXXXXXXXX` or `+639XXXXXXXXX`.
3. Before lookup, the application normalizes national-format numbers to the `+639...` format stored in the database.
4. Only accounts with a verified phone number can request a password reset.
5. A new six-digit code is generated for each eligible recovery request.
6. The code is sent through the Semaphore SMS API and is not placed in an email or reset link.
7. The user submits the same phone number, the received code, and a new password.
8. The database stores a hash of the code, not the original code.
9. Codes expire after ten minutes and are consumed after successful use.
10. Each code has a five-attempt limit, and repeat requests for the same account have a 60-second delay.
11. Route throttling also limits recovery requests and code submissions.
12. Unknown or unverified numbers receive the same recovery-request response as eligible numbers.
13. A successful reset hashes the new password and rotates the remember token.
14. Feature tests verify the SMS request using an HTTP fake, so automated tests do not send real messages.
15. The Semaphore key is recognized by the local app, but delivery to a real phone still needs a live test.

#### Account Settings

1. A **Settings** link is available from the dashboard navigation.
2. The settings route requires an authenticated user.
3. The profile form supports updates to the user's name, email address, and mobile number.
4. New registrations require a unique mobile number.
5. Existing users can add or change their mobile number from Settings.
6. Changing the number requires the current password to help prevent an unauthorized recovery-number swap.
7. A changed number loses its verified status, and any pending codes for that account are deleted.
8. The verification form sends a six-digit code to the saved number through Semaphore.
9. A number becomes eligible for password recovery only after its verification code is accepted.
10. Password changes also require the current password and a confirmed new password of at least eight characters.
11. Passwords are hashed, and the remember token is rotated after a password change.
12. Tests cover profile updates, duplicate numbers, phone verification, password changes, and guest access.
13. All signed-in users can use Settings; patient and administrator roles are not yet separated.

#### Phone Number Saving Fix

1. The settings form previously accepted only the international `+639...` phone format.
2. Entering a valid local number such as `09302324013` caused the browser to block submission.
3. The phone input pattern now accepts both `09XXXXXXXXX` and `+639XXXXXXXXX`.
4. Server-side normalization converts local-format numbers before validation and saving.
5. Both formats are stored consistently as `+639...` numbers.
6. Registration, Settings, password-recovery requests, and password-reset submissions use the same normalization.
7. The database uniqueness check therefore applies to one canonical value per phone number.
8. The phone fields and OTP storage table were added through migrations.
9. The migrations were applied to the local database.
10. Feature tests confirm that local-format numbers save in normalized form and duplicate numbers are rejected.

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

1. Registration tests now confirm that a phone number is required and starts unverified.
2. Recovery tests check that a verified number triggers a Semaphore SMS request.
3. Those requests are faked in tests, so no real SMS is sent during automated validation.
4. Tests confirm that unknown and unverified numbers receive the generic response without an SMS request.
5. A valid recovery code changes the password and cannot be reused.
6. Invalid, expired, and exhausted codes do not change the password.
7. Settings tests verify number normalization, uniqueness, current-password confirmation, and phone verification.
8. The full test suite passed **30 tests with 169 assertions**.
9. Laravel Pint was run on the changed PHP files.
10. This README documents the new flow, configuration, local server recovery, and known limitations.

### 4. Remaining Tasks Before Final Submission

The target dates below are suggested and can be adjusted to match the project schedule.

| Remaining Task | Responsible | Target Completion |
|---|---|---|
| Send and verify a recovery code on a real phone; check Semaphore balance and sender settings. | Project developer / team (to be assigned) | November 20, 2026 |
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
2. Enter the verified mobile number saved on the account in `09XXXXXXXXX` or `+639XXXXXXXXX` format.
3. Retrieve the six-digit code sent by SMS.
4. Enter the phone number, code, new password, and confirmation on the reset form.

The code expires after ten minutes, can only be used once, and is limited to five attempts. For a new account, first verify its phone number from **Settings**.

## How to Use Account Settings

After signing in, select **Settings** from the dashboard.

- **Profile information**: update the name, email address, or mobile number. Email addresses and phone numbers must be unique; changing the mobile number also requires the current password.
- **Phone verification**: save a mobile number, request an SMS code, and enter that code to enable password recovery.
- **Change password**: enter the current password, new password, and confirmation. The new password must be at least eight characters.

Changing the saved mobile number clears its verification status. Existing users who do not yet have a phone number must add and verify one before using phone-based recovery.

## Configure Semaphore SMS

Password recovery and phone verification use Semaphore's SMS API. Add the following values to your private local `.env` file or deployment secret manager:

```dotenv
SEMAPHORE_API_KEY=your-semaphore-api-key
SEMAPHORE_SENDER_NAME=
```

`SEMAPHORE_SENDER_NAME` is optional and depends on the sender name configured for the Semaphore account. Do not put a real API key in this README, source control, or a chat message. The `.env.example` contains empty placeholders only. Real SMS delivery may incur provider charges and cannot be confirmed until a valid key and recipient phone are configured.

The local `MAIL_MAILER=log` setting does not affect password recovery; this flow does not send recovery email. A `password_reset_tokens` table migration from the earlier email-based implementation may remain in an existing database; the current SMS flow stores hashed codes in `phone_verification_codes`.

## Database and Tests

Run pending migrations before using the feature in a new environment:

```bash
php artisan migrate
```

Run the test suite with:

```bash
php artisan test --compact
```

## Security Limitations

- Password reset rotates the account's `remember_token`, but does not automatically revoke every active browser session.
- Phone recovery relies on control of the verified phone number and the security of the Semaphore account and delivery network.
- The SMS API key must be stored in a protected environment variable or secret manager, never committed to source control.
- The local app has not yet been verified against a live Semaphore account or real phone.
- Use HTTPS in production, set the correct production `APP_URL`, and do not leave `APP_DEBUG` enabled.
- The app does not currently have separate patient and administrator roles.
