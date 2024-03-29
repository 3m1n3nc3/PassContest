# PassContest

Project: Passcontest - Contest voting script
Written by: 3m1n3nc3
For: Newnify Music Limited
Start Date: 22 Oct. 2018
Initial Release: 12 01 2019
Version: Version 1.3.0
Version date: 30 02 2019
Version Info: Major bugs and security fixes.

Change Log:

Version 1.1.0 - 23 01 2019
---------------------
1. Security Enhancement
2. General Bug Fixes

Version 1.2.0 - 27 01 2019
---------------------
1. Fixed bug that displays the login form on the source code, even after login.
2. Added a gallery option, to view and upload images by both manager and user.
3. Added option for contest manager to choose whether to allow free contestants.
4. Allow Cropping image before upload.
5. Separated email and phone details on profile and account from address, when address is absent.
8. Allow scrolling through user gallery photos from voting page.
9. Fixed bug that returns an error when suspended users visit the premium page. 

Version 1.3.0 - 30 02 2019
---------------------
1. Added a timeline feature.
2. Added follow as relationships.
3. Login can now be completed with a registered email address.
4. Fixed bug that overrides system error reporting settings.
5. Fixed the width of photos to make the fully visible.
6. Added Page to view relationships.
7. Added a messenger feature.
8. Added twilio SMS.
9. Added option to send test SMS and Email.
10. Other bug fixes.
11. Added a global search feature.
12. Added ability to choose country, state and city instead of typing it.
13. Added gender to user profile
14. Added options to set skin and change landing page in settings
15. Added options to set skin and change landing page in settings
16. Added option so set introductory text without having to edit language files
17. Added an auto installer
18. Built an Developer API surface

Log
DB - Added: "cid" to table "contest"
DB - Added: "tags" to table "contest"

DB - Added: "online" to table "users"

DB - Added: "email_reply_temp" to table "settings"
DB - Added: "online_time" to table "settings"
DB - Added: "per_messenger" to table "settings"
DB - Added: "email_social" to table "settings"
DB - Added: "twillio_sid" to table "settings"
DB - Added: "twillio_token" to table "settings"
DB - Added: "twillio_phone" to table "settings"
DB - Added: "site_phone" to table "settings"
DB - Added: "ads_6" to table "settings"
DB - Added: "skin" to table "settings"
DB - Added: "landing" to table "settings"
DB - Added: "sms" to table "settings"
DB - Added: "sms_premium" to table "settings"

DB - Added: "master" to table "comment"

DB - Created: table "timelines"
DB - Created: table "relationships"
DB - Created: table "likes"
DB - Created: table "blocked"
DB - Created: table "countries"
DB - Created: table "states"
DB - Created: table "cities"
DB - Created: table "welcome"
DB - Created: table "api"

API Login: passcontest.com/api.php?a=connect&client_id=1000&token=1234567890&redirect_uri=http://passcontest.com/api.php?a=connect

API Profile: http://passcontest.com/api.php?a=profile&client_id=1000&id=2
API Profile LIST: http://passcontest.com/api.php?a=profile&client_id=1000&list=true

API Contest: http://passcontest.com/api.php?a=contest&client_id=1000&id=3
API Contest LIST: http://passcontest.com/api.php?a=contest&client_id=1000&list=true
