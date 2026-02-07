@component('mail::message')
# Welcome to Isaiah Nail Bar

Dear {{ $provider->name }},

Your service provider account has been successfully created. We are pleased to have you join our team.

---

## Login Credentials

Please use the following credentials to access your account:

**Email:** {{ $provider->email }}  
**Password:** {{ $password }}

@component('mail::button', ['url' => $loginUrl])
Login to Your Account
@endcomponent

---

## Important Security Information

**Please take the following actions immediately:**

1. Login to your account using the credentials above
2. Change your password to something secure and memorable
3. Keep your credentials confidential
4. Never share your login information with anyone

---

## Next Steps

Once logged in, you will be able to:
- View your assigned appointments
- Manage your schedule
- Update your profile information
- Access booking details

---

If you have any questions or need assistance, please contact our management team.

Best regards,  
Isaiah Nail Bar Management
@endcomponent
