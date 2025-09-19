# Facebook Page Access Token Information

## Token Expiration for Page Posting

### The Short Answer
**Facebook Page Access Tokens obtained through the proper exchange process do not expire.**

### What This Means
When you run `setup.php` and follow the token exchange process, the script:
1. Takes your short-lived user token
2. Exchanges it for a long-lived user token (60+ days)
3. Uses that to get a Page Access Token
4. **This Page Access Token never expires** ✅

### Important Conditions
The Page Access Token remains valid indefinitely as long as:
- ✅ The user (page admin) doesn't revoke the app's permissions
- ✅ The Facebook app remains active and not disabled
- ✅ The user remains an admin of the Facebook page
- ✅ The Facebook app's credentials don't change

### For Posting to Pages Only
Since you're only concerned with posting to a Facebook page:
- **You don't need to worry about token renewal**
- **You don't need to implement refresh logic**
- **The token obtained from setup.php will work indefinitely**

### If Something Goes Wrong
The only time you'd need a new token is if:
- The app permissions are revoked
- The page admin removes the app
- Facebook detects suspicious activity and invalidates the token

In these cases, simply run `setup.php` again to get a new token.

### Bottom Line
**For page posting, set it once and forget it!** The token you get from the setup process will keep working until something explicitly breaks the connection between your app and the Facebook page.